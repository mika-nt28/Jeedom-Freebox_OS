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

class Free_Update
{
    public static function UpdateAction($logicalId, $logicalId_type, $logicalId_name, $logicalId_value, $logicalId_conf, $logicalId_eq, $_options, $_cmd)
    {
        if ($logicalId != 'refresh' && $logicalId != 'WakeonLAN') {
            //log::add('Freebox_OS', 'debug', '┌───────── Update commande ');
            //log::add('Freebox_OS', 'debug', '│ Connexion sur la freebox pour mise à jour de : ' . $logicalId_name);
        }
        $Free_API = new Free_API();
        if ($logicalId_eq->getconfiguration('type') == 'parental' || $logicalId_eq->getConfiguration('type') == 'player'  || $logicalId_eq->getConfiguration('type') == 'VM') {
            $update = $logicalId_eq->getconfiguration('type');
        } else {
            $update = $logicalId_eq->getLogicalId();
        }
        switch ($update) {
            case 'airmedia':
                if ($logicalId != 'refresh') {
                    Free_Update::update_airmedia($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd);
                    Free_Refresh::RefreshInformation($logicalId_eq->getId());
                } else {
                    log::add('Freebox_OS', 'debug', '│ Pas de fonction rafraichir pour cet équipement');
                    log::add('Freebox_OS', 'debug', '└─────────');
                }
                break;
            case 'connexion':
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'disk':
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'downloads':
                Free_Update::update_download($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'LCD':
                if ($logicalId != 'refresh') {
                    Free_Update::update_LCD($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                }
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'homeadapters':
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'parental':
                if ($logicalId != 'refresh') {
                    Free_Update::update_parental($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd, $update);
                }
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'phone':
                Free_Update::update_phone($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'player':
                if ($logicalId != 'refresh') {
                    Free_Update::update_player($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd, $update);
                }
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'netshare':
                Free_Update::update_netshare($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'network':
            case 'networkwifiguest':
                if ($logicalId != 'refresh') {
                    Free_Update::update_network($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $update);
                }
                if ($logicalId != 'WakeonLAN') {
                    Free_Refresh::RefreshInformation($logicalId_eq->getId());
                }
                break;
            case 'system':
                Free_Update::update_system($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                if ($logicalId != 'reboot') {
                    Free_Refresh::RefreshInformation($logicalId_eq->getId());
                }
                break;
            case 'VM':
                if ($logicalId != 'refresh') {
                    Free_Update::update_VM($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $update);
                }
            case 'wifi':
                if ($logicalId != 'refresh') {
                    Free_Update::update_wifi($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                }
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            default:
                Free_Update::update_default($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd, $logicalId_conf);
                //if ($logicalId == 'refresh' || config::byKey('FREEBOX_TILES_CRON', 'Freebox_OS') == 0) {
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                //}
                break;
        }
    }
    private static function update_VM($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $update)
    {
        $Free_API->universal_put(null, $logicalId_eq->getconfiguration('type'), $logicalId_eq->getConfiguration('action'), null, null, null, $logicalId);
    }
    private static function update_airmedia($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd)
    {
        $receivers = $logicalId_eq->getCmd(null, "ActualAirmedia");
        log::add('Freebox_OS', 'debug', '│ Media type : ' . $_options['titre'] . ' -- Media : ' . $_options['message'] . ' -- Action : ' . $logicalId);

        if (!is_object($receivers) || $receivers->execCmd() == "" || $_options['titre'] == null) {
            log::add('Freebox_OS', 'error', '[AirPlay] Impossible d\'envoyer la demande, les paramètres sont incomplets' . $receivers->execCmd() . ' type :' . $_options['titre']);
            return;
        }
        $Parameter["media_type"] = $_options['titre'];
        $Parameter["media"] = $_options['message'];
        $Parameter["password"] = $_cmd->getConfiguration('password');
        switch ($logicalId) {
            case "airmediastart":
                $Parameter["action"] = "start";
                $Free_API->airmedia('action', $Parameter, $receivers->execCmd());
                break;
            case "airmediastop":
                $Parameter["action"] = "stop";
                $Free_API->airmedia('action', $Parameter, $receivers->execCmd());
                break;
        }
    }

    private static function update_download($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options)
    {
        $result = $Free_API->universal_get('download', null, null, 'stats/');
        if ($result != false) {
            switch ($logicalId) {
                case "stop_dl":
                    $Free_API->downloads_put(0);
                    break;
                case "start_dl":
                    $Free_API->downloads_put(1);
                    break;
            }
        }
        if ($result != false) {
            switch ($logicalId) {
                case "normal":
                case "slow":
                case "hibernate":
                    $parametre['throttling'] = $logicalId;
                    $Free_API->universal_put($parametre, 'download', null, null, null);
                    break;
                case "schedule":
                    $parametre['throttling'] = $logicalId;
                    $parametre['is_scheduled'] = true;
                    $Free_API->universal_put($parametre, 'download', null, null, null);
                    break;
            }
        }
    }

    private static function update_netshare($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options)
    {
        switch ($logicalId) {
            case "FTP_enabledOn":
                $Free_API->universal_put(true, 'universalAPI', 'ftp/config', null, 'enabled', null);
                break;
            case "FTP_enabledOff":
                $Free_API->universal_put(false, 'universalAPI', 'ftp/config', null, 'enabled', null);
                break;
            case "file_share_enabledOn":
                $Free_API->universal_put(true, 'universalAPI', 'netshare/samba', null, 'file_share_enabled', null);
                break;
            case "file_share_enabledOff":
                $Free_API->universal_put(false, 'universalAPI', 'netshare/samba', null, 'file_share_enabled', null);
                break;
            case "mac_share_enabledOn":
                $Free_API->universal_put(true, 'universalAPI', 'netshare/afp', null, 'enabled', null);
                break;
            case "mac_share_enabledOff":
                $Free_API->universal_put(false, 'universalAPI', 'netshare/afp', null, 'enabled', null);
                break;
            case "print_share_enabledOn":
                $Free_API->universal_put(true, 'universalAPI', 'netshare/samba', null, 'print_share_enabled', null);
                break;
            case "print_share_enabledOff":
                $Free_API->universal_put(false, 'universalAPI', 'netshare/samba', null, 'print_share_enabled', null);
                break;
            case "smbv2_enabledOn":
                $Free_API->universal_put(true, 'universalAPI', 'netshare/samba', null, 'smbv2_enabled', null, true);
                break;
            case "smbv2_enabledOff":
                $Free_API->universal_put(false, 'universalAPI', 'netshare/samba', null, 'smbv2_enabled', null, true);
                break;
        }
    }
    private static function update_network($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $network)
    {
        switch ($logicalId) {
            case "search":
                Free_CreateEq::createEq($network, false);
                break;
            case "WakeonLAN":
                if ($_options['mac_address'] == null) {
                    log::add('Freebox_OS', 'error', 'Adresse mac vide');
                    break;
                }
                $option = null;
                $option = array(
                    "mac" => $_options['mac_address'],
                    "password" => $_options['password']
                );
                $Free_API->universal_put(null, 'universal_put', $_options['mac_address'], null, 'lan/wol/pub/', null, $option);
                break;
            case "add_del_mac":
                if ($_options['ip'] == null || $_options['mac_address'] == null) {
                    log::add('Freebox_OS', 'error', 'IP  ou adresse mac vide');
                    break;
                }
                $option = null;
                $option = array(
                    "mac" => $_options['mac_address'],
                    "ip" => $_options['ip'],
                    "comment" => $_options['comment'],
                );
                if ($_options['function'] != 'device') {
                    $Free_API->universal_put(null, 'universal_put', $_options['mac_address'], null, 'dhcp/static_lease/', $_options['function'], $option);
                }
                $option = array(
                    "id" => 'ether-' . $_options['mac_address'],
                    "primary_name" => $_options['name'],
                    'host_type'  => $_options['type']
                );
                $Free_API->universal_put(null, 'universal_put', 'ether-' . $_options['mac_address'], null, 'lan/browser/pub', 'PUT', $option);
                break;
            case "redir":
                if ($_options['lan_ip'] == null) {
                    log::add('Freebox_OS', 'error', 'Adresse IP vide');
                    break;
                }
                $option = null;
                $option = array(
                    'enabled' =>  $_options['enable_lan'],
                    'comment' =>  $_options['comment'],
                    'lan_port' =>  $_options['lan_port'],
                    'wan_port_end' =>  $_options['wan_port_end'],
                    'wan_port_start' =>  $_options['wan_port_start'],
                    'lan_ip' =>  $_options['lan_ip'],
                    'ip_proto' => $_options['ip_proto'],
                    'src_ip' =>  $_options['src_ip']
                );
                $Free_API->universal_put(null, 'universal_put', null, null, 'fw/redir', 'POST', $option);
                break;
        }
    }
    private static function update_parental($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd, $update)
    {
        $cmd = cmd::byid($_cmd->getvalue());

        if ($cmd !== false) {
            $_status = $cmd->execCmd();
        }
        $Free_API->universal_put($logicalId, $update, $logicalId_eq->getConfiguration('action'), null, $_options, $_status);
        Free_Refresh::RefreshInformation($logicalId_eq->getId());
    }

    private static function update_player($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd, $update)
    {
        $Free_API->universal_put($logicalId, 'player_ID_ctrl', $logicalId_eq->getConfiguration('action'), null, $_options);
    }

    private static function update_phone($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options)
    {
        $result = $Free_API->nb_appel_absence();
        if ($result != false) {
            switch ($logicalId) {
                case "phone_dell_call":
                    $Free_API->universal_put(null, 'phone', null, null, 'delete_all');
                    break;
                case "phone_read_call":
                    $Free_API->universal_put(null, 'phone', null, null, 'mark_all_as_read');
                    break;
            }
        }
    }

    private static function update_system($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options)
    {
        switch ($logicalId) {
            case "reboot":
                $Free_API->universal_put(null, 'reboot', null, null, null);
                break;
            case '4GOn':
                $Free_API->universal_put(1, '4G', null, null, null);
                break;
            case '4GOff':
                $Free_API->universal_put(0, '4G', null, null, null);
                break;
        }
    }
    private static function update_LCD($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options)
    {
        switch ($logicalId) {
            case 'brightness':
                $Free_API->universal_put(1, 'universal_put', null, null, 'lcd/config', 'PUT', array('brightness' => $_options['slider']));
                break;
            case 'hide_wifi_keyOn':
                $Free_API->universal_put(1, 'universal_put', null, null, 'lcd/config', 'PUT', array('hide_wifi_key' => true));
                break;
            case 'hide_wifi_keyOff':
                $Free_API->universal_put(1, 'universal_put', null, null, 'lcd/config', 'PUT', array('hide_wifi_key' => false));
                break;
            case 'orientation':
                if ($_options['select'] != 0) {
                    $orientation_forced = true;
                } else {
                    $orientation_forced = false;
                }
                $Free_API->universal_put(1, 'universal_put', null, null, 'lcd/config', 'PUT', array('orientation' => $_options['select'], 'orientation_forced' => $orientation_forced));
                break;
        }
    }
    private static function update_wifi($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options)
    {
        if ($logicalId != 'refresh') {
            switch ($logicalId) {
                case 'mac_filter_state':
                    $Free_API->universal_put($_options['select'], 'wifi', null, null, 'config', null, 'mac_filter_state');
                    break;
                case 'add_del_mac';
                    if ($_options['function'] == null || $_options['filter'] == null || $_options['mac_address'] == null) {
                        log::add('Freebox_OS', 'error', 'Méthode Filtrage  ou type de Filtrage incorrect ');
                        break;
                    }

                    $Free_API->universal_put(null, 'wifi', $_options, null, 'mac_filter');
                case 'wifiOn':
                    $Free_API->universal_put(1, 'wifi', null, null, 'config');
                    break;
                case 'wifiOff':
                    $Free_API->universal_put(0, 'wifi', null, null, 'config');
                    break;
                case 'wifiPlanningOn':
                    $Free_API->universal_put(1, 'wifi', null, null, 'planning');
                    break;
                case 'wifiPlanningOff':
                    $Free_API->universal_put(0, 'wifi', null, null, 'planning');
                    break;
                case 'wifiSessionWPSOff':
                case 'wifiWPSOff':
                    $Free_API->universal_put(0, 'wifi', null, null, 'wps/stop');
                    break;
                default:
                    $Free_API->universal_put($logicalId, 'wifi', null, null, 'wps/start');
                    break;
            }
        }
    }

    private static function update_default($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd, $logicalId_conf)
    {
        $_execute = 1;
        switch ($logicalId_type) {
            case 'slider':
                if ($_cmd->getConfiguration('invertslide')) {
                    log::add('Freebox_OS', 'debug', '│ Inverse Slider ');
                    $parametre['value'] = ($_cmd->getConfiguration('maxValue') - $_cmd->getConfiguration('minValue')) - $_options['slider'];
                } else {
                    $parametre['value'] = (int) $_options['slider'];
                }
                $parametre['value_type'] = 'int';

                $action = $logicalId_eq->getConfiguration('action');
                $type = $logicalId_eq->getConfiguration('type');
                log::add('Freebox_OS', 'debug', '│ type : ' . $type . ' -- action : ' . $action . ' -- valeur type : ' . $parametre['value_type'] . ' -- valeur Inversé  : ' . $_cmd->getConfiguration('invertslide') . ' -- valeur  : ' . $parametre['value'] . ' -- valeur slider : ' . $_options['slider']);
                if ($action == 'intensity_picker' || $action == 'color_picker') {
                    $cmd = cmd::byid($_cmd->getConfiguration('binaryID'));
                    /*if ($cmd !== false) {
                        if ($cmd->execCmd() == 0) {
                            $_execute = 0;
                            log::add('Freebox_OS', 'debug', '│ Pas d\'action car l\'équipement est éteint');
                        }
                    }*/
                }
                break;
            case 'color':
                /*list($r, $g, $b) = str_split(str_replace('#', '', $_options['color']), 2);
                $info = Free_Color::convertRGBToXY(hexdec($r), hexdec($g), hexdec($b));
                $replace['#color#'] = round($info['x'] * 65535) . '::' . round($info['y'] * 65535);


                if ($replace['#color#'] == '000000') {
                    $bright = '00';
                    log::add('Freebox_OS', 'debug', '>──────────> ETEINDRE LA LAMPE');
                } else {
                    //$_value = $bright . $color;
                    //$_value = hexdec($_value);
                    log::add('Freebox_OS', 'debug', '>──────────> RGB EN HEX : ' . $replace['#color#']);
                    $parametre['value'] = $replace['#color#'];
                    $parametre['value_type'] = 'int';
                    $cmd = cmd::byid($_cmd->getConfiguration('binaryID'));
                    if ($cmd !== false) {
                        if ($cmd->execCmd() == 0) {
                            $_execute = 0;
                            log::add('Freebox_OS', 'debug', '│ Pas d\'action car l\'équipement est éteint');
                        }
                    }
                }*/
                break;
            case 'message':
                $parametre['value'] = $_options['message'];
                $parametre['value_type'] = 'void';
                break;
            case 'select':
                $parametre['value'] = $_options['select'];
                $parametre['value_type'] = 'void';
                break;
            default:
                $parametre['value_type'] = 'bool';
                if ($logicalId_conf >= 0 && (stripos($logicalId, 'PB_On') !== FALSE || stripos($logicalId, 'PB_Off') !== FALSE)) {
                    //log::add('Freebox_OS', 'debug', '│ Paramétrage spécifique BP ON/OFF : ' . $logicalId_conf);
                    if (stripos($logicalId, 'PB_On')  == 'PB_On') {
                        $parametre['value'] = true;
                    } else {
                        $parametre['value'] = false;
                    }
                    $logicalId = $logicalId_conf;
                } else {
                    if (stripos($logicalId, 'PB_UP') || stripos($logicalId, 'PB_DOWN')) {
                        log::add('Freebox_OS', 'debug', '│ Paramétrage spécifique BP UP/DOWN (' . $logicalId . ') : ' . $logicalId_conf);
                        $parametre['value_type'] = 'void';
                        $logicalId = $logicalId_conf;
                        if (stripos($logicalId, 'PB_UP')) {
                            $parametre['value'] = true;
                        } else {
                            $parametre['value'] = false;
                        }
                    } else {
                        $parametre['value'] = true;
                        $Listener = cmd::byId(str_replace('#', '', $_cmd->getValue()));

                        if (is_object($Listener)) {
                            $parametre['value'] = $Listener->execCmd();
                        }
                        if ($_cmd->getConfiguration('invertslide')) {
                            $parametre['value'] = !$parametre['value'];
                        }
                    }
                }
                break;
        }
        if ($logicalId != 'refresh') {
            if ($_execute == 1) $Free_API->universal_put($parametre, 'set_tiles', $logicalId, $logicalId_eq->getLogicalId(), null);
        }
    }
}
