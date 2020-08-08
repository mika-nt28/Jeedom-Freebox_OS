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

class Free_CreateTV
{

    public static function createTV($create = 'default')
    {
        $logicalinfo = Freebox_OS::getlogicalinfo();
        if (version_compare(jeedom::version(), "4", "<")) {
            $templatecore_V4 = null;
        } else {
            $templatecore_V4  = 'core::';
        };
    }
    private static function createTV_function1($logicalinfo, $templatecore_V4)
    {
        log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : Fonction');
        if (version_compare(jeedom::version(), "4", "<")) {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
        } else {
            log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
        };

        log::add('Freebox_OS', 'debug', '└─────────');
    }
}
