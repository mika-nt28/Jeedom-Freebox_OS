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

class Free_Template
{
	public static function getTemplate()
	{
		// Template pour le Wifi (action)
		$return = array('action' => array('other' => array()));
		$return['action']['other']['Wifi'] = array(
			'template' => 'tmplicon',
			'display' => array(
				'#icon#' => '<i class=\'icon_blue icon fas fa-wifi\'></i>',
			),
			'replace' => array(
				'#_icon_on_#' => '<i class=\'icon_green icon fas fa-wifi\'></i>',
				'#_icon_off_#' => '<i class=\'icon_red icon fas fa-times\'></i>',
				'#_time_widget_#' => '1'
			)
		);

		// Template pour le planning Wifi (action)
		$return['action']['other']['Planning Wifi'] = array(
			'template' => 'tmplicon',
			'display' => array(
				'#icon#' => '<i class=\'icon_blue icon fas fa-calendar-alt\'></i>',
			),
			'replace' => array(
				'#_icon_on_#' => '<i class=\'icon_green icon fas fa-calendar-alt\'></i>',
				'#_icon_off_#' => '<i class=\'icon_red icon fas fa-calendar-times\'></i>',
				'#_time_widget_#' => '1'
			)
		);
		// Template pour le 4G (action)
		$return['action']['other']['4G'] = array(
			'template' => 'tmplicon',
			'display' => array(
				'#icon#' => '<i class=\'icon_blue icon fas fa-broadcast-tower\'></i>',
			),
			'replace' => array(
				'#_icon_on_#' => '<i class=\'icon_green icon fas fa-broadcast-tower\'></i>',
				'#_icon_off_#' => '<i class=\'icon_red icon fas fa-broadcast-tower\'></i>',
				'#_time_widget_#' => '1'
			)
		);
		// Template pour le Wifi Wps (action)
		$return['action']['other']['Wfi WPS'] = array(
			'template' => 'tmplicon',
			'display' => array(
				'#icon#' => '<i class=\'icon_orange icon fas fa-broadcast-tower\'></i>',
			),
			'replace' => array(
				'#_icon_on_#' => '<i class=\'icon_green icon fas fa-broadcast-tower\'></i>',
				'#_icon_off_#' => '<i class=\'icon_red icon fas fa-broadcast-tower\'></i>',
				'#_time_widget_#' => '1'
			)
		);
		// Template pour l'état du contrôle Parental' (info)
		$return['info']['string']['Parental'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_time_widget_#' => '1'),
			'test' => array(
				array('operation' => "#value# == 'allowed'", 'state_light' => '<i class=\'icon_green icon fas fa-user-check\'></i>'),
				array('operation' => "#value# == 'denied'", 'state_light' => '<i class=\'icon_red icon fas fa-user-lock\'></i>'),
				array('operation' => "#value# == 'webonly'", 'state_light' => '<i class=\'icon_orange icon fas fa-user-shield\'></i>')
			)
		);
		// Template pour l'état du contrôle Player' (info)
		$return['info']['string']['Player'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_time_widget_#' => '1'),
			'test' => array(
				array('operation' => "#value# == 'standby'", 'state_light' => '<i class=\'icon_red icon fas fa-power-off\'></i>'),
				array('operation' => "#value# == 'running'", 'state_light' => '<i class=\'icon_green icon fas fa-power-off\'></i>'),
				array('operation' => "#value# == ''", 'state_light' => '<i class=\'icon_orange icon fas fa-question\'></i>')
			)
		);
		// Template pour l'état de l'alarme' (info)
		$return['info']['string']['Alarme Freebox'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_time_widget_#' => '1'),
			'test' => array(
				array('operation' => "#value# == 'idle'", 'state_light' => '<i class=\'icon_green icon jeedom-lock-ouvert\'></i>'),
				array('operation' => "#value# == 'alarm2_armed'", 'state_light' => '<i class=\'icon_red icon nature-night2\'></i>'),
				array('operation' => "#value# == 'alarm1_armed'", 'state_light' => '<i class=\'icon_red icon jeedom-lock-ferme\'></i>'),
				array('operation' => "#value# == 'alarm1_arming'", 'state_light' => '<i class=\'icon_orange icon jeedom-lock-partiel\'></i>'),
				array('operation' => "#value# == 'alarm2_arming'", 'state_light' => '<i class=\'icon_orange icon jeedom-lock-partiel\'></i>'),
				array('operation' => "#value# == 'alarm1_alert_timer'", 'state_light' => '<i class=\'icon_red icon far fa-clock\'></i>'),
				array('operation' => "#value# == 'alarm2_alert_timer'", 'state_light' => '<i class=\'icon_red icon far fa-clock\'></i>'),
				array('operation' => "#value# == 'alert'", 'state_light' => '<i class=\'icon_red icon jeedom-alerte2\'></i>')
			)
		);
		// Template pour l'état de l'alarme' (info)
		$return['info']['numeric']['Télécommande Freebox'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_time_widget_#' => '1'),
			'test' => array(
				array('operation' => "#value# == ''", 'state_light' => '<i class=\'icon_green icon jeedom-lock-ouvert\'></i>'),
				array('operation' => "#value# == 2", 'state_light' => '<i class=\'icon_green icon jeedom-lock-ouvert\'></i>'),
				array('operation' => "#value# == 3", 'state_light' => '<i class=\'icon_red icon nature-night2\'></i>'),
				array('operation' => "#value# == 1", 'state_light' => '<i class=\'icon_red icon jeedom-lock-ferme\'></i>')
			)
		);

		// Template pour l'état du mode de téléchargement' (info)
		$return['info']['string']['Mode Téléchargement'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_time_widget_#' => '1'),
			'test' => array(
				array('operation' => "#value# == 'normal'", 'state_light' => '<i class=\'icon_green icon fas fa-rocket\'></i>'),
				array('operation' => "#value# == 'slow'", 'state_light' => '<i class=\'icon_green icon fas fa-download\'></i>'),
				array('operation' => "#value# == 'hibernate'", 'state_light' => '<i class=\'icon_red icon far fa-pause-circle\'></i>'),
				array('operation' => "#value# == 'schedule'", 'state_light' => '<i class=\'icon_green icon far fa-calendar-alt\'></i>')
			)
		);
		return $return;
	}
}
