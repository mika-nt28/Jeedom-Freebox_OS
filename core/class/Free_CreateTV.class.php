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
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: ' . $logicalinfo['playerName'] . ' ──');
        $Free_API = new Free_API();
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
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('CONFIGURATION PLAYER', __FILE__)) . ' : ' . $nb_player . ' - ' . $_devicename);
                    if (isset($Equipement['id'])) {
                        $player_STATE = 'KO';
                        $player_log = ' -- ' . (__('Il n\'est pas possible de récupérer le status du Player donc pas de création de la commande d\'état', __FILE__));
                        $player_ID = $Equipement['mac'];
                        $player_MAC = 'MAC';
                        if ($Equipement['id']) {
                            if ($Equipement['id'] != null) {
                                $results_playerID = $Free_API->universal_get('universalAPI', null, null, 'player/' . $Equipement['id'] . '/api/v6/status', true, true, false);
                                if (isset($results_playerID['power_state'])) {
                                    log::add('Freebox_OS', 'debug', '| ───▶︎ ETAT PLAYER : ' . $results_playerID['power_state']);
                                    if ($results_playerID['power_state'] == 'running' || $results_playerID['power_state'] == 'standby') {
                                        $player_STATE = 'OK';
                                        $player_log = ' -- ' . (__('Il est possible de récupérer le status du Player', __FILE__));
                                    }
                                    $player_ID = $Equipement['id'];
                                    $player_MAC = 'ID';
                                    log::add('Freebox_OS', 'debug', '| ───▶︎ PLAYER : ' . $_devicename . ' -- Id : ' . $Equipement['id'] . $player_log);
                                } else {
                                    log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ PLAYER : ' . $_devicename . ' -- Id : ' . $Equipement['id'] . $player_log . ':/fg:');
                                }
                            } else {
                                log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ PLAYER : ' . $_devicename . ' -- Mac : ' . $Equipement['mac'] . ' -- L\'Id est vide ───▶︎ ' . $player_log . ':/fg:');
                            }
                            $EqLogic = Freebox_OS::AddEqLogic($_devicename, 'player_' . $player_ID, 'multimedia', true, 'player', null, $player_ID, '*/5 * * * *', null, $player_STATE, null, 'system', true, $player_MAC);
                            log::add('Freebox_OS', 'debug', '| ───▶︎ Nom : ' . $_devicename . ' -- id / mac : player_' . $Equipement['id'] . ' / ' . $Equipement['mac'] . ' -- FREE-ID : ' . $Equipement['id'] . ' -- TYPE-ID : ' . $player_MAC);
                            $EqLogic->AddCommand('Mac', 'mac', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', false, false);
                            $EqLogic->AddCommand('Type', 'stb_type', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 2, '0', false, false);
                            $EqLogic->AddCommand('Modèle', 'device_model', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 3, '0', false, false);
                            $EqLogic->AddCommand('Version', 'api_version', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 4, '0', false, false);
                            $EqLogic->AddCommand('API Disponible', 'api_available', 'info', 'binary', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 5, '0', false, false);
                            $EqLogic->AddCommand('Disponible sur le réseau', 'reachable', 'info', 'binary', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 6, '0', false, false);
                            if ($player_STATE == 'OK') {
                                $EqLogic->AddCommand('Etat', 'power_state', 'info', 'string', $TemplatePlayer, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 7, '0', false, false);
                            }
                        }
                    } else {
                        log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ ' . (__('AUCUNE INFO supplémentaire disponible pour le player ou absence d\'ID', __FILE__)) . ':/fg:');
                    }
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('FIN CONFIGURATION PLAYER', __FILE__)) . ' : ' . $nb_player . ' / ' . $_devicename);
                    $nb_player++;
                }
            } else {
                log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ ' . (__('PAS DE', __FILE__)) . ' ' . $logicalinfo['playerName'] . ' ' . (__('SUR VOTRE BOX', __FILE__)) . ':/fg:');
            }
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
}
