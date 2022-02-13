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
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../core/api/MullerIntuitivApi.php';

class MullerIntuitiv extends eqLogic {
    /*     * *************************Attributs****************************** */
    public static $_widgetPossibility = array('custom' => true, 'custom::layout' => false);

    /*     * ***********************Methode static*************************** */

    public static function templateWidget(): array
    {
        $return = ['info' => ['binary' => []]];
        $return['info']['binary']['windows'] = [
            'template' => 'tmplicon',
            'replace' => [
                '#_icon_on_#' => '<i class=\'icon jeedom-fenetre-ouverte\'></i>',
                '#_icon_off_#' => '<i class=\'icon jeedom-fenetre-ferme\'></i>'
            ]
        ];

        return $return;
    }

    public static function getSession(): MullerIntuitivApi
    {
        $username = config::byKey('login','MullerIntuitiv');
        $password = config::byKey('mdp','MullerIntuitiv');

        return new MullerIntuitivApi($username,$password);
    }

    public static function getModeHome(){
        $api = MullerIntuitiv::getSession();
        return $api->getModeHome();
    }

    public static function getRooms(){
        $api = MullerIntuitiv::getSession();
        return $api->getRooms();
    }

    public static function getHomeName(){
        $api = MullerIntuitiv::getSession();
        return $api->getHomeName();
    }

    public static function getRoomsIdAndName(): array
    {
        $api = MullerIntuitiv::getSession();
        return $api->getRoomsIdAndName();
    }

    /**
     * @throws Exception
     */
    public static function getSynMods(){
        $roomsidandname = MullerIntuitiv::getRoomsIdAndName();
        $homename = MullerIntuitiv::getHomeName();

        $mullerintuitivhome = eqLogic::byLogicalId( 'MullerIntuitiv_home', 'MullerIntuitiv', $_multiple = false);
        if (!is_object($mullerintuitivhome)) {
            $mullerintuitivhome = new MullerIntuitiv();
            $mullerintuitivhome->setName($homename);
            $mullerintuitivhome->setLogicalId('MullerIntuitiv_home');
            $mullerintuitivhome->setEqType_name('MullerIntuitiv');
            $mullerintuitivhome->setIsVisible(1);
            $mullerintuitivhome->setIsEnable(1);
            $mullerintuitivhome->setCategory('heating', 1);
        }
        $mullerintuitivhome->save();

        foreach ($roomsidandname as $room){
            $mullerintuitivmodule = eqLogic::byLogicalId( 'MullerIntuitiv_'.$room['id'], 'MullerIntuitiv', $_multiple = false);
            if (!is_object($mullerintuitivmodule)) {
                $mullerintuitivmodule = new MullerIntuitiv();
                $mullerintuitivmodule->setName($room['name']);
                $mullerintuitivmodule->setLogicalId('MullerIntuitiv_'.$room['id']);
                $mullerintuitivmodule->setEqType_name('MullerIntuitiv');
            }
            $mullerintuitivmodule->setConfiguration('mullerintuitiv_id',$room['id']);
            $mullerintuitivmodule->setConfiguration('mullerintuitiv_type',$room['type']);
            $mullerintuitivmodule->setIsVisible(1);
            $mullerintuitivmodule->setIsEnable(1);
            $mullerintuitivmodule->setCategory('heating', 1);
            $mullerintuitivmodule->save();
        }
    }

    public static function cron() {
        foreach (oklyn::byType('MullerIntuitiv') as $eqLogic) {
            if ($eqLogic->getIsEnable() == 1) {
                $eqLogic->updateApiMullerIntuitiv($eqLogic->getConfiguration('mullerintuitiv_id'));
            }
        }
    }

    /*     * *********************Méthodes d'instance************************* */
    public function replaceMode(string $mode){
        if ($mode == 'schedule' || $mode == 'home'){
             return str_replace($mode, 'Home',$mode);
        }elseif ($mode == 'hg'){
            return str_replace($mode, 'Hors Gel',$mode);
        }elseif ($mode == 'away'){
            return str_replace($mode, 'Absent',$mode);
        }

        return '';
    }

