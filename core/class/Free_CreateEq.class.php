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
        $logicalinfo = Freebox_OS::getlogicalinfo();
        if (version_compare(jeedom::version(), "4", "<")) {
            $templatecore_V4 = null;
        } else {
            $templatecore_V4  = 'core::';
        };
        $Api_version = config::byKey('API_FREEBOX', 'Freebox_OS');
        //log::add('Freebox_OS', 'debug', '│──────────> Version API Compatible avec la Freebox : ' . $Api_version);
        if ($Api_version === '') {
            $result = Free_Refresh::refresh_API($Api_version);
            log::add('Freebox_OS', 'debug', '│──────────> Version API Compatible avec la Freebox : ' . $result);
            $Api_version = $result;
        }
        switch ($create) {
            case 'airmedia':
                Free_CreateEq::createEq_airmedia($logicalinfo, $templatecore_V4, $Api_version);
                break;
            case 'connexion':
                Free_CreateEq::createEq_connexion($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_connexion_4G($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_connexion_xdsl($logicalinfo, $templatecore_V4, $Api_version);
                break;
            case 'disk':
                Free_CreateEq::createEq_disk_SP($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_disk_RAID($logicalinfo, $templatecore_V4, $Api_version);
                break;
            case 'LCD':
                Free_CreateEq::createEq_LCD($logicalinfo, $templatecore_V4, $Api_version);
                break;
            case 'downloads':
                Free_CreateEq::createEq_download($logicalinfo, $templatecore_V4, $Api_version);
                break;
            case 'parental':
                Free_CreateEq::createEq_parental($logicalinfo, $templatecore_V4, $Api_version);
                break;
            case 'network':
                Free_CreateEq::createEq_network_interface($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_network_SP($logicalinfo, $templatecore_V4, 'LAN', $IsVisible, $Api_version);
                break;
            case 'netshare':
                Free_CreateEq::createEq_netshare($logicalinfo, $templatecore_V4, $Api_version);
                break;
            case 'networkwifiguest':
                Free_CreateEq::createEq_network_interface($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_network_SP($logicalinfo, $templatecore_V4, 'WIFIGUEST', $IsVisible, $Api_version);
                break;
            case 'phone':
                Free_CreateEq::createEq_phone($logicalinfo, $templatecore_V4, $Api_version);
                break;
            case 'system':
                Free_CreateEq::createEq_system($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_system_lan($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_system_SP($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_system_SP_lang($logicalinfo, $templatecore_V4, $Api_version);
                break;
            case 'wifi':
                Free_CreateEq::createEq_wifi($logicalinfo, $templatecore_V4, $Api_version);
                break;
            default:
                log::add('Freebox_OS', 'debug', '================= ORDRE DE LA CREATION DES EQUIPEMENTS STANDARDS  ==================');
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['systemName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['connexionName'] . ' / 4G' . ' / Fibre' . ' / xdsl');
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['freeplugName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['diskName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['phoneName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['LCDName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['airmediaName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['downloadsName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['networkName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['networkwifiguestName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['netshareName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['wifiName']);
                log::add('Freebox_OS', 'debug', '================= ENSEMBLE DES PLAYERS SOUS TENSION');
                log::add('Freebox_OS', 'debug', '================= ENSEMBLE DES VM');
                log::add('Freebox_OS', 'debug', '====================================================================================');
                Free_CreateEq::createEq_system($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_system_lan($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_system_SP($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_system_SP_lang($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_connexion($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_connexion_4G($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_connexion_xdsl($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_FreePlug($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_disk($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_phone($logicalinfo, $templatecore_V4, $Api_version);
                Free_CreateEq::createEq_netshare($logicalinfo, $templatecore_V4, $Api_version);
                $Type_box = config::byKey('TYPE_FREEBOX', 'Freebox_OS');
                if ($Type_box == 'fbxgw1r' || $Type_box == 'fbxgw2r') {
                    Free_CreateEq::createEq_LCD($logicalinfo, $templatecore_V4, $Api_version);
                } else {
                    log::add('Freebox_OS', 'debug', '>───────── Type de box compatible pour modifier les réglages de l\'afficheur : ' . $Type_box);
                }
                if (config::byKey('TYPE_FREEBOX_MODE', 'Freebox_OS') == 'router') {
                    Free_CreateEq::createEq_airmedia($logicalinfo, $templatecore_V4, $Api_version);
                    Free_CreateEq::createEq_download($logicalinfo, $templatecore_V4, $Api_version);
                    Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4, 'LAN', $Api_version);
                    Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4, 'WIFIGUEST', $Api_version);
                    Free_CreateEq::createEq_wifi($logicalinfo, $templatecore_V4, $Api_version);
                } else {
                    log::add('Freebox_OS', 'debug', '================= BOX EN MODE BRIDGE : LES ÉQUIPEMENTS SUIVANTS NE SONT PAS CRÉER  ==================');
                    log::add('Freebox_OS', 'debug', '>───────── ' . $logicalinfo['airmediaName']);
                    log::add('Freebox_OS', 'debug', '>───────── ' . $logicalinfo['downloadsName']);
                    log::add('Freebox_OS', 'debug', '>───────── ' . $logicalinfo['networkName'] . ' / ' . $logicalinfo['networkwifiguestName']);
                    log::add('Freebox_OS', 'debug', '====================================================================================');
                }
                if ($Type_box != 'fbxgw1r' && $Type_box != 'fbxgw2r') {
                    log::add('Freebox_OS', 'debug', '================= BOX COMPATIBLE AVEC LES VM  ==================');
                    Free_CreateEq::createEq_VM($logicalinfo, $templatecore_V4, $Api_version);
                } else {
                    log::add('Freebox_OS', 'debug', '================= BOX NON COMPATIBLE AVEC LES VM  ==================');
                }
                break;
        }
    }
    private static function createEq_airmedia($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['airmediaName']);
        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconAirPlayOn = 'fas fa-play';
            $iconAirPlayOff = 'fas fa-stop';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconAirPlayOn = 'fas fa-play icon_green';
            $iconAirPlayOff = 'fas fa-stop icon_red';
        };
        $Airmedia = Freebox_OS::AddEqLogic($logicalinfo['airmediaName'], $logicalinfo['airmediaID'], 'multimedia', false, null, null, null, '*/5 * * * *');
        $Airmedia->AddCommand('Choix Player AirMedia', 'ActualAirmedia', 'info', 'string', 'Freebox_OS::AirMedia_Recever', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', false, true);
        $Airmedia->AddCommand('Start', 'airmediastart', 'action', 'message', 'Freebox_OS::AirMedia_Start', null, null, 1, 'default', 'default', 0, $iconAirPlayOn, 0, 'default', 'default', 2, '0', $updateicon, false, null, true);
        $Airmedia->AddCommand('Stop', 'airmediastop', 'action', 'message', 'Freebox_OS::AirMedia_Stop', null, null, 1, 'default', 'default', 0, $iconAirPlayOff, 0, 'default', 'default', 3, '0', $updateicon, false, null, true);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_airmedia_sp($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement spécifique : ' . $logicalinfo['airmediaName']);
        $Free_API = new Free_API();
        $Free_API->universal_get('airmedia', null, null, null, true, true, false, $Api_version);
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_connexion($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['connexionName']);
        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconspeed = 'fas fa-tachometer-alt';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconspeed = 'fas fa-tachometer-alt icon_blue';
        };
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('connexion', null, null, 'ftth', true, true, false, $Api_version);
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
        $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *');
        $Connexion->AddCommand('Débit descendant', 'rate_down', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  1, '0', $updateicon, true, null, true, null, '#value# / 1024', '2');
        $Connexion->AddCommand('Débit montant', 'rate_up', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  2, '0', $updateicon, true, null, true, null, '#value# / 1024', '2');
        $Connexion->AddCommand('Débit descendant (max)', 'bandwidth_down', 'info', 'numeric', $templatecore_V4 . 'badge', $_bandwidth_down_unit, null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  3, '0', $updateicon, true, null, true, null, $_bandwidth_value_down, '2');
        $Connexion->AddCommand('Débit montant (max)', 'bandwidth_up', 'info', 'numeric', $templatecore_V4 . 'badge', $_bandwidth_up_unit, null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  4, '0', $updateicon, true, null, true, null, $_bandwidth_value_up, '2');
        $Connexion->AddCommand('Type de connexion', 'media', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  5, '0', $updateicon, true);
        $Connexion->AddCommand('Etat de la connexion', 'state', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  6, '0', $updateicon, true);
        $Connexion->AddCommand('IPv4', 'ipv4', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  7, '0', $updateicon, true);
        $Connexion->AddCommand('IPv6', 'ipv6', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  8, '0', $updateicon, true);
        $Connexion->AddCommand('Réponse Ping', 'ping', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  9, '0', $updateicon, true);
        $Connexion->AddCommand('Proxy Wake on Lan', 'wol', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  10, '0', $updateicon, true);
        log::add('Freebox_OS', 'debug', '└─────────');
        if ($result['sfp_present'] != null) {
            Free_CreateEq::createEq_connexion_FTTH($logicalinfo, $templatecore_V4, $result, $Api_version);
        }
        log::add('Freebox_OS', 'debug', '│──────────> ' . $_modul);
    }
    private static function createEq_connexion_FTTH($logicalinfo, $templatecore_V4, $result, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes Spécifique Fibre : ' . $logicalinfo['connexionName']);

        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
        };
        $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *');
        if (isset($result['link_type'])) {
            $Connexion->AddCommand('Type de connexion Fibre', 'link_type', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  20, '0', $updateicon, true);
        } else {
            log::add('Freebox_OS', 'debug', '│──────────>  Fonction type de connexion Fibre non présent');
        }
        $Connexion->AddCommand('Module Fibre présent', 'sfp_present', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  21, '0', $updateicon, true);
        $Connexion->AddCommand('Signal Fibre présent', 'sfp_has_signal', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  22, '0', $updateicon, true);
        $Connexion->AddCommand('Etat Alimentation', 'sfp_alim_ok', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  23, '0', $updateicon, true);
        $Connexion->AddCommand('Puissance transmise', 'sfp_pwr_tx', 'info', 'numeric', $templatecore_V4 . 'badge', 'dBm', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  24, '0', $updateicon, true, null, null, null, '#value# / 100', '2');
        $Connexion->AddCommand('Puissance reçue', 'sfp_pwr_rx', 'info', 'numeric', $templatecore_V4 . 'badge', 'dBm', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  25, '0', $updateicon, true, null, null, null, '#value# / 100', '2');

        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_connexion_4G($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes Spécifique 4G : ' . $logicalinfo['connexionName']);
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('connexion', null, null, 'lte/config', true, true, false, $Api_version);
        if ($result != false && $result != 'Aucun module 4G détecté') {
            $_modul = 'Module 4G : Présent';
            log::add('Freebox_OS', 'debug', '│──────────>' . $_modul);
            $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *');
            $Connexion->AddCommand('Débit xDSL Descendant', 'tx_used_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  20, '0', null, true, null, null, null, '#value# / 1000', '2');
            $Connexion->AddCommand('Débit xDSL Montant', 'rx_used_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  21, '0', null, true, null, null, null, '#value# / 1000', '2');
            $Connexion->AddCommand('Débit xDSL Descendant (max)', 'tx_max_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  22, '0', null, true, null, null, null, '#value# / 1000', '2');
            $Connexion->AddCommand('Débit xDSL Montant (max)', 'rx_max_rate_xdsl', 'info', 'numeric', $templatecore_V4 . 'badge', 'ko/s', null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  23, '0', null, true, null, null, null, '#value# / 1000', '2');
            $Connexion->AddCommand('Etat de la connexion xDSL 4G', 'state', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  28, '0', null, true);
        } else {
            $_modul = 'Module 4G : Non Présent';
            log::add('Freebox_OS', 'debug', '│──────────> ' . $_modul);
        }

        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_connexion_xdsl($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes Spécifique xdsl : ' . $logicalinfo['connexionName']);
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('connexion', null, null, 'xdsl', true, true, false, $Api_version);
        if ($result != false && $result != 'Aucun module 4G détecté') {
            if ($result['status']['status'] != 'down') {
                $_modul = 'Module xdsl : Présent';
                $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *');
                $Connexion->AddCommand('Type de modulation', 'modulation', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  40, '0', null, true);
                $Connexion->AddCommand('Protocole', 'protocol', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  41, '0', null, true);
            } else {
                $_modul = 'Module xdsl : Présent mais désactivé - Aucune création des équipements';
            }
        } else {
            $_modul = 'Module xdsl : Non Présent';
        }
        log::add('Freebox_OS', 'debug', '│──────────> ' . $_modul);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_disk($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : ' . $logicalinfo['diskName']);
        Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *');
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_disk_SP($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement spécifique : ' . $logicalinfo['diskName']);
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3');
            $icontemp = 'fas fa-thermometer-half';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $icontemp = 'fas fa-thermometer-half icon_blue';
        };
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, true, $Api_version);
        if ($result == 'auth_required') {
            $result = $Free_API->universal_get('universalAPI', null, null, 'storage/disk', true, true, true, $Api_version);
        }
        $disk = Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *');
        if ($result != false) {
            foreach ($result['result'] as $disks) {
                if ($disks['temp'] != 0) {
                    log::add('Freebox_OS', 'debug', '──────────> Température : ' . $disks['temp'] . '°C' . '- Disque [' . $disks['serial'] . '] - ' . $disks['id']);
                    $disk->AddCommand('Disque [' . $disks['serial'] . '] Temperature', $disks['id'] . '_temp', 'info', 'numeric', $templatecore_V4 . 'line', '°C', null, 1, 'default', 'default', 0, $icontemp, 0, '0', '100', null, 0, false, true, null, null);
                }
                if ($disks['serial'] != null) {
                    log::add('Freebox_OS', 'debug', '──────────> Tourne : ' . $disks['spinning'] . '- Disque [' . $disks['serial'] . '] - ' . $disks['id']);
                    $disk->AddCommand('Disque [' . $disks['serial'] . '] Tourne', $disks['id'] . '_spinning', 'info', 'binary', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, null, '0', false, false, 'never', null, null, null);
                }
                foreach ($disks['partitions'] as $partition) {
                    log::add('Freebox_OS', 'debug', '│──────────> ID :' . $partition['id'] . ' : Disque [' . $disks['type'] . '] - ' . $disks['id'] . ' - Partitions : ' . $partition['label']);
                    $disk->AddCommand($partition['label'] . ' - ' . $disks['type'] . ' - ' . $partition['fstype'], $partition['id'], 'info', 'numeric', 'core::horizontal', '%', null, 1, 'default', 'default', 0, 'fas fa-hdd fa-2x', 0, '0', 100, null, '0', false, false, 'never', null, true, '#value#*100', 2);
                }
            }
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_disk_RAID($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement spécifique RAID : ' . $logicalinfo['diskName']);
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3');
            $icontemp = 'fas fa-thermometer-half';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $icontemp = 'fas fa-thermometer-half icon_blue';
        };
        $Type_box = config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS');
        if ($Type_box == 'OK') {
            $Free_API = new Free_API();
            $disk = Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *');
            $result = $Free_API->universal_get('universalAPI', null, null, 'storage/raid', true, true, false, $Api_version);

            if ($result != false) {
                $order_i = 0;
                foreach ($result as $raid) {
                    log::add('Freebox_OS', 'debug', '│──────────> RAID : ' . $raid['name']);
                    $order_i--;
                    $disk->AddCommand('Raid ' . $raid['name'] . ' state', $raid['id'] . '_state', 'info', 'string', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order_i, '0', false, false, 'never', null, null, null);
                    $order_i--;
                    $disk->AddCommand('Raid ' . $raid['name'] . ' sync_action', $raid['id'] . '_sync_action', 'info', 'string', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order_i, '0', false, false, 'never', null, null, null);
                    $order_i--;
                    $disk->AddCommand('Raid ' . $raid['name'] . ' degraded', $raid['id'] . '_degraded', 'info', 'binary', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order_i, '0', false, false, 'never', null, null, null);
                    $order_i--;
                    if (isset($raid['members'])) {
                        foreach ($raid['members'] as $members_raid) {
                            $disk->AddCommand('Etat Role Disque ' . $members_raid['disk']['serial'], $members_raid['id'] . '_role', 'info', 'string', 'default', null, null, 1, 'default', 'default', 0, null, 0, null, null, $order_i, '0', false, false, 'never', null, null, null);
                            $order_i--;
                        }
                    }
                }
            }
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_download($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['downloadsName']);
        $updateicon = true;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconDownloadsOn = 'fas fa-play';
            $iconDownloadsOff = 'fas fa-stop';
            $iconRSSnb = 'fas fa-rss';
            $iconRSSread = 'fas fa-rss-square';
            $iconconn_ready = 'fas fa-ethernet';
            $icontask = 'fas fa-tasks';
            $icontask_queued = 'fas fa-tasks';
            $icontask_error = 'fas fa-tasks';
            $icondownload = 'fas fa-file-download';
            $iconspeed = 'fas fa-tachometer-alt';
            $iconcalendar = 'far fa-calendar-alt icon_green';
            $Templatemode = 'default';
            $iconDownloadsnormal = 'fas fa-rocket';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
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
        };
        $downloads = Freebox_OS::AddEqLogic($logicalinfo['downloadsName'], $logicalinfo['downloadsID'], 'multimedia', false, null, null, null, '5 */12 * * *');
        $downloads->AddCommand('Nb de tâche(s)', 'nb_tasks', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  1, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Nb de tâche(s) active', 'nb_tasks_active', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask,  0, 'default', 'default',  2, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Nb de tâche(s) en extraction', 'nb_tasks_extracting', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  3, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Nb de tâche(s) en réparation', 'nb_tasks_repairing', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  4, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Nb de tâche(s) en vérification', 'nb_tasks_checking', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  5, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Nb de tâche(s) en attente', 'nb_tasks_queued', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask_queued, 0, 'default', 'default',  6, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Nb de tâche(s) en erreur', 'nb_tasks_error', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask_error, 0, 'default', 'default',  7, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Nb de tâche(s) stoppée(s)', 'nb_tasks_stopped', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask_error, 0, 'default', 'default',  8, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Nb de tâche(s) terminée(s)', 'nb_tasks_done', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icontask, 0, 'default', 'default',  9, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Téléchargement en cours', 'nb_tasks_downloading', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $icondownload, 0, 'default', 'default', 10, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Vitesse réception', 'rx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default', 11, '0', $updateicon, true, null, true, null, '#value# / 1000', '2');
        $downloads->AddCommand('Vitesse émission', 'tx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, $iconspeed, 0, 'default', 'default',  12, '0', $updateicon, true, null, true, null, '#value# / 1000', '2');
        $downloads->AddCommand('Start Téléchargement', 'start_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsOn, 0, 'default', 'default',  13, '0', $updateicon, false, null, false);
        $downloads->AddCommand('Stop Téléchargement', 'stop_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsOff, 0, 'default', 'default',  14, '0', $updateicon, false, null, false);
        $downloads->AddCommand('Nb de flux RSS', 'nb_rss', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconRSSnb, 0, 'default', 'default',  15, '0', $updateicon, false, null, true);
        $downloads->AddCommand('Nb de flux RSS Non Lu', 'nb_rss_items_unread', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconRSSread, 0, 'default', 'default',  16, '0', $updateicon, false, null, true);
        $downloads->AddCommand('Etat connexion', 'conn_ready', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconconn_ready, 0, 'default', 'default',  17, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Etat Planning', 'throttling_is_scheduled', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconcalendar, 0, 'default', 'default',  18, '0', $updateicon, true, null, true);
        $action = $downloads->AddCommand('Mode Téléchargement', 'mode', 'info', 'string', $Templatemode, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  19, '0', $updateicon, true, null, true);
        $listValue = 'normal|Mode normal;slow|Mode lent;hibernate|Mode Stop;schedule|Mode Planning';
        $downloads->AddCommand('Choix Mode Téléchargement', 'mode_download', 'action', 'select', null, null, null, 1, $action, 'mode', 0, $iconDownloadsnormal, 0, 'default', 'default',  20, '0', $updateicon, false, null, true, null, null, null, null, null, null, null, null, $listValue);

        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_FreePlug($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['freeplugName']);
        $updateicon = false;
        $Free_API = new Free_API();
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconReboot = 'fas fa-sync';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconReboot = 'fas fa-sync icon_red';
        };
        $result = $Free_API->universal_get('universalAPI', null, null, 'freeplug', true, true, false, $Api_version);
        foreach ($result['result'] as $freeplugs) {
            foreach ($freeplugs['members'] as $freeplug) {
                log::add('Freebox_OS', 'debug', '│──────────>  Création Freeplug : ' . $freeplug['id']);
                $FreePlug = Freebox_OS::AddEqLogic($logicalinfo['freeplugName'] . ' - ' . $freeplug['id'], $freeplug['id'], 'default', true, $logicalinfo['freeplugName'], null, null, '*/5 * * * *', null, null, null, 'system');
                $FreePlug->AddCommand('Rôle', 'net_role', 'info', 'string',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', 10, '0', $updateicon, false, false, true);
                $FreePlug->AddCommand('Redémarrer', 'reset', 'action', 'other',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconReboot, 0, 'default', 'default',  1, '0', true, false, null, true);
                //$FreePlug->AddCommand('Débit TX', 'tx_rate', 'info', 'numeric',  $templatecore_V4 . 'line', 'Mb/s', 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', 12, '0', $updateicon, false, false, true);
                //$FreePlug->AddCommand('Débit RX', 'rx_rate', 'info', 'numeric',  $templatecore_V4 . 'line', 'Mb/s', 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', 12, '0', $updateicon, false, false, true);
            }
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_LCD($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement spécifique : ' . $logicalinfo['LCDName']);
        $LCD = Freebox_OS::AddEqLogic($logicalinfo['LCDName'], $logicalinfo['LCDID'], 'default', false, null, null, null, '5 */12 * * *');
        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconbrightness = 'fas fa-adjust';
            $iconorientation = 'fas fa-map-signs';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconbrightness = 'fas fa-adjust icon_green';
            $iconorientation = 'fas fa-map-signs icon_green';
        };
        $StatusLCD = $LCD->AddCommand('Etat Lumininosité écran LCD', 'brightness', "info", 'numeric', null, '%', null, 0, '', '', '', $iconbrightness, 0, '0', 100, 20, 2, $updateicon, true, false, true);
        $LCD->AddCommand('Lumininosité écran LCD', 'brightness', 'action', 'slider', null, '%', null, 1, $StatusLCD, 'default', 0, $iconbrightness, 0, '0', 100, 21, '0', $updateicon, false, null, true, null, 'floor(#value#)');
        // Affichage Orientation
        $StatusLCD = $LCD->AddCommand('Etat Orientation', 'orientation', "info", 'string', null, null, null, 0, '', '', '', $iconorientation, 0, '0', 100, 30, 2, $updateicon, true, false, true);
        $listValue = '0|Horizontal;90|90 degrés;180|180 degrés;270|270 degrés';
        $LCD->AddCommand('Orientation', 'orientation', 'action', 'select', null, null, null, 1, $StatusLCD, 'default', 0, $iconorientation, 0, '0', 100, 31, '0', $updateicon, false, null, true, null, null . null, null, null, null, null, null, $listValue);
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_parental($logicalinfo, $templatecore_V4, $Api_version)
    {
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('parentalprofile', null, null, null, true, true, false, $Api_version);
        foreach ($result  as $Equipement) {
            log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : Contrôle parental');
            if (version_compare(jeedom::version(), "4", "<")) {
                log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
                $Templateparent = null;
                $iconparent_allowed = 'fas fa-user-check';
                $iconparent_denied = 'fas fa-user-lock';
                $iconparent_temp = 'fas fa-user-clock';
            } else {
                log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
                $Templateparent = 'Freebox_OS::Parental';
                $iconparent_allowed = 'fas fa-user-check icon_green';
                $iconparent_denied = 'fas fa-user-lock icon_red';
                $iconparent_temp = 'fas fa-user-clock icon_blue';
            };

            $category = 'default';
            $Equipement['name'] = preg_replace('/\'+/', ' ', $Equipement['name']); // Suppression '

            $parental = Freebox_OS::AddEqLogic($Equipement['name'], 'parental_' . $Equipement['id'], $category, true, 'parental', null, $Equipement['id'], '*/5 * * * *', null, null, null, 'parental_controls');
            $StatusParental = $parental->AddCommand('Etat', $Equipement['id'], "info", 'string', $Templateparent, null, null, 1, '', '', '', '', 0, 'default', 'default', 1, 1, false, true, null, true);
            $parental->AddCommand('Autoriser', 'allowed', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconparent_allowed, 0, 'default', 'default', 2, '0', false, false, null, true);
            $parental->AddCommand('Bloquer', 'denied', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconparent_denied, 0, 'default', 'default', 3, '0', false, false, null, true);
            $listValue = '1800|0h30;3600|1h00;5400|1h30;7200|2h00;10800|3h00;14400|4h00';
            $parental->AddCommand('Autoriser-Bloquer Temporairement', 'tempDenied', 'action', 'select', null, null, null, 1, $StatusParental, 'parentalStatus', '', $iconparent_temp, 0, 'default', 'default', 4, '0', false, false, '', true, null, null, null, null, null, null, null, null, $listValue);
            log::add('Freebox_OS', 'debug', '└─────────');
        }
    }
    private static function createEq_phone($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['phoneName']);
        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconmissed = 'icon techno-phone1';
            $iconaccepted = 'icon techno-phone3';
            $iconoutgoing = 'ficon techno-phone2';
            $iconDell_call = 'fas fa-magic';
            $iconRead_call = 'fab fa-readme';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconmissed = 'icon techno-phone1 icon_red';
            $iconaccepted = 'icon techno-phone3 icon_blue';
            $iconoutgoing = 'icon techno-phone2 icon_green';
            $iconDell_call = 'fas fa-magic icon_red';
            $iconRead_call = 'fab fa-readme icon_blue';
        };
        $phone = Freebox_OS::AddEqLogic($logicalinfo['phoneName'], $logicalinfo['phoneID'], 'default', false, null, null, null, '*/30 * * * *');
        $phone->AddCommand('Nb Manqués', 'nbmissed', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 1, 'default', 'default', 0, $iconmissed, 1, 'default', 'default',  1, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand('Liste Manqués', 'listmissed', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmissed, 1, 'default', 'default',  2, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand('Nb Reçus', 'nbaccepted', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 1, 'default', 'default', 0, $iconaccepted, 1, 'default', 'default',  3, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand('Liste Reçus', 'listaccepted', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconaccepted, 1, 'default', 'default',  4, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand('Nb Emis', 'nboutgoing', 'info', 'numeric', $templatecore_V4 . 'badge', null, null, 1, 'default', 'default', 0, $iconoutgoing, 1, 'default', 'default',  5, '0', $updateicon, true, false, true, null, null, null, null);
        $phone->AddCommand('Liste Emis', 'listoutgoing', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconoutgoing, 1, 'default', 'default',  6, '0', $updateicon, true, false, null, null, null, null, 'NONAME');
        $phone->AddCommand('Vider le journal d appels', 'phone_dell_call', 'action', 'other', 'default', null, null,  1, 'default', 'default', 0, $iconDell_call, 1, 'default', 'default', 9, '0', $updateicon, false, null, true);
        $phone->AddCommand('Tout marquer comme lu', 'phone_read_call', 'action', 'other', 'default', null, null,  1, 'default', 'default', 0, $iconRead_call, 0, 'default', 'default', 10, '0', $updateicon, false, null, true);

        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_network($logicalinfo, $templatecore_V4, $_network = 'LAN', $Api_version)
    {
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
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $icon_search = 'fas fa-search-plus';
            $icon_wol = 'fas fa-broadcast-tower';
            $icon_dhcp = 'fas fa-network-wired';
            $icon_redir = 'fas fa-project-diagram';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $icon_search = 'fas fa-search-plus icon_green';
            $icon_wol = 'fas fa-broadcast-tower icon_orange';
            $icon_dhcp = 'fas fa-network-wired icon_blue';
            $icon_redir = 'fas fa-project-diagram icon_blue';
        };
        $updateWidget = false;
        $_IsVisible = 1;
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : ' . $_networkname);
        $network = Freebox_OS::AddEqLogic($_networkname, $_networkID, 'default', false, null, null, null, '*/5 * * * *');
        // ---> $network->AddCommand('Redirections des ports', 'redir', 'action', 'message',  'default', null, null, 0, 'default', 'default', 0, $icon_redir, 0, 'default', 'default',  -33, '0', true, false, null, true, null, null, null, null, null, 'redir?lan_ip=#lan_ip#&enable_lan=#enable_lan#&src_ip=#src_ip#&ip_proto=#ip_proto#&wan_port_start=#wan_port_start#&wan_port_end=#wan_port_end#&lan_port=#lan_port#&comment=#comment#');
        $network->AddCommand('Ajouter supprimer IP Fixe', 'add_del_mac', 'action', 'message',  'default', null, null, 0, 'default', 'default', 0, $icon_dhcp, 0, 'default', 'default',  -31, '0', $updateWidget, false, null, true, null, null, null, null, null, 'add_del_dhcp?mac_address=#mac#&ip=#ip#&comment=#comment#&name=#name#&function=#function#&type=#type#');
        $network->AddCommand('Rechercher les nouveaux appareils', 'search', 'action', 'other',  $templatecore_V4 . 'line', null, null, true, 'default', 'default', 0, $icon_search, true, 'default', 'default',  -30, '0', $updateWidget, false, null, true, null, null, null, null, null, null, null, true);
        $network->AddCommand('Wake on LAN', 'WakeonLAN', 'action', 'message',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, $icon_wol, 0, 'default', 'default',  -32, '0', $updateWidget, false, null, true, null, null, null, null, null, 'wol?mac_address=#mac#&password=#password#');
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_netshare($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['netshareName']);
        $updateicon = false;
        $_order = 1;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $color_on = null;
            $color_off = null;
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $color_on = ' icon_green';
            $color_off = ' icon_red';
        };

        $netshare = Freebox_OS::AddEqLogic($logicalinfo['netshareName'], $logicalinfo['netshareID'], 'multimedia', false, null, null, null, '5 */12 * * *');
        $boucle_num = 1; // 1 = Partage Imprimante - 2 = Partage de fichiers Windows - 3 = Partage Fichier Mac - 4 = Partage Fichier FTP
        $_order = 1;
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
            log::add('Freebox_OS', 'debug', '│──────────> Boucle pour Création des commandes : ' . $name);
            $netshareSTATUS = $netshare->AddCommand($name, $Logical_ID, "info", 'binary', null, null, 'LIGHT_STATE', 0, '', '', '', $icon, 0, 'default', 'default', '0', $_order, $updateicon, true);
            $_order++;
            $netshare->AddCommand('Activer ' . $name, $Logical_ID . 'On', 'action', 'other', $template, null, 'LIGHT_ON', 1, $netshareSTATUS, '', 0, $icon . $color_on, 0, 'default', 'default', $_order, '0', $updateicon, false);
            $_order++;
            $netshare->AddCommand('Désactiver ' . $name, $Logical_ID  . 'Off', 'action', 'other', $template, null, 'LIGHT_OFF', 1, $netshareSTATUS, '', 0, $icon . $color_off, 0, 'default', 'default', $_order, '0', $updateicon, false);
            $_order++;

            $boucle_num++;
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_network_interface($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Recherche des Interfaces réseaux : ' . $logicalinfo['networkName']);
        $Free_API = new Free_API();
        //$Free_API->universal_get('network', null, null, 'browser/interfaces');
        $Free_API->universal_get('universalAPI', null, null, 'lan/browser/interfaces', true, true, true, $Api_version);
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_network_SP($logicalinfo, $templatecore_V4, $_network = 'LAN', $IsVisible = true, $Api_version)
    {
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

        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $icon_search = 'fas fa-search-plus';
            $icon_wol = 'fas fa-broadcast-tower';
            $icon_dhcp = 'fas fa-network-wired';
            $icon_redir = 'fas fa-project-diagram';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $icon_search = 'fas fa-search-plus icon_green';
            $icon_wol = 'fas fa-broadcast-tower icon_orange';
            $icon_dhcp = 'fas fa-network-wired icon_blue';
            $icon_redir = 'fas fa-project-diagram icon_blue';
        };
        $updateWidget = false;
        if ($IsVisible == true) {
            $_IsVisible = 1;
        } else {
            $_IsVisible = '0';
        }
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes spécifiques : ' . $_networkname);
        $Free_API = new Free_API();
        $network = Freebox_OS::EqLogic_ID($_networkname, $_networkID);
        // ---> $network->AddCommand('Redirections des ports', 'redir', 'action', 'message',  'default', null, null, 0, 'default', 'default', 0, $icon_redir, 0, 'default', 'default',  -33, '0', true, false, null, true, null, null, null, null, null, 'redir?lan_ip=#lan_ip#&enable_lan=#enable_lan#&src_ip=#src_ip#&ip_proto=#ip_proto#&wan_port_start=#wan_port_start#&wan_port_end=#wan_port_end#&lan_port=#lan_port#&comment=#comment#');
        $network->AddCommand('Ajouter supprimer IP Fixe', 'add_del_mac', 'action', 'message',  'default', null, null, 0, 'default', 'default', 0, $icon_dhcp, 0, 'default', 'default',  -31, '0', $updateWidget, false, null, true, null, null, null, null, null, 'add_del_dhcp?mac_address=#mac#&ip=#ip#&comment=#comment#&name=#name#&function=#function#&type=#type#');
        $network->AddCommand('Rechercher les nouveaux appareils', 'search', 'action', 'other',  $templatecore_V4 . 'line', null, null, true, 'default', 'default', 0, $icon_search, true, 'default', 'default',  -30, '0', $updateWidget, false, null, true, null, null, null, null, null, null, null, true);
        $network->AddCommand('Wake on LAN', 'WakeonLAN', 'action', 'message',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, $icon_wol, 0, 'default', 'default',  -32, '0', $updateWidget, false, null, true, null, null, null, null, null, 'wol?mac_address=#mac#&password=#password#');
        //$result = $Free_API->universal_get('network', null, null, 'lan/browser/' . $_networkinterface);
        $result = $Free_API->universal_get('universalAPI', null, null, 'lan/browser/' . $_networkinterface, true, true, true, $Api_version);

        if (isset($result['result'])) {
            if ($network->getConfiguration('UpdateName') == 1) {
                $updatename_disable = 1;
            } else {
                $updatename_disable = 0;
            }
            log::add('Freebox_OS', 'debug', '│──────────> Désactiver la mise à jour des noms : ' . $updatename_disable);
            foreach ($result['result'] as $Equipement) {
                if ($Equipement['primary_name'] != '') {
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
                    $Equipement['primary_name'] = str_replace(array_keys($replace_device_type), $replace_device_type, $Equipement['primary_name']);

                    if ($updatename_disable == 0) {
                        $updatename = true; // mise à jour automatique des noms des commandes
                    } else {
                        $updatename = false; // mise à jour automatique des noms des commandes
                    }
                    if (isset($Equipement['access_point'])) {
                        $name_connectivity_type = $Equipement['access_point']['connectivity_type'];
                    } else {
                        $name_connectivity_type = 'Wifi Ethernet ?';
                    }
                    $Command = $network->AddCommand($Equipement['primary_name'], $Equipement['id'], 'info', 'binary', 'Freebox_OS::Network', null, null, $_IsVisible, 'default', 'default', 0, null, 0, 'default', 'default', null, '0', $updateWidget, true, null, null, null, null, null, null, null, null, null, null, null, $updatename, $name_connectivity_type);
                    $Command->setConfiguration('host_type', $Equipement['host_type']);
                    if (isset($Equipement['l3connectivities'])) {
                        foreach ($Equipement['l3connectivities'] as $Ip) {
                            if ($Ip['active']) {
                                if ($Ip['af'] == 'ipv4') {
                                    $Command->setConfiguration('IPV4', $Ip['addr']);
                                } else {
                                    $Command->setConfiguration('IPV6', $Ip['addr']);
                                }
                            }
                        }
                    }
                    if (isset($Equipement['l2ident'])) {
                        $ident = $Equipement['l2ident'];
                        if ($ident['type'] == 'mac_address') {
                            $Command->setConfiguration('mac_address', $ident['id']);
                        }
                    }
                    if ($Command->execCmd() != $Equipement['active']) {
                        $Command->setCollectDate(date('Y-m-d H:i:s'));
                        $Command->setConfiguration('doNotRepeatEvent', 1);
                        $Command->event($Equipement['active']);
                    }
                    $Command->save();
                }
            }
            log::add('Freebox_OS', 'debug', '└─────────');
        } else {
            log::add('Freebox_OS', 'debug', '│===========> PAS D\'APPAREIL TROUVE');
        }
    }

    private static function createEq_notification($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : ' . $logicalinfo['notificationName']);
        $Free_API = new Free_API();
        $Free_API->universal_get('notification', null, null, null, true, null, $Api_version);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_system($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['systemName']);
        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconReboot = 'fas fa-sync';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconReboot = 'fas fa-sync icon_red';
        };
        $system = Freebox_OS::AddEqLogic($logicalinfo['systemName'], $logicalinfo['systemID'], 'default', false, null, null, null, '*/30 * * * *');
        $system->AddCommand('Reboot', 'reboot', 'action', 'other',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconReboot, 0, 'default', 'default',  31, '0', true, false, null, true);
        $system->AddCommand('Freebox firmware version', 'firmware_version', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', $updateicon, true);
        $system->AddCommand('Mac', 'mac', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  2, '0', $updateicon, true);
        $system->AddCommand('Allumée depuis', 'uptime', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  3, '0', $updateicon, true);
        $system->AddCommand('Board name', 'board_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  4, '0', $updateicon, true);
        $system->AddCommand('Serial', 'serial', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  5, '0', $updateicon, true);
        $system->AddCommand('Type de Freebox', 'pretty_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'model_info', 0, null, 0, 'default', 'default',  63, '0', $updateicon, true);
        $system->AddCommand('Type de Wifi', 'wifi_type', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'model_info',  0, null, 0, 'default', 'default',  64, '0', $updateicon, true);
        $system->AddCommand('Modele de Freebox', 'model_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'model_info',  0, null, 0, 'default', 'default',  65, '0', $updateicon, true);
        //$system->AddCommand('Redirection de ports', 'port_forwarding', 'action', 'message', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 'default', 6, '0', $updateicon);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_system_lan($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes spécifique Type de Freebox : ' . $logicalinfo['systemName']);
        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
        };
        $system = Freebox_OS::AddEqLogic($logicalinfo['systemName'], $logicalinfo['systemID'], 'default', false, null, null, null, '*/30 * * * *');
        $system->AddCommand('Nom Freebox', 'name', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 60, '0', $updateicon, true);
        $system->AddCommand('Mode Freebox', 'mode', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  61, '0', $updateicon, true);
        $system->AddCommand('Ip', 'ip', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  62, '0', $updateicon, true);
        log::add('Freebox_OS', 'debug', '└─────────');

        Free_Refresh::RefreshInformation($logicalinfo['systemID']);
    }

    private static function createEq_system_SP($logicalinfo, $templatecore_V4, $Api_version)
    {
        $Free_API = new Free_API();
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes spécifiques Capteurs : ' . $logicalinfo['systemName']);
        $system = Freebox_OS::AddEqLogic($logicalinfo['systemName'], $logicalinfo['systemID'], 'default', false, null, null, null, '*/30 * * * *');
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3');
            $Template4G = null;
            $templatecore_V4 = null;
            $icontemp = 'fas fa-thermometer-half';
            $iconfan = 'fas fa-fan';
            $icon4Gon = 'fas fa-broadcast-tower';
            $icon4Goff = 'fas fa-broadcast-tower';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $Template4G = 'Freebox_OS::4G';
            $templatecore_V4  = 'core::';
            $icontemp = 'fas fa-thermometer-half icon_blue';
            $iconfan = 'fas fa-fan icon_blue';
            $icon4Gon = 'fas fa-broadcast-tower icon_green';
            $icon4Goff = 'fas fa-broadcast-tower icon_red';
        };
        $boucle_num = 1; // 1 = sensors - 2 = fans - 3 = extension
        $_order = 6;
        while ($boucle_num <= 3) {

            if ($boucle_num == 1) {
                $boucle_update = 'sensors';
            } else if ($boucle_num == 2) {
                $boucle_update = 'fans';
            } else if ($boucle_num == 3) {
                $boucle_update = 'expansions';
            }
            $result_SP = $Free_API->universal_get('system', null, $boucle_update, null, true, true, false, $Api_version);
            if ($result_SP != false) {
                log::add('Freebox_OS', 'debug', '│──────────> Boucle pour Update : ' . $boucle_update);

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
                        log::add('Freebox_OS', 'debug', '│ Name : ' . $_name . ' -- id : ' . $_id . ' -- value : ' . $_value . ' -- unité : ' . $_unit . ' -- type : ' . $_type);
                        if ($_name != '') {

                            $system->AddCommand($_name, $_id, 'info', $_type, $templatecore_V4 . 'line', $_unit, null, $IsVisible, 'default', $link_logicalId, 0, $icon, 0, $_min, $_max, $_order, 0, false, true, null, $_iconname);

                            $system->checkAndUpdateCmd($_id, $_value);

                            if ($boucle_update == 'expansions') {
                                if ($Equipement['type'] == 'dsl_lte') {
                                    // Début ajout 4G
                                    $_4G = $system->AddCommand('Etat 4G ', '4GStatut', "info", 'binary', null . 'line', null, null, 0, '', '', '', '', 1, 'default', 'default', 32, '0', false, 'never', null, true);
                                    $system->AddCommand('4G On', '4GOn', 'action', 'other', $Template4G, null, 'ENERGY_ON', 1, $_4G, '4GStatut', 0, $icon4Gon, 1, 'default', 'default', 33, '0', false, false, null, true);
                                    $system->AddCommand('4G Off', '4GOff', 'action', 'other', $Template4G, null, 'ENERGY_OFF', 1, $_4G, '4GStatut', 0, $icon4Goff, 0, 'default', 'default', 34, '0', false, false, null, true);
                                }
                            }
                            $_order++;
                        }
                    }
                }
            } else {
                log::add('Freebox_OS', 'debug', '│──────────> Pas de commande spécifique : ' . $logicalinfo['systemName']);
                break;
            }
            $boucle_num++;
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_system_SP_lang($logicalinfo, $templatecore_V4, $Api_version)
    {
        $updateicon = false;
        $system = Freebox_OS::AddEqLogic($logicalinfo['systemName'], $logicalinfo['systemID'], 'default', false, null, null, null, '*/30 * * * *');
        $Free_API = new Free_API();
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3');
            $iconLang = 'fas fa-language';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconLang = 'fas fa-language icon_blue';
        };
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des langues : ' . $logicalinfo['systemName']);
        $system->AddCommand('langue Box', 'lang', 'info', 'string', 'default', null, 'default', 1, 'default', '4GStatut', 0, $iconLang, 1, 'default', 'default', 50, '0', false, false, null, true);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_VM($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['VMName']);
        $updateicon = true;
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('universalAPI', null, null, 'vm', false, false, false, $Api_version);
        if ($result != null) {
            $VMmemory = 'fas fa-memory';
            $VMCPU = 'fas fa-microchip';
            $VMscreen = 'fas fa-desktop';
            $VMdisk = 'fas fa-hdd';
            $VMstatus = 'fas fa-info-circle';
            if (version_compare(jeedom::version(), "4", "<")) {
                log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
                $VMOn = 'fas fa-play';
                $VMOff = 'fas fa-stop';
                $VMRestart = 'fas fa-sync';
                $VMUSB = 'fab fa-usb';
                $VMstatus = 'fas fa-info-circle';
                $TemplateVM = 'default';
            } else {
                log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
                $VMOn = 'fas fa-play icon_green';
                $VMOff = 'fas fa-stop icon_red';
                $VMRestart = 'fas fa-sync icon_red';
                $VMUSB = 'fab fa-usb icon_green';
                $VMstatus = 'fas fa-info-circle icon_green';
                $TemplateVM = 'Freebox_OS::VM';
            };

            foreach ($result as $Equipement) {
                if ($Equipement['name'] == null && $Equipement['cloudinit_hostname'] != null) {
                    $VM_name = $Equipement['cloudinit_hostname'];
                } else if ($Equipement['name'] != null) {
                    $VM_name = $Equipement['name'];
                } else {
                    $VM_name = 'VM_' . $Equipement['id'];
                }
                $_VM = Freebox_OS::AddEqLogic($VM_name, 'VM_' . $Equipement['id'], 'multimedia', true, 'VM', null, $Equipement['id'], '*/5 * * * *', null, null);
                $_VM->AddCommand('CPU(s)', 'vcpus', 'info', 'numeric',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, $VMCPU, 0, 'default', 'default', 10, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Mac', 'mac', 'info', 'string',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, 'default', 0, 'default', 'default', 11, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Mémoire', 'memory', 'info', 'numeric',  $templatecore_V4 . 'line', 'Mo', 'default', 0, 'default', 'default', 0, $VMmemory, 0, 'default', 'default', 12, '0', $updateicon, false, false, true);
                $_VM->AddCommand('USB', 'bind_usb_ports', 'info', 'string',  null, null, 'default', 1, 'default', 'default', 0, $VMUSB, 1, 'default', 'default', 13, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Ecran virtuel', 'enable_screen', 'info', 'binary',  $templatecore_V4 . 'line', null, 'default', 0, 'default', 'default', 0, $VMscreen, '0', 'default', 'default', 14, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Nom', 'name', 'info', 'string',  null, null, 'default', 0, 'default', 'default', 0, 'default', 1, 'default', 'default', 15, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Type de disque', 'disk_type', 'info', 'string',  null, null, 'default', 0, 'default', 'default', 0, $VMdisk, 1, 'default', 'default', 16, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Status', 'status', 'info', 'string', $TemplateVM, null, 'default', 1, 'default', 'default', 0, $VMstatus, 0, 'default', 'default', 1, '0', $updateicon, false, false, true);
                $_VM->AddCommand('Start', 'start', 'action', 'other', 'default', null, 'default', 1, 'default', 'default', 0, $VMOn, 0, 'default', 'default', 2, '0', $updateicon, false);
                $_VM->AddCommand('Stop', 'stop', 'action', 'other', 'default', null, 'default', 1, 'default', 'default', 0, $VMOff, 0, 'default', 'default', 3, '0', $updateicon, false);
                $_VM->AddCommand('Redémarrer', 'restart', 'action', 'other', 'default', null, 'default', 1, 'default', 'default', 0, $VMRestart, 0, 'default', 'default', 4, '0', $updateicon, false);
            }
        } else {
            log::add('Freebox_OS', 'debug', '│ PAS DE ' . $logicalinfo['VMName'] . ' SUR VOTRE BOX ');
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_wifi($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['wifiName']);
        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $TemplateWifiOnOFF = 'default';
            $iconWifiOn = 'fas fa-wifi';
            $iconWifiOff = 'fas fa-times';
            $iconWifiPlanningOn = 'fas fa-calendar-alt';
            $iconWifiPlanningOff = 'fas fa-calendar-times';
            $iconWifiWPSOn = 'fas fa-ethernet';
            $iconWifiWPSOff = 'fas fa-ethernet';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $TemplateWifiOnOFF = 'Freebox_OS::Wifi';
            $TemplateWifiPlanningOnOFF = 'Freebox_OS::Planning Wifi';
            $TemplateWifiWPSOnOFF = 'Freebox_OS::Wfi WPS';
            $iconWifiOn = 'fas fa-wifi icon_green';
            $iconWifiOff = 'fas fa-times icon_red';
            $iconWifiPlanningOn = 'fas fa-calendar-alt icon_green';
            $iconWifiPlanningOff = 'fas fa-calendar-times icon_red';
            $iconWifiWPSOn = 'fas fa-ethernet icon_green';
            $iconWifiWPSOff = 'fas fa-ethernet icon_red';
        };
        $Wifi = Freebox_OS::AddEqLogic($logicalinfo['wifiName'], $logicalinfo['wifiID'], 'default', false, null, null, null, '*/5 * * * *');
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
        log::add('Freebox_OS', 'debug', '└─────────');
        Free_CreateEq::createEq_wifi_bss($logicalinfo, $templatecore_V4, $Wifi, $Api_version);
        Free_CreateEq::createEq_mac_filter($logicalinfo, $templatecore_V4, $Wifi, $Api_version);
        Free_CreateEq::createEq_wifi_ap($logicalinfo, $templatecore_V4, $Wifi, $Api_version);
    }

    private static function createEq_wifi_ap($logicalinfo, $templatecore_V4, $Wifi, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement spécifique : ' . $logicalinfo['wifiName'] . ' / ' . $logicalinfo['wifiAPName']);

        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconWifi = 'fas fa-wifi';
            $TemplateWifi = 'default';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconWifi = 'fas fa-wifi icon_blue';
            $TemplateWifi = 'Freebox_OS::Wifi Statut carte';
        };
        $order = 50;
        $Free_API = new Free_API();
        //$result = $Free_API->universal_get('wifi', null, null, 'ap');
        $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/ap', true, true, true, $Api_version);

        $nb_card = count($result['result']);
        if ($result != false) {
            for ($k = 0; $k < $nb_card; $k++) {
                log::add('Freebox_OS', 'debug', '│──────────> Nom de la carte ' . $result['result'][$k]['name'] . ' - Id : ' . $result['result'][$k]['id'] . ' - Status : ' . $result['result'][$k]['status']['state']);
                $Wifi->AddCommand('Etat carte Wifi ' . $result['result'][$k]['name'], $result['result'][$k]['id'], 'info', 'string', $TemplateWifi, null, null, 1, null, null, 0, $iconWifi, false, 'default', 'default', $order, '0', $updateicon, false, false, true);
                $order++;
            }
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_wifi_bss($logicalinfo, $templatecore_V4, $Wifi, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement spécifique : ' . $logicalinfo['wifiName'] . ' / ' . $logicalinfo['wifiWPSName']);

        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconWifiSessionWPSOn = 'fas fa-link';
            $iconWifiSessionWPSOff = 'fas fa-link';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconWifiSessionWPSOn = 'fas fa-link icon_orange';
            $iconWifiSessionWPSOff = 'fas fa-link icon_red';
        };
        $order = 30;
        $Wifi->AddCommand('Wifi Session WPS (toutes les sessions) Off', 'wifiSessionWPSOff', 'action', 'other', null, null, 'LIGHT_OFF', 1, null, null, 0, $iconWifiSessionWPSOff, true, 'default', 'default', $order, '0', $updateicon, false, false, true);
        $order++;
        $Free_API = new Free_API();
        //$result = $Free_API->universal_get('wifi', null, null, 'bss');
        $result = $Free_API->universal_get('universalAPI', null, null, 'wifi/bss', true, true, true, $Api_version);
        if ($result != false) {
            foreach ($result['result'] as $wifibss) {
                if ($wifibss['config']['wps_enabled'] != true) continue;
                if ($wifibss['config']['use_default_config'] == true) {
                    $WPSname = 'Wifi Session WPS (' . $wifibss['shared_bss_params']['ssid'] . ') On';
                } else {
                    $WPSname = 'Wifi Session WPS (' . $wifibss['config']['ssid'] . ') On';
                }
                $Wifi->AddCommand($WPSname, $wifibss['id'], 'action', 'other', null, null, 'LIGHT_ON', 1, null, null, 0, $iconWifiSessionWPSOn, true, 'default', 'default', $order, '0', $updateicon, false, false, true);
                if ($wifibss['config']['use_default_config'] == true) {
                    log::add('Freebox_OS', 'debug', '│──────────> Configuration Wifi commune pour l\'ensemble des cartes');
                    break;
                } else {
                    $order++;
                }
            }
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_mac_filter($logicalinfo, $templatecore_V4, $Wifi, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : ' . $logicalinfo['wifimmac_filter']);
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $Templatemac = null;
            $iconmac_filter_state = 'fas fa-wifi';
            $iconmac_add_del_mac = 'fas fa-calculator';
            $iconmac_list_white = 'fas fa-list-alt';
            $iconmac_list_black = 'far fa-list-alt';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $Templatemac = 'Freebox_OS::Filtrage Adresse Mac';
            $iconmac_filter_state = 'fas fa-wifi icon_blue';
            $iconmac_add_del_mac = 'fas fa-calculator icon_red';
            $iconmac_list_white = 'fas fa-list-alt icon_green';
            $iconmac_list_black = 'far fa-list-alt icon_red';
        };
        $order = 40;
        $Statutmac = $Wifi->AddCommand('Etat Mode de filtrage', 'wifimac_filter_state', "info", 'string', $Templatemac, null, null, 1, null, null, null, null, 1, 'default', 'default', $order, 1, false, true, null, true);
        $order++;
        $listValue = 'disabled|Désactiver;blacklist|Liste Noire;whitelist|Liste Blanche';
        $Wifi->AddCommand('Mode de filtrage', 'mac_filter_state', 'action', 'select', null, null, null, 1, $Statutmac, 'wifimac_filter_state', null, $iconmac_filter_state, 0, 'default', 'default', $order, '0', false, false, null, true, null, null, null, null, null, null, null, null, $listValue);
        $order++;
        $Wifi->AddCommand('Ajout - Supprimer filtrage Mac', 'add_del_mac', 'action', 'message',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, $iconmac_add_del_mac, 0, 'default', 'default',  $order, '0', true, false, null, true, null, null, null, null, null, 'add_del_mac?mac_address=#mac_address#&function=#function#&filter=#filter#&comment=#comment#');
        $order++;
        $Wifi->AddCommand('Liste Mac Blanche', 'listwhite', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmac_list_white, 0, 'default', 'default',  $order, '0', null, true, false, true, null, null, null, null);
        $order++;
        $Wifi->AddCommand('Liste MAC Noire', 'listblack', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmac_list_black, 0, 'default', 'default',  $order, '0', null, true, false, true, null, null, null, null);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_upload($logicalinfo, $templatecore_V4, $Api_version)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : ' . $logicalinfo['notificationName']);
        $Free_API = new Free_API();
        $Free_API->universal_get('upload', null, null, null, null, null, $Api_version);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
}
