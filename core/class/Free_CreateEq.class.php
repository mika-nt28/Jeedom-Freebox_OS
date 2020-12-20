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
        switch ($create) {
            case 'airmedia':
                Free_CreateEq::createEq_airmedia($logicalinfo, $templatecore_V4);
                break;
            case 'connexion':
                Free_CreateEq::createEq_connexion($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_connexion_4G($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_connexion_xdsl($logicalinfo, $templatecore_V4);
                break;
            case 'disk':
                Free_CreateEq::createEq_disk_SP($logicalinfo, $templatecore_V4);
                break;
            case 'downloads':
                Free_CreateEq::createEq_download($logicalinfo, $templatecore_V4);
                break;
            case 'parental':
                Free_CreateEq::createEq_parental($logicalinfo, $templatecore_V4);
                break;
            case 'network':
                Free_CreateEq::createEq_network_interface($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_network_SP($logicalinfo, $templatecore_V4, 'LAN', $IsVisible);
                break;
            case 'netshare':
                Free_CreateEq::createEq_netshare($logicalinfo, $templatecore_V4);
                break;
            case 'networkwifiguest':
                Free_CreateEq::createEq_network_interface($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_network_SP($logicalinfo, $templatecore_V4, 'WIFIGUEST', $IsVisible);
                break;
            case 'phone':
                Free_CreateEq::createEq_phone($logicalinfo, $templatecore_V4);
                break;
            case 'system':
                Free_CreateEq::createEq_system($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_system_lan($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_system_SP($logicalinfo, $templatecore_V4);
                break;
            case 'wifi':
                Free_CreateEq::createEq_wifi($logicalinfo, $templatecore_V4);
                break;
            default:
                log::add('Freebox_OS', 'debug', '================= ORDRE DE LA CREATION DES EQUIPEMENTS STANDARDS  ==================');
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['airmediaName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['connexionName'] . ' / 4G' . ' / Fibre' . ' / xdsl');
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['diskName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['phoneName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['systemName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['networkName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['networkwifiguestName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['netshareName']);
                log::add('Freebox_OS', 'debug', '================= ' . $logicalinfo['wifiName']);
                log::add('Freebox_OS', 'debug', '================= ENSEMBLE DES PLAYERS SOUS TENSION');
                log::add('Freebox_OS', 'debug', '====================================================================================');
                Free_CreateEq::createEq_airmedia($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_connexion($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_connexion_4G($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_connexion_xdsl($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_disk($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_download($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_phone($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_system($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_system_lan($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_system_SP($logicalinfo, $templatecore_V4);
                if (config::byKey('TYPE_FREEBOX_MODE', 'Freebox_OS') == 'router') {
                    Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4, 'LAN');
                    Free_CreateEq::createEq_network($logicalinfo, $templatecore_V4, 'WIFIGUEST');
                }
                Free_CreateEq::createEq_netshare($logicalinfo, $templatecore_V4);
                Free_CreateEq::createEq_wifi($logicalinfo, $templatecore_V4);
                // TEST
                //Free_CreateEq::createEq_notification($logicalinfo, $templatecore_V4);
                //Free_CreateEq::createEq_upload($logicalinfo, $templatecore_V4);
                break;
        }
    }
    private static function createEq_airmedia($logicalinfo, $templatecore_V4)
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
    private static function createEq_airmedia_sp($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement spécifique : ' . $logicalinfo['airmediaName']);
        $Free_API = new Free_API();
        $Free_API->universal_get('airmedia', null, null, null);
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_connexion($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['connexionName']);
        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
        };
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('connexion', null, null, 'ftth');
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
        $Connexion->AddCommand('Débit descendant', 'rate_down', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  1, '0', $updateicon, true, null, null, null, '#value# / 1024', '2');
        $Connexion->AddCommand('Débit montant', 'rate_up', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  2, '0', $updateicon, true, null, null, null, '#value# / 1024', '2');
        $Connexion->AddCommand('Débit descendant (max)', 'bandwidth_down', 'info', 'numeric', $templatecore_V4 . 'badge', $_bandwidth_down_unit, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  3, '0', $updateicon, true, null, null, null, $_bandwidth_value_down, '2');
        $Connexion->AddCommand('Débit montant (max)', 'bandwidth_up', 'info', 'numeric', $templatecore_V4 . 'badge', $_bandwidth_up_unit, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  4, '0', $updateicon, true, null, null, null, $_bandwidth_value_up, '2');
        $Connexion->AddCommand('Type de connexion', 'media', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  5, '0', $updateicon, true);
        $Connexion->AddCommand('Etat de la connexion', 'state', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  6, '0', $updateicon, true);
        $Connexion->AddCommand('IPv4', 'ipv4', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  7, '0', $updateicon, true);
        $Connexion->AddCommand('IPv6', 'ipv6', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  8, '0', $updateicon, true);
        $Connexion->AddCommand('Réponse Ping', 'ping', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  9, '0', $updateicon, true);
        $Connexion->AddCommand('Proxy Wake on Lan', 'wol', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  10, '0', $updateicon, true);
        log::add('Freebox_OS', 'debug', '└─────────');
        if ($result['sfp_present'] != null) {
            Free_CreateEq::createEq_connexion_FTTH($logicalinfo, $templatecore_V4);
        }
        log::add('Freebox_OS', 'debug', '│──────────> ' . $_modul);
    }
    private static function createEq_connexion_FTTH($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes Spécifique Fibre : ' . $logicalinfo['connexionName']);

        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
        };
        $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *');
        $Connexion->AddCommand('Type de connexion Fibre', 'link_type', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  20, '0', $updateicon, true);
        $Connexion->AddCommand('Module Fibre présent', 'sfp_present', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  21, '0', $updateicon, true);
        $Connexion->AddCommand('Signal Fibre présent', 'sfp_has_signal', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  22, '0', $updateicon, true);
        $Connexion->AddCommand('Etat Alimentation', 'sfp_alim_ok', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  23, '0', $updateicon, true);
        $Connexion->AddCommand('Puissance transmise', 'sfp_pwr_tx', 'info', 'numeric', $templatecore_V4 . 'badge', 'dBm', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  24, '0', $updateicon, true, null, null, null, '#value# / 100', '2');
        $Connexion->AddCommand('Puissance reçue', 'sfp_pwr_rx', 'info', 'numeric', $templatecore_V4 . 'badge', 'dBm', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  25, '0', $updateicon, true, null, null, null, '#value# / 100', '2');

        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_connexion_4G($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes Spécifique 4G : ' . $logicalinfo['connexionName']);
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('connexion', null, null, 'lte/config');
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
    private static function createEq_connexion_xdsl($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes Spécifique xdsl : ' . $logicalinfo['connexionName']);
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('connexion', null, null, 'xdsl');
        if ($result != false && $result != 'Aucun module 4G détecté') {
            $_modul = 'Module xdsl : Présent';
            log::add('Freebox_OS', 'debug', '│──────────> ' . $_modul);
            $Connexion = Freebox_OS::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null, '*/15 * * * *');
            $Connexion->AddCommand('Type de modulation', 'modulation', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  40, '0', null, true);
            $Connexion->AddCommand('Protocole', 'protocol', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  41, '0', null, true);
        } else {
            $_modul = 'Module xdsl : Non Présent';
            log::add('Freebox_OS', 'debug', '│──────────> ' . $_modul);
        }

        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_disk($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : ' . $logicalinfo['diskName']);
        Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *');
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_disk_SP($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement spécifique : ' . $logicalinfo['diskName']);
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('disk', null, null, null);
        if ($result == 'auth_required') {
            $result = $Free_API->universal_get('disk', null, null, null);
        }
        $disk = Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null, null, '5 */12 * * *');
        if ($result != false) {
            foreach ($result['result'] as $disks) {
                foreach ($disks['partitions'] as $partition) {
                    log::add('Freebox_OS', 'debug', '│──────────> Disque [' . $disks['type'] . '] - ' . $disks['id'] . ' - Partitions : ' . $partition['label'] . ' -  id ' . $partition['id']);
                    $disk->AddCommand($partition['label'] . ' - ' . $disks['type'] . ' - ' . $partition['fstype'], $partition['id'], 'info', 'numeric', 'core::horizontal', '%', null, 1, 'default', 'default', 0, 'fas fa-hdd fa-2x', 0, '0', 100, null, '0', false, false, 'never', null, true, '#value#*100', 2);
                }
            }
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_download($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : ' . $logicalinfo['downloadsName']);
        $updateicon = false;
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
            $iconDownloadsOn = 'fas fa-play';
            $iconDownloadsOff = 'fas fa-stop';
            $iconRSSnb = 'fas fa-rss';
            $iconRSSread = 'fas fa-rss-square';
            $iconconn_ready = 'fas fa-ethernet';
            $iconthrottling_is_scheduled = 'far fa-calendar-alt';
            $Templatemode = 'default';
            $iconDownloadsnormal = 'fas fa-rocket';
            $iconDownloadsslow = 'fas fa-download';
            $iconDownloadshibernate = 'far fa-pause-circle';
            $iconDownloadsschedule  = 'far fa-calendar-alt';
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $iconDownloadsOn = 'fas fa-play icon_green';
            $iconDownloadsOff = 'fas fa-stop icon_red';
            $iconRSSnb = 'fas fa-rss icon_green';
            $iconRSSread = 'fas fa-rss-square icon_orange';
            $iconconn_ready = 'fas fa-ethernet icon_green';
            $iconthrottling_is_scheduled = 'far fa-calendar-alt icon_green';
            $Templatemode = 'Freebox_OS::Mode Téléchargement';
            $iconDownloadsnormal = 'fas fa-rocket icon_green';
            $iconDownloadsslow = 'fas fa-download icon_green';
            $iconDownloadshibernate = 'far fa-pause-circle icon_red';
            $iconDownloadsschedule = 'far fa-calendar-alt icon_green';
        };
        $downloads = Freebox_OS::AddEqLogic($logicalinfo['downloadsName'], $logicalinfo['downloadsID'], 'multimedia', false, null, null, null, '5 */12 * * *');
        $downloads->AddCommand('Nb de tâche(s)', 'nb_tasks', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  1, '0', $updateicon, true);
        $downloads->AddCommand('Nb de tâche(s) active', 'nb_tasks_active', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  2, '0', $updateicon, true);
        $downloads->AddCommand('Nb de tâche(s) en extraction', 'nb_tasks_extracting', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  3, '0', $updateicon, true);
        $downloads->AddCommand('Nb de tâche(s) en réparation', 'nb_tasks_repairing', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  4, '0', $updateicon, true);
        $downloads->AddCommand('Nb de tâche(s) en vérification', 'nb_tasks_checking', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  5, '0', $updateicon, true);
        $downloads->AddCommand('Nb de tâche(s) en attente', 'nb_tasks_queued', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  6, '0', $updateicon, true);
        $downloads->AddCommand('Nb de tâche(s) en erreur', 'nb_tasks_error', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  7, '0', $updateicon, true);
        $downloads->AddCommand('Nb de tâche(s) stoppée(s)', 'nb_tasks_stopped', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  8, '0', $updateicon, true);
        $downloads->AddCommand('Nb de tâche(s) terminée(s)', 'nb_tasks_done', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  9, '0', $updateicon, true);
        $downloads->AddCommand('Téléchargement en cours', 'nb_tasks_downloading', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 10, '0', $updateicon, true);
        $downloads->AddCommand('Vitesse réception', 'rx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 11, '0', $updateicon, true, null, null, null, '#value# / 1000', '2');
        $downloads->AddCommand('Vitesse émission', 'tx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  12, '0', $updateicon, true, null, null, null, '#value# / 1000', '2');
        $downloads->AddCommand('Start Téléchargement', 'start_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsOn, 0, 'default', 'default',  13, '0', $updateicon, false);
        $downloads->AddCommand('Stop Téléchargement', 'stop_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsOff, 0, 'default', 'default',  14, '0', $updateicon, false);
        $downloads->AddCommand('Nb de flux RSS', 'nb_rss', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconRSSnb, 0, 'default', 'default',  15, '0', $updateicon, false, null, true);
        $downloads->AddCommand('Nb de flux RSS Non Lu', 'nb_rss_items_unread', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconRSSread, 0, 'default', 'default',  16, '0', $updateicon, false, null, true);
        $downloads->AddCommand('Etat connexion', 'conn_ready', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconconn_ready, 0, 'default', 'default',  17, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Etat Planning', 'throttling_is_scheduled', 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconthrottling_is_scheduled, 0, 'default', 'default',  18, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Mode Téléchargement', 'mode', 'info', 'string', $Templatemode, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  19, '0', $updateicon, true, null, true);
        $downloads->AddCommand('Activer mode normal', 'normal', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsnormal, 0, 'default', 'default',  20, '0', $updateicon, false, null, true);
        $downloads->AddCommand('Activer mode lent', 'slow', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsslow, 0, 'default', 'default',  21, '0', $updateicon, false, null, true);
        $downloads->AddCommand('Activer mode Stop', 'hibernate', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadshibernate, 0, 'default', 'default',  22, '0', $updateicon, false, null, true);
        $downloads->AddCommand('Activer mode Planning', ' schedule', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconDownloadsschedule, 0, 'default', 'default',  23, '0', $updateicon, false, null, true);
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_parental($logicalinfo, $templatecore_V4)
    {
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('parentalprofile');
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

            $parental = Freebox_OS::AddEqLogic($Equipement['name'], 'parental_' . $Equipement['id'], $category, true, 'parental', null, $Equipement['id'], '*/5 * * * *');
            $StatusParental = $parental->AddCommand('Etat', $Equipement['id'], "info", 'string', $Templateparent, null, null, 1, '', '', '', '', 0, 'default', 'default', 1, 1, false, true, null, true);
            $parental->AddCommand('Autoriser', 'allowed', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconparent_allowed, 0, 'default', 'default', 2, '0', false, false, null, true);
            $parental->AddCommand('Bloquer', 'denied', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconparent_denied, 0, 'default', 'default', 3, '0', false, false, null, true);
            $parental->AddCommand('Autoriser-Bloquer Temporairement', 'tempDenied', 'action', 'select', null, null, null, 1, $StatusParental, 'parentalStatus', '', $iconparent_temp, 0, 'default', 'default', 4, '0', false, false, '', true);
            log::add('Freebox_OS', 'debug', '└─────────');
        }
    }
    private static function createEq_phone($logicalinfo, $templatecore_V4)
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

    private static function createEq_network($logicalinfo, $templatecore_V4, $_network = 'LAN')
    {
        if ($_network == 'LAN') {
            $_networkname = $logicalinfo['networkName'];
            $_networkID = $logicalinfo['networkID'];
        } else if ($_network == 'WIFIGUEST') {
            $_networkname = $logicalinfo['networkwifiguestName'];
            $_networkID = $logicalinfo['networkwifiguestID'];
        }
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : ' . $_networkname);
        Freebox_OS::AddEqLogic($_networkname, $_networkID, 'default', false, null, null, null, '*/5 * * * *');
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_netshare($logicalinfo, $templatecore_V4)
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
        while ($boucle_num <= 4) {
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
    private static function createEq_network_interface($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Recherche des Interfaces réseaux ');
        $Free_API = new Free_API();
        $Free_API->universal_get('network', null, null, 'browser/interfaces');
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_network_SP($logicalinfo, $templatecore_V4, $_network = 'LAN', $IsVisible = true)
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
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
            $icon_search = 'fas fa-search-plus icon_green';
            $icon_wol = 'fas fa-broadcast-tower icon_orange';
            $icon_dhcp = 'fas fa-network-wired icon_blue';
        };
        $updateWidget = false;
        if ($IsVisible == true) {
            $_IsVisible = 1;
        } else {
            $_IsVisible = '0';
        }
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes spécifiques : ' . $_networkname);
        $Free_API = new Free_API();
        $network = Freebox_OS::AddEqLogic($_networkname, $_networkID, 'default', false, null, null, null, '*/5 * * * *');
        $network->AddCommand('Ajouter supprimer IP Fixe', 'add_del_mac', 'action', 'message',  'default', null, null, 0, 'default', 'default', 0, $icon_dhcp, 0, 'default', 'default',  -3, '0', true, false, null, true, null, null, null, null, null, 'add_del_dhcp?mac_address=#mac#&ip=#ip#&comment=#comment#&name=#name#&function=#function#&type=#type#');
        $network->AddCommand('Rechercher les nouveaux appareils', 'search', 'action', 'other',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, $icon_search, 0, 'default', 'default',  -2, '0', true, false, null, true);
        $network->AddCommand('Wake on LAN', 'WakeonLAN', 'action', 'message',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, $icon_wol, 0, 'default', 'default',  -1, '0', true, false, null, true, null, null, null, null, null, 'wol?mac_address=#mac#&password=#password#');
        $result = $Free_API->universal_get('network', null, null, 'browser/' . $_networkinterface);


        if (isset($result['result'])) {

            foreach ($result['result'] as $Equipement) {
                if ($Equipement['primary_name'] != '') {
                    $Command = $network->AddCommand($Equipement['primary_name'], $Equipement['id'], 'info', 'binary', 'Freebox_OS::Network', null, null, $_IsVisible, 'default', 'default', 0, null, 0, 'default', 'default', null, '0', $updateWidget, true);
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

    private static function createEq_notification($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : ' . $logicalinfo['notificationName']);
        $Free_API = new Free_API();
        $Free_API->universal_get('notification', null, null);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_system($logicalinfo, $templatecore_V4)
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
    private static function createEq_system_lan($logicalinfo, $templatecore_V4)
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

    private static function createEq_system_SP($logicalinfo, $templatecore_V4)
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
            log::add('Freebox_OS', 'debug', '│──────────> Boucle pour Update : ' . $boucle_update);
            foreach ($Free_API->universal_get('system', null, $boucle_update) as $Equipement) {
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
            $boucle_num++;
        }
        log::add('Freebox_OS', 'debug', '└─────────');
    }

    private static function createEq_wifi($logicalinfo, $templatecore_V4)
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
        $Wifi->AddCommand('Wifi WPS On', 'wifiWPSOn', 'action', 'other', $TemplateWifiWPSOnOFF, null, 'LIGHT_ON', 1, $WifiWPS, 'wifiWPS', 0, $iconWifiWPSOn, 0, 'default', 'default', 14, '0', $updateicon, false);
        $Wifi->AddCommand('Wifi WPS Off', 'wifiWPSOff', 'action', 'other', $TemplateWifiWPSOnOFF, null, 'LIGHT_OFF', 1, $WifiWPS, 'wifiWPS', 0, $iconWifiWPSOff, 0, 'default', 'default', 15, '0', $updateicon, false);
        log::add('Freebox_OS', 'debug', '└─────────');
        Free_CreateEq::createEq_wifi_bss($logicalinfo, $templatecore_V4, $Wifi);
        Free_CreateEq::createEq_mac_filter($logicalinfo, $templatecore_V4, $Wifi);
        Free_CreateEq::createEq_wifi_ap($logicalinfo, $templatecore_V4, $Wifi);
    }

    private static function createEq_wifi_ap($logicalinfo, $templatecore_V4, $Wifi)
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
        $result = $Free_API->universal_get('wifi', null, null, 'ap');

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


    private static function createEq_wifi_bss($logicalinfo, $templatecore_V4, $Wifi)
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
        $Wifi->AddCommand('Wifi Session WPS Off (toutes les sessions)', 'wifiSessionWPSOff', 'action', 'other', null, null, 'LIGHT_OFF', 1, null, null, 0, $iconWifiSessionWPSOff, true, 'default', 'default', $order, '0', $updateicon, false, false, true);
        $order++;
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('wifi', null, null, 'bss');

        if ($result != false) {
            foreach ($result as $wifibss) {
                if ($wifibss['config']['wps_enabled'] != true) continue;
                $Wifi->AddCommand('On Session WPS ' . $wifibss['shared_bss_params']['ssid'], $wifibss['id'], 'action', 'other', null, null, 'LIGHT_ON', 1, null, null, 0, $iconWifiSessionWPSOn, true, 'default', 'default', $order, '0', $updateicon, false, false, true);
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

    private static function createEq_mac_filter($logicalinfo, $templatecore_V4, $Wifi)
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
        $Wifi->AddCommand('Mode de filtrage', 'mac_filter_state', 'action', 'select', null, null, null, 1, $Statutmac, 'wifimac_filter_state', null, $iconmac_filter_state, 0, 'default', 'default', $order, '0', false, false, null, true);
        $order++;
        $Wifi->AddCommand('Ajout - Supprimer filtrage Mac', 'add_del_mac', 'action', 'message',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, $iconmac_add_del_mac, 0, 'default', 'default',  $order, '0', true, false, null, true, null, null, null, null, null, 'add_del_mac?mac_address=#mac_address#&function=#function#&filter=#filter#&comment=#comment#');
        $order++;
        $Wifi->AddCommand('Liste Mac Blanche', 'listwhite', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmac_list_white, 0, 'default', 'default',  $order, '0', null, true, false, true, null, null, null, null);
        $order++;
        $Wifi->AddCommand('Liste MAC Noire', 'listblack', 'info', 'string', null, null, null, 1, 'default', 'default', 0, $iconmac_list_black, 0, 'default', 'default',  $order, '0', null, true, false, true, null, null, null, null);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
    private static function createEq_upload($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Création équipement : ' . $logicalinfo['notificationName']);
        $Free_API = new Free_API();
        $Free_API->universal_get('upload', null, null);
        log::add('Freebox_OS', 'debug', '└─────────');
    }
}
