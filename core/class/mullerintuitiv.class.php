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
use Psr\Http\Message\ResponseInterface;

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../core/class/token.class.php';
require_once __DIR__ . '/../../core/class/homes.class.php';
require_once __DIR__ . '/../../core/class/measure.class.php';
require_once __DIR__ . '/../../core/class/rooms.class.php';

class mullerintuitiv extends eqLogic {

    /*     * *************************Attributs****************************** */
    public static $_widgetPossibility = ['custom' => true, 'custom::layout' => false];

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

    /**
     * @throws GuzzleException
     */
    public static function getToken(): string
    {
        $token = new token();

        return $token->getAccesToken();
    }

    /**
     * @throws GuzzleException
     */
    public static function getHomes(){
        $token = mullerintuitiv::getToken();
        $gethomes = new homes();

        return $gethomes->getHomes($token);
    }

    /**
     * @throws GuzzleException
     */
    public static function getHomeSchedulesAll(): array
    {
        $token = mullerintuitiv::getToken();
        $gethomes = new homes();

        return $gethomes->getHomeSchedulesAll($token);
    }

    /**
     * @throws GuzzleException
     */
    public static function getRoomsIdAndName(): array
    {
        $token = mullerintuitiv::getToken();
        $gethomes = new homes();

        return $gethomes->getRoomsIdAndName($token);
    }

    /**
     * @throws GuzzleException
     */
    public static function getRoomMeasure(
        string $dateend,
        string $datebegin,
        string $roomid,
        string $bridge,
        string $homeid
    ): array
    {
        $token = mullerintuitiv::getToken();
        $measure = new measure();

        return $measure->getRoomMeasures(
            $dateend,
            $datebegin,
            $roomid,
            $bridge,
            $homeid,
            $token
        );
    }

    /**
     * @throws GuzzleException
     */
    public static function getHomeMeasure(
        string $modulesid,
        string $dateend,
        string $datebegin,
        string $homeid
    ): array
    {
        $token = mullerintuitiv::getToken();
        $measure = new measure();

        return $measure->getHomeMeasures(
            $modulesid,
            $dateend,
            $datebegin,
            $homeid,
            $token
        );
    }

    /**
     * @throws GuzzleException
     */
    public static function getRooms(string $homeid){
        $token = mullerintuitiv::getToken();
        $getrooms = new rooms();

        return $getrooms->getRooms($token,$homeid);
    }

    /**
     * @throws GuzzleException
     */
    public static function setRoomMode(
        string $roomid,
        string $thermsetpointmode,
        string $homeid
    ): ResponseInterface
    {
        $token = mullerintuitiv::getToken();
        $getrooms = new rooms();

        return $getrooms->setRoomMode($roomid, $token, $thermsetpointmode, $homeid);
    }

    /**
     * @throws GuzzleException
     */
    public static function setRoomTemperature(
        string $roomid,
        float $roomtemp,
        int $thermsetpointendtime,
        string $homeid
    ): ResponseInterface
    {
        $token = mullerintuitiv::getToken();
        $getrooms = new rooms();

        return $getrooms->setRoomTemperature($roomid, $roomtemp, $token, $thermsetpointendtime, $homeid);
    }

    /**
     * @throws GuzzleException
     */
    public static function setRoomWindows(
        string $roomid,
        bool $windows,
        string $homeid
    ): ResponseInterface
    {
        $token = mullerintuitiv::getToken();
        $getrooms = new rooms();

        return $getrooms->setRoomWindows($roomid, $windows, $token, $homeid);
    }

    /**
     * @throws GuzzleException
     */
    public static function setModeHome(string $modehome, string $homeid): ResponseInterface
    {
        $token = mullerintuitiv::getToken();
        $gethomes = new homes();

        return $gethomes->setModeHome($modehome, $token, $homeid);
    }

    /**
     * @throws GuzzleException
     */
    public static function getConfigHome(string $homeid): array
    {
        $token = mullerintuitiv::getToken();
        $gethomes = new homes();

        return $gethomes->getConfigHome($token, $homeid);
    }

    /**
     * @throws GuzzleException
     */
    public static function setSwitchHomeSchedule(string $scheduleid, string $homeid): ResponseInterface
    {
        $token = mullerintuitiv::getToken();
        $gethomes = new homes();

        return $gethomes->setSwitchHomeSchedule($scheduleid, $token, $homeid);
    }

