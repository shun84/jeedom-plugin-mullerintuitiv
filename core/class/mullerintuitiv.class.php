<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */

use GuzzleHttp\Exception\GuzzleException;

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../core/api/mullerintuitivApi.php';

class mullerintuitiv extends eqLogic {

    /*     * *************************Attributs****************************** */
    public static $_widgetPossibility = array('custom' => true, 'custom::layout' => false);

    protected const ICONWINDOWSON = '<i class=\'icon jeedom-fenetre-ouverte\'></i>';
    protected const ICONWINDOWSOFF = '<i class=\'icon jeedom-fenetre-ferme\'></i>';

    /*     * ***********************Methode static*************************** */

    public static function templateWidget(): array
    {
        $return = ['info' => ['binary' => []]];
        $return['info']['binary']['windows'] = [
            'template' => 'tmplicon',
            'replace' => [
                '#_icon_on_#' => self::ICONWINDOWSON,
                '#_icon_off_#' => self::ICONWINDOWSOFF
            ]
        ];

        return $return;
    }

    public static function getMullerintuitivApi(): mullerintuitivApi
    {
        $username = config::byKey('login','mullerintuitiv');
        $password = config::byKey('mdp','mullerintuitiv');
        return new mullerintuitivApi($username,$password);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public static function getSession(){
        $api = mullerintuitiv::getMullerintuitivApi();
        try {
            $token = $api->getToken();
            $tokens = json_decode($token->getBody()->getContents(), true);
            config::save('access_token',$tokens['access_token'],'mullerintuitiv');
            config::save('refresh_token',$tokens['refresh_token'],'mullerintuitiv');
            config::save('expires_in', time()+$tokens['expires_in'],'mullerintuitiv');
        } catch (Exception $e){
            if ($e->getCode() != 503 || $e->getCode() != 504 || $e->getCode() != 502 || $e->getCode() != 404){
                throw new Exception(__($e->getMessage(), __FILE__));
            }
        }

    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public static function getAccesToken(): string
    {
        $api = self::getMullerintuitivApi();

        if (config::byKey('access_token','mullerintuitiv') === ''){
            mullerintuitiv::getSession();
        }

        if (config::byKey('refresh_token','mullerintuitiv') !== '' && time() < config::byKey('expires_in','mullerintuitiv')){
            try {
                $refreshtoken = $api->getRefreshToken(config::byKey('refresh_token','mullerintuitiv'));
                $refreshtokens = json_decode($refreshtoken->getBody()->getContents(), true);
                config::save('access_token',$refreshtokens['access_token'],'mullerintuitiv');
                config::save('refresh_token',$refreshtokens['refresh_token'],'mullerintuitiv');
                config::save('expires_in', time()+$refreshtokens['expires_in'],'mullerintuitiv');

            } catch (Exception $e){
                config::remove('access_token','mullerintuitiv');
                throw new Exception(__($e->getMessage(), __FILE__));
            }
        } else {
            mullerintuitiv::getSession();
        }

        return config::byKey('access_token','mullerintuitiv');
    }

    /**
     * @throws GuzzleException
     */
    public static function getHomesSchedulesIdAndName(mullerintuitivApi $api): array
    {
        $token = mullerintuitiv::getAccesToken();
        $gethomeschedulesall = $api->getHomeSchedulesAll($token);

        $gethomescheduleidandname = [];
        foreach ($gethomeschedulesall as $gethomeschedule){
            if (in_array($gethomeschedule['selected'],$gethomeschedulesall)){
                $gethomescheduleidandname[] = [
                    'name' => $gethomeschedule['name'],
                    'id' => $gethomeschedule['id'],
                    'selected' => $gethomeschedule['selected']
                ];
            }else{
                $gethomescheduleidandname[] = [
                    'name' => $gethomeschedule['name'],
                    'id' => $gethomeschedule['id']
                ];
            }
        }

        return $gethomescheduleidandname;
    }

    /**
     * @throws GuzzleException
     */
    public static function getRoomMeasure(
        mullerintuitivApi $mullerintuitivApi,
        string $dateend,
        string $datebegin,
        string $roomid,
        string $bridge,
        string $homeid
    ): array
    {
        $token = mullerintuitiv::getAccesToken();
        $getdayroommeasures = $mullerintuitivApi->getRoomMeasure(
            $roomid,
            $token,
            $dateend,
            $datebegin,
            '1day',
            $bridge,
            $homeid
        );

        $getweekroommeasures = $mullerintuitivApi->getRoomMeasure(
            $roomid,
            $token,
            $dateend,
            $datebegin,
            '1week',
            $bridge,
            $homeid
        );

        $getmonthroommeasures = $mullerintuitivApi->getRoomMeasure(
            $roomid,
            $token,
            $dateend,
            $datebegin,
            '1month',
            $bridge,
            $homeid
        );

        $valueanddate = [
            'day' => [],
            'week' => [],
            'month' => []
        ];

        foreach ($getdayroommeasures as $getdayroommeasure){
            $begtime = $getdayroommeasure['beg_time'];
            $begtime = strtotime('-11 hour', $begtime);
            $values = $getdayroommeasure['value'];
            foreach ($values as $value){
                $val = $value[0];
                $valueanddate['day'][] = [
                    $begtime * 1000,
                    $val / 1000
                ];
                $begtime = strtotime('+1 day', $begtime);
            }
        }

        foreach ($getweekroommeasures as $getweekroommeasure){
            $begtime = $getweekroommeasure['beg_time'];
            $begtime = strtotime('-3 day -11 hour', $begtime);
            $values = $getweekroommeasure['value'];
            foreach ($values as $value){
                $val = $value[0];
                $valueanddate['week'][] = [
                    $begtime * 1000,
                    $val / 1000
                ];
                $begtime = strtotime('+1 week', $begtime);
            }
        }

        foreach ($getmonthroommeasures as $getmonthroommeasure){
            $begtime = $getmonthroommeasure['beg_time'];
            $mois = date('m',$begtime);
            $annee = date('Y', $begtime);
            $begtime = strtotime($annee.'-'.$mois.'-01 00:00:00 UTC');
            $values = $getmonthroommeasure['value'];
            foreach ($values as $value){
                $val = $value[0];
                $valueanddate['month'][] = [
                    $begtime * 1000,
                    $val / 1000
                ];
                $begtime = strtotime('+1 month', $begtime);
            }
        }

        return $valueanddate;
    }


    /**
     * @throws GuzzleException
     */
    public static function getHomeMeasure(
        mullerintuitivApi $mullerintuitivApi,
        string $modulesid,
        string $dateend,
        string $datebegin,
        string $homeid
    ): array
    {
        $token = mullerintuitiv::getAccesToken();
        $getdayhomemeasures = $mullerintuitivApi->getHomeMeasure(
            $modulesid,
            $token,
            $dateend,
            $datebegin,
            '1day',
            $homeid
        );

        $getweekhomemeasures = $mullerintuitivApi->getHomeMeasure(
            $modulesid,
            $token,
            $dateend,
            $datebegin,
            '1week',
            $homeid
        );

        $getmonthhomemeasures = $mullerintuitivApi->getHomeMeasure(
            $modulesid,
            $token,
            $dateend,
            $datebegin,
            '1month',
            $homeid
        );

        $valueanddate = [
            'day' => [],
            'week' => [],
            'month' => []
        ];

        foreach ($getdayhomemeasures as $getdayhomemeasure){
            $begtime = $getdayhomemeasure['beg_time'];
            $begtime = strtotime('-11 hour', $begtime);
            $values = $getdayhomemeasure['value'];
            foreach ($values as $value){
                $val = $value[0];
                $valueanddate['day'][] = [
                    $begtime * 1000,
                    $val / 1000
                ];
                $begtime = strtotime('+1 day', $begtime);
            }
        }

        foreach ($getweekhomemeasures as $getweekhomemeasure){
            $begtime = $getweekhomemeasure['beg_time'];
            $begtime = strtotime('-3 day -11 hour', $begtime);
            $values = $getweekhomemeasure['value'];
            foreach ($values as $value){
                $val = $value[0];
                $valueanddate['week'][] = [
                    $begtime * 1000,
                    $val / 1000
                ];
                $begtime = strtotime('+1 week', $begtime);
            }
        }

        foreach ($getmonthhomemeasures as $getmonthhomemeasure){
            $begtime = $getmonthhomemeasure['beg_time'];
            $mois = date('m',$begtime);
            $annee = date('Y', $begtime);
            $begtime = strtotime($annee.'-'.$mois.'-01 00:00:00 UTC');
            $values = $getmonthhomemeasure['value'];
            foreach ($values as $value){
                $val = $value[0];
                $valueanddate['month'][] = [
                    $begtime * 1000,
                    $val / 1000
                ];
                $begtime = strtotime('+1 month', $begtime);
            }
        }

        return $valueanddate;
    }

    /**
     * @throws Exception|GuzzleException
     */
    public static function getSynMods(mullerintuitivApi $api){
        $token = mullerintuitiv::getAccesToken();
        $homes = $api->getHomes($token);
        $roomsidandname = $api->getRoomsIdAndName($token);

        foreach ($homes as $home){
            $mullerintuitivhome = eqLogic::byLogicalId( 'mullerintuitiv_home_'.$home['id'], 'mullerintuitiv');
            if (!is_object($mullerintuitivhome)) {
                $mullerintuitivhome = new mullerintuitiv();
                $mullerintuitivhome->setName($home['name']);
                $mullerintuitivhome->setLogicalId('mullerintuitiv_home_'.$home['id']);
                $mullerintuitivhome->setEqType_name('mullerintuitiv');
                $mullerintuitivhome->setConfiguration('mullerintuitiv_id', $home['id']);
                $mullerintuitivhome->setConfiguration('mullerintuitiv_type','home');
                $mullerintuitivhome->setConfiguration('mullerintuitiv_therm_relay',$home['modules'][0]['id']);
                $mullerintuitivhome->setIsVisible(1);
                $mullerintuitivhome->setIsEnable(1);
                $mullerintuitivhome->setCategory('heating', 1);
            }
            $mullerintuitivhome->save();

            foreach ($roomsidandname as $room){
                $mullerintuitivmodule = eqLogic::byLogicalId( 'mullerintuitiv_'.$room['id'], 'mullerintuitiv');
                if (!is_object($mullerintuitivmodule)) {
                    $mullerintuitivmodule = new mullerintuitiv();
                    $mullerintuitivmodule->setName($room['name']);
                    $mullerintuitivmodule->setLogicalId('mullerintuitiv_'.$room['id']);
                    $mullerintuitivmodule->setEqType_name('mullerintuitiv');
                }
                $mullerintuitivmodule->setConfiguration('mullerintuitiv_id',$room['id']);
                $mullerintuitivmodule->setConfiguration('mullerintuitiv_type',$room['type']);
                $mullerintuitivmodule->setConfiguration('mullerintuitiv_therm_relay',$room['therm_relay']);
                $mullerintuitivmodule->setIsVisible(1);
                $mullerintuitivmodule->setIsEnable(1);
                $mullerintuitivmodule->setCategory('heating', 1);
                $mullerintuitivmodule->save();
            }
        }
    }

    public static function cron15() {
        foreach (mullerintuitiv::byType('mullerintuitiv') as $eqLogic) {
            if ($eqLogic->getIsEnable() === '1') {
                $api = mullerintuitiv::getMullerintuitivApi();
                $eqLogic->updateApiMullerIntuitiv($api,$eqLogic->getConfiguration('mullerintuitiv_id'));
            }
        }
    }

    /*     * *********************Méthodes d'instance************************* */
    public static function replaceMode(string $mode){
        if ($mode === 'schedule' || $mode === 'home'){
             return str_replace($mode, 'Home',$mode);
        } elseif ($mode === mullerintuitivApi::MODE['HG']){
            return str_replace($mode, 'Hors Gel',$mode);
        } elseif ($mode === mullerintuitivApi::MODE['ABSENT']){
            return str_replace($mode, 'Absent',$mode);
        } elseif ($mode === 'manual'){
            return str_replace($mode, 'Manuel',$mode);
        } elseif ($mode === mullerintuitivApi::MODE['OFF']){
            return str_replace($mode, 'Arreter',$mode);
        }

        return false;
    }

    /**
     * @throws GuzzleException
     */
    public function getListValueNameSchedules(): string
    {
        $api = mullerintuitiv::getMullerintuitivApi();
        $homeschedulesidandname = mullerintuitiv::getHomesSchedulesIdAndName($api);

        $getlistvaluenameschedules = [];
        $count = 0;
        foreach ($homeschedulesidandname as $valuehomeschedule){
            $getlistvaluenameschedules[] = $count++.'|'.$valuehomeschedule['name'];
        }

        return implode(";",$getlistvaluenameschedules);
    }

    /**
     * @throws GuzzleException
     */
    public function updateApiMullerIntuitiv(mullerintuitivApi $api, string $mullerintuitivid){
        $token = mullerintuitiv::getAccesToken();

        $homes = $api->getHomes($token);
        log::add('mullerintuitiv','debug',json_encode($homes));

        foreach ($homes as $home){
            $roomsupdate = $api->getRooms($token, $home['id']);
            log::add('mullerintuitiv','debug',json_encode($roomsupdate));

            if (strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                $homeschedulesidandname = mullerintuitiv::getHomesSchedulesIdAndName($api);

                $this->checkAndUpdateCmd('therm_mode', $this->replaceMode($home['therm_mode']));

                foreach ($homeschedulesidandname as $valuehomeschedule){
                    if ($valuehomeschedule['selected'] === true ){
                        $this->checkAndUpdateCmd('getschedule',$valuehomeschedule['name']);
                    }
                }
            }

            foreach ($roomsupdate as $valueupdate){
                if ($mullerintuitivid === $valueupdate['id']){
                    $this->checkAndUpdateCmd('open_window', $valueupdate['open_window']);
                    $this->checkAndUpdateCmd('therm_measured_temperature', $valueupdate['therm_measured_temperature']);
                    $this->checkAndUpdateCmd('therm_setpoint_mode', $this->replaceMode($valueupdate['therm_setpoint_mode']));
                    $this->checkAndUpdateCmd('therm_setpoint_temperature', $valueupdate['therm_setpoint_temperature']);
                    $this->checkAndUpdateCmd('boost', $valueupdate['boost_status']);
                }
            }
        }

        $this->refreshWidget();
    }

    /**
     * @throws Exception
     */
    public function preSave() {
        if (!preg_match('/home/', $this->getLogicalId())){
            $this->setDisplay("width", "192px");
            $this->setDisplay("height", "280px");
        }
    }

    /**
     * @throws Exception|GuzzleException
     */
    public function postSave() {
        if (preg_match('/home/', $this->getLogicalId())){
            $getmodehome = $this->getCmd(null, 'therm_mode');
            if (!is_object($getmodehome)) {
                $getmodehome = new mullerintuitivCmd();
            }
            $getmodehome->setName(__('Mode', __FILE__));
            $getmodehome->setLogicalId('therm_mode');
            $getmodehome->setEqLogic_id($this->getId());
            $getmodehome->setGeneric_type('THERMOSTAT_MODE');
            $getmodehome->setType('info');
            $getmodehome->setSubType('string');
            $getmodehome->save();

            $setmodeschedule = $this->getCmd(null, 'homemodehome');
            if (!is_object($setmodeschedule)) {
                $setmodeschedule = new mullerintuitivCmd();
            }
            $setmodeschedule->setName(__('Home', __FILE__));
            $setmodeschedule->setLogicalId('homemodehome');
            $setmodeschedule->setEqLogic_id($this->getId());
            $setmodeschedule->setDisplay('icon', '<i class="icon maison-home63"></i>');
            $setmodeschedule->setGeneric_type('THERMOSTAT_SET_MODE');
            $setmodeschedule->setType('action');
            $setmodeschedule->setSubType('other');
            $setmodeschedule->setValue($getmodehome->getId());
            $setmodeschedule->save();

            $setmodehg = $this->getCmd(null, 'homemodefrost');
            if (!is_object($setmodehg)) {
                $setmodehg = new mullerintuitivCmd();
            }
            $setmodehg->setName(__('Hors Gel', __FILE__));
            $setmodehg->setLogicalId('homemodefrost');
            $setmodehg->setEqLogic_id($this->getId());
            $setmodehg->setDisplay('icon', '<i class="icon nature-snowflake"></i>');
            $setmodehg->setGeneric_type('THERMOSTAT_SET_MODE');
            $setmodehg->setType('action');
            $setmodehg->setSubType('other');
            $setmodehg->setValue($getmodehome->getId());
            $setmodehg->save();

            $setmodeaway = $this->getCmd(null, 'homemodeaway');
            if (!is_object($setmodeaway)) {
                $setmodeaway = new mullerintuitivCmd();
            }
            $setmodeaway->setName(__('Absent', __FILE__));
            $setmodeaway->setLogicalId('homemodeaway');
            $setmodeaway->setDisplay('icon', '<i class="fas fa-sign-out-alt"></i>');
            $setmodeaway->setEqLogic_id($this->getId());
            $setmodeaway->setGeneric_type('THERMOSTAT_SET_MODE');
            $setmodeaway->setType('action');
            $setmodeaway->setSubType('other');
            $setmodeaway->setValue($getmodehome->getId());
            $setmodeaway->save();

            $gethomeschedule = $this->getCmd(null, 'getschedule');
            if (!is_object($gethomeschedule)) {
                $gethomeschedule = new mullerintuitivCmd();
            }
            $gethomeschedule->setName(__('Récupération du planning', __FILE__));
            $gethomeschedule->setLogicalId('getschedule');
            $gethomeschedule->setEqLogic_id($this->getId());
            $gethomeschedule->setType('info');
            $gethomeschedule->setSubType('string');
            $gethomeschedule->save();

            $sethomeschedule = $this->getCmd(null, 'setschedule');
            if (!is_object($sethomeschedule)) {
                $sethomeschedule = new mullerintuitivCmd();
            }
            $sethomeschedule->setName(__('Appliquer le planning', __FILE__));
            $sethomeschedule->setLogicalId('setschedule');
            $sethomeschedule->setEqLogic_id($this->getId());
            $sethomeschedule->setType('action');
            $sethomeschedule->setSubType('select');
            $sethomeschedule->setConfiguration('listValue', $this->getListValueNameSchedules());
            $sethomeschedule->setValue($gethomeschedule->getId());
            $sethomeschedule->save();
        } else {
            $gettemp = $this->getCmd(null, 'therm_measured_temperature');
            if (!is_object($gettemp)) {
                $gettemp = new mullerintuitivCmd();
            }
            $gettemp->setName(__('Temperature', __FILE__));
            $gettemp->setLogicalId('therm_measured_temperature');
            $gettemp->setEqLogic_id($this->getId());
            $gettemp->setTemplate('dashboard', 'line');
            $gettemp->setTemplate('mobile', 'line');
            $gettemp->setIsHistorized(1);
            $gettemp->setDisplay('icon', '<i class="icon jeedom-thermometre-celcius"></i>');
            $gettemp->setGeneric_type('THERMOSTAT_TEMPERATURE');
            $gettemp->setUnite('°C');
            $gettemp->setType('info');
            $gettemp->setSubType('numeric');
            $gettemp->save();

            $getconstemp = $this->getCmd(null, 'therm_setpoint_temperature');
            if (!is_object($getconstemp)) {
                $getconstemp = new mullerintuitivCmd();
            }
            $getconstemp->setName(__('Thermostat', __FILE__));
            $getconstemp->setLogicalId('therm_setpoint_temperature');
            $getconstemp->setEqLogic_id($this->getId());
            $getconstemp->setIsVisible(0);
            $getconstemp->setGeneric_type('THERMOSTAT_SETPOINT');
            $getconstemp->setUnite('°C');
            $getconstemp->setType('info');
            $getconstemp->setSubType('numeric');
            $getconstemp->save();

            $setconstemp = $this->getCmd(null, 'setconstemp');
            if (!is_object($setconstemp)) {
                $setconstemp = new mullerintuitivCmd();
            }
            $setconstemp->setName(__('Action Consigne Temperature', __FILE__));
            $setconstemp->setLogicalId('setconstemp');
            $setconstemp->setEqLogic_id($this->getId());
            $setconstemp->setTemplate('dashboard','button');
            $setconstemp->setTemplate('mobile','button');
            $setconstemp->setGeneric_type('THERMOSTAT_SET_SETPOINT');
            $setconstemp->setType('action');
            $setconstemp->setSubType('slider');
            $setconstemp->setConfiguration('minValue',0);
            $setconstemp->setConfiguration('maxValue', 32);
            $setconstemp->setValue($getconstemp->getId());
            $setconstemp->save();

            $getmoderoom = $this->getCmd(null, 'therm_setpoint_mode');
            if (!is_object($getmoderoom)) {
                $getmoderoom = new mullerintuitivCmd();
            }
            $getmoderoom->setName(__('Mode', __FILE__));
            $getmoderoom->setLogicalId('therm_setpoint_mode');
            $getmoderoom->setEqLogic_id($this->getId());
            $getmoderoom->setGeneric_type('THERMOSTAT_MODE');
            $getmoderoom->setType('info');
            $getmoderoom->setSubType('string');
            $getmoderoom->save();

            $setmodehome = $this->getCmd(null, 'roommodehome');
            if (!is_object($setmodehome)) {
                $setmodehome = new mullerintuitivCmd();
            }
            $setmodehome->setName(__('Home', __FILE__));
            $setmodehome->setLogicalId('roommodehome');
            $setmodehome->setEqLogic_id($this->getId());
            $setmodehome->setGeneric_type('THERMOSTAT_SET_MODE');
            $setmodehome->setType('action');
            $setmodehome->setSubType('other');
            $setmodehome->setValue($getmoderoom->getId());
            $setmodehome->setDisplay('icon', '<i class="icon maison-home63"></i>');
            $setmodehome->save();

            $setmodehg = $this->getCmd(null, 'roommodefrost');
            if (!is_object($setmodehg)) {
                $setmodehg = new mullerintuitivCmd();
            }
            $setmodehg->setName(__('Hors Gel', __FILE__));
            $setmodehg->setLogicalId('roommodefrost');
            $setmodehg->setEqLogic_id($this->getId());
            $setmodehg->setGeneric_type('THERMOSTAT_SET_MODE');
            $setmodehg->setType('action');
            $setmodehg->setSubType('other');
            $setmodehg->setValue($getmoderoom->getId());
            $setmodehg->setDisplay('icon', '<i class="icon nature-snowflake"></i>');
            $setmodehg->save();

            $setmodeoff = $this->getCmd(null, 'roommodeoff');
            if (!is_object($setmodeoff)) {
                $setmodeoff = new mullerintuitivCmd();
            }
            $setmodeoff->setName(__('Off', __FILE__));
            $setmodeoff->setLogicalId('roommodeoff');
            $setmodeoff->setEqLogic_id($this->getId());
            $setmodeoff->setGeneric_type('THERMOSTAT_SET_MODE');
            $setmodeoff->setType('action');
            $setmodeoff->setSubType('other');
            $setmodeoff->setValue($getmoderoom->getId());
            $setmodeoff->setDisplay('icon', '<i class="fas fa-stop"></i>');
            $setmodeoff->save();

            $getwindow = $this->getCmd(null, 'open_window');
            if (!is_object($getwindow)) {
                $getwindow = new mullerintuitivCmd();
            }
            $getwindow->setName(__('Fenêtre', __FILE__));
            $getwindow->setLogicalId('open_window');
            $getwindow->setEqLogic_id($this->getId());
            $getwindow->setTemplate('dashboard','mullerintuitiv::windows');
            $getwindow->setTemplate('mobile','mullerintuitiv::windows');
            $getwindow->setGeneric_type('OPENING_WINDOW');
            $getwindow->setType('info');
            $getwindow->setSubType('binary');
            $getwindow->save();

            $setwindowsopen = $this->getCmd(null, 'windowsopen');
            if (!is_object($setwindowsopen)) {
                $setwindowsopen = new mullerintuitivCmd();
            }
            $setwindowsopen->setName(__('Ouverture de la fenêtre', __FILE__));
            $setwindowsopen->setLogicalId('windowsopen');
            $setwindowsopen->setEqLogic_id($this->getId());
            $setwindowsopen->setType('action');
            $setwindowsopen->setSubType('other');
            $setwindowsopen->setValue($getwindow->getId());
            $setwindowsopen->setDisplay('icon', self::ICONWINDOWSON);
            $setwindowsopen->save();

            $setwindowsclose = $this->getCmd(null, 'windowsclose');
            if (!is_object($setwindowsclose)) {
                $setwindowsclose = new mullerintuitivCmd();
            }
            $setwindowsclose->setName(__('Fermeture de la fenêtre', __FILE__));
            $setwindowsclose->setLogicalId('windowsclose');
            $setwindowsclose->setEqLogic_id($this->getId());
            $setwindowsclose->setType('action');
            $setwindowsclose->setSubType('other');
            $setwindowsclose->setValue($getwindow->getId());
            $setwindowsclose->setDisplay('icon', self::ICONWINDOWSOFF);
            $setwindowsclose->save();

            if ($this->getConfiguration('mullerintuitiv_type') === 'bathroom'){ // bathroom
                $getboost = $this->getCmd(null, 'boost');
                if (!is_object($getboost)) {
                    $getboost = new mullerintuitivCmd();
                }
                $getboost->setName(__('Boost', __FILE__));
                $getboost->setLogicalId('boost');
                $getboost->setEqLogic_id($this->getId());
                $getboost->setType('info');
                $getboost->setSubType('string');
                $getboost->save();
            }
        }

        if ($this->getIsEnable() === '1') {
            $api = mullerintuitiv::getMullerintuitivApi();
            $this->updateApiMullerIntuitiv($api,$this->getConfiguration('mullerintuitiv_id'));
        }
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function toHtml($_version = 'dashboard') {
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = jeedom::versionAlias($_version);

        $token = mullerintuitiv::getAccesToken();
        $api = mullerintuitiv::getMullerintuitivApi();
        $homeschedulesidandname = mullerintuitiv::getHomesSchedulesIdAndName($api);
        $homes = $api->getHomes($token);

        $getmoderoom = $this->getCmd(null, 'therm_setpoint_mode');
        $replace['#getmoderoom#'] = is_object($getmoderoom) ? $getmoderoom->execCmd() : '';
        $replace['#getnameroom#'] = is_object($getmoderoom) ? $getmoderoom->getName() : '';

        foreach ($homes as $home){
            $getconfighome = $api->getConfigHome($token, $home['id']);
            $thermsetpointdefaultduration = time()+(60 * $getconfighome[0]['therm_setpoint_default_duration']);
            $replace['#getdefaultduration#'] = date('H:i',$thermsetpointdefaultduration);
        }

        $setroommodehome = $this->getCmd(null, 'roommodehome');
        $replace['#setroommodehome#'] = is_object($setroommodehome) ? $setroommodehome->getId() : '';

        $setroommodefrost = $this->getCmd(null, 'roommodefrost');
        $replace['#setroommodefrost#'] = is_object($setroommodefrost) ? $setroommodefrost->getId() : '';

        $setroommodeoff = $this->getCmd(null, 'roommodeoff');
        $replace['#setroommodeoff#'] = is_object($setroommodeoff) ? $setroommodeoff->getId() : '';

        $setwindowsopen = $this->getCmd(null, 'windowsopen');
        $replace['#setwindowsopen#'] = is_object($setwindowsopen) ? $setwindowsopen->getId() : '';

        $setwindowsclose = $this->getCmd(null, 'windowsclose');
        $replace['#setwindowsclose#'] = is_object($setwindowsclose) ? $setwindowsclose->getId() : '';

        $getwindow = $this->getCmd(null, 'open_window');
        $replace['#getwindow#'] = is_object($getwindow) ? $getwindow->execCmd() : '';

        $gettemp = $this->getCmd(null, 'therm_measured_temperature');
        $replace['#gettemp#'] = is_object($gettemp) ? $gettemp->execCmd() : '';
        $replace['#gettempname#'] = '<i class=\'icon jeedom-thermometre-celcius\'></i>';
        $replace['#gettempunite#'] = is_object($gettemp) ? $gettemp->getUnite() : '';

        $getboost = $this->getCmd(null, 'boost');
        $replace['#getboost#'] = is_object($getboost) ? $getboost->execCmd() : '';
        $replace['#getboostname#'] = is_object($getboost) ? $getboost->getName() : '';

        $replacethermostat = [];

        $setconstemp = $this->getCmd(null, 'setconstemp');
        $replacethermostat['#id#'] = is_object($setconstemp) ? $setconstemp->getId() : '';
        $replacethermostat['#maxValue#'] = is_object($setconstemp) ? $setconstemp->getConfiguration('maxValue') : '';
        $replacethermostat['#minValue#'] = is_object($setconstemp) ? $setconstemp->getConfiguration('minValue') : '';

        $gettemp = $this->getCmd(null, 'therm_setpoint_temperature');
        $replacethermostat['#name_display#'] = is_object($gettemp) ? $gettemp->getName() : '';
        $replacethermostat['#uid#'] = is_object($gettemp) ? $gettemp->getId() : '';
        $replacethermostat['#state#'] = is_object($gettemp) ? $gettemp->execCmd() : '';
        $replacethermostat['#unite#'] = is_object($gettemp) ? $gettemp->getUnite() : '';

        $replace['#thermostat#'] = template_replace($replacethermostat, getTemplate('core', $version, 'thermostat', __CLASS__));

        $replacewindows = [];

        $getwindow = $this->getCmd(null, 'open_window');
        $replacewindows['#id#'] = is_object($getwindow) ? $getwindow->getId() : '';
        $replacewindows['#name_display#'] = is_object($getwindow) ? $getwindow->getName() : '';
        $replacewindows['#state#'] = is_object($getwindow) ? $getwindow->execCmd() : '';
        $replacewindows['#_icon_on_#'] = self::ICONWINDOWSON;
        $replacewindows['#_icon_off_#'] = self::ICONWINDOWSOFF;

        $replace['#windows#'] = template_replace($replacewindows, getTemplate('core', $version, 'windows', __CLASS__));

        $getmodehome = $this->getCmd(null, 'therm_mode');
        $replace['#gethome#'] = is_object($getmodehome) ? $getmodehome->execCmd() : '';
        $replace['#gethomename#'] = is_object($getmodehome) ? $getmodehome->getName() : '';

        $setmodeschedule = $this->getCmd(null, 'homemodehome');
        $replace['#setmodeschedule#'] = is_object($setmodeschedule) ? $setmodeschedule->getId() : '';

        $setmodehg = $this->getCmd(null, 'homemodefrost');
        $replace['#setmodehg#'] = is_object($setmodehg) ? $setmodehg->getId() : '';

        $setmodeaway = $this->getCmd(null, 'homemodeaway');
        $replace['#setmodeaway#'] = is_object($setmodeaway) ? $setmodeaway->getId() : '';

        $replaceschedule = [];

        $sethomeschedule = $this->getCmd(null, 'setschedule');
        $replaceschedule['#id#'] = is_object($sethomeschedule) ? $sethomeschedule->getId() : '';
        $replaceschedule['#name_display#'] = is_object($sethomeschedule) ? $sethomeschedule->getName() : '';

        $listvalue = is_object($sethomeschedule) ? $sethomeschedule->getConfiguration('listValue') : '';
        $listvalue = explode(';',$listvalue);
        $listschedule = '';
        $nameselected = '';
        $count = 0;
        foreach ($homeschedulesidandname as $valuehomeschedule) {
            if (in_array($valuehomeschedule['selected'],$homeschedulesidandname)){
                $nameselected = $valuehomeschedule['name'];
            }
        }

        foreach ($listvalue as $valuename){
            if ($nameselected === substr($valuename, 2)){
                $listschedule .= '<option selected="selected" value="'.$count++.'">'.substr($valuename, 2).'</option>';
            }else{
                $listschedule .= '<option value="'.$count++.'">'.substr($valuename, 2).'</option>';
            }
        }
        $replaceschedule['#listValue#'] = $listschedule;

        $gethomeschedule = $this->getCmd(null, 'getschedule');
        $replaceschedule['#uid#'] = is_object($gethomeschedule) ? $gethomeschedule->getId() : '';

        $replace['#schedule#'] = template_replace($replaceschedule, getTemplate('core', $version, 'selectschedule', __CLASS__));

        if (preg_match('/home/', $this->getLogicalId())){
            $html = $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'home', 'mullerintuitiv')));
        } else {
            $html = $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'mullerintuitiv', 'mullerintuitiv')));
        }

        cache::set('widgetHtml' . $_version . $this->getId(), $html);
        return $html;
    }

    /*     * **********************Getteur Setteur*************************** */
}

