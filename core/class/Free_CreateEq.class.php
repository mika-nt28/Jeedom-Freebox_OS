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

use Sabre\VObject\Component\Available;

require_once __DIR__  . '/../../../../core/php/core.inc.php';

class Free_CreateEq
{
    public static function createEq($create = 'default', $IsVisible = true)
    {
        $date = time();
        $date = date("d/m/Y H:i:s", $date);
        $logicalinfo = Freebox_OS::getlogicalinfo();
        //if (version_compare(jeedom::version(), "4", "<")) {
        //  $templatecore_V4 = null;
        //} else {
        $templatecore_V4  = 'core::';
        //};
        $API_version = config::byKey('FREEBOX_API', 'Freebox_OS');
        if ($API_version == null || $API_version === 'TEST_V8') {
            $result_API = Freebox_OS::FreeboxAPI();
            log::add('Freebox_OS', 'debug', '[WARNING] : ' . (__('Version API Compatible avec la Freebox', __FILE__)) . ' : ' . $result_API);
        }
        $order = 0;
        switch ($create) {
            case 'airmedia':
                Free_CreateEq::createEq_airmedia($logicalinfo, $templatecore_V4, $order);
                break;
            case 'box':
                Free_CreateEq::createEq_Type_Box();
                break;
            case 'connexion':
                Free_CreateEq::createEq_connexion($logicalinfo, $templatecore_V4, $order);
                Free_CreateEq::createEq_connexion_4G($logicalinfo, $templatecore_V4);
                break;
            case 'disk_check':
                Free_CreateEq::createEq_disk_check($logicalinfo);
            case 'disk':
                Free_CreateEq::createEq_disk_SP($logicalinfo, $templatecore_V4, $order);
                break;
            case 'LCD':
                $Setting = Free_CreateEq::createEq_Type_Box();
                Free_CreateEq::createEq_LCD($logicalinfo, $templatecore_V4, $order, $Setting);
                break;
            case 'downloads':
                Free_CreateEq::createEq_download($logicalinfo, $templatecore_V4, $order);
                break;
            case 'parental':
                Free_CreateEq::createEq_parental($logicalinfo, $templatecore_V4, $order);
                config::save('SEARCH_PARENTAL', $date, 'Freebox_OS');
                break;
            case 'management':
                Free_CreateEq::createEq_management($logicalinfo, $templatecore_V4, $order);
                break;
            case 'network':
                Free_CreateEq::createEq_network_interface($logicalinfo, $templatecore_V4, $order);
                Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4,  $order, 'LAN');
                Free_CreateEq::createEq_network_SP($logicalinfo, $templatecore_V4, $order, 'LAN', $IsVisible);
                break;
            case 'netshare':
                Free_CreateEq::createEq_netshare($logicalinfo, $templatecore_V4, $order);
                break;
            case 'networkwifiguest':
                Free_CreateEq::createEq_network_interface($logicalinfo, $templatecore_V4, $order);
                Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4,  $order, 'WIFIGUEST');
                Free_CreateEq::createEq_network_SP($logicalinfo, $templatecore_V4,  $order, 'WIFIGUEST', $IsVisible);
                break;
            case 'phone':
                Free_CreateEq::createEq_phone($logicalinfo, $templatecore_V4, $order);
                break;
            case 'system':
                Free_CreateEq::createEq_system_full($logicalinfo, $templatecore_V4, $order);
                break;
            case 'wifi':
                Free_CreateEq::createEq_wifi($logicalinfo, $templatecore_V4, $order);
                break;
            default:
                Freebox_OS::FreeboxAPI();
                log::add('Freebox_OS', 'debug', '[INFO] - ' . (__('ORDRE DE LA CREATION DES EQUIPEMENTS STANDARDS', __FILE__)) . ' -- ' . $date);
                config::save('SEARCH_EQ', config::byKey('SEARCH_EQ', 'Freebox_OS', $date), 'Freebox_OS');
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . 'Récupération info de la box');
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['systemName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['connexionName'] . ' / 4G' . ' / Fibre' . ' / xdsl');
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['freeplugName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['diskName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['phoneName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['LCDName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['airmediaName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['downloadsName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['networkName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['networkwifiguestName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['netshareName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . $logicalinfo['wifiName']);
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . (__('ENSEMBLE DES PLAYERS SOUS TENSION', __FILE__)));
                log::add('Freebox_OS', 'debug', ':fg-info:=================:/fg: ' . (__('ENSEMBLE DES VM', __FILE__)));
                log::add('Freebox_OS', 'debug', '');
                //log::add('Freebox_OS', 'debug', '====================================================================================');
                $Setting = Free_CreateEq::createEq_Type_Box();
                //log::add('Freebox_OS', 'debug', '====================================================================================');
                Free_CreateEq::createEq_system_full($logicalinfo, $templatecore_V4, $order);
                //log::add('Freebox_OS', 'debug', '====================================================================================');
                Free_CreateEq::createEq_connexion($logicalinfo, $templatecore_V4);
                //log::add('Freebox_OS', 'debug', '====================================================================================');
                Free_CreateEq::createEq_FreePlug($logicalinfo, $templatecore_V4, $order);
                if ($Setting['disk_status'] == 'active') {
                    //$result_disk = Free_CreateEq::createEq_disk_check($logicalinfo);
                    //if ($result_disk == true) {
                    Free_CreateEq::createEq_disk($logicalinfo, $templatecore_V4, $order);
                    //}
                } else {
                    log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ ' . (__('AUCUN DISQUE => PAS DE CREATION DE L\'EQUIPEMENT', __FILE__)) . ':/fg: (' . $Setting['disk_status'] . ' / ' . $Setting['disk_status_description'] . ')');
                }

                Free_CreateEq::createEq_phone($logicalinfo, $templatecore_V4, $order);
                Free_CreateEq::createEq_netshare($logicalinfo, $templatecore_V4, $order);
                $Type_box = config::byKey('TYPE_FREEBOX', 'Freebox_OS');
                Free_CreateEq::createEq_LCD($logicalinfo, $templatecore_V4, $order, $Setting);

                if (config::byKey('TYPE_FREEBOX_MODE', 'Freebox_OS') == 'router') {
                    Free_CreateEq::createEq_airmedia($logicalinfo, $templatecore_V4, $order);
                    log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['downloadsName'] . ' ──');
                    if ($Setting['disk_status'] == 'active') {
                        Free_CreateEq::createEq_download($logicalinfo, $templatecore_V4, $order);
                    } else {
                        log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ ' . (__('AUCUN DISQUE => PAS DE CREATION DE L\'EQUIPEMENT', __FILE__)) . ':/fg: (' . $Setting['disk_status'] . ' / ' . $Setting['disk_status_description'] . ')');
                    }
                    log::add('Freebox_OS', 'debug', '└────────────────────');

                    Free_CreateEq::createEq_management($logicalinfo, $templatecore_V4, $order);
                    Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4, $order, 'LAN');
                    Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4, $order, 'WIFIGUEST');
                    Free_CreateEq::createEq_wifi($logicalinfo, $templatecore_V4, $order);
                    //Free_CreateEq::createEq_notification($logicalinfo, $templatecore_V4);
                } else {
                    log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ ' . (__('BOX EN MODE BRIDGE : LES ÉQUIPEMENTS SUIVANTS NE SONT PAS CRÉER', __FILE__)) . ':/fg:');
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $logicalinfo['airmediaName']);
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $logicalinfo['downloadsName']);
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $logicalinfo['networkName'] . ' / ' . $logicalinfo['networkwifiguestName']);
                }
                log::add('Freebox_OS', 'debug', '┌── :fg-success: ' . (__('Vérification Compatibilité avec l\'option VM', __FILE__)) . ' :/fg:──');
                if ($Setting['has_vm'] == true) {
                    log::add('Freebox_OS', 'debug', '| :fg-info:───▶︎ ' . (__('BOX COMPATIBLE AVEC LES VM', __FILE__)) . ':/fg:');
                    Free_CreateEq::createEq_VM($logicalinfo, $templatecore_V4, $order);
                } else {
                    log::add('Freebox_OS', 'debug', '| :fg-info:───▶︎ ' . (__('BOX NON COMPATIBLE AVEC LES VM', __FILE__)) . ':/fg:');
                }
                log::add('Freebox_OS', 'debug', '└────────────────────');
                config::save('SEARCH_EQ', $date, 'Freebox_OS');
                break;
        }
    }
    private static function createEq_Type_Box()
    {
        log::add('Freebox_OS', 'info', '┌── :fg-success: ' . (__('Vérification de la compatibilité de la box avec certaines options', __FILE__)) . ' :/fg:──');
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('system', null, null);
        if (isset($result['disk_status'])) {
            $disk_status_description = $result['disk_status'];
            $disk_status_description = str_ireplace('not_detected', __('Le disque n\'a pas été détecté', __FILE__), $disk_status_description);
            $disk_status_description = str_ireplace('disabled', __('Le disque est désactivé', __FILE__), $disk_status_description);
            $disk_status_description = str_ireplace('initializing', __('Le disque est en cours d\'initialisation', __FILE__), $disk_status_description);
            $disk_status_description = str_ireplace('error', __('Le disque n\'a pas pu être monté', __FILE__), $disk_status_description);
            $disk_status_description = str_ireplace('active', __('Le disque est prêt', __FILE__), $disk_status_description);
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Etat du disque', __FILE__)) . ' ::/fg: ' . $result['disk_status'] . ' / ' . $disk_status_description);
            $disk_status = $result['disk_status'];
        }
        if (isset($result['model_info']['has_vm'])) {
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Box compatible avec les VM', __FILE__)) . ' ::/fg: ' . $result['model_info']['has_vm']);
            $has_vm = $result['model_info']['has_vm'];
        } else {
            $has_vm = false;
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Box compatible avec les VM', __FILE__)) . '::/fg: ' . (__('Non', __FILE__)));
        }
        // board_Name
        config::save('FREEBOX_VM', $has_vm, 'Freebox_OS');
        // Compatibilité LED Rouges
        if (isset($result['model_info']['has_led_strip'])) {
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Box compatible avec les LED rouges', __FILE__)) . ' ::/fg: ' . $result['model_info']['has_led_strip']);
            $has_led_strip = $result['model_info']['has_led_strip'];
        } else {
            $has_led_strip = '0';
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Box compatible avec les LED rouges', __FILE__)) . '::/fg: ' . (__('Non', __FILE__)));
        }
        config::save('FREEBOX_LED_RD', $has_led_strip, 'Freebox_OS');

        // Compatibilité mode Eco Wfi
        if (isset($result['model_info']['has_eco_wifi'])) {
            $has_eco_wifi = 1;
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Box compatible avec le mode Eco Wifi', __FILE__)) . ' ::/fg: ' . $result['model_info']['has_eco_wifi']);
        } else {
            $has_eco_wifi = '0';
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Box compatible avec le mode Eco Wifi', __FILE__)) . ' ::/fg: ' . (__('Non', __FILE__)));
        }
        config::save('FREEBOX_HAS_ECO_WFI', $has_eco_wifi, 'Freebox_OS');

        if (isset($result['model_info']['has_lcd_orientation'])) {
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Box compatible avec l\'orientation du texte sur l\'afficheur', __FILE__)) . ' ::/fg: ' . $result['model_info']['has_lcd_orientation']);
            $has_lcd_orientation = $result['model_info']['has_lcd_orientation'];
        } else {
            $has_lcd_orientation = false;
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Box compatible avec l\'orientation du texte sur l\'afficheur', __FILE__)) . '::/fg: ' . (__('Non', __FILE__)));
        }
        if (isset($result['model_info']['has_home_automation'])) {
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Module domotique', __FILE__)) . ' ::/fg: ' . $result['model_info']['has_vm']);
            $has_home_automation = $result['model_info']['has_home_automation'];
        } else {
            $has_home_automation = false;
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Module domotique', __FILE__)) . ': :/fg: ' . (__('Non présent', __FILE__)));
        }
        if ($result['board_name'] == 'fbxgw7r') {
            $has_home_box = 'OK';
        } else {
            $has_home_box = 'KO';
            config::save('FREEBOX_TILES_CRON', 0, 'Freebox_OS');
            $cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxGET');
            if (is_object($cron)) {
                $cron->stop();
                $cron->remove();
                log::add('Freebox_OS', 'info', '| [  OK  ] - ' . (__('SUPPRESSION CRON DOMOTIQUE', __FILE__)));
            }
            log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Etat CRON Domotique', __FILE__)) . ' ::/fg: ' . config::byKey('FREEBOX_TILES_CRON', 'Freebox_OS'));
        }
        log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Box compatible avec la domotique', __FILE__)) . ' ::/fg: ' . config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS'));

        $Setting = array(
            'has_vm' => $has_vm,
            'has_home_automation' => $has_home_automation,
            'has_home_box' => $has_home_box,
            'has_eco_wifi' => $has_eco_wifi,
            'has_led_strip' => $has_led_strip,
            'has_lcd_orientation' => $has_lcd_orientation,
            'disk_status_description' => $disk_status_description,
            'disk_status' => $disk_status
        );
        // board_Name
        config::save('TYPE_FREEBOX', $result['board_name'], 'Freebox_OS');
        log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ Board name ::/fg: ' . config::byKey('TYPE_FREEBOX', 'Freebox_OS'));
        // pretty_name
        config::save('TYPE_FREEBOX_NAME', $result['model_info']['pretty_name'], 'Freebox_OS');
        log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Type de box', __FILE__)) . '  ::/fg: ' . config::byKey('TYPE_FREEBOX_NAME', 'Freebox_OS'));
        // Titles
        config::save('TYPE_FREEBOX_TILES', $Setting['has_home_box'], 'Freebox_OS');

        log::add('Freebox_OS', 'info', '└────────────────────');
        return $Setting;
    }
    private static function createEq_airmedia($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['airmediaName'] . ' ──');
        $updateicon = false;
        $start_icon = 'fas fa-play icon_green';
        $stop_icon = 'fas fa-stop icon_red';
        $receivers_icon = 'fas fa-play-circle icon_blue';
        $media_icon = 'fas fa-file-export icon_blue';
        $password_icon = 'fas fa-barcode icon_orange';

        $Free_API = new Free_API();
        $receivers_list = null;
        $result = $Free_API->universal_get('universalAPI', null, null, 'airmedia/receivers', true, true, null);
        if (isset($result)) {
            if ($result != false) {
                foreach ($result as $airmedia) {
                    if ($receivers_list == null) {
                        $receivers_list = $airmedia['name'] . '|' . $airmedia['name'];
                    } else {
                        $receivers_list .= ';' . $airmedia['name'] . '|' . $airmedia['name'];
                    }
                }
                log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('Equipements détectées', __FILE__)) . ' : ' . $receivers_list);
            }
        }

        $EqLogic = Freebox_OS::AddEqLogic($logicalinfo['airmediaName'], $logicalinfo['airmediaID'], 'multimedia', false, null, null, null, '*/5 * * * *', null, null, 'system', true);
        $receivers = $EqLogic->AddCommand(__('Player AirMedia choisi', __FILE__), 'receivers_info', 'info', 'string', 'default', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', $order++, '0', false, true);
        $EqLogic->AddCommand(__('Choix du Player AirMedia', __FILE__), 'receivers', 'action', 'select', null, null, null, 1, $receivers, 'default', $receivers_icon, null, 0, 'default', 'default', $order++, '0', false, true, null, null, null, null, null, null, null, null, null, null, $receivers_list, null, null);

        $media_type = $EqLogic->AddCommand(__('Media choisi', __FILE__), 'media_type_info', 'info', 'string', 'default', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', $order++, '0', false, true);
        $media_type_list = null;
        $EqLogic->AddCommand(__('Choix du Media', __FILE__), 'media_type', 'action', 'select', null, null, null, 1, $media_type, 'default', 0, null, 0, 'default', 'default', $order++, '0', false, true, null, null, null, null, null, null, null, null, null, null, $media_type_list, null, null);

        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => 'URL',

        );
        $media = $EqLogic->AddCommand(__('URL choisi', __FILE__), 'media_info', 'info', 'string', 'default', null, null, 1, 'default', 'default', 0, $media_icon, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand(__('Envoyer URL', __FILE__), 'media', 'action', 'message', 'default', null, null, 1, $media, 'default', 0, $media_icon, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, $config_message);

        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => (__('Mot de passe', __FILE__)),

        );
        $password = $EqLogic->AddCommand(__('Mot de Passe actuel', __FILE__), 'password_info', 'info', 'string', 'default', null, null, 0, 'default', 'default', 0, $password_icon, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand(__('Envoyer Mot de passe', __FILE__), 'password', 'action', 'message', 'default', null, null, 1, $password, 'default', 0, $password_icon, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, $config_message);

        $EqLogic->AddCommand(__('Start', __FILE__), 'start', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $start_icon, 0, 'default', 'default', $order++, '0', $updateicon, false, null, true);
        $EqLogic->AddCommand(__('Stop', __FILE__), 'stop', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $stop_icon, 0, 'default', 'default', $order++, '0', $updateicon, false, null, true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_connexion($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['connexionName'] . ' ──');
        $updateicon = false;
        $iconspeed = 'fas fa-tachometer-alt icon_blue';

        $Free_API = new Free_API();
        $result = $Free_API->universal_get('connexion', null, null, 'ftth', true, true, false);
        if ($result['sfp_present'] == null) {
            $_modul = 'Module Fibre : Non Présent';
            $_bandwidth_value_down = '#value# / 1000000';
            $_bandwidth_down_unit = 'Mb/s';
            $_bandwidth_value_up = '#value#  / 1000000';
            $_bandwidth_up_unit = 'Mb/s';
        } else {
            $_modul = 'Module Fibre : Présent';
            $_bandwidth_value_down = '#value#  / 1000000000';
            $_bandwidth_down_unit = 'Gb/s';
            $_bandwidth_value_up = '#value#  / 1000000';
            $_bandwidth_up_unit = 'Mb/s';
        }
        $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *', null, null, null, 'system', true);
        $Connexion->AddCommand(__('Débit descendant', __FILE__), 'rate_down', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value# / 1024', '2');
        $Connexion->AddCommand(__('Débit montant', __FILE__), 'rate_up', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value# / 1024', '2', null, null, null, null, true);
        $Connexion->AddCommand(__('Débit descendant (max)', __FILE__), 'bandwidth_down', 'info', 'numeric', $templatecore_V4 . 'badge', $_bandwidth_down_unit, null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, $_bandwidth_value_down, '2');
        $Connexion->AddCommand(__('Débit montant (max)', __FILE__), 'bandwidth_up', 'info', 'numeric', $templatecore_V4 . 'badge', $_bandwidth_up_unit, null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, $_bandwidth_value_up, '2', null, null, null, null, true);
        $Connexion->AddCommand(__('Reçu', __FILE__), 'bytes_down', 'info', 'numeric', $templatecore_V4 . 'badge', 'Go', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value#  / 1000000000', '2', null, null, null, null, false);
        $Connexion->AddCommand(__('Émis', __FILE__), 'bytes_up', 'info', 'numeric', $templatecore_V4 . 'badge', 'Go', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value#  / 1000000000', '2', null, null, null, null, true);
        $Connexion->AddCommand(__('Type de connexion', __FILE__), 'media', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand(__('Etat de la connexion', __FILE__), 'state', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand(__('IPv4', __FILE__), 'ipv4', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand(__('IPv6', __FILE__), 'ipv6', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand(__('Réponse Ping', __FILE__), 'ping', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand(__('Proxy Wake on Lan', __FILE__), 'wol', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);

        //log::add('Freebox_OS', 'debug', '[  OK  ] - FIN CREATION : ' . $logicalinfo['connexionName']);
        if ($result['sfp_present'] != null) {
            $order = 19;
            Free_CreateEq::createEq_connexion_FTTH($logicalinfo, $templatecore_V4, $order, $result);
        }
        log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $_modul);

        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_connexion_FTTH($logicalinfo, $templatecore_V4, $order = 19, $result = null)
    {
        $updateicon = false;
        if ($result = ! null) {
            $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *', null, null, 'system', true);
            if (isset($result['link_type'])) {
                $Connexion->AddCommand(__('Type de connexion Fibre', __FILE__), 'link_type', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
            } else {
                log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('Fonction type de connexion Fibre non présent', __FILE__)));
            }
            log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ ' . (__('Ajout des commandes spécifiques pour la fibre', __FILE__)) . ' ::/fg: ' . $logicalinfo['connexionName']);
            $Connexion->AddCommand(__('Module Fibre présent', __FILE__), 'sfp_present', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
            $Connexion->AddCommand(__('Signal Fibre présent', __FILE__), 'sfp_has_signal', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
            $Connexion->AddCommand(__('Etat Alimentation', __FILE__), 'sfp_alim_ok', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
            $Connexion->AddCommand(__('Puissance transmise', __FILE__), 'sfp_pwr_tx', 'info', 'numeric', $templatecore_V4 . 'badge', 'dBm', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, '#value# / 100', '2', null, null, null, null, false);
            $Connexion->AddCommand(__('Puissance reçue', __FILE__), 'sfp_pwr_rx', 'info', 'numeric', $templatecore_V4 . 'badge', 'dBm', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, '#value# / 100', '2', null, null, null, null, true);
            log::add('Freebox_OS', 'debug', '└────────────────────');
        }
    }
    private static function createEq_connexion_4G($logicalinfo, $templatecore_V4, $order = 19)
    {
        log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ ' . (__('Ajout des commandes spécifiques pour la 4G', __FILE__)) . ' ::/fg: ' . $logicalinfo['connexionName']);
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('universalAPI', null, null, '/connection/aggregation', true, true, false);

        if ($result != false && $result != 'Aucun module 4G détecté') {
            $_modul = (__('Module 4G : Présent', __FILE__));
            log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $_modul);
            $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *', null, null, 'system', true);
            log::add('Freebox_OS', 'debug', '[WARNING] - ' . (__('DEBUT CREATION DES COMMANDES POUR LA 4G', __FILE__)) . ' : ' . $logicalinfo['connexionName']);
            $Connexion->AddCommand(__('Débit xDSL Descendant', __FILE__), 'tx_used_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, '#value# / 1000', '2', null, null, null, null, true);
            $Connexion->AddCommand(__('Débit xDSL Montant', __FILE__), 'rx_used_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, '#value# / 1000', '2', null, null, null, null, true);
            $Connexion->AddCommand(__('Débit xDSL Descendant (max)', __FILE__), 'tx_max_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, '#value# / 1000', '2', null, null, null, null, true);
            $Connexion->AddCommand(__('Débit xDSL Montant (max)', __FILE__), 'rx_max_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, '#value# / 1000', '2', null, null, null, null, true);
            $Connexion->AddCommand(__('Etat de la connexion xDSL 4G', __FILE__), 'state', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true);
        } else {
            $_modul = (__('Module 4G : Non Présent', __FILE__));
            log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $_modul);
        }

        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_disk_check($logicalinfo)
    {
        $Free_API = new Free_API();
        log::add('Freebox_OS', 'debug', '| :fg-success:| ───▶︎ ' . (__('Contrôle présence disque', __FILE__)) . ' : ' . ':/fg:');
        $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, true);
        if ($result != false) {
            $result_disk = true;
        } else {
            $result_disk = false;
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
        return $result_disk;
    }

    private static function createEq_disk($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['diskName'] . ' ──');
        Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *', null, null, 'system', true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_disk_SP($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['diskName'] . ' ──');
        $icontemp = 'fas fa-thermometer-half icon_blue';
        $Type_box = config::byKey('TYPE_FREEBOX', 'Freebox_OS');
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, true);
        if ($result == 'auth_required') {
            $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, true);
        }
        if ($result != false) {
            $disk = Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *', null, null, 'system', true);
            foreach ($result['result'] as $disks) {
                if ($disks['temp'] != 0) {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('Température', __FILE__)) . ' : ' . $disks['temp'] . '°C' . '- Disque [' . $disks['serial'] . '] - ' . $disks['id']);
                    $disk->AddCommand('Disque [' . $disks['serial'] . '] Temperature', $disks['id'] . '_temp', 'info', 'numeric', $templatecore_V4 . 'line', '°C', null, 1, 'default', 'default', 0, $icontemp, 0, '0', '100', $order++, 0, false, true, null, true, null, null, null, null, null, null, null, true);
                }
                if ($disks['serial'] != null) {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('Tourne', __FILE__)) . ' : ' . $disks['spinning'] . '- Disque [' . $disks['serial'] . '] - ' . $disks['id']);
                    $disk->AddCommand('Disque [' . $disks['serial'] . '] Tourne', $disks['id'] . '_spinning', 'info', 'binary', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order++, '0', false, false, 'never', null, null, null, null, null, null, null, null, true);
                }
                foreach ($disks['partitions'] as $partition) {
                    $order2 = 200;
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ID :' . $partition['id'] . ' : Disque [' . $disks['type'] . '] - ' . $disks['id'] . ' - Partitions : ' . $partition['label']);
                    $name = $partition['label'] . ' - ' . $disks['type'] . ' - ' . $partition['fstype'];
                    $disk->AddCommand($name, $partition['id'], 'info', 'numeric', 'core::horizontal', '%', null, 1, 'default', 'default', 0, 'fas fa-hdd fa-2x', 0, '0', 100, $order2++, '0', false, 'never', null, true, null, '#value#*100', 2, null, null, null, null, true, null, false, null);
                }
            }
            if ($Type_box != 'fbxgw1r' && $Type_box != 'fbxgw2r') {
                $disk_raid = 'OK';
                log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('BOX COMPATIBLE AVEC LES DISQUES RAID', __FILE__)) . ' : ' . $Type_box . ' -' . $disk_raid);
                Free_CreateEq::createEq_disk_RAID($logicalinfo, $templatecore_V4, $order);
            } else {
                $disk_raid = 'KO';
                log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('BOX NON COMPATIBLE AVEC LES DISQUES RAID', __FILE__)) . ' : ' . $Type_box . ' -' . $disk_raid);
            }
        } else {
            log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('AUCUN DISQUE', __FILE__)) . ' - KO');
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_disk_RAID($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ ' . (__('Ajout des commandes spécifiques pour', __FILE__)) . ' ::/fg: ' . $logicalinfo['diskName'] . ' - RAID');

        $Free_API = new Free_API();
        $disk = Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *');
        $result = $Free_API->universal_get('universalAPI', null, null, 'storage/raid', true, true, false);

        if ($result != false) {
            $order = 100;
            foreach ($result as $raid) {
                log::add('Freebox_OS', 'debug', '| ───▶︎ RAID : ' . $raid['name']);
                $disk->AddCommand('Raid ' . $raid['name'] . ' state', $raid['id'] . '_state', 'info', 'string', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order++, '0', false, false, 'never', null, null, null, null, null, null, null, null, true);
                $disk->AddCommand('Raid ' . $raid['name'] . ' sync_action', $raid['id'] . '_sync_action', 'info', 'string', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order++, '0', false, false, 'never', null, null, null, null, null, null, null, null, true);
                $disk->AddCommand('Raid ' . $raid['name'] . ' degraded',     $raid['id'] . '_degraded', 'info', 'binary', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order++, '0', false, false, 'never', null, null, null, null, null, null, null, null, true);
                $order = 200;
                if (isset($raid['members'])) {
                    foreach ($raid['members'] as $members_raid) {
                        $disk->AddCommand('Etat Role Disque ' . $members_raid['disk']['serial'], $members_raid['id'] . '_role', 'info', 'string', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order++, '0', false, false, 'never', null, null, null, null, null, null, null, null, true);
                    }
                }
            }
        } else {
            log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('AUCUN DISQUE', __FILE__)) . ' - KO');
        }
    }

    private static function createEq_download($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['downloadsName'] . ' ──');
        $iconDownloadsOn = 'fas fa-play icon_green';
        $iconDownloadsOff = 'fas fa-stop icon_red';
        $iconRSSnb = 'fas fa-rss icon_green';
        $iconRSSread = 'fas fa-rss-square icon_orange';
        $iconconn_ready = 'fas fa-ethernet icon_green';
        $icontask = 'fas fa-tasks icon_green';
        $icontask_queued = 'fas fa-tasks icon_orange';
        $icontask_error = 'fas fa-tasks icon_red';
        $icondownload = 'fas fa-file-download icon_blue';
        $iconspeed = 'fas fa-tachometer-alt icon_blue';
        $iconcalendar = 'far fa-calendar-alt icon_green';
        $Templatemode = 'Freebox_OS::Mode Téléchargement';
        $iconDownloadsnormal = 'fas fa-rocket icon_green';
        $updateicon = true;

        $downloads = Freebox_OS::AddEqLogic($logicalinfo['downloadsName'], $logicalinfo['downloadsID'], 'multimedia', false, null, null, null, '5 */12 * * *', null, null, 'system', true);
        $downloads->AddCommand(__('Nb de tâche(s)', __FILE__), 'nb_tasks', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de tâche(s) active', __FILE__), 'nb_tasks_active', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask,  0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de tâche(s) en extraction', __FILE__), 'nb_tasks_extracting', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de tâche(s) en réparation', __FILE__), 'nb_tasks_repairing', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de tâche(s) en vérification', __FILE__), 'nb_tasks_checking', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de tâche(s) en attente', __FILE__), 'nb_tasks_queued', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask_queued, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de tâche(s) en erreur', __FILE__), 'nb_tasks_error', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask_error, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de tâche(s) stoppée(s)', __FILE__), 'nb_tasks_stopped', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask_error, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de tâche(s) terminée(s)', __FILE__), 'nb_tasks_done', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de flux RSS', __FILE__), 'nb_rss', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconRSSnb, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Nb de flux RSS Non Lu', __FILE__), 'nb_rss_items_unread', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconRSSread, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Etat connexion', __FILE__), 'conn_ready', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconconn_ready, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Etat Planning', __FILE__), 'throttling_is_scheduled', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconcalendar, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Téléchargement en cours', __FILE__), 'nb_tasks_downloading', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icondownload, 0, 'default', 'default', $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Vitesse réception', __FILE__), 'rx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default', $order++, '0', $updateicon, true, null, true, null, '#value# / 1000', '2', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand(__('Vitesse émission', __FILE__), 'tx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value# / 1000', '2', null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $action = $downloads->AddCommand('Mode Téléchargement', 'mode', 'info', 'string', $Templatemode, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true);
        $listValue = "normal|" . __('Mode normal', __FILE__) . ";slow|" . __('Mode lent', __FILE__) . ";hibernate|" . __('Mode Stop', __FILE__) . ";schedule|" . __('Mode Planning', __FILE__);
        $downloads->AddCommand(__('Choix Mode Téléchargement', __FILE__), 'mode_download', 'action', 'select', null, null, null, 1, $action, 'mode', 0, $iconDownloadsnormal, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, null, $listValue);
        $downloads->AddCommand(__('Start Téléchargement', __FILE__), 'start_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsOn, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, false);
        $downloads->AddCommand(__('Stop Téléchargement', __FILE__), 'stop_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsOff, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, false);

        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_FreePlug($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['freeplugName'] . ' ──');
        $updateicon = false;
        $Free_API = new Free_API();
        $iconReboot = 'fas fa-sync icon_red';

        $result = $Free_API->universal_get('universalAPI', null, null, 'freeplug', true, true, false);
        if (isset($result['result'])) {
            foreach ($result['result'] as $freeplugs) {
                foreach ($freeplugs['members'] as $freeplug) {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('Création Freeplug', __FILE__)) . ' : ' . $freeplug['id']);
                    $FreePlug = Freebox_OS::AddEqLogic($logicalinfo['freeplugName'] . ' - ' . $freeplug['id'], $freeplug['id'], 'default', true, $logicalinfo['freeplugID'], null, null, '*/5 * * * *', null, null, 'system');
                    $FreePlug->AddCommand(__('Rôle', __FILE__), 'net_role', 'info', 'string',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', 10, '0', $updateicon, false, false, true);
                    $FreePlug->AddCommand(__('Redémarrer', __FILE__), 'reset', 'action', 'other',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconReboot, 0, 'default', 'default',  1, '0', true, false, null, true);
                }
            }
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_LCD($logicalinfo, $templatecore_V4, $order = 0, $Setting = null)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['LCDName'] . ' ──');
        $LCD = Freebox_OS::AddEqLogic($logicalinfo['LCDName'], $logicalinfo['LCDID'], 'default', false, null, null, null, '5 */12 * * *', null, null, 'system', true);
        $iconbrightness = 'fas fa-adjust icon_green';
        $iconorientation = 'fas fa-map-signs icon_green';
        $iconorientationF = 'fas fa-map-signs icon_orange';
        $iconled_strip_animation = 'fas fa-highlighter icon_red';
        $iconled_strip = 'fas fa-traffic-light icon_green';
        $iconwifi = 'fas fa-wifi icon_orange';
        $updateicon = false;
        $StatusLCD = $LCD->AddCommand(__('Etat Lumininosité écran LCD', __FILE__), 'brightness', "info", 'numeric', null, '%', null, 0, '', '', '', $iconbrightness, 0, '0', 100, $order++, 2, $updateicon, true, false, true);
        $LCD->AddCommand(__('Lumininosité écran LCD', __FILE__), 'brightness_action', 'action', 'slider', null, '%', null, 1, $StatusLCD, 'default', 0, $iconbrightness, 0, 1, 100, $order++, '0', $updateicon, false, null, true, null, 'floor(#value#)');

        // Afficher Clef Wifi
        $StatusWifi = $LCD->AddCommand(__('Cacher Clef Wifi', __FILE__), 'hide_wifi_key', 'info', 'binary', null, null, 'SWITCH_STATE', 0, null, null, 0, $iconwifi, 0, null, null, $order++, 1, true, 'never', null, true, null, null, null, null, null, null, null, null);
        $LCD->AddCommand(__('Cacher Clef Wifi On', __FILE__), 'hide_wifi_keyOn', 'action', 'other', 'core::toggleLine', null, 'SWITCH_ON', 1, $StatusWifi, 'hide_wifi_key', 0, $iconwifi, 0, null, null, $order++, '0', true, 'never', null, true, null, null, null, null, null, null, null, null);
        $LCD->AddCommand(__('Cacher Clef Wifi Off', __FILE__), 'hide_wifi_keyOff', 'action', 'other', 'core::toggleLine', null, 'SWITCH_OFF', 1, $StatusWifi, 'hide_wifi_key', 0, $iconwifi, 0, null, null, $order++, '0', true, 'never', null, true, null, null, null, null, null, null, null, null);

        if ($Setting != null) {
            // Gestion orientation de l'affichage sur la box
            if ($Setting['has_lcd_orientation'] == 1) {
                // Affichage Orientation
                log::add('Freebox_OS', 'info', '| :fg-success:───▶︎ ' . (__('Box compatible avec l\'orientation du texte sur l\'afficheur', __FILE__)) . ':/fg:');
                $listValue = "0|" . __('Horizontal', __FILE__) . ";90|" . __('90 degrés', __FILE__) . ";180|" . __('180 degrés', __FILE__) . ";270|" . __('270 degrés', __FILE__);
                $StatusLCD = $LCD->AddCommand(__('Etat Orientation', __FILE__), 'orientation', "info", 'string', null, null, null, 0, '', '', '', $iconorientation, 0, '0', 100, $order++, 2, $updateicon, true, false, true);
                $LCD->AddCommand(__('Orientation', __FILE__), 'orientation', 'action', 'select', null, null, null, 1, $StatusLCD, 'default', 0, $iconorientation, 0, '0', 100, $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, null, $listValue);
                // Forcer l'orientation
                $Orientation = $LCD->AddCommand(__('Forcer Orientation', __FILE__), 'orientation_forced', 'info', 'binary', null, null, 'SWITCH_STATE', 0, null, null, 0, $iconorientationF, 0, null, null, $order++, 1, true, 'never', null, true, null, null, null, null, null, null, null, null);
                $LCD->AddCommand(__('Forcer Orientation On', __FILE__), 'orientation_forcedOn', 'action', 'other', 'core::toggleLine', null, 'SWITCH_ON', 1, $Orientation, 'orientation_forced', 0, $iconorientationF, 0, null, null, $order++, '0', true, 'never', null, true, null, null, null, null, null, null, null, null);
                $LCD->AddCommand(__('Forcer Orientation Off', __FILE__), 'orientation_forcedOff', 'action', 'other', 'core::toggleLine', null, 'SWITCH_OFF', 1, $Orientation, 'orientation_forced', 0, $iconorientationF, 0, null, null, $order++, '0', true, 'never', null, true, null, null, null, null, null, null, null, null);
            } else {
                log::add('Freebox_OS', 'info', '| :fg-success:───▶︎ ' . (__('Box compatible avec l\'orientation du texte sur l\'afficheur', __FILE__)) . '::/fg: ' . (__('Non', __FILE__)));
            }

            // LED Box      
            if ($Setting['has_led_strip'] == 1) {
                //Animation LED
                log::add('Freebox_OS', 'info', '| :fg-success:───▶︎ ' . (__('Box compatible avec les LED rouges', __FILE__)) . ':/fg:');
                $listValue = "organic|" . __('Organique', __FILE__) . ";static|" . __('Statique', __FILE__) . ";breathing|" . __('Respiration', __FILE__) . ";rain|" . __('Pluie', __FILE__) . ";trail|" . __('Chenillard', __FILE__) . ";wave|" . __('Vague', __FILE__);
                $led_strip_animation = $LCD->AddCommand(__('Animation du bandeau lumineux', __FILE__), 'led_strip_animation', "info", 'string', null, null, null, 0, '', '', '', $iconled_strip_animation, 0, '0', 100, $order++, 2, $updateicon, true, false, true);
                $LCD->AddCommand(__('Choix animation du bandeau lumineux', __FILE__), 'led_strip_animation_action', 'action', 'select', null, null, null, 1, $led_strip_animation, 'default', 0, $iconled_strip_animation, 0, '0', 100, $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, null, $listValue);
                // Luminosité du bandeau LED
                $led_strip_brightness = $LCD->AddCommand(__('Etat Luninosité du bandeau LED', __FILE__), 'led_strip_brightness', "info", 'numeric', null, '%', null, 0, '', '', '', $iconbrightness, 0, '0', 100, $order++, 2, $updateicon, true, false, true);
                $LCD->AddCommand(__('Luninosité du bandeau LED', __FILE__), 'led_strip_brightness_action', 'action', 'slider', null, '%', null, 1, $led_strip_brightness, 'default', 0, $iconbrightness, 0, '0', 100, $order++, '0', $updateicon, false, null, true, null, 'floor(#value#)');
                // Activation du bandeau LED
                $led_strip = $LCD->AddCommand(__('Etat du bandeau de LED', __FILE__), 'led_strip_enabled', 'info', 'binary', null, null, 'SWITCH_STATE', 0, null, null, 0, $iconled_strip, 0, null, null, $order++, 1, true, 'never', null, true, null, null, null, null, null, null, null, null);
                $LCD->AddCommand(__('Bandeau LED On', __FILE__), 'led_strip_enabledOn', 'action', 'other', 'core::toggleLine', null, 'SWITCH_ON', 1, $led_strip, 'led_strip_enabled', 0, $iconled_strip, 0, null, null, $order++, '0', true, 'never', null, true, null, null, null, null, null, null, null, null);
                $LCD->AddCommand(__('Bandeau LED Off', __FILE__), 'led_strip_enabledOff', 'action', 'other', 'core::toggleLine', null, 'SWITCH_OFF', 1, $led_strip, 'led_strip_enabled', 0, $iconled_strip, 0, null, null, $order++, '0', true, 'never', null, true, null, null, null, null, null, null, null, null);
            } else {
                log::add('Freebox_OS', 'info', '| :fg-success:───▶︎ ' . (__('Box compatible avec les LED rouges', __FILE__)) . '::/fg: ' . (__('Non', __FILE__)));
            }
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_parental($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['parentalName'] . ' ──');
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('parentalprofile', null, null, true, true, true, false);
        if (isset($result['result'])) {
            $result =  $result['result'];
            foreach ($result  as $Equipement) {
                log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ ' . (__('Début de création des commandes spécifiques pour le contrôle parental', __FILE__)) . ' :/fg:');
                $Templateparent = 'Freebox_OS::Parental';
                $iconparent_allowed = 'fas fa-user-check icon_green';
                $iconparent_denied = 'fas fa-user-lock icon_red';
                $iconparent_temp = 'fas fa-user-clock icon_blue';

                $category = 'default';
                $Equipement['name'] = preg_replace('/\'+/', ' ', $Equipement['name']); // Suppression '
                log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('Nom du controle parental', __FILE__)) . ' : ' . $Equipement['name']);
                $parental = Freebox_OS::AddEqLogic($Equipement['name'], 'parental_' . $Equipement['id'], $category, true, 'parental', null, $Equipement['id'], '*/5 * * * *', null, null, 'parental_controls');
                $StatusParental = $parental->AddCommand(__('Etat', __FILE__), 'current_mode', "info", 'string', $Templateparent, null, null, 1, '', '', '', '', 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, true);
                $parental->AddCommand(__('Autoriser', __FILE__), 'allowed', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconparent_allowed, 0, 'default', 'default', $order++, '0', false, false, null, true);
                $parental->AddCommand(__('Bloquer', __FILE__), 'denied', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconparent_denied, 0, 'default', 'default', $order++, '0', false, false, null, true);
                $listValue = '1800|0h30;3600|1h00;5400|1h30;7200|2h00;10800|3h00;14400|4h00';
                $parental->AddCommand(__('Autoriser-Bloquer Temporairement', __FILE__), 'tempDenied', 'action', 'select', null, null, null, 1, $StatusParental, 'parentalStatus', '', $iconparent_temp, 0, 'default', 'default', $order++, '0', false, false, '', true, null, null, null, null, null, null, null, null, $listValue);
                $StatusParental = $parental->AddCommand(__('Vacances associées au profil', __FILE__), 'cdayranges', "info", 'string', null, null, null, 1, '', '', '', '', 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, true);
                $StatusParental = $parental->AddCommand(__('Appareils associées au profil', __FILE__), 'macs', "info", 'string', null, null, null, 1, '', '', '', '', 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, true);
            }
        } else {
            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎' . (__('AUCUN CONTROLE PARENTAL', __FILE__)) . ' :/fg:──');
        }

        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_phone($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['phoneName'] . ' ──');
        $iconmissed = 'icon techno-phone1 icon_red';
        $iconaccepted = 'icon techno-phone3 icon_blue';
        $iconoutgoing = 'icon techno-phone2 icon_green';
        $iconDell_call = 'fas fa-magic icon_red';
        $iconRead_call = 'fab fa-readme icon_blue';
        $updateicon = false;

        $phone = Freebox_OS::AddEqLogic($logicalinfo['phoneName'], $logicalinfo['phoneID'], 'default', false, null, null, null, '*/30 * * * *', null, null, 'system', true);
        $phone->AddCommand(__('Nb Manqués', __FILE__), 'missed', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 1, 'default', 'default', 0, $iconmissed, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand(__('Liste Manqués', __FILE__), 'listmissed', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmissed, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand(__('Nb Reçus', __FILE__), 'accepted', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 1, 'default', 'default', 0, $iconaccepted, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand(__('Liste Reçus', __FILE__), 'listaccepted', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconaccepted, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand(__('Nb Manqués (nouveau)', __FILE__), 'missed_new', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 0, 'default', 'default', 0, $iconmissed, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand(__('Liste Manqués (nouveau)', __FILE__), 'listmissed_new', 'info', 'string', null, null, null, 0, 'default', 'default', 0, $iconmissed, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand(__('Nb Reçus (nouveau)', __FILE__), 'accepted_new', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 0, 'default', 'default', 0, $iconaccepted, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand(__('Liste Reçus (nouveau)', __FILE__), 'listaccepted_new', 'info', 'string', null, null, null, 0, 'default', 'default', 0, $iconaccepted, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand(__('Nb Emis', __FILE__), 'outgoing', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 1, 'default', 'default', 0, $iconoutgoing, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand(__('Liste Emis', __FILE__), 'listoutgoing', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconoutgoing, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand(__('Vider le journal d appels', __FILE__), 'phone_dell_call', 'action', 'other', 'default', null, null,  1, 'default', 'default', 0, $iconDell_call, 1, 'default', 'default', $order++, '0', $updateicon, false, null, true);
        $phone->AddCommand(__('Tout marquer comme lu', __FILE__), 'phone_read_call', 'action', 'other', 'default', null, null,  1, 'default', 'default', 0, $iconRead_call, 0, 'default', 'default', $order++, '0', $updateicon, false, null, true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_management($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['managementName'] . ' ──');
        $icon_dhcp = 'fas fa-network-wired icon_blue';
        $icon_host_type = 'fas fa-laptop icon_green';
        $icon_method = 'fas fa-list icon_orange';
        $icon_add_del_ip = 'fas fa-network-wired icon_blue';
        $icon_primary_name = 'fas fa-book icon_blue';
        $icon_comment = 'far fa-comment icon_orange';
        $updateWidget = false;
        // Pour test Visibilité
        $_IsVisible = 0;

        $EqLogic = Freebox_OS::AddEqLogic($logicalinfo['managementName'], $logicalinfo['managementID'], 'default', false, null, null, null, '0 0 1 1 *', null, null, 'system', true, null);
        // Type de phériphérique
        $host_type_list = "other|" . __('Autre', __FILE__) . ";ip_camera|" . __('Caméra IP', __FILE__) . ";vg_console|" . __('Console de jeux', __FILE__) . ";freebox_crystal|" . __('Freebox Crystal', __FILE__) . ";freebox_delta|" . __('Freebox Delta', __FILE__) . ";freebox_hd|" . __('Freebox HD', __FILE__) . ";freebox_mini|" . __('Freebox Mini', __FILE__) . ";freebox_one|" . __('Freebox One', __FILE__) . ";freebox_player|" . __('Freebox Player', __FILE__) . ";freebox_pop|" . __('Freebox Pop', __FILE__) . ";freebox_wifi|" . __('Freebox Wi-Fi Pop', __FILE__) . ";printer|" . __('Imprimante', __FILE__) . ";nas|" . __('NAS', __FILE__) . ";workstation|" . __('Ordinateur Fixe', __FILE__) . ";laptop|" . __('Ordinateur Portable', __FILE__) . ";multimedia_device|" . __('Périphérique multimédia', __FILE__) . ";networking_device|" . __('Périphérique réseau', __FILE__) . ";smartphone|" . __('Smartphone', __FILE__) . ";tablet|" . __('Tablette', __FILE__) . ";ip_phone|" . __('Téléphone IP', __FILE__) . ";television|" . __('Télévision', __FILE__) . ";car|" . __('Véhicule connecté', __FILE__);
        $host_type = $EqLogic->AddCommand(__('Type de périphérique choisi', __FILE__), 'host_type_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_host_type, 0, 'default', 'default', $order, '0', false, true, null, true);
        $EqLogic->AddCommand(__('Sélection Type de périphérique', __FILE__), 'host_type', 'action', 'select', null, null, null, $_IsVisible, $host_type, 'default', 0, $icon_host_type, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, $host_type_list, null, null);

        // Méthode de modification
        $method_list = "POST|" . __('Ajouter IP fixe', __FILE__) . ";DELETE|" . __('Supprimer IP Fixe', __FILE__) . ";PUT|" . __('Modifier IP Equipement', __FILE__) . ";DEVICE|" . __('Modifier le type de Périphérique', __FILE__) . ";ADD_blacklist|" . __('Ajouter Liste Noire', __FILE__) . ";ADD_whitelist|" . __('Ajouter Liste Blanche', __FILE__) . ";DEL_blacklist|" . __('Supprimer Liste Noire', __FILE__) . ";DEL_whitelist|" . __('Supprimer Liste Blanche', __FILE__) . ";PUT_blacklist|" . __('Modifier Liste Noire', __FILE__) . ";PUT_whitelist|" . __('Modifier Liste Blanche', __FILE__) . ";POST_WOL|" . __('Wake on LAN', __FILE__);
        $method = $EqLogic->AddCommand(__('Choix modification Appareil', __FILE__), 'method_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_method, 0, 'default', 'default', $order++, '0', false, true, null, true);
        $EqLogic->AddCommand(__('Sélection modification Appareil', __FILE__), 'method', 'action', 'select', null, null, null, $_IsVisible, $method, 'default', 0, $icon_method, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, $method_list, null, null);

        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => (__('Adresse IP', __FILE__)),

        );

        $add_del_ip = $EqLogic->AddCommand(__('IP choisi', __FILE__), 'add_del_ip_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_add_del_ip, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand(__('Choix IP', __FILE__), 'add_del_ip', 'action', 'message', 'default', null, null, $_IsVisible, $add_del_ip, 'default', 0, $icon_add_del_ip, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, null, null, null, null, $config_message);

        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => (__('Nom Appareil', __FILE__)),

        );
        $primary_name = $EqLogic->AddCommand('Nom choisi', 'primary_name_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_primary_name, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand('Nom Appareil', 'primary_name', 'action', 'message', 'default', null, null, $_IsVisible, $primary_name, 'default', 0, $icon_primary_name, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, null, null, null, null, $config_message);

        //Commentaires
        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => (__('Commentaire ou Mot de Passe (pour la fonction Wake on Lan)', __FILE__)),

        );
        $primary_name = $EqLogic->AddCommand(__('Commentaire choisi', __FILE__), 'comment_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_comment, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand(__('Commentaire', __FILE__), 'comment', 'action', 'message', 'default', null, null, $_IsVisible, $primary_name, 'default', 0, $icon_comment, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, null, null, null, null, $config_message);

        // Commande Action
        $EqLogic->AddCommand('Modifier Appareil', 'start', 'action', 'other',  'default', null, null, 0, 'default', 'default', 0, $icon_dhcp, 0, 'default', 'default',  $order++, '0', $updateWidget, false, null, true, null, null, null, null, null);

        log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('La commande "Appareil connecté choisi" sera créée par l\'équipement', __FILE__)) . ' : ' . $logicalinfo['networkName'] . ' et/ou ' . $logicalinfo['networkwifiguestName']);
        log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('La commande "Sélection appareil connecté" sera créée par l\'équipement', __FILE__)) . ' : ' . $logicalinfo['networkName'] . ' et/ou ' . $logicalinfo['networkwifiguestName']);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_network($logicalinfo, $templatecore_V4,  $order = 0, $_network = 'LAN')
    {
        if ($_network == 'LAN') {
            $_networkname = $logicalinfo['networkName'];
            $_networkID = $logicalinfo['networkID'];
        } else if ($_network == 'WIFIGUEST') {
            $_networkname = $logicalinfo['networkwifiguestName'];
            $_networkID = $logicalinfo['networkwifiguestID'];
            $icon_search = 'fas fa-broadcast-tower icon_green';
        }
        $icon_search = 'fas fa-search-plus icon_green';

        $updateWidget = false;
        // Pour test Visibilité
        $_IsVisible = 0;
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $_networkname . ': ──');
        $EqLogic = Freebox_OS::AddEqLogic($_networkname, $_networkID, 'default', false, null, null, null, '*/5 * * * *');
        $EqLogic->AddCommand(__('Rechercher les nouveaux appareils', __FILE__), 'search', 'action', 'other',  $templatecore_V4 . 'line', null, null, true, 'default', 'default', 0, $icon_search, true, 'default', 'default',  $order++, '0', $updateWidget, false, null, true, null, null, null, null, null, null, null, true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_netshare($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['netshareName'] . ' ──');
        $order = 1;
        $color_on = ' icon_green';
        $color_off = ' icon_red';
        $updateicon = false;

        $netshare = Freebox_OS::AddEqLogic($logicalinfo['netshareName'], $logicalinfo['netshareID'], 'multimedia', false, null, null, null, '5 */12 * * *', null, null, 'system', true);
        $boucle_num = 1; // 1 = Partage Imprimante - 2 = Partage de fichiers Windows - 3 = Partage Fichier Mac - 4 = Partage Fichier FTP
        $order = 1;
        while ($boucle_num <= 5) {
            if ($boucle_num == 1) {
                $name = __('Partage Imprimante', __FILE__);
                $Logical_ID = 'print_share_enabled';
                $icon = 'fas fa-print';
                $template = 'Freebox_OS::Partage Imprimante';
            } else if ($boucle_num == 2) {
                $name = __('Partage de fichiers Windows', __FILE__);
                $Logical_ID = 'file_share_enabled';
                $icon = 'fas fa-share-alt-square';
                $template = 'Freebox_OS::Partage Fichier Windows';
            } else if ($boucle_num == 3) {
                $name = __('Partage de fichiers Mac', __FILE__);
                $Logical_ID = 'mac_share_enabled';
                $icon = 'fas fa-share-alt';
                $template = 'Freebox_OS::Partage Fichier Mac';
            } else if ($boucle_num == 4) {
                $name = __('Partage FTP', __FILE__);
                $Logical_ID = 'FTP_enabled';
                $icon = 'fas fa-handshake';
                $template = 'Freebox_OS::Partage FTP';
            } else if ($boucle_num == 5) {
                $name = __('SMBv2', __FILE__);
                $Logical_ID = 'smbv2_enabled';
                $icon = 'fab fa-creative-commons-share';
                $template = 'Freebox_OS::Activer SMBv2';
            }
            log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('Boucle pour Création des commandes', __FILE__)) . ' : ' . $name);
            $netshareSTATUS = $netshare->AddCommand($name, $Logical_ID, "info", 'binary', null, null, 'SWITCH_STATE', 0, '', '', '', $icon, 0, 'default', 'default', '0', $order, $updateicon, true);
            $netshare->AddCommand(__('Activer', __FILE__) . ' ' . $name, $Logical_ID . 'On', 'action', 'other', $template, null, 'SWITCH_ON', 1, $netshareSTATUS, '', 0, $icon . $color_on, 0, 'default', 'default', $order++, '0', $updateicon, false);
            $netshare->AddCommand(__('Désactiver', __FILE__) . ' ' . $name, $Logical_ID  . 'Off', 'action', 'other', $template, null, 'SWITCH_OFF', 1, $netshareSTATUS, '', 0, $icon . $color_off, 0, 'default', 'default', $order++, '0', $updateicon, false);
            $boucle_num++;
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_network_interface($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success: ' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['networkName'] . ' ──');
        $Free_API = new Free_API();
        $Free_API->universal_get('universalAPI', null, null, 'lan/browser/interfaces', true, true, true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_network_SP($logicalinfo, $templatecore_V4, $order = 0, $_network = 'LAN', $IsVisible = true)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $_network . ' ──');
        $icon_search = null;
        $icon_network = 'fas fa-network-wired icon_green';
        $updateWidget = false;
        if ($_network == 'LAN') {
            $_networkname = $logicalinfo['networkName'];
            $_networkID = $logicalinfo['networkID'];
            $_networkinterface = 'pub';
        } else if ($_network == 'WIFIGUEST') {
            $_networkname = $logicalinfo['networkwifiguestName'];
            $_networkID = $logicalinfo['networkwifiguestID'];
            $_networkinterface = 'wifiguest';
            $icon_search = 'fas fa-broadcast-tower icon_green';
        }
        if ($IsVisible == true) {
            $_IsVisible = 1;
        } else {
            $_IsVisible = '0';
        }
        log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ ' . (__('Ajout des commandes spécifiques pour l\'équipement', __FILE__)) . ' ::/fg: ' . $_networkname);
        $Free_API = new Free_API();
        $EqLogic = Freebox_OS::EqLogic_ID($_networkname, $_networkID);
        $EqLogic->AddCommand(__('Rechercher les nouveaux appareils', __FILE__), 'search', 'action', 'other',  $templatecore_V4 . 'line', null, null, true, 'default', 'default', 0, $icon_search, true, 'default', 'default',  -30, '0', $updateWidget, false, null, true, null, null, null, null, null, null, null, true);
        $result = $Free_API->universal_get('universalAPI', null, null, 'lan/browser/' . $_networkinterface, true, true, true);
        $order_count_active = 100;
        $order_count_noactive = 400;
        $network_list = null;
        $active_list = null;
        $noactive_list = null;

        if (isset($result['result'])) {
            if ($EqLogic->getConfiguration('UpdateName') == 1) {
                $updatename_disable = 1;
                log::add('Freebox_OS', 'debug', '| └───▶︎ :fg-info:' . (__('Mise à jour des noms', __FILE__)) . ' ::/fg: ' . (__('non actif', __FILE__)));
            } else {
                $updatename_disable = 0;
                log::add('Freebox_OS', 'debug', '| └───▶︎ :fg-info:' . (__('Mise à jour des noms', __FILE__)) . ' ::/fg: ' . (__('actif', __FILE__)));
            }
            $result_network = $result['result'];

            foreach ($result_network as $result) {
                if ($result['primary_name'] != '') {
                    $replace_device_type = array(
                        ' ' => ' ',
                        '/' => ' ',
                        '/\'+/' => ' ',
                        '\\' => ' ',
                        'É' => 'E',
                        '\"' => ' ',
                        "\'" => ' ',
                        "[" => '',
                        "]" => '',
                        "'" => ' '
                    );
                    $result['primary_name'] = str_replace(array_keys($replace_device_type), $replace_device_type, $result['primary_name']);
                    if ($updatename_disable == 0) {
                        $updatename = true; // mise à jour automatique des noms des commandes
                    } else {
                        $updatename = false; // mise à jour automatique des noms des commandes
                    }

                    if (isset($result['access_point'])) {
                        $name_connectivity_type = $result['access_point']['connectivity_type'];
                    } else {
                        $name_connectivity_type = 'Wifi Ethernet ?';
                    }
                    $Ipv4 = null;
                    $Ipv6 = null;
                    $mac_address = null;

                    if (isset($result['l3connectivities'])) {
                        foreach ($result['l3connectivities'] as $Ip) {
                            if ($Ip['active']) {
                                if ($Ip['af'] == 'ipv4') {
                                    $Ipv4 = $Ip['addr'];
                                } else {
                                    $Ipv6 = $Ip['addr'];
                                }
                            }
                        }
                        if (isset($result['l2ident'])) {
                            $ident = $result['l2ident'];
                            if ($ident['type'] == 'mac_address') {
                                $mac_address = $ident['id'];
                            }
                        }

                        if ($result['active'] == true) {
                            $order = $order_count_active++;
                            // Liste des actifs
                            if ($active_list == null) {
                                $active_list = $mac_address . '|' . $result['primary_name'] . ' ( ' . $mac_address . ')';
                            } else {
                                $active_list .= ';' . $mac_address . '|' . $result['primary_name'] . ' ( ' . $mac_address . ')';
                            }
                            // Liste des équipements 
                            if ($network_list == null) {
                                $network_list = $result['id'] . '|' . $result['primary_name'] . ' ( ' . $mac_address . ')';
                            } else {
                                $network_list .= ';' . $result['id'] . '|' . $result['primary_name'] . ' ( ' . $mac_address . ')';
                            }
                            $value = true;
                        } else {
                            $order = $order_count_noactive++;
                            $value = 0;
                            // Liste des non actifs
                            if ($noactive_list == null) {
                                $noactive_list = $mac_address . '|' . $result['primary_name'] . ' ( ' . $mac_address . ')';
                            } else {
                                $noactive_list .= ';' . $mac_address . '|' . $result['primary_name'] . ' ( ' . $mac_address . ')';
                            }
                            // Liste des équipements 
                            if ($network_list == null) {
                                $network_list = $result['id'] . '|' . $result['primary_name'] . ' ( ' . $mac_address . ')';
                            } else {
                                $network_list .= ';' . $result['id'] . '|' . $result['primary_name'] . ' ( ' . $mac_address . ')';
                            }
                        }

                        $Parameter = array(
                            "updatename" =>  $updatename,
                            "host_type" => $result['host_type'],
                            "IPV4" => $Ipv4,
                            "IPV6" => $Ipv6,
                            "mac_address" => $mac_address,
                            "order" => $order,
                            "repeat" => true,
                        );

                        $EqLogic->AddCommand($result['primary_name'], $result['id'], 'info', 'binary', 'Freebox_OS::Network', null, null, $_IsVisible, 'default', 'default', 0, null, 0, 'default', 'default', null, '0', $updateWidget, true, null, null, null, null, null, null, null, null, null, null, null, $Parameter, $name_connectivity_type);
                    }
                }
            }
            log::add('Freebox_OS', 'debug', '| ───▶︎ :fg-success:' . (__('Appareil(s) connecté(s)', __FILE__)) . ' ::/fg: ' . $active_list);
            log::add('Freebox_OS', 'debug', '| ───▶︎ :fg-success:' . (__('Appareil(s) non connecté(s)', __FILE__)) . ' ::/fg: ' . $noactive_list);
            $_IsVisible = 0;
            //$_networkname = $logicalinfo['managementName'];
            log::add('Freebox_OS', 'debug', '| ───▶︎ :fg-success:' . (__('Ajout des commandes spécifiques pour l\'équipement', __FILE__)) . ' ::/fg: ' . $logicalinfo['managementName']);
            $EqLogic = Freebox_OS::AddEqLogic($logicalinfo['managementName'], $logicalinfo['managementID'], 'default', false, null, null, null, '0 0 1 1 *', null, null, 'system', true, null);
            $host_type = $EqLogic->AddCommand(__('Appareil connecté choisi', __FILE__), 'host_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_network, 0, 'default', 'default', 14, '0', false, true, null, true);
            $EqLogic->AddCommand(__('Sélection appareil connecté', __FILE__), 'host', 'action', 'select', null, null, null, $_IsVisible, $host_type, 'default', 0, $icon_network, 0, 'default', 'default', 15, '0', false, true, null, true, null, null, null, null, null, null, null, null, $network_list, null, null);
            $EqLogic->refreshWidget();
        } else {
            log::add('Freebox_OS', 'debug', '| ───▶︎ ' . (__('PAS D\'APPAREIL TROUVE', __FILE__)));
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_notification($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '    . $logicalinfo['notificationName'] . ' ──');
        $Free_API = new Free_API();
        $Free_API->universal_get('universalAPI', null, null, '/notif/targets', true, true, false);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_system_full($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '    .  $logicalinfo['systemName'] . ' ──');
        $system = Freebox_OS::AddEqLogic($logicalinfo['systemName'], $logicalinfo['systemID'], 'default', false, null, null, null, '*/30 * * * *', null, null, 'system', true);
        $order = 10;
        Free_CreateEq::createEq_system($logicalinfo, $templatecore_V4, $order, $system);
        $order = 1;
        Free_CreateEq::createEq_system_lan($logicalinfo, $templatecore_V4, $order, $system);
        $order = 20;
        Free_CreateEq::createEq_system_SP($logicalinfo, $templatecore_V4, $order, $system);
        $order = 49;
        Free_CreateEq::createEq_system_SP_lang($logicalinfo, $templatecore_V4, $order, $system);
        $order = 60;
        Free_CreateEq::createEq_system_standby($logicalinfo, $templatecore_V4, $order, $system);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_system($logicalinfo, $templatecore_V4, $order = 10, $system = null)
    {
        log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ ' . (__('Ajout des commandes spécifiques', __FILE__)) . ' ::/fg: ' . $logicalinfo['systemName'] . ' - ' . (__('Standards', __FILE__)));
        $iconReboot = 'fas fa-sync icon_red';
        $icondisk_status = 'mdi-harddisk icon_green';
        $icondisk_model_name = 'techno-freebox icon_green';
        $updateicon = false;
        if ($system != null) {
            //Model_info
            $system->AddCommand(__('Modele de Freebox', __FILE__), 'model_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'model_info',  0, $icondisk_model_name, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            //SYSTEM
            $system->AddCommand(__('Freebox firmware version', __FILE__), 'firmware_version', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'system', 0, null, 0, 'default', 'default', 1, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            $system->AddCommand(__('Mac', __FILE__), 'mac', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'system', 0, null, 0, 'default', 'default',  2, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            $system->AddCommand(__('Allumée depuis', __FILE__), 'uptime', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'system', 0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            $system->AddCommand('Board name', 'board_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'system', 0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            $system->AddCommand(__('Numéro de série', __FILE__), 'serial', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'system', 0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            $system->AddCommand(__('Status du disque', __FILE__), 'disk_status', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', null, 0,  $icondisk_status, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            //Mise à jour
            $system->AddCommand(__('Info mise à jour Freebox Server', __FILE__), 'state', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'update', 0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            //Model_info
            $system->AddCommand(__('Type de Freebox', __FILE__), 'pretty_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'model_info', 0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            $system->AddCommand(__('Type de Wifi', __FILE__), 'wifi_type', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'model_info',  0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);


            // A traiter a part
            $order = 130;
            $system->AddCommand(__('Redémarrage', __FILE__), 'reboot', 'action', 'other',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconReboot, true, 'default', 'default',   $order++, '0', true, null, null, true, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        }
    }
    private static function createEq_system_standby($logicalinfo, $templatecore_V4, $order = 1, $system = null)
    {
        log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ ' . (__('Ajout des commandes spécifiques pour l\'équipement', __FILE__)) . ' ::/fg: ' .  $logicalinfo['systemName'] . ' - Mode Standby Disponible');
        if ($system != null) {
            $Free_API = new Free_API();
            $result = $Free_API->universal_get('system', null, null, null, true, true, null);
            if (isset($result['model_info']['has_standby'])) {
                $system->AddCommand(__('Mode Standby disponible', __FILE__), 'has_standby', 'info', 'binary',  $templatecore_V4 . 'line', null, null, 0, 'default', 'model_info',  0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            } else {
                log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ ' . (__('Mode Gestion d\'énergie pas disponible', __FILE__)) . ':/fg:');
            }
            if (isset($result['model_info']['has_eco_wifi'])) {
                $system->AddCommand(__('Mode Veille Wifi', __FILE__), 'has_eco_wifi', 'info', 'binary',  $templatecore_V4 . 'line', null, null, 0, 'default', 'model_info',  0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            } else {
                log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ ' . (__('Mode Veille Wifi pas disponible', __FILE__)) . ':/fg:');
            }
        }
    }
    private static function createEq_system_lan($logicalinfo, $templatecore_V4, $order = 1, $system = null)
    {
        log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ ' . (__('Ajout des commandes spécifiques pour l\'équipement', __FILE__)) . ' ::/fg: ' .  $logicalinfo['systemName'] . ' - LAN');
        if ($system != null) {
            $icondisk_model_name = 'kiko-router icon_green';
            $updateicon = false;
            //LAN
            $system->AddCommand(__('Nom Freebox', __FILE__), 'name', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'LAN', 0, null, 0, 'default', 'default', $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            $system->AddCommand(__('Mode Freebox', __FILE__), 'mode', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'LAN', 0, $icondisk_model_name, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            $system->AddCommand(__('Ip', __FILE__), 'ip', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'LAN', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            Free_Refresh::RefreshInformation($logicalinfo['systemID']);
        }
    }

    private static function createEq_system_SP($logicalinfo, $templatecore_V4, $order = 20, $system = null)
    {
        log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ ' . (__('Ajout des commandes spécifiques pour l\'équipement', __FILE__)) . ' ::/fg: ' . $logicalinfo['systemName'] . ' - Capteurs');
        if ($system != null) {
            $Free_API = new Free_API();
            $Template4G = 'Freebox_OS::4G';
            $templatecore_V4  = 'core::';
            $icontemp = 'fas fa-thermometer-half icon_blue';
            $iconfan = 'fas fa-fan icon_blue';
            $icon4Gon = 'fas fa-broadcast-tower icon_green';
            $icon4Goff = 'fas fa-broadcast-tower icon_red';

            $boucle_num = 1; // 1 = sensors - 2 = fans - 3 = extension

            while ($boucle_num <= 3) {

                if ($boucle_num == 1) {
                    $boucle_update = 'sensors';
                } else if ($boucle_num == 2) {
                    $boucle_update = 'fans';
                } else if ($boucle_num == 3) {
                    $boucle_update = 'expansions';
                }
                $result_SP = $Free_API->universal_get('system', null, $boucle_update, null, true, true, false);
                if ($boucle_num == 3) {
                    if (isset(($result_SP['has_expansions']))) {
                        log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Module expansions disponible', __FILE__)) . ':/fg:');
                    } else {
                        log::add('Freebox_OS', 'info', '| :fg-info:───▶︎ ' . (__('Module expansions non disponible', __FILE__)) . ':/fg:');
                        break;
                    }
                }
                if ($result_SP != false) {
                    log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ ' . (__('Boucle pour la mise à jour', __FILE__)) . ' ::/fg: ' . $boucle_update);

                    foreach ($result_SP  as $Equipement) {
                        if ($Equipement != null) {
                            $icon = null;
                            $_max = 'default';
                            $_min = 'default';
                            $_unit = null;
                            if ($boucle_update != 'expansions') {
                                $_name = $Equipement['name'];
                                $_id = $Equipement['id'];
                                $_value = $Equipement['value'];
                            }
                            $_type = 'numeric';
                            $IsVisible = 1;
                            $_iconname = true;
                            if (strpos($_id, 'temp') !== FALSE) {
                                $_unit = '°C';
                                $_max = 100;
                                $_min = '0';
                                $icon = $icontemp;
                                $link_logicalId = 'sensors';
                            } else if (strpos($_id, 'fan') !== FALSE) {
                                $_unit = 'tr/min';
                                $_max = 5000;
                                $_min = '0';
                                $icon = $iconfan;
                                $link_logicalId = 'fans';
                            } else if ($boucle_num = 3) {
                                $_iconname = null;
                                $_type = 'binary';
                                $_id = $Equipement['slot'];
                                $_name = __('Slot', __FILE__) . ' ' . $Equipement['slot'] . ' - ' . $Equipement['type'];
                                $IsVisible = '0';
                                $_value = $Equipement['present'];
                                $link_logicalId = 'expansions';
                            }
                            //log::add('Freebox_OS', 'debug', '| ───▶︎ Name : ' . $_name . ' -- id : ' . $_id . ' -- value : ' . $_value . ' -- unité : ' . $_unit . ' -- type : ' . $_type);
                            if ($_name != '') {
                                $system->AddCommand($_name, $_id, 'info', $_type, $templatecore_V4 . 'line', $_unit, null, $IsVisible, 'default', $link_logicalId, 0, $icon, 0, $_min, $_max, $order, 0, false, true, null, $_iconname, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
                                $system->checkAndUpdateCmd($_id, $_value);

                                if ($boucle_update == 'expansions') {
                                    if ($Equipement['type'] == 'dsl_lte') {
                                        // Début ajout 4G
                                        $order = 31;
                                        $_4G = $system->AddCommand(__('Etat 4G', __FILE__), '4GStatut', "info", 'binary', $templatecore_V4 . 'line', null, 'SWITCH_STATE', 0, '', '4G', '', '', 1, 'default', 'default', $order++, '0', false, 'never', null, true);
                                        $system->AddCommand(__('4G On', __FILE__), '4GOn', 'action', 'other', $Template4G, null, 'STATE_ON', 1, $_4G, '4GStatut', 0, $icon4Gon, 1, 'default', 'default', $order++, '0', false, false, null, true);
                                        $system->AddCommand(__('4G Off', __FILE__), '4GOff', 'action', 'other', $Template4G, null, 'STATE_OFF', 1, $_4G, '4GStatut', 0, $icon4Goff, 0, 'default', 'default', $order++, '0', false, false, null, true);
                                        $system->AddCommand(__('Etat du réseau 4G', __FILE__), 'state_lte', 'info', 'string', 'default', null, 'default', 1, 'default', 'default', 0, 'default', 0, 'default', 'default', $order++, '0', false, false, null, true);
                                        $system->AddCommand(__('Etat de la radio 4G', __FILE__), 'associated_lte', 'info', 'binary', 'default', null, 'default', 1, 'default', 'default', 0, 'default', 0, 'default', 'default', $order++, '0', false, false, null, true);
                                    }
                                }
                                $order++;
                            }
                        }
                    }
                } else {
                    log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ ' . (__('Pas de commande spécifique', __FILE__)) . ' : ' . $logicalinfo['systemName'] . ' ' . (__('pour', __FILE__)) . ' ' . $boucle_update . ':/fg:');
                    break;
                }
                $boucle_num++;
            }
        }
    }
    private static function createEq_system_SP_lang($logicalinfo, $templatecore_V4, $order = 49, $system = null)
    {
        $iconLang = 'fas fa-language icon_blue';
        if ($system != null) {
            log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ ' . (__('Ajout des commandes spécifiques pour l\'équipement', __FILE__)) . ' ::/fg: ' .  $logicalinfo['systemName'] . ' - ' . (__('langues', __FILE__)));
            // Recherche Langue disponible
            $Free_API = new Free_API();
            $result = $Free_API->universal_get('universalAPI', null, null, 'lang', true, true, null);
            $avalaibleList = null;
            if (isset($result['avalaible'])) {
                foreach ($result['avalaible'] as $lang) {
                    if ($lang === 'fra') {
                        $langList = __('Français', __FILE__);
                    } else if ($lang === 'eng') {
                        $langList = __('Anglais', __FILE__);
                    } else if ($lang === 'ita') {
                        $langList = __('Italien', __FILE__);
                    }
                    if ($avalaibleList != null) {
                        $avalaibleList  .= ';';
                    }
                    $avalaibleList  .= $lang . '|' . $langList;
                }
            }

            // Ajout Commande
            $avalaible = $system->AddCommand(__('langue de la Box', __FILE__), 'lang', 'info', 'string', 'default', null, 'default', 1, 'default', 'LANG', 0, $iconLang, 1, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
            //$system->AddCommand('Choix Langue', 'avalaible', 'action', 'select', null, null, null, 1, $avalaible, 'default', 0, null, 0, null, null, $order++, '0', null, false, null, true, null, null, null, null, null, null, null, null, $avalaibleList);
        }
    }
    private static function createEq_VM($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['VMName'] . ' ──');
        $updateicon = true;
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('universalAPI', null, null, 'vm', false, false, false);
        if ($result != null) {
            $VMmemory = 'fas fa-memory';
            $VMCPU = 'fas fa-microchip';
            $VMscreen = 'fas fa-desktop';
            $VMdisk = 'fas fa-hdd';
            $VMstatus = 'fas fa-info-circle';
            $VMOn = 'fas fa-play icon_green';
            $VMOff = 'fas fa-stop icon_red';
            $VMRestart = 'fas fa-sync icon_red';
            $VMUSB = 'fab fa-usb icon_green';
            $VMstatus = 'fas fa-info-circle icon_green';
            $TemplateVM = 'Freebox_OS::VM';
            foreach ($result as $Equipement) {
                $order = 0;
                if ($Equipement['name'] == null && $Equipement['cloudinit_hostname'] != null) {
                    $VM_name = $Equipement['cloudinit_hostname'];
                } else if ($Equipement['name'] != null) {
                    $VM_name = $Equipement['name'];
                } else {
                    $VM_name = 'VM_' . $Equipement['id'];
                }
                $_VM = Freebox_OS::AddEqLogic($VM_name, 'VM_' . $Equipement['id'], 'multimedia', true, 'VM', null, $Equipement['id'], '*/5 * * * *', null, null, 'system', true);
                $_VM->AddCommand(__('Status', __FILE__), 'status', 'info', 'string', $TemplateVM, null, 'default', 1, 'default', 'default', 0, $VMstatus, 0, 'default', 'default', $order++, '0', $updateicon, false, false, true, null, null, null, null, null, null, null, true);
                $_VM->AddCommand(__('Start', __FILE__), 'start', 'action', 'other', 'default', null, 'default', 1, 'default', 'default', 0, $VMOn, 0, 'default', 'default', $order++, '0', $updateicon, false);
                $_VM->AddCommand(__('Stop', __FILE__), 'stop', 'action', 'other', 'default', null, 'default', 1, 'default', 'default', 0, $VMOff, 0, 'default', 'default', $order++, '0', $updateicon, false);
                $_VM->AddCommand(__('Redémarrer', __FILE__), 'restart', 'action', 'other', 'default', null, 'default', 1, 'default', 'default', 0, $VMRestart, 0, 'default', 'default', $order++, '0', $updateicon, false, null, null, null, null, null, null, null, null, null, true);
                $order = 10;
                $_VM->AddCommand(__('CPU(s)', __FILE__), 'vcpus', 'info', 'numeric',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, $VMCPU, 0, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand(__('Mac', __FILE__), 'mac', 'info', 'string',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand(__('Mémoire', __FILE__), 'memory', 'info', 'numeric',  $templatecore_V4 . 'line', 'Mo', 'default', 0, 'default', 'default', 0, $VMmemory, 0, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand(__('USB', __FILE__), 'bind_usb_ports', 'info', 'string',  null, null, 'default', 1, 'default', 'default', 0, $VMUSB, 1, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand(__('Ecran virtuel', __FILE__), 'enable_screen', 'info', 'binary',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, $VMscreen, '0', 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand(__('Nom', __FILE__), 'name', 'info', 'string',  null, null, 'default', 0, 'default', 'default', 0, 'default', 1, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand(__('Type de disque', __FILE__), 'disk_type', 'info', 'string',  null, null, 'default', 0, 'default', 'default', 0, $VMdisk, 1, 'default', 'default', $order++, '0', $updateicon, false, false, true);
            }
        } else {
            log::add('Freebox_OS', 'debug', '|:fg-warning: ──────▶︎ ' . (__('PAS DE', __FILE__)) . ' ' . $logicalinfo['VMName'] . ' ' . (__('SUR VOTRE BOX', __FILE__)) . ':/fg:');
        }
    }

    private static function createEq_wifi($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['wifiName'] . ' ──');
        $updateicon = false;
        $TemplateWifiOnOFF = 'Freebox_OS::Wifi';
        $TemplateWifiPlanningOnOFF = 'Freebox_OS::Planning Wifi';
        $TemplateWifiWPSOnOFF = 'Freebox_OS::Wfi WPS';
        $iconWifiOn = 'fas fa-wifi icon_green';
        $iconWifiOff = 'fas fa-times icon_red';
        $iconWifiPlanningOn = 'fas fa-calendar-alt icon_green';
        $iconWifiPlanningOff = 'fas fa-calendar-times icon_red';
        $iconWifiWPSOn = 'fas fa-ethernet icon_green';
        $iconWifiWPSOff = 'fas fa-ethernet icon_red';

        $Wifi = Freebox_OS::AddEqLogic($logicalinfo['wifiName'], $logicalinfo['wifiID'], 'default', false, null, null, null, '*/5 * * * *', null, null, 'system', true, null);
        $StatusWifi = $Wifi->AddCommand(__('Etat Wifi', __FILE__), 'wifiStatut', "info", 'binary', null, null, 'SWITCH_STATE', 0, '', '', '', '', 0, 'default', 'default', 1, 1, $updateicon, true);
        $Wifi->AddCommand(__('Wifi On', __FILE__), 'wifiStatutOn', 'action', 'other', $TemplateWifiOnOFF, null, 'SWITCH_ON', 1, $StatusWifi, 'wifiStatut', 0, $iconWifiOn, 0, 'default', 'default', 10, '0', $updateicon, false);
        $Wifi->AddCommand(__('Wifi Off', __FILE__), 'wifiStatutOff', 'action', 'other', $TemplateWifiOnOFF, null, 'SWITCH_OFF', 1, $StatusWifi, 'wifiStatut', 0, $iconWifiOff, 0, 'default', 'default', 11, '0', $updateicon, false);
        // Planification Wifi
        $PlanningWifi = $Wifi->AddCommand(__('Etat Planning', __FILE__), 'use_planning', "info", 'binary', null, null, 'SWITCH_STATE', 0, '', '', '', '', 0, 'default', 'default', '0', 2, $updateicon, true);
        $Wifi->AddCommand(__('Wifi Planning On', __FILE__), 'use_planningOn', 'action', 'other', $TemplateWifiPlanningOnOFF, null, 'SWITCH_ON', 1, $PlanningWifi, 'wifiPlanning', 0, $iconWifiPlanningOn, 0, 'default', 'default', 12, '0', $updateicon, false);
        $Wifi->AddCommand(__('Wifi Planning Off', __FILE__), 'use_planningOff', 'action', 'other', $TemplateWifiPlanningOnOFF, null, 'SWITCH_OFF', 1, $PlanningWifi, 'wifiPlanning', 0, $iconWifiPlanningOff, 0, 'default', 'default', 13, '0', $updateicon, false);
        $order = 49;
        Free_CreateEq::createEq_wifi_ap($logicalinfo, $templatecore_V4, $order, $Wifi);
        $order = 29;
        // Wifi WPS
        Free_CreateEq::createEq_wifi_bss($logicalinfo, $templatecore_V4, $order, $Wifi);
        $order = 39;
        Free_CreateEq::createEq_mac_filter($logicalinfo, $templatecore_V4, $order, $Wifi);
        $order = 50;
        Free_CreateEq::createEq_wifi_Standby($logicalinfo, $templatecore_V4, $order, $Wifi);
        $order = 60;
        Free_CreateEq::createEq_wifi_Eco($logicalinfo, $templatecore_V4, $order, $Wifi);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_wifi_ap($logicalinfo, $templatecore_V4, $order = 49, $Wifi = null)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['wifiName'] . ' / ' . $logicalinfo['wifiAPName'] . ' ──');
        if ($Wifi != null) {
            $iconWifi = 'fas fa-wifi icon_blue';
            $TemplateWifi = 'Freebox_OS::Wifi Statut carte';
            $updateicon = false;;
            $Free_API = new Free_API();
            $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/ap', true, true, true);

            $nb_card = count($result['result']);
            if ($result != false) {
                for ($k = 0; $k < $nb_card; $k++) {
                    log::add('Freebox_OS', 'debug', '| ──────▶︎ ' . (__('Nom de la commande', __FILE__)) . ' : ' .  (__('Etat Wifi', __FILE__)) . ' ' . $result['result'][$k]['name'] . ' - Id : ' . $result['result'][$k]['id'] . ' - ' . (__('Status', __FILE__)) . ' : ' . $result['result'][$k]['status']['state']);
                    $Wifi->AddCommand(__('Etat Wifi', __FILE__) . ' ' . $result['result'][$k]['name'], $result['result'][$k]['id'], 'info', 'string', $TemplateWifi, null, null, 1, 'CARD', 0, $iconWifi, false, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                }
            }
        }
    }

    private static function createEq_wifi_Eco($logicalinfo, $templatecore_V4, $order = 49, $Wifi = null)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['wifiName'] . ' / ' . $logicalinfo['wifiECOName'] . ' ──');
        if ($Wifi != null) {
            $iconWifi = 'fas fa-wifi icon_red';
            $iconpower_saving = 'fas fa-wifi icon_orange';
            $iconWifiOn = 'fas fa-wifi icon_green';
            $iconWifiOff = 'fas fa-wifi icon_red';
            $TemplateEcoWifi = 'Freebox_OS::Mode Eco Wifi';
            $Free_API = new Free_API();
            $result = $Free_API->universal_get('system', null, null, null, true, true, null);

            if (isset($result['model_info']['has_eco_wifi'])) {
                $Wifi->AddCommand(__('Support Mode Éco-WiFi', __FILE__), 'has_eco_wifi', 'info', 'binary',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default',  0, $iconWifi, 0, 'default', 'default',  $order++, '0', false, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
                $power_saving = $Wifi->AddCommand(__('Etat Mode Éco-WiFi', __FILE__), 'power_saving', 'info', 'binary',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default',  0, $iconpower_saving, 1, 'default', 'default',  $order++, true, false, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
                $Wifi->AddCommand(__('Mode Éco-WiFi On', __FILE__), 'power_savingOn', 'action', 'other', $TemplateEcoWifi, null, 'SWITCH_ON', 1, $power_saving, null, 0, $iconWifiOn, 0, 'default', 'default', $order++, '0', false, false);
                $Wifi->AddCommand(__('Mode Éco-WiFi Off', __FILE__), 'power_savingOff', 'action', 'other', $TemplateEcoWifi, null, 'SWITCH_OFF', 1, $power_saving, null, 0, $iconWifiOff, 0, 'default', 'default', $order++, '0', false, false);
            } else {
                config::save('FREEBOX_HAS_ECO_WFI', 0, 'Freebox_OS');
                log::add('Freebox_OS', 'debug', '| ──────▶︎ ' . (__('Pas de mode Eco non supporté', __FILE__)));
            }
        }
    }

    private static function createEq_wifi_bss($logicalinfo, $templatecore_V4, $order = 29, $Wifi = null)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:' . (__('Début de création des commandes spécifiques pour', __FILE__)) . ' ::/fg: '  . $logicalinfo['wifiName'] . ' / ' . $logicalinfo['wifiWPSName'] . ' ──');
        if ($Wifi != null) {
            $iconWifiSessionWPSOn = 'fas fa-link icon_orange';
            $iconWifiSessionWPSOff = 'fas fa-link icon_red';
            $updateicon = false;

            $WifiWPS = $Wifi->AddCommand(__('Etat WPS', __FILE__), 'wifiWPS', "info", 'binary', null, null, 'SWITCH_STATE', 0, '', '', '', '', 0, 'default', 'default', '0', 3, $updateicon, true);
            $Wifi->AddCommand(__('Wifi Session WPS (toutes les sessions) Off', __FILE__), 'wifiSessionWPSOff', 'action', 'other', null, null, 'SWITCH_OFF', 1, $WifiWPS, 'wifiWPS', 0, $iconWifiSessionWPSOff, true, 'default', 'default', $order++, '0', $updateicon, false, false, true);
            $Free_API = new Free_API();
            $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/bss', true, true, true);
            if ($result != false) {
                foreach ($result['result'] as $wifibss) {
                    if ($wifibss['config']['wps_enabled'] != true) continue;
                    if ($wifibss['config']['use_default_config'] == true) {
                        $WPSname = __('Wifi Session WPS', __FILE__) . ' (' . $wifibss['shared_bss_params']['ssid'] . ') On';
                    } else {
                        $WPSname = __('Wifi Session WPS', __FILE__) . ' (' . $wifibss['config']['ssid'] . ') On';
                    }
                    $Wifi->AddCommand($WPSname, $wifibss['id'], 'action', 'other', null, null, 'SWITCH_ON', 1, $WifiWPS, 'wifiWPS', 0, $iconWifiSessionWPSOn, true, 'default', 'default', $order++, '0', $updateicon, false, false, true, null, null, null, null, null, null, null, true);
                    if ($wifibss['config']['use_default_config'] == true) {
                        log::add('Freebox_OS', 'debug', '| ──────▶︎ ' . (__('Configuration Wifi commune pour l\'ensemble des cartes', __FILE__)));
                        break;
                    } else {
                        //$order++;
                    }
                }
            }
        }
    }
    private static function createEq_wifi_Standby($logicalinfo, $templatecore_V4, $order = 29, $Wifi = null)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:' . (__('Début de création des commandes spécifiques pour', __FILE__)) . ' ::/fg: ' . $logicalinfo['wifistandbyName'] . ' ──');
        $updateicon = false;
        if ($Wifi != null) {
            $planning_mode = $Wifi->AddCommand(__('Etat Mode de veille planning', __FILE__), 'planning_mode', 'info', 'string', 'default', null, 'default', 1, 'default', 'default', 0, 'default', 0, 'default', 'default', $order++, '0', $updateicon, false, false, true, null, null, null, null, null, null, null, true);
            $listValue = "wifi_off|" . __('Veille Wifi', __FILE__) . ";suspend|" . __('Veille totale', __FILE__);
            //$Wifi->AddCommand(__('Choix Mode de veille planning', __FILE__), 'mode_planning', 'action', 'select', null, null, null, 1, $planning_mode, 'mode', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, null, $listValue);
        }
    }

    private static function createEq_mac_filter($logicalinfo, $templatecore_V4, $order = 39, $EqLogic = null)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:' . (__('Début de création des commandes spécifiques pour', __FILE__)) . ' ::/fg: ' . $logicalinfo['wifimmac_filter'] . ' ──');
        if ($EqLogic != null) {
            $iconmac_list_white = 'fas fa-list-alt';
            $iconmac_list_black = 'far fa-list-alt';

            //$Statutmac = $EqLogic->AddCommand('Etat Mode de filtrage', 'wifimac_filter_state', "info", 'string', $Templatemac, null, null, 1, null, null, null, null, 1, 'default', 'default', $order++, 1, false, true, null, true);
            //$listValue = 'disabled|Désactiver;blacklist|Liste Noire;whitelist|Liste Blanche';
            //$EqLogic->AddCommand('Mode de filtrage', 'mac_filter_state', 'action', 'select', null, null, null, 1, $Statutmac, 'wifimac_filter_state', null, $iconmac_filter_state, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null, null, null, null, null, $listValue);
            $EqLogic->AddCommand(__('Liste Mac Blanche', __FILE__), 'whitelist', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmac_list_white, 0, 'default', 'default',  $order++, '0', null, true, false, true, null, null, null, null, null, null, null, true);
            $EqLogic->AddCommand(__('Liste MAC Noire', __FILE__), 'blacklist', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmac_list_black, 0, 'default', 'default',  $order++, '0', null, true, false, true, null, null, null, null, null, null, null, true);
        }
    }

    private static function createEq_upload($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:' . (__('Début de création des commandes pour', __FILE__)) . ' ::/fg: ' . $logicalinfo['notificationName'] . ' ──');
        $Free_API = new Free_API();
        $Free_API->universal_get('upload', null, null, null, null, null);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
}
