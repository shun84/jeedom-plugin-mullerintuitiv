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
require_once __DIR__  . '/../../core/php/mullerintuitiv.inc.php';

class mullerintuitiv extends eqLogic {

    /*     * *************************Attributs****************************** */
    public static array $_widgetPossibility = [
        'custom' => true,
        'custom::layout' => true
    ];

    protected const ICONWINDOWSON = '<i class="icon jeedom-fenetre-ouverte"></i>';
    protected const ICONWINDOWSOFF = '<i class="icon jeedom-fenetre-ferme"></i>';

    /*     * ***********************Methode static*************************** */

    public static function templateWidget(): array
    {
        $return = ['info' => ['string' => []]];
        $return['info']['binary']['windows'] = [
            'template' => 'tmplicon',
            'replace' => [
                '#_icon_on_#' => self::ICONWINDOWSON,
                '#_icon_off_#' => self::ICONWINDOWSOFF
            ]
        ];

        $return['info']['string']['boost'] = [
            'template' => 'tmplmultistate',
            'test' => [
                [
                    'operation' => '#value# == \'in-progress\'',
                    'state_light' => '<span style=\'color: white; font-size: 15px; border-radius: 5px; background-color: #fdac51;\'><i class=\'fas fa-hand-paper\'></i> BOOST</span>',
                    'state_dark' => '<span style=\'color: white; font-size: 15px; border-radius: 5px; background-color: #fdac51;\'><i class=\'fas fa-hand-paper\'></i> BOOST</span>'
                ],
                [
                    'operation' => '#value# != \'in-progress\'',
                    'state_light' => '<span style=\'color: white; font-size: 15px; border-radius: 5px;\'><i class=\'fas fa-hand-paper\'></i> NO BOOST</span>',
                    'state_dark' => '<span style=\'color: white; font-size: 15px; border-radius: 5px;\'><i class=\'fas fa-hand-paper\'></i> NO BOOST</span>'
                ]
            ]
        ];

        return $return;
    }

