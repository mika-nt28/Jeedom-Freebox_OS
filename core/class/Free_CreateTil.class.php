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

        if (config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS') == '') {
            Free_CreateTil::createTil_modelBox();
        }
        $Type_box = config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS');
        log::add('Freebox_OS', 'debug', '>───────── Type de box compatible Tiles ? : ' . $Type_box);
        $Free_API = new Free_API();
        if ($Type_box == 'OK' || $create == "box") {
            $logicalinfo = Freebox_OS::getlogicalinfo();
            if (version_compare(jeedom::version(), "4", "<")) {
                $templatecore_V4 = null;
            } else {
                $templatecore_V4  = 'core::';
            };
            switch ($create) {
                case 'box':
                    Free_CreateTil::createTil_modelBox();
                    break;
                case 'camera':
                    Free_CreateTil::createTil_Camera();
                    break;
                case 'homeadapters':
                    Free_CreateTil::createTil_homeadapters($Free_API, $logicalinfo, $templatecore_V4);
                    break;
                case 'homeadapters_SP':
                    Free_CreateTil::createTil_homeadapters_SP($Free_API, $logicalinfo, $templatecore_V4);
                    break;
                case 'SetSettingTiles':
                    Free_CreateTil::createTil_SettingTiles($Type_box);
                    break;
                case 'Tiles_debug':
                    Free_CreateTil::createTil_debug($Free_API, $logicalinfo, $templatecore_V4);
                    break;
                case 'Tiles_group':
                    $result = Free_CreateTil::createTil_Group();
                    break;
                default:
                    $result = Free_CreateTil::createTil_Tiles($Free_API, $logicalinfo, $templatecore_V4);
                    break;
            }
            if (isset($result['result'])) {
                return $result;
            } else {
                return;
            }
        } else {
            if ($create == 'box') {
                Free_CreateTil::createTil_modelBox();
                $Type_box = config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS');
            }
            if ($Type_box == 'OK') {
                log::add('Freebox_OS', 'error', 'Votre Box prend en charge cette fonctionnalité de Tiles, merci de relancer le scan');
            } else {
                log::add('Freebox_OS', 'error', 'Votre Box ne prend pas en charge cette fonctionnalité de Tiles');
            }
            return;
        }
    }
    private static function createTil_SettingTiles($Type_box)
    {
        if (config::byKey('FREEBOX_TILES_CRON', 'Freebox_OS') == 1) {
            if ($Type_box == 'OK') {
                $cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxGET');
                if (!is_object($cron)) {
                    $cron = new cron();
                    $cron->setClass('Freebox_OS');
                    $cron->setFunction('FreeboxGET');
                    $cron->setEnable(1);
                    $cron->setDeamon(1);
                    //$cron->setDeamonSleepTime(1);
                    $cron->setSchedule('* * * * *');
                    $cron->setTimeout('1440');
                    $cron->save();
                }
            }
            Freebox_OS::deamon_stop();
            Freebox_OS::deamon_start();
        } else {
            $cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxGET');
            if (is_object($cron)) {
                $cron->stop();
                $cron->remove();
            }
        }
    }
    private static function createTil_modelBox()
    {
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('system', null, null);
        if ($result['board_name'] == 'fbxgw7r') {
            $Type_box = 'OK';
        } else {
            $Type_box = 'NOK';
        }
        config::save('TYPE_FREEBOX', $result['board_name'], 'Freebox_OS');
        config::save('TYPE_FREEBOX_NAME', $result['model_info']['pretty_name'], 'Freebox_OS');
        config::save('TYPE_FREEBOX_TILES', $Type_box, 'Freebox_OS');
        return $Type_box;
    }
    public static function createTil_Camera()
    {
        $EqLogic = eqLogic::byLogicalId(init('id'), 'camera');
        if (!is_object($EqLogic)) {
            $defaultRoom = intval(config::byKey('defaultParentObject', "Freebox_OS", '', true));
            $url = explode('@', explode('://', init('url'))[1]);
            $room = init('room');
            log::add('Freebox_OS', 'debug', '┌───────── Création de la caméra : ' . init('name'));
            $username = explode(':', $url[0])[0];
            $password = explode(':', $url[0])[1];

            $adresse = explode(':', explode('/', $url[1])[0]);
            $ip = $adresse[0];
            $port = $adresse[1];
            $EqLogic = new camera();
            $EqLogic->setName(init('name'));
            $EqLogic->setLogicalId(init('id'));

            if ($defaultRoom) $EqLogic->setObject_id($defaultRoom);

            $EqLogic->setEqType_name('camera');
            $EqLogic->setIsEnable(1);
            $EqLogic->setIsVisible(0);
            $EqLogic->setcategory('security', 1);
            $EqLogic->setconfiguration("protocole", "http");
            $EqLogic->setconfiguration("ip", $ip);
            $EqLogic->setconfiguration("port", $port);
            log::add('Freebox_OS', 'debug', '│ IP : ' . $ip . ' - Port : ' . $port);
            $EqLogic->setconfiguration("username", $username);
            $EqLogic->setconfiguration("password", $password);
            $EqLogic->setconfiguration("videoFramerate", 15);
            $EqLogic->setconfiguration("device", "rocketcam");
            $URLrtsp = init('url');
            $URLrtsp = str_replace($ip, "#ip#", $URLrtsp);
            $URLrtsp = str_replace($username, "#username#", $URLrtsp);
            $URLrtsp = str_replace($password, "#password#", $URLrtsp);
            $EqLogic->setconfiguration('cameraStreamAccessUrl', $URLrtsp);
            $EqLogic->save();
        }
        // Changement URL
        $URLrtsp = init('url');
        //$URLrtsp = str_replace("rtsp", "http", $URLrtsp);
        //$URLrtsp = str_replace("/stream.m3u8", "/live", $URLrtsp);
        $URLrtsp = str_replace($ip, "#ip#", $URLrtsp);
        $URLrtsp = str_replace($password, "#password#", $URLrtsp);
        $URLrtsp = str_replace($username, "#username#", $URLrtsp);
        $EqLogic->setconfiguration('cameraStreamAccessUrl', $URLrtsp);
        $EqLogic->setconfiguration("streamRTSP", 1);
        log::add('Freebox_OS', 'debug', '│ URL du flux : ' . $URLrtsp);
        $EqLogic->save();
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    public static function createTil_Group()
    {
        $Free_API = new Free_API();
        $tiles  = $Free_API->universal_get('universalAPI', null, null, 'home/tileset/all');
        $result_GP = [];
        foreach ($tiles as $tile) {
            $group = $tile['group']['label'];
            if ($group == "" || $group == null) continue;
            if (!in_array($group, $result_GP)) {
                array_push($result_GP, $group);
                log::add('Freebox_OS', 'debug', '>───────── Pièce : ' . $group);
            }
        }
        return $result_GP;
    }

    private static function createTil_homeadapters($Free_API, $logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '>───────── Création équipement : Home Adapters');
        Freebox_OS::AddEqLogic($logicalinfo['homeadaptersName'], $logicalinfo['homeadaptersID'], 'default', false, null, null, null, '12 */12 * * *', null, null, null, 'tiles_SP');
    }
    public static function createTil_homeadapters_SP($Free_API, $logicalinfo, $templatecore_V4)
    {
        $homeadapters = Freebox_OS::AddEqLogic($logicalinfo['homeadaptersName'], $logicalinfo['homeadaptersID'], 'default', false, null, null, null, '12 */12 * * *', null, null, null, 'tiles_SP');
        $result = $Free_API->universal_get('universalAPI', null, null, 'home/adapters');
        foreach ($result as $Equipement) {
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
    private static function createTil_debug($Free_API, $logicalinfo, $templatecore_V4)
    {
        //log::remove('Freebox_OS');
        log::add('Freebox_OS', 'debug', '********************');
        log::add('Freebox_OS', 'debug', '******************** LOG DEBUG : ' . 'TILES / NODES ********************');
        log::add('Freebox_OS', 'debug', '>> ================ >> LOG POUR DEBUG : ' . 'NODES');
        $Free_API->universal_get('universalAPI', null, null, 'home/nodes');
        log::add('Freebox_OS', 'debug', '>> ================ >> LOG POUR DEBUG : ' . 'TILES');
        $Free_API->universal_get('tiles');
        log::add('Freebox_OS', 'debug', '>> ================ >> LOG POUR DEBUG : ' . 'CAMERA');
        $Free_API->universal_get('universalAPI', null, null, 'camera');
        log::add('Freebox_OS', 'debug', '********************  FIN LOG DEBUG : ' . 'TILES / NODES ********************');
        log::add('Freebox_OS', 'debug', '********************');
    }
    private static function createTil_Tiles($Free_API, $logicalinfo, $templatecore_V4)
    {
        $WebcamOKAll = false;
        //$Link_I_store = null;
        $Link_I_ALARM = null;
        $Link_I_ALARM_ENABLE = null;
        $_eq_type = null;
        $_eq_room = null;
        $_eq_data = null;
        $_eq_node = null;
        $eq_group = 'tiles';
        $boucle_num = 1; // 1 = Tiles - 2 = Node 
        while ($boucle_num <= 2) {
            if ($boucle_num == 2) {
                $result = $Free_API->universal_get('universalAPI', null, null, 'home/nodes');
                $eq_group = 'nodes';
            } else if ($boucle_num == 1) {
                $_eq_category = true;
                $result = $Free_API->universal_get('tiles');
            }
            log::add('Freebox_OS', 'debug', '>> ================ >> TYPE DE CREATION : ' . $eq_group);
            foreach ($result as $Equipement) {
                $_eq_category = true;
                if ($eq_group == 'nodes') { //
                    if ($Equipement['category'] == 'pir' ||  $Equipement['category'] == 'kfb' ||  $Equipement['category'] == 'dws' ||  $Equipement['category'] == 'alarm' || $Equipement['category'] == 'basic_shutter' || $Equipement['category'] == 'shutter' || $Equipement['category'] = 'opener'  || $Equipement['category'] == 'plug' ||  $Equipement['category'] == 'camera' || $Equipement['category'] == 'light') {
                        if (isset($Equipement['action'])) {
                            $_eq_action = $Equipement['action'];
                        } else {
                            $_eq_action = null;
                        }
                        log::add('Freebox_OS', 'debug', '>> ================ >> ' . $Equipement['label'] . ' / ' . $_eq_action . ' / ' . $Equipement['id']);
                    } else {
                        $_eq_category = false;
                    }
                }

                if ($_eq_category  === true) {
                    $_eq_type2 = null;
                    if ($boucle_num == 2) {
                        $_eq_type = $Equipement['category'];
                        $_eq_room = $Equipement['group']['label'];
                        $_eq_data = $Equipement['show_endpoints'];
                        $_eq_node = $Equipement['id'];
                        if ($Equipement['category'] == 'light') {
                            $_eq_type2 = $Equipement['category'];
                        }
                    } else if ($boucle_num == 1) {
                        $_eq_type = $Equipement['type'];
                        $_eq_room = $Equipement['group']['label'];
                        $_eq_data = $Equipement['data'];
                        $_eq_node = $Equipement['node_id'];
                    }
                    $_autorefresh = '*/5 * * * *';
                    if (isset($Equipement['action'])) {
                        $_eq_action = $Equipement['action'];
                    } else {
                        $_eq_action = null;
                    }
                    //if ($_eq_type != 'camera') {
                    if ($_eq_type == 'alarm_sensor' || $_eq_type == 'alarm_control' || $_eq_type == 'alarm' || $_eq_type == 'kfb' || $_eq_type == 'pir' || $_eq_type == 'dws' || $_eq_type == 'alarm_remote' || $_eq_type == 'camera') {
                        $category = 'security';
                        if ($_eq_type == 'alarm_remote') {
                            $_autorefresh = '*/5 * * * *';
                        } elseif ($_eq_type == 'camera') {
                            $_autorefresh = '*/15 * * * *';
                        } else {
                            $_autorefresh = '* * * * *';
                        }
                    } elseif ($_eq_type == 'light') {
                        $category = 'light';
                    } elseif ($_eq_action == 'store' ||  $_eq_action == 'store_slider') {
                        $category = 'opening';
                    } else {
                        $category = 'default';
                    }
                    $room = Free_CreateTil::getPiece($_eq_room);
                    $replace_device_type = array(
                        ' ' => ' ',
                        '/' => ' ',
                        '/\'+/' => ' ',
                        '\\' => ' ',
                        'É' => 'E',
                        '\"' => ' ',
                        "\'" => ' ',
                        "'" => ' '
                    );
                    $Equipement['label'] = str_replace(array_keys($replace_device_type), $replace_device_type, $Equipement['label']);
                    if ($_eq_type != 'camera' && $boucle_num != 2) {
                        $Tile = Freebox_OS::AddEqLogic(($Equipement['label'] != '' ? $Equipement['label'] : $_eq_type), $_eq_node, $category, true, $_eq_type,  $_eq_action, null, $_autorefresh, 'default', null, $_eq_type2, $eq_group);
                    } else {
                        $Tile = Freebox_OS::AddEqLogic(($Equipement['label'] != '' ? $Equipement['label'] : $_eq_type), $_eq_node, $category, true, $_eq_type,  $_eq_action, null, $_autorefresh, 'default', null, $_eq_type2, $eq_group);
                    }

                    $_eqLogic = null;
                    $Setting_mouv_sensor = null;
                    foreach ($_eq_data as $Command) {
                        if ($boucle_num == 2) { // 
                            $_cmd_ep_id = $Command['id'];
                            log::add('Freebox_OS', 'debug', '>> ======== >> Label  ' . $Command['label'] . ' - id : ' . $_cmd_ep_id . ' - name : ' . $Command['name'] . ' -- Access : ' . $Command['ui']['access'] . ' -- Type de Valeur : ' . $Command['value_type']);
                        } else if ($boucle_num == 1) {
                            $_cmd_ep_id = $Command['ep_id'];
                        }
                        if ($Command['label'] != '' &&  $_eq_category === true) {
                            $info = null;
                            $action = null;
                            $generic_type = null;
                            $label_sup = null;
                            $infoCmd = null;
                            $IsVisible = 1;
                            $icon = null;

                            if ($_eq_type == 'camera' && $boucle_num != 2 && method_exists('camera', 'getUrl')) {
                                $_eqLogic == $_eq_type;
                                $command['label'] = str_replace(array_keys($replace_device_type), $replace_device_type, $Command['label']);
                                $parameter['name'] = $Command['label'];
                                $parameter['id'] = 'FreeboxCamera_' . $_eq_node;
                                $parameter['room'] = $_eq_room;
                                $parameter['url'] = $Command['value'];
                                log::add('Freebox_OS', 'debug', '>> ================ >> ' . $parameter['name']);
                                log::add('Freebox_OS', 'debug', '┌───────── Caméra trouvée pour l\'équipement FREEBOX : ' . $parameter['name'] . ' -- Pièce : ' . $parameter['room']);
                                log::add('Freebox_OS', 'debug', '│ Id : ' . $parameter['id']);

                                $WebcamOK = false;
                                foreach (eqLogic::byLogicalId($parameter['id'], 'camera', true) as $_eqLogic) {
                                    $WebcamOK = 1;
                                    log::add('Freebox_OS', 'debug', '│ La caméra a déjà été créée ');
                                };
                                if ($WebcamOK == false) {
                                    event::add('Freebox_OS::camera', json_encode($parameter));
                                    $WebcamOKAll = 1;
                                }
                                log::add('Freebox_OS', 'debug', '└─────────');
                                continue;
                            }

                            if (!is_object($Tile)) continue;
                            //log::add('Freebox_OS', 'debug', '┌───────── Commande trouvée pour l\'équipement FREEBOX : ' . $Equipement['label'] . ' -- Pièce : ' . $_eq_room . ' (Node ID ' . $_eq_node . ')');
                            $command['label'] = str_replace(array_keys($replace_device_type), $replace_device_type, $Command['label']);
                            log::add('Freebox_OS', 'debug', '│ Label : ' . $Command['label'] . ' -- Name : ' . $Command['name'] . ' -- Type (eq) : ' . $_eq_type . ' -- Action (eq): ' . $_eq_action . ' -- Index : ' . $_cmd_ep_id . ' -- Value Type : ' . $Command['value_type'] . ' -- Access : ' . $Command['ui']['access']);
                            if (isset($Command['ui']['unit'])) {
                                $_unit = $Command['ui']['unit'];
                            } else {
                                $_unit = null;
                            }
                            //log::add('Freebox_OS', 'debug', '│ Valeur actuelle : ' . $Command['value'] . ' ' . $_unit);
                            $order_range = 0;
                            if (isset($Command['ui']['icon_color_range'])) {
                                foreach ($Command['ui']['icon_color_range'] as $range) {
                                    //log::add('Freebox_OS', 'debug', '│------------> Range Color ' . $order_range . ' : ' . $range);
                                    $order_range++;
                                }
                            }
                            $order_range = 0;
                            if (isset($Command['ui']['range'])) {
                                foreach ($Command['ui']['range'] as $range) {
                                    //log::add('Freebox_OS', 'debug', '│------------> Range ' . $order_range . ' : ' . $range);
                                    $order_range++;
                                }
                            }
                            $order_range = 0;
                            if (isset($Command['ui']['icon_range'])) {
                                foreach ($Command['ui']['icon_range'] as $range) {
                                    //log::add('Freebox_OS', 'debug', '│------------> Range Icon ' . $order_range . ' : ' . $range);
                                    $order_range++;
                                }
                            }
                            switch ($Command['value_type']) {
                                case "void":
                                    //$generic_type = null;
                                    //$icon = null;
                                    //$order = null;
                                    $Link_I = 'default';
                                    //$IsVisible = 1;
                                    //$_iconname = '0';
                                    //$_home_config_eq = null;
                                    $setting = Free_CreateTil::search_setting_void($_eq_action, $Command['ui']['access'], $Command['name'], $_eq_type, $Command['label'], $eq_group, $_cmd_ep_id, $templatecore_V4);
                                    if ($Command['name'] == 'up' || $Command['name'] == 'stop' || $Command['name'] == 'down') {
                                        //$Link_I = $Link_I_store;
                                    } elseif (($Command['name'] == 'alarm1' && $_eq_type = 'alarm_control') || ($Command['name'] == 'alarm2' && $_eq_type = 'alarm_control')) {
                                        $Link_I = $Link_I_ALARM;
                                    } elseif ($Command['name'] == 'off' && $_eq_type = 'alarm_control') {
                                        $Link_I = $Link_I_ALARM_ENABLE;
                                    }
                                    $action = $Tile->AddCommand($setting['Label'], $setting['Cmd_ep_id'], 'action', $setting['SubType'], $setting['Templatecore'], null, $setting['Generic_type'], $setting['IsVisible'], $Link_I, $Link_I, 0, $setting['Icon'], 0, 'default', 'default', $setting['Order'], 0, false, false, null, $setting['Iconname'], $setting['Home_config_eq'], null, null, null, null, null, $eq_group);
                                    if ($setting['Label2'] != null) {
                                        $action2 = null;
                                        $action2 = $Tile->AddCommand($setting['Label2'], $setting['Cmd_ep_id2'], 'action', $setting['SubType'], $setting['Templatecore'], null, $setting['Generic_type2'], $setting['IsVisible'], $Link_I, $Link_I, 0, $setting['Icon2'], 0, 'default', 'default', $setting['Order2'], 0, false, false, null, $setting['Iconname'], $setting['Home_config_eq'], null, null, null, null, null, $eq_group);
                                    }
                                    if ($setting['TypeCMD'] == 'PB_SP') {
                                        $info_link = cmd::byEqLogicIdCmdName($Tile->getId(), __($setting['Label_I'], __FILE__));
                                        if ($info_link  != null) {
                                            Free_CreateTil::Create_linK($info_link, $action);
                                        }
                                    }
                                    break;
                                case "int":
                                    $name = $Command['name'];
                                    $link_logicalId = 'default';
                                    //foreach (str_split($Command['ui']['access'], 2) as $access) {
                                    $setting = Free_CreateTil::search_setting_int($_eq_action, $Command['ui']['access'], $Command['name'], $_eq_type, $Command['label'],  $eq_group, $_cmd_ep_id, $name, $Setting_mouv_sensor, $Command);
                                    $Templatecore = $setting['Templatecore'];
                                    $Templatecore_I = $setting['Templatecore_I'];
                                    if ($setting['CreateCMD'] == 1) {
                                        if (isset($Command['ui']['unit'])) {
                                            if ($Command['ui']['unit'] == null) {
                                                $_unit = '%';
                                            }
                                        } elseif ($setting['Unit'] != null) {
                                            $_unit = $setting['Unit'];
                                        }

                                        if ($Command['ui']['access'] === 'rw' ||  $Command['ui']['access'] === 'r') {
                                            if ($setting['Search'] != 'pir_battery_r_nodes' && $setting['Search'] != 'kfb_battery_r_nodes') {
                                                $order = $setting['Order'];
                                                $Info = $Tile->AddCommand($setting['Label_I'], $_cmd_ep_id, 'info', $setting['SubType_I'], $Templatecore_I, $_unit, $setting['Generic_type_I'], $setting['IsVisible_I'], 'default', $link_logicalId, 0, $setting['Icon_I'], $setting['ForceLineB'], $setting['Min'], $setting['Max'],  $setting['Order'], $setting['IsHistorized'], false, true, null, true, null, null, null, null, null, null, $eq_group);
                                                $order++;
                                            } else {
                                                $Name = 'Batterie';
                                                $_cmd_search = cmd::byEqLogicIdCmdName($Tile->getId(), __($Name, __FILE__));
                                                if (is_object($_cmd_search)) {
                                                    if ($_eq_type == "alarm_sensor" && $Command['label'] == 'Détection') {
                                                        $_eq_type_battery = 'alarm_sensor_mouv_sensor';
                                                    } else {
                                                        $_eq_type_battery =  $setting['Search'];
                                                    }
                                                    $battery = Free_CreateTil::Battery_type($_eq_type_battery);
                                                    $_cmd_search->setLogicalId($_cmd_ep_id);
                                                    $_cmd_search->setConfiguration('TypeNode', $eq_group);
                                                    $_cmd_search->setConfiguration("battery_type", $battery);
                                                    $_cmd_search->save();
                                                } else {
                                                    $Info = $Tile->AddCommand($setting['Label_I'], $_cmd_ep_id, 'info', $setting['SubType_I'], $Templatecore_I, $_unit, $setting['Generic_type_I'], $setting['IsVisible_I'], 'default', $link_logicalId, 0, $setting['Icon_I'], $setting['ForceLineB'], $setting['Min'], $setting['Max'],  $setting['Order'], $setting['IsHistorized'], false, true, null, true, null, null, null, null, null, null, $eq_group);
                                                }
                                            }
                                            if ($Command['ui']['access'] === 'rw') {
                                                $Action =  $Tile->AddCommand($setting['Label'], $_cmd_ep_id, 'action', $setting['SubType'], $Templatecore, $_unit, $setting['Generic_type'], $setting['IsVisible'], 'default', $link_logicalId, 0, $setting['Icon'], $setting['ForceLineB'], $setting['Min'], $setting['Max'], $order, $setting['IsHistorized'], false, false, null, true, null, null, null, null, null, null, $eq_group);
                                                Free_CreateTil::Create_linK($Info, $Action);
                                            }

                                            if (($name == "luminosity" || ($_eq_action == "color_picker" || $_eq_action == "heat_picker") && $name  == 'v')) {
                                                $_cmd = $Tile->getCmd("info", 0);
                                                $Link_I_light = $Info;
                                                $_slider = $Info;
                                                $_slider->setConfiguration("binaryID", $_cmd->getID());
                                                $_slider->save();
                                            } elseif (($_eq_action == "color_picker" || $_eq_action == "heat_picker") && $name  == 'hs') {
                                                $_cmd = $Tile->getCmd("info", 0);
                                                $Link_I_light = $Info;
                                                $_slider_color = $Info;
                                                $_slider_color->setConfiguration("binaryID", $_cmd->getID());
                                                $_slider_color->save();
                                            }
                                            if ($setting['TypeCMD'] == 'PB_SP') {
                                                $Action_link = cmd::byEqLogicIdCmdName($Tile->getId(), __($setting['Label'], __FILE__));
                                                if ($Action_link != null) {
                                                    Free_CreateTil::Create_linK($Info, $Action_link);
                                                }
                                            }
                                        }
                                        if ($Command['ui']['access'] === 'w') {
                                            $Action = $Tile->AddCommand($setting['Label'], $_cmd_ep_id, 'action', $setting['SubType'], $setting['Templatecore_I'], $_unit, $setting['Generic_type'], $setting['IsVisible'], 'default', 'default', 0, $setting['Icon'], $setting['ForceLineB'], $setting['Min'], $setting['Max'], $setting['Order'], false, false, null, null, true, null, null, null, null, null, null, $eq_group);
                                        }

                                        $Tile->checkAndUpdateCmd($_cmd_ep_id, $Command['value']);
                                        //Gestion des batteries
                                        if ($name == "battery_warning" || $setting['Generic_type_I'] == 'BATTERY') {
                                            if ($_eq_type == "alarm_sensor" && $Command['label'] == 'Détection') {
                                                $_eq_type_battery = 'alarm_sensor_mouv_sensor';
                                            } else {
                                                $_eq_type_battery =  $_eq_type;
                                            }

                                            $battery = Free_CreateTil::Battery_type($_eq_type_battery);
                                            if ($_eq_type == 'alarm_control') {
                                                $Tile->batteryStatus($Command['value']);
                                            } elseif ($Command['value'] != '' || $Command['value'] != null) {
                                                log::add('Freebox_OS', 'debug', '│ Valeur Batterie : ' . $Command['value']);
                                                $Tile->batteryStatus($Command['value']);
                                            } else {
                                                log::add('Freebox_OS', 'debug', '│ La valeur de la batterie est nulle ' . $Command['value'] . ' ==> PAS DE TRAITEMENT PAR JEEDOM DE L\'ALARME BATTERIE');
                                            }
                                            $Tile->batteryStatus($Command['value']);
                                            $Tile->setConfiguration("battery_type", $battery);
                                            $Tile->save();
                                        }
                                    }
                                    //}
                                    break;
                                case "bool":
                                    //foreach (str_split($Command['ui']['access']) as $access) {
                                    //$IsVisible = 1;
                                    $link_logicalId = 'default';
                                    $order = null;
                                    $_unit = null;
                                    //$Type_command = null;
                                    $_home_config_eq = null;
                                    if ($Command['label'] == 'Détection') {
                                        $Setting_mouv_sensor = '_' . 'mouv_sensor';
                                    }
                                    //if ($Command['label'] == 'Enclenché' || ($Command['name'] == 'switch' && $_eq_action == 'toggle')) {
                                    //  $Type_command = 'PB';
                                    //}
                                    $setting = Free_CreateTil::search_setting_bool($_eq_action, $Command['ui']['access'], $Command['name'], $_eq_type, $Command['label'], $eq_group, $_cmd_ep_id, $templatecore_V4);
                                    $_home_config_eq = $setting['Home_config_eq'];
                                    if ($_eq_type == 'kfb' || $_eq_type == 'pir' || $_eq_type == 'dws' || $_eq_type == 'alarm_remote') {
                                        $_home_config_eq = $_eq_type;
                                    }
                                    //$IsVisible = $setting['IsVisible'];

                                    if ($setting['CreateCMD'] == 1) {
                                        if ($Command['ui']['access'] === 'rw' ||  $Command['ui']['access'] === 'r') {
                                            $infoCmd = $Tile->AddCommand($setting['Label'], $_cmd_ep_id, 'info', 'binary', $setting['Templatecore'], $_unit, $setting['Generic_type'], $setting['IsVisible'], 'default', $link_logicalId, $setting['InvertBinary'], $setting['Icon'], 0, 'default', 'default',  $setting['Order'], 0, false, true, null, null, $_home_config_eq, null, null, null, null, null, $eq_group, $setting['Eq_type_home']);
                                            $Tile->checkAndUpdateCmd($_cmd_ep_id, $Command['value']);
                                            if ($_eq_action == 'store') {
                                                //$Link_I_store = $infoCmd;
                                            } elseif ($_eq_type == 'light' || ($_eq_type == 'info' && $_eq_action == 'toggle') ||  $setting['TypeCMD'] == 'PB') {
                                                $Link_I_light = $infoCmd;
                                            } else {
                                                $Link_I_store = 'default';
                                            }
                                            if ($setting['TypeCMD'] == 'PB_SP') {
                                                $Action_linkON = cmd::byEqLogicIdCmdName($Tile->getId(), __($setting['LabelON'], __FILE__));
                                                $Action_linkOFF = cmd::byEqLogicIdCmdName($Tile->getId(), __($setting['LabelOFF'], __FILE__));
                                                if ($Action_linkON != null && $Action_linkOFF != null) {
                                                    Free_CreateTil::Create_linK($infoCmd, $Action_linkON);
                                                    Free_CreateTil::Create_linK($infoCmd, $Action_linkOFF);
                                                }
                                            }
                                            if ($Command['ui']['access'] === 'rw') {
                                                $order_A = $setting['Order_A'];
                                                $Tile->AddCommand($setting['LabelON'], $setting['LogicalIdON'], 'action', 'other', $setting['TemplatecoreON'], $_unit, $setting['Generic_typeON'], $setting['IsVisiblePB'], $Link_I_light, $_cmd_ep_id, $setting['InvertBinary'], $setting['IconON'], 1, 'default', 'default', $order_A, 0, false, false, null, null, null, null, null, null, null, null, $eq_group, $setting['Eq_type_home']);
                                                $order_A++;
                                                $Tile->AddCommand($setting['LabelOFF'], $setting['LogicalIdOFF'], 'action', 'other', $setting['Templatecore'], $_unit, $setting['Generic_typeOFF'], $setting['IsVisiblePB'], $Link_I_light, $_cmd_ep_id, $setting['InvertBinary'], $setting['IconOFF'], 0, 'default', 'default', $order_A, 0, false, false, null, null, null, null, null, null, null, null, $eq_group, $setting['Eq_type_home']);
                                            }
                                        } else if ($Command['ui']['access'] === 'w') {
                                            if ($setting['TypeCMD'] != 'PB_SP') {
                                                $Tile->AddCommand($setting['Label'], $_cmd_ep_id, 'action', 'other', $setting['Templatecore'], $_unit, $setting['Generic_type'], $setting['IsVisible'], 'default', $link_logicalId, $setting['InvertBinary'], $setting['Icon'], 0, 'default', 'default',  $setting['Order'], 0, false, true, null, null, $_home_config_eq, null, null, null, null, null, $eq_group, $setting['Eq_type_home']);
                                            } else {
                                                $order_A = $setting['Order_A'];
                                                $Tile->AddCommand($setting['LabelON'], $setting['LogicalIdON'], 'action', 'other', $setting['TemplatecoreON'], $_unit, $setting['Generic_typeON'], $setting['IsVisiblePB'], 'default', $_cmd_ep_id, $setting['InvertBinary'], $setting['IconON'], 1, 'default', 'default', $order_A, 0, false, false, null, null, null, null, null, null, null, null, $eq_group, $setting['Eq_type_home']);
                                                $order_A++;
                                                $Tile->AddCommand($setting['LabelOFF'], $setting['LogicalIdOFF'], 'action', 'other', $setting['TemplatecoreOFF'], $_unit, $setting['Generic_typeOFF'], $setting['IsVisiblePB'], 'default', $_cmd_ep_id, $setting['InvertBinary'], $setting['IconOFF'], 0, 'default', 'default', $order_A, 0, false, false, null, null, null, null, null, null, null, null, $eq_group, $setting['Eq_type_home']);
                                            }
                                        }
                                    }
                                    //}
                                    break;
                                case "string":
                                    //foreach (str_split($Command['ui']['access'], 2) as $access) {
                                    $setting = Free_CreateTil::search_setting_string($_eq_action, $Command['ui']['access'], $Command['name'], $_eq_type, $Command['label'], $eq_group, $_cmd_ep_id);
                                    //$IsVisible = 1;
                                    //$generic_type = null;
                                    if ($setting['CreateCMD'] == 1) {
                                        if ($Command['ui']['access'] === 'rw' ||  $Command['ui']['access'] === 'r') {
                                            $info = $Tile->AddCommand($setting['Label_I'], $_cmd_ep_id, 'info', 'string', $setting['Templatecore'], $_unit, $setting['Generic_type_I'], $setting['IsVisible_I'], 'default', 'default', 0, $setting['Icon_I'], 0, 'default', 'default', $setting['Order'], 0, false, true, null, true, null, null, null, null, null, null, $eq_group);
                                            $Link_I_ALARM = $info;
                                            if ($Command['name'] == "state" && $_eq_type == 'alarm_control') {
                                                log::add('Freebox_OS', 'debug', '│──────────> Ajout commande spécifique pour Homebridge');
                                                $ALARM_ENABLE = $Tile->AddCommand('Actif', 'ALARM_enable', 'info', 'binary', 'core::lock', null, 'ALARM_ENABLE_STATE', 1, 'default', $_cmd_ep_id, 0, null, 0, 'default', 'default', 1, 1, false, true, null, null, null, null, null, null, null, null, $eq_group);
                                                $Link_I_ALARM_ENABLE = $ALARM_ENABLE;
                                                $Tile->AddCommand('Statut', 'ALARM_state', 'info', 'binary', 'core::alert', null, 'ALARM_STATE', 1, 'default', $_cmd_ep_id, 1, null, 0, 'default', 'default',  2, 1, false, true, null, null, null, null, null, null, null, null, $eq_group);
                                                $Tile->AddCommand('Mode', 'ALARM_mode', 'info', 'string', null, null, 'ALARM_MODE', 0, 0, $_cmd_ep_id, 0, null, 0, 'default', 'default', 3, 1, false, true, null, null, null, null, null, null, null, null, $eq_group);
                                                log::add('Freebox_OS', 'debug', '│──────────> Fin Ajout commande spécifique pour Homebridge');
                                            }

                                            if ($Command['ui']['access'] === 'rw') {
                                                if ($Command['name'] != 'disk') {
                                                    $action = $Tile->AddCommand($setting['Label'], $_cmd_ep_id, 'action', 'message', null, $_unit, $setting['Generic_type'], $setting['IsVisible'], 'default', 'default', 0, $setting['Icon'], 0, 'default', 'default', $setting['Order'], 0, false, false, null, null, null, null, null, null, null, null, $eq_group);
                                                }
                                            }
                                            if ($setting['TypeCMD'] == 'PB_SP') {
                                                $Action_linkON = cmd::byEqLogicIdCmdName($Tile->getId(), __($setting['LabelON'], __FILE__));
                                                $Action_linkOFF = cmd::byEqLogicIdCmdName($Tile->getId(), __($setting['LabelOFF'], __FILE__));
                                                if ($Action_linkON != null && $Action_linkOFF != null) {
                                                    Free_CreateTil::Create_linK($info, $Action_linkON);
                                                    Free_CreateTil::Create_linK($info, $Action_linkOFF);
                                                }
                                            }
                                        }
                                        if ($Command['ui']['access'] === 'w') {
                                            $Tile->AddCommand($setting['Label_I'], $_cmd_ep_id, 'action', 'message', null, $_unit, $setting['Generic_type_I'], $setting['IsVisible'], 'default', 'default', 0, $setting['Icon_I'], 0, 'default', 'default', $setting['Order'], 0, false, false, null, null, null, null, null, null, null, null, $eq_group);
                                        }
                                    }
                                    //}
                                    break;
                            }
                            if (is_object($info) && is_object($action)) {
                                $action->setValue($info->getId());
                                $action->save();
                            }
                            //log::add('Freebox_OS', 'debug', '└─────────');
                        }
                    }
                }
            }
            $boucle_num++;
            if ($boucle_num == 3) {
                return $WebcamOKAll;
            }
        }
    }

    private static function Create_linK($info, $action)
    {
        if (is_object($info) && is_object($action)) {
            $action->setValue($info->getId());
            $action->save();
        }
    }
    private static function getPiece($pieceName)
    {
        $config = config::bykey('FREEBOX_PIECE', 'Freebox_OS', "null");
        if ($config == "null") return "null";
        if (isset($config[$pieceName])) {
            $result = intval($config[$pieceName]);
        } else {
            $result = null;
        }

        return $result;
    }

    private static function search_setting_string($_Eq_action, $Access, $Name, $_Eq_type = null, $Label_O, $eq_group = null, $_Cmd_ep_id)
    {
        $Setting1 = $_Eq_type;
        $Setting2 = $Name;
        $Setting3 = null;
        if ($_Eq_action != null) {
            $Setting3 = '_' . $_Eq_action;
        }
        $Setting4 = null;
        if ($eq_group != null) {
            $eq_group = '_' . $eq_group;
        }

        $Search =  $Setting1 . '_' . $Setting2  . $Setting3 . $Setting4 . "_" . $Access  . $eq_group;
        log::add('Freebox_OS', 'debug', '│-----=============================================-------> Setting STRING pour  : ' . $Search);
        $IsVisible = 1;
        $IsVisible_I = 1;
        $Templatecore = null;
        $Order = null;
        $Icon = null;
        $Icon_I = null;
        $Generic_type = null;
        $Generic_type_I = null;
        $Label_I = 'Etat ' . $Label_O;
        $Label = $Label_O;
        $CreateCMD = true;
        $TypeCMD_BOOL = null;
        $LabelON =  null;
        $LabelOFF = null;

        switch ($Search) {
            case 'alarm_control_error_r_tiles':
                $Label_I = $Label_O;
                $Order = 10;
                $Icon_I = 'fas fa-exclamation-triangle icon_red';
                break;
            case 'alarm_control_state_r_tiles':
                $Icon_I = "fas fa-bell icon_red";
                $Label_I = "Etat Alarme";
                $Templatecore = 'Freebox_OS::Alarme Freebox';
                $Order = 4;
                $IsVisible_I = '0';
                break;
            case 'alarm_control_pin_rw_tiles':
                $IsVisible = '0';
                $IsVisible_I = '0';
                $Icon = 'far fa-keyboard icon_green';
                $Icon_I = 'far fa-keyboard';
                break;
            case 'camera_disk_rw_nodes':
                $IsVisible = '0';
                $IsVisible_I = '0';
                $Icon = 'far fa-save icon_green';
                $Icon_I = 'far fa-save';
                break;
            case 'shutter_state_r_nodes':
                $Generic_type = 'FLAP_STATE';
                $Icon = 'icon jeedom-volet-ouvert';
                $Templatecore = 'shutter';
                $Order = 7;
                $TypeCMD_BOOL = 'PB_SP';
                $LabelON = 'Haut - Ouvert';
                $LabelOFF = 'Bas - Fermée';
                break;
            case 'alarm_pin_r_nodes':
            case 'alarm_pin_rw_nodes':
            case 'camera_disk_r_nodes':
            case 'opener_state_r_nodes':
                $CreateCMD = 'PAS DE CREATION';
                break;
        }
        if ($CreateCMD === true) {
            $Value = "";
        } else {
            $Value = ' ==> ' . $CreateCMD;
        }
        $Setting = array(
            "CreateCMD" => $CreateCMD,
            "Eq_type_home" =>  $eq_group,
            "Generic_type" => $Generic_type,
            "Icon" => $Icon,
            "Icon_I" => $Icon_I,
            "Order" => $Order,
            "IsVisible" => $IsVisible,
            "IsVisible_I" => $IsVisible_I,
            "Templatecore" => $Templatecore,
            "Label_I" => $Label_I,
            "Label" => $Label,
            "Search" => $Search,
            "LabelON" => $LabelON,
            "LabelOFF" => $LabelOFF,
            "TypeCMD" => $TypeCMD_BOOL,
            "Generic_type_I" => $Generic_type_I,
            "Generic_type" => $Generic_type
        );
        return $Setting;
    }
    private static function search_setting_void($_Eq_action, $Access, $Name, $_Eq_type = null, $Label_O, $eq_group = null, $_Cmd_ep_id)
    {
        $Setting1 = $_Eq_type;
        $Setting2 = $Name;
        $Setting3 = null;
        if ($_Eq_action != null) {
            $Setting3 = '_' . $_Eq_action;
        }
        $Setting4 = null;
        if ($eq_group != null) {
            $eq_group = '_' . $eq_group;
        }
        $CreateCMD = true;
        $IsVisible = 1;
        $Order = null;
        $Order2 = null;
        $Icon = null;
        $Icon2 = null;
        $Generic_type = null;
        $Generic_type2 = null;
        $Templatecore = 'default';
        $SubType = 'other';
        $ForceLineB = 'default';
        $_Iconname = null;
        $Home_config_eq = null;
        $Label_I = null;
        $Label_O2 = null;
        $_Cmd_ep_id2 = null;
        $TypeCMD_BOOL = 'null';
        $Search =  $Setting1 . '_' . $Setting2  . $Setting3 . $Setting4 . "_" . $Access  . $eq_group;
        log::add('Freebox_OS', 'debug', '│-----=============================================-------> Setting VOID pour  : ' . $Search);

        switch ($Search) {
                /*case 'shutter_toggle_w_nodes':
                // Toggle UP
                $Generic_type = 'FLAP_UP';
                $Icon = 'fas fa-arrow-up';
                $Label_O = 'Haut - Ouvert';
                $_Cmd_ep_id = 'PB_UP';
                $Order = 8;
                // Toggle DOWN
                $Generic_type2 = 'FLAP_DOWN';
                $Icon2 = 'fas fa-arrow-down';
                $Label_O2 = 'Bas - Fermée';
                $_Cmd_ep_id2 = 'PB_DOWN';
                $Order2 = 89;
                // ETAT 
                $Label_I = "État";
                break;*/
            case 'info_up_store_w_tiles':
                $Generic_type = 'FLAP_UP';
                $Icon = 'fas fa-arrow-up';
                $Label_I = "État";
                $TypeCMD_BOOL = 'PB_SP';
                $Order = 2;
                break;
            case 'info_stop_store_w_tiles':
            case 'info_stop_store_slider_w_tiles':
                $Generic_type = 'FLAP_STOP';
                $Icon = 'fas fa-stop';
                $Label_I = "État";
                $TypeCMD_BOOL = 'PB_SP';
                $Order = 3;
                break;
            case 'info_down_store_w_tiles':
                $Generic_type = 'FLAP_DOWN';
                $Icon = 'fas fa-arrow-down';
                $Label_I = "État";
                $TypeCMD_BOOL = 'PB_SP';
                $Order = 4;
                break;
            case 'alarm_control_alarm1_w_tiles':
                $Generic_type = 'ALARM_SET_MODE';
                $Icon = 'icon jeedom-lock-ferme icon_red';
                //$Link_I = $Link_I_ALARM;
                $_Iconname = 1;
                $Order = 6;
                $Home_config_eq = 'SetModeAbsent';
                break;
            case 'alarm_control_alarm2_w_tiles':
                $Generic_type = 'ALARM_SET_MODE';
                $Icon = 'icon nature-night2 icon_red';
                //$Link_I = $Link_I_ALARM;
                $_Iconname = 1;
                $Order = 7;
                $Home_config_eq = 'SetModeNuit';
                break;
            case 'alarm_control_off_w_tiles':
                $Generic_type = 'ALARM_RELEASED';
                $Icon = 'icon jeedom-lock-ouvert icon_green';
                //$Link_I = $Link_I_ALARM_ENABLE;
                $_Iconname = 1;
                $Order = 8;
                break;
            case 'alarm_control_skip_w_tiles':
                $IsVisible = 0;
                $Order = 9;
                break;
            case 'basic_shutter_up_w_nodes':
            case 'basic_shutter_stop_w_nodes':
            case 'basic_shutter_down_w_nodes':
            case 'shutter_stop_w_nodes':
            case 'opener_stop_w_nodes':
            case 'shutter_toggle_w_nodes':
                $CreateCMD = 'PAS DE CREATION';
                break;
            default:
                $CreateCMD = 'NO SETTING';
        }

        $Setting = array(
            "CreateCMD" => $CreateCMD,
            "Eq_type_home" =>  $eq_group,
            "Search" => $Search,
            "Order" => $Order,
            "Order2" => $Order2,
            "Generic_type" => $Generic_type,
            "Generic_type2" => $Generic_type2,
            "Icon" => $Icon,
            "Icon2" => $Icon2,
            "IsVisible" => $IsVisible,
            "Label" => $Label_O,
            "Label2" => $Label_O2,
            "Label_I" => $Label_I,
            "Cmd_ep_id" => $_Cmd_ep_id,
            "Cmd_ep_id2" => $_Cmd_ep_id2,
            "SubType" => $SubType,
            "Templatecore" => $Templatecore,
            "ForceLineB" => $ForceLineB,
            "Iconname" => $_Iconname,
            "TypeCMD" => $TypeCMD_BOOL,
            "Home_config_eq" => $Home_config_eq
        );
        return $Setting;
    }
    private static function search_setting_int($_Eq_action, $Access, $Name, $_Eq_type, $Label_O, $eq_group = null, $_Cmd_ep_id = null,  $Setting_mouv_sensor = null, $Command = null)
    {
        $Setting1 = $_Eq_type;
        $Setting2 = $Name;
        $Setting3 = null;
        if ($_Eq_action != null) {
            $Setting3 = '_' . $_Eq_action;
        }
        $Setting4 = null;
        if ($eq_group != null) {
            $eq_group = '_' . $eq_group;
        }

        $Search =  $Setting1 . '_' . $Setting2  . $Setting3 . $Setting4 . "_" . $Access  . $eq_group;
        log::add('Freebox_OS', 'debug', '│-----=============================================-------> Setting INT pour  : ' . $Search);

        $Generic_type = null;
        $Generic_type_I = null;
        $Templatecore = 'default';
        $Templatecore_I = 'default';
        $Icon = null;
        $Icon_I = null;
        $_Min = 'default';
        $_Max = 'default';
        $IsVisible = 1;
        $IsVisible_I = '0';
        $IsHistorized = '0';
        $ForceLineB = 'default';
        $_Iconname = null;
        $InvertSlide = '0';
        $SubType = 'slider';
        $SubType_I = 'numeric';
        $Order = null;
        $Unit = null;
        $TypeCMD = null;
        $CreateCMD = true;
        $Label_sup = null;
        if ($Access == "rw") {
            $Label_sup = 'Etat ';
        }
        $Label_I = $Label_sup . $Label_O;
        $Label = $Label_O;


        switch ($Search) {
            case 'info_position_store_slider_rw_tiles':
                $Label_I = 'Etat volet';
                $Generic_type_I = 'FLAP_STATE';
                $Generic_type = 'FLAP_SLIDER';
                $Templatecore = 'shutter';
                $_Min = '0';
                $_Max = 100;
                $InvertSlide = true;
                break;
            case 'light_luminosity_intensity_picker_rw_tiles':
            case 'light_v_color_picker_rw_tiles':
            case 'light_v_heat_picker_rw_tiles':
                $Icon_I = 'fas fa-adjust';
                $Icon = 'fas fa-adjust icon_green';
                $Templatecore = 'default'; //$templatecore_V4 . 'light';
                $_Min = '0';
                $_Max = 255;
                $Generic_type = 'LIGHT_SLIDER';
                $Generic_type_I = 'LIGHT_STATE';
                break;
            case 'light_hs_color_picker_rw_tiles':
                $Icon_I = 'fas fa-palette';
                $Icon = 'fas fa-palette icon_green';
                $Label_I = 'ETAT ' . $Label_O;
                $Label = $Label_O;
                $Generic_type = 'LIGHT_SET_COLOR';
                $Generic_type_I = 'LIGHT_COLOR';
                $SubType = 'slider';
                $SubType_I = 'string';
                $Order = 64;
                break;
            case 'alarm_remote_pushed_r_tiles':
                $Templatecore_I = 'Freebox_OS::Télécommande Freebox';
                $_Min = '0';
                $_Max  = 4;
                $IsVisible_I = 1;
                $IsHistorized = 1;
                break;
            case 'alarm_control_battery_r_tiles':
            case 'pir_battery_r_nodes':
            case 'dws_battery_r_nodes':
            case 'kfb_battery_r_nodes':
                $Label_I = 'Batterie';
                $Generic_type_I = 'BATTERY';
                $Icon_I = 'fas fa-battery-full';
                $Name = 'Batterie';
                $_Min = '0';
                $_Max = 100;
                $Unit = "%";
                break;
                // Début caméra
            case 'camera_threshold_rw_nodes':
                $Label_I = 'Etat ' . $Label;
                $_Min = '0';
                $_Max = 4;
                $Order = 150;
                $ForceLineB = 1;
                break;
            case 'camera_sensitivity_rw_nodes':
                $Label_I = 'Etat ' . $Label;
                $_Min = '0';
                $_Max = 4;
                $Order = 152;
                $ForceLineB = 1;
                break;
            case 'camera_rssi_r_nodes':
                $Label_I = 'Niveau de réception';
                $_Min = '-150';
                $_Max = '-50';
                $Order = 154;
                $ForceLineB = 1;
                break;
            case 'camera_volume_r_nodes':
            case 'camera_volume_w_nodes':
                $Label_I = 'Etat Volume du Micro';
                $Label = 'Volume du Micro';
                $Icon_I = 'fas fa-volume-up';
                $Icon = 'fas fa-volume-up icon_green';
                $_Min = '0';
                $_Max = 100;
                $Order = 156;
                $ForceLineB = 1;
                $TypeCMD = 'PB_SP';
                break;
            case 'camera_sound_trigger_r_nodes':
            case 'camera_sound_trigger_w_nodes':
                $Label_I = 'Etat Sensibilité du micro';
                $Label = 'Sensibilité du micro';
                $_Min = '0';
                $_Max = 4;
                $Order = 158;
                $ForceLineB = 1;
                $TypeCMD = 'PB_SP';
                break;
            case 'alarm_timeout1_rw_nodes':
            case 'alarm_timeout2_rw_nodes':
            case 'alarm_timeout3_rw_nodes':
            case 'alarm_volume_rw_nodes':
            case 'alarm_sound_rw_nodes':
                $Icon_I = 'fas fa-stopwatch';
                $Icon = 'fas fa-stopwatch icon_green';
                switch ($Search) {
                    case 'alarm_timeout1_rw_nodes':
                        $Order = 20;
                        break;
                    case 'alarm_timeout2_rw_nodes':
                        $Order = 22;
                        break;
                    case 'alarm_timeout3_rw_nodes':
                        $Order = 24;
                        break;
                    case 'alarm_volume_rw_nodes':
                        $Order = 26;
                        $_Max = 100;
                        $Icon_I = 'fas fa-volume-up';
                        $Icon = 'fas fa-volume-up icon_green';
                        break;
                    case 'alarm_sound_rw_nodes':
                        $Order = 28;
                        $_Max = 100;
                        $Icon_I = 'fas fa-volume-up';
                        $Icon = 'fas fa-volume-up icon_green';
                        break;
                }
                $Templatecore = 'button';
                $Label_I = 'ETAT ' . $Label;
                $_Min = '0';
                $TypeCMD = 'action_info';
                $ForceLineB = true;
                $_Iconname = true;
                break;

            case 'light_v_rw_nodes':
            case 'light_hs_rw_nodes':
            case 'shutter_position_set_w_nodes':
            case 'shutter_position_set_r_nodes':
            case 'opener_position_set_rw_nodes':
            case 'opener_position_set_r_nodes':
            case 'alarm_timeout1_r_nodes':
            case 'alarm_timeout2_r_nodes':
            case 'alarm_timeout3_r_nodes':
            case 'alarm_sound_r_nodes':
            case 'alarm_volume_r_nodes':
            case 'kfb_pushed_r_nodes':
            case 'camera_sensitivity_r_nodes':
            case 'camera_threshold_r_nodes':
            case 'alarm_sensor_battery_r_tiles':
            case 'alarm_remote_battery_warning_r_tiles':
            case 'alarm_battery_r_nodes':
            case 'light_luminosity_rw_nodes':
                $CreateCMD = 'PAS DE CREATION';
                break;
            default:
                $CreateCMD = 'NO SETTING';
        }

        $Setting = array(
            "CreateCMD" => $CreateCMD,
            "Eq_type_home" =>  $eq_group,
            "Unit" => $Unit,
            "Min" => $_Min,
            "Max" => $_Max,
            "Search" => $Search,
            "invertSlide" => $InvertSlide,
            "Order" => $Order,
            // Info
            "Generic_type_I" => $Generic_type_I,
            "IsVisible_I" => $IsVisible_I,
            "Label_I" => $Label_I,
            "SubType_I" => $SubType_I,
            "Icon_I" => $Icon_I,
            "Templatecore_I" => $Templatecore_I,
            "IsHistorized" => $IsHistorized,
            // Action
            "Generic_type" => $Generic_type,
            "Icon" => $Icon,
            "IsVisible" => $IsVisible,
            "Label" => $Label,
            "SubType" => $SubType,
            "Templatecore" => $Templatecore,
            "ForceLineB" => $ForceLineB,
            "Iconname" => $_Iconname,
            "TypeCMD" => $TypeCMD
        );
        return $Setting;
    }
    private static function search_setting_bool($_Eq_action, $Access, $Name, $_Eq_type, $Label_O = null, $eq_group, $_Cmd_ep_id = null, $Templatecore_V4 = null)
    {
        $Setting1 = $_Eq_type;
        $Setting2 = $Name;
        $Setting3 = null;
        if ($_Eq_action != null) {
            $Setting3 = '_' . $_Eq_action;
        }
        $Setting4 = null;
        if ($Label_O == 'Détection') {
            $Setting4 = '_' . 'mouv_sensor';
        }
        if ($eq_group != null) {
            $eq_group = '_' . $eq_group;
        }

        $Search =  $Setting1 . '_' . $Setting2  . $Setting3 . $Setting4 . "_" . $Access  . $eq_group;
        log::add('Freebox_OS', 'debug', '│-----=============================================-------> Setting BOOL pour  : ' . $Search);

        // Reset Template
        $TemplatecoreON = null;
        $TemplatecoreOFF = null;
        $Templatecore = null;
        // Reset Label et logicalId
        $Label_ETAT = $Label_O;
        $LabelON = 'PB_On';
        $LabelOFF = 'PB_Off';
        $LogicalIdON = 'PB_On';
        $LogicalIdOFF = 'PB_Off';
        $Icon = null;
        $IconON = null;
        $IconOFF = null;
        // Reset type de commande
        $TypeCMD_BOOL = null;
        $CreateCMD = true;
        $Generic_type = null;
        $Generic_typeON = null;
        $Generic_typeOFF = null;
        $InvertBinary = 0;
        $Order = 0;
        $Order_A = 0;
        $_Home_config_eq = null;
        $IsVisible = 1;
        $IsVisible_PB = null;
        switch ($Search) {
            case 'camera_detection_w_nodes':
            case 'camera_activation_w_nodes':
            case 'camera_quality_w_nodes':
            case 'camera_flip_w_nodes':
            case 'camera_timestamp_w_nodes':
            case 'camera_sound_detection_w_nodes':
            case 'camera_rtsp_w_nodes':
            case 'camera_detection_r_nodes':
            case 'camera_activation_r_nodes':
            case 'camera_quality_r_nodes':
            case 'camera_flip_r_nodes':
            case 'camera_timestamp_r_nodes':
            case 'camera_sound_detection_r_nodes':
            case 'camera_rtsp_r_nodes':
            case 'kfb_enable_w_nodes':
            case 'kfb_enable_r_nodes':
            case 'pir_alarm1_w_nodes':
            case 'pir_alarm2_w_nodes':
            case 'pir_timed_w_nodes':
            case 'pir_alarm1_r_nodes':
            case 'pir_alarm2_r_nodes':
            case 'pir_timed_r_nodes':
            case 'dws_alarm1_w_nodes':
            case 'dws_alarm2_w_nodes':
            case 'dws_timed_w_nodes':
            case 'dws_alarm1_r_nodes':
            case 'dws_alarm2_r_nodes':
            case 'dws_timed_r_nodes':
                $Label_ETAT =  $Label_O;
                $LabelON = 'Inclure ' . $Label_O . ' ON';
                $LabelOFF = 'Exclure ' . $Label_O . ' OFF';
                $LogicalIdON = 'PB_On' . $_Cmd_ep_id;
                $LogicalIdOFF = 'PB_Off' . $_Cmd_ep_id;
                $Generic_type = 'LIGHT_STATE';
                $Generic_typeON = 'LIGHT_ON';
                $Generic_typeOFF = 'LIGHT_OFF';
                $TypeCMD_BOOL = 'PB_SP';
                $Templatecore = 'default';
                $TemplatecoreON = $Templatecore_V4 . 'binarySwitch';
                $TemplatecoreOFF = $Templatecore_V4 . 'binarySwitch';
                $eq_group = 'nodes';
                $IsVisible = '0';
                $IsVisible_PB = 1;
                switch ($Search) {
                    case 'kfb_enable_w_nodes':
                    case 'kfb_enable_r_nodes':
                        $Icon = 'fas fa-toggle-on';
                        $IconON = 'fas fa-toggle-on icon_green';
                        $IconOFF = 'fas fa-toggle-on icon_red';
                        $Order = 20;
                        $Order_A = 21;
                        break;
                    case 'pir_timed_w_nodes':
                    case 'pir_timed_r_nodes':
                    case 'camera_timestamp_w_nodes':
                    case 'camera_timestamp_r_nodes':
                        $Icon = 'fas fa-stopwatch-20';
                        $IconON = 'fas fa-stopwatch-20 icon_green';
                        $IconOFF = 'fas fa-stopwatch-20 icon_red';
                        $Order = 24;
                        $Order_A = 25;
                        break;
                    case 'dws_timed_w_nodes':
                    case 'dws_timed_r_nodes':
                        $Icon = 'fas fa-stopwatch-20';
                        $IconON = 'fas fa-stopwatch-20 icon_green';
                        $IconOFF = 'fas fa-stopwatch-20 icon_red';
                        $Order = 28;
                        $Order_A = 29;
                        break;
                    case 'pir_alarm1_w_nodes':
                    case 'pir_alarm1_r_nodes':
                        $Icon = 'fas fa-lock';
                        $IconON = 'fas fa-lock icon_green';
                        $IconOFF = 'fas fa-lock icon_red';
                        $Order = 32;
                        $Order_A = 33;
                        break;
                    case 'camera_activation_w_nodes':
                    case 'camera_activation_r_nodes':
                        $Label_ETAT =  'Activation';
                        $LabelON = 'Inclure ' . $Label_ETAT . ' ON';
                        $LabelOFF = 'Exclure ' . $Label_ETAT . ' OFF';
                        $Icon = 'fas fa-lock';
                        $IconON = 'fas fa-lock icon_green';
                        $IconOFF = 'fas fa-lock icon_red';
                        $Order = 32;
                        $Order_A = 33;
                        break;
                    case 'dws_alarm1_w_nodes':
                    case 'dws_alarm1_r_nodes':
                        $Icon = 'fas fa-lock';
                        $IconON = 'fas fa-lock icon_green';
                        $IconOFF = 'fas fa-lock icon_red';
                        $Order = 36;
                        $Order_A = 37;
                        break;
                    case 'pir_alarm2_w_nodes':
                    case 'pir_alarm2_r_nodes':
                        $Icon = 'fas fa-user-lock';
                        $IconON = 'fas fa-user-lock icon_green';
                        $IconOFF = 'fas fa-user-lock icon_red';
                        $Order = 40;
                        $Order_A = 41;
                        break;
                    case 'dws_alarm2_w_nodes':
                    case 'dws_alarm2_r_nodes':
                        $Icon = 'fas fa-user-lock';
                        $IconON = 'fas fa-user-lock icon_green';
                        $IconOFF = 'fas fa-user-lock icon_red';
                        $Order = 44;
                        $Order_A = 45;
                        break;
                    case 'camera_rtsp_w_nodes':
                    case 'camera_rtsp_r_nodes':
                        $Icon = 'fas fa-external-link-square-alt';
                        $IconON = 'fas fa-external-link-square-alt icon_green';
                        $IconOFF = 'fas fa-external-link-square-alt icon_red';
                        $Order = 48;
                        $Order_A = 49;
                        break;
                    case 'camera_detection_w_nodes':
                    case 'camera_detection_r_nodes':
                        $Icon = 'fas fa-running';
                        $IconON = 'fas fa-running icon_green';
                        $IconOFF = 'fas fa-running icon_red';
                        $Order = 52;
                        $Order_A = 53;
                        break;
                    case 'camera_quality_w_nodes':
                    case 'camera_quality_r_nodes':
                        $Icon = 'fas fa-video';
                        $IconON = 'fas fa-video icon_green';
                        $IconOFF = 'fas fa-video icon_red';
                        $Order = 56;
                        $Order_A = 57;
                        break;
                    case 'camera_flip_w_nodes':
                    case 'camera_flip_r_nodes':
                        $Icon = 'fas fa-undo-alt';
                        $IconON = 'fas fa-undo-alt icon_green';
                        $IconOFF = 'fas fa-undo-alt icon_red';
                        $Order = 60;
                        $Order_A = 61;
                        break;
                    case 'camera_sound_detection_w_nodes':
                    case 'camera_sound_detection_r_nodes':
                        $Icon = 'fas fa-microphone-alt';
                        $IconON = 'fas fa-microphone-alt icon_green';
                        $IconOFF = 'fas fa-microphone-alt icon_red';
                        $Order = 64;
                        $Order_A = 65;
                        break;
                }
                break;
            case 'light_switch_state_color_picker_rw_tiles':
            case 'light_switch_state_intensity_picker_rw_tiles':
            case 'light_switch_state_heat_picker_rw_tiles':
            case 'light_hs_heat_picker_rw_tiles':
                $Label_ETAT = 'Etat';
                $LabelON = 'On';
                $LabelOFF = 'Off';
                $Generic_type = 'LIGHT_STATE';
                $Generic_typeON = 'LIGHT_ON';
                $Generic_typeOFF = 'LIGHT_OFF';
                $Icon = 'far fa-lightbulb';
                $IconON = 'far fa-lightbulb icon_yellow';
                $IconOFF = 'far fa-lightbulb icon_red';
                $Templatecore = $Templatecore_V4 . 'light';
                $TemplatecoreON = $Templatecore;
                $TemplatecoreOFF = $TemplatecoreON;
                $Order = 1;
                $IsVisible_PB = 1;
                $IsVisible = '0';
                $Order = 60;
                $Order_A = 61;
                break;
            case 'info_switch_toggle_rw_tiles':
                $Label_ETAT = 'Etat';
                $LabelON = 'On';
                $LabelOFF = 'Off';
                $Generic_type = 'GENERIC_INFO';
                $Generic_typeON = 'ENERGY_ON';
                $Generic_typeOFF = 'ENERGY_OFF';
                $Icon = 'fas fa-plug';
                $IconON = 'fas fa-plug icon_green';
                $IconOFF = 'fas fa-plug icon_red';
                $Templatecore = $Templatecore_V4 . 'prise';
                $TemplatecoreON = $Templatecore;
                $TemplatecoreOFF = $TemplatecoreON;
                $Order = 1;
                $IsVisible_PB = 1;
                $IsVisible = '0';
                $Order = 70;
                $Order_A = 71;
                break;
            case 'info_state_store_r_tiles':
                $Generic_type = 'FLAP_STATE';
                $Icon = 'icon jeedom-volet-ouvert';
                $Templatecore = 'shutter';
                break;
            case 'alarm_sensor_cover_r_tiles':
                $Templatecore = 'alert';
                $Generic_type = 'SABOTAGE';
                $InvertBinary = 1;
                break;
            case 'alarm_sensor_trigger_r_tiles':
                $Generic_type = 'OPENING';
                $Templatecore = $Templatecore_V4 . 'door';
                break;
            case 'alarm_sensor_trigger_mouv_sensor_r_tiles':
                $Generic_type = 'PRESENCE';
                $Templatecore = $Templatecore_V4 . 'presence';
                $_Home_config_eq = 'mouv_sensor';
                $InvertBinary = 1;
                break;
            case 'light_switch_rw_nodes':
            case 'alarm_sensor_trigger_r_tiles':
            case 'alarm_sensor_cover_r_tiles':
            case 'alarm_sensor_alarm1_r_tiles':
            case 'alarm_sensor_alarm2_r_tiles':
            case 'pir_cover_r_nodes':
            case 'dws_cover_r_nodes':
            case 'plug_switch_rw_nodes':
            case 'plug_switch_r_nodes':
            case 'pir_trigger_r_nodes':
            case 'basic_shutter_state_r_nodes':
                $CreateCMD = 'PAS DE CREATION';
                break;
            default:
                $CreateCMD = 'NO SETTING';
                break;
        }

        if (version_compare(jeedom::version(), "4.1", "<")) {
            if ($IconON == 'fas fa-toggle-on icon_green') {
                $IconON = 'default';
            }
            if ($IconOFF == 'fas fa-toggle-on icon_red') {
                $IconOFF = 'default';
            }
        }
        $Setting = array(
            "CreateCMD" => $CreateCMD,
            "Eq_type_home" =>  $eq_group,
            "Generic_type" => $Generic_type,
            "Generic_typeON" => $Generic_typeON,
            "Generic_typeOFF" => $Generic_typeOFF,
            "Home_config_eq" => $_Home_config_eq,
            "Icon" => $Icon,
            "IconON" => $IconON,
            "IconOFF" => $IconOFF,
            "Order" => $Order,
            "Order_A" => $Order_A,
            "InvertBinary" => $InvertBinary,
            "IsVisible" => $IsVisible,
            "IsVisiblePB" => $IsVisible_PB,
            "Label" => $Label_ETAT,
            "LabelON" => $LabelON,
            "LabelOFF" => $LabelOFF,
            "LogicalIdON" => $LogicalIdON,
            "LogicalIdOFF" => $LogicalIdOFF,
            "TypeCMD" => $TypeCMD_BOOL,
            "Templatecore" => $Templatecore,
            "TemplatecoreON" => $TemplatecoreON,
            "TemplatecoreOFF" => $TemplatecoreOFF,
            "Search" => $Search
        );

        return $Setting;
    }
    private static function  Battery_type($Type_eq)
    {
        switch ($Type_eq) {
            case 'alarm':
            case 'alarm_control':
                $Battery = '2 x AA/LR6';
                break;
            case 'alarm_sensor_mouv_sensor':
            case 'pir':
                $Battery = '2 x CR123A';
                break;
            case 'alarm_remote':
            case 'kfb_battery_r_nodes':
            case 'kfb':
            case 'alarm_sensor':
                $Battery = '1 x CR2450';
                break;
            default:
                $Battery = null;
                break;
        }
        return $Battery;
    }
}
