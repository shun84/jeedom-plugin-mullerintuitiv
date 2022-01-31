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

    /*
    * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
    * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	    public static $_widgetPossibility = array();
    */
    public static $_widgetPossibility = array('custom' => true);
    /*     * ***********************Methode static*************************** */

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

    // Fonction exécutée automatiquement toutes les minutes par Jeedom
    public static function cron() {
        foreach (oklyn::byType('MullerIntuitiv') as $eqLogic) {
            if ($eqLogic->getIsEnable() == 1) {
                $roomsidandname = self::getRoomsIdAndName();
                foreach ($roomsidandname as $room){
                    $eqLogic->updateApiMullerIntuitiv($room['id']);
                }
            }
        }
    }

    /*     * *********************Méthodes d'instance************************* */

    public function updateApiMullerIntuitiv(string $mullerintuitivid){
        $replacemodehome = '';
        $rooms = $this->getRooms();
        $modehome = $this->getModeHome();

        if ($modehome == 'schedule'){
            $replacemodehome = str_replace($modehome, 'Home',$modehome);
        }elseif ($modehome == 'hg'){
            $replacemodehome = str_replace($modehome, 'Hors Gel',$modehome);
        }elseif ($modehome == 'away'){
            $replacemodehome = str_replace($modehome, 'Absent',$modehome);
        }

        $this->checkAndUpdateCmd('therm_mode', $replacemodehome);

        foreach ($rooms as $value){
           if ($mullerintuitivid == $value['id']){
               $this->checkAndUpdateCmd('open_window', $value['open_window']);
               $this->checkAndUpdateCmd('therm_measured_temperature', $value['therm_measured_temperature']);
               $this->checkAndUpdateCmd('therm_setpoint_mode', $value['therm_setpoint_mode']);
               $this->checkAndUpdateCmd('therm_setpoint_temperature', $value['therm_setpoint_temperature']);
           }
        }

        $this->refreshWidget();
    }


    // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {

    }

    // Fonction exécutée automatiquement après la création de l'équipement
    public function postInsert() {

    }

    // Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {
        
    }

    // Fonction exécutée automatiquement après la mise à jour de l'équipement
    public function postUpdate() {
        
    }

    // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
    public function preSave() {
        
    }

    // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement

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
            $setmodeschedule->setDisplay('icon', '<i class="fas fa-house-user"></i>');
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
            $setmodehg->setDisplay('icon', '<i class="fas fa-snowflake"></i>');
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
            $gettemp->setUnite('°C');
            $gettemp->setType('info');
            $gettemp->setSubType('numeric');
            $gettemp->save();

            $getconstemp = $this->getCmd(null, 'therm_setpoint_temperature');
            if (!is_object($getconstemp)) {
                $getconstemp = new MullerIntuitivCmd();
            }
            $getconstemp->setName(__('Récupération Consigne Temperature', __FILE__));
            $getconstemp->setLogicalId('therm_setpoint_temperature');
            $getconstemp->setEqLogic_id($this->getId());
            $getconstemp->setIsVisible(0);
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
            $setconstemp->setType('action');
            $setconstemp->setSubType('slider');
            $setconstemp->setValue($getconstemp->getId());
            $setconstemp->save();

            $getmoderoom = $this->getCmd(null, 'therm_setpoint_mode');
            if (!is_object($getmoderoom)) {
                $getmoderoom = new MullerIntuitivCmd();
            }
            $getmoderoom->setName(__('Mode', __FILE__));
            $getmoderoom->setLogicalId('therm_setpoint_mode');
            $getmoderoom->setEqLogic_id($this->getId());
            $getmoderoom->setType('info');
            $getmoderoom->setSubType('string');
            $getmoderoom->save();

            $setmodehome = $this->getCmd(null, 'roommodehome');
            if (!is_object($setmodehome)) {
                $setmodehome = new MullerIntuitivCmd();
            }
            $setmodehome->setName(__('Home', __FILE__));
            $setmodehome->setLogicalId('roommodehome');
            $setmodehome->setEqLogic_id($this->getId());
            $setmodehome->setType('action');
            $setmodehome->setSubType('other');
            $setmodehome->setValue($getmoderoom->getId());
            $setmodehome->setDisplay('icon', '<i class="fas fa-house-user"></i>');
            $setmodehome->save();

            $setmodehg = $this->getCmd(null, 'roommodefrost');
            if (!is_object($setmodehg)) {
                $setmodehg = new MullerIntuitivCmd();
            }
            $setmodehg->setName(__('Hors Gel', __FILE__));
            $setmodehg->setLogicalId('roommodefrost');
            $setmodehg->setEqLogic_id($this->getId());
            $setmodehg->setType('action');
            $setmodehg->setSubType('other');
            $setmodehg->setValue($getmoderoom->getId());
            $setmodehg->setDisplay('icon', '<i class="fas fa-snowflake"></i>');
            $setmodehg->save();

            $getwindow = $this->getCmd(null, 'open_window');
            if (!is_object($getwindow)) {
                $getwindow = new MullerIntuitivCmd();
            }
            $getwindow->setName(__('Window', __FILE__));
            $getwindow->setLogicalId('open_window');
            $getwindow->setEqLogic_id($this->getId());
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
            $setwindowsopen->setDisplay('icon', '<i class="fas fa-door-open"></i>');
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
            $setwindowsclose->setDisplay('icon', '<i class="fas fa-door-closed"></i>');
            $setwindowsclose->save();
        }

        if ($this->getIsEnable() == 1) {
            $this->updateApiMullerIntuitiv($this->getConfiguration('mullerintuitiv_id'));
        }
    }

    // Fonction exécutée automatiquement avant la suppression de l'équipement
    public function preRemove() {
        
    }

    // Fonction exécutée automatiquement après la suppression de l'équipement
    public function postRemove() {
        
    }

    /*
     * Non obligatoire : permet de modifier l'affichage du widget (également utilisable par les commandes)
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire : permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire : permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class MullerIntuitivCmd extends cmd {
    /*     * *************************Attributs****************************** */
    
    /*
      public static $_widgetPossibility = array();
    */
    
    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    /**
     * @throws Exception
     */
    public function execute($_options = []) {
        $eqlogic = $this->getEqLogic();
        $mullerintuitivid = $eqlogic->getConfiguration('mullerintuitiv_id');
        $api = MullerIntuitiv::getSession();
        $rooms = $api->getRooms();
        $getmodehome = $api->getModeHome();

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

        $eqlogic->updateApiMullerIntuitiv($mullerintuitivid);
    }

    /*     * **********************Getteur Setteur*************************** */


}


