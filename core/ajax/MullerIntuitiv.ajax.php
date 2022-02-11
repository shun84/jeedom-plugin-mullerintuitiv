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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }
    
    /* Fonction permettant l'envoi de l'entête 'Content-Type: application/json'
       En V3 : indiquer l'argument 'true' pour contrôler le token d'accès Jeedom
       En V4 : autoriser l'exécution d'une méthode 'action' en GET en indiquant le(s) nom(s) de(s) action(s) dans un tableau en argument
    */
    ajax::init();

    if (init('action') == 'synmodules') {
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

        ajax::success();
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}

