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
            log::add('Freebox_OS', 'debug', '[WARNING] : Version API Compatible avec la Freebox : ' . $result_API);
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
                Free_CreateEq::createEq_LCD($logicalinfo, $templatecore_V4, $order);
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
                log::add('Freebox_OS', 'debug', '[INFO] - ORDRE DE LA CREATION DES EQUIPEMENTS STANDARDS -- ' . $date);
                config::save('SEARCH_EQ', config::byKey('SEARCH_EQ', 'Freebox_OS', $date), 'Freebox_OS');
                log::add('Freebox_OS', 'debug', '[INFO] - ORDRE DE LA CREATION DES EQUIPEMENTS STANDARDS');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['systemName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['connexionName'] . ' / 4G' . ' / Fibre' . ' / xdsl' . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['freeplugName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['diskName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['phoneName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['LCDName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['airmediaName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['downloadsName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['networkName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['networkwifiguestName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['netshareName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ' . $logicalinfo['wifiName'] . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ENSEMBLE DES PLAYERS SOUS TENSION' . ':/fg:');
                log::add('Freebox_OS', 'debug', ':fg-info:================= ENSEMBLE DES VM' . ':/fg:');
                log::add('Freebox_OS', 'debug', '');
                Free_CreateEq::createEq_system_full($logicalinfo, $templatecore_V4, $order);
                //log::add('Freebox_OS', 'debug', '====================================================================================');
                Free_CreateEq::createEq_connexion($logicalinfo, $templatecore_V4);
                //log::add('Freebox_OS', 'debug', '====================================================================================');
                Free_CreateEq::createEq_FreePlug($logicalinfo, $templatecore_V4, $order);
                $result_disk = Free_CreateEq::createEq_disk_check($logicalinfo);
                log::add('Freebox_OS', 'debug', '┌── :fg-success:Check Présence disque :/fg:──');
                if ($result_disk == true) {
                    Free_CreateEq::createEq_disk($logicalinfo, $templatecore_V4, $order);
                } else {
                    log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ AUCUN DISQUE => PAS DE CREATION DE L\'EQUIPEMENT:/fg:');
                }
                log::add('Freebox_OS', 'debug', '└────────────────────');

                Free_CreateEq::createEq_phone($logicalinfo, $templatecore_V4, $order);
                Free_CreateEq::createEq_netshare($logicalinfo, $templatecore_V4, $order);
                $Type_box = config::byKey('TYPE_FREEBOX', 'Freebox_OS');
                log::add('Freebox_OS', 'debug', '┌── :fg-success:Check Compatibilité avec l\'option VM :/fg:──');
                if ($Type_box == 'fbxgw1r' || $Type_box == 'fbxgw2r' || $Type_box == 'fbxgw9r') {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ BOX COMPATIBLE AVEC LA MODIFICATION DE L\'AFFICHEUR : ' . $Type_box);
                    log::add('Freebox_OS', 'debug', '└────────────────────');
                    Free_CreateEq::createEq_LCD($logicalinfo, $templatecore_V4, $order);
                } else {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ BOX NON COMPATIBLE AVEC LA MODIFICATION DE L\'AFFICHEUR : ' . $Type_box);
                    log::add('Freebox_OS', 'debug', '└────────────────────');
                }

                if (config::byKey('TYPE_FREEBOX_MODE', 'Freebox_OS') == 'router') {
                    Free_CreateEq::createEq_airmedia($logicalinfo, $templatecore_V4, $order);
                    if ($result_disk == true) {
                        Free_CreateEq::createEq_download($logicalinfo, $templatecore_V4, $order);
                    } else {
                        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['downloadsName'] . ':/fg: ──');
                        log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ AUCUN DISQUE => PAS DE CREATION DE L\'EQUIPEMENT:/fg:');
                        log::add('Freebox_OS', 'debug', '└────────────────────');
                    }

                    Free_CreateEq::createEq_management($logicalinfo, $templatecore_V4, $order);
                    Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4, $order, 'LAN');
                    Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4, $order, 'WIFIGUEST');
                    Free_CreateEq::createEq_wifi($logicalinfo, $templatecore_V4, $order);
                    //Free_CreateEq::createEq_notification($logicalinfo, $templatecore_V4);
                } else {
                    log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ BOX EN MODE BRIDGE : LES ÉQUIPEMENTS SUIVANTS NE SONT PAS CRÉER  ' . ':/fg:');
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $logicalinfo['airmediaName']);
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $logicalinfo['downloadsName']);
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $logicalinfo['networkName'] . ' / ' . $logicalinfo['networkwifiguestName']);
                }
                log::add('Freebox_OS', 'debug', '┌── :fg-success:Check Compatibilité avec l\'option VM :/fg:──');
                if ($Type_box != 'fbxgw1r' && $Type_box != 'fbxgw2r') {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ BOX COMPATIBLE AVEC LES VM ');
                    Free_CreateEq::createEq_VM($logicalinfo, $templatecore_V4, $order);
                } else {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ BOX NON COMPATIBLE AVEC LES VM');
                }
                log::add('Freebox_OS', 'debug', '└────────────────────');
                config::save('SEARCH_EQ', $date, 'Freebox_OS');
                break;
        }
    }
    private static function createEq_Type_Box()
    {
        log::add('Freebox_OS', 'info', '┌── :fg-success:Check Compatibilité avec l\'option domotique :/fg:──');
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('system', null, null);
        if ($result['board_name'] == 'fbxgw7r') {
            $Type_box = 'OK';
        } else {
            $Type_box = 'KO';
            config::save('FREEBOX_TILES_CRON', 0, 'Freebox_OS');
            $cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxGET');
            if (is_object($cron)) {
                $cron->stop();
                $cron->remove();
                log::add('Freebox_OS', 'info', '| [  OK  ] - SUPPRESSION CRON DOMOTIQUE');
            }
            log::add('Freebox_OS', 'info', '| ───▶︎ Etat CRON Domotique : ' . config::byKey('FREEBOX_TILES_CRON', 'Freebox_OS'));
        }
        config::save('TYPE_FREEBOX', $result['board_name'], 'Freebox_OS');
        log::add('Freebox_OS', 'info', '| ───▶︎ Board name : ' . config::byKey('TYPE_FREEBOX', 'Freebox_OS'));
        config::save('TYPE_FREEBOX_NAME', $result['model_info']['pretty_name'], 'Freebox_OS');
        log::add('Freebox_OS', 'info', '| ───▶︎ Type de box : ' . config::byKey('TYPE_FREEBOX_NAME', 'Freebox_OS'));
        config::save('TYPE_FREEBOX_TILES', $Type_box, 'Freebox_OS');
        log::add('Freebox_OS', 'info', '| ───▶︎ Compatibilité Domotique : ' . config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS'));
        log::add('Freebox_OS', 'info', '└────────────────────');
        return $Type_box;
    }
    private static function createEq_airmedia($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['airmediaName'] . ':/fg: ──');
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
                log::add('Freebox_OS', 'debug', '| ───▶︎ Equipements détectées : ' . $receivers_list);
            }
        }

        $EqLogic = Freebox_OS::AddEqLogic($logicalinfo['airmediaName'], $logicalinfo['airmediaID'], 'multimedia', false, null, null, null, '*/5 * * * *', null, null, null, 'system', true);
        $receivers = $EqLogic->AddCommand('Player AirMedia choisi', 'receivers_info', 'info', 'string', 'default', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', $order++, '0', false, true);
        $EqLogic->AddCommand('Choix du Player AirMedia', 'receivers', 'action', 'select', null, null, null, 1, $receivers, 'default', $receivers_icon, null, 0, 'default', 'default', $order++, '0', false, true, null, null, null, null, null, null, null, null, null, null, $receivers_list, null, null);

        $media_type = $EqLogic->AddCommand('Media choisi', 'media_type_info', 'info', 'string', 'default', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', $order++, '0', false, true);
        $media_type_list = null;
        $EqLogic->AddCommand('Choix du Media', 'media_type', 'action', 'select', null, null, null, 1, $media_type, 'default', 0, null, 0, 'default', 'default', $order++, '0', false, true, null, null, null, null, null, null, null, null, null, null, $media_type_list, null, null);

        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => 'URL',

        );
        $media = $EqLogic->AddCommand('URL choisi', 'media_info', 'info', 'string', 'default', null, null, 1, 'default', 'default', 0, $media_icon, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand('Envoyer URL', 'media', 'action', 'message', 'default', null, null, 1, $media, 'default', 0, $media_icon, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, $config_message);

        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => 'Mot de passe',

        );
        $password = $EqLogic->AddCommand('Mot de Passe actuel', 'password_info', 'info', 'string', 'default', null, null, 0, 'default', 'default', 0, $password_icon, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand('Envoyer Mot de passe', 'password', 'action', 'message', 'default', null, null, 1, $password, 'default', 0, $password_icon, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, $config_message);

        $EqLogic->AddCommand('Start', 'start', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $start_icon, 0, 'default', 'default', $order++, '0', $updateicon, false, null, true);
        $EqLogic->AddCommand('Stop', 'stop', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $stop_icon, 0, 'default', 'default', $order++, '0', $updateicon, false, null, true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_connexion($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['connexionName'] . ':/fg: ──');
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
        $Connexion->AddCommand('Débit descendant', 'rate_down', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value# / 1024', '2');
        $Connexion->AddCommand('Débit montant', 'rate_up', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value# / 1024', '2', null, null, null, null, true);
        $Connexion->AddCommand('Débit descendant (max)', 'bandwidth_down', 'info', 'numeric', $templatecore_V4 . 'badge', $_bandwidth_down_unit, null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, $_bandwidth_value_down, '2');
        $Connexion->AddCommand('Débit montant (max)', 'bandwidth_up', 'info', 'numeric', $templatecore_V4 . 'badge', $_bandwidth_up_unit, null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, $_bandwidth_value_up, '2', null, null, null, null, true);
        $Connexion->AddCommand('Reçu', 'bytes_down', 'info', 'numeric', $templatecore_V4 . 'badge', 'Go', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value#  / 1000000000', '2', null, null, null, null, false);
        $Connexion->AddCommand('Émis', 'bytes_up', 'info', 'numeric', $templatecore_V4 . 'badge', 'Go', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value#  / 1000000000', '2', null, null, null, null, true);
        $Connexion->AddCommand('Type de connexion', 'media', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand('Etat de la connexion', 'state', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand('IPv4', 'ipv4', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand('IPv6', 'ipv6', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand('Réponse Ping', 'ping', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand('Proxy Wake on Lan', 'wol', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);

        //log::add('Freebox_OS', 'debug', '[  OK  ] - FIN CREATION : ' . $logicalinfo['connexionName']);
        if ($result['sfp_present'] != null) {
            $order = 19;
            Free_CreateEq::createEq_connexion_FTTH($logicalinfo, $templatecore_V4, $order, $result);
        }
        log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $_modul);

        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_connexion_FTTH($logicalinfo, $templatecore_V4, $order = 19, $result)
    {
        $updateicon = false;

        $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *', null, null, null, 'system', true);
        if (isset($result['link_type'])) {
            $Connexion->AddCommand('Type de connexion Fibre', 'link_type', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        } else {
            log::add('Freebox_OS', 'debug', '| ───▶︎ Fonction type de connexion Fibre non présent');
        }
        log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ Ajout des commandes spécifiques pour la fibre : ' . $logicalinfo['connexionName'] . ':/fg:');
        $Connexion->AddCommand('Module Fibre présent', 'sfp_present', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand('Signal Fibre présent', 'sfp_has_signal', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand('Etat Alimentation', 'sfp_alim_ok', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true);
        $Connexion->AddCommand('Puissance transmise', 'sfp_pwr_tx', 'info', 'numeric', $templatecore_V4 . 'badge', 'dBm', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, '#value# / 100', '2', null, null, null, null, false);
        $Connexion->AddCommand('Puissance reçue', 'sfp_pwr_rx', 'info', 'numeric', $templatecore_V4 . 'badge', 'dBm', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, '#value# / 100', '2', null, null, null, null, true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_connexion_4G($logicalinfo, $templatecore_V4, $order = 19)
    {
        log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ Ajout des commandes spécifiques pour la 4G : ' . $logicalinfo['connexionName'] . ':/fg:');
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('universalAPI', null, null, '/connection/aggregation', true, true, false);

        if ($result != false && $result != 'Aucun module 4G détecté') {
            $_modul = 'Module 4G : Présent';
            log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $_modul);
            $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *', null, null, null, 'system', true);
            log::add('Freebox_OS', 'debug', '[WARNING] - DEBUT CREATION DES COMMANDES POUR LA 4G : ' . $logicalinfo['connexionName']);
            $Connexion->AddCommand('Débit xDSL Descendant', 'tx_used_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, '#value# / 1000', '2', null, null, null, null, true);
            $Connexion->AddCommand('Débit xDSL Montant', 'rx_used_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, '#value# / 1000', '2', null, null, null, null, true);
            $Connexion->AddCommand('Débit xDSL Descendant (max)', 'tx_max_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, '#value# / 1000', '2', null, null, null, null, true);
            $Connexion->AddCommand('Débit xDSL Montant (max)', 'rx_max_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true, null, null, null, '#value# / 1000', '2', null, null, null, null, true);
            $Connexion->AddCommand('Etat de la connexion xDSL 4G', 'state', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', null, true);
        } else {
            $_modul = 'Module 4G : Non Présent';
            log::add('Freebox_OS', 'debug', '| ───▶︎ ' . $_modul);
        }

        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_disk_check($logicalinfo)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['diskName'] . ':/fg: ──');
        Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *', null, null, null, 'system', true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_disk($logicalinfo, $templatecore_V4)
    {
        $Free_API = new Free_API();
        log::add('Freebox_OS', 'debug', '| :fg-success:| ───▶︎ Contrôle présence disque : ' . ':/fg:');
        $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, true);
        if ($result != false) {
            $result_disk = true;
        } else {
            $result_disk = false;
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
        return $result_disk;
    }

    private static function createEq_disk_SP($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['diskName'] . ':/fg: ──');
        $icontemp = 'fas fa-thermometer-half icon_blue';
        $Type_box = config::byKey('TYPE_FREEBOX', 'Freebox_OS');
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, true);
        if ($result == 'auth_required') {
            $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, true);
        }
        if ($result != false) {
            $disk = Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *', null, null, null, 'system', true);
            foreach ($result['result'] as $disks) {
                if ($disks['temp'] != 0) {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ Température : ' . $disks['temp'] . '°C' . '- Disque [' . $disks['serial'] . '] - ' . $disks['id']);
                    $disk->AddCommand('Disque [' . $disks['serial'] . '] Temperature', $disks['id'] . '_temp', 'info', 'numeric', $templatecore_V4 . 'line', '°C', null, 1, 'default', 'default', 0, $icontemp, 0, '0', '100', $order++, 0, false, true, null, true, null, null, null, null, null, null, null, true);
                }
                if ($disks['serial'] != null) {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ Tourne : ' . $disks['spinning'] . '- Disque [' . $disks['serial'] . '] - ' . $disks['id']);
                    $disk->AddCommand('Disque [' . $disks['serial'] . '] Tourne', $disks['id'] . '_spinning', 'info', 'binary', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order++, '0', false, false, 'never', null, null, null, null, null, null, null, null, true);
                }
                foreach ($disks['partitions'] as $partition) {
                    $order2 = 200;
                    log::add('Freebox_OS', 'debug', '| ───▶︎ ID :' . $partition['id'] . ' : Disque [' . $disks['type'] . '] - ' . $disks['id'] . ' - Partitions : ' . $partition['label']);
                    $disk->AddCommand($partition['label'] . ' - ' . $disks['type'] . ' - ' . $partition['fstype'], $partition['id'], 'info', 'numeric', 'core::horizontal', '%', null, 1, 'default', 'default', 0, 'fas fa-hdd fa-2x', 0, '0', 100, $order2++, '0', false, false, 'never', null, true, '#value#*100', 2, null, null, null, null, true);
                }
            }
            if ($Type_box != 'fbxgw1r' && $Type_box != 'fbxgw2r') {
                $disk_raid = 'OK';
                log::add('Freebox_OS', 'debug', '| ───▶︎ BOX COMPATIBLE AVEC LES DISQUES RAID : ' . $Type_box . ' -' . $disk_raid);
                Free_CreateEq::createEq_disk_RAID($logicalinfo, $templatecore_V4, $order);
            } else {
                $disk_raid = 'KO';
                log::add('Freebox_OS', 'debug', '| ───▶︎ BOX NON COMPATIBLE AVEC LES DISQUES RAID : ' . $Type_box . ' -' . $disk_raid);
            }
        } else {
            log::add('Freebox_OS', 'debug', '| ───▶︎ AUCUN DISQUE - KO');
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_disk_RAID($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ Ajout des commandes spécifiques pour : ' . $logicalinfo['diskName'] . ' - RAID' . ':/fg:');

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
            log::add('Freebox_OS', 'debug', '| ───▶︎ AUCUN DISQUE - KO');
        }
    }

    private static function createEq_download($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['downloadsName'] . ':/fg: ──');
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

        $downloads = Freebox_OS::AddEqLogic($logicalinfo['downloadsName'], $logicalinfo['downloadsID'], 'multimedia', false, null, null, null, '5 */12 * * *', null, null, null, 'system', true);
        $downloads->AddCommand('Nb de tâche(s)', 'nb_tasks', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de tâche(s) active', 'nb_tasks_active', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask,  0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de tâche(s) en extraction', 'nb_tasks_extracting', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de tâche(s) en réparation', 'nb_tasks_repairing', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de tâche(s) en vérification', 'nb_tasks_checking', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de tâche(s) en attente', 'nb_tasks_queued', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask_queued, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de tâche(s) en erreur', 'nb_tasks_error', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask_error, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de tâche(s) stoppée(s)', 'nb_tasks_stopped', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask_error, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de tâche(s) terminée(s)', 'nb_tasks_done', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de flux RSS', 'nb_rss', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconRSSnb, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Nb de flux RSS Non Lu', 'nb_rss_items_unread', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconRSSread, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Etat connexion', 'conn_ready', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconconn_ready, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Etat Planning', 'throttling_is_scheduled', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconcalendar, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Téléchargement en cours', 'nb_tasks_downloading', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icondownload, 0, 'default', 'default', $order++, '0', $updateicon, true, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Vitesse réception', 'rx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default', $order++, '0', $updateicon, true, null, true, null, '#value# / 1000', '2', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $downloads->AddCommand('Vitesse émission', 'tx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true, null, '#value# / 1000', '2', null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $action = $downloads->AddCommand('Mode Téléchargement', 'mode', 'info', 'string', $Templatemode, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, true);
        $listValue = 'normal|Mode normal;slow|Mode lent;hibernate|Mode Stop;schedule|Mode Planning';
        $downloads->AddCommand('Choix Mode Téléchargement', 'mode_download', 'action', 'select', null, null, null, 1, $action, 'mode', 0, $iconDownloadsnormal, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, null, $listValue);
        $downloads->AddCommand('Start Téléchargement', 'start_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsOn, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, false);
        $downloads->AddCommand('Stop Téléchargement', 'stop_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsOff, 0, 'default', 'default',  $order++, '0', $updateicon, false, null, false);

        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_FreePlug($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['freeplugName'] . ':/fg: ──');
        $updateicon = false;
        $Free_API = new Free_API();
        $iconReboot = 'fas fa-sync icon_red';

        $result = $Free_API->universal_get('universalAPI', null, null, 'freeplug', true, true, false);
        if (isset($result['result'])) {
            foreach ($result['result'] as $freeplugs) {
                foreach ($freeplugs['members'] as $freeplug) {
                    log::add('Freebox_OS', 'debug', '| ───▶︎ Création Freeplug : ' . $freeplug['id']);
                    $FreePlug = Freebox_OS::AddEqLogic($logicalinfo['freeplugName'] . ' - ' . $freeplug['id'], $freeplug['id'], 'default', true, $logicalinfo['freeplugID'], null, null, '*/5 * * * *', null, null, null, 'system');
                    $FreePlug->AddCommand('Rôle', 'net_role', 'info', 'string',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', 10, '0', $updateicon, false, false, true);
                    $FreePlug->AddCommand('Redémarrer', 'reset', 'action', 'other',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconReboot, 0, 'default', 'default',  1, '0', true, false, null, true);
                    //$FreePlug->AddCommand('Débit TX', 'tx_rate', 'info', 'numeric',  $templatecore_V4 . 'line', 'Mb/s', 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', 12, '0', $updateicon, false, false, true);
                    //$FreePlug->AddCommand('Débit RX', 'rx_rate', 'info', 'numeric',  $templatecore_V4 . 'line', 'Mb/s', 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', 12, '0', $updateicon, false, false, true);
                }
            }
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_LCD($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['LCDName'] . ':/fg: ──');
        $LCD = Freebox_OS::AddEqLogic($logicalinfo['LCDName'], $logicalinfo['LCDID'], 'default', false, null, null, null, '5 */12 * * *', null, null, null, 'system', true);
        $iconbrightness = 'fas fa-adjust icon_green';
        $iconorientation = 'fas fa-map-signs icon_green';
        $updateicon = false;
        $StatusLCD = $LCD->AddCommand('Etat Lumininosité écran LCD', 'brightness', "info", 'numeric', null, '%', null, 0, '', '', '', $iconbrightness, 0, '0', 100, $order++, 2, $updateicon, true, false, true);
        $LCD->AddCommand('Lumininosité écran LCD', 'brightness', 'action', 'slider', null, '%', null, 1, $StatusLCD, 'default', 0, $iconbrightness, 0, '0', 100, $order++, '0', $updateicon, false, null, true, null, 'floor(#value#)');
        // Affichage Orientation
        $StatusLCD = $LCD->AddCommand('Etat Orientation', 'orientation', "info", 'string', null, null, null, 0, '', '', '', $iconorientation, 0, '0', 100, $order++, 2, $updateicon, true, false, true);
        $listValue = '0|Horizontal;90|90 degrés;180|180 degrés;270|270 degrés';
        $LCD->AddCommand('Orientation', 'orientation', 'action', 'select', null, null, null, 1, $StatusLCD, 'default', 0, $iconorientation, 0, '0', 100, $order++, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, null, $listValue);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_parental($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['parentalName'] . ':/fg: ──');
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('parentalprofile', null, null, true, true, true, false);
        if (isset($result['result'])) {
            $result =  $result['result'];
            foreach ($result  as $Equipement) {
                log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ Début de création des commandes spécifiques pour le contrôle parental' . ':/fg:');
                $Templateparent = 'Freebox_OS::Parental';
                $iconparent_allowed = 'fas fa-user-check icon_green';
                $iconparent_denied = 'fas fa-user-lock icon_red';
                $iconparent_temp = 'fas fa-user-clock icon_blue';

                $category = 'default';
                $Equipement['name'] = preg_replace('/\'+/', ' ', $Equipement['name']); // Suppression '
                log::add('Freebox_OS', 'debug', '| ───▶︎ Nom du controle parental : ' . $Equipement['name']);
                $parental = Freebox_OS::AddEqLogic($Equipement['name'], 'parental_' . $Equipement['id'], $category, true, 'parental', null, $Equipement['id'], '*/5 * * * *', null, null, null, 'parental_controls');
                $StatusParental = $parental->AddCommand('Etat', $Equipement['id'], "info", 'string', $Templateparent, null, null, 1, '', '', '', '', 0, 'default', 'default', $order++, 1, false, true, null, true, null, null, null, null, null, null, null, true);
                $parental->AddCommand('Autoriser', 'allowed', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconparent_allowed, 0, 'default', 'default', $order++, '0', false, false, null, true);
                $parental->AddCommand('Bloquer', 'denied', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconparent_denied, 0, 'default', 'default', $order++, '0', false, false, null, true);
                $listValue = '1800|0h30;3600|1h00;5400|1h30;7200|2h00;10800|3h00;14400|4h00';
                $parental->AddCommand('Autoriser-Bloquer Temporairement', 'tempDenied', 'action', 'select', null, null, null, 1, $StatusParental, 'parentalStatus', '', $iconparent_temp, 0, 'default', 'default', $order++, '0', false, false, '', true, null, null, null, null, null, null, null, null, $listValue);
            }
        } else {
            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎  AUCUN CONTROLE PARENTAL :/fg:──');
        }

        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_phone($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['phoneName'] . ':/fg: ──');
        $iconmissed = 'icon techno-phone1 icon_red';
        $iconaccepted = 'icon techno-phone3 icon_blue';
        $iconoutgoing = 'icon techno-phone2 icon_green';
        $iconDell_call = 'fas fa-magic icon_red';
        $iconRead_call = 'fab fa-readme icon_blue';
        $updateicon = false;

        $phone = Freebox_OS::AddEqLogic($logicalinfo['phoneName'], $logicalinfo['phoneID'], 'default', false, null, null, null, '*/30 * * * *', null, null, null, 'system', true);
        $phone->AddCommand('Nb Manqués', 'nbmissed', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 1, 'default', 'default', 0, $iconmissed, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand('Liste Manqués', 'listmissed', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmissed, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand('Nb Reçus', 'nbaccepted', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 1, 'default', 'default', 0, $iconaccepted, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand('Liste Reçus', 'listaccepted', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconaccepted, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand('Nb Emis', 'nboutgoing', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 1, 'default', 'default', 0, $iconoutgoing, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand('Liste Emis', 'listoutgoing', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconoutgoing, 1, 'default', 'default',  $order++, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand('Vider le journal d appels', 'phone_dell_call', 'action', 'other', 'default', null, null,  1, 'default', 'default', 0, $iconDell_call, 1, 'default', 'default', $order++, '0', $updateicon, false, null, true);
        $phone->AddCommand('Tout marquer comme lu', 'phone_read_call', 'action', 'other', 'default', null, null,  1, 'default', 'default', 0, $iconRead_call, 0, 'default', 'default', $order++, '0', $updateicon, false, null, true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_management($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['managementName'] . ':/fg: ──');
        $icon_dhcp = 'fas fa-network-wired icon_blue';
        $icon_host_type = 'fas fa-laptop icon_green';
        $icon_method = 'fas fa-list icon_orange';
        $icon_add_del_ip = 'fas fa-network-wired icon_blue';
        $icon_primary_name = 'fas fa-book icon_blue';
        $icon_comment = 'far fa-comment icon_orange';
        $updateWidget = false;
        // Pour test Visibilité
        $_IsVisible = 0;

        $EqLogic = Freebox_OS::AddEqLogic($logicalinfo['managementName'], $logicalinfo['managementID'], 'default', false, null, null, null, '0 0 1 1 *', null, null, null, 'system', true, null);
        // Type de phériphérique
        $host_type_list = "other|Autre;ip_camera|Caméra IP;vg_console|Console de jeux;freebox_crystal|Freebox Crystal;freebox_delta|Freebox Delta;freebox_hd|Freebox HD;freebox_mini|Freebox Mini;freebox_one|Freebox One;freebox_player|Freebox Player;freebox_pop|Freebox Pop;freebox_wifi|Freebox Wi-Fi Pop;printer|Imprimante;nas|NAS;workstation|Ordinateur Fixe;laptop|Ordinateur Portable;multimedia_device|Périphérique multimédia;networking_device|Périphérique réseau;smartphone|Smartphone;tablet|Tablette;ip_phone|Téléphone IP;television|Télévision;car|Véhicule connecté";
        $host_type = $EqLogic->AddCommand('Type de périphérique choisi', 'host_type_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_host_type, 0, 'default', 'default', $order, '0', false, true, null, true);
        $EqLogic->AddCommand('Sélection Type de périphérique', 'host_type', 'action', 'select', null, null, null, $_IsVisible, $host_type, 'default', 0, $icon_host_type, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, $host_type_list, null, null);

        // Méthode de modification
        $method_list = 'POST|Ajouter IP fixe;DELETE|Supprimer IP Fixe;PUT|Modifier IP Equipement;DEVICE|Modifier le type de Périphérique;ADD_blacklist|Ajouter Liste Noire;ADD_whitelist|Ajouter Liste Blanche;DEL_blacklist|Supprimer Liste Noire;DEL_whitelist|Supprimer Liste Blanche;PUT_blacklist|Modifier Liste Noire;PUT_whitelist|Modifier Liste Blanche;POST_WOL|Wake on LAN';
        $method = $EqLogic->AddCommand('Choix modification Appareil', 'method_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_method, 0, 'default', 'default', $order++, '0', false, true, null, true);
        $EqLogic->AddCommand('Sélection modification Appareil', 'method', 'action', 'select', null, null, null, $_IsVisible, $method, 'default', 0, $icon_method, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, $method_list, null, null);

        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => 'Adresse IP',

        );

        $add_del_ip = $EqLogic->AddCommand('IP choisi', 'add_del_ip_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_add_del_ip, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand('Choix IP', 'add_del_ip', 'action', 'message', 'default', null, null, $_IsVisible, $add_del_ip, 'default', 0, $icon_add_del_ip, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, null, null, null, null, $config_message);

        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => 'Nom Appareil',

        );
        $primary_name = $EqLogic->AddCommand('Nom choisi', 'primary_name_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_primary_name, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand('Nom Appareil', 'primary_name', 'action', 'message', 'default', null, null, $_IsVisible, $primary_name, 'default', 0, $icon_primary_name, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, null, null, null, null, $config_message);

        //Commentaires
        $config_message = array(
            'title_disable' => 1,
            'message_placeholder' => 'Commentaire ou Mot de Passe (pour la fonction Wake on Lan)',

        );
        $primary_name = $EqLogic->AddCommand('Commentaire choisi', 'comment_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_comment, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null);
        $EqLogic->AddCommand('Commentaire', 'comment', 'action', 'message', 'default', null, null, $_IsVisible, $primary_name, 'default', 0, $icon_comment, 0, 'default', 'default', $order++, '0', false, true, null, true, null, null, null, null, null, null, null, null, null, null, null, null, $config_message);

        // Commande Action
        $EqLogic->AddCommand('Modifier Appareil', 'start', 'action', 'other',  'default', null, null, 0, 'default', 'default', 0, $icon_dhcp, 0, 'default', 'default',  $order++, '0', $updateWidget, false, null, true, null, null, null, null, null);

        log::add('Freebox_OS', 'debug', '| ───▶︎ La commande "Appareil connecté choisi" sera créée par l\'équipement : ' . $logicalinfo['networkName'] . ' et/ou ' . $logicalinfo['networkwifiguestName']);
        log::add('Freebox_OS', 'debug', '| ───▶︎ La commande "Sélection appareil connecté" sera créée par l\'équipement : ' . $logicalinfo['networkName'] . ' et/ou ' . $logicalinfo['networkwifiguestName']);
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
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $_networkname . ':/fg: ──');
        $EqLogic = Freebox_OS::AddEqLogic($_networkname, $_networkID, 'default', false, null, null, null, '*/5 * * * *');
        // ───▶︎ $network->AddCommand('Redirections des ports', 'redir', 'action', 'message',  'default', null, null, 0, 'default', 'default', 0, $icon_redir, 0, 'default', 'default',  -33, '0', true, false, null, true, null, null, null, null, null, 'redir?lan_ip=#lan_ip#&enable_lan=#enable_lan#&src_ip=#src_ip#&ip_proto=#ip_proto#&wan_port_start=#wan_port_start#&wan_port_end=#wan_port_end#&lan_port=#lan_port#&comment=#comment#');
        $EqLogic->AddCommand('Rechercher les nouveaux appareils', 'search', 'action', 'other',  $templatecore_V4 . 'line', null, null, true, 'default', 'default', 0, $icon_search, true, 'default', 'default',  $order++, '0', $updateWidget, false, null, true, null, null, null, null, null, null, null, true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_netshare($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['netshareName'] . ':/fg: ──');
        $order = 1;
        $color_on = ' icon_green';
        $color_off = ' icon_red';
        $updateicon = false;

        $netshare = Freebox_OS::AddEqLogic($logicalinfo['netshareName'], $logicalinfo['netshareID'], 'multimedia', false, null, null, null, '5 */12 * * *', null, null, null, 'system', true);
        $boucle_num = 1; // 1 = Partage Imprimante - 2 = Partage de fichiers Windows - 3 = Partage Fichier Mac - 4 = Partage Fichier FTP
        $order = 1;
        while ($boucle_num <= 5) {
            if ($boucle_num == 1) {
                $name = 'Partage Imprimante';
                $Logical_ID = 'print_share_enabled';
                $icon = 'fas fa-print';
                $template = 'Freebox_OS::Partage Imprimante';
            } else if ($boucle_num == 2) {
                $name = 'Partage de fichiers Windows';
                $Logical_ID = 'file_share_enabled';
                $icon = 'fas fa-share-alt-square';
                $template = 'Freebox_OS::Partage Fichier Windows';
            } else if ($boucle_num == 3) {
                $name = 'Partage de fichiers Mac';
                $Logical_ID = 'mac_share_enabled';
                $icon = 'fas fa-share-alt';
                $template = 'Freebox_OS::Partage Fichier Mac';
            } else if ($boucle_num == 4) {
                $name = 'Partage FTP';
                $Logical_ID = 'FTP_enabled';
                $icon = 'fas fa-handshake';
                $template = 'Freebox_OS::Partage FTP';
            } else if ($boucle_num == 5) {
                $name = 'SMBv2';
                $Logical_ID = 'smbv2_enabled';
                $icon = 'fab fa-creative-commons-share';
                $template = 'Freebox_OS::Activer SMBv2';
            }
            log::add('Freebox_OS', 'debug', '| ───▶︎ Boucle pour Création des commandes : ' . $name);
            $netshareSTATUS = $netshare->AddCommand($name, $Logical_ID, "info", 'binary', null, null, 'LIGHT_STATE', 0, '', '', '', $icon, 0, 'default', 'default', '0', $order, $updateicon, true);
            $netshare->AddCommand('Activer ' . $name, $Logical_ID . 'On', 'action', 'other', $template, null, 'LIGHT_ON', 1, $netshareSTATUS, '', 0, $icon . $color_on, 0, 'default', 'default', $order++, '0', $updateicon, false);
            $netshare->AddCommand('Désactiver ' . $name, $Logical_ID  . 'Off', 'action', 'other', $template, null, 'LIGHT_OFF', 1, $netshareSTATUS, '', 0, $icon . $color_off, 0, 'default', 'default', $order++, '0', $updateicon, false);
            $boucle_num++;
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_network_interface($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['networkName'] . ':/fg: ──');
        $Free_API = new Free_API();
        $Free_API->universal_get('universalAPI', null, null, 'lan/browser/interfaces', true, true, true);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_network_SP($logicalinfo, $templatecore_V4, $order = 0, $_network = 'LAN', $IsVisible = true)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $_network . ':/fg: ──');
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
        log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ Ajout des commandes spécifiques pour l\'équipement : ' . $_networkname . ':/fg:');
        $Free_API = new Free_API();
        $EqLogic = Freebox_OS::EqLogic_ID($_networkname, $_networkID);
        // ───▶︎ $network->AddCommand('Redirections des ports', 'redir', 'action', 'message',  'default', null, null, 0, 'default', 'default', 0, $icon_redir, 0, 'default', 'default',  -33, '0', true, false, null, true, null, null, null, null, null, 'redir?lan_ip=#lan_ip#&enable_lan=#enable_lan#&src_ip=#src_ip#&ip_proto=#ip_proto#&wan_port_start=#wan_port_start#&wan_port_end=#wan_port_end#&lan_port=#lan_port#&comment=#comment#');
        //$EqLogic->AddCommand('Ajouter supprimer IP Fixe', 'add_del_mac', 'action', 'message',  'default', null, null, 0, 'default', 'default', 0, $icon_dhcp, 0, 'default', 'default',  -31, '0', $updateWidget, false, null, true, null, null, null, null, null, 'add_del_dhcp?mac_address=#mac#&ip=#ip#&comment=#comment#&name=#name#&function=#function#&type=#type#');
        $EqLogic->AddCommand('Rechercher les nouveaux appareils', 'search', 'action', 'other',  $templatecore_V4 . 'line', null, null, true, 'default', 'default', 0, $icon_search, true, 'default', 'default',  -30, '0', $updateWidget, false, null, true, null, null, null, null, null, null, null, true);
        //$EqLogic->AddCommand('Wake on LAN', 'WakeonLAN', 'action', 'message',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, $icon_wol, 0, 'default', 'default',  -32, '0', $updateWidget, false, null, true, null, null, null, null, null, 'wol?mac_address=#mac#&password=#password#');
        //$result = $Free_API->universal_get('network', null, null, 'lan/browser/' . $_networkinterface);
        $result = $Free_API->universal_get('universalAPI', null, null, 'lan/browser/' . $_networkinterface, true, true, true);
        $order_count_active = 100;
        $order_count_noactive = 400;
        $network_list = null;
        $active_list = null;
        $noactive_list = null;

        if (isset($result['result'])) {
            if ($EqLogic->getConfiguration('UpdateName') == 1) {
                $updatename_disable = 1;
                log::add('Freebox_OS', 'debug', '| ───▶︎ Mise à jour des noms : non actif - ' . $updatename_disable);
            } else {
                $updatename_disable = 0;
                log::add('Freebox_OS', 'debug', '| ───▶︎ Mise à jour des noms : actif - ' . $updatename_disable);
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
            log::add('Freebox_OS', 'debug', '| ───▶︎ Appareil(s) connecté(s) ' . $active_list);
            log::add('Freebox_OS', 'debug', '| ───▶︎ Appareil(s) non connecté(s) ' . $noactive_list);
            $_IsVisible = 0;
            //$_networkname = $logicalinfo['managementName'];
            log::add('Freebox_OS', 'debug', '| :fg-success:───▶︎ Ajout des commandes spécifiques pour l\'équipement : ' . $logicalinfo['managementName'] . ':/fg:');
            $EqLogic = Freebox_OS::AddEqLogic($logicalinfo['managementName'], $logicalinfo['managementID'], 'default', false, null, null, null, '0 0 1 1 *', null, null, null, 'system', true, null);
            $host_type = $EqLogic->AddCommand('Appareil connecté choisi', 'host_info', 'info', 'string', 'default', null, null, $_IsVisible, 'default', 'default', 0, $icon_network, 0, 'default', 'default', 14, '0', false, true, null, true);
            $EqLogic->AddCommand('Sélection appareil connecté', 'host', 'action', 'select', null, null, null, $_IsVisible, $host_type, 'default', 0, $icon_network, 0, 'default', 'default', 15, '0', false, true, null, true, null, null, null, null, null, null, null, null, $network_list, null, null);
            $EqLogic->refreshWidget();
        } else {
            log::add('Freebox_OS', 'debug', '| ───▶︎ PAS D\'APPAREIL TROUVE');
        }
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_notification($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '    . $logicalinfo['notificationName'] . ':/fg: ──');
        $Free_API = new Free_API();
        $Free_API->universal_get('universalAPI', null, null, '/notif/targets', true, true, false);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_system_full($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '    .  $logicalinfo['systemName'] . ':/fg: ──');
        $system = Freebox_OS::AddEqLogic($logicalinfo['systemName'], $logicalinfo['systemID'], 'default', false, null, null, null, '*/30 * * * *', null, null, null, 'system', true);
        $order = 10;
        Free_CreateEq::createEq_system($logicalinfo, $templatecore_V4, $order, $system);
        $order = 1;
        Free_CreateEq::createEq_system_lan($logicalinfo, $templatecore_V4, $order, $system);
        $order = 20;
        Free_CreateEq::createEq_system_SP($logicalinfo, $templatecore_V4, $order, $system);
        $order = 49;
        Free_CreateEq::createEq_system_SP_lang($logicalinfo, $templatecore_V4, $order, $system);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
    private static function createEq_system($logicalinfo, $templatecore_V4, $order = 10, $system)
    {
        log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ Ajout des commandes spécifiques : ' . $logicalinfo['systemName'] . ' - Standards' . ':/fg:');
        $iconReboot = 'fas fa-sync icon_red';
        $updateicon = false;

        $system->AddCommand('Modele de Freebox', 'model_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'model_info',  0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $system->AddCommand('Freebox firmware version', 'firmware_version', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $system->AddCommand('Mac', 'mac', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  2, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $system->AddCommand('Allumée depuis', 'uptime', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $system->AddCommand('Board name', 'board_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $system->AddCommand('Serial', 'serial', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $system->AddCommand('Type de Freebox', 'pretty_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'model_info', 0, null, 0, 'default', 'default',   $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $system->AddCommand('Type de Wifi', 'wifi_type', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'model_info',  0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $order = 130;
        $system->AddCommand('Reboot', 'reboot', 'action', 'other',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconReboot, 0, 'default', 'default',   $order++, '0', true, false, null, true);
        //$system->AddCommand('Redirection de ports', 'port_forwarding', 'action', 'message', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 'default', 6, '0', $updateicon);
    }
    private static function createEq_system_lan($logicalinfo, $templatecore_V4, $order = 1, $system)
    {
        log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ Ajout des commandes spécifiques pour l\'équipement : ' .  $logicalinfo['systemName'] . ' - LAN' . ':/fg:');
        $updateicon = false;

        $system->AddCommand('Nom Freebox', 'name', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $system->AddCommand('Mode Freebox', 'mode', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        $system->AddCommand('Ip', 'ip', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);

        Free_Refresh::RefreshInformation($logicalinfo['systemID']);
    }

    private static function createEq_system_SP($logicalinfo, $templatecore_V4, $order = 20, $system)
    {
        log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ Ajout des commandes spécifiques pour l\'équipement : ' . $logicalinfo['systemName'] . ' - Capteurs' . ':/fg:');
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
            if ($result_SP != false) {
                log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ Boucle pour Update : ' . $boucle_update . ':/fg:');

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
                            $_name = 'Slot ' . $Equipement['slot'] . ' - ' . $Equipement['type'];
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
                                    $_4G = $system->AddCommand('Etat 4G ', '4GStatut', "info", 'binary', null . 'line', null, null, 0, '', '', '', '', 1, 'default', 'default', $order++, '0', false, 'never', null, true);
                                    $system->AddCommand('4G On', '4GOn', 'action', 'other', $Template4G, null, 'ENERGY_ON', 1, $_4G, '4GStatut', 0, $icon4Gon, 1, 'default', 'default', $order++, '0', false, false, null, true);
                                    $system->AddCommand('4G Off', '4GOff', 'action', 'other', $Template4G, null, 'ENERGY_OFF', 1, $_4G, '4GStatut', 0, $icon4Goff, 0, 'default', 'default', $order++, '0', false, false, null, true);
                                    $system->AddCommand('Etat du réseau 4G', 'state_lte', 'info', 'string', 'default', null, 'default', 1, 'default', 'default', 0, 'default', 0, 'default', 'default', $order++, '0', false, false, null, true);
                                    $system->AddCommand('Etat de la radio 4G', 'associated_lte', 'info', 'binary', 'default', null, 'default', 1, 'default', 'default', 0, 'default', 0, 'default', 'default', $order++, '0', false, false, null, true);
                                }
                            }
                            $order++;
                        }
                    }
                }
            } else {
                log::add('Freebox_OS', 'debug', '|:fg-warning: ───▶︎ Pas de commande spécifique : ' . $logicalinfo['systemName'] . ' pour ' . $boucle_update . ':/fg:');
                break;
            }
            $boucle_num++;
        }
    }
    private static function createEq_system_SP_lang($logicalinfo, $templatecore_V4, $order = 49, $system)
    {
        $Free_API = new Free_API();
        $iconLang = 'fas fa-language icon_blue';
        $updateicon = false;
        log::add('Freebox_OS', 'debug', '|:fg-success:───▶︎ Ajout des commandes spécifiques pour l\'équipement : ' .  $logicalinfo['systemName'] . ' - langues' . ':/fg:');
        $system->AddCommand('langue Box', 'lang', 'info', 'string', 'default', null, 'default', 1, 'default', '4GStatut', 0, $iconLang, 1, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
    }
    private static function createEq_VM($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:Début de création des commandes pour : '  . $logicalinfo['VMName'] . ':/fg: ──');
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
                $_VM = Freebox_OS::AddEqLogic($VM_name, 'VM_' . $Equipement['id'], 'multimedia', true, 'VM', null, $Equipement['id'], '*/5 * * * *', null, null, null, 'system', true);
                $_VM->AddCommand('Status', 'status', 'info', 'string', $TemplateVM, null, 'default', 1, 'default', 'default', 0, $VMstatus, 0, 'default', 'default', $order++, '0', $updateicon, false, false, true, null, null, null, null, null, null, null, true);
                $_VM->AddCommand('Start', 'start', 'action', 'other', 'default', null, 'default', 1, 'default', 'default', 0, $VMOn, 0, 'default', 'default', $order++, '0', $updateicon, false);
                $_VM->AddCommand('Stop', 'stop', 'action', 'other', 'default', null, 'default', 1, 'default', 'default', 0, $VMOff, 0, 'default', 'default', $order++, '0', $updateicon, false);
                $_VM->AddCommand('Redémarrer', 'restart', 'action', 'other', 'default', null, 'default', 1, 'default', 'default', 0, $VMRestart, 0, 'default', 'default', $order++, '0', $updateicon, false, null, null, null, null, null, null, null, null, null, true);
                $order = 10;
                $_VM->AddCommand('CPU(s)', 'vcpus', 'info', 'numeric',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, $VMCPU, 0, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Mac', 'mac', 'info', 'string',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Mémoire', 'memory', 'info', 'numeric',  $templatecore_V4 . 'line', 'Mo', 'default', 0, 'default', 'default', 0, $VMmemory, 0, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand('USB', 'bind_usb_ports', 'info', 'string',  null, null, 'default', 1, 'default', 'default', 0, $VMUSB, 1, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Ecran virtuel', 'enable_screen', 'info', 'binary',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, $VMscreen, '0', 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Nom', 'name', 'info', 'string',  null, null, 'default', 0, 'default', 'default', 0, 'default', 1, 'default', 'default', $order++, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Type de disque', 'disk_type', 'info', 'string',  null, null, 'default', 0, 'default', 'default', 0, $VMdisk, 1, 'default', 'default', $order++, '0', $updateicon, false, false, true);
            }
        } else {
            log::add('Freebox_OS', 'debug', '|:fg-warning: ──────▶︎ PAS DE ' . $logicalinfo['VMName'] . ' SUR VOTRE BOX ' . ':/fg:');
        }
    }

    private static function createEq_wifi($logicalinfo, $templatecore_V4, $order = 0)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : '  . $logicalinfo['wifiName'] . ':/fg: ──');
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

        $Wifi = Freebox_OS::AddEqLogic($logicalinfo['wifiName'], $logicalinfo['wifiID'], 'default', false, null, null, null, '*/5 * * * *', null, null, null, 'system', true, null);
        $StatusWifi = $Wifi->AddCommand('Etat Wifi', 'wifiStatut', "info", 'binary', null, null, 'ENERGY_STATE', 0, '', '', '', '', 0, 'default', 'default', 1, 1, $updateicon, true);
        $Wifi->AddCommand('Wifi On', 'wifiOn', 'action', 'other', $TemplateWifiOnOFF, null, 'ENERGY_ON', 1, $StatusWifi, 'wifiStatut', 0, $iconWifiOn, 0, 'default', 'default', 10, '0', $updateicon, false);
        $Wifi->AddCommand('Wifi Off', 'wifiOff', 'action', 'other', $TemplateWifiOnOFF, null, 'ENERGY_OFF', 1, $StatusWifi, 'wifiStatut', 0, $iconWifiOff, 0, 'default', 'default', 11, '0', $updateicon, false);
        // Planification Wifi
        $PlanningWifi = $Wifi->AddCommand('Etat Planning', 'wifiPlanning', "info", 'binary', null, null, 'LIGHT_STATE', 0, '', '', '', '', 0, 'default', 'default', '0', 2, $updateicon, true);
        $Wifi->AddCommand('Wifi Planning On', 'wifiPlanningOn', 'action', 'other', $TemplateWifiPlanningOnOFF, null, 'LIGHT_ON', 1, $PlanningWifi, 'wifiPlanning', 0, $iconWifiPlanningOn, 0, 'default', 'default', 12, '0', $updateicon, false);
        $Wifi->AddCommand('Wifi Planning Off', 'wifiPlanningOff', 'action', 'other', $TemplateWifiPlanningOnOFF, null, 'LIGHT_OFF', 1, $PlanningWifi, 'wifiPlanning', 0, $iconWifiPlanningOff, 0, 'default', 'default', 13, '0', $updateicon, false);
        // Wifi WPS
        $WifiWPS = $Wifi->AddCommand('Etat WPS', 'wifiWPS', "info", 'binary', null, null, 'LIGHT_STATE', 0, '', '', '', '', 0, 'default', 'default', '0', 3, $updateicon, true);
        //$Wifi->AddCommand('Wifi WPS On', 'wifiWPSOn', 'action', 'other', $TemplateWifiWPSOnOFF, null, 'LIGHT_ON', 1, $WifiWPS, 'wifiWPS', 0, $iconWifiWPSOn, 0, 'default', 'default', 14, '0', $updateicon, false);
        //$Wifi->AddCommand('Wifi WPS Off', 'wifiWPSOff', 'action', 'other', $TemplateWifiWPSOnOFF, null, 'LIGHT_OFF', 1, $WifiWPS, 'wifiWPS', 0, $iconWifiWPSOff, 0, 'default', 'default', 15, '0', $updateicon, false);
        $order = 49;
        Free_CreateEq::createEq_wifi_ap($logicalinfo, $templatecore_V4, $order, $Wifi);
        $order = 29;
        Free_CreateEq::createEq_wifi_bss($logicalinfo, $templatecore_V4, $order, $Wifi);
        $order = 39;
        Free_CreateEq::createEq_mac_filter($logicalinfo, $templatecore_V4, $order, $Wifi);
        $order = 50;
        Free_CreateEq::createEq_wifi_Standby($logicalinfo, $templatecore_V4, $order, $Wifi);
        $order = 60;
        Free_CreateEq::createEq_wifi_Eco($logicalinfo, $templatecore_V4, $order, $Wifi);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }

    private static function createEq_wifi_ap($logicalinfo, $templatecore_V4, $order = 49, $Wifi)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:Début de création des commandes spécifiques pour : '  . $logicalinfo['wifiName'] . ' / ' . $logicalinfo['wifiAPName'] . ':/fg: ──');
        $iconWifi = 'fas fa-wifi icon_blue';
        $TemplateWifi = 'Freebox_OS::Wifi Statut carte';
        $updateicon = false;;
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/ap', true, true, true);

        $nb_card = count($result['result']);
        if ($result != false) {
            for ($k = 0; $k < $nb_card; $k++) {
                log::add('Freebox_OS', 'debug', '| ──────▶︎ Nom de la commande : ' . 'Etat Wifi ' . $result['result'][$k]['name'] . ' - Id : ' . $result['result'][$k]['id'] . ' - Status : ' . $result['result'][$k]['status']['state']);
                $Wifi->AddCommand('Etat Wifi ' . $result['result'][$k]['name'], $result['result'][$k]['id'], 'info', 'string', $TemplateWifi, null, null, 1, null, null, 0, $iconWifi, false, 'default', 'default', $order++, '0', $updateicon, false, false, true);
            }
        }
    }

    private static function createEq_wifi_Eco($logicalinfo, $templatecore_V4, $order = 49, $Wifi)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:Début de création des commandes spécifiques pour : '  . $logicalinfo['wifiName'] . ' / ' . $logicalinfo['wifiECOName'] . ':/fg: ──');
        $iconWifi = 'fas fa-wifi icon_blue';
        $updateicon = false;;
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('system', null, null, null, true, true, null);

        if (isset($result['model_info']['has_eco_wifi'])) {
            $Wifi->AddCommand('Mode Éco-WiFi', 'has_eco_wifi', 'info', 'binary',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default',  0, $iconWifi, 0, 'default', 'default',  $order++, '0', $updateicon, true, null, null, null, null, null, null, null, null, null, true, null, null, null, null, null, null, null, null, null, null);
        } else {
            log::add('Freebox_OS', 'debug', '| ──────▶︎ Pas de mode Eco non supporté');
        }
    }

    private static function createEq_wifi_bss($logicalinfo, $templatecore_V4, $order = 29, $Wifi)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:Début de création des commandes spécifiques pour : '  . $logicalinfo['wifiName'] . ' / ' . $logicalinfo['wifiWPSName'] . ':/fg: ──');
        $iconWifiSessionWPSOn = 'fas fa-link icon_orange';
        $iconWifiSessionWPSOff = 'fas fa-link icon_red';
        $updateicon = false;

        $Wifi->AddCommand('Wifi Session WPS (toutes les sessions) Off', 'wifiSessionWPSOff', 'action', 'other', null, null, 'LIGHT_OFF', 1, null, null, 0, $iconWifiSessionWPSOff, true, 'default', 'default', $order++, '0', $updateicon, false, false, true);
        $Free_API = new Free_API();
        //$result = $Free_API->universal_get('wifi', null, null, 'bss');
        $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/bss', true, true, true);
        if ($result != false) {
            foreach ($result['result'] as $wifibss) {
                if ($wifibss['config']['wps_enabled'] != true) continue;
                if ($wifibss['config']['use_default_config'] == true) {
                    $WPSname = 'Wifi Session WPS (' . $wifibss['shared_bss_params']['ssid'] . ') On';
                } else {
                    $WPSname = 'Wifi Session WPS (' . $wifibss['config']['ssid'] . ') On';
                }
                $Wifi->AddCommand($WPSname, $wifibss['id'], 'action', 'other', null, null, 'LIGHT_ON', 1, null, null, 0, $iconWifiSessionWPSOn, true, 'default', 'default', $order++, '0', $updateicon, false, false, true, null, null, null, null, null, null, null, true);
                if ($wifibss['config']['use_default_config'] == true) {
                    log::add('Freebox_OS', 'debug', '| ──────▶︎ Configuration Wifi commune pour l\'ensemble des cartes');
                    break;
                } else {
                    //$order++;
                }
            }
        }
    }
    private static function createEq_wifi_Standby($logicalinfo, $templatecore_V4, $order = 29, $Wifi)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:Début de création des commandes spécifiques pour : ' . $logicalinfo['wifistandbyName'] . ':/fg: ──');
        $iconWifiSessionWPSOn = 'fas fa-link icon_orange';
        $iconWifiSessionWPSOff = 'fas fa-link icon_red';
        $updateicon = false;

        //$Wifi->AddCommand('Wifi Session WPS (toutes les sessions) Off', 'wifiSessionWPSOff', 'action', 'other', null, null, 'LIGHT_OFF', 1, null, null, 0, $iconWifiSessionWPSOff, true, 'default', 'default', $order++, '0', $updateicon, false, false, true);
        $Free_API = new Free_API();
        //$result = $Free_API->universal_get('wifi', null, null, 'bss');
        $result = $Free_API->universal_get('universalAPI', null, null, 'standby/status', true, true, true);
        $Wifi->AddCommand('Mode de veille', 'planning_mode', 'info', 'string', 'default', null, 'default', 1, 'default', 'default', 0, 'default', 0, 'default', 'default', $order++, '0', $updateicon, false, false, true, null, null, null, null, null, null, null, true);
        $listValue = 'normal|Mode normal;slow|Mode lent';
        /* if ($result != false) {
            foreach ($result['result'] as $wifibss) {
                if ($wifibss['config']['wps_enabled'] != true) continue;
                if ($wifibss['config']['use_default_config'] == true) {
                    $WPSname = 'Wifi Session WPS (' . $wifibss['shared_bss_params']['ssid'] . ') On';
                } else {
                    $WPSname = 'Wifi Session WPS (' . $wifibss['config']['ssid'] . ') On';
                }
                $Wifi->AddCommand($WPSname, $wifibss['id'], 'action', 'other', null, null, 'LIGHT_ON', 1, null, null, 0, $iconWifiSessionWPSOn, true, 'default', 'default', $order++, '0', $updateicon, false, false, true, null, null, null, null, null, null, null, true);
                if ($wifibss['config']['use_default_config'] == true) {
                    log::add('Freebox_OS', 'debug', '| ──────▶︎ Configuration Wifi commune pour l\'ensemble des cartes');
                    break;
                } else {
                    //$order++;
                }
            }
        }*/
    }

    private static function createEq_mac_filter($logicalinfo, $templatecore_V4, $order = 39, $EqLogic)
    {
        log::add('Freebox_OS', 'debug', '| ──────▶︎ :fg-success:Début de création des commandes pour : ' . $logicalinfo['wifimmac_filter'] . ':/fg: ──');
        $Templatemac = 'Freebox_OS::Filtrage Adresse Mac';
        $iconmac_filter_state = 'fas fa-wifi icon_blue';
        $iconmac_list_white = 'fas fa-list-alt';
        $iconmac_list_black = 'far fa-list-alt';
        $updateWidget = false;
        // Pour test Visibilité
        $_IsVisible = 0;

        //$Statutmac = $EqLogic->AddCommand('Etat Mode de filtrage', 'wifimac_filter_state', "info", 'string', $Templatemac, null, null, 1, null, null, null, null, 1, 'default', 'default', $order++, 1, false, true, null, true);
        //$listValue = 'disabled|Désactiver;blacklist|Liste Noire;whitelist|Liste Blanche';
        //$EqLogic->AddCommand('Mode de filtrage', 'mac_filter_state', 'action', 'select', null, null, null, 1, $Statutmac, 'wifimac_filter_state', null, $iconmac_filter_state, 0, 'default', 'default', $order++, '0', false, false, null, true, null, null, null, null, null, null, null, null, $listValue);
        $EqLogic->AddCommand('Liste Mac Blanche', 'listwhite', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmac_list_white, 0, 'default', 'default',  $order++, '0', null, true, false, true, null, null, null, null, null, null, null, true);
        $EqLogic->AddCommand('Liste MAC Noire', 'listblack', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmac_list_black, 0, 'default', 'default',  $order++, '0', null, true, false, true, null, null, null, null, null, null, null, true);
    }
    private static function createEq_upload($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌── :fg-success:Début de création des commandes pour : ' . $logicalinfo['notificationName'] . ':/fg: ──');
        $Free_API = new Free_API();
        $Free_API->universal_get('upload', null, null, null, null, null);
        log::add('Freebox_OS', 'debug', '└────────────────────');
    }
}