class mullerintuitivCmd extends cmd {
    /*     * *************************Attributs****************************** */
    public static $_widgetPossibility = ['custom' => false];
    
    /*     * ***********************Methode static*************************** */

    /*     * *********************Methode d'instance************************* */

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function execute($_options = []) {
        $mullerintuitivid = $this->getEqLogic()->getConfiguration('mullerintuitiv_id');
        $token = mullerintuitiv::getAccesToken();
        $api = mullerintuitiv::getMullerintuitivApi();
        $homes = $api->getHomes($token);

        foreach ($homes as $home){
            $rooms = $api->getRooms($token, $home['id']);
            $getconfighome = $api->getConfigHome($token, $home['id']);
            $thermsetpointdefaultduration = time()+(60 * $getconfighome[0]['therm_setpoint_default_duration']);

            if ($this->getLogicalId() === 'homemodehome' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                $api->setModeHome('schedule', $token, $home['id']);
            }

            if ($this->getLogicalId() === 'homemodefrost' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                $api->setModeHome(mullerintuitivApi::MODE['HG'], $token, $home['id']);
            }

            if ($this->getLogicalId() === 'homemodeaway' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                $api->setModeHome(mullerintuitivApi::MODE['ABSENT'], $token, $home['id']);
            }

            if ($this->getLogicalId() === 'setschedule' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                $selectvalue = (int)$_options['select'];
                $listvalue = $this->getConfiguration('listValue');
                $listvalue = explode(';',$listvalue);

                $count = 0;
                foreach ($listvalue as $valuename){
                    if ($selectvalue === $count++){
                        $valuename = substr($valuename, 2);
                        $homeschedulesidandname = mullerintuitiv::getHomesSchedulesIdAndName($api);

                        foreach ($homeschedulesidandname as $valuehomeschedule){
                            if ($valuename === $valuehomeschedule['name']){
                                $id = $valuehomeschedule['id'];
                                $api->setSwitchHomeSchedule($id, $token, $home['id']);
                            }
                        }
                    }
                }
            }

            foreach ($rooms as $value){
                if ($this->getLogicalId() === 'roommodehome' && $mullerintuitivid === $value['id']){
                    $api->setRoomMode($mullerintuitivid, $token, mullerintuitivApi::MODE['HOME'], $home['id']);
                }

                if ($this->getLogicalId() === 'roommodefrost' && $mullerintuitivid === $value['id']){
                    $api->setRoomMode($mullerintuitivid, $token, mullerintuitivApi::MODE['HG'], $home['id']);
                }

                if ($this->getLogicalId() === 'roommodeoff' && $mullerintuitivid === $value['id']){
                    $api->setRoomMode($mullerintuitivid, $token, mullerintuitivApi::MODE['OFF'], $home['id']);
                }

                if ($this->getLogicalId() === 'setconstemp' && $mullerintuitivid === $value['id']){
                    $api->setRoomTemperature($mullerintuitivid,(float)$_options['slider'],$token, $thermsetpointdefaultduration, $home['id']);
                }

                if ($this->getLogicalId() === 'windowsopen' && $mullerintuitivid === $value['id']){
                    $api->setRoomWindows($mullerintuitivid, true, $token, $home['id']);
                    sleep(4);
                }

                if ($this->getLogicalId() === 'windowsclose' && $mullerintuitivid === $value['id']){
                    $api->setRoomWindows($mullerintuitivid, false, $token, $home['id']);
                    sleep(4);
                }
            }
        }

        sleep(1);
        foreach (mullerintuitiv::byType('mullerintuitiv') as $eqLogic) {
                $eqLogic->updateApiMullerIntuitiv($api,$eqLogic->getConfiguration('mullerintuitiv_id'));
        }
    }

    /*     * **********************Getteur Setteur*************************** */


}


