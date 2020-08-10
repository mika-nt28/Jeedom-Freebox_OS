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

class Free_CreateTil
{
    public static function createTil($create = 'default')
    {
        $Type_box = Free_CreateTil::createTil_Box();
        if ($Type_box == 'OK') {
            $logicalinfo = Freebox_OS::getlogicalinfo();
            if (version_compare(jeedom::version(), "4", "<")) {
                $templatecore_V4 = null;
            } else {
                $templatecore_V4  = 'core::';
            };
            switch ($create) {
                case 'box':
                    Free_CreateTil::createTil_Box();
                    break;
                case 'homeadapters':
                    Free_CreateTil::createTil_homeadapters($logicalinfo, $templatecore_V4);
                    break;
                case 'homeadapters_SP':
                    Free_CreateTil::createTil_homeadapters_SP($logicalinfo, $templatecore_V4);
                    break;
                default:
                    Free_CreateTil::createTil_Tiles($logicalinfo, $templatecore_V4);
                    break;
            }
        } else {
            log::add('Freebox_OS', 'error', 'Votre Box ne prend pas en charge cette fonctionnalité de Tiles');
        }
    }
    private static function createTil_Box()
    {
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('system', null, null);
        if ($result['board_name'] == 'fbxgw7r') {
            $Type_box = 'OK';
        } else {
            $Type_box = 'NOK';
        }
        return $Type_box;
    }
    private static function createTil_homeadapters($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : Home Adapters');
        Freebox_OS::AddEqLogic($logicalinfo['homeadaptersName'], $logicalinfo['homeadaptersID'], 'default', false, null, null, null, '12 */12 * * *');
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    public static function createTil_homeadapters_SP($logicalinfo, $templatecore_V4)
    {
        $Free_API = new Free_API();

        $homeadapters = Freebox_OS::AddEqLogic($logicalinfo['homeadaptersName'], $logicalinfo['homeadaptersID'], 'default', false, null, null, null, '12 */12 * * *');

        foreach ($Free_API->universal_get('homeadapters') as $Equipement) {
            if ($Equipement['label'] != '') {
                $homeadapters->AddCommand($Equipement['label'], $Equipement['id'], 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', null, 0, false, false);
                if ($Equipement['status'] == 'active') {
                    $homeadapters_value = 1;
                } else {
                    $homeadapters_value = 0;
                }
                $homeadapters->checkAndUpdateCmd($Equipement['id'], $homeadapters_value);
            }
        }
    }
    private static function createTil_Tiles($logicalinfo, $templatecore_V4)
    {
        $Free_API = new Free_API();
        foreach ($Free_API->universal_get('tiles') as $Equipement) {
            $_autorefresh = '*/5 * * * *';
            if ($Equipement['type'] != 'camera') {
                if ($Equipement['type'] == 'alarm_sensor' || $Equipement['type'] == 'alarm_control' || $Equipement['type'] == 'alarm_remote') {
                    $category = 'security';
                    if ($Equipement['type'] == 'alarm_remote') {
                        $_autorefresh = '*/5 * * * *';
                    } else {
                        $_autorefresh = '* * * * *';
                    }
                } elseif ($Equipement['type'] == 'light') {
                    $category = 'light';
                } elseif ($Equipement['action'] == 'store' || $Equipement['action'] == 'store_slider') {
                    $category = 'opening';
                } else {
                    $category = 'default';
                }

                $Equipement['label'] = preg_replace('/\'+/', ' ', $Equipement['label']); // Suppression '
                if (isset($Equipement['label'])) {
                    $Tile = Freebox_OS::AddEqLogic($Equipement['label'], $Equipement['node_id'], $category, true, $Equipement['type'], $Equipement['action'], null, $_autorefresh, $Equipement['group']['label']);
                } else {
                    $Tile = Freebox_OS::AddEqLogic($Equipement['type'], $Equipement['node_id'], $category, true, $Equipement['type'], $Equipement['action'], null, $_autorefresh, $Equipement['group']['label']);
                }
            }
            foreach ($Equipement['data'] as $Command) {
                if ($Command['label'] != '') {
                    $info = null;
                    $action = null;
                    $generic_type = null;
                    $label_sup = null;
                    $infoCmd = null;
                    $IsVisible = 1;
                    $icon = null;
                    if ($Equipement['type'] == 'camera' && method_exists('camera', 'getUrl')) {
                        $parameter['name'] = $Command['label'];
                        $parameter['id'] = $Command['ep_id'];
                        $parameter['url'] = $Command['value'];
                        $parameter['room'] = $Equipement['group']['label'];
                        log::add('Freebox_OS', 'debug', '┌───────── Caméra trouvée pour l\'équipement FREEBOX : ' . $parameter['name'] . ' -- Pièce : ' . $parameter['room']);
                        log::add('Freebox_OS', 'debug', '│ Id : ' . $parameter['id']);
                        log::add('Freebox_OS', 'debug', '│ URL : ' . $parameter['url']);
                        log::add('Freebox_OS', 'debug', '└─────────');
                        event::add('Freebox_OS::camera', json_encode($parameter));
                        continue;
                    }
                    if (!is_object($Tile)) continue;
                    log::add('Freebox_OS', 'debug', '┌───────── Commande trouvée pour l\'équipement FREEBOX : ' . $Equipement['label'] . ' -- Pièce : ' . $Equipement['group']['label'] . ' (Node ID ' . $Equipement['node_id'] . ')');
                    $Command['label'] = preg_replace('/É+/', 'E', $Command['label']); // Suppression É
                    $Command['label'] = preg_replace('/\'+/', ' ', $Command['label']); // Suppression '
                    log::add('Freebox_OS', 'debug', '│ Label : ' . $Command['label'] . ' -- Name : ' . $Command['name']);
                    log::add('Freebox_OS', 'debug', '│ Type (eq) : ' . $Equipement['type'] . ' -- Action (eq): ' . $Equipement['action']);
                    log::add('Freebox_OS', 'debug', '│ Index : ' . $Command['ep_id'] . ' -- Value Type : ' . $Command['value_type'] . ' -- Access : ' . $Command['ui']['access']);
                    log::add('Freebox_OS', 'debug', '│ Valeur actuelle : ' . $Command['value'] . ' ' . $Command['ui']['unit']);
                    log::add('Freebox_OS', 'debug', '│ Range : ' . $Command['ui']['range'][0] . '-' . $Command['ui']['range'][1] . '-' . $Command['ui']['range'][2] . '-' . $Command['ui']['range'][3] . $Command['ui']['range'][4] . '-' . $Command['ui']['range'][5] . '-' . $Command['ui']['range'][6] . ' -- Range color : ' . $Command['ui']['icon_color_range'][0] . '-' . $Command['ui']['icon_color_range'][1]);
                    switch ($Command['value_type']) {
                        case "void":
                            $generic_type = null;
                            $icon = null;
                            $order = null;
                            $Link_I = 'default';
                            $IsVisible = 1;
                            $_iconname = '0';
                            $_home_mode_set = null;
                            if ($Command['name'] == 'up') {
                                $generic_type = 'FLAP_UP';
                                $icon = 'fas fa-arrow-up';
                                $Link_I = $Link_I_store;
                                $order = 2;
                            } elseif ($Command['name'] == 'stop') {
                                $generic_type = 'FLAP_STOP';
                                $icon = 'fas fa-stop';
                                $Link_I = $Link_I_store;
                                $order = 3;
                            } elseif ($Command['name'] == 'down') {
                                $generic_type = 'FLAP_DOWN';
                                $icon = 'fas fa-arrow-down';
                                $Link_I = $Link_I_store;
                                $order = 4;
                            } elseif ($Command['name'] == 'alarm1' && $Equipement['type'] = 'alarm_control') {
                                $generic_type = 'ALARM_SET_MODE';
                                $icon = 'icon jeedom-lock-ferme icon_red';
                                $Link_I = $Link_I_ALARM;
                                $_iconname = 1;
                                $order = 6;
                                $_home_mode_set = 'SetModeAbsent';
                            } elseif ($Command['name'] == 'alarm2' && $Equipement['type'] = 'alarm_control') {
                                $generic_type = 'ALARM_SET_MODE';
                                $icon = 'icon nature-night2 icon_red';
                                $Link_I = $Link_I_ALARM;
                                $_iconname = 1;
                                $order = 7;
                                $_home_mode_set = 'SetModeNuit';
                            } elseif ($Command['name'] == 'off' && $Equipement['type'] = 'alarm_control') {
                                $generic_type = 'ALARM_RELEASED';
                                $icon = 'icon jeedom-lock-ouvert icon_green';
                                $Link_I = $Link_I_ALARM_ENABLE;
                                $_iconname = 1;
                                $order = 8;
                            } elseif ($Command['name'] == 'skip') {
                                $IsVisible = 0;
                                $order = 9;
                            }
                            $action = $Tile->AddCommand($Command['label'], $Command['ep_id'], 'action', 'other', null, $Command['ui']['unit'], $generic_type, $IsVisible, $Link_I, $Link_I, 0, $icon, 0, 'default', 'default', $order, 0, false, false, null, $_iconname, $_home_mode_set);
                            break;
                        case "int":
                            foreach (str_split($Command['ui']['access']) as $access) {
                                $generic_type = null;
                                $Templatecore = null;
                                $Templatecore_A = null;
                                $_min = 'default';
                                $_max = 'default';
                                $IsVisible = 1;
                                $IsVisible_I = '0';
                                $IsHistorized = '0';
                                $name = $Command['label'];
                                $link_logicalId = 'default';
                                $icon = null;
                                $generic_type_I = null;
                                if ($access == "r") {
                                    if ($Command['ui']['access'] == "rw") {
                                        $label_sup = 'Etat ';
                                    }
                                    if ($Equipement['action'] == "store_slider" && $Command['name'] == 'position') {
                                        $generic_type_I = 'FLAP_STATE';
                                        $generic_type = 'FLAP_SLIDER';
                                        $Templatecore = $templatecore_V4 . 'shutter';
                                        $_min = '0';
                                        $_max = 100;
                                    } elseif ($Command['name'] == "luminosity" || ($Equipement['action'] == "color_picker" && $Command['name'] == 'v')) {
                                        $Templatecore_A = 'default'; //$templatecore_V4 . 'light';
                                        $_min = '0';
                                        $_max = 255;
                                        $generic_type = 'LIGHT_SET_COLOR';
                                        $generic_type_I = 'LIGHT_COLOR';
                                        $link_logicalId = $Command['ep_id'];
                                    } elseif ($Equipement['action'] == "color_picker" && $Command['name'] == 'hs') {
                                        $Templatecore_A = 'default';
                                        $_min = '0';
                                        $_max = 255;
                                        $generic_type = 'LIGHT_SLIDER';
                                        $generic_type_I = 'LIGHT_STATE';
                                        $link_logicalId = $Command['ep_id'];
                                    } elseif ($Equipement['type'] == "alarm_remote" && $Command['name'] == 'pushed') {
                                        $Templatecore = 'Freebox_OS::Télécommande Freebox';
                                        $_min = '0';
                                        $_max = $Command['ui']['range'][3];
                                        $IsVisible_I = 1;
                                        $IsHistorized = 1;
                                    } elseif ($Command['name'] == "battery_warning") {
                                        $generic_type_I = 'BATTERY';
                                        $icon = 'fas fa-battery-full';
                                        $name = 'Batterie';
                                    }
                                    if ($Equipement['action'] != "store_slider" && $Command['name'] != 'position') {
                                        $_name_I = $label_sup . $name;
                                    } else {
                                        $_name_I = 'Etat ouverture volet';
                                    }
                                    if ($Command['name'] == "luminosity" || ($Equipement['action'] == "color_picker" && $Command['name'] == 'v')) {
                                        $infoCmd = $Tile->AddCommand($label_sup . $name, $Command['ep_id'], 'info', 'numeric', $Templatecore, $Command['ui']['unit'], $generic_type_I, $IsVisible_I, 'default', $link_logicalId, 0, null, 0, $_min, $_max,  null, $IsHistorized, false, true, $binaireID);

                                        $_cmd = $Tile->getCmd("info", 0);

                                        $Link_I_light = $infoCmd;
                                        $_slider = $Tile->AddCommand($name, $Command['ep_id'], 'action', 'slider', $Templatecore_A, $Command['ui']['unit'], $generic_type, $IsVisible, $Link_I_light, $link_logicalId, 0, null, 0, $_min, $_max,  2, $IsHistorized, false, false);
                                        $_slider->setConfiguration("binaryID", $_cmd->getID());
                                        $_slider->save();
                                    } else {
                                        $infoCmd = $Tile->AddCommand($_name_I, $Command['ep_id'], 'info', 'numeric', $Templatecore, $Command['ui']['unit'], $generic_type_I, $IsVisible_I, 'default', $link_logicalId, 0, $icon, 0, $_min, $_max, null, $IsHistorized, false, true, null);
                                    }

                                    if (($Equipement['action'] == "color_picker" && $Command['name'] == 'hs') || ($Equipement['action'] == "store_slider" && $Command['name'] == 'position')) {
                                        $Tile->AddCommand($name, $Command['ep_id'], 'action', 'slider', $Templatecore_A, $Command['ui']['unit'], $generic_type, $IsVisible, $infoCmd, $link_logicalId, $IsVisible_I, null, 0, $_min, $_max, null, $IsHistorized, false, false, null);
                                    }
                                    $label_sup = null;
                                    $Tile->checkAndUpdateCmd($Command['ep_id'], $Command['value']);
                                    //Gestion des batteries
                                    if ($Command['name'] == "battery_warning") {
                                        if ($Equipement['type'] == 'alarm_control') {
                                            $Tile->batteryStatus($Command['value']);
                                        } elseif ($Command['value'] != '' || $Command['value'] != null) {
                                            log::add('Freebox_OS', 'debug', '│ Valeur Batterie : ' . $Command['value']);
                                            $Tile->batteryStatus($Command['value']);
                                        } else {
                                            log::add('Freebox_OS', 'debug', '│ La valeur de la batterie est nulle ' . $Command['value']);
                                            log::add('Freebox_OS', 'debug', '│ PAS DE TRAITEMENT PAR JEEDOM DE L\'ALARME BATTERIE');
                                        }
                                    }
                                }
                                if ($access == "w") {
                                    if ($Command['name'] != "luminosity" && $Equipement['action'] != "color_picker" && $Equipement['action'] == "store_slider" && $Command['name'] == 'position') {
                                        $action = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'action', 'slider', null, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', 'default', 0, null, 0, 'default', null, 0, false, false, null);
                                    }
                                }
                            }
                            break;
                        case "bool":
                            foreach (str_split($Command['ui']['access']) as $access) {
                                $IsVisible = 1;
                                $Label = $Command['label'];
                                $link_logicalId = 'default';
                                $order = null;
                                $IsVisible_PB = 0;
                                $Type_command = null;
                                if ($Command['label'] == 'Enclenché' || ($Command['name'] == 'switch' && $Equipement['action'] == 'toggle')) {
                                    $Type_command = 'PB';
                                }
                                if ($access == "r") {
                                    if ($Equipement['action'] == "store") {
                                        $generic_type = 'FLAP_STATE';
                                        $Templatecore = $templatecore_V4 . 'shutter';
                                    } elseif ($Equipement['type'] == "alarm_sensor" && $Command['name'] == 'cover') {
                                        $generic_type = 'SABOTAGE';
                                        $Templatecore = null;
                                        $invertBinary = 1;
                                    } elseif ($Equipement['type'] == "alarm_sensor" && $Command['name'] == 'trigger' && $Command['label'] != 'Détection') {
                                        $generic_type = 'OPENING';
                                        $Templatecore = $templatecore_V4 . 'door';
                                    } elseif ($Equipement['type'] == "alarm_sensor" && $Command['name'] == 'trigger' && $Command['label'] == 'Détection') {
                                        $generic_type = 'PRESENCE';
                                        $Templatecore = $templatecore_V4 . 'presence';
                                        $invertBinary = 0;
                                    } elseif ($Command['label'] == 'Enclenché' || ($Command['name'] == 'switch' && $Equipement['action'] == 'toggle')) {
                                        $generic_type = 'LIGHT_STATE';
                                        $Templatecore = $templatecore_V4 . 'light';
                                        $invertBinary = 0;
                                        $IsVisible = 0;
                                        $Label = 'Etat';
                                        $link_logicalId = $Command['ep_id'];
                                        $order = 1;
                                        $IsVisible_PB = 1;
                                    } else {
                                        $generic_type = null;
                                        $Templatecore = null;
                                        $invertBinary = 0;
                                    }

                                    $infoCmd = $Tile->AddCommand($Label, $Command['ep_id'], 'info', 'binary', $Templatecore, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', $link_logicalId, $invertBinary, null, 0, 'default', 'default',  $order, 0, false, true, null);
                                    $Tile->checkAndUpdateCmd($Command['ep_id'], $Command['value']);
                                    if ($Equipement['action'] == 'store') {
                                        $Link_I_store = $infoCmd;
                                    } elseif ($Equipement['type'] == 'light') {
                                        $Link_I_light = $infoCmd;
                                    } else {
                                        $Link_I_store = 'default';
                                    }
                                    if ($Type_command == 'PB') {
                                        $Tile->AddCommand('On', 'PB_On', 'action', 'other', $Templatecore, $Command['ui']['unit'], 'LIGHT_ON', $IsVisible_PB, $Link_I_light, $Command['ep_id'], $invertBinary, null, 1, 'default', 'default', 3, 0, false, false, null);
                                        $Tile->AddCommand('Off', 'PB_Off', 'action', 'other', $Templatecore, $Command['ui']['unit'], 'LIGHT_OFF', $IsVisible_PB, $Link_I_light, $Command['ep_id'], $invertBinary, null, 0, 'default', 'default', 4, 0, false, false, null);
                                    }

                                    $label_sup = null;
                                    $generic_type = null;
                                    $Templatecore = null;
                                    $invertBinary = 0;
                                }
                                if ($access == "w") {
                                    if ($Type_command != 'PB') {
                                        $action = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'action', 'other', null, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', 'default', 0, null, 0, 'default', 'default', 'default', null, 0, false, false, null);
                                    }
                                }
                            }
                            break;
                        case "string":
                            foreach (str_split($Command['ui']['access']) as $access) {
                                $IsVisible = 1;
                                $Templatecore = null;
                                $order = null;
                                $icon = null;
                                $generic_type = null;
                                if ($Command['name'] == "pin") {
                                    $IsVisible = 0;
                                }
                                if ($Command['name'] == "state" && $Equipement['type'] == 'alarm_control') {
                                    $Templatecore = 'Freebox_OS::Alarme Freebox';
                                    $order = 4;
                                    $IsVisible = 0;
                                } elseif ($Command['name'] == "error") {
                                    $order = 10;
                                    $icon = 'icon fas fa-exclamation-triangle icon_red';
                                }
                                if ($access == "r") {
                                    if ($Command['ui']['access'] == "rw") {
                                        $label_sup = 'Etat ';
                                    }
                                    $info = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'info', 'string', $Templatecore, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', 'default', 0, $icon, 0, 'default', 'default', $order, 0, false, true, null);
                                    $Link_I_ALARM = $info;
                                    if ($Command['name'] == "state" && $Equipement['type'] == 'alarm_control') {
                                        log::add('Freebox_OS', 'debug', '│──────────> Ajout commande spécifique pour Homebridge');
                                        $ALARM_ENABLE = $Tile->AddCommand('Actif', 'ALARM_enable', 'info', 'binary', 'core::lock', null, 'ALARM_ENABLE_STATE', 1, 'default', $Command['ep_id'], 0, null, 0, 'default', 'default', 1, 1, false, true, null);
                                        $Link_I_ALARM_ENABLE = $ALARM_ENABLE;
                                        $Tile->AddCommand('Statut', 'ALARM_state', 'info', 'binary', 'core::alert', null, 'ALARM_STATE', 1, 'default', $Command['ep_id'], 1, null, 0, 'default', 'default',  2, 1, false, true, null);
                                        $Tile->AddCommand('Mode', 'ALARM_mode', 'info', 'string', null, null, 'ALARM_MODE', 1, 'default', $Command['ep_id'], 0, null, 0, 'default', 'default', 3, 1, false, true, null);
                                        log::add('Freebox_OS', 'debug', '│──────────> Fin Ajout commande spécifique pour Homebridge');
                                    }
                                    $Tile->checkAndUpdateCmd($Command['ep_id'], $Command['value']);
                                }
                                $label_sup = null;
                                if ($access == "w") {
                                    $action = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'action', 'message', null, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', 'default', 0, $icon, 0, 'default', 'default', $order, 0, false, false, null);
                                }
                            }
                            break;
                    }
                    if (is_object($info) && is_object($action)) {
                        $action->setValue($info->getId());
                        $action->save();
                    }
                    log::add('Freebox_OS', 'debug', '└─────────');
                }
            }
        }
    }
}
