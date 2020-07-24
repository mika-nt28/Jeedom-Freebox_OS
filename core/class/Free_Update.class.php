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
        log::add('Freebox_OS', 'debug', '┌───────── Update commande ');
        log::add('Freebox_OS', 'debug', '│ Connexion sur la freebox pour mise à jour de : ' . $logicalId_name);

        $Free_API = new Free_API();
        if ($logicalId_eq->getconfiguration('type') == 'parental' || $logicalId_eq->getConfiguration('type') == 'player') {
            $update = $logicalId_eq->getconfiguration('type');
        } else {
            $update = $logicalId_eq->getLogicalId();
        }

        switch ($update) {
            case 'airmedia':
                Free_Update::update_airmedia($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd);
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'connexion':
                break;
            case 'disk':
                break;
            case 'downloads':
                Free_Update::update_download($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'homeadapters':

                break;
            case 'parental':
                $Free_API->universal_put($logicalId, $update, $logicalId_eq->getConfiguration('action'), null, $_options);
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'phone':
                Free_Update::update_phone($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            case 'player':
                break;
            case 'network':
                break;
            case 'system':
                Free_Update::update_system($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                if ($logicalId != 'reboot') {
                    Free_Refresh::RefreshInformation($logicalId_eq->getId());
                }
                break;
            case 'wifi':
                Free_Update::update_wifi($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options);
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
            default:
                Free_Update::update_default($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd, $logicalId_conf);
                Free_Refresh::RefreshInformation($logicalId_eq->getId());
                break;
        }
    }

    private static function update_airmedia($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd)
    {
        $receivers = $logicalId_eq->getCmd(null, "ActualAirmedia");
        if (!is_object($receivers) || $receivers->execCmd() == "" || $_options['titre'] == null) {
            log::add('Freebox_OS', 'debug', '│ [AirPlay] Impossible d\'envoyer la demande les paramètres sont incomplet équipement' . $receivers->execCmd() . ' type:' . $_options['titre']);
            return;
        }
        $Parameter["media_type"] = $_options['titre'];
        $Parameter["media"] = $_options['message'];
        $Parameter["password"] = $_cmd->getConfiguration('password');
        switch ($logicalId) {
            case "airmediastart":
                log::add('Freebox_OS', 'debug', '│ [AirPlay] AirMedia Start : ' . $Parameter["media"]);
                $Parameter["action"] = "start";
                $return = $Free_API->airmedia('action', $Parameter, $receivers->execCmd());
                break;
            case "airmediastop":
                $Parameter["action"] = "stop";
                $return = $Free_API->airmedia('action', $Parameter, $receivers->execCmd());
                break;
        }
    }


    private static function update_download($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options)
    {
        $result = $Free_API->universal_get('download_stats');
        if ($result != false) {
            switch ($logicalId) {
                case "stop_dl":
                    $Free_API->downloads(0);
                    break;
                case "start_dl":
                    $Free_API->downloads(1);
                    break;
            }
        }
    }

    private static function update_phone($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options)
    {
        $result = $Free_API->nb_appel_absence();
        if ($result != false) {
            switch ($logicalId) {
                case "sonnerieDectOn":
                    $Free_API->ringtone('ON');
                    break;
                case "sonnerieDectOff":
                    $Free_API->ringtone('OFF');
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
            case "update":
                $Free_API->Updatesystem();
                break;
            case '4GOn':
                $Free_API->universal_put(1, '4G', null, null, null);
                break;
            case '4GOff':
                $Free_API->universal_put(0, '4G', null, null, null);
                break;
        }
    }

    private static function update_wifi($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options)
    {
        switch ($logicalId) {
            case "wifiOnOff":
                $result = $Free_API->universal_get();
                if ($result == true) {
                    $Free_API->universal_put(0, 'wifi', null, null, null);
                } else {
                    $Free_API->universal_put(1, 'wifi', null, null, null);
                }
                break;
            case 'wifiOn':
                $Free_API->universal_put(1, 'wifi', null, null, null);
                break;
            case 'wifiOff':
                $Free_API->universal_put(0, 'wifi', null, null, null);
                break;
            case 'wifiPlanningOn':
                $Free_API->universal_put(1, 'planning', null, null, null);
                break;
            case 'wifiPlanningOff':
                $Free_API->universal_put(0, 'planning', null, null, null);
                break;
        }
    }

    private static function update_default($logicalId, $logicalId_type, $logicalId_eq, $Free_API, $_options, $_cmd, $logicalId_conf)
    {
        $_execute = 1;
        switch ($logicalId_type) {
            case 'slider':
                if ($_cmd->getConfiguration('inverse')) {
                    $parametre['value'] = ($_cmd->getConfiguration('maxValue') - $_cmd->getConfiguration('minValue')) - $_options['slider'];
                } else {
                    $parametre['value'] = (int) $_options['slider'];
                }
                $parametre['value_type'] = 'int';
                $cmd = cmd::byid($_cmd->getConfiguration('binaryID'));

                if ($cmd !== false) {
                    if ($cmd->getValue() === false) {
                        $_execute = 0;
                    }
                }
                break;
            case 'color':
                $parametre['value'] = $_options['color'];
                $parametre['value_type'] = '';
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
                if ($logicalId_conf >= 0 && ($logicalId == 'PB_On' || $logicalId == 'PB_Off')) {

                    log::add('Freebox_OS', 'debug', '│ Paramétrage spécifique BP ON/OFF : ' . $logicalId_conf);

                    if ($logicalId == 'PB_On') {
                        $parametre['value'] = true;
                    } else {
                        $parametre['value'] = false;
                    }
                    $logicalId = $logicalId_conf;
                } else {
                    $parametre['value'] = true;
                    $Listener = cmd::byId(str_replace('#', '', $_cmd->getValue()));

                    if (is_object($Listener)) {
                        $parametre['value'] = $Listener->execCmd();
                    }
                    if ($_cmd->getConfiguration('inverse')) {
                        $parametre['value'] = !$parametre['value'];
                    }
                }
                break;
        }
        if ($_execute == 1) $Free_API->universal_put($parametre, 'set_tiles', $logicalId, $logicalId_eq->getLogicalId(), null);
    }
}