    /**
     * @throws Exception
     */
    public static function getSynMods(): void
    {
        $homes = homes::getHomes();
        $roomsidandname = homes::getRoomsIdAndName();

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

    public static function cron10(): void
    {
        foreach (self::byType('mullerintuitiv') as $eqLogic) {
            $eqLogic->updateApiMullerIntuitiv($eqLogic->getConfiguration('mullerintuitiv_id'));
        }
    }

    public static function replaceMode(string $mode)
    {
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
     * @throws Exception
     */
    public function getChauffe(): string
    {
        $getschedules = schedules::getSchedules();

        $jour = getdate();
        $semaine = ["dimanche","lundi","mardi","mercredi","jeudi",
            "vendredi","samedi"];

        $getdatehours = date('H:i');
        $getheuredays = [];

        foreach ($getschedules['planningall'] as $days){
            foreach ($days[$semaine[$jour['wday']]] as $day){
                foreach ($day['plage'] as $plage){
                    $getheuredays[] = ['date' => $plage['date'], 'zone' => $plage['zone']];
                }
            }

            $j = 1;
            foreach ($getheuredays as $getheureday){
                if ($getdatehours >= '00:00' && $getdatehours <= $getheuredays[1]['date']){
                    foreach ($getschedules['planningall']['zones'] as $zone){
                        if ($zone['id'] === $getheuredays[0]['zone']){
                            return $zone['name'].' -> '.$getheuredays[1]['date'];
                        }
                    }
                }

                if ($getdatehours >= $getheureday['date'] && $getdatehours <= $getheuredays[$j]['date']){
                    foreach ($getschedules['planningall']['zones'] as $zone){
                        if ($zone['id'] === $getheureday['zone']){
                            return $zone['name'].' -> '.$getheuredays[$j]['date'];
                        }
                    }
                }

                if ($getheuredays[$j]['date'] === null && $getdatehours <= '23:59'){
                    foreach ($getschedules['planningall']['zones'] as $zone){
                        $lastplageday = end($getheuredays);
                        $lastplagezone = prev($getheuredays);
                        if ($zone['id'] === $lastplagezone['zone']){
                            return $zone['name'].' -> '.$lastplageday['date'];
                        }
                    }
                }
                $j++;
            }
        }
        return 'En cours';
    }

    /**
     * @throws Exception
     */
    public function getListValueNameSchedules(): string
    {
        $homeschedulesidandname = schedules::getHomesSchedulesIdAndName();

        $getlistvaluenameschedules = [];
        $count = 0;
        foreach ($homeschedulesidandname as $valuehomeschedule){
            $getlistvaluenameschedules[] = $count++.'|'.$valuehomeschedule['name'];
        }

        return implode(";",$getlistvaluenameschedules);
    }

    /**
     * @throws Exception
     */
    public function updateApiMullerIntuitiv(string $mullerintuitivid): void
    {
        $homes = homes::getHomes();
        log::add('mullerintuitiv','debug',json_encode($homes));

        foreach ($homes as $home){
            $roomsupdate = rooms::getRooms($home['id']);
            log::add('mullerintuitiv','debug',json_encode($roomsupdate));

            if (strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                $homeschedulesidandname = schedules::getHomesSchedulesIdAndName();

                $this->checkAndUpdateCmd('therm_mode', $this->replaceMode($home['therm_mode']));

                foreach ($homeschedulesidandname as $valuehomeschedule){
                    if (isset($valuehomeschedule['selected']) === true ){
                        $this->checkAndUpdateCmd('getschedule',$valuehomeschedule['name']);
                    }
                }

                $this->checkAndUpdateCmd('getchauffe', $this->getChauffe());
            }

            foreach ($roomsupdate as $valueupdate){
                if ($mullerintuitivid === $valueupdate['id']){
                    $mullerintuitivmoduleroom = eqLogic::byLogicalId( 'mullerintuitiv_'.$mullerintuitivid, 'mullerintuitiv');
                    $mullerintuitivbridgeroom = $mullerintuitivmoduleroom->getConfiguration('mullerintuitiv_therm_relay');

                    $getroommeasure = measure::getRoomMeasures(
                        strtotime(date('Y-m-d') . ' 23:59:59'),
                        strtotime(date('Y-m-d')  . ' 00:00:00'),
                        $mullerintuitivid,
                        $mullerintuitivbridgeroom,
                        $home['id']
                    );

                    $this->checkAndUpdateCmd('open_window', $valueupdate['open_window']);
                    $this->checkAndUpdateCmd('therm_measured_temperature', $valueupdate['therm_measured_temperature']);
                    $this->checkAndUpdateCmd('therm_setpoint_mode', $this->replaceMode($valueupdate['therm_setpoint_mode']));
                    $this->checkAndUpdateCmd('therm_setpoint_temperature', $valueupdate['therm_setpoint_temperature']);
                    $this->checkAndUpdateCmd('boost', $valueupdate['boost_status']);
                    $this->checkAndUpdateCmd('energy', isset($getroommeasure['day'][0][1]));
                }
            }
        }

        $this->refreshWidget();
    }

    /**
     * @throws Exception
     */
    public function postSave(): void
    {
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
            $gethomeschedule->setIsVisible(0);
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

            $getchauffe = $this->getCmd(null, 'getchauffe');
            if (!is_object($getchauffe)) {
                $getchauffe  = new mullerintuitivCmd();
            }
            $getchauffe->setName(__('Prochaine chauffe', __FILE__));
            $getchauffe->setLogicalId('getchauffe');
            $getchauffe->setEqLogic_id($this->getId());
            $getchauffe->setType('info');
            $getchauffe->setSubType('string');
            $getchauffe->save();
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
                $getboost->setTemplate('dashboard','mullerintuitiv::boost');
                $getboost->setTemplate('mobile','mullerintuitiv::boost');
                $getboost->setEqLogic_id($this->getId());
                $getboost->setType('info');
                $getboost->setSubType('string');
                $getboost->save();
            }

            $getenergy = $this->getCmd(null, 'energy');
            if (!is_object($getenergy)) {
                $getenergy = new mullerintuitivCmd();
            }
            $getenergy->setName(__('Consommation', __FILE__));
            $getenergy->setLogicalId('energy');
            $getenergy->setEqLogic_id($this->getId());
            $getenergy->setGeneric_type('CONSUMPTION');
            $getenergy->setTemplate('dashboard', 'line');
            $getenergy->setIsHistorized(1);
            $getenergy->setType('info');
            $getenergy->setSubType('numeric');
            $getenergy->setUnite('w');
            $getenergy->save();
        }
    }
}

class mullerintuitivCmd extends cmd {
    /*     * *************************Attributs****************************** */
//    public static $_widgetPossibility = ['custom' => false];