    public function updateApiMullerIntuitiv(string $mullerintuitivid){
        $replacemoderoom = '';
        $rooms = $this->getRooms();
        $modehome = $this->getModeHome();

        $this->checkAndUpdateCmd('therm_mode', $this->replaceMode($modehome));

        foreach ($rooms as $value){
           if ($mullerintuitivid == $value['id']){
               $moderoom = $value['therm_setpoint_mode'];

               if ($modehome == 'away'){
                   $replacemoderoom = str_replace($moderoom, 'Absent',$moderoom);
               }elseif ($moderoom == 'hg' || $moderoom == 'home'){
                   $replacemoderoom = $this->replaceMode($moderoom);
               }

               $this->checkAndUpdateCmd('open_window', $value['open_window']);
               $this->checkAndUpdateCmd('therm_measured_temperature', $value['therm_measured_temperature']);
               $this->checkAndUpdateCmd('therm_setpoint_mode', $replacemoderoom);
               $this->checkAndUpdateCmd('therm_setpoint_temperature', $value['therm_setpoint_temperature']);
           }
        }

        $this->refreshWidget();
    }

    public function preSave() {
        if ($this->getLogicalId() != 'MullerIntuitiv_home'){
            $this->setDisplay("width","192px");
            $this->setDisplay("height","252px");
        }
    }

    /**
     * @throws Exception
     */
    public function postSave() {
        if ($this->getLogicalId() == 'MullerIntuitiv_home'){
            $getmodehome = $this->getCmd(null, 'therm_mode');
            if (!is_object($getmodehome)) {
                $getmodehome = new MullerIntuitivCmd();
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
                $setmodeschedule = new MullerIntuitivCmd();
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
                $setmodehg = new MullerIntuitivCmd();
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
                $setmodeaway = new MullerIntuitivCmd();
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
        }else{
            $gettemp = $this->getCmd(null, 'therm_measured_temperature');
            if (!is_object($gettemp)) {
                $gettemp = new MullerIntuitivCmd();
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
                $getconstemp = new MullerIntuitivCmd();
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
                $setconstemp = new MullerIntuitivCmd();
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
                $getmoderoom = new MullerIntuitivCmd();
            }
            $getmoderoom->setName(__('Mode', __FILE__));
            $getmoderoom->setLogicalId('therm_setpoint_mode');
            $getmoderoom->setEqLogic_id($this->getId());
            $getmoderoom->setGeneric_type('THERMOSTAT_MODE');
            $getmoderoom->setType('info');
            $getmoderoom->setSubType('string');
            $getmoderoom->setOrder(1);
            $getmoderoom->save();

            $setmodehome = $this->getCmd(null, 'roommodehome');
            if (!is_object($setmodehome)) {
                $setmodehome = new MullerIntuitivCmd();
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
                $setmodehg = new MullerIntuitivCmd();
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

            $getwindow = $this->getCmd(null, 'open_window');
            if (!is_object($getwindow)) {
                $getwindow = new MullerIntuitivCmd();
            }
            $getwindow->setName(__('Fenêtre', __FILE__));
            $getwindow->setLogicalId('open_window');
            $getwindow->setEqLogic_id($this->getId());
            $getwindow->setTemplate('dashboard','MullerIntuitiv::windows');
            $getwindow->setTemplate('mobile','MullerIntuitiv::windows');
            $getwindow->setGeneric_type('OPENING_WINDOW');
            $getwindow->setType('info');
            $getwindow->setSubType('binary');
            $getwindow->save();

            $setwindowsopen = $this->getCmd(null, 'windowsopen');
            if (!is_object($setwindowsopen)) {
                $setwindowsopen = new MullerIntuitivCmd();
            }
            $setwindowsopen->setName(__('Ouverture de la fenêtre', __FILE__));
            $setwindowsopen->setLogicalId('windowsopen');
            $setwindowsopen->setEqLogic_id($this->getId());
            $setwindowsopen->setType('action');
            $setwindowsopen->setSubType('other');
            $setwindowsopen->setValue($getwindow->getId());
            $setwindowsopen->setDisplay('icon', '<i class="icon jeedom-fenetre-ouverte"></i>');
            $setwindowsopen->save();

            $setwindowsclose = $this->getCmd(null, 'windowsclose');
            if (!is_object($setwindowsclose)) {
                $setwindowsclose = new MullerIntuitivCmd();
            }
            $setwindowsclose->setName(__('Fermeture de la fenêtre', __FILE__));
            $setwindowsclose->setLogicalId('windowsclose');
            $setwindowsclose->setEqLogic_id($this->getId());
            $setwindowsclose->setType('action');
            $setwindowsclose->setSubType('other');
            $setwindowsclose->setValue($getwindow->getId());
            $setwindowsclose->setDisplay('icon', '<i class="icon jeedom-fenetre-ferme"></i>');
            $setwindowsclose->save();
        }

        if ($this->getIsEnable() == 1) {
            $this->updateApiMullerIntuitiv($this->getConfiguration('mullerintuitiv_id'));
        }
    }

    /**
     * @throws Exception
     */
    public function toHtml($_version = 'dashboard') {
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = jeedom::versionAlias($_version);

        $getmoderoom = $this->getCmd(null, 'therm_setpoint_mode');
        $replace['#getmoderoom#'] = is_object($getmoderoom) ? $getmoderoom->execCmd() : '';
        $replace['#getnameroom#'] = is_object($getmoderoom) ? $getmoderoom->getName() : '';

        $setroommodehome = $this->getCmd(null, 'roommodehome');
        $replace['#setroommodehome#'] = is_object($setroommodehome) ? $setroommodehome->getId() : '';

        $setroommodefrost = $this->getCmd(null, 'roommodefrost');
        $replace['#setroommodefrost#'] = is_object($setroommodefrost) ? $setroommodefrost->getId() : '';

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
        $replacewindows['#_icon_on_#'] = '<i class=\'icon jeedom-fenetre-ouverte\'></i>';
        $replacewindows['#_icon_off_#'] = '<i class=\'icon jeedom-fenetre-ferme\'></i>';

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

        if ($this->getLogicalId() == 'MullerIntuitiv_home'){
            $html = $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'home', 'MullerIntuitiv')));
        }else{
            $html = $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'mullerintuitiv', 'MullerIntuitiv')));
        }

        cache::set('widgetHtml' . $_version . $this->getId(), $html, 0);
        return $html;
    }

    /*     * **********************Getteur Setteur*************************** */
}

