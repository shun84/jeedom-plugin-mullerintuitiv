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

use GuzzleHttp\Exception\GuzzleException;

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
        mullerintuitiv::getSynMods();
        ajax::success();
    }

    if (init('action') == 'getMullerintuitiv') {
        $object = jeeObject::byId(init('object_id'));

        if (!is_object($object)) {
            throw new Exception(__('Mullerintuitiv racine trouvé', __FILE__));
        }

        $return = ['object' => utils::o2a($object)];

        $date = [
            'start' => init('dateStart'),
            'end' => init('dateEnd'),
        ];

        if ($date['start'] == '') {
            $date['start'] = date('Y-m-d', strtotime('-1 months ' . date('Y-m-d')));
        }
        if ($date['end'] == '') {
            $date['end'] = date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')));
        }
        $return['date'] = $date;

        $token = mullerintuitiv::getToken();
        $gethomes = mullerintuitiv::getHomes();

        foreach ($object->getEqLogic(true, false, 'mullerintuitiv') as $eqLogic) {
            $mullerintuitiv = utils::o2a($eqLogic);
            $mullerintuitivid = $mullerintuitiv['configuration']['mullerintuitiv_id'];
            foreach ($gethomes as $home){
                if (preg_match('/home/', $mullerintuitiv['logicalId'])){
                    $gethomemeasure = mullerintuitiv::getHomeMeasure(
                        $home['modules'][0]['id'],
                        strtotime($date['end']  . ' 00:00:00 UTC'),
                        strtotime($date['start']  . ' 00:00:00 UTC'),
                        $home['id']
                    );
                    $return['eqLogics'][] = [
                        'eqLogic' => $mullerintuitiv,
                        'html' => $eqLogic->toHtml(init('version')),
                        'gethomemeasure' => $gethomemeasure
                    ];

                } else {
                    $mullerintuitivbridge = $mullerintuitiv['configuration']['mullerintuitiv_therm_relay'];
                    $getroommeasure = mullerintuitiv::getRoomMeasure(
                        strtotime($date['end']  . ' 00:00:00 UTC'),
                        strtotime($date['start']  . ' 00:00:00 UTC'),
                        $mullerintuitivid,
                        $mullerintuitivbridge,
                        $home['id']
                    );
                    $return['eqLogics'][] = [
                        'eqLogic' => $mullerintuitiv,
                        'html' => $eqLogic->toHtml(init('version')),
                        'getroommeasure' => $getroommeasure
                    ];
                }
            }
        }

        ajax::success($return);
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
} catch (GuzzleException $e) {
    ajax::error(displayException($e), $e->getCode());
}

