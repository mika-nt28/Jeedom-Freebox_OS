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
        Freebox_OS::Create_API();
        $API_version = config::byKey('FREEBOX_API', 'Freebox_OS');
        if ($API_version == null || $API_version === 'TEST_V8') {
            $result_API = Freebox_OS::Create_API();
            log::add('Freebox_OS', 'debug', '│──────────> Version API Compatible avec la Freebox : ' . $result_API);
        }

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
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3');
            $TemplatePlayer = null;
            $iconePlayOn = 'fas fa-play';
            $iconePlayOff = 'fas fa-stop';
            $iconePlayPrevious = 'fas fa-angle-double-left';
            $iconePlayNext = 'fas fa-angle-double-right';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $TemplatePlayer = 'Freebox_OS::Player';
            $iconePlayOn = 'fas fa-play icon_green';
            $iconePlayOff = 'fas fa-stop icon_red';
            $iconePlayPrevious = 'fas fa-angle-double-left';
            $iconePlayNext = 'fas fa-angle-double-right';
        };

        $result = $Free_API->universal_get('player');
        if ($result != null) {
            foreach ($result as $Equipement) {
                if ($Equipement['device_name'] == null) {
                    $_devicename = 'Player - ' . $Equipement['id'] . ' - ' . $Equipement['mac'];
                } else {
                    $_devicename = $Equipement['device_name'];
                }


                if ($Equipement['id'] != null) {
                    $results_playerID = $Free_API->universal_get('player_ID', $Equipement['id']);
                    log::add('Freebox_OS', 'debug', '│===========> ETAT PLAYER : ' . $results_playerID['power_state']);
                    if ($results_playerID['power_state'] == 'running' || $results_playerID['power_state'] == 'standby') {
                        $player_STATE = 'OK';
                        log::add('Freebox_OS', 'debug', '│===========> PLAYER : ' . $_devicename . ' -- Il est possible de récupérer le status du Player');
                    } else {
                        $player_STATE = 'NOK';
                        log::add('Freebox_OS', 'debug', '│===========> PLAYER : ' . $_devicename . ' -- Il n\'est pas possible de récupérer le status du Player donc pas de création des commandes d\'état');
                    }

                    log::add('Freebox_OS', 'debug', '│──────────> PLAYER : ' . $_devicename . ' -- Id : ' . $Equipement['id']);
                    $player = Freebox_OS::AddEqLogic($_devicename, 'player_' . $Equipement['id'], 'multimedia', true, 'player', null, $Equipement['id'], '*/5 * * * *', null, $player_STATE);
                    log::add('Freebox_OS', 'debug', '│ Nom : ' . $_devicename . ' -- id : player_' . $Equipement['id'] . ' -- FREE-ID : ' . $Equipement['id']);
                    $player->AddCommand('Mac', 'mac', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', false, false);
                    $player->AddCommand('Type', 'stb_type', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 2, '0', false, false);
                    $player->AddCommand('Modèle', 'device_model', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 3, '0', false, false);
                    $player->AddCommand('Version', 'api_version', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 4, '0', false, false);
                    $player->AddCommand('API Disponible', 'api_available', 'info', 'binary', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 5, '0', false, false);
                    $player->AddCommand('Disponible sur le réseau', 'reachable', 'info', 'binary', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 6, '0', false, false);
                    if ($player_STATE == 'OK') {
                        $player->AddCommand('Etat', 'power_state', 'info', 'string', $TemplatePlayer, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 7, '0', false, false);
                    }
                    Free_Refresh::RefreshInformation($player->getId());
                } else {
                    log::add('Freebox_OS', 'debug', '│===========> PLAYER : ' . $_devicename . ' -- L\'Id est vide donc pas de création de l\'équipement (mettre sous tension le player pour résoudre ce problème)');
                }
            }
        } else {
            log::add('Freebox_OS', 'debug', '│ PAS DE ' . $logicalinfo['playerName'] . ' SUR VOTRE BOX ');
        }

        log::add('Freebox_OS', 'debug', '└─────────');
    }
}
