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
                    Free_Refresh::refresh_parental($EqLogics, $Free_API);
                    /*
                    foreach ($EqLogics->getCmd('info') as $Command) {
                        $results = $Free_API->universal_get('   parental', $EqLogics->getConfiguration('action'));
                        if ($results != false) {
                            $EqLogics->checkAndUpdateCmd($Command->getLogicalId(), $results['current_mode']);
                        } else {
                            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎  AUCUN CONTROLE PARENTAL AVEC CET ID :/fg:──');
                            Freebox_OS::DisableEqLogic($EqLogics, false);
                        }
                    }*/
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
    private static function refresh_parental($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null,  $para_Value_calcul = null)
    {
        $list = 'current_mode,cdayranges,macs';
        $result = $Free_API->universal_get('parental', $EqLogics->getConfiguration('action'));
        if (isset($result['profile_id'])) {
            $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
            $cdayranges = null;
            if (isset($result['cdayranges'])) {
                if ($result['cdayranges'] != null) {
                    foreach ($result['cdayranges'] as $cdayrange) {
                        if ($cdayrange == null) {
                            $cdayranges = $cdayrange;
                        } else {
                            $cdayranges .= '<br>' . $cdayrange;
                        }
                    }
                } else {
                    $cdayranges = 'Aucune periode de Vacances associées au profil';
                }
            }
            log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Vacances : ' . $cdayranges . ':/fg:');
            $macs = null;
            if (isset($result['macs'])) {
                if ($result['macs'] != null) {
                    foreach ($result['macs'] as $MAC) {
                        if ($macs == null) {
                            $macs = $MAC;
                        } else {
                            $macs .= '<br>' . $MAC;
                        }
                    }
                } else {
                    $macs = 'Aucun appareil associé au profil';
                }
            }
            $Value_calcul = array('cdayranges' => $cdayranges, 'macs' => $macs);
            Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul, $Value_calcul);
        } else {
            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ ' . 'Pas de contrôle de réseau existant avec ce profil' .  ':/fg:');
            Freebox_OS::DisableEqLogic($EqLogics, false);
        }
    }



    private static function refresh_airmedia($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_result2 = null, $para_Value_calcul = null, $para_result3 = null)
    {
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
    private static function refresh_connexion($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        $list = 'bandwidth_down,bandwidth_up,bytes_down,bytes_up,ipv4,ipv6,media,rate_down,rate_up,state';
        $result = $Free_API->universal_get('connexion', null, null, null);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);

        $result = $Free_API->universal_get('connexion', null, null, null);
        if ($result != false) {
            foreach ($EqLogics->getCmd('info') as $Command) {
                if (is_object($Command)) {
                    switch ($Command->getLogicalId()) {
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

    private static function refresh_connexion_4G($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Connexion 4G - xDSL');
        $result = $Free_API->universal_get('universalAPI', null, null, 'connection/aggregation', true, true, true);

        $list = 'rx_max_rate,rx_used_rate,tx_max_rate,tx_used_rate';
        $para_LogicalId = array('rx_max_rate' => 'rx_max_rate_lte', 'rx_used_rate' => 'rx_used_rate_lte', 'tx_max_rate' => 'tx_max_rate_lte', 'tx_used_rate' => 'tx_used_rate_lte');
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Connexion 4G - xDSL / lte');
        $para_result = array('nb' => 3, 1 => 'result', 2 => 'tunnel', 3 => 'lte');
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, null);

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Connexion 4G - xDSL / xDSL');
        $para_LogicalId = array('rx_max_rate' => 'rx_max_rate_xdsl', 'rx_used_rate' => 'rx_used_rate_xdsl', 'tx_max_rate' => 'tx_max_rate_xdsl', 'tx_used_rate' => 'tx_used_rate_xdsl');
        $para_result = array('nb' => 3, 1 => 'result', 2 => 'tunnel', 3 => 'xdsl');
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, null);
        $para_LogicalId = null;

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Connexion 4G - xDSL / Config');
        $list = 'enabled';
        $para_LogicalId = array('enabled' => 'state');
        $para_result = array('nb' => 1, 1 => 'result', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
        $para_LogicalId = null;
    }
    private static function refresh_connexion_Config($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Config PING');
        $list = 'ping,wol';
        $result =  $Free_API->universal_get('connexion', null, null, 'config', true, true, null);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
    }

    private static function refresh_connexion_FTTH($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Connexion FTTH');
        $list = 'link_type,sfp_present,sfp_has_signal,sfp_alim_ok,sfp_pwr_tx,sfp_pwr_rx';
        $result = $Free_API->universal_get('connexion', null, null, 'ftth', true, true, null);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
    }

    private static function refresh_disk($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, null);
        $log_Erreur = 'AUCUN DISQUE';
        if ($result != false) {
            foreach ($result as $disks) {
                $list = 'temp,spinning';
                $para_LogicalId = array('temp' => $disks['id'] . '_temp', 'spinning' => $disks['id'] . '_spinning');
                $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
                Free_Refresh::refresh_VALUE($EqLogics, $disks, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
                $para_LogicalId = null;
                foreach ($disks['partitions'] as $partition) {
                    if ($partition['total_bytes'] != null) {
                        $value = $partition['used_bytes'] / $partition['total_bytes'];
                    } else {
                        $value = 0;
                    }
                    foreach ($EqLogics->getCmd('info') as $Cmd) {
                        if ($Cmd->getLogicalId() != $partition['id']) continue;
                        $EqLogics->checkAndUpdateCmd($partition['id'], $value);
                        log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ ' . $Cmd->getName() . ' ::/fg: ' . $value);
                    }
                }
            }
        } else {
            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ ' . $log_Erreur .  ':/fg:');
        }
        $log_Erreur = null;

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: RAID');
        $Type_box = config::byKey('TYPE_FREEBOX', 'Freebox_OS');
        $log_Erreur = 'AUCUN DISQUE RAID';
        if ($Type_box != 'fbxgw1r' && $Type_box != 'fbxgw2r') {
            $result = $Free_API->universal_get('universalAPI', null, null, 'storage/raid', true, true, null);
            if ($result != false) {
                log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Ancien Disque --- TEST 1');
                foreach ($result as $disks) {
                    $list = 'state,sync_action,role,degraded';
                    $para_LogicalId = array('state' => $disks['id'] . '_state', 'sync_action' => $disks['id'] . '_sync_action', 'role' => $disks['id'] . '_role', 'degraded' => $disks['id'] . '_degraded');
                    $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
                    Free_Refresh::refresh_VALUE($EqLogics, $disks, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
                    $para_LogicalId = null;
                    foreach ($disks['members'] as $members_raid) {
                        if ($Cmd->getLogicalId() != $members_raid['id'] . '_role') continue;
                        $EqLogics->checkAndUpdateCmd($members_raid['id'] . '_role', $members_raid['role']);
                        log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ ' . $Cmd->getName() . ' ::/fg: ' . $members_raid['role']);
                    }
                }
            } else {
                log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ ' . $log_Erreur .  ':/fg:');
            }
            $log_Erreur = null;
        }
    }

    private static function refresh_download($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Config');
        $list = 'mode';
        $result = $Free_API->universal_get('universalAPI', null, null, 'downloads/config', true, true, true);
        $para_result = array('nb' => 2, 1 => 'result', 2 => 'throttling', 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Etat');
        $list = 'conn_ready,throttling_is_scheduled,nb_tasks,nb_tasks_downloading,nb_tasks_done,nb_rss,nb_rss_items_unread,rx_rate,tx_rate,nb_tasks_active,nb_tasks_stopped,nb_tasks_queued,nb_tasks_repairing,nb_tasks_extracting,nb_tasks_error,nb_tasks_checking';
        $para_Value_calcul  = array('rx_rate' => '_bcdiv_', 'tx_rate' => '_bcdiv_');
        $result = $Free_API->universal_get('universalAPI', null, null, 'downloads/stats', true, true, true);
        $para_result = array('nb' => 1, 1 => 'result', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
        $para_Value_calcul = null;
    }
    private static function refresh_LCD($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        $list = 'orientation,orientation_forced,brightness,hide_wifi_key';
        $result = $Free_API->universal_get('universalAPI', null, null, 'lcd/config/', true, true, null);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, null);
    }

    private static function refresh_phone($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null,  $para_Value_calcul = null)
    {
        $log_Erreur = 'AUCUN APPEL';
        $list = 'missed,accepted,outgoing,listmissed,listaccepted,listoutgoing';
        $result = $Free_API->nb_appel_absence();
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
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

    private static function refresh_netshare($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null,  $para_Value_calcul = null)
    {
        $list = 'file_share_enabled,print_share_enabled,smbv2_enabled';
        $result = $Free_API->universal_get('universalAPI', null, null, 'netshare/samba', true, true, false);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur,  $para_Value_calcul);

        $para_LogicalId = array('enabled' => 'mac_share_enabled');
        $list = 'enabled';
        $result = $Free_API->universal_get('universalAPI', null, null, 'netshare/afp', true, true, false);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur,  $para_Value_calcul);

        $para_LogicalId = array('enabled' => 'FTP_enabled');
        $list = 'enabled';
        $result = $Free_API->universal_get('universalAPI', null, null, 'ftp/config', true, true, false);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur,  $para_Value_calcul);
    }
    public static function Free_removeLogicId($eqLogic, $cmdDell)
    {
        //  suppression fonction
        $cmd = $eqLogic->getCmd('info', $cmdDell);
        if (is_object($cmd)) {
            $cmd->remove();
        }
    }
    private static function refresh_system($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        log::add('Freebox_OS', 'debug', '───▶︎ Récupération des valeurs du Système');
        // Config réeseau
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: LAN');
        $para_Config = array('name' => 'TYPE_FREEBOX_NAME', 'mode' => 'TYPE_FREEBOX_MODE');
        $list = 'ip,mode,name';
        $result =  $Free_API->universal_get('network', null, null, 'lan/config/', true, true, true);
        $para_result = array('nb' => 1, 1 => 'result', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
        $para_Config = null;

        //Config Lang
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: langue');
        $para_Value = array('lang__fra' => 'Français', 'lang_eng' => 'English', 'lang_ita' => 'Italiano');
        $list = 'lang';
        $result = $Free_API->universal_get('universalAPI', null, null, 'lang', true, true, null);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur,  $para_Value_calcul);
        $para_Value = null;

        //Mise à jour
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: MISE A JOUR');
        $para_Value = array('state__initializing_' => 'Le processus de mise à jour est en cours d\'initialisation', 'state__upgrading' => 'Le micrologiciel est en cours de mise à jour', 'state__up_to_date' => 'Le micrologiciel est à jour', 'state__error' => 'Une erreur s\'est produite pendant la mise à jour');
        $list = 'state';
        $result = $Free_API->universal_get('universalAPI', null, null, 'update', true, true, null);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur,  $para_Value_calcul);

        // Système
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour : Autres' .  ':/fg:');
        $list = 'name,pretty_name,wifi_type,has_standby,has_eco_wifi';
        $para_LogicalId = array('name' => 'model_name');
        $result = $Free_API->universal_get('system', null, null, null, true, true, null);
        $para_result = array('nb' => 1, 1 => 'model_info', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur,  $para_Value_calcul);
        $para_LogicalId = null;

        $list = 'firmware_version,mac,uptime,board_name,info';
        $para_Value_calcul  = array('uptime' => '_TIME_');
        $para_Config = array('board_name' => 'TYPE_FREEBOX');
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur,  $para_Value_calcul);
        $para_Config = null;
        $para_Value_calcul = null;

        // Mise à jour Sensors / Fan / Expansions
        Free_Refresh::refresh_system_sensor($EqLogics, $result);

        foreach ($EqLogics->getCmd('info') as $Cmd) {
            switch ($Cmd->getConfiguration('logicalId')) {
                case "4G":
                    Free_Refresh::refresh_system_4G($EqLogics, $Free_API, $para_LogicalId, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null);
                    break;
                default:
                    break;
            }
        }
    }
    private  static function refresh_system_sensor($EqLogics, $result)
    {
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Sensors');
        foreach ($EqLogics->getCmd('info') as $Cmd) {
            foreach ($result['fans'] as $system) {
                if ($Cmd->getLogicalId('data') == $system['id']) {
                    if (isset($system['value'])) {
                        $EqLogics->checkAndUpdateCmd($system['id'], $system['value']);
                        log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ ' . $Cmd->getName() . ' ::/fg: ' . $system['value']);
                    }
                }
            }
        }
        foreach ($EqLogics->getCmd('info') as $Cmd) {
            foreach ($result['sensors'] as $system) {
                if ($Cmd->getLogicalId('data') == $system['id']) {
                    if (isset($system['value'])) {
                        $EqLogics->checkAndUpdateCmd($system['id'], $system['value']);
                        log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ ' . $Cmd->getName() . ' ::/fg: ' . $system['value']);
                    }
                }
            }
        }
        foreach ($EqLogics->getCmd('info') as $Cmd) {
            if (isset($result['expansions'])) {
                foreach ($result['expansions'] as $system) {
                    if ($Cmd->getLogicalId('data') == $system['slot']) {
                        if (isset($system['present'])) {
                            $EqLogics->checkAndUpdateCmd($system['id'], $system['present']);
                            log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ ' . $Cmd->getName() . ' ::/fg: ' . $system['present']);
                        }
                    }
                }
            } else {
                //log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ AUCUNE CARTE D\'EXTENSION' . ':/fg:');
            }
        }
    }
    private static function refresh_system_4G($EqLogics, $Free_API, $para_LogicalId, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: 4G');

        $list = 'enabled,state';
        $para_LogicalId = array('enabled' => '4GStatut', 'state' => 'state_lte');
        $result = $Free_API->universal_get('universalAPI', null, null, 'connection/lte/aggregation', true, true, true);
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Connexion 4G - xDSL / lte');
        $para_result = array('nb' => 1, 1 => 'result', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);

        $list = 'associated';
        $para_LogicalId = array('associated' => 'associated_lte');
        $para_result = array('nb' => 2, 1 => 'result', 2 => 'radio', 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, 'radio', $para_Value_calcul);
        $para_LogicalId = null;
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
    private static function refresh_player($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
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
                foreach ($EqLogics->getCmd('info') as $Cmd) {
                    if (is_object($Cmd)) {
                        switch ($Cmd->getLogicalId()) {
                            case "power_state":
                                if ($player_power_state != 'KO') {
                                    log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Etat ::/fg: ' . $player_power_state);
                                    $EqLogics->checkAndUpdateCmd($Cmd->getLogicalId(), $player_power_state);
                                }
                                break;
                            default:
                                if (isset($results_player[$Cmd->getLogicalId()])) {
                                    $EqLogics->checkAndUpdateCmd($Cmd->getLogicalId(), $results_player[$Cmd->getLogicalId()]);
                                    log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ ' . $Cmd->getName() . ' ::/fg: ' . $results_player[$Cmd->getLogicalId()]);
                                }
                                break;
                                break;
                                log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ API Disponible ::/fg:    ' . $results_player['api_available']);
                                $EqLogics->checkAndUpdateCmd($Cmd->getLogicalId(), $results_player['api_available']);
                                break;
                        }
                    }
                }
                break;
            }
        }
    }
    private static function refresh_freeplug($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        $list = 'net_role,rx_rate,tx_rate';
        $log_Erreur = 'Erreur freeplug : Pas de plug avec cet identifiant';
        $para_Value = array('net_role__cco' => 'Coordinateur', 'net_role__sta' => 'Station');
        $result = $Free_API->universal_get('universalAPI', $EqLogics->getLogicalId(), null, 'freeplug', true, true, false);
        $para_result = array('nb' => 1, 1 => 'result', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
        $log_Erreur = null;
        $para_Value = null;
    }
    private static function refresh_VM($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        $list = 'enable_screen,disk_type,mac,memory,name,status,vcpus,bind_usb_ports';
        $log_Erreur = 'VM : Impossible de récupérer l’état de cette VM : La VM n’existe pas';
        $result = $Free_API->universal_get('universalAPI', $EqLogics->getConfiguration('action'), null, 'vm/', true, true, false);
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        $bind_usb_ports = null;
        if (isset($result['id'])) {
            if ($result['bind_usb_ports'] != null) {
                if (isset($result['bind_usb_ports'])) {
                    foreach ($result['cdayranges'] as $USB) {
                        if ($bind_usb_ports == null) {
                            $bind_usb_ports = $USB;
                        } else {
                            $bind_usb_ports .= '<br>' . $USB;
                        }
                    }
                }
            } else {
                $bind_usb_ports = 'Aucun port USB de connecté';
            }
            $Value_calcul = array('bind_usb_ports' => $bind_usb_ports);
            Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul, $Value_calcul);
            $log_Erreur = null;
        } else {
            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ ' . $log_Erreur .  ':/fg:');
            $log_Erreur = null;
            Freebox_OS::DisableEqLogic($EqLogics, false);
        }
    }
    private static function refresh_WebSocket($EqLogics, $Free_API)
    {
        $result = $Free_API->universal_get('WebSocket', null, null, null);
    }
    private static function refresh_wifi($EqLogics, $Free_API, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null)
    {
        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Liste Noire/Blanche');
        $result = $Free_API->mac_filter_list();
        $list = 'blacklist,whitelist';
        $para_result = array('nb' => 0, 1 => null, 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Eco Energie');
        $result = $Free_API->universal_get('system', null, null, null, true, true, null);
        $list = 'has_eco_wifi';
        $para_result = array('nb' => 1, 1 => 'model_info', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Planning Mode');
        $list = 'planning_mode';
        $para_Value = array('planning_mode__suspend' => 'Veille totale', 'planning_mode__wifi_off' => 'Veille WiFi');
        $result = $Free_API->universal_get('universalAPI', null, null, 'standby/status', true, true, true);
        $para_result = array('nb' => 1, 1 => 'result', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
        $para_Value = null;

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Planning');
        $list = 'use_planning';
        $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/planning', true, true, true);
        $para_result = array('nb' => 1, 1 => 'result', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Config');
        $list = 'mac_filter_state,enabled';
        $para_LogicalId = array('enabled' => 'wifiStatut');
        $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/config', true, true, true);
        $para_result = array('nb' => 1, 1 => 'result', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
        $para_LogicalId = null;

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: WPS');
        $list = 'enabled';
        $para_LogicalId = array('enabled' => 'wifiWPS');
        $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/config', true, true, true);
        $para_result = array('nb' => 1, 1 => 'result', 2 => null, 3 => null);
        Free_Refresh::refresh_VALUE($EqLogics, $result, $list, $para_result, $para_LogicalId, $para_Value, $para_Config, $log_Erreur, $para_Value_calcul);
        $para_LogicalId = null;

        log::add('Freebox_OS', 'debug', '──────────▶︎ :fg-success: Mise à jour ::/fg: Status des Cartes');
        $result_ap = $Free_API->universal_get('universalAPI', null, null, 'wifi/ap', true, true, true);
        $nb_card = count($result_ap['result']);
        $Card_value = null;
        $Card_id = null;
        if ($result_ap != false) {
            for ($k = 0; $k < $nb_card; $k++) {
                $Card_value = $result_ap['result'][$k]['status']['state'];
                $Card_id = $result_ap['result'][$k]['id'];
                foreach ($EqLogics->getCmd('info') as $Cmd) {
                    if ($Cmd->getLogicalId('data') == $Card_id) {
                        if ($Cmd->getConfiguration('logicalId') == 'CARD') {
                            $EqLogics->checkAndUpdateCmd($Card_id, $Card_value);
                            log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ ' . $Cmd->getName() . ' ::/fg: ' . $Card_value);
                        }
                    }
                }
            }
        }
    }
    private  static function refresh_VALUE($EqLogics, $result, $list, $para_result = null, $para_LogicalId = null, $para_Value = null, $para_Config = null, $log_Erreur = null, $para_Value_calcul = null, $Value_calcul = null)
    {
        if ($para_result['nb'] != 0) {
            if ($para_result['nb'] === 1) {
                if (isset($result)) {
                    $result = $result[$para_result[1]];
                    //log::add('Freebox_OS', 'debug', ':fg-info:Niveau 1 ───▶︎ :/fg:' . $para_result[1]);
                }
            } else if ($para_result['nb'] === 2) {
                $result = $result[$para_result[1]][$para_result[2]];
                //log::add('Freebox_OS', 'debug', ':fg-info:Niveau 2 ───▶︎ :/fg:' . $para_result[1] . '/' . $para_result[2]);
            } else if ($para_result['nb'] === 3) {
                $result = $result[$para_result[1]][$para_result[2]][$para_result[3]];
                //log::add('Freebox_OS', 'debug', ':fg-info:Niveau 3 ───▶︎ :/fg:' . $para_result[1] . '/' . $para_result[2] . '/' . $para_result[3]);
            }
        }

        if ($result != false) {
            $fields = explode(',', $list);

            foreach ($EqLogics->getCmd('info') as $Cmd) {
                foreach ($fields as $fieldname) {
                    $fielLogicalId = $fieldname;
                    if ($para_LogicalId != null) { // Récupération du LogicalId si différent entre Jeedom et Freebox
                        if (isset($para_LogicalId[$fieldname])) {
                            $fielLogicalId = $para_LogicalId[$fieldname];
                            //log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Remplacement Logicalid' . ' ::/fg: ' . $fielLogicalId);
                        }
                    }
                    if ($Cmd->getLogicalId('data') == $fielLogicalId) {
                        if (isset($result[$fieldname])) {
                            $value = $result[$fieldname];
                            if ($para_Value != null) { // Récupération de la valeur si différent entre Jeedom et Freebox
                                $fieldnameValue = $fieldname . '__' . $value;
                                if (isset($para_Value[$fieldnameValue])) {
                                    $value = $para_Value[$fieldname . '__' . $value];
                                    //log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Remplacement Valeur' . ' ::/fg: ' . $value);
                                }
                            }
                            if ($para_Value_calcul != null) {
                                if (isset($para_Value_calcul[$fieldname])) {
                                    if ($para_Value_calcul[$fieldname] === '_TIME_') {
                                        $_uptime = $value;
                                        $_uptime = str_replace(' heure ', 'h ', $_uptime);
                                        $_uptime = str_replace(' heures ', 'h ', $_uptime);
                                        $_uptime = str_replace(' minute ', 'min ', $_uptime);
                                        $_uptime = str_replace(' minutes ', 'min ', $_uptime);
                                        $_uptime = str_replace(' secondes', 's', $_uptime);
                                        $_uptime = str_replace(' seconde', 's', $_uptime);
                                        $value = $_uptime;
                                        //log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Calcul Temps' . ' ::/fg: ' . $value);
                                    }
                                    if ($para_Value_calcul[$fieldname] === '_bcdiv_') {
                                        if (function_exists('bcdiv')) {
                                            $value = bcdiv($value, 1048576, 2);
                                        }
                                        //Log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Calcul bcdiv' . ' ::/fg: ' . $value);
                                    }
                                }
                            }
                            if ($Value_calcul != null) {
                                if (isset($Value_calcul[$fieldname])) {
                                    $value = $Value_calcul[$fieldname];
                                }
                            }
                            $EqLogics->checkAndUpdateCmd($fielLogicalId, $value);
                            log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ ' . $Cmd->getName() . ' ::/fg: ' . $value);

                            if ($para_Config != null) { // Mise à jour des paramétres Config
                                if (isset($para_Config[$fieldname])) {
                                    config::save($para_Config[$fieldname], $value, 'Freebox_OS');
                                    log::add('Freebox_OS', 'debug', ':fg-info:───▶︎ Update Config Plugin ' . ' ::/fg: ' . $para_Config[$fieldname]);
                                }
                            }
                            break;
                        }
                    }
                }
            }
        } else {
            if ($log_Erreur != null) {
                log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ ' . $log_Erreur .  ':/fg:');
            }
        }
    }
}
