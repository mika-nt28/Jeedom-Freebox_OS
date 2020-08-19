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

class Free_Refresh
{
    public static function RefreshInformation($_freeboxID)
    {
        $Free_API = new Free_API();
        $Equipement = eqlogic::byId($_freeboxID);
        if (is_object($Equipement) && $Equipement->getIsEnable()) {
            if ($Equipement->getConfiguration('type') == 'player' || $Equipement->getConfiguration('type') == 'parental') {
                $refresh = $Equipement->getConfiguration('type');
            } else {
                $refresh = $Equipement->getLogicalId();
            }

            switch ($refresh) {
                case 'airmedia':

                    break;
                case 'connexion':
                    Free_Refresh::refresh_connexion($Equipement, $Free_API);
                    break;
                case 'disk':
                    foreach ($Equipement->getCmd('info') as $Command) {
                        if (is_object($Command)) {
                            $result = $Free_API->universal_get('disk', $Command->getLogicalId());
                            if ($result != false) {
                                log::add('Freebox_OS', 'debug', '>───────── Occupation du disque : ' . $result . '%');
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
                            }
                        }
                    }
                    break;
                case 'downloads':
                    Free_Refresh::refresh_download($Equipement, $Free_API);
                    break;
                case 'homeadapters':
                    foreach ($Equipement->getCmd('info') as $Command) {
                        $result = $Free_API->universal_get('homeadapters_status', $Command->getLogicalId());
                        if ($result != false) {
                            if ($result['status'] == 'active') {
                                $homeadapters_value = 1;
                            } else {
                                $homeadapters_value = 0;
                            }
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $homeadapters_value);
                        }
                    }
                    break;
                case 'parental':
                    foreach ($Equipement->getCmd('info') as $Command) {
                        $results = $Free_API->universal_get('parental_ID', $Equipement->getConfiguration('action'));
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $results['current_mode']);
                    }
                    break;
                case 'phone':
                    Free_Refresh::refresh_phone($Equipement, $Free_API);
                    break;
                case 'player':
                    Free_Refresh::refresh_player($Equipement, $Free_API);
                    break;
                case 'network':
                    Free_Refresh::refresh_network($Equipement, $Free_API);
                    break;
                case 'system':
                    Free_Refresh::refresh_system($Equipement, $Free_API);
                    break;
                case 'wifi':
                    foreach ($Equipement->getCmd('info') as $Command) {
                        if (is_object($Command)) {
                            switch ($Command->getLogicalId()) {
                                case "wifiStatut":
                                    $result = $Free_API->universal_get('wifi');
                                    $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
                                    break;
                                case "wifiPlanning":
                                    $result = $Free_API->universal_get('planning');
                                    $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
                                    break;
                            }
                        }
                    }
                    break;
                default:
                    Free_Refresh::refresh_default($Equipement, $Free_API);
                    break;
            }
        }
    }

    private static function refresh_connexion($Equipement, $Free_API)
    {
        $result = $Free_API->connexion_stats();
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "bandwidth_down":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['bandwidth_down']);
                            break;
                        case "bandwidth_up":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['bandwidth_up']);
                            break;
                        case "ipv4":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['ipv4']);
                            break;
                        case "ipv6":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['ipv6']);
                            break;
                        case "media":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['media']);
                            break;
                        case "rate_down":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['rate_down']);
                            break;
                        case "rate_up":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['rate_up']);
                            break;
                        case "state":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['state']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_download($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('download_stats');
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "nb_tasks":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks']);
                            break;
                        case "nb_tasks_downloading":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_downloading']);
                            break;
                        case "nb_tasks_done":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_done']);
                            break;
                        case "nb_rss":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_rss']);
                            break;
                        case "nb_rss_items_unread":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_rss_items_unread']);
                            break;
                        case "rx_rate":
                            $result = $result['rx_rate'];
                            if (function_exists('bcdiv'))
                                $result = bcdiv($result, 1048576, 2);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
                            break;
                        case "tx_rate":
                            $result = $result['tx_rate'];
                            if (function_exists('bcdiv'))
                                $result = bcdiv($result, 1048576, 2);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
                            break;
                        case "nb_tasks_active":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_active']);
                            break;
                        case "nb_tasks_stopped":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_stopped']);
                            break;
                        case "nb_tasks_queued":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_queued']);
                            break;
                        case "nb_tasks_repairing":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_repairing']);
                            break;
                        case "nb_tasks_extracting":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_extracting']);
                            break;
                        case "nb_tasks_error":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_error']);
                            break;
                        case "nb_tasks_checking":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_checking']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_phone($Equipement, $Free_API)
    {
        $result = $Free_API->nb_appel_absence();
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "nbAppelsManquee":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['missed']);
                            break;
                        case "nbAppelRecus":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['accepted']);
                            break;
                        case "nbAppelPasse":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['outgoing']);
                            break;
                        case "listAppelsManquee":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['list_missed']);
                            break;
                        case "listAppelsRecus":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['list_accepted']);
                            break;
                        case "listAppelsPasse":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['list_outgoing']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_network($Equipement, $Free_API)
    {
        foreach ($Equipement->getCmd('info') as $Command) {
            if (is_object($Command)) {
                $result = $Free_API->universal_get('network_ping', $Command->getLogicalId());
                if (!$result['success']) {
                    log::add('Freebox_OS', 'debug', '>───────── ERROR ' . $Command->getLogicalId() . '=> APPAREIL PAS TROUVE');
                    if ($result['error_code'] === "internal_error") {
                        $Command->remove();
                    }
                } else {
                    if (isset($result['result']['l3connectivities'])) {
                        foreach ($result['result']['l3connectivities'] as $Ip) {
                            if ($Ip['active']) {
                                if ($Ip['af'] == 'ipv4') {
                                    $Command->setConfiguration('IPV4', $Ip['addr']);
                                } else {
                                    $Command->setConfiguration('IPV6', $Ip['addr']);
                                }
                            }
                        }
                    }
                    $Command->setConfiguration('host_type', $result['result']['host_type']);
                    $Command->save();

                    if (isset($result['result']['active'])) {
                        if ($result['result']['active'] == 'true') {
                            $Command->setOrder($Command->getOrder() % 1000);
                            $Command->save();
                            log::add('Freebox_OS', 'debug', '>───────── ' . $Command->getLogicalId() . ' : true ');
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), true);
                        } else {
                            $Command->setOrder($Command->getOrder() % 1000 + 1000);
                            $Command->save();
                            log::add('Freebox_OS', 'debug', '>───────── ' . $Command->getLogicalId() . ' : false ');
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), false);
                        }
                    } else {
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), false);
                    }
                }
            }
        }
    }

    private static function refresh_system($Equipement, $Free_API)
    {
        foreach ($Equipement->getCmd('info') as $Command) {
            $logicalId = $Command->getConfiguration('logicalId');

            switch ($Command->getConfiguration('logicalId')) {
                case "sensors":
                    foreach ($Free_API->universal_get('system', null, "sensors") as $system) {
                        if ($Command->getLogicalId() != $system['id']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $Equipement->checkAndUpdateCmd($system['id'], $value);
                    }
                    break;
                case "fans":
                    foreach ($Free_API->universal_get('system', null, "fans") as $system) {
                        if ($Command->getLogicalId() != $system['id']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $Equipement->checkAndUpdateCmd($system['id'], $value);
                    }
                    break;
                case "expansions":
                    foreach ($Free_API->universal_get('system', null, "expansions") as $system) {
                        if ($Command->getLogicalId() != $system['slot']) continue;
                        $value = $system['present'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['slot'] . ' -- valeur : ' . $value);
                        $Equipement->checkAndUpdateCmd($system['slot'], $value);
                    }
                    break;
                default:
                    if (is_object($Command)) {
                        if ($Command->getLogicalId() == "4GStatut") {
                            $result = $Free_API->universal_get('4G');
                        } else {
                            $result = $Free_API->universal_get('system', null, null);
                        }

                        switch ($Command->getLogicalId()) {
                            case "mac":
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['mac']);
                                break;
                            case "uptime":
                                $result = $result['uptime'];
                                $result = str_replace(' heure ', 'h ', $result);
                                $result = str_replace(' heures ', 'h ', $result);
                                $result = str_replace(' minute ', 'min ', $result);
                                $result = str_replace(' minutes ', 'min ', $result);
                                $result = str_replace(' secondes', 's', $result);
                                $result = str_replace(' seconde', 's', $result);
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
                                break;
                            case "board_name":
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['board_name']);
                                break;
                            case "serial":
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['serial']);
                                break;
                            case "firmware_version":
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['firmware_version']);
                                break;
                            case "4GStatut":
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
                                break;
                        }
                    }
                    break;
            }
        }
    }

    private static function refresh_default($Equipement, $Free_API)
    {
        $results = $Free_API->universal_get('tiles_ID', $Equipement->getLogicalId());

        if ($results != false) {
            foreach ($results as $result) {
                foreach ($result['data'] as $data) {
                    $cmd = $Equipement->getCmd('info', $data['ep_id']);
                    if (!is_object($cmd)) break;

                    log::add('Freebox_OS', 'debug', '│ Label : ' . $data['label'] . ' -- Name : ' . $data['name'] . ' -- Id : ' . $data['ep_id'] . ' -- Value : ' . $data['value']);
                    if ($data['name'] == 'pushed') {
                        $nb_pushed = count($data['history']);
                        $nb_pushed_k = $nb_pushed - 1;
                        $_value_history = $data['history'][$nb_pushed_k]['value'];
                        log::add('Freebox_OS', 'debug', '│ Nb pushed -1  : ' . $nb_pushed_k . ' -- Valeur historique récente  : ' . $_value_history);
                    };


                    switch ($cmd->getSubType()) {
                        case 'numeric':
                            if ($cmd->getConfiguration('inverse')) {
                                $_value = ($cmd->getConfiguration('maxValue') - $cmd->getConfiguration('minValue')) - $data['value'];
                            } else {
                                if ($data['name'] == 'pushed') {
                                    $_value = $_value_history;
                                } else {
                                    $_value = $data['value'];
                                }
                            }
                            break;
                        case 'string':
                            if ($data['name'] == 'state' && $Equipement->getConfiguration('type') == 'alarm_control') {
                                log::add('Freebox_OS', 'debug', '│──────────> Update commande spécifique pour Homebridge : ' . $Equipement->getConfiguration('type'));
                                $_Alarm_stat_value = '0';
                                $_Alarm_enable_value = '1';

                                switch ($data['value']) {
                                    case 'alarm1_arming':
                                        $_Alarm_mode_value = 'Alarme principale';
                                        log::add('Freebox_OS', 'debug', '│ Mode 1 : Alarme principale (arming)');
                                        break;
                                    case 'alarm1_armed':
                                        $_Alarm_mode_value = 'Alarme principale';
                                        log::add('Freebox_OS', 'debug', '│ Mode 1 : Alarme principale (armed)');
                                        break;
                                    case 'alarm2_arming':
                                        $_Alarm_mode_value = 'Alarme secondaire';
                                        log::add('Freebox_OS', 'debug', '│ Mode 2 : Alarme secondaire (arming)');
                                        break;
                                    case 'alarm2_armed':
                                        $_Alarm_mode_value = 'Alarme secondaire';
                                        log::add('Freebox_OS', 'debug', '│ Mode 2 : Alarme secondaire (armed)');
                                        break;
                                    case 'alert':
                                        $_Alarm_stat_value = '1';
                                        log::add('Freebox_OS', 'debug', '│ Alarme');
                                        break;
                                    case 'alarm1_alert_timer':
                                        $_Alarm_stat_value = '1';
                                        log::add('Freebox_OS', 'debug', '│ Alarme');
                                        break;
                                    case 'alarm2_alert_timer':
                                        $_Alarm_stat_value = '1';
                                        log::add('Freebox_OS', 'debug', '│ Alarme');
                                        break;
                                    case 'idle':
                                        $_Alarm_enable_value = '0';
                                        log::add('Freebox_OS', 'debug', '│ Alarme désactivée');
                                        break;
                                    default:
                                        $_Alarm_mode_value = null;
                                        log::add('Freebox_OS', 'debug', '│ Aucun Mode');
                                        break;
                                }

                                $Equipement->checkAndUpdateCmd('ALARM_state', $_Alarm_stat_value);
                                log::add('Freebox_OS', 'debug', '│ Label : ' . 'Statut' . ' -- Id : ' . 'ALARM_state' . ' -- Value : ' . $_Alarm_stat_value);
                                $Equipement->checkAndUpdateCmd('ALARM_enable', $_Alarm_enable_value);
                                log::add('Freebox_OS', 'debug', '│ Label : ' . 'Actif' . ' -- Id : ' . 'ALARM_enable' . ' -- Value : ' . $_Alarm_enable_value);
                                $Equipement->checkAndUpdateCmd('ALARM_mode', $_Alarm_mode_value);
                                log::add('Freebox_OS', 'debug', '│ Label : ' . 'Mode' . ' -- Id : ' . 'ALARM_mode' . ' -- Value : ' . $_Alarm_mode_value);
                                log::add('Freebox_OS', 'debug', '│──────────> Fin Update commande spécifique pour Homebridge');
                            };

                            $_value = $data['value'];
                            break;
                        case 'binary':
                            if ($cmd->getConfiguration('inverse')) {
                                $_value = !$data['value'];
                            } else {
                                $_value = $data['value'];
                            }
                            break;
                    }
                    $Equipement->checkAndUpdateCmd($data['ep_id'], $_value);
                }
            }
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function refresh_player($Equipement, $Free_API)
    {

        $results_playerID = $Free_API->universal_get('player_ID', $Equipement->getConfiguration('action'));
        $results_players = $Free_API->universal_get('player', $Equipement->getConfiguration('action'));

        $cmd_mac = $Equipement->getCmd('info', 'mac');
        $cmd_stb_type = $Equipement->getCmd('info', 'stb_type');
        $cmd_device_model = $Equipement->getCmd('info', 'device_model');
        $cmd_api_version = $Equipement->getCmd('info', 'api_version');
        $cmd_api_available = $Equipement->getCmd('info', 'api_available');
        $cmd_reachable = $Equipement->getCmd('info', 'reachable');
        $cmd_powerState = $Equipement->getCmd('info', 'power_state');


        foreach ($results_players as $results_player) {
            if ($results_player['id'] != $Equipement->getConfiguration('action')) continue;

            if ($results_player['api_available']) {
                if ($cmd_stb_type) $Equipement->checkAndUpdateCmd($cmd_stb_type->getLogicalId(), $results_player['stb_type']);
                if ($cmd_device_model) $Equipement->checkAndUpdateCmd($cmd_device_model->getLogicalId(), $results_player['device_model']);
                if ($cmd_api_version) $Equipement->checkAndUpdateCmd($cmd_api_version->getLogicalId(), $results_player['api_version']);
            }

            if ($cmd_mac) $Equipement->checkAndUpdateCmd($cmd_mac->getLogicalId(), $results_player['mac']);
            if ($cmd_api_available) $Equipement->checkAndUpdateCmd($cmd_api_available->getLogicalId(), $results_player['api_available']);
            if ($cmd_reachable) $Equipement->checkAndUpdateCmd($cmd_reachable->getLogicalId(), $results_player['reachable']);
        }

        if ($cmd_powerState) $Equipement->checkAndUpdateCmd($cmd_powerState->getLogicalId(), $results_playerID['power_state']);
    }
}