    /**
     * @throws GuzzleException
     */
    public static function getHomesSchedulesIdAndName(): array
    {
        $gethomeschedulesall = mullerintuitiv::getHomeSchedulesAll();

        $gethomescheduleidandname = [];
        foreach ($gethomeschedulesall as $gethomeschedule){
            if (in_array($gethomeschedule['selected'],$gethomeschedulesall)){
                $gethomescheduleidandname[] = [
                    'name' => $gethomeschedule['name'],
                    'id' => $gethomeschedule['id'],
                    'selected' => $gethomeschedule['selected']
                ];
            } else {
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
     * @throws Exception
     */
    public static function getSynMods(){
        $homes = mullerintuitiv::getHomes();
        $roomsidandname = mullerintuitiv::getRoomsIdAndName();

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
                $eqLogic->updateApiMullerIntuitiv($eqLogic->getConfiguration('mullerintuitiv_id'));
            }
        }
    }

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

    /*     * *********************Méthodes d'instance************************* */

    /**
     * @throws GuzzleException
     */
    public function getListValueNameSchedules(): string
    {
        $homeschedulesidandname = mullerintuitiv::getHomesSchedulesIdAndName();

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
    public function updateApiMullerIntuitiv(string $mullerintuitivid){
        $homes = mullerintuitiv::getHomes();
        log::add('mullerintuitiv','debug',json_encode($homes));

        foreach ($homes as $home){
            $roomsupdate = mullerintuitiv::getRooms($home['id']);
            log::add('mullerintuitiv','debug',json_encode($roomsupdate));

            if (strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                $homeschedulesidandname = mullerintuitiv::getHomesSchedulesIdAndName();

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
            $this->updateApiMullerIntuitiv($this->getConfiguration('mullerintuitiv_id'));
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

        $homeschedulesidandname = mullerintuitiv::getHomesSchedulesIdAndName();
        $homes = mullerintuitiv::getHomes();

        $getmoderoom = $this->getCmd(null, 'therm_setpoint_mode');
        $replace['#getmoderoom#'] = is_object($getmoderoom) ? $getmoderoom->execCmd() : '';
        $replace['#getnameroom#'] = is_object($getmoderoom) ? $getmoderoom->getName() : '';

        foreach ($homes as $home){
            $getconfighome = mullerintuitiv::getConfigHome($home['id']);
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

        $replacetempandboost = [];

        $gettemp = $this->getCmd(null, 'therm_measured_temperature');
        $replacetempandboost['#id#'] = is_object($gettemp) ? $gettemp->getId() : '';
        $getstate = is_object($gettemp) ? $gettemp->execCmd() : '';
        $gettempname = '<i class=\'icon jeedom-thermometre-celcius\'></i>';
        $gettempunite = is_object($gettemp) ? $gettemp->getUnite() : '';

        $getboost = $this->getCmd(null, 'boost');
        $replacetempandboost['#state#'] = is_object($getboost) ? $getboost->execCmd() : '';

        $replacetempandboost['#temp#'] = '<span class=\'cmdName\'>'.$gettempname.' <strong class=\'state\'>'.$getstate.' '.$gettempunite.'</strong></span>';
        $replacetempandboost['#boost#'] = '<span style=\'color: white; font-size: 15px; border-radius: 5px; background-color: #fdac51;\'><i class=\'fas fa-hand-paper\'></i> BOOST</span>';

        $replace['#tempandboost#'] = template_replace($replacetempandboost, getTemplate('core', $version, 'tempandboost', __CLASS__));

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
            } else {
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
        $homes = mullerintuitiv::getHomes();

        foreach ($homes as $home){
            $rooms = mullerintuitiv::getRooms($home['id']);
            $getconfighome = mullerintuitiv::getConfigHome($home['id']);
            $thermsetpointdefaultduration = time()+(60 * $getconfighome[0]['therm_setpoint_default_duration']);

            if ($this->getLogicalId() === 'homemodehome' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                mullerintuitiv::setModeHome('schedule', $home['id']);
            }

            if ($this->getLogicalId() === 'homemodefrost' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                mullerintuitiv::setModeHome(mullerintuitivApi::MODE['HG'], $home['id']);
            }

            if ($this->getLogicalId() === 'homemodeaway' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                mullerintuitiv::setModeHome(mullerintuitivApi::MODE['ABSENT'], $home['id']);
            }

            if ($this->getLogicalId() === 'setschedule' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                $selectvalue = (int)$_options['select'];
                $listvalue = $this->getConfiguration('listValue');
                $listvalue = explode(';',$listvalue);

                $count = 0;
                foreach ($listvalue as $valuename){
                    if ($selectvalue === $count++){
                        $valuename = substr($valuename, 2);
                        $homeschedulesidandname = mullerintuitiv::getHomesSchedulesIdAndName();

                        foreach ($homeschedulesidandname as $valuehomeschedule){
                            if ($valuename === $valuehomeschedule['name']){
                                $id = $valuehomeschedule['id'];
                                mullerintuitiv::setSwitchHomeSchedule($id, $home['id']);
                            }
                        }
                    }
                }
            }

            foreach ($rooms as $value){
                if ($this->getLogicalId() === 'roommodehome' && $mullerintuitivid === $value['id']){
                    mullerintuitiv::setRoomMode($mullerintuitivid, mullerintuitivApi::MODE['HOME'], $home['id']);
                }

                if ($this->getLogicalId() === 'roommodefrost' && $mullerintuitivid === $value['id']){
                    mullerintuitiv::setRoomMode($mullerintuitivid, mullerintuitivApi::MODE['HG'], $home['id']);
                }

                if ($this->getLogicalId() === 'roommodeoff' && $mullerintuitivid === $value['id']){
                    mullerintuitiv::setRoomMode($mullerintuitivid, mullerintuitivApi::MODE['OFF'], $home['id']);
                }

                if ($this->getLogicalId() === 'setconstemp' && $mullerintuitivid === $value['id']){
                    mullerintuitiv::setRoomTemperature($mullerintuitivid,(float)$_options['slider'], $thermsetpointdefaultduration, $home['id']);
                }

                if ($this->getLogicalId() === 'windowsopen' && $mullerintuitivid === $value['id']){
                    mullerintuitiv::setRoomWindows($mullerintuitivid, true, $home['id']);
                    sleep(4);
                }

                if ($this->getLogicalId() === 'windowsclose' && $mullerintuitivid === $value['id']){
                    mullerintuitiv::setRoomWindows($mullerintuitivid, false, $home['id']);
                    sleep(4);
                }
            }
        }

        sleep(1);
        foreach (mullerintuitiv::byType('mullerintuitiv') as $eqLogic) {
                $eqLogic->updateApiMullerIntuitiv($eqLogic->getConfiguration('mullerintuitiv_id'));
        }
    }

    /*     * **********************Getteur Setteur*************************** */


}