    /**
     * @throws Exception
     */
    public function execute($_options = []): void
    {
        $mullerintuitivid = $this->getEqLogic()->getConfiguration('mullerintuitiv_id');
        $homes = homes::getHomes();

        foreach ($homes as $home){
            $mullerintuitivhome = eqLogic::byLogicalId( 'mullerintuitiv_home_'.$home['id'], 'mullerintuitiv');
            $rooms = rooms::getRooms($home['id']);
            $getconfighome = homes::getConfigHome($home['id']);
            $thermsetpointdefaultduration = time()+(60 * $getconfighome[0]['therm_setpoint_default_duration']);

            if ($this->getLogicalId() === 'homemodehome' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                homes::setModeHome('schedule', $home['id']);
                $mullerintuitivhome->setDisplay('parameters',['style' => ''])->save();
                foreach ($rooms as $room){
                    $mullerintuitivmodule = eqLogic::byLogicalId( 'mullerintuitiv_'.$room['id'], 'mullerintuitiv');
                    $mullerintuitivmodule->setDisplay('parameters',['style' => ''])->save();
                }
            }

            if ($this->getLogicalId() === 'homemodefrost' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                homes::setModeHome(mullerintuitivApi::MODE['HG'], $home['id']);
                $mullerintuitivhome->setDisplay('parameters',['style' => 'background-color: #505050 !important'])->save();
                foreach ($rooms as $room){
                    $mullerintuitivmodule = eqLogic::byLogicalId( 'mullerintuitiv_'.$room['id'], 'mullerintuitiv');
                    $mullerintuitivmodule->setDisplay('parameters',['style' => 'background-color: #505050 !important'])->save();
                }
            }

            if ($this->getLogicalId() === 'homemodeaway' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                homes::setModeHome(mullerintuitivApi::MODE['ABSENT'], $home['id']);
                $mullerintuitivhome->setDisplay('parameters',['style' => 'background-color: #4155a3 !important'])->save();
                foreach ($rooms as $room){
                    $mullerintuitivmodule = eqLogic::byLogicalId( 'mullerintuitiv_'.$room['id'], 'mullerintuitiv');
                    $mullerintuitivmodule->setDisplay('parameters',['style' => 'background-color: #4155a3 !important'])->save();
                }
            }

            if ($this->getLogicalId() === 'setschedule' && strlen($mullerintuitivid) > 10 && $mullerintuitivid === $home['id']){
                $selectvalue = (int)$_options['select'];
                $listvalue = $this->getConfiguration('listValue');
                $listvalue = explode(';',$listvalue);

                $count = 0;
                foreach ($listvalue as $valuename){
                    if ($selectvalue === $count++){
                        $valuename = substr($valuename, 2);
                        $homeschedulesidandname = schedules::getHomesSchedulesIdAndName();

                        foreach ($homeschedulesidandname as $valuehomeschedule){
                            if ($valuename === $valuehomeschedule['name']){
                                $id = $valuehomeschedule['id'];
                                homes::setSwitchHomeSchedule($id, $home['id']);
                            }
                        }
                    }
                }
            }

            foreach ($rooms as $room){
                $mullerintuitivmodule = eqLogic::byLogicalId( 'mullerintuitiv_'.$room['id'], 'mullerintuitiv');
                if ($this->getLogicalId() === 'roommodehome' && $mullerintuitivid === $room['id']){
                    rooms::setRoomMode($mullerintuitivid, mullerintuitivApi::MODE['HOME'], $home['id']);
                    $mullerintuitivmodule->setDisplay('parameters',['style' => ''])->save();
                }

                if ($this->getLogicalId() === 'roommodefrost' && $mullerintuitivid === $room['id']){
                    rooms::setRoomMode($mullerintuitivid, mullerintuitivApi::MODE['HG'], $home['id']);
                    $mullerintuitivmodule->setDisplay('parameters',['style' => 'background-color: #505050 !important'])->save();
                }

                if ($this->getLogicalId() === 'roommodeoff' && $mullerintuitivid === $room['id']){
                    rooms::setRoomMode($mullerintuitivid, mullerintuitivApi::MODE['OFF'], $home['id']);
                    $mullerintuitivmodule->setDisplay('parameters',['style' => 'background-color: #a34141 !important'])->save();
                }

                if ($this->getLogicalId() === 'setconstemp' && $mullerintuitivid === $room['id']){
                    rooms::setRoomTemperature($mullerintuitivid,(float)$_options['slider'], $thermsetpointdefaultduration, $home['id']);
                    $mullerintuitivmodule->setDisplay('parameters',['style' => 'background-color: #ffab53 !important'])->save();
                }

                if ($this->getLogicalId() === 'windowsopen' && $mullerintuitivid === $room['id']){
                    rooms::setRoomWindows($mullerintuitivid, true, $home['id']);
                    sleep(4);
                }

                if ($this->getLogicalId() === 'windowsclose' && $mullerintuitivid === $room['id']){
                    rooms::setRoomWindows($mullerintuitivid, false, $home['id']);
                    sleep(4);
                }
            }
        }

        sleep(1);
        foreach (mullerintuitiv::byType('mullerintuitiv') as $eqLogic) {
                $eqLogic->updateApiMullerIntuitiv($eqLogic->getConfiguration('mullerintuitiv_id'));
        }
    }
}