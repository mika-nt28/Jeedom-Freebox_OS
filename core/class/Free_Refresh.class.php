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
        $API_version = config::byKey('FREEBOX_API', 'Freebox_OS');
        $TYPE_FREEBOX_TILES = config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS');
        if ($API_version == null || $API_version === 'TEST_V8') {
            $result_API = Freebox_OS::FreeboxAPI();
            log::add('Freebox_OS', 'debug', ':fg-info: Version API Compatible avec la Freebox : ' . $result_API . ':/fg:');
        }
        if ($_freeboxID == 'Tiles_global') {
            Free_Refresh::refresh_titles_global($EqLogics, $Free_API);
        }

        if (is_object($EqLogics) && $EqLogics->getIsEnable()) {
            if ($_freeboxID != 'Tiles_global') {
                log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour : ' . $EqLogics->getName() . ' :/fg: ◀︎───────────');
            }
            if ($EqLogics->getConfiguration('type') == 'player' || $EqLogics->getConfiguration('type') == 'parental' || $EqLogics->getConfiguration('type') == 'freeplug' || $EqLogics->getConfiguration('type') == 'VM') {
                $refresh = $EqLogics->getConfiguration('type');
            } else {
                $refresh = $EqLogics->getLogicalId();
            }
            switch ($refresh) {
                case 'management':
                    log::add('Freebox_OS', 'debug', '───▶︎ Pas de fonction rafraichir pour cet équipement');
                    break;
                case 'airmedia':
                    Free_Refresh::refresh_airmedia($EqLogics, $Free_API);
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
                    if ($TYPE_FREEBOX_TILES == 'OK') {
                        $result = $Free_API->universal_get('universalAPI', null, null, 'home/adapters');
                        foreach ($EqLogics->getCmd('info') as $Command) {
                            foreach ($result as $Cmd) {
                                if ($Cmd['id'] == $Command->getLogicalId()) {
                                    if ($Cmd['status'] == 'active') {
                                        $homeadapters_value = 1;
                                    } else {
                                        $homeadapters_value = 0;
                                    }
                                    log::add('Freebox_OS', 'debug', '| ───▶︎ Update pour Id : ' . $Cmd['id'] . ' -- Nom : ' . $Cmd['label'] . ' -- Etat : ' . $homeadapters_value);
                                    $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $homeadapters_value);
                                }
                            }
                        }
                    } else {
                        log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎  La box n\'est plus comptatible avec cette application :/fg:──');
                        Freebox_OS::DisableEqLogic($EqLogics, true);
                    }
                    break;
                case 'parental':
                    foreach ($EqLogics->getCmd('info') as $Command) {
                        $results = $Free_API->universal_get('parental', $EqLogics->getConfiguration('action'));
                        if ($results != false) {
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $results['current_mode']);
                        } else {
                            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎  AUCUN CONTROLE PARENTAL AVEC CET ID :/fg:──');
                            Freebox_OS::DisableEqLogic($EqLogics, false);
                        }
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
                    if ($TYPE_FREEBOX_TILES == 'OK') {
                        Free_Refresh::refresh_titles($EqLogics, $Free_API);
                        // Free_Refresh::refresh_titles_global_CmdbyCmd($EqLogics, $Free_API, true);
                    } else {
                        log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎  La box n\'est plus comptatible avec cette application :/fg:──');
                        Freebox_OS::DisableEqLogic($EqLogics, true);
                    }

                    break;
            }
            if ($_freeboxID != 'Tiles_global') {
                log::add('Freebox_OS', 'debug', '───────────────────────────────────────────');
            }
        }
    }
    private static function refresh_airmedia($EqLogics, $Free_API)
    {
        $logicalinfo = Freebox_OS::getlogicalinfo();
        foreach ($EqLogics->getCmd('info') as $Command) {
            if (is_object($Command)) {
                switch ($Command->getLogicalId()) {
                    case "receivers_info":
                        $receivers_Value = $Command->execCmd();
                        $result = $Free_API->universal_get('universalAPI', null, null, 'airmedia/receivers', true, true, null);

                        // Gestion Liste déroulante Airmedia
                        $receivers_list = null;
                        if ($result != false) {
                            foreach ($result as $airmedia) {
                                if ($receivers_list == null) {
                                    $receivers_list = $airmedia['name'] . '|' . $airmedia['name'];
                                } else {
                                    $receivers_list .= ';' . $airmedia['name'] . '|' . $airmedia['name'];
                                }
                            }
                            log::add('Freebox_OS', 'debug', '───▶︎ Liste des Airmedia : ' . $receivers_list);
                            $EqLogics->AddCommand('Choix du Player AirMedia', 'receivers', 'action', 'select', null, null, null, 1, 'default', 'default', null, null, 0, 'default', 'default', 2, '0', false, true, null, null, null, null, null, null, null, null, null, null, $receivers_list, null, null, true);
                        }
                        // Gestion Liste déroulante Type de média
                        if ($receivers_Value != null) {
                            $media_type_list = null;
                            if ($result != false) {
                                foreach ($result as $airmedia) {
                                    if ($airmedia['name'] == $receivers_Value) {
                                        if ($airmedia['capabilities']['photo'] == true) {
                                            // log::add('Freebox_OS', 'debug', '│ Photo : ' . $airmedia['capabilities']['photo']);
                                            if ($media_type_list == null) {
                                                $media_type_list = 'photo' . '|' . 'Photo';
                                            }
                                        }
                                        if ($airmedia['capabilities']['screen'] == true) {
                                            //log::add('Freebox_OS', 'debug', '│ Screen : ' . $airmedia['capabilities']['screen']);
                                            if ($media_type_list == null) {
                                                $media_type_list = 'screen' . '|' . 'Screen';
                                            } else {
                                                $media_type_list .= ';' . 'screen' . '|' . 'Screen';
                                            }
                                        }
                                        if ($airmedia['capabilities']['audio'] == true) {
                                            //log::add('Freebox_OS', 'debug', '│ Audio : ' . $airmedia['capabilities']['audio']);
                                            if ($media_type_list == null) {
                                                $media_type_list = 'audio' . '|' . 'Audio';
                                            } else {
                                                $media_type_list .= ';' . 'audio' . '|' . 'Audio';
                                            }
                                        }
                                        if ($airmedia['capabilities']['video'] == true) {
                                            //log::add('Freebox_OS', 'debug', '│ Vidéo : ' . $airmedia['capabilities']['video']);
                                            if ($media_type_list == null) {
                                                $media_type_list = 'video' . '|' . 'video';
                                            } else {
                                                $media_type_list .= ';' . 'video' . '|' . 'Vidéo';
                                            }
                                        }
                                        log::add('Freebox_OS', 'debug', '───▶︎  Liste des médias compatible pour : ' . $receivers_Value . ' avec les valeurs : ' . $media_type_list);
                                        $EqLogics->AddCommand('Choix du Media', 'media_type', 'action', 'select', null, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 4, '0', false, true, null, null, null, null, null, null, null, null, null, null, $media_type_list, null, null, true);
                                        $EqLogics->refreshWidget();
                                    }
                                }
                            }
                        } else {
                            $EqLogics->refreshWidget();
                        }

                        break;
                }
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
                        case "bytes_down":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['bytes_down']);
                            break;
                        case "bytes_up":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['bytes_up']);
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
                        case "rx_max_rate_xdsl": // toute la partie 4G
                            Free_Refresh::refresh_connexion_4G($EqLogics, $Free_API);
                            break;
                        case "ping": // toute la partie CONFIG
                            Free_Refresh::refresh_connexion_Config($EqLogics, $Free_API);
                            break;
                        case "sfp_present": // toute la partie Fibre
                            Free_Refresh::refresh_connexion_FTTH($EqLogics, $Free_API);
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_connexion_4G($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'connection/aggregation', true, true, true);
        if ($result != false && $result != 'Aucun module 4G détecté') {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "rx_max_rate_lte":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['tunnel']['lte']['rx_max_rate']);
                            break;
                        case "rx_used_rate_lte":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['tunnel']['lte']['rx_used_rate']);
                            break;
                        case "rx_max_rate_xdsl":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['tunnel']['xdsl']['rx_max_rate']);
                            break;
                        case "rx_used_rate_xdsl":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['tunnel']['xdsl']['rx_used_rate']);
                            break;
                        case "state":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['enabled']);
                            break;
                        case "tx_max_rate_lte":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['tunnel']['lte']['tx_max_rate']);
                            break;
                        case "tx_used_rate_lte":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['tunnel']['lte']['tx_used_rate']);
                            break;
                        case "tx_max_rate_xdsl":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['tunnel']['xdsl']['tx_max_rate']);
                            break;
                        case "tx_used_rate_xdsl":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['tunnel']['xdsl']['tx_used_rate']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_connexion_Config($EqLogics, $Free_API)
    {
        $result =  $Free_API->universal_get('connexion', null, null, 'config', true, true, null);
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
        $result = $Free_API->universal_get('connexion', null, null, 'ftth', true, true, null);
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "link_type":
                            if (isset($result['link_type'])) {
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['link_type']);
                            } else {
                                Free_Refresh::Free_removeLogicId($EqLogics, $Command->getLogicalId());
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
                                Free_Refresh::Free_removeLogicId($EqLogics, $Command->getLogicalId());
                            }
                            break;
                        case "sfp_pwr_tx":
                            if (isset($result['sfp_pwr_tx'])) {
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_pwr_tx']);
                            } else {
                                Free_Refresh::Free_removeLogicId($EqLogics, $Command->getLogicalId());
                            }
                            break;
                        case "sfp_pwr_rx":
                            if (isset($result['sfp_pwr_rx'])) {
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['sfp_pwr_rx']);
                            } else {
                                Free_Refresh::Free_removeLogicId($EqLogics, $Command->getLogicalId());
                            }
                            break;
                    }
                }
            }
        }
    }

    private static function refresh_disk($EqLogics, $Free_API)
    {
        //$result = $Free_API->universal_get('disk', null, null, null);
        $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, null);
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    foreach ($result as $disks) {
                        switch ($Command->getLogicalId()) {
                            case $disks['id'] . '_temp':
                                log::add('Freebox_OS', 'debug', '───▶︎ Disque [' . $disks['serial'] . ' - ' . $disks['id'] . '] '  . 'Température :' . $disks['temp'] . '°C');
                                $EqLogics->checkAndUpdateCmd($disks['id'] . '_temp', $disks['temp']);
                                break;
                            case $disks['id'] . '_spinning':
                                log::add('Freebox_OS', 'debug', '───▶︎ Disque [' . $disks['serial'] . ' - ' . $disks['id'] . '] '  . 'Tourne :' . $disks['spinning']);
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
                            log::add('Freebox_OS', 'debug', '───▶︎ Occupation de la partition ' . $partition['label'] . ' : ' . $value . ' - Pour le disque  [' . $disks['type'] . '] - ' . $disks['id']);

                            $EqLogics->checkAndUpdateCmd($partition['id'], $value);
                        }
                    }
                }
            }
        } else {
            log::add('Freebox_OS', 'debug', '[WARNING] - AUCUN DISQUE');
            Freebox_OS::DisableEqLogic($EqLogics, false);
        }
        $Type_box = config::byKey('TYPE_FREEBOX', 'Freebox_OS');
        if ($Type_box != 'fbxgw1r' && $Type_box != 'fbxgw2r') {
            $result = $Free_API->universal_get('universalAPI', null, null, 'storage/raid', true, true, null);
            if ($result != false) {
                if (is_object($Command)) {
                    foreach ($result as $raid) {
                        foreach ($EqLogics->getCmd('info') as $Command) {
                            switch ($Command->getLogicalId()) {
                                case $raid['id'] . '_state':
                                    log::add('Freebox_OS', 'debug', '───▶︎ Raid_' . $raid['id'] . '_state : ' . $raid['state']);
                                    $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $raid['state']);
                                    break;
                                case $raid['id'] . '_sync_action':
                                    log::add('Freebox_OS', 'debug', '───▶︎ Raid_' . $raid['id'] . '_sync_action : ' . $raid['sync_action']);
                                    $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $raid['sync_action']);
                                    break;
                                case $raid['id'] . '_role':
                                    log::add('Freebox_OS', 'debug', '───▶︎ Raid_' . $raid['id'] . '_role : ' . $raid['role']);
                                    $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $raid['role']);
                                    break;
                                case $raid['id'] . '_degraded':
                                    log::add('Freebox_OS', 'debug', '───▶︎ Raid_' . $raid['id'] . '_degraded : ' . $raid['degraded']);
                                    $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $raid['degraded']);
                                    break;
                            }
                        }
                        if (isset($raid['members'])) {
                            foreach ($raid['members'] as $members_raid) {
                                foreach ($EqLogics->getCmd('info') as $Command) {
                                    switch ($Command->getLogicalId()) {
                                        case $members_raid['id'] . '_role':
                                            log::add('Freebox_OS', 'debug', '───▶︎ Role pour le disque ' . $members_raid['disk']['serial'] . ' : ' . $members_raid['role']);
                                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $members_raid['role']);
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                log::add('Freebox_OS', 'debug', '[WARNING] - AUCUN DISQUE RAID');
            }
        }
    }

    private static function refresh_download($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'downloads/stats', true, true, true);
        if ($result['result'] != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "conn_ready":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['conn_ready']);
                            break;
                        case "throttling_is_scheduled":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['throttling_is_scheduled']);
                            break;
                        case "nb_tasks":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks']);
                            break;
                        case "nb_tasks_downloading":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks_downloading']);
                            break;
                        case "nb_tasks_done":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks_done']);
                            break;
                        case "nb_rss":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_rss']);
                            break;
                        case "nb_rss_items_unread":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_rss_items_unread']);
                            break;
                        case "rx_rate":
                            $rx_rate = $result['result']['rx_rate'];
                            if (function_exists('bcdiv'))
                                $rx_rate = bcdiv($result, 1048576, 2);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $rx_rate);
                            break;
                        case "tx_rate":
                            $tx_rate = $result['result']['tx_rate'];
                            if (function_exists('bcdiv'))
                                $tx_rate = bcdiv($tx_rate, 1048576, 2);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $tx_rate);
                            break;
                        case "nb_tasks_active":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks_active']);
                            break;
                        case "nb_tasks_stopped":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks_stopped']);
                            break;
                        case "nb_tasks_queued":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks_queued']);
                            break;
                        case "nb_tasks_repairing":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks_repairing']);
                            break;
                        case "nb_tasks_extracting":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks_extracting']);
                            break;
                        case "nb_tasks_error":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks_error']);
                            break;
                        case "nb_tasks_checking":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['nb_tasks_checking']);
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
        $result = $Free_API->universal_get('universalAPI', null, null, 'downloads/config', true, true, true);

        if ($result['result'] != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "mode":
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['throttling']['mode']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_LCD($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'lcd/config/', true, true, null);
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
            log::add('Freebox_OS', 'debug', ':fg-info:Nb Appels manqués : ' . $result['missed'] . $list_missed . ':/fg:');
            log::add('Freebox_OS', 'debug', ':fg-info:Nb Appels reçus : ' . $result['accepted'] . $list_accepted . ':/fg:');
            log::add('Freebox_OS', 'debug', ':fg-info:Nb Appels passés : ' . $result['outgoing'] . $list_outgoing . ':/fg:');
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
        $active_list = null;
        $active_listIP = null;
        $noactive_list = null;
        $updatename = false;
        $_IsVisible = 'default';
        $_UpdateVisible = false;
        $IsVisible_option = true;
        $updateWidget = false;
        $mac_address = null;
        if ($_network == 'LAN') {
            $_networkinterface = 'pub';
        } else if ($_network == 'WIFIGUEST') {
            $_networkinterface = 'wifiguest';
        }
        $result_network_ping = $Free_API->universal_get('universalAPI', null, null, 'lan/browser/' . $_networkinterface, true, true, true);
        $result_network_DHCP = $Free_API->universal_get('universalAPI', null, null, 'dhcp/static_lease', true, true, true);
        $order_count_active = 100;
        $result_network = null;
        $order_count_noactive = 400;
        if ($EqLogics->getConfiguration('UpdateVisible') == true) {
            $_UpdateVisible = true;
            log::add('Freebox_OS', 'debug', '| ───▶︎ ETAT Option "Afficher uniquement les connectés" = ' . $_UpdateVisible . ' => : les équipements avec statut 0 ne seront pas affichés');
        } else {
            $_UpdateVisible = false;
            log::add('Freebox_OS', 'debug', '| ───▶︎ ETAT Option "Afficher uniquement les connectés" = 0 => : les équipements avec statut 0 seront affichés');
        }

        if (!isset($result_network_ping['result'])) {
            //  if (!$result_network_ping['success']) {
            log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ RESULTAT Requête pas correct ou Pas d\'appareil trouvé' . ':/fg:');
        } else {
            foreach ($EqLogics->getCmd('info') as $Command) {
                $result_network = $result_network_ping['result'];
                $_control_id = array_search($Command->getLogicalId(), array_column($result_network, 'id'), true);

                if ($_control_id  === false) {
                    if ($Command->getLogicalId() == 'host_info' || $Command->getLogicalId() == 'host_type_info' || $Command->getLogicalId() == 'method_info' || $Command->getLogicalId() == 'add_del_ip_info' || $Command->getLogicalId() == 'primary_name_info' || $Command->getLogicalId() == 'comment_info') {
                    } else {
                        log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ APPAREIL PAS TROUVE : ' . $Command->getLogicalId() . ' => SUPPRESSION' . ':/fg:');
                        $Command->remove();
                    }
                }
                if (is_object($Command)) {
                    $Ipv6 = null;
                    $Ipv4 = null;
                    foreach ($result_network as $result) {

                        $Cmd = $EqLogics->getCmd('info', $result['id']);
                        if ($Command->getLogicalId() != $result['id']) continue;

                        if (isset($result['access_point'])) {
                            $name_connectivity_type = $result['access_point']['connectivity_type'];
                        } else {
                            $name_connectivity_type = 'Wifi Ethernet ?';
                        }
                        if (isset($result['l3connectivities'])) {
                            foreach ($result['l3connectivities'] as $Ip) {
                                if ($Ip['active']) {
                                    if ($Ip['af'] == 'ipv4') {
                                        $Cmd->setConfiguration('IPV4', $Ip['addr']);
                                        $Ipv4 = $Ip['addr'];
                                    } else {
                                        $Cmd->setConfiguration('IPV6', $Ip['addr']);
                                        $Ipv6 = $Ip['addr'];
                                    }
                                }
                            }
                        }
                        if (isset($result['l2ident'])) {
                            $ident = $result['l2ident'];
                            if ($ident['type'] == 'mac_address') {
                                $mac_address = $ident['id'];
                                if (isset($result_network_DHCP['result'])) {
                                    foreach ($result_network_DHCP['result'] as $IP) {
                                        if ($IP['mac'] == $mac_address) {
                                            $Ipv4 = $IP['ip'];
                                            if ($active_listIP == null) {
                                                $active_listIP = $IP['hostname'] . '(' . $IP['ip'] . ')';
                                            } else {
                                                $active_listIP .= '|' . $IP['hostname'] . '(' . $IP['ip'] . ')';
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (isset($result['active'])) {
                            if ($result['active'] == true) {
                                $order = $order_count_active++;
                                if ($active_list == null) {
                                    $active_list = $result['primary_name'] . '(' . $mac_address . ')';
                                } else {
                                    $active_list .= '|' . $result['primary_name'] . '(' . $mac_address . ')';
                                }
                                $value = true;
                                $IsVisible_option = true;
                            } else {
                                $order = $order_count_noactive++;
                                $value = 0;
                                $IsVisible_option = false;
                                // Liste des non actifs
                                if ($noactive_list == null) {
                                    $noactive_list = $result['primary_name'] . ' (' . $mac_address . ')';
                                } else {
                                    $noactive_list .= '|' . $result['primary_name'] . ' (' . $mac_address . ')';
                                }
                            }
                        } else {
                            $value = 0;
                            $IsVisible_option = '0';
                        }

                        $Parameter = array(
                            "updatename" =>  $updatename,
                            "host_type" => $result['host_type'],
                            "IPV4" => $Ipv4,
                            "IPV6" => $Ipv6,
                            "mac_address" => $mac_address,
                            "order" => $order,
                            "repeatevent" => true,
                            "repeat" => true,
                            "UpdateVisible" => $_UpdateVisible,
                            "IsVisible_option" => $IsVisible_option
                        );
                        $EqLogics->AddCommand($result['primary_name'], $result['id'], 'info', 'binary', 'Freebox_OS::Network', null, null, $_IsVisible, 'default', 'default', 0, null, 0, 'default', 'default', null, '0', $updateWidget, true, null, null, null, null, null, null, null, null, null, null, null, $Parameter, $name_connectivity_type);
                        $EqLogics->checkAndUpdateCmd($Cmd, $value);
                        if ($_UpdateVisible == true) {
                            $Cmd->setIsVisible($IsVisible_option);
                            $Cmd->save();
                        }
                        break;
                    }
                }
            }
            log::add('Freebox_OS', 'debug', '| ───▶︎ Appareil(s) connecté(s) : ' . $active_list);
            log::add('Freebox_OS', 'debug', '| ───▶︎ Appareil(s) connecté(s) avec IP Fixe : ' . $active_listIP);
            log::add('Freebox_OS', 'debug', '| ───▶︎ Appareil(s) non connecté(s) : ' . $noactive_list);
        }
    }

    private static function refresh_netshare($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'netshare/samba', true, true, false);
        $resultmac = $Free_API->universal_get('universalAPI', null, null, 'netshare/afp', true, true, false);
        $resultFTP = $Free_API->universal_get('universalAPI', null, null, 'ftp/config', true, true, false);
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "file_share_enabled":
                            log::add('Freebox_OS', 'debug', '───▶︎ Partage Fichier Windows : ' . $result['file_share_enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['file_share_enabled']);
                            break;
                        case "FTP_enabled":
                            log::add('Freebox_OS', 'debug', '───▶︎ Partage Fichier FTP : ' . $resultFTP['enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $resultFTP['enabled']);
                            break;
                        case "mac_share_enabled":
                            log::add('Freebox_OS', 'debug', '───▶︎ Partage Fichier Mac : ' . $resultmac['enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $resultmac['enabled']);
                            break;
                        case "print_share_enabled":
                            log::add('Freebox_OS', 'debug', '───▶︎ Partage Imprimante : ' . $result['print_share_enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['print_share_enabled']);
                            break;
                        case "smbv2_enabled":
                            if (isset($result['smbv2_enabled'])) {
                                log::add('Freebox_OS', 'debug', '───▶︎ Etat Samba SMBv2 : ' . $result['smbv2_enabled']);
                                if ($result['smbv2_enabled'] == true) {
                                    Free_Refresh::Free_removeLogicId($EqLogics, 'print_share_enabledOn');
                                    Free_Refresh::Free_removeLogicId($EqLogics, 'print_share_enabledOff');
                                    Free_Refresh::Free_removeLogicId($EqLogics, 'print_share_enabled');
                                }
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['smbv2_enabled']);
                            } else {
                                Free_Refresh::Free_removeLogicId($EqLogics, 'smbv2_enabledOn');
                                Free_Refresh::Free_removeLogicId($EqLogics, 'smbv2_enabledOff');
                                Free_Refresh::Free_removeLogicId($EqLogics, 'smbv2_enabled');
                            }


                            break;
                    }
                }
            }
        }
    }
    public static function Free_removeLogicId($eqLogic, $cmdDell)
    {
        //  suppression fonction
        $cmd = $eqLogic->getCmd('info', $cmdDell);
        if (is_object($cmd)) {
            $cmd->remove();
        }
    }

    private static function refresh_system($EqLogics, $Free_API)
    {
        log::add('Freebox_OS', 'debug', '───▶︎ Récupération des valeurs du Système');
        $result = $Free_API->universal_get('system', null, null, null, true, true, null);
        foreach ($EqLogics->getCmd('info') as $Command) {
            $logicalId = $Command->getConfiguration('logicalId');

            switch ($Command->getConfiguration('logicalId')) {
                case "sensors":
                    foreach ($result['sensors'] as $system) {
                        if ($Command->getLogicalId() != $system['id']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '───▶︎ Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $EqLogics->checkAndUpdateCmd($system['id'], $value);
                        break;
                    }
                    break;
                case "fans":
                    foreach ($result['fans'] as $system) {
                        if ($Command->getLogicalId() != $system['id']) continue;
                        $value = $system['value'];
                        log::add('Freebox_OS', 'debug', '───▶︎ Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                        $EqLogics->checkAndUpdateCmd($system['id'], $value);
                        break;
                    }
                    break;
                case "expansions":
                    foreach ($result['expansions'] as $system) {
                        if (!isset($system['slot'])) continue;
                        if ($Command->getLogicalId() != $system['slot']) continue;

                        $value = $system['present'];
                        log::add('Freebox_OS', 'debug', '───▶︎ Update pour Type : ' . $logicalId . ' -- Id : ' . $system['slot'] . ' -- valeur : ' . $value);
                        $EqLogics->checkAndUpdateCmd($system['slot'], $value);
                        break;
                    }
                    break;
                case "model_info":
                    if (is_object($Command)) {
                        switch ($Command->getLogicalId()) {
                            case "model_name":
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['name']);
                                log::add('Freebox_OS', 'debug', '───▶︎ Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $result['model_info']['name']);
                                break;
                            case "pretty_name":
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['pretty_name']);
                                config::save('TYPE_FREEBOX_NAME', $result['model_info']['pretty_name'], 'Freebox_OS');
                                log::add('Freebox_OS', 'debug', '───▶︎ Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $result['model_info']['pretty_name']);
                                break;
                            case "wifi_type":
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['wifi_type']);
                                log::add('Freebox_OS', 'debug', '───▶︎ Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $result['model_info']['wifi_type']);
                                break;
                        }
                    }
                    if (config::byKey('TYPE_FREEBOX', 'Freebox_OS') == 'fbxgw7r') {
                        foreach ($result['model_info'] as $system) {
                            if (!isset($system['slot'])) continue;
                            if ($Command->getLogicalId() != $system['slot']) continue;
                            $value = $system['value'];
                            log::add('Freebox_OS', 'debug', '───▶︎ Update pour Type : ' . $logicalId . ' -- Id : ' . $system['id'] . ' -- valeur : ' . $value);
                            $EqLogics->checkAndUpdateCmd($system['id'], $value);
                            break;
                        }
                    }

                    break;
                default:
                    if (is_object($Command)) {
                        switch ($Command->getLogicalId()) {
                            case "mac":
                                log::add('Freebox_OS', 'debug', '───▶︎ Update pour Adresse mac : ' . $result['mac']);
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
                                log::add('Freebox_OS', 'debug', '───▶︎ Allumée depuis : ' . $_uptime);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $_uptime);
                                break;
                            case "board_name":
                                log::add('Freebox_OS', 'debug', '───▶︎ Board name : ' . $result['board_name']);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['board_name']);
                                config::save('TYPE_FREEBOX', $result['board_name'], 'Freebox_OS');
                                break;
                            case "serial":
                                log::add('Freebox_OS', 'debug', '───▶︎ Numéro de série : ' . $result['serial']);
                                $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['serial']);
                                break;
                            case "firmware_version":
                                log::add('Freebox_OS', 'debug', '───▶︎ Version Firmware : ' . $result['firmware_version']);
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
        $result = $Free_API->universal_get('universalAPI', null, null, 'connection/lte/aggregation', true, true, true);
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "4GStatut":
                            log::add('Freebox_OS', 'debug', '───▶︎ Etat de la carte 4G : ' . $result['result']['enabled']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['enabled']);
                            break;
                        case "associated_lte":
                            log::add('Freebox_OS', 'debug', '───▶︎ Etat Radio 4G : ' . $result['result']['radio']['associated']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['radio']['associated']);
                            break;
                        case "state_lte":
                            log::add('Freebox_OS', 'debug', '───▶︎ Etat du réseau 4G : ' . $result['result']['state']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['state']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_system_lan($EqLogics, $Free_API)
    {
        $result =  $Free_API->universal_get('network', null, null, 'lan/config/', true, true, true);

        if ($result != false || isset($result['result']) != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "ip":
                            log::add('Freebox_OS', 'debug', '───▶︎ IP : ' . $result['result']['ip']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['ip']);
                            break;
                        case "mode":
                            log::add('Freebox_OS', 'debug', '───▶︎ Mode : ' . $result['result']['mode']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['mode']);
                            config::save('TYPE_FREEBOX_MODE', $result['result']['mode'], 'Freebox_OS');
                            break;
                        case "name":
                            log::add('Freebox_OS', 'debug', '───▶︎ Nom : ' . $result['result']['name']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['result']['name']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_system_lang($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'lang', true, true, null);
        $result_config2 = $Free_API->universal_get('universalAPI', null, null, 'notif/targets', true, true, null);
        if ($result != false || isset($result['result']) != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
                        case "lang":
                            log::add('Freebox_OS', 'debug', '───▶︎ Lang : ' . $result['lang']);
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['lang']);
                            break;
                    }
                }
            }
        }
    }
    private static function refresh_titles_string($EqLogic, $data, $log_result, $Cmd, $logicalId_name = null, $_cmd_id = null)
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
                log::add('Freebox_OS', 'debug', '───▶︎ Update commande spécifique pour Homebridge : ' . $EqLogic->getConfiguration('type') . ' -- ' . $_Alarm_log);
            }
            $EqLogic->checkAndUpdateCmd('ALARM_state', $_Alarm_stat_value);
            $EqLogic->checkAndUpdateCmd('ALARM_enable', $_Alarm_enable_value);
            $EqLogic->checkAndUpdateCmd('ALARM_mode', $_Alarm_mode_value);
            if ($log_result == true) {
                log::add('Freebox_OS', 'debug', '───▶︎ Statut (ALARM_state) = ' . $_Alarm_stat_value . ' / Actif (ALARM_enable) = ' . $_Alarm_enable_value . ' / Mode (ALARM_mode) = ' . $_Alarm_mode_value);
            }
        };

        if ($data['ui']['display'] == 'color') {
            $_value = $data['value'];
        } else {
            if ($data['name'] == 'error' && ($EqLogic->getConfiguration('type') == 'alarm_control' || $EqLogic->getConfiguration('type2') == 'alarm')) {
                if ($data['value'] == null) {
                    $_value = 'Pas de message d\'erreur';
                    if ($log_result == true) {
                        log::add('Freebox_OS', 'debug', '───▶︎ Update commande spécifique Message erreur : ' . $EqLogic->getConfiguration('type') . ' -- ' . $data['value']);
                    }
                }
            } else {
                $_value = $data['value'];
            }
        }
        return $_value;
    }
    private static function refresh_titles_bool($EqLogic, $data, $log_result, $Cmd, $logicalId_name = null, $_cmd_id = null)
    {
        /*  Suppression de cette inversion car c'est gérer par le core
        if ($EqLogic->getConfiguration('info') == 'mouv_sensor' && $Cmd->getConfiguration('info') == 'mouv_sensor') {
            if ($log_result == true) {
            log::add('Freebox_OS', 'debug', '───▶︎ Inversion de la valeur pour les détecteurs de mouvement pour être compatible avec Homebridge');
            }
            $_value = false;
            if ($data['value'] == false) {
              $_value = true;
            }
        } else {
            $_value = $data['value'];
        }
        */
        $_value = $data['value'];
        if ($log_result == true) {
            Log::add('Freebox_OS', 'debug', '───▶︎ ' . $logicalId_name . ' (' . $_cmd_id . ') = ' . $_value);
        }
        return $_value;
    }
    private static function refresh_titles_int($EqLogic, $data, $log_result, $Cmd, $logicalId_name = null, $_cmd_id = null)
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
            if ($data['value'] == null || $data['value'] == '') {
                $_value = '0';
            } else {
                $_value = $data['value'];
            }
        }
        if (($log_result == true & $data['name'] != 'pushed')) {
            Log::add('Freebox_OS', 'debug', '───▶︎ ' . $logicalId_name . ' (' . $_cmd_id . ') = ' . $_value . ' -- valeur Box = ' . $data['value'] . ' -- Etat Option Inverser = ' . $Cmd->getDisplay('invertBinary'));
        }
        return $_value;
    }

    private static function refresh_titles_global($EqLogics, $Free_API)
    {
        $boucle_num = 1; // 1 = Tiles - 2 = Node 
        $log_result = true;
        if (config::byKey('FREEBOX_TILES_CRON', 'Freebox_OS') == 1) {
            $log_result == false;
        }
        while ($boucle_num <= 2) {
            if ($boucle_num == 2) {
                $result = $Free_API->universal_get('universalAPI', null, null, 'home/nodes', false, false, null);
            } else if ($boucle_num == 1) {
                $result = $Free_API->universal_get('universalAPI', null, null, 'home/tileset/all', false, false, null);
            }

            foreach ($result as $node) {
                if ($boucle_num == 2) {
                    $_eq_type = $node['category'];
                    $_eq_data = $node['show_endpoints'];
                    $_eq_node = $node['id'];
                    $boucle_name = 'NODES';
                } else if ($boucle_num == 1) {
                    $_eq_type = $node['type'];
                    $_eq_data = $node['data'];
                    $_eq_node = $node['node_id'];
                    $boucle_name = 'TILESET';
                }
                if ($boucle_num == 1 && $_eq_type == 'camera') {
                } else {
                    $EqLogic = eqLogic::byLogicalId($_eq_node, 'Freebox_OS');
                    if (is_object($EqLogic)) {
                        if ($EqLogic->getIsEnable()) {
                            log::add('Freebox_OS', 'debug', ':fg-info: MISE A JOUR POUR : ' . $EqLogic->getName() . ' - Boucle : ' . $boucle_name . ':/fg:');
                            foreach ($_eq_data as $data) {
                                if ($boucle_num == 1) {
                                    $_cmd_id = $data['ep_id'];
                                    $cmd = $EqLogic->getCmd('info', $_cmd_id);
                                    if (is_object($cmd)) {
                                        Free_Refresh::refresh_titles_CMD($cmd, $EqLogic, $data, $_cmd_id, $log_result);
                                    }
                                } else {
                                    $_cmd_id = $data['id'];
                                    $cmd = $EqLogic->getCmd('info', $_cmd_id);
                                    if (is_object($cmd)) {
                                        if ($cmd->getConfiguration('TypeNode') == 'nodes') {
                                            if ($EqLogic->getConfiguration('type2') == 'pir' || $EqLogic->getConfiguration('type2') == 'kfb' || $EqLogic->getConfiguration('type2') == 'dws' || $EqLogic->getConfiguration('type2') == 'alarm' || $EqLogic->getConfiguration('type') == 'camera'  || $EqLogic->getConfiguration('type2') == 'basic_shutter' || $EqLogic->getConfiguration('type2') == 'opener' || $EqLogics['category'] == 'shutter'  || $EqLogic->getConfiguration('type') == 'light') {
                                                Free_Refresh::refresh_titles_CMD($cmd, $EqLogic, $data, $_cmd_id, $log_result);
                                            }
                                        } else {
                                            //log::add('Freebox_OS', 'debug', '───▶︎ Aucune mise à jour avec la boucle : ' . $boucle_name);
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
    private static function refresh_titles_remote($EqLogic, $data, $log_result, $Cmd)
    {
        $nb_pushed = count($data['history']);
        $nb_pushed_k = $nb_pushed - 1;
        $_value = $data['history'][$nb_pushed_k]['value'];
        $timestamp = $data['history'][$nb_pushed_k]['timestamp'];
        if ($log_result == true) {
            log::add('Freebox_OS', 'debug', '───▶︎ ' . '[' . $Cmd->getName() . ']' . ' : Nb de valeur enregistrée -1 = ' . $nb_pushed_k . ' -- Valeur historique récente = ' . $_value . ' [' . date("d/m/Y H:i:s", $timestamp) . ' - ' . $timestamp  . ']');
        }
        if ($Cmd->getConfiguration('history_remote') == $timestamp) {
            if ($log_result == true) {
                log::add('Freebox_OS', 'debug', '───▶︎ ' . 'Pas de changement de la valeur de la télécommande');
            }
        } else {
            $Cmd->setConfiguration('history_remote', $timestamp);
            $Cmd->save();
            if ($log_result == true) {
                log::add('Freebox_OS', 'debug', '───▶︎ ' . 'Changement de la valeur nécessaire pour la télécommande');
            }
            $EqLogic->checkAndUpdateCmd($Cmd, $_value);
        }
    }
    private static function refresh_titles_CMD($Cmd, $EqLogic, $data, $_cmd_id, $log_result)
    {
        if ($data['name'] == 'battery' || $data['name'] == 'battery_warning') {
            $_value = $data['value'];
            $EqLogic->batteryStatus($_value);
        }
        $logicalId_name = $Cmd->getName();
        $_cmd_id = $Cmd->getLogicalId();

        if ($data['name'] == 'pushed' & $EqLogic->getConfiguration('type') == 'alarm_remote') {
            Free_Refresh::refresh_titles_remote($EqLogic, $data, $log_result, $Cmd);
        } else {
            switch ($Cmd->getSubType()) {
                case 'numeric':
                    $_value = Free_Refresh::refresh_titles_int($EqLogic, $data, $log_result, $Cmd, $logicalId_name, $_cmd_id);
                    break;
                case 'string':
                    $_value = Free_Refresh::refresh_titles_string($EqLogic, $data, $log_result, $Cmd, $logicalId_name, $_cmd_id);
                    break;
                case 'binary':
                    $_value = Free_Refresh::refresh_titles_bool($EqLogic, $data, $log_result, $Cmd, $logicalId_name, $_cmd_id);
                    break;
            }
            if ($Cmd->getConfiguration('TypeNode') == 'nodes') { // 
                $_cmd_ep_id = $data['id'];
            } else {
                $_cmd_ep_id = $data['ep_id'];
            }
            $EqLogic->checkAndUpdateCmd($_cmd_ep_id, $_value);
        }
    }
    private static function refresh_titles($EqLogics, $Free_API)
    {
        $results = $Free_API->universal_get('tiles', $EqLogics->getLogicalId(), null, null, true, true, FALSE);
        $log_result = true;
        if (config::byKey('FREEBOX_TILES_CRON', 'Freebox_OS') == 1) {
            $log_result == false;
        }
        if ($results != false) {
            foreach ($results as $result) {
                foreach ($result['data'] as $data) {
                    $cmd = $EqLogics->getCmd('info', $data['ep_id']);
                    if (is_object($cmd)) {
                        Free_Refresh::refresh_titles_CMD($cmd, $EqLogics, $data, $data['ep_id'], $log_result);
                    } else {
                        $cmdaction = $EqLogics->getCmd('action', $data['ep_id']);
                    }
                }
            }
        }
        if ($EqLogics->getConfiguration('type2') == 'pir' || $EqLogics->getConfiguration('type2') == 'alarm' || $EqLogics->getConfiguration('type2') == 'dws' || $EqLogics->getConfiguration('type') == 'camera' || $EqLogics->getConfiguration('type2') == 'alarm' || $EqLogics->getConfiguration('type2') == 'kfb' || $EqLogics->getConfiguration('type2') == 'shutter' || $EqLogics->getConfiguration('type2') == 'basic_shutter') {
            Free_Refresh::refresh_titles_nodes($EqLogics, $Free_API, $data['ep_id'], $log_result, $cmd);
        }
    }
    private static function refresh_titles_nodes($EqLogics, $Free_API, $ep_id, $log_result, $Cmd)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'home/nodes/' . $EqLogics->getLogicalId(), true, true, FALSE);
        foreach ($result['show_endpoints'] as $Cmd) {
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
        log::add('Freebox_OS', 'debug', '───▶︎ ETAT PLAYER DISPONIBLE ? : [  ' . $EqLogics->getConfiguration('player') . '  ]');
        if ($EqLogics->getConfiguration('player') == 'OK' && $EqLogics->getConfiguration('player_MAC') != 'MAC') {
            $results_playerID = $Free_API->universal_get('universalAPI', null, null, 'player/' . $EqLogics->getConfiguration('action') . '/api/v6/status', false, true, false);
            if (!isset($results_playerID['power_state'])) {
                log::add('Freebox_OS', 'debug', ':fg-info:l\'etat n\'est pas disponible car le Player n\'est pas joignable:/fg:');
                $player_power_state = 'standby';
            } else {
                log::add('Freebox_OS', 'debug', '───▶︎ l\'etat est disponible');
                $player_power_state = $results_playerID['power_state'];
            }
        } else {
            $player_power_state = 'KO';
            log::add('Freebox_OS', 'debug', ':fg-info:Il n\'est pas possible de récupérer le status du Player :/fg:');
        }

        $results_players = $Free_API->universal_get('universalAPI', null, null, 'player/', true, true, true);
        $results_players = $results_players['result'];
        foreach ($results_players as $results_player) {
            if ($EqLogics->getConfiguration('player_MAC') == 'MAC') {

                $results_player_ID = $results_player['mac'];
            } else {
                $results_player_ID = $results_player['id'];
            }
            if ($results_player_ID != $EqLogics->getConfiguration('action')) {
                continue;
            } else {
                log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ PLAYER TROUVE' . ':/fg:');
                foreach ($EqLogics->getCmd('info') as $cmd) {
                    if (is_object($cmd)) {
                        switch ($cmd->getLogicalId()) {
                            case "mac":
                                log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Adresse Mac : :/fg:' . $results_player['mac']);
                                $EqLogics->checkAndUpdateCmd($cmd->getLogicalId(), $results_player['mac']);
                                break;
                            case "stb_type":
                                if (isset($results_player['stb_type'])) {
                                    log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Type : :/fg:' . $results_player['stb_type']);
                                    $EqLogics->checkAndUpdateCmd($cmd->getLogicalId(), $results_player['stb_type']);
                                }
                                break;
                            case "api_version":
                                if (isset($results_player['api_version'])) {
                                    log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ API : :/fg:' . $results_player['api_version']);
                                    $EqLogics->checkAndUpdateCmd($cmd->getLogicalId(), $results_player['api_version']);
                                }
                                break;
                            case "device_model":
                                if (isset($results_player['device_model'])) {
                                    log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Modele : :/fg:' . $results_player['device_model']);
                                    $EqLogics->checkAndUpdateCmd($cmd->getLogicalId(), $results_player['device_model']);
                                }
                                break;
                            case "reachable":
                                log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Disponible sur le réseau : :/fg:' . $results_player['reachable']);
                                $EqLogics->checkAndUpdateCmd($cmd->getLogicalId(), $results_player['reachable']);
                                break;
                            case "power_state":
                                if ($player_power_state != 'KO') {
                                    log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Etat : :/fg:' . $player_power_state);
                                    $EqLogics->checkAndUpdateCmd($cmd->getLogicalId(), $player_power_state);
                                }
                                break;
                            case "api_available":
                                log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ API Disponible : :/fg:   ' . $results_player['api_available']);
                                $EqLogics->checkAndUpdateCmd($cmd->getLogicalId(), $results_player['api_available']);
                                break;
                        }
                    }
                }
                break;
            }
        }
    }
    private static function refresh_freeplug($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', $EqLogics->getLogicalId(), null, 'freeplug', true, true, false);
        if ($result['success'] === true) {
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
        } else {
            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ AUCUN FREEPLUG AVEC CET ID' . ':/fg:');
        }
    }
    private static function refresh_VM($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('universalAPI', $EqLogics->getConfiguration('action'), null, 'vm/', true, true, false);
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
        } else {
            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ AUCUNE VM AVEC CET ID' . ':/fg:');
            Freebox_OS::DisableEqLogic($EqLogics, false);
        }
    }
    private static function refresh_WebSocket($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('WebSocket', null, null, null);
    }
    private static function refresh_wifi($EqLogics, $Free_API)
    {
        log::add('Freebox_OS', 'debug', '───▶︎ Wifi : Update Liste Noire/Blanche');
        $listmac = $listmac = $Free_API->mac_filter_list();
        if ($listmac != false) {
            if ($listmac['listmac_blacklist'] != null || $listmac['listmac_whitelist'] != null) {
                log::add('Freebox_OS', 'debug', '───▶︎ Liste Noire : ' . $listmac['listmac_blacklist']);
                log::add('Freebox_OS', 'debug', '───▶︎ Liste Blanche : ' . $listmac['listmac_whitelist']);
            } else {
                log::add('Freebox_OS', 'debug', '───▶︎ Liste Noire/Blanche : Vide');
            }
        }
        $result_config = $Free_API->universal_get('universalAPI', null, null, 'wifi/config', true, true, true);
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
                        $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/planning', true, true, true);
                        if ($result['result']['use_planning']) {
                            $value = true;
                        }
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $value);
                        break;
                    case "has_eco_wifi":
                        $result = $Free_API->universal_get('system', null, null, null, true, true, null);
                        log::add('Freebox_OS', 'debug', '───▶︎ Update Mode eco Wifi: ' . $result['model_info']['has_eco_wifi']);
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result['model_info']['has_eco_wifi']);
                    case "wifiWPS":
                        $value = false;
                        $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/wps/config', true, true, true);
                        if ($result['result']['enabled']) {
                            $value = true;
                        }
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $value);
                        break;
                    case "wifimac_filter_state":
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result_config['result']['mac_filter_state']);
                        break;
                    case "planning_mode":
                        $result_mode = $Free_API->universal_get('universalAPI', null, null, 'standby/status', true, true, true);
                        if ($result_mode['result']['planning_mode'] == 'suspend') {
                            $value_mode = 'Veille totale';
                        } else if ($result_mode['result']['planning_mode'] == 'wifi_off') {
                            $value_mode = 'Veille WiFi';
                        }
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $value_mode);
                        break;
                    default:
                        $result_ap = $Free_API->universal_get('universalAPI', null, null, 'wifi/ap/' . $Command->getLogicalId(), true, true);
                        log::add('Freebox_OS', 'debug', '───▶︎ Status Carte ' . $result_ap['name'] . ' / ' . $Command->getLogicalId() . ' : ' . $result_ap['status']['state']);
                        $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $result_ap['status']['state']);
                        break;
                }
            }
        }
    }
}