class MullerIntuitivCmd extends cmd {
    /*     * *************************Attributs****************************** */
    public static $_widgetPossibility = ['custom' => false];
    
    /*     * ***********************Methode static*************************** */

    /*     * *********************Methode d'instance************************* */

    /**
     * @throws Exception
     */
    public function execute($_options = []) {
        $eqlogic = $this->getEqLogic();
        $mullerintuitivid = $eqlogic->getConfiguration('mullerintuitiv_id');
        $api = MullerIntuitiv::getSession();
        $rooms = MullerIntuitiv::getRooms();
        $getmodehome = MullerIntuitiv::getModeHome();

        if ($this->getLogicalId() == 'homemodehome'){
            $api->setModeHome('schedule');
        }

        if ($this->getLogicalId() == 'homemodefrost'){
            $api->setModeHome('hg');
        }

        if ($this->getLogicalId() == 'homemodeaway'){
            $api->setModeHome('away');
        }

        foreach ($rooms as $value){
            if ($value['therm_setpoint_mode'] == 'off' && $mullerintuitivid == $value['id']){
                throw new Exception(__('Votre radiateur est éteint vous ne pouvez pas faire cette action.', __FILE__));
            }

            if ($this->getLogicalId() == 'setconstemp' && $mullerintuitivid == $value['id']){
                $api->setTemperature($mullerintuitivid,(float)$_options['slider']);
            }

            if ($this->getLogicalId() == 'roommodehome' && $mullerintuitivid == $value['id']){
                if($getmodehome == 'hg' || $getmodehome == 'away'){
                    throw new Exception(__('La maison est en mode Absent ou Hors Gel vous ne pouvez pas faire cette action pour '.$eqlogic->getName(), __FILE__));
                }
                $api->setRoomHome($mullerintuitivid);
            }

            if ($this->getLogicalId() == 'roommodefrost' && $mullerintuitivid == $value['id']){
                if($getmodehome == 'hg' || $getmodehome == 'away'){
                    throw new Exception(__('La maison est en mode Absent ou Hors Gel vous ne pouvez pas faire cette action pour '.$eqlogic->getName(), __FILE__));
                }
                $api->setRoomHorsGel($mullerintuitivid);
            }

            if ($this->getLogicalId() == 'windowsopen' && $mullerintuitivid == $value['id']){
                if($getmodehome == 'hg' || $getmodehome == 'away'){
                    throw new Exception(__('La maison est en mode Absent ou Hors Gel vous ne pouvez pas faire cette action pour '.$eqlogic->getName(), __FILE__));
                }
                $api->setWindows($mullerintuitivid, true);
            }

            if ($this->getLogicalId() == 'windowsclose' && $mullerintuitivid == $value['id']){
                if($getmodehome == 'hg' || $getmodehome == 'away'){
                    throw new Exception(__('La maison est en mode Absent ou Hors Gel vous ne pouvez pas faire cette action pour '.$eqlogic->getName(), __FILE__));
                }
                $api->setWindows($mullerintuitivid, false);
            }
        }

        foreach (oklyn::byType('MullerIntuitiv') as $eqLogic) {
                $eqLogic->updateApiMullerIntuitiv($eqLogic->getConfiguration('mullerintuitiv_id'));
        }
    }

    /*     * **********************Getteur Setteur*************************** */


}


