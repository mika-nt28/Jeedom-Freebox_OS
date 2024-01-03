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

class Free_CreateTV
{
    public static function createTV($create = 'default')
    {
        $logicalinfo = Freebox_OS::getlogicalinfo();
        if (version_compare(jeedom::version(), "4", "<")) {
            $templatecore_V4 = null;
        } else {
            $templatecore_V4  = 'core::';
        };
        switch ($create) {
            default:
                Free_CreateTV::createTV_player($logicalinfo, $templatecore_V4);
                break;
        }
    }
    private static function createTV_player($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['playerName']);
        $Free_API = new Free_API();
        log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
        $TemplatePlayer = 'Freebox_OS::Player';

        $result = $Free_API->universal_get('universalAPI', null, null, 'player', false, true, true);
        $nb_player = 1;
        if (isset($result['result'])) {
            $result = $result['result'];
            if ($result != null) {
                foreach ($result as $Equipement) {
                    if ($Equipement['device_name'] == null) {
                        $_devicename = 'Player - ' . $Equipement['id'] . ' - ' . $Equipement['mac'];
                    } else {
                        $_devicename = $Equipement['device_name'];
                    }
                    log::add('Freebox_OS', 'debug', '│===========> CONFIGURATION PLAYER : ' . $nb_player . ' - ' . $_devicename);

                    if ($Equipement['id'] != null) {
                        $results_playerID = $Free_API->universal_get('universalAPI', null, null, 'player/' . $Equipement['id'] . '/api/v6/status', true, true, false);
                        log::add('Freebox_OS', 'debug', '│===========> ETAT PLAYER : ' . $results_playerID['power_state']);
                        if ($results_playerID['power_state'] == 'running' || $results_playerID['power_state'] == 'standby') {
                            $player_STATE = 'OK';
                            $player_log = ' -- Il est possible de récupérer le status du Player';
                        } else {
                            $player_STATE = 'NOK';
                            $player_log = ' -- Il n\'est pas possible de récupérer le status du Player donc pas de création des commandes d\'état';
                        }

                        log::add('Freebox_OS', 'debug', '│===========> PLAYER : ' . $_devicename . ' -- Id : ' . $Equipement['id'] . $player_log);
                        $EqLogic = Freebox_OS::AddEqLogic($_devicename, 'player_' . $Equipement['id'], 'multimedia', true, 'player', null, $Equipement['id'], '*/5 * * * *', null, $player_STATE, null, 'system', true);
                        log::add('Freebox_OS', 'debug', '│ Nom : ' . $_devicename . ' -- id : player_' . $Equipement['id'] . ' -- FREE-ID : ' . $Equipement['id']);
                        $EqLogic->AddCommand('Mac', 'mac', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', false, false);
                        $EqLogic->AddCommand('Type', 'stb_type', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 2, '0', false, false);
                        $EqLogic->AddCommand('Modèle', 'device_model', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 3, '0', false, false);
                        $EqLogic->AddCommand('Version', 'api_version', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 4, '0', false, false);
                        $EqLogic->AddCommand('API Disponible', 'api_available', 'info', 'binary', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 5, '0', false, false);
                        $EqLogic->AddCommand('Disponible sur le réseau', 'reachable', 'info', 'binary', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 6, '0', false, false);
                        if ($player_STATE == 'OK') {
                            $EqLogic->AddCommand('Etat', 'power_state', 'info', 'string', $TemplatePlayer, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 7, '0', false, false);
                        }
                    } else {
                        log::add('Freebox_OS', 'debug', '│===========> PLAYER : ' . $_devicename . ' -- L\'Id est vide donc pas de création de l\'équipement (mettre sous tension le player pour résoudre ce problème)');
                    }
                    log::add('Freebox_OS', 'debug', '│===========> FIN CONFIGURATION PLAYER : ' . $nb_player . ' / ' . $_devicename);
                    $nb_player++;
                }
            } else {
                log::add('Freebox_OS', 'debug', '│ PAS DE ' . $logicalinfo['playerName'] . ' SUR VOTRE BOX ');
            }
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }
}
