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
                    Free_Refresh::refresh_disk($Equipement, $Free_API);
                    break;
                case 'downloads':
                    Free_Refresh::refresh_download($Equipement, $Free_API);
                    break;
                case 'homeadapters':
                    foreach ($Equipement->getCmd('info') as $Command) {
                        $result = $Free_API->universal_get('homeadapters', $Command->getLogicalId(), null, null);
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
                        $results = $Free_API->universal_get('parental', $Equipement->getConfiguration('action'));
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $results['current_mode']);
                    }
                    break;
                case 'phone':
                    Free_Refresh::refresh_phone($Equipement, $Free_API);
                    break;
                case 'player':
                    Free_Refresh::refresh_player($Equipement, $Free_API);
                    break;
                case 'netshare':
                    Free_Refresh::refresh_netshare($Equipement, $Free_API);
                    break;
                case 'network':
                    Free_Refresh::refresh_network_global($Equipement, $Free_API, 'LAN');
                    break;
                case 'networkwifiguest':
                    Free_Refresh::refresh_network_global($Equipement, $Free_API, 'WIFIGUEST');
                    break;
                case 'system':
                    Free_Refresh::refresh_system($Equipement, $Free_API);
                    break;
                case 'wifi':
                    Free_Refresh::refresh_wifi($Equipement, $Free_API);
                    break;
                default:
                    Free_Refresh::refresh_default($Equipement, $Free_API);
                    break;
            }
        }
    }

    private static function refresh_connexion($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('connexion', null, null, null);
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
                        case "rx_max_rate_lte": // toute la partie 4G
                            Free_Refresh::refresh_connexion_4G($Equipement, $Free_API);
                            break;
                        case "ping": // toute la partie CONFIG
                            Free_Refresh::refresh_connexion_Config($Equipement, $Free_API);
                            break;
                        case "link_type": // toute la partie Fibre
                            Free_Refresh::refresh_connexion_FTTH($Equipement, $Free_API);
                            break;
                        case "modulation": // toute la partie XDSL
                            Free_Refresh::refresh_connexion_xdsl($Equipement, $Free_API);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_connexion_4G($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('connexion', null, 1, 'lte/config');
        if ($result != false && $result != 'Aucun module 4G détecté') {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "rx_max_rate_lte":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['lte']['rx_max_rate']);
                            break;
                        case "rx_used_rate_lte":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['lte']['rx_used_rate']);
                            break;
                        case "rx_max_rate_xdsl":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['xdsl']['rx_max_rate']);
                            break;
                        case "rx_used_rate_xdsl":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['xdsl']['rx_used_rate']);
                            break;
                        case "state":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['state']);
                            break;
                        case "tx_max_rate_lte":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['lte']['tx_max_rate']);
                            break;
                        case "tx_used_rate_lte":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['lte']['tx_used_rate']);
                            break;
                        case "tx_max_rate_xdsl":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['xdsl']['tx_max_rate']);
                            break;
                        case "tx_used_rate_xdsl":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['xdsl']['tx_used_rate']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_connexion_Config($Equipement, $Free_API)
    {
        $result =  $Free_API->universal_get('connexion', null, null, 'config');
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "ping":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['ping']);
                            break;
                        case "wol":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['wol']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_connexion_FTTH($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('connexion', null, null, 'ftth');
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "link_type":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['link_type']);
                            break;
                        case "sfp_present":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_present']);
                            break;
                        case "sfp_has_signal":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_has_signal']);
                            break;
                        case "sfp_alim_ok":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_alim_ok']);
                            break;
                        case "sfp_pwr_tx":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_pwr_tx']);
                            break;
                        case "sfp_pwr_rx":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_pwr_rx']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_connexion_xdsl($Equipement, $Free_API)
    {
        $result =  $Free_API->universal_get('connexion', null, null, 'xdsl');
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "modulation":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['status']['modulation']);
                            break;
                        case "protocol":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['status']['protocol']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_disk($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('disk', null, null, null);

        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    foreach ($result['result'] as $disks) {
                        foreach ($disks['partitions'] as $partition) {

                            if ($Command->getLogicalId() != $partition['id']) continue;

                            if ($partition['total_bytes'] != null) {
                                $value = $partition['used_bytes'] / $partition['total_bytes'];
                            } else {
                                $value = 0;
                            }
                            log::add('Freebox_OS', 'debug', '>───────── Occupation de la partition ' . $partition['label'] . ' : ' . $value . ' - Pour le disque  [' . $disks['type'] . '] - ' . $disks['id']);
                            $Equipement->checkAndUpdateCmd($partition['id'], $value);
                        }
                    }
                }
            }
        }
    }

    private static function refresh_download($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('download', null, null, 'stats/');
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "conn_ready":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['conn_ready']);
                            break;
                        case "throttling_is_scheduled":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['throttling_is_scheduled']);
                            break;
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
                            $rx_rate = $result['rx_rate'];
                            if (function_exists('bcdiv'))
                                $rx_rate = bcdiv($result, 1048576, 2);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $rx_rate);
                            break;
                        case "tx_rate":
                            $tx_rate = $result['tx_rate'];
                            if (function_exists('bcdiv'))
                                $tx_rate = bcdiv($tx_rate, 1048576, 2);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $tx_rate);
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
                        case "mode":
                            Free_Refresh::refresh_download_config($Equipement, $Free_API);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_download_config($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('download', null, null, 'config/');
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "mode":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['throttling']['mode']);
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
            log::add('Freebox_OS', 'debug', '>───────── Nb Appels manqués : ' . $result['missed'] . ' -- Liste des appels manqués : ' . $result['list_missed']);
            log::add('Freebox_OS', 'debug', '>───────── Nb Appels reçus : ' . $result['accepted'] . ' -- Liste des appels reçus : ' . $result['list_accepted']);
            log::add('Freebox_OS', 'debug', '>───────── Nb Appels passés : ' . $result['outgoing'] . ' -- Liste des appels passés : ' . $result['list_outgoing']);
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "nbmissed":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['missed']);
                            break;
                        case "nbaccepted":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['accepted']);
                            break;
                        case "nboutgoing":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['outgoing']);
                            break;
                        case "listmissed":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['list_missed']);
                            break;
                        case "listaccepted":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['list_accepted']);
                            break;
                        case "listoutgoing":
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['list_outgoing']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_network_global($Equipement, $Free_API, $_network = 'LAN')
    {
        $value = null;
        if ($_network == 'LAN') {
            $_networkinterface = 'pub';
        } else if ($_network == 'WIFIGUEST') {
            $_networkinterface = 'wifiguest';
        }
        $result_network_ping = $Free_API->universal_get('network_ping', null, null, 'browser/' . $_networkinterface);

        if (!$result_network_ping['success']) {
            log::add('Freebox_OS', 'debug', '│===========> RESULTAT  Requête pas correct : ' . $result_network_ping['success']);
        } else {
            foreach ($Equipement->getCmd('info') as $Command) {

                $result_network = $result_network_ping['result'];
                $_control_id = array_search($Command->getLogicalId(), array_column($result_network, 'id'), true);

                if ($_control_id  === false) {
                    log::add('Freebox_OS', 'debug', '│===========> APPAREIL PAS TROUVE : ' . $Command->getLogicalId() . ' => SUPPRESSION');
                    $Command->remove();
                }
                if (is_object($Command)) {
                    foreach ($result_network as $result) {

                        $cmd = $Equipement->getCmd('info', $result['id']);
                        if ($Command->getLogicalId() != $result['id']) continue;

                        if (isset($result['l3connectivities'])) {
                            foreach ($result['l3connectivities'] as $Ip) {
                                if ($Ip['active']) {
                                    if ($Ip['af'] == 'ipv4') {
                                        $cmd->setConfiguration('IPV4', $Ip['addr']);
                                    } else {
                                        $cmd->setConfiguration('IPV6', $Ip['addr']);
                                    }
                                }
                            }
                        }
                        $cmd->setConfiguration('host_type', $result['host_type']);
                        if (isset($result['active'])) {
                            if ($result['active'] == 'true') {
                                $cmd->setOrder($cmd->getOrder() % 1000);
                                $value = true;
                            } else {
                                $cmd->setOrder($cmd->getOrder() % 1000 + 1000);
                                $value = 0;
                            }
                        } else {
                            $value = 0;
                        }

                        $Equipement->checkAndUpdateCmd($cmd, $value);
                        $cmd->save();
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Id : ' . $result['id'] . ' -- Nom : ' . $result['primary_name'] . ' -- Etat : ' . $value . ' -- Type : ' . $result['host_type']);
                        break;
                    }
                }
            }
        }
    }

    private static function refresh_netshare($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'netshare/samba');
        $resultmac = $Free_API->universal_get('universalAPI', null, null, 'netshare/afp');
        $resultFTP = $Free_API->universal_get('universalAPI', null, null, 'ftp/config');
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "file_share_enabled":
                            log::add('Freebox_OS', 'debug', '│──────────> Partage Fichier Windows : ' . $result['file_share_enabled']);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['file_share_enabled']);
                            break;
                        case "FTP_enabled":
                            log::add('Freebox_OS', 'debug', '│──────────> Partage Fichier Mac : ' . $resultFTP['enabled']);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $resultFTP['enabled']);
                            break;
                        case "mac_share_enabled":
                            log::add('Freebox_OS', 'debug', '│──────────> Partage Fichier Mac : ' . $resultmac['enabled']);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $resultmac['enabled']);
                            break;
                        case "print_share_enabled":
                            log::add('Freebox_OS', 'debug', '│──────────> Partage Imprimante : ' . $result['print_share_enabled']);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['print_share_enabled']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_system($Equipement, $Free_API)
    {
        log::add('Freebox_OS', 'debug', '│──────────> Récupération des valeurs du Système');
        $result = $Free_API->universal_get('system', null, null, null);
        foreach ($Equipement->getCmd('info') as $Command) {
            $logicalId = $Command->getConfiguration('logicalId');

            switch ($Command->getConfiguration('logicalId')) {
                case "sensors":
                    foreach ($result['sensors'] as $system) {
                        if ($Command->getLogicalId() != $system['id']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $Equipement->checkAndUpdateCmd($system['id'], $value);
                        break;
                    }
                    break;
                case "fans":
                    foreach ($result['fans'] as $system) {
                        if ($Command->getLogicalId() != $system['id']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $Equipement->checkAndUpdateCmd($system['id'], $value);
                        break;
                    }
                    break;
                case "expansions":
                    foreach ($result['expansions'] as $system) {
                        if (!isset($system['slot'])) continue;
                        if ($Command->getLogicalId() != $system['slot']) continue;

                        $value = $system['present'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['slot'] . ' -- valeur : ' . $value);
                        $Equipement->checkAndUpdateCmd($system['slot'], $value);
                        break;
                    }
                    break;
                case "model_info":
                    if (is_object($Command)) {
                        switch ($Command->getLogicalId()) {
                            case "model_name":
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['name']);
                                log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $result['model_info']['name']);
                                break;
                            case "pretty_name":
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['pretty_name']);
                                config::save('TYPE_FREEBOX_NAME', $result['model_info']['pretty_name'], 'Freebox_OS');
                                log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $result['model_info']['pretty_name']);
                                break;
                            case "wifi_type":
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['wifi_type']);
                                log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $result['model_info']['wifi_type']);
                                break;
                        }
                    }

                    foreach ($result['model_info'] as $system) {
                        if (!isset($system['slot'])) continue;
                        if ($Command->getLogicalId() != $system['slot']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $Equipement->checkAndUpdateCmd($system['id'], $value);
                        break;
                    }
                    break;
                default:
                    if (is_object($Command)) {
                        switch ($Command->getLogicalId()) {
                            case "mac":
                                log::add('Freebox_OS', 'debug', '│──────────> Update pour Adresse mac : ' . $result['mac']);
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['mac']);
                                break;
                            case "uptime":
                                $_uptime = $result['uptime'];
                                $_uptime = str_replace(' heure ', 'h ', $_uptime);
                                $_uptime = str_replace(' heures ', 'h ', $_uptime);
                                $_uptime = str_replace(' minute ', 'min ', $_uptime);
                                $_uptime = str_replace(' minutes ', 'min ', $_uptime);
                                $_uptime = str_replace(' secondes', 's', $_uptime);
                                $_uptime = str_replace(' seconde', 's', $_uptime);
                                log::add('Freebox_OS', 'debug', '│──────────> Allumée depuis : ' . $_uptime);
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $_uptime);
                                break;
                            case "board_name":
                                log::add('Freebox_OS', 'debug', '│──────────> Board name : ' . $result['board_name']);
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['board_name']);
                                config::save('TYPE_FREEBOX', $result['board_name'], 'Freebox_OS');
                                break;
                            case "serial":
                                log::add('Freebox_OS', 'debug', '│──────────> Numéro de série : ' . $result['serial']);
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['serial']);
                                break;
                            case "firmware_version":
                                log::add('Freebox_OS', 'debug', '│──────────> Version Firmware : ' . $result['firmware_version']);
                                $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['firmware_version']);
                                break;
                            case "4GStatut": // toute la partie 4G
                                Free_Refresh::refresh_system_4G($Equipement, $Free_API);
                                break;
                            case "mode": // toute la partie Info de la Freebox
                                Free_Refresh::refresh_system_lan($Equipement, $Free_API);
                                break;
                        }
                    }
                    break;
            }
        }
    }
    private static function refresh_system_4G($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('connexion', null, null, 'lte/config');
        if ($result != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "4GStatut":
                            log::add('Freebox_OS', 'debug', '│──────────> Etat 4G : ' . $result['enabled']);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['enabled']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_system_lan($Equipement, $Free_API)
    {
        $result =  $Free_API->universal_get('network', null, null, 'config/');

        if ($result != false || isset($result['result']) != false) {
            foreach ($Equipement->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "ip":
                            log::add('Freebox_OS', 'debug', '│──────────> IP : ' . $result['result']['ip']);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['ip']);
                            break;
                        case "mode":
                            log::add('Freebox_OS', 'debug', '│──────────> Mode : ' . $result['result']['mode']);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['mode']);
                            config::save('TYPE_FREEBOX_MODE', $result['result']['mode'], 'Freebox_OS');
                            break;
                        case "name":
                            log::add('Freebox_OS', 'debug', '│──────────> Nom : ' . $result['result']['name']);
                            $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['name']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_default($Equipement, $Free_API)
    {
        $_Alarm_mode_value = null;
        $_Alarm_stat_value = null;
        $_Alarm_mode_value = null;
        $results = $Free_API->universal_get('tiles', $Equipement->getLogicalId(), null, null);

        if ($results != false) {
            foreach ($results as $result) {
                foreach ($result['data'] as $data) {
                    $cmd = $Equipement->getCmd('info', $data['ep_id']);
                    if (!is_object($cmd)) break;

                    if ($data['name'] == 'pushed') {
                        $nb_pushed = count($data['history']);
                        $nb_pushed_k = $nb_pushed - 1;
                        $_value_history = $data['history'][$nb_pushed_k]['value'];
                        log::add('Freebox_OS', 'debug', '│ Nb pushed -1  : ' . $nb_pushed_k . ' -- Valeur historique récente  : ' . $_value_history);
                    };

                    switch ($cmd->getSubType()) {
                        case 'numeric':
                            if ($cmd->getConfiguration('invertnumeric')) {
                                $_value = ($cmd->getConfiguration('maxValue') - $cmd->getConfiguration('minValue')) - $data['value'];
                            } else {
                                if ($data['name'] == 'pushed') {
                                    $_value = $_value_history;
                                } else {
                                    if ($data['value'] == null || $data['value'] == '') {
                                        $_value = 0;
                                    } else {
                                        $_value = $data['value'];
                                    }
                                }
                            }
                            log::add('Freebox_OS', 'debug', '│──────────> Valeur : ' . $_value . ' -- valeur Box : ' . $data['value'] . ' -- valeur Inverser : ' . $cmd->getConfiguration('invertnumeric'));
                            break;
                        case 'string':
                            if ($data['name'] == 'state' && $Equipement->getConfiguration('type') == 'alarm_control') {
                                log::add('Freebox_OS', 'debug', '│──────────> Update commande spécifique pour Homebridge : ' . $Equipement->getConfiguration('type'));
                                $_Alarm_stat_value = '0';
                                $_Alarm_enable_value = '1';

                                switch ($data['value']) {
                                    case 'alarm1_arming':
                                        $_Alarm_mode_value = $Equipement->getConfiguration('ModeAbsent');
                                        log::add('Freebox_OS', 'debug', '│ Mode 1 : Alarme principale (arming)');
                                        break;
                                    case 'alarm1_armed':
                                        $_Alarm_mode_value = $Equipement->getConfiguration('ModeAbsent');
                                        log::add('Freebox_OS', 'debug', '│ Mode 1 : Alarme principale (armed)');
                                        break;
                                    case 'alarm2_arming':
                                        $_Alarm_mode_value = $Equipement->getConfiguration('ModeNuit');
                                        log::add('Freebox_OS', 'debug', '│ Mode 2 : Alarme secondaire (arming)');
                                        break;
                                    case 'alarm2_armed':
                                        $_Alarm_mode_value = $Equipement->getConfiguration('ModeNuit');
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

                            if ($data['ui']['display'] == 'color') {
                                //$color = dechex($data['value']);
                                //log::add('Freebox_OS', 'debug', '│──────────> Value Freebox : ' . $data['value']);
                                //log::add('Freebox_OS', 'debug', '│──────────> Couleur : ' . $color);
                                //$_value = $color;
                                /*$_value = str_pad(dechex($data['value']), 8, "0", STR_PAD_LEFT);
                                $_value2 = str_pad(dechex($data['value']), 8, "0", STR_PAD_LEFT);
                                $result = Free_Color::convertRGBToXY($data['value']);
                                log::add('Freebox_OS', 'debug', '│──────────> x : ' . $result['x'] . ' -- y : ' . $result['y'] . ' -- bri : ' . $result['bri']);
                                $RGB = Free_Color::convertxyToRGB($result['x'], $result['y'], $result['bri']);
                                $rouge = substr($_value2, 1, 2);
                                $vert  = substr($_value2, 3, 2);
                                $bleu  = substr($_value2, 5, 2);
                                log::add('Freebox_OS', 'debug', '│──────────> Value 1 : ' . $_value);
                                log::add('Freebox_OS', 'debug', '│──────────> Value 2 : ' . $_value2);
                                log::add('Freebox_OS', 'debug', '│──────────> rouge : ' . $rouge . ' -- Vert : ' . $vert . ' -- Bleu : ' . $bleu);
                                $_light = hexdec(substr($_value, 7, 2));
                                $_value = '#' . substr($_value2, -6);
                                log::add('Freebox_OS', 'debug', '>──────────> Display de Type : ' . $data['ui']['display'] . ' -- Light : ' . $_light . ' -- Valeur : ' . $_value);
                            */
                            } else {
                                $_value = $data['value'];
                            }
                            break;
                        case 'binary':
                            if ($Equipement->getConfiguration('info') == 'mouv_sensor' && $cmd->getConfiguration('info') == 'mouv_sensor') {
                                log::add('Freebox_OS', 'debug', '│──────────> Inversion valeur pour les détecteurs de mouvement pour être compatible Homebridge ');
                                $_value = false;
                                if ($data['value'] == false) {
                                    $_value = true;
                                }
                            } else {
                                $_value = $data['value'];
                            }

                            break;
                    }
                    $Equipement->checkAndUpdateCmd($data['ep_id'], $_value);
                }
            }
        }
        if ($Equipement->getConfiguration('type2') == 'pir' || $Equipement->getConfiguration('type2') == 'dws' || $Equipement->getConfiguration('type') == 'camera' || $Equipement->getConfiguration('type') == 'alarm') {
            Free_Refresh::refresh_default_nodes($Equipement, $Free_API);
        }
        //log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function refresh_default_nodes($Equipement, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'home/nodes');
        foreach ($result as $_eq) {
            if ($_eq['id'] == $Equipement->getLogicalId()) {
                $_eq_data = $_eq['show_endpoints'];
                foreach ($_eq_data as $Cmd) {
                    foreach ($Equipement->getCmd('info') as $Command) {
                        if ($Command->getLogicalId() == $Cmd['id'] && $Command->getConfiguration('TypeNode') == 'nodes') {
                            if ($Command->getConfiguration('info') == 'mouv_sensor') {
                                $_value = false;
                                if ($Cmd['value'] == false) {
                                    $_value = true;
                                }
                            } else {
                                $_value = $Cmd['value'];
                            }
                            //log::add('Freebox_OS', 'debug', '│──────────> Valeur : ' . $_value . ' -- valeur Box : ' . $Cmd['value'] . ' -- Type Info : ' . $Command->getConfiguration('info'));
                            $Equipement->checkAndUpdateCmd($Cmd['id'], $_value);
                            break;
                        }
                    }
                }
                break;
            }
        }
    }
    private static function refresh_player($Equipement, $Free_API)
    {
        if ($Equipement->getConfiguration('player') == 'OK') {
            $results_playerID = $Free_API->universal_get('player_ID', $Equipement->getConfiguration('action'));
        }

        log::add('Freebox_OS', 'debug', '│──────────> Player OK ? : ' . $Equipement->getConfiguration('player'));
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

        if (isset($results_playerID) && $cmd_powerState) $Equipement->checkAndUpdateCmd($cmd_powerState->getLogicalId(), $results_playerID['power_state']);
    }

    private static function refresh_wifi($Equipement, $Free_API)
    {
        $listmac = $Free_API->mac_filter_list();
        if ($listmac != false) {
            log::add('Freebox_OS', 'debug', '>───────── Liste Noire : ' . $listmac['listmac_blacklist']);
            log::add('Freebox_OS', 'debug', '>───────── Liste Blanche : ' . $listmac['listmac_whitelist']);
        }
        $result_config = $Free_API->universal_get('wifi', null, null, 'config');
        $value = false;
        foreach ($Equipement->getCmd('info') as $Command) {
            if (is_object($Command)) {
                switch ($Command->getLogicalId()) {
                    case "listblack":
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $listmac['listmac_blacklist']);
                        break;
                    case "listwhite":
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $listmac['listmac_whitelist']);
                        break;
                    case "wifiStatut":
                        $value = false;
                        if ($result_config['result']['enabled']) {
                            $value = true;
                        }
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $value);
                        break;
                    case "wifiPlanning":
                        $value = false;
                        $result = $Free_API->universal_get('wifi', null, null, 'planning');
                        if ($result['result']['use_planning']) {
                            $value = true;
                        }
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $value);
                        break;
                    case "wifiWPS":
                        $value = false;
                        $result = $Free_API->universal_get('wifi', null, null, 'wps/config');
                        if ($result['result']['enabled']) {
                            $value = true;
                        }
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $value);
                        break;
                    case "wifimac_filter_state":
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result_config['result']['mac_filter_state']);
                        break;
                    default:
                        $result_ap = $Free_API->universal_get('wifi', null, null, 'ap/' . $Command->getLogicalId());
                        log::add('Freebox_OS', 'debug', '>───────── Status Carte ' . $result_ap['result']['name'] . ' / ' . $Command->getLogicalId() . ' : ' . $result_ap['result']['status']['state']);
                        $Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result_ap['result']['status']['state']);
                        break;
                }
            }
        }
    }
}
