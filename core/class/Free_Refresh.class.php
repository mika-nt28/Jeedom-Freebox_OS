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
        $EqLogics = eqlogic::byId($_freeboxID);
        if ($_freeboxID == 'Tiles_global') {
            Free_Refresh::refresh_titles_global($EqLogics, $Free_API);
            //} else if ($_freeboxID == 'Tiles_global_CmdbyCmd') {
            //  $log_result = false;
            //Free_Refresh::refresh_titles_global_CmdbyCmd($EqLogics, $Free_API, $log_result);
        }
        if (is_object($EqLogics) && $EqLogics->getIsEnable()) {
            if ($EqLogics->getConfiguration('type') == 'player' || $EqLogics->getConfiguration('type') == 'parental' || $EqLogics->getConfiguration('type') == 'freeplug' || $EqLogics->getConfiguration('type') == 'VM') {
                $refresh = $EqLogics->getConfiguration('type');
            } else {
                $refresh = $EqLogics->getLogicalId();
            }
            switch ($refresh) {
                case 'airmedia':
                    break;
                case 'connexion':
                    Free_Refresh::refresh_connexion($EqLogics, $Free_API);
                    break;
                case 'disk':
                    Free_Refresh::refresh_disk($EqLogics, $Free_API);
                    break;
                case 'downloads':
                    Free_Refresh::refresh_download($EqLogics, $Free_API);
                    break;
                case 'freeplug':
                    Free_Refresh::refresh_freeplug($EqLogics, $Free_API);
                    break;
                case 'LCD':
                    Free_Refresh::refresh_LCD($EqLogics, $Free_API);
                    break;
                case 'homeadapters':
                    $result = $Free_API->universal_get('universalAPI', null, null, 'home/adapters');
                    foreach ($EqLogics->getCmd('info') as $Command) {
                        foreach ($result as $Cmd) {
                            if ($Cmd['id'] == $Command->getLogicalId()) {
                                if ($Cmd['status'] == 'active') {
                                    $homeadapters_value = 1;
                                } else {
                                    $homeadapters_value = 0;
                                }
                                log::add('Freebox_OS', 'debug', '│──────────> Update pour Id : ' . $Cmd['id'] . ' -- Nom : ' . $Cmd['label'] . ' -- Etat : ' . $homeadapters_value);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $homeadapters_value);
                            }
                        }
                    }
                    break;
                case 'parental':
                    foreach ($EqLogics->getCmd('info') as $Command) {
                        $results = $Free_API->universal_get('parental', $EqLogics->getConfiguration('action'));
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $results['current_mode']);
                    }
                    break;
                case 'phone':
                    Free_Refresh::refresh_phone($EqLogics, $Free_API);
                    break;
                case 'player':
                    Free_Refresh::refresh_player($EqLogics, $Free_API);
                    break;
                case 'netshare':
                    Free_Refresh::refresh_netshare($EqLogics, $Free_API);
                    break;
                case 'network':
                    Free_Refresh::refresh_network_global($EqLogics, $Free_API, 'LAN');
                    break;
                case 'networkwifiguest':
                    Free_Refresh::refresh_network_global($EqLogics, $Free_API, 'WIFIGUEST');
                    break;
                case 'system':
                    Free_Refresh::refresh_system($EqLogics, $Free_API);
                    break;
                case 'VM':
                    Free_Refresh::refresh_VM($EqLogics, $Free_API);
                    break;
                case 'wifi':
                    Free_Refresh::refresh_wifi($EqLogics, $Free_API);
                    break;
                default:
                    //if (config::byKey('FREEBOX_TILES_CmdbyCmd', 'Freebox_OS') == 1) {
                    //  Free_Refresh::refresh_titles_CmdbyCmd($EqLogics, $Free_API, true);
                    //} else {
                    Free_Refresh::refresh_titles($EqLogics, $Free_API);
                    //}


                    break;
            }
        }
    }

    private static function refresh_connexion($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('connexion', null, null, null);
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "bandwidth_down":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['bandwidth_down']);
                            break;
                        case "bandwidth_up":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['bandwidth_up']);
                            break;
                        case "ipv4":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['ipv4']);
                            break;
                        case "ipv6":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['ipv6']);
                            break;
                        case "media":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['media']);
                            break;
                        case "rate_down":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['rate_down']);
                            break;
                        case "rate_up":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['rate_up']);
                            break;
                        case "state":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['state']);
                            break;
                        case "rx_max_rate_lte": // toute la partie 4G
                            Free_Refresh::refresh_connexion_4G($EqLogics, $Free_API);
                            break;
                        case "ping": // toute la partie CONFIG
                            Free_Refresh::refresh_connexion_Config($EqLogics, $Free_API);
                            break;
                        case "sfp_present": // toute la partie Fibre
                            Free_Refresh::refresh_connexion_FTTH($EqLogics, $Free_API);
                            break;
                        case "modulation": // toute la partie XDSL
                            Free_Refresh::refresh_connexion_xdsl($EqLogics, $Free_API);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_connexion_4G($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('connexion', null, 1, 'lte/config');
        if ($result != false && $result != 'Aucun module 4G détecté') {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "rx_max_rate_lte":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['lte']['rx_max_rate']);
                            break;
                        case "rx_used_rate_lte":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['lte']['rx_used_rate']);
                            break;
                        case "rx_max_rate_xdsl":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['xdsl']['rx_max_rate']);
                            break;
                        case "rx_used_rate_xdsl":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['xdsl']['rx_used_rate']);
                            break;
                        case "state":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['state']);
                            break;
                        case "tx_max_rate_lte":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['lte']['tx_max_rate']);
                            break;
                        case "tx_used_rate_lte":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['lte']['tx_used_rate']);
                            break;
                        case "tx_max_rate_xdsl":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['xdsl']['tx_max_rate']);
                            break;
                        case "tx_used_rate_xdsl":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['tunnel']['xdsl']['tx_used_rate']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_connexion_Config($EqLogics, $Free_API)
    {
        $result =  $Free_API->universal_get('connexion', null, null, 'config');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "ping":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['ping']);
                            break;
                        case "wol":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['wol']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_connexion_FTTH($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('connexion', null, null, 'ftth');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "link_type":
                            if (isset($result['link_type'])) {
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['link_type']);
                            } else {
                                Free_Refresh::removeLogicId($EqLogics, $Command->getLogicalId());
                            }
                            break;
                        case "sfp_present":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_present']);
                            break;
                        case "sfp_has_signal":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_has_signal']);
                            break;
                        case "sfp_alim_ok":
                            if (isset($result['sfp_alim_ok'])) {
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_alim_ok']);
                            } else {
                                Free_Refresh::removeLogicId($EqLogics, $Command->getLogicalId());
                            }
                            break;
                        case "sfp_pwr_tx":
                            if (isset($result['sfp_pwr_tx'])) {
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_pwr_tx']);
                            } else {
                                Free_Refresh::removeLogicId($EqLogics, $Command->getLogicalId());
                            }
                            break;
                        case "sfp_pwr_rx":
                            if (isset($result['sfp_pwr_rx'])) {
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_pwr_rx']);
                            } else {
                                Free_Refresh::removeLogicId($EqLogics, $Command->getLogicalId());
                            }
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_connexion_xdsl($EqLogics, $Free_API)
    {
        $result =  $Free_API->universal_get('connexion', null, null, 'xdsl');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "modulation":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['status']['modulation']);
                            break;
                        case "protocol":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['status']['protocol']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_disk($EqLogics, $Free_API)
    {
        //$result = $Free_API->universal_get('disk', null, null, null);
        $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    foreach ($result as $disks) {
                        switch ($Command->getLogicalId()) {
                            case $disks['id'] . '_temp':
                                log::add('Freebox_OS', 'debug', '>───────── Disque [' . $disks['serial'] . ' - ' . $disks['id'] . '] '  . 'Température :' . $disks['temp'] . '°C');
                                $EqLogics->checkAndUpdateCmd($disks['id'] . '_temp', $disks['temp']);
                                break;
                            case $disks['id'] . '_spinning':
                                log::add('Freebox_OS', 'debug', '>───────── Disque [' . $disks['serial'] . ' - ' . $disks['id'] . '] '  . 'Tourne :' . $disks['spinning']);
                                $EqLogics->checkAndUpdateCmd($disks['id'] . '_spinning', $disks['spinning']);
                                break;
                        }
                        foreach ($disks['partitions'] as $partition) {
                            if ($Command->getLogicalId() != $partition['id']) continue;

                            if ($partition['total_bytes'] != null) {
                                $value = $partition['used_bytes'] / $partition['total_bytes'];
                            } else {
                                $value = 0;
                            }
                            log::add('Freebox_OS', 'debug', '>───────── Occupation de la partition ' . $partition['label'] . ' : ' . $value . ' - Pour le disque  [' . $disks['type'] . '] - ' . $disks['id']);

                            $EqLogics->checkAndUpdateCmd($partition['id'], $value);
                        }
                    }
                }
            }
        }
        $Type_box = config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS');
        if ($Type_box == 'OK') {
            $result = $Free_API->universal_get('universalAPI', null, null, 'storage/raid');
            if (is_object($Command)) {
                foreach ($result as $raid) {
                    foreach ($EqLogics->getCmd('info') as $Command) {
                        switch ($Command->getLogicalId()) {
                            case $raid['id'] . '_state':
                                log::add('Freebox_OS', 'debug', '>─────────  Raid_' . $raid['id'] . '_state : ' . $raid['state']);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $raid['state']);
                                break;
                            case $raid['id'] . '_sync_action':
                                log::add('Freebox_OS', 'debug', '>─────────  Raid_' . $raid['id'] . '_sync_action : ' . $raid['sync_action']);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $raid['sync_action']);
                                break;
                        }
                    }
                }
            }
        }
    }

    private static function refresh_download($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('download', null, null, 'stats/');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "conn_ready":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['conn_ready']);
                            break;
                        case "throttling_is_scheduled":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['throttling_is_scheduled']);
                            break;
                        case "nb_tasks":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks']);
                            break;
                        case "nb_tasks_downloading":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_downloading']);
                            break;
                        case "nb_tasks_done":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_done']);
                            break;
                        case "nb_rss":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_rss']);
                            break;
                        case "nb_rss_items_unread":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_rss_items_unread']);
                            break;
                        case "rx_rate":
                            $rx_rate = $result['rx_rate'];
                            if (function_exists('bcdiv'))
                                $rx_rate = bcdiv($result, 1048576, 2);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $rx_rate);
                            break;
                        case "tx_rate":
                            $tx_rate = $result['tx_rate'];
                            if (function_exists('bcdiv'))
                                $tx_rate = bcdiv($tx_rate, 1048576, 2);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $tx_rate);
                            break;
                        case "nb_tasks_active":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_active']);
                            break;
                        case "nb_tasks_stopped":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_stopped']);
                            break;
                        case "nb_tasks_queued":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_queued']);
                            break;
                        case "nb_tasks_repairing":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_repairing']);
                            break;
                        case "nb_tasks_extracting":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_extracting']);
                            break;
                        case "nb_tasks_error":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_error']);
                            break;
                        case "nb_tasks_checking":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_checking']);
                            break;
                        case "mode":
                            Free_Refresh::refresh_download_config($EqLogics, $Free_API);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_download_config($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('download', null, null, 'config/');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "mode":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['throttling']['mode']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_LCD($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'lcd/config/');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "orientation":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['orientation']);
                            break;
                        case "orientation_forced":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['orientation']);
                            break;
                        case "brightness":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['brightness']);
                            break;
                        case "hide_wifi_key":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['hide_wifi_key']);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_phone($EqLogics, $Free_API)
    {
        $result = $Free_API->nb_appel_absence();
        $list_missed = null;
        $list_accepted = null;
        $list_outgoing = null;
        if ($result != false) {
            if ($result['list_missed'] != null) {
                $list_missed = ' -- Liste des appels manqués : ' . $result['list_missed'];
            }
            if ($result['list_accepted'] != null) {
                $list_accepted = ' -- Liste des appels reçus : ' . $result['list_accepted'];
            }
            if ($result['list_outgoing'] != null) {
                $list_outgoing = ' -- Liste des appels passés : ' . $result['list_outgoing'];
            }
            log::add('Freebox_OS', 'debug', '>───────── Nb Appels manqués : ' . $result['missed'] . $list_missed);
            log::add('Freebox_OS', 'debug', '>───────── Nb Appels reçus : ' . $result['accepted'] . $list_accepted);
            log::add('Freebox_OS', 'debug', '>───────── Nb Appels passés : ' . $result['outgoing'] . $list_outgoing);
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "nbmissed":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['missed']);
                            break;
                        case "nbaccepted":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['accepted']);
                            break;
                        case "nboutgoing":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['outgoing']);
                            break;
                        case "listmissed":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['list_missed']);
                            break;
                        case "listaccepted":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['list_accepted']);
                            break;
                        case "listoutgoing":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['list_outgoing']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_network_global($EqLogics, $Free_API, $_network = 'LAN')
    {
        $value = null;
        if ($_network == 'LAN') {
            $_networkinterface = 'pub';
        } else if ($_network == 'WIFIGUEST') {
            $_networkinterface = 'wifiguest';
        }
        $result_network_ping = $Free_API->universal_get('network_ping', null, null, 'browser/' . $_networkinterface);
        $order_count_active = 100;
        $order_count_noactive = 400;
        if (!$result_network_ping['success']) {
            log::add('Freebox_OS', 'debug', '│===========> RESULTAT  Requête pas correct : ' . $result_network_ping['success']);
        } else {
            foreach ($EqLogics->getCmd('info') as $Command) {

                $result_network = $result_network_ping['result'];
                $_control_id = array_search($Command->getLogicalId(), array_column($result_network, 'id'), true);

                if ($_control_id  === false) {
                    log::add('Freebox_OS', 'debug', '│===========> APPAREIL PAS TROUVE : ' . $Command->getLogicalId() . ' => SUPPRESSION');
                    $Command->remove();
                }
                if (is_object($Command)) {
                    foreach ($result_network as $result) {

                        $cmd = $EqLogics->getCmd('info', $result['id']);
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
                                $order_count_active++;
                                //$cmd->setOrder($order_count_active);
                                $cmd->save();
                                $cmd->setOrder($cmd->getOrder() % $order_count_active);
                                $value = true;
                            } else {
                                $order_count_noactive++;
                                //$cmd->setOrder($order_count_noactive);
                                $cmd->save();
                                $cmd->setOrder($cmd->getOrder() % $order_count_noactive);
                                $value = 0;
                            }
                        } else {
                            $value = 0;
                        }

                        $EqLogics->checkAndUpdateCmd($cmd, $value);
                        $cmd->save();
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Id : ' . $result['id'] . ' -- Nom : ' . $result['primary_name'] . ' -- Etat : ' . $value . ' -- Type : ' . $result['host_type']);
                        break;
                    }
                }
            }
        }
    }

    private static function refresh_netshare($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'netshare/samba');
        $resultmac = $Free_API->universal_get('universalAPI', null, null, 'netshare/afp');
        $resultFTP = $Free_API->universal_get('universalAPI', null, null, 'ftp/config');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "file_share_enabled":
                            log::add('Freebox_OS', 'debug', '│──────────> Partage Fichier Windows : ' . $result['file_share_enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['file_share_enabled']);
                            break;
                        case "FTP_enabled":
                            log::add('Freebox_OS', 'debug', '│──────────> Partage Fichier FTP : ' . $resultFTP['enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $resultFTP['enabled']);
                            break;
                        case "mac_share_enabled":
                            log::add('Freebox_OS', 'debug', '│──────────> Partage Fichier Mac : ' . $resultmac['enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $resultmac['enabled']);
                            break;
                        case "print_share_enabled":
                            log::add('Freebox_OS', 'debug', '│──────────> Partage Imprimante : ' . $result['print_share_enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['print_share_enabled']);
                            break;
                        case "smbv2_enabled":
                            if (isset($result['smbv2_enabled'])) {
                                log::add('Freebox_OS', 'debug', '│──────────> Etat Samba SMBv2 : ' . $result['smbv2_enabled']);
                                if ($result['smbv2_enabled'] == true) {
                                    Free_Refresh::removeLogicId($EqLogics, 'print_share_enabledOn');
                                    Free_Refresh::removeLogicId($EqLogics, 'print_share_enabledOff');
                                    Free_Refresh::removeLogicId($EqLogics, 'print_share_enabled');
                                }
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['smbv2_enabled']);
                            } else {
                                Free_Refresh::removeLogicId($EqLogics, 'smbv2_enabledOn');
                                Free_Refresh::removeLogicId($EqLogics, 'smbv2_enabledOff');
                                Free_Refresh::removeLogicId($EqLogics, 'smbv2_enabled');
                            }


                            break;
                    }
                }
            }
        }
    }
    private static function removeLogicId($eqLogic, $from)
    {
        //  suppression fonction
        $cmd = $eqLogic->getCmd(null, $from);
        if (is_object($cmd)) {
            $cmd->remove();
        }
    }

    private static function refresh_system($EqLogics, $Free_API)
    {
        log::add('Freebox_OS', 'debug', '│──────────> Récupération des valeurs du Système');
        $result = $Free_API->universal_get('system', null, null, null);
        foreach ($EqLogics->getCmd('info') as $Command) {
            $logicalId = $Command->getConfiguration('logicalId');

            switch ($Command->getConfiguration('logicalId')) {
                case "sensors":
                    foreach ($result['sensors'] as $system) {
                        if ($Command->getLogicalId() != $system['id']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $EqLogics->checkAndUpdateCmd($system['id'], $value);
                        break;
                    }
                    break;
                case "fans":
                    foreach ($result['fans'] as $system) {
                        if ($Command->getLogicalId() != $system['id']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $EqLogics->checkAndUpdateCmd($system['id'], $value);
                        break;
                    }
                    break;
                case "expansions":
                    foreach ($result['expansions'] as $system) {
                        if (!isset($system['slot'])) continue;
                        if ($Command->getLogicalId() != $system['slot']) continue;

                        $value = $system['present'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['slot'] . ' -- valeur : ' . $value);
                        $EqLogics->checkAndUpdateCmd($system['slot'], $value);
                        break;
                    }
                    break;
                case "model_info":
                    if (is_object($Command)) {
                        switch ($Command->getLogicalId()) {
                            case "model_name":
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['name']);
                                log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $result['model_info']['name']);
                                break;
                            case "pretty_name":
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['pretty_name']);
                                config::save('TYPE_FREEBOX_NAME', $result['model_info']['pretty_name'], 'Freebox_OS');
                                log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $result['model_info']['pretty_name']);
                                break;
                            case "wifi_type":
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['wifi_type']);
                                log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $result['model_info']['wifi_type']);
                                break;
                        }
                    }

                    foreach ($result['model_info'] as $system) {
                        if (!isset($system['slot'])) continue;
                        if ($Command->getLogicalId() != $system['slot']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $EqLogics->checkAndUpdateCmd($system['id'], $value);
                        break;
                    }
                    break;
                default:
                    if (is_object($Command)) {
                        switch ($Command->getLogicalId()) {
                            case "mac":
                                log::add('Freebox_OS', 'debug', '│──────────> Update pour Adresse mac : ' . $result['mac']);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['mac']);
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
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $_uptime);
                                break;
                            case "board_name":
                                log::add('Freebox_OS', 'debug', '│──────────> Board name : ' . $result['board_name']);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['board_name']);
                                config::save('TYPE_FREEBOX', $result['board_name'], 'Freebox_OS');
                                break;
                            case "serial":
                                log::add('Freebox_OS', 'debug', '│──────────> Numéro de série : ' . $result['serial']);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['serial']);
                                break;
                            case "firmware_version":
                                log::add('Freebox_OS', 'debug', '│──────────> Version Firmware : ' . $result['firmware_version']);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['firmware_version']);
                                break;
                            case "4GStatut": // toute la partie 4G
                                Free_Refresh::refresh_system_4G($EqLogics, $Free_API);
                                break;
                            case "mode": // toute la partie Info de la Freebox
                                Free_Refresh::refresh_system_lan($EqLogics, $Free_API);
                                break;
                            case "lang": // toute la partie Info de la Freebox
                                Free_Refresh::refresh_system_lang($EqLogics, $Free_API);
                                break;
                        }
                    }
                    break;
            }
        }
    }
    private static function refresh_system_4G($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('connexion', null, null, 'lte/config');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "4GStatut":
                            log::add('Freebox_OS', 'debug', '│──────────> Etat 4G : ' . $result['enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['enabled']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_system_lan($EqLogics, $Free_API)
    {
        $result =  $Free_API->universal_get('network', null, null, 'config/');

        if ($result != false || isset($result['result']) != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "ip":
                            log::add('Freebox_OS', 'debug', '│──────────> IP : ' . $result['result']['ip']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['ip']);
                            break;
                        case "mode":
                            log::add('Freebox_OS', 'debug', '│──────────> Mode : ' . $result['result']['mode']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['mode']);
                            config::save('TYPE_FREEBOX_MODE', $result['result']['mode'], 'Freebox_OS');
                            break;
                        case "name":
                            log::add('Freebox_OS', 'debug', '│──────────> Nom : ' . $result['result']['name']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['name']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_system_lang($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'lang');
        $result_config2 = $Free_API->universal_get('universalAPI', null, null, 'notif/targets');
        if ($result != false || isset($result['result']) != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "lang":
                            log::add('Freebox_OS', 'debug', '│──────────> lang : ' . $result['lang']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['lang']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_titles_string($EqLogic, $data, $log_result, $Cmd)
    {
        $_Alarm_mode_value = null;
        $_Alarm_stat_value = null;

        if ($data['name'] == 'state' && ($EqLogic->getConfiguration('type') == 'alarm_control' || $EqLogic->getConfiguration('type2') == 'alarm')) {

            $_Alarm_stat_value = '0';
            $_Alarm_enable_value = '1';
            $_Alarm_log = null;

            switch ($data['value']) {
                case 'alarm1_arming':
                    $_Alarm_mode_value = $EqLogic->getConfiguration('ModeAbsent');
                    $_Alarm_log = 'Mode 1 : Alarme principale (arming)';
                    break;
                case 'alarm1_armed':
                    $_Alarm_mode_value = $EqLogic->getConfiguration('ModeAbsent');
                    $_Alarm_log = 'Mode 1 : Alarme principale (armed)';
                    break;
                case 'alarm2_arming':
                    $_Alarm_mode_value = $EqLogic->getConfiguration('ModeNuit');
                    $_Alarm_log = 'Mode 2 : Alarme secondaire (arming)';
                    break;
                case 'alarm2_armed':
                    $_Alarm_mode_value = $EqLogic->getConfiguration('ModeNuit');
                    $_Alarm_log = 'Mode 2 : Alarme secondaire (armed)';
                    break;
                case 'alert':
                    $_Alarm_stat_value = '1';
                    $_Alarm_log = 'Alarme';
                    break;
                case 'alarm1_alert_timer':
                    $_Alarm_stat_value = '1';
                    $_Alarm_log = 'Alarme principale - timer';
                    break;
                case 'alarm2_alert_timer':
                    $_Alarm_stat_value = '1';
                    $_Alarm_log = 'Alarme secondaire - timer';
                    break;
                case 'idle':
                    $_Alarm_enable_value = '0';
                    $_Alarm_log = 'Alarme désactivée';
                    break;
                default:
                    $_Alarm_mode_value = null;
                    $_Alarm_log = 'Aucun Mode';
                    break;
            }
            if ($log_result == true) {
                log::add('Freebox_OS', 'debug', '│──────────> Update commande spécifique pour Homebridge : ' . $EqLogic->getConfiguration('type') . ' -- ' . $_Alarm_log);
            }
            $EqLogic->checkAndUpdateCmd('ALARM_state', $_Alarm_stat_value);
            $EqLogic->checkAndUpdateCmd('ALARM_enable', $_Alarm_enable_value);
            $EqLogic->checkAndUpdateCmd('ALARM_mode', $_Alarm_mode_value);
            if ($log_result == true) {
                log::add('Freebox_OS', 'debug', '│ Statut (ALARM_state) = ' . $_Alarm_stat_value . ' / Actif (ALARM_enable) = ' . $_Alarm_enable_value . ' / Mode (ALARM_mode) = ' . $_Alarm_mode_value);
            }
        };

        if ($data['ui']['display'] == 'color') {
            $color = Free_Color::hexToRgb($data['value']);
            //log::add('Freebox_OS', 'debug', '│──────────> Value Freebox : ' . $data['value']);
            //log::add('Freebox_OS', 'debug', '│──────────> Couleur : ' . $color);
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
            $_value = $data['value'];
        } else {
            if ($data['name'] == 'error' && ($EqLogic->getConfiguration('type') == 'alarm_control' || $EqLogic->getConfiguration('type2') == 'alarm')) {
                if ($data['value'] == null) {
                    $_value = 'Pas de message d\'erreur';
                    if ($log_result == true) {
                        log::add('Freebox_OS', 'debug', '│──────────> Update commande spécifique Message erreur : ' . $EqLogic->getConfiguration('type') . ' -- ' . $data['value']);
                    }
                }
            } else {
                $_value = $data['value'];
            }
        }
        return $_value;
    }
    private static function refresh_titles_bool($EqLogic, $data, $log_result, $Cmd)
    {
        if ($EqLogic->getConfiguration('info') == 'mouv_sensor' && $Cmd->getConfiguration('info') == 'mouv_sensor') {
            if ($log_result == true) {
                log::add('Freebox_OS', 'debug', '│──────────> Inversion de la valeur pour les détecteurs de mouvement pour être compatible avec Homebridge ');
            }
            $_value = false;
            if ($data['value'] == false) {
                $_value = true;
            }
        } else {
            $_value = $data['value'];
        }
        //log::add('Freebox_OS', 'debug', '│──────────> ' . $logicalId_name . ' (' . $_cmd_id . ') = ' . $_value);
        return $_value;
    }
    private static function refresh_titles_int($EqLogic, $data, $log_result, $Cmd)
    {
        if ($Cmd->getDisplay('invertBinary') == 1) {
            if ($data['value'] === $Cmd->getConfiguration('maxValue')) {
                $_value = $Cmd->getConfiguration('minValue');
            } else if ($data['value'] === $Cmd->getConfiguration('minValue')) {
                $_value = $Cmd->getConfiguration('maxValue');
            } else {
                $_value = ($Cmd->getConfiguration('maxValue') - $Cmd->getConfiguration('minValue')) - $data['value'];
            }
        } else {
            if ($data['name'] == 'pushed') {
                $nb_pushed = count($data['history']);
                $nb_pushed_k = $nb_pushed - 1;
                $_value = $data['history'][$nb_pushed_k]['value'];
                if ($log_result == true) {
                    log::add('Freebox_OS', 'debug', '│ Nb pushed -1  : ' . $nb_pushed_k . ' -- Valeur historique récente  : ' . $_value);
                }
            } else {
                if ($data['value'] == null || $data['value'] == '') {
                    $_value = '0';
                } else {
                    $_value = $data['value'];
                }
            }
        }
        if ($log_result == true) {
            //log::add('Freebox_OS', 'debug', '│──────────> ' . $logicalId_name . ' (' . $_cmd_id . ') = ' . $_value . ' -- valeur Box = ' . $data['value'] . ' -- Etat Option Inverser = ' . $Cmd->getDisplay('invertBinary'));
        }
        return $_value;
    }
    private static function refresh_titles_CmdbyCmd($EqLogic, $Free_API, $log_result = false)
    {
        $_eqName = $EqLogic->getName();
        $EqLogicID = eqlogic::byId($EqLogic->getId());
        if ($log_result == true) {
            log::add('Freebox_OS', 'debug', '******************** UPDATE POUR  : ' . $_eqName . ' **************************************** ');
        }
        foreach ($EqLogic->getCmd('info') as $Cmd) {
            if ($Cmd->getLogicalId() == 'ALARM_enable' || $Cmd->getLogicalId() == 'ALARM_state' || $Cmd->getLogicalId() == 'ALARM_mode') {
                //break;
            } else {

                $result = $Free_API->universal_get('universalAPI', $Cmd->getLogicalId(), null, 'home/tileset/' . $EqLogic->getLogicalId(), true, $log_result);
                if ($Cmd->getLogicalId() == '11' && ($EqLogic->getConfiguration('type') == 'alarm_control' || $EqLogic->getConfiguration('type2') == 'alarm')) {
                    $Name = 'state';
                    if ($log_result == true) {
                        log::add('Freebox_OS', 'debug', '│ =========================> ───────── OPTION SPECIFIQUE POUR STATE ALARME : ' . $EqLogic->getLogicalId() . ' - '  . $_eqName);
                    }
                } else if ($Cmd->getLogicalId() == '2' && ($EqLogic->getConfiguration('type') == 'alarm_remote' || $EqLogic->getConfiguration('type2') == 'kfb')) {
                    $Name = 'pushed';
                    if ($log_result == true) {
                        log::add('Freebox_OS', 'debug', '│ =========================> ───────── OPTION SPECIFIQUE POUR BOUTON TELECOMMANDE : ' . $EqLogic->getLogicalId() . ' - '  . $_eqName);
                    }
                } else if ($Cmd->getLogicalId() == '13' && ($EqLogic->getConfiguration('type') == 'alarm_control' || $EqLogic->getConfiguration('type2') == 'alarm')) {
                    $Name = 'error';
                    if ($log_result == true) {
                        log::add('Freebox_OS', 'debug', '│ =========================> ───────── OPTION SPECIFIQUE POUR ERREUR ALARME : ' . $EqLogic->getLogicalId() . ' - '  . $_eqName);
                    }
                } else {
                    $Name = $Cmd->getName();
                }
                $data = array(
                    "name" => $Name,
                    "value" => $result['value'],
                    "value_type" => $result['value_type'],
                    "history" => $result['history']
                );
                switch ($result['value_type']) {
                    case 'string':
                        $_value = Free_Refresh::refresh_titles_string($EqLogic, $data, $log_result, $Cmd);
                        $EqLogicID->checkAndUpdateCmd($Cmd, $_value);
                        if ($log_result == true) {
                            log::add('Freebox_OS', 'debug', '│ Type de données : ' . $result['value_type'] . ' (' . $Cmd->getLogicalId() . ' - ' . $Cmd->getName() . ') : '  . $_value . ' [' . $EqLogic->getLogicalId() . ' '  . $_eqName . ']');
                        }
                        break;
                    case 'bool':
                        $_value = Free_Refresh::refresh_titles_bool($EqLogic, $data, $log_result, $Cmd);
                        $EqLogicID->checkAndUpdateCmd($Cmd, $_value);
                        $EqLogicID->checkAndUpdateCmd($Cmd, $_value);
                        if ($log_result == true) {
                            log::add('Freebox_OS', 'debug', '│ Type de données : ' . $result['value_type'] . ' (' . $Cmd->getLogicalId() . ' - ' . $Cmd->getName() . ') : '  . $_value . ' [' . $EqLogic->getLogicalId() . ' '  . $_eqName . ']');
                        }
                        break;
                    case 'int':
                        $_value = Free_Refresh::refresh_titles_int($EqLogic, $data, $log_result, $Cmd);
                        $EqLogicID->checkAndUpdateCmd($Cmd, $_value);
                        if ($Cmd->getGeneric_type() == 'BATTERY') {
                            $EqLogic->batteryStatus($_value);
                        }
                        $EqLogicID->checkAndUpdateCmd($Cmd, $_value);
                        if ($log_result == true) {
                            log::add('Freebox_OS', 'debug', '│ Type de données : ' . $result['value_type'] . ' (' . $Cmd->getLogicalId() . ' - ' . $Cmd->getName() . ') : '  . $_value . ' [' . $EqLogic->getLogicalId() . ' '  . $_eqName . ']');
                        }
                        break;
                    default:
                        if ($log_result == true) {
                            log::add('Freebox_OS', 'debug', '│ Aucun Traitement POUR CE TYPE DE DONNEES : ' .  $result['value_type']);
                        }
                        break;
                }
            }
        }
        if ($log_result == true) {
            log::add('Freebox_OS', 'debug', '**************************************************************************************************** ');
        }
    }
    private static function refresh_titles_global_CmdbyCmd($EqLogics, $Free_API, $log_result)
    {
        $EqLogics = eqLogic::byType('Freebox_OS');
        foreach ($EqLogics as $EqLogic) {
            if ($EqLogic->getConfiguration('eq_group') == 'tiles' || $EqLogic->getConfiguration('eq_group') == 'nodes') {
                $_eqName = $EqLogic->getName();
                if ($log_result == true) {
                    log::add('Freebox_OS', 'debug', '=========================> ───────── UPDATE EQUIPEMENT : ' . $EqLogic->getLogicalId() . ' - '  . $_eqName);
                }
                Free_Refresh::refresh_titles_CmdbyCmd($EqLogic, $Free_API, false);
            }
        }
    }
    private static function refresh_titles_global($EqLogics, $Free_API)
    {

        $boucle_num = 1; // 1 = Tiles - 2 = Node 
        while ($boucle_num <= 2) {
            if ($boucle_num == 2) {
                $result = $Free_API->universal_get('universalAPI', null, null, 'home/nodes', false, false);
            } else if ($boucle_num == 1) {
                $result = $Free_API->universal_get('universalAPI', null, null, 'home/tileset/all', false, false);
            }

            foreach ($result as $node) {
                if ($boucle_num == 2) {
                    $_eq_type = $node['category'];
                    $_eq_data = $node['show_endpoints'];
                    $_eq_node = $node['id'];
                    $_type_boucle = 'NODE';
                } else if ($boucle_num == 1) {
                    $_eq_type = $node['type'];
                    $_eq_data = $node['data'];
                    $_eq_node = $node['node_id'];
                    $_type_boucle = 'TILES';
                }
                //log::add('Freebox_OS', 'debug', '******************** Update Boucle : ' . $_type_boucle . ' ******************** ');
                if ($boucle_num == 1 && $_eq_type == 'camera') {
                } else {
                    $EqLogic = eqLogic::byLogicalId($_eq_node, 'Freebox_OS');
                    if (is_object($EqLogic)) {
                        if ($EqLogic->getIsEnable()) {
                            //log::add('Freebox_OS', 'debug', '******************** Update de : ' . $node['label'] . ' / ' . $_eq_node . ' / ' . $_eq_type  . ' (' . $_type_boucle . ') ******************** ');
                            foreach ($_eq_data as $data) {
                                if ($boucle_num == 1) {
                                    $_cmd_id = $data['ep_id'];
                                    $cmd = $EqLogic->getCmd('info', $_cmd_id);
                                    if (is_object($cmd)) {
                                        Free_Refresh::refresh_titles_CMD($cmd, $EqLogic, $data, $_cmd_id, false);
                                    }
                                } else {
                                    $_cmd_id = $data['id'];
                                    $cmd = $EqLogic->getCmd('info', $_cmd_id);
                                    if (is_object($cmd)) {
                                        if ($cmd->getConfiguration('TypeNode') == 'nodes') {
                                            if ($EqLogic->getConfiguration('type2') == 'pir' || $EqLogic->getConfiguration('type2') == 'kfb' || $EqLogic->getConfiguration('type2') == 'dws' || $EqLogic->getConfiguration('type2') == 'alarm' || $EqLogic->getConfiguration('type') == 'camera'  || $EqLogic->getConfiguration('type2') == 'basic_shutter' || $EqLogic->getConfiguration('type2') == 'opener' || $EqLogics['category'] == 'shutter'  || $EqLogic->getConfiguration('type') == 'light') {
                                                Free_Refresh::refresh_titles_CMD($cmd, $EqLogic, $data, $_cmd_id, false);
                                            }
                                        } else {
                                            //log::add('Freebox_OS', 'debug', '│──────────> Id : ' . $_cmd_id . ' -- AUCUNE MISE A JOUR A FAIRE AVEC LA REQUETE NODE');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $boucle_num++;
        }
    }
    private static function refresh_titles_CMD($cmd, $EqLogic, $data, $_cmd_id, $log_result)
    {
        $_Alarm_mode_value = null;
        $_Alarm_stat_value = null;
        $logicalId_name = $cmd->getName();

        if ($data['name'] == 'pushed') {
            $nb_pushed = count($data['history']);
            $nb_pushed_k = $nb_pushed - 1;
            $_value_history = $data['history'][$nb_pushed_k]['value'];
            if ($log_result == true) {
                log::add('Freebox_OS', 'debug', '│ Nb pushed -1  : ' . $nb_pushed_k . ' -- Valeur historique récente  : ' . $_value_history);
            }
        };
        if ($data['name'] == 'battery' || $data['name'] == 'battery_warning') {
            $_value = $data['value'];
            $EqLogic->batteryStatus($_value);
        }

        switch ($cmd->getSubType()) {
            case 'numeric':
                $_value = Free_Refresh::refresh_titles_int($EqLogic, $data, $log_result, $cmd);

                break;
            case 'string':
                $_value = Free_Refresh::refresh_titles_string($EqLogic, $data, $log_result, $cmd);
                break;
            case 'binary':
                $_value = Free_Refresh::refresh_titles_bool($EqLogic, $data, $log_result, $cmd);
                break;
        }
        if ($cmd->getConfiguration('TypeNode') == 'nodes') { // 
            $_cmd_ep_id = $data['id'];
        } else {
            $_cmd_ep_id = $data['ep_id'];
        }
        $EqLogic->checkAndUpdateCmd($_cmd_ep_id, $_value);
    }
    private static function refresh_titles($EqLogics, $Free_API)
    {
        $results = $Free_API->universal_get('tiles', $EqLogics->getLogicalId(), null, null);

        if ($results != false) {
            foreach ($results as $result) {
                foreach ($result['data'] as $data) {
                    $cmd = $EqLogics->getCmd('info', $data['ep_id']);
                    if (!is_object($cmd)) break;
                    Free_Refresh::refresh_titles_CMD($cmd, $EqLogics, $data, $data['ep_id'], false);
                }
            }
        }
        if ($EqLogics->getConfiguration('type2') == 'pir' || $EqLogics->getConfiguration('type2') == 'alarm' || $EqLogics->getConfiguration('type2') == 'dws' || $EqLogics->getConfiguration('type') == 'camera' || $EqLogics->getConfiguration('type2') == 'alarm' || $EqLogics->getConfiguration('type2') == 'kfb' || $EqLogics->getConfiguration('type2') == 'shutter' || $EqLogics->getConfiguration('type2') == 'basic_shutter') {
            Free_Refresh::refresh_titles_nodes($EqLogics, $Free_API, $data['ep_id']);
        }
    }
    private static function refresh_titles_nodes($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'home/nodes/' . $EqLogics->getLogicalId());
        $_eq_data = $result['show_endpoints'];
        foreach ($_eq_data as $Cmd) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if ($Command->getLogicalId() == $Cmd['id'] && $Command->getConfiguration('TypeNode') == 'nodes') {
                    if ($Command->getConfiguration('info') == 'mouv_sensor') {
                        $_value = false;
                        if ($Cmd['value'] == false) {
                            $_value = true;
                        }
                    } else {
                        $_value = $Cmd['value'];
                        if ($Cmd['name'] == 'battery') {
                            $EqLogics->batteryStatus($_value);
                        }
                    }
                    $EqLogics->checkAndUpdateCmd($Cmd['id'], $_value);
                    break;
                }
            }
        }
    }
    private static function refresh_player($EqLogics, $Free_API)
    {
        if ($EqLogics->getConfiguration('player') == 'OK') {
            $results_playerID = $Free_API->universal_get('player_ID', $EqLogics->getConfiguration('action'));
        }

        log::add('Freebox_OS', 'debug', '│──────────> Player OK ? : ' . $EqLogics->getConfiguration('player'));
        $results_players = $Free_API->universal_get('player', $EqLogics->getConfiguration('action'));

        $cmd_mac = $EqLogics->getCmd('info', 'mac');
        $cmd_stb_type = $EqLogics->getCmd('info', 'stb_type');
        $cmd_device_model = $EqLogics->getCmd('info', 'device_model');
        $cmd_api_version = $EqLogics->getCmd('info', 'api_version');
        $cmd_api_available = $EqLogics->getCmd('info', 'api_available');
        $cmd_reachable = $EqLogics->getCmd('info', 'reachable');
        $cmd_powerState = $EqLogics->getCmd('info', 'power_state');


        foreach ($results_players as $results_player) {
            if ($results_player['id'] != $EqLogics->getConfiguration('action')) continue;

            if ($results_player['api_available']) {
                if ($cmd_stb_type) $EqLogics->checkAndUpdateCmd($cmd_stb_type->getLogicalId(), $results_player['stb_type']);
                if ($cmd_device_model) $EqLogics->checkAndUpdateCmd($cmd_device_model->getLogicalId(), $results_player['device_model']);
                if ($cmd_api_version) $EqLogics->checkAndUpdateCmd($cmd_api_version->getLogicalId(), $results_player['api_version']);
            }

            if ($cmd_mac) $EqLogics->checkAndUpdateCmd($cmd_mac->getLogicalId(), $results_player['mac']);
            if ($cmd_api_available) $EqLogics->checkAndUpdateCmd($cmd_api_available->getLogicalId(), $results_player['api_available']);
            if ($cmd_reachable) $EqLogics->checkAndUpdateCmd($cmd_reachable->getLogicalId(), $results_player['reachable']);
        }

        if (isset($results_playerID) && $cmd_powerState) $EqLogics->checkAndUpdateCmd($cmd_powerState->getLogicalId(), $results_playerID['power_state']);
    }
    private static function refresh_freeplug($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', $EqLogics->getLogicalId(), null, 'freeplug', true, true);
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "net_role":
                            if ($result['result']['net_role'] == 'cco') {
                                $value = 'Coordinateur';
                            } else {
                                $value = 'Station';
                            }
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $value);
                            break;
                        case "rx_rate":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['rx_rate']);
                            break;
                        case "tx_rate":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['tx_rate']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_VM($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', $EqLogics->getConfiguration('action'), null, 'vm/');
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case 'bind_usb_ports':
                            $bind_usb_ports = null;
                            if ($result['bind_usb_ports'] != null) {
                                if (isset($result['bind_usb_ports'])) {
                                    foreach ($result['bind_usb_ports'] as $USB) {
                                        $bind_usb_ports .= '<br>' . $USB;
                                    }
                                }
                            } else {
                                $bind_usb_ports .= 'Aucun port USB de connecté';
                            }
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $bind_usb_ports);
                            break;
                        case "enable_screen":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['enable_screen']);
                            break;
                        case "disk_type":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['disk_type']);
                            break;
                        case "mac":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['mac']);
                            break;
                        case "memory":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['memory']);
                            break;
                        case "name":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['name']);
                            break;
                        case "status":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['status']);
                            break;
                        case "vcpus":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['vcpus']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_WebSocket($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('WebSocket', null, null, null);
    }
    private static function refresh_wifi($EqLogics, $Free_API)
    {
        $listmac = $Free_API->mac_filter_list();
        if ($listmac != false) {
            log::add('Freebox_OS', 'debug', '>───────── Liste Noire : ' . $listmac['listmac_blacklist']);
            log::add('Freebox_OS', 'debug', '>───────── Liste Blanche : ' . $listmac['listmac_whitelist']);
        }
        $result_config = $Free_API->universal_get('wifi', null, null, 'config');
        $value = false;
        foreach ($EqLogics->getCmd('info') as $Command) {
            if (is_object($Command)) {
                switch ($Command->getLogicalId()) {
                    case "listblack":
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $listmac['listmac_blacklist']);
                        break;
                    case "listwhite":
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $listmac['listmac_whitelist']);
                        break;
                    case "wifiStatut":
                        $value = false;
                        if ($result_config['result']['enabled']) {
                            $value = true;
                        }
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $value);
                        break;
                    case "wifiPlanning":
                        $value = false;
                        $result = $Free_API->universal_get('wifi', null, null, 'planning');
                        if ($result['result']['use_planning']) {
                            $value = true;
                        }
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $value);
                        break;
                    case "wifiWPS":
                        $value = false;
                        $result = $Free_API->universal_get('wifi', null, null, 'wps/config');
                        if ($result['result']['enabled']) {
                            $value = true;
                        }
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $value);
                        break;
                    case "wifimac_filter_state":
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result_config['result']['mac_filter_state']);
                        break;
                    default:
                        $result_ap = $Free_API->universal_get('wifi', null, null, 'ap/' . $Command->getLogicalId());
                        log::add('Freebox_OS', 'debug', '>───────── Status Carte ' . $result_ap['result']['name'] . ' / ' . $Command->getLogicalId() . ' : ' . $result_ap['result']['status']['state']);
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result_ap['result']['status']['state']);
                        break;
                }
            }
        }
    }
}
