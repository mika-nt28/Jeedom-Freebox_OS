<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
include_file('core', 'FreeboxAPI', 'class', 'Freebox_OS');

class Freebox_OS extends eqLogic
{
	public static function deamon_info()
	{
		$return = array();
		$return['log'] = 'Freebox_OS';
		if (trim(config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS')) != '' && config::byKey('FREEBOX_SERVER_APP_TOKEN', 'Freebox_OS') != '' && trim(config::byKey('FREEBOX_SERVER_APP_ID', 'Freebox_OS')) != '') {
			$return['launchable'] = 'ok';
		} else {
			$return['launchable'] = 'nok';
		}
		$return['state'] = 'ok';
		$session_token = cache::byKey('Freebox_OS::SessionToken');
		if (!is_object($session_token) || $session_token->getValue('') == '') {
			$return['state'] = 'nok';
			return $return;
		}
		$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
		if (!is_object($cron)) {
			$return['state'] = 'nok';
			return $return;
		}
		foreach (eqLogic::byType('Freebox_OS') as $Equipement) {
			if ($Equipement->getIsEnable() && count($Equipement->getCmd()) > 0) {
				$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation', array('Freebox_id' => $Equipement->getId()));
				if (!is_object($cron) || !$cron->running()) {
					$return['state'] = 'nok';
					return $return;
				}
			}
		}
		return $return;
	}
	public static function deamon_start($_debug = false)
	{
		log::remove('Freebox_OS');
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') return;
		if ($deamon_info['state'] == 'ok') return;
		$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('Freebox_OS');
			$cron->setFunction('RefreshToken');
			$cron->setEnable(1);
			$cron->setSchedule('15 * * * *');
			$cron->setTimeout('1');
			$cron->save();
		}
		$cron->start();
		$cron->run();
		foreach (eqLogic::byType('Freebox_OS') as $Equipement) {
			if ($Equipement->getIsEnable() && count($Equipement->getCmd()) > 0) {
				$Equipement->CreateDemon();
			}
		}
	}
	public static function deamon_stop()
	{
		$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
		if (is_object($cron)) {
			$cron->stop();
			$cron->remove();
		}
		foreach (eqLogic::byType('Freebox_OS') as $Equipement) {
			$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation', array('Freebox_id' => $Equipement->getId()));
			if (is_object($cron)) {
				$cron->stop();
				$cron->remove();
			}
		}
		$FreeboxAPI = new FreeboxAPI();
		$FreeboxAPI->close_session();
	}
	private function CreateDemon()
	{
		$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation', array('Freebox_id' => $this->getId()));
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('Freebox_OS');
			$cron->setFunction('RefreshInformation');
			$cron->setOption(array('Freebox_id' => $this->getId()));
			$cron->setEnable(1);
			$cron->setDeamon(1);
			$cron->setSchedule('* * * * *');
			$cron->setTimeout('1');
			$cron->save();
		}
		$cron->start();
		$cron->run();
		return $cron;
	}
	public static function AddEqLogic($Name, $_logicalId, $category = null, $tiles, $eq_type, $eq_action)
	{
		$EqLogic = self::byLogicalId($_logicalId, 'Freebox_OS');
		if (!is_object($EqLogic)) {
			$EqLogic = new Freebox_OS();
			$EqLogic->setLogicalId($_logicalId);
			$EqLogic->setObject_id(null);
			$EqLogic->setEqType_name('Freebox_OS');
			$EqLogic->setIsEnable(1);
			$EqLogic->setIsVisible(0);
			$EqLogic->setName($Name);
			if ($category != null) {
				$EqLogic->setcategory($category, 1);
			}
			$EqLogic->setConfiguration('waite', '300');
			$EqLogic->save();
		}
		$EqLogic->setConfiguration('logicalID', $_logicalId);
		if ($tiles == true) {
			$EqLogic->setConfiguration('type', $eq_type);
			$EqLogic->setConfiguration('action', $eq_action);
		}
		$EqLogic->save();
		return $EqLogic;
	}
	public static function templateWidget()
	{
		// Template pour le wifi action
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

		// Template pour le planning action
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
		// Template pour le 4G action
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
		// Template pour l'état du contrôle Parental'
		$return['info']['string']['Parental'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_time_widget_#' => '1'),
			'test' => array(
				array('operation' => "#value# == 'allowed'", 'state_light' => '<i class=\'icon_green icon fas fa-user-check\'></i>'),
				array('operation' => "#value# == 'denied'", 'state_light' => '<i class=\'icon_red icon fas fa-user-lock\'></i>'),
				array('operation' => "#value# == 'webonly'", 'state_light' => '<i class=\'icon_orange icon fas fa-user-shield\'></i>')
			)
		);
		// Template pour l'état du contrôle Player'
		$return['info']['string']['Player'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_time_widget_#' => '1'),
			'test' => array(
				array('operation' => "#value# == 'standby'", 'state_light' => '<i class=\'icon_red icon fas fa-power-off\'></i>'),
				array('operation' => "#value# == 'running'", 'state_light' => '<i class=\'icon_green icon fas fa-power-off\'></i>'),
				array('operation' => "#value# == ''", 'state_light' => '<i class=\'icon_orange icon fas fa-question\'></i>')
			)
		);
		// Template pour l'état de l'alarme'
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
		// Template pour l'état de l'alarme'
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
		return $return;
	}
	public static function addReseau()
	{
		$FreeboxAPI = new FreeboxAPI();
		$Reseau = self::AddEqLogic('Appareils connectés', 'Reseau', 'multimedia', false, null, null);
		log::add('Freebox_OS', 'debug', '>───────── Commande trouvée pour le réseau');
		foreach ($FreeboxAPI->universal_get('reseau') as $Equipement) {
			if ($Equipement['primary_name'] != '') {
				$Command = $Reseau->AddCommand($Equipement['primary_name'], $Equipement['id'], 'info', 'binary', 'Freebox_OS::Freebox_OS_Reseau', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', null, '0', false, true);
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
				if ($Command->execCmd() != $Equipement['active']) {
					$Command->setCollectDate(date('Y-m-d H:i:s'));
					$Command->setConfiguration('doNotRepeatEvent', 1);
					$Command->event($Equipement['active']);
				}
				$Command->save();
			}
		}
	}
	public static function addHomeAdapters()
	{
		$FreeboxAPI = new FreeboxAPI();
		$HomeAdapters = self::AddEqLogic('Home Adapters', 'HomeAdapters', 'default', false, null, null);
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$templatecore_V4 = null;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$templatecore_V4  = 'core::';
		};
		foreach ($FreeboxAPI->universal_get('HomeAdapters') as $Equipement) {
			if ($Equipement['label'] != '') {
				$HomeAdapters->AddCommand($Equipement['label'], $Equipement['id'], 'info', 'binary', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', null, 0, false, false);
				if ($Equipement['status'] == 'active') {
					$HomeAdapters_value = 1;
				} else {
					$HomeAdapters_value = 0;
				}
				$HomeAdapters->checkAndUpdateCmd($Equipement['id'], $HomeAdapters_value);
			}
		}
	}
	public static function addPlayer()
	{
		$FreeboxAPI = new FreeboxAPI();
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3');
			$TemplatePlayer = null;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$TemplatePlayer = 'Freebox_OS::Player';
		};

		$result = $FreeboxAPI->universal_get('player');
		foreach ($result as $Equipement) {
			log::add('Freebox_OS', 'debug', '│──────────> PLAYER : ' . $Equipement['device_name'] . ' -- Id : ' . $Equipement['id']);
			if ($Equipement['id'] != null) {
				$player = self::AddEqLogic($Equipement['device_name'], $Equipement['id'], 'multimedia', true, 'player', null);
				log::add('Freebox_OS', 'debug', '│ Nom : ' . $Equipement['device_name'] . ' -- id :' . $Equipement['id'] . ' -- id :' . $Equipement['mac'] . ' -- id :' . $Equipement['uid']);
			}
			$player->AddCommand('Mac', 'mac', 'info', 'string', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', false, false);
			$player->checkAndUpdateCmd('mac', $Equipement['mac']);
			$player->AddCommand('Etat', 'power_state', 'info', 'string', $TemplatePlayer, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', false, false);
		}
	}
	public static function addSystem()
	{
		$FreeboxAPI = new FreeboxAPI();

		$logicalinfo = Freebox_OS::getlogicalinfo();
		$System = self::AddEqLogic($logicalinfo['systemName'], $logicalinfo['systemID'], 'default', false, null, null);
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3');
			$Template4G = null;
			$templatecore_V4 = null;
			$icontemp = 'fas fa-thermometer-half';
			$iconfan = 'fas fa-fan';
			$icone4Gon = 'fas fa-broadcast-tower';
			$icone4Goff = 'fas fa-broadcast-tower';
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$Template4G = 'Freebox_OS::4G';
			$templatecore_V4  = 'core::';
			$icontemp = 'fas fa-thermometer-half icon_blue';
			$iconfan = 'fas fa-fan icon_blue';
			$icone4Gon = 'fas fa-broadcast-tower icon_green';
			$icone4Goff = 'fas fa-broadcast-tower icon_red';
		};
		$boucle_update = 1; // 1 = sensors - 2 = fans - 3 = extension
		$_order = 6;
		while ($boucle_update <= 3) {
			log::add('Freebox_OS', 'debug', '│──────────> Boucle Update : ' . $boucle_update);
			foreach ($FreeboxAPI->universal_get('system', null, $boucle_update) as $Equipement) {
				$icon = null;
				$_max = 'default';
				$_min = 'default';
				$_unit = null;
				$_name = $Equipement['name'];
				$_id = $Equipement['id'];
				$_value = $Equipement['value'];
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
				} else if ($boucle_update = 3) {
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

					$System->AddCommand($_name, $_id, 'info', $_type, $templatecore_V4 . 'line', $_unit, null, $IsVisible, 'default', $link_logicalId, 0, $icon, 0, $_min, $_max, $_order, 0, false, true, null, $_iconname);

					$System->checkAndUpdateCmd($_id, $_value);
					if ($Equipement['type'] == 'dsl_lte') {
						// Début ajout 4G
						$_4G = $System->AddCommand('Etat 4G ', '4GStatut', "info", 'binary', null . 'line', null, null, 0, '', '', '', '', 1, 'default', 'default', 32, '0', false, 'never', 'System', true);
						$System->AddCommand('4G On', '4GOn', 'action', 'other', $Template4G, null, 'ENERGY_ON', 1, $_4G, '4GStatut', 0, $icone4Gon, 1, 'default', 'default', 33, '0', false, false, 'System', true);
						$System->AddCommand('4G Off', '4GOff', 'action', 'other', $Template4G, null, 'ENERGY_OFF', 1, $_4G, '4GStatut', 0, $icone4Goff, 0, 'default', 'default', 34, '0', false, false, 'System', true);
					}
					$_order++;
				}
			}
			$boucle_update++;
		}
	}
	public static function addparental()
	{
		$FreeboxAPI = new FreeboxAPI();
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$templatecore_V4 = null;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$templatecore_V4  = 'core::';
		};
		foreach ($FreeboxAPI->universal_get('parentalprofile') as $Equipement) {
			log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : Contrôle parental');
			if (version_compare(jeedom::version(), "4", "<")) {
				log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
				$Templateparent = null;
				$iconeparent_allowed = 'fas fa-user-check';
				$iconeparent_denied = 'fas fa-user-lock';
				$iconeparent_webonly = 'fas fa-user-shield';
			} else {
				log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
				$Templateparent = 'Freebox_OS::Parental';
				$iconeparent_allowed = 'fas fa-user-check icon_green';
				$iconeparent_denied = 'fas fa-user-lock icon_red';
				$iconeparent_webonly = 'fas fa-user-shield icon_orange';
			};

			$category = 'default';
			$Equipement['name'] = preg_replace('/\'+/', ' ', $Equipement['name']); // Suppression '

			$parental = self::AddEqLogic($Equipement['name'], $Equipement['id'], $category, true, 'parental', null);
			$StatusParental = $parental->AddCommand('Etat', $Equipement['id'], "info", 'string', $Templateparent, null, null, 1, '', '', '', '', 0, 'default', 'default', 1, 1, false, true, 'parental', true);
			$parental->AddCommand('Autoriser', 'allowed', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconeparent_allowed, 0, 'default', 'default', 2, '0', false, false, 'parental', true);
			$parental->AddCommand('Bloquer', 'denied', 'action', 'other', null, null, null, 1, $StatusParental, 'parentalStatus', 0, $iconeparent_denied, 0, 'default', 'default', 3, '0', false, false, 'parental', true);
			log::add('Freebox_OS', 'debug', '└─────────');
		}
	}
	public static function addTiles()
	{
		$FreeboxAPI = new FreeboxAPI();

		$logicalinfo = Freebox_OS::getlogicalinfo();

		self::AddEqLogic($logicalinfo['homeadaptersName'], $logicalinfo['homeadaptersID'], 'default', false, null, null);
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$templatecore_V4 = null;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$templatecore_V4  = 'core::';
		};
		foreach ($FreeboxAPI->universal_get('tiles') as $Equipement) {
			if ($Equipement['type'] != 'camera') {
				if ($Equipement['type'] == 'alarm_sensor' || $Equipement['type'] == 'alarm_control' || $Equipement['type'] == 'alarm_remote') {
					$category = 'security';
				} elseif ($Equipement['type'] == 'light') {
					$category = 'light';
				} elseif ($Equipement['action'] == 'store' || $Equipement['action'] == 'store_slider') {
					$category = 'opening';
				} else {
					$category = 'default';
				}

				$Equipement['label'] = preg_replace('/\'+/', ' ', $Equipement['label']); // Suppression '
				if (isset($Equipement['label'])) {
					$Tile = self::AddEqLogic($Equipement['label'], $Equipement['node_id'], $category, true, $Equipement['type'], $Equipement['action']);
				} else {
					$Tile = self::AddEqLogic($Equipement['type'], $Equipement['node_id'], $category, true, $Equipement['type'], $Equipement['action']);
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
						log::add('Freebox_OS', 'debug', '┌───────── Caméra trouvée pour l\'équipement FREEBOX : ' . $parameter['name']);
						log::add('Freebox_OS', 'debug', '│ Id : ' . $parameter['id']);
						log::add('Freebox_OS', 'debug', '│ URL : ' . $parameter['url']);
						log::add('Freebox_OS', 'debug', '└─────────');
						event::add('Freebox_OS::camera', json_encode($parameter));
						continue;
					}
					if (!is_object($Tile)) continue;
					log::add('Freebox_OS', 'debug', '┌───────── Commande trouvée pour l\'équipement FREEBOX : ' . $Equipement['label'] . ' (Node ID ' . $Equipement['node_id'] . ')');
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
							$action = $Tile->AddCommand($Command['label'], $Command['ep_id'], 'action', 'other', null, $Command['ui']['unit'], $generic_type, $IsVisible, $Link_I, $Link_I, 0, $icon, 0, 'default', 'default', $order, 0, false, false, $Equipement['type'], $_iconname, $_home_mode_set);
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
									if ($Equipement['action'] == "store_slider") {
										$generic_type = 'FLAP_STATE';
										$Templatecore = $templatecore_V4 . 'shutter';
										$_min = '0';
										$_max = 100;
									} elseif ($Command['name'] == "luminosity" || ($Equipement['action'] == "color_picker" && $Command['name'] == 'v')) {
										$Templatecore_A = $templatecore_V4 . 'light';
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
									if ($Command['name'] == "luminosity" || ($Equipement['action'] == "color_picker" && $Command['name'] == 'v')) {
										if ($Equipement['action'] != 'intensity_picker' && $Equipement['action'] != 'color_picker') {
											$infoCmd = $Tile->AddCommand($label_sup . $name, $Command['ep_id'], 'info', 'numeric', $Templatecore, $Command['ui']['unit'], $generic_type_I, $IsVisible_I, 'default', $link_logicalId, 0, null, 0, $_min, $_max,  null, $IsHistorized, false, true, $Equipement['type']);
											$Link_I_light = $infoCmd;
										}
										$Tile->AddCommand($name, $Command['ep_id'], 'action', 'slider', $Templatecore_A, $Command['ui']['unit'], $generic_type, $IsVisible, $Link_I_light, $link_logicalId, 0, null, 0, $_min, $_max,  2, $IsHistorized, false, false);
									} else {
										$infoCmd = $Tile->AddCommand($label_sup . $name, $Command['ep_id'], 'info', 'numeric', $Templatecore, $Command['ui']['unit'], $generic_type_I, $IsVisible_I, 'default', $link_logicalId, 0, $icon, 0, $_min, $_max, null, $IsHistorized, false, true, $Equipement['type']);
									}

									if ($Equipement['action'] == "color_picker" && $Command['name'] == 'hs') {
										$Tile->AddCommand($name, $Command['ep_id'], 'action', 'slider', $Templatecore_A, $Command['ui']['unit'], $generic_type, $IsVisible, $infoCmd, $link_logicalId, $IsVisible_I, null, 0, $_min, $_max, null, $IsHistorized, false, false, $Equipement['type']);
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
											log::add('Freebox_OS', 'debug', '│ Valeur de Batterie  Nulle : ' . $Command['value']);
											log::add('Freebox_OS', 'debug', '│ PAS DE TRAITEMENT PAR JEEDOM DE L\'ALARME BATTERIE');
										}
									}
								}
								if ($access == "w") {
									if ($Command['name'] != "luminosity" && $Equipement['action'] != "color_picker") {
										$action = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'action', 'slider', null, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', 'default', 0, null, 0, 'default', null, 0, false, false, $Equipement['type']);
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

									$infoCmd = $Tile->AddCommand($Label, $Command['ep_id'], 'info', 'binary', $Templatecore, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', $link_logicalId, $invertBinary, null, 0, 'default', 'default',  $order, 0, false, true, $Equipement['type']);
									$Tile->checkAndUpdateCmd($Command['ep_id'], $Command['value']);
									if ($Equipement['action'] == 'store') {
										$Link_I_store = $infoCmd;
									} elseif ($Equipement['type'] == 'light') {
										$Link_I_light = $infoCmd;
									} else {
										$Link_I_store = 'default';
									}
									if ($Type_command == 'PB') {
										$Tile->AddCommand('On', 'PB_On', 'action', 'other', $Templatecore, $Command['ui']['unit'], 'LIGHT_ON', $IsVisible_PB, $Link_I_light, $Command['ep_id'], $invertBinary, null, 1, 'default', 'default', 3, 0, false, false, $Equipement['type']);
										$Tile->AddCommand('Off', 'PB_Off', 'action', 'other', $Templatecore, $Command['ui']['unit'], 'LIGHT_OFF', $IsVisible_PB, $Link_I_light, $Command['ep_id'], $invertBinary, null, 0, 'default', 'default', 4, 0, false, false, $Equipement['type']);
									}

									$label_sup = null;
									$generic_type = null;
									$Templatecore = null;
									$invertBinary = 0;
								}
								if ($access == "w") {
									if ($Type_command != 'PB') {
										$action = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'action', 'other', null, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', 'default', 0, null, 0, 'default', 'default', 'default', null, 0, false, false, $Equipement['type']);
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
									$info = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'info', 'string', $Templatecore, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', 'default', 0, $icon, 0, 'default', 'default', $order, 0, false, true, $Equipement['type']);
									$Link_I_ALARM = $info;
									if ($Command['name'] == "state" && $Equipement['type'] == 'alarm_control') {
										log::add('Freebox_OS', 'debug', '│──────────> Ajout commande spécifique pour Homebridge');
										$ALARM_ENABLE = $Tile->AddCommand('Actif', 'ALARM_enable', 'info', 'binary', 'core::lock', null, 'ALARM_ENABLE_STATE', 1, 'default', $Command['ep_id'], 0, null, 0, 'default', 'default', 1, 1, false, true, $Equipement['type']);
										$Link_I_ALARM_ENABLE = $ALARM_ENABLE;
										$Tile->AddCommand('Statut', 'ALARM_state', 'info', 'binary', 'core::alert', null, 'ALARM_STATE', 1, 'default', $Command['ep_id'], 1, null, 0, 'default', 'default',  2, 1, false, true, $Equipement['type']);
										$Tile->AddCommand('Mode', 'ALARM_mode', 'info', 'string', null, null, 'ALARM_MODE', 1, 'default', $Command['ep_id'], 0, null, 0, 'default', 'default', 3, 1, false, true, $Equipement['type']);
										log::add('Freebox_OS', 'debug', '│──────────> Fin Ajout commande spécifique pour Homebridge');
									}
									$Tile->checkAndUpdateCmd($Command['ep_id'], $Command['value']);
								}
								$label_sup = null;
								if ($access == "w") {
									$action = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'action', 'message', null, $Command['ui']['unit'], $generic_type, $IsVisible, 'default', 'default', 0, $icon, 0, 'default', 'default', $order, 0, false, false, $Equipement['type']);
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

	public function AddCommand($Name, $_logicalId, $Type = 'info', $SubType = 'binary', $Template = null, $unite = null, $generic_type = null, $IsVisible = 1, $link_I = 'default', $link_logicalId = 'default',  $invertBinary = '0', $icon, $forceLineB = '0', $valuemin = 'default', $valuemax = 'default', $_order = null, $IsHistorized = '0', $forceIcone_widget = false, $repeatevent = false, $_Equipement = null, $_iconname = null, $_home_mode_set = null)
	{
		log::add('Freebox_OS', 'debug', '│ Name: ' . $Name . ' -- Type : ' . $Type . ' -- LogicalID : ' . $_logicalId . ' -- Template Widget / Ligne : ' . $Template . '/' . $forceLineB . '-- Type de générique : ' . $generic_type . ' -- Inverser : ' . $invertBinary . ' -- Icône : ' . $icon . ' -- Min/Max : ' . $valuemin . '/' . $valuemax);

		$Command = $this->getCmd($Type, $_logicalId);
		if (!is_object($Command)) {
			$VerifName = $Name;
			$Command = new Freebox_OSCmd();
			$Command->setId(null);
			$Command->setLogicalId($_logicalId);
			$Command->setEqLogic_id($this->getId());
			$count = 0;
			while (is_object(cmd::byEqLogicIdCmdName($this->getId(), $VerifName))) {
				$count++;
				$VerifName = $Name . '(' . $count . ')';
			}
			$Command->setName($VerifName);

			$Command->setType($Type);
			$Command->setSubType($SubType);

			if ($Template != null) {
				$Command->setTemplate('dashboard', $Template);
				$Command->setTemplate('mobile', $Template);
			}
			if ($unite != null && $SubType == 'numeric') {
				$Command->setUnite($unite);
			}
			$Command->setIsVisible($IsVisible);
			$Command->setIsHistorized($IsHistorized);

			if ($invertBinary != null && $SubType == 'binary') {
				$Command->setdisplay('invertBinary', 1);
			}
			if ($icon != null) {
				$Command->setdisplay('icon', '<i class="' . $icon . '"></i>');
			}
			if ($forceLineB != null) {
				$Command->setdisplay('forceReturnLineBefore', 1);
			}
			if ($_iconname != null) {
				$Command->setdisplay('showIconAndNamedashboard', 1);
			}

			$Command->save();
		}
		if ($generic_type != null) {
			$Command->setGeneric_type($generic_type);
		}
		if ($_home_mode_set != null) { // Compatibilité Homebridge
			$this->setconfiguration($_home_mode_set, $Command->getId() . "|" . $VerifName);
			$this->save(true);
			if ($_home_mode_set == 'SetModeAbsent') {
				$this->setConfiguration('SetModePresent', "NOT");
			} else {
				$this->setconfiguration($_home_mode_set, $Command->getId() . "|" . $VerifName);
			}
			log::add('Freebox_OS', 'debug', '│ Paramétrage du Mode Homebridge Set Mode : ' . $_home_mode_set);
		}
		if ($repeatevent == true && $Type == 'info') {
			$Command->setconfiguration('repeatEventManagement', 'never');
			log::add('Freebox_OS', 'debug', '│ No Repeat pour l\'info avec le nom : ' . $Name);
		}
		if ($valuemin != 'default') {
			$Command->setconfiguration('minValue', $valuemin);
		}
		if ($valuemax != 'default') {
			$Command->setconfiguration('maxValue', $valuemax);
		}
		/*if ($_Equipement != null) {
			$Command->setconfiguration('equipement', $_Equipement);
		}*/

		if (is_object($link_I) && $Type == 'action') {
			$Command->setValue($link_I->getId());
		}
		if ($link_logicalId != 'default') {
			$Command->setconfiguration('logicalId', $link_logicalId);
		}
		if ($_order != null) {
			$Command->setOrder($_order);
		}

		// Forçage pour mettre à jour l'affichage // Option en cas de Update Plugin
		if ($forceIcone_widget == true) {
			if ($icon != null) {
				$Command->setdisplay('icon', '<i class="' . $icon . '"></i>');
			}
			if ($Template != null) {
				$Command->setTemplate('dashboard', $Template);
				$Command->setTemplate('mobile', $Template);
			}
			$Command->setIsVisible($IsVisible);

			if ($forceLineB != null) {
				$Command->setdisplay('forceReturnLineBefore', 1);
			}
		}
		$Command->save();

		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new Freebox_OSCmd();
			$refresh->setLogicalId('refresh');
			$refresh->setIsVisible(1);
			$refresh->setName(__('Rafraichir', __FILE__));
			$refresh->setType('action');
			$refresh->setSubType('other');
			$refresh->setEqLogic_id($this->getId());
			$refresh->save();
		}
		return $Command;
	}
	public static function CreateArchi()
	{
		$logicalinfo = Freebox_OS::getlogicalinfo();

		self::AddEqLogic($logicalinfo['reseauName'], $logicalinfo['reseauID'], 'default', false, null, null);
		self::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null);
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$templatecore_V4 = null;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$templatecore_V4  = 'core::';
		};
		// ADSL - Réeseau
		log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : Réseau');
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$updateiconeADSL = false;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$updateiconeADSL = false;
		};
		$Connexion = self::AddEqLogic($logicalinfo['connexionName'], $logicalinfo['connexionID'], 'default', false, null, null);
		$Connexion->AddCommand('rate down', 'rate_down', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  1, '0', $updateiconeADSL, true);
		$Connexion->AddCommand('rate up', 'rate_up', 'info', 'numeric', $templatecore_V4 . 'badge', 'Ko/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  2, '0', $updateiconeADSL, true);
		$Connexion->AddCommand('bandwidth up', 'bandwidth_up', 'info', 'numeric', $templatecore_V4 . 'badge', 'Mb/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  3, '0', $updateiconeADSL, true);
		$Connexion->AddCommand('bandwidth down', 'bandwidth_down', 'info', 'numeric', $templatecore_V4 . 'badge', 'Mb/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  4, '0', $updateiconeADSL, true);
		$Connexion->AddCommand('media', 'media', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  5, '0', $updateiconeADSL, true);
		$Connexion->AddCommand('state', 'state', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  6, '0', $updateiconeADSL, true);
		log::add('Freebox_OS', 'debug', '└─────────');
		// System
		log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : Système');
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$iconeUpdate = 'fas fa-download';
			$iconeReboot = 'fas fa-sync';
			$updateiconeSystem = false;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$iconeUpdate = 'fas fa-download icon_blue';
			$iconeReboot = 'fas fa-sync icon_red';
			$updateiconeSystem = false;
		};
		$System = self::AddEqLogic($logicalinfo['systemName'], $logicalinfo['systemID'], 'default', false, null, null);
		$System->AddCommand('Update', 'update', 'action', 'other', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconeUpdate, 0, 'default', 'default',  30, '0', $updateiconeSystem, false);
		$System->AddCommand('Reboot', 'reboot', 'action', 'other',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, $iconeReboot, 0, 'default', 'default',  31, '0', $updateiconeSystem, false);
		$System->AddCommand('Freebox firmware version', 'firmware_version', 'info', 'string', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', $updateiconeSystem, true);
		$System->AddCommand('Mac', 'mac', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  2, '0', $updateiconeSystem, true);
		$System->AddCommand('Allumée depuis', 'uptime', 'info', 'string',  $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  3, '0', $updateiconeSystem, true);
		$System->AddCommand('Board name', 'board_name', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  4, '0', $updateiconeSystem, true);
		$System->AddCommand('Serial', 'serial', 'info', 'string',  $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  5, '0', $updateiconeSystem, true);
		$System->AddCommand('Redirection de ports', 'port_forwarding', 'action', 'message', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default', 10, '0', $updateiconeSystem, false);
		log::add('Freebox_OS', 'debug', '└─────────');

		//Wifi
		log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : Wifi');
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$TemplateWifiOnOFF = 'Freebox_OS::Freebox_OS::Wifi';
			$iconeWifiOn = 'fas fa-wifi';
			$iconeWifiOff = 'fas fa-times';
			$iconeWifiPlanningOn = 'fas fa-calendar-alt';
			$iconeWifiPlanningOff = 'fas fa-calendar-times';
			$updateiconeWifi = false;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$TemplateWifiOnOFF = 'Freebox_OS::Wifi';
			$TemplateWifiPlanningOnOFF = 'Freebox_OS::Planning Wifi';
			$iconeWifiOn = 'fas fa-wifi icon_green';
			$iconeWifiOff = 'fas fa-times icon_red';
			$iconeWifiPlanningOn = 'fas fa-calendar-alt icon_green';
			$iconeWifiPlanningOff = 'fas fa-calendar-times icon_red';
			$updateiconeWifi = false;
		};
		$Wifi = self::AddEqLogic($logicalinfo['wifiName'], $logicalinfo['wifiID'], 'default', false, null, null);
		$StatusWifi = $Wifi->AddCommand('Etat wifi', 'wifiStatut', "info", 'binary', null, null, 'ENERGY_STATE', 0, '', '', '', '', 0, 'default', 'default', 1, 1, $updateiconeWifi, true);
		$Wifi->AddCommand('Wifi On', 'wifiOn', 'action', 'other', $TemplateWifiOnOFF, null, 'ENERGY_ON', 1, $StatusWifi, 'wifiStatut', 0, $iconeWifiOn, 0, 'default', 'default', 4, '0', $updateiconeWifi, false);
		$Wifi->AddCommand('Wifi Off', 'wifiOff', 'action', 'other', $TemplateWifiOnOFF, null, 'ENERGY_OFF', 1, $StatusWifi, 'wifiStatut', 0, $iconeWifiOff, 0, 'default', 'default', 5, '0', $updateiconeWifi, false);
		// Planification Wifi
		$PlanningWifi = $Wifi->AddCommand('Etat Planning', 'wifiPlanning', "info", 'binary', null, null, null, 0, '', '', '', '', 0, 'default', 'default', '0', 2, $updateiconeWifi, true);
		$Wifi->AddCommand('Wifi Planning On', 'wifiPlanningOn', 'action', 'other', $TemplateWifiPlanningOnOFF, null, 'ENERGY_ON', 1, $PlanningWifi, 'wifiPlanning', 0, $iconeWifiPlanningOn, 0, 'default', 'default', 6, '0', $updateiconeWifi, false);
		$Wifi->AddCommand('Wifi Planning Off', 'wifiPlanningOff', 'action', 'other', $TemplateWifiPlanningOnOFF, null, 'ENERGY_OFF', 1, $PlanningWifi, 'wifiPlanning', 0, $iconeWifiPlanningOff, 0, 'default', 'default', 7, '0', $updateiconeWifi, false);
		log::add('Freebox_OS', 'debug', '└─────────');
		//Phone
		log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : Téléphone');
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$iconeDectOn = 'jeedom-bell';
			$iconeDectOff = 'jeedom-no-bell';
			$iconeManquee = 'icon techno-phone1';
			$iconeRecus = 'icon techno-phone3';
			$iconePasses = 'ficon techno-phone2';
			$updateiconePhone = false;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$iconeDectOn = 'jeedom-bell icon_red';
			$iconeDectOff = 'jeedom-no-bell icon_green';
			$iconeManquee = 'icon techno-phone1 icon_red';
			$iconeRecus = 'icon techno-phone3 icon_blue';
			$iconePasses = 'icon techno-phone2 icon_green';
			$updateiconePhone = false;
		};
		$Phone = self::AddEqLogic($logicalinfo['phoneName'], $logicalinfo['phoneID'], 'default', false, null, null);
		$Phone->AddCommand('Nombre Appels Manqués', 'nbAppelsManquee', 'info', 'numeric', 'Freebox_OS::Freebox_OS_Phone', null, null, 1, 'default', 'default', 0, $iconeManquee, 0, 'default', 'default',  1, '0', $updateiconePhone, true);
		$Phone->AddCommand('Nombre Appels Reçus', 'nbAppelRecus', 'info', 'numeric', 'Freebox_OS::Freebox_OS_Phone', null, null, 1, 'default', 'default', 0, $iconeRecus, 0, 'default', 'default', 2, '0', $updateiconePhone, true);
		$Phone->AddCommand('Nombre Appels Passés', 'nbAppelPasse', 'info', 'numeric', 'Freebox_OS::Freebox_OS_Phone', null, null, 1, 'default', 'default', 0, $iconePasses, 0, 'default', 'default',  3, '0', $updateiconePhone, true);
		$Phone->AddCommand('Liste Appels Manqués', 'listAppelsManquee', 'info', 'string', 'Freebox_OS::Freebox_OS_Phone', null, null, 1, 'default', 'default', 0, $iconeManquee, 1, 'default', 'default',  6, '0', $updateiconePhone, true);
		$Phone->AddCommand('Liste Appels Reçus', 'listAppelsRecus', 'info', 'string', 'Freebox_OS::Freebox_OS_Phone', null, null, 1, 'default', 'default', 0, $iconeRecus, 0, 'default', 'default', 7, '0', $updateiconePhone, true);
		$Phone->AddCommand('Liste Appels Passés', 'listAppelsPasse', 'info', 'string', 'Freebox_OS::Freebox_OS_Phone', null, null,  1, 'default', 'default', 0, $iconePasses, 0, 'default', 'default',  8, '0', $updateiconePhone, true);
		$Phone->AddCommand('Faire sonner les téléphones DECT', 'sonnerieDectOn', 'action', 'other', 'Freebox_OS::Freebox_OS_Phone', null, null, 1, 'default', 'default', 0, $iconeDectOn, 1, 'default', 'default', 4, '0', $updateiconePhone, false);
		$Phone->AddCommand('Arrêter les sonneries des téléphones DECT', 'sonnerieDectOff', 'action', 'other', 'Freebox_OS::Freebox_OS_Phone', null, null,  1, 'default', 'default', 0, $iconeDectOff, 0, 'default', 'default', 5, '0', $updateiconePhone, false);
		log::add('Freebox_OS', 'debug', '└─────────');
		//Downloads
		log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : Téléchargements');
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$updateiconeDownloads = false;
			$iconeDownloadsOn = 'fas fa-play';
			$iconeDownloadsOff = 'fas fa-stop';
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$iconeDownloadsOn = 'fas fa-play icon_green';
			$iconeDownloadsOff = 'fas fa-stop icon_red';
			$updateiconeDownloads = false;
		};
		$Downloads = self::AddEqLogic($logicalinfo['downloadsName'], $logicalinfo['downloadsID'], 'multimedia', false, null, null);
		$Downloads->AddCommand('Nombre de tâche(s)', 'nb_tasks', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  1, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Nombre de tâche(s) active', 'nb_tasks_active', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  2, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Nombre de tâche(s) en extraction', 'nb_tasks_extracting', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  3, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Nombre de tâche(s) en réparation', 'nb_tasks_repairing', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  4, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Nombre de tâche(s) en vérification', 'nb_tasks_checking', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  5, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Nombre de tâche(s) en attente', 'nb_tasks_queued', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  6, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Nombre de tâche(s) en erreur', 'nb_tasks_error', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  7, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Nombre de tâche(s) stoppée(s)', 'nb_tasks_stopped', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  8, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Nombre de tâche(s) terminée(s)', 'nb_tasks_done', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  9, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Téléchargement en cours', 'nb_tasks_downloading', 'info', 'numeric', $templatecore_V4 . 'line', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 10, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Vitesse réception', 'rx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Mo/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 11, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Vitesse émission', 'tx_rate', 'info', 'numeric', $templatecore_V4 . 'badge', 'Mo/s', null, 1, 'default', 'default', 0, null, 0, 'default', 'default',  12, '0', $updateiconeDownloads, true);
		$Downloads->AddCommand('Start DL', 'start_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconeDownloadsOn, 0, 'default', 'default',  13, '0', $updateiconeDownloads, false);
		$Downloads->AddCommand('Stop DL', 'stop_dl', 'action', 'other', null, null, null, 1, 'default', 'default', 0, $iconeDownloadsOff, 0, 'default', 'default',  14, '0', $updateiconeDownloads, false);
		log::add('Freebox_OS', 'debug', '└─────────');
		// AirPlay
		log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : AirPlay');
		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$iconeAirPlayOn = 'fas fa-play';
			$iconeAirPlayOff = 'fas fa-stop';
			$updateiconeAirPlay = false;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$iconeAirPlayOn = 'fas fa-play icon_green';
			$iconeAirPlayOff = 'fas fa-stop icon_red';
			$updateiconeAirPlay = false;
		};
		$Airmedia = self::AddEqLogic($logicalinfo['airmediaName'], $logicalinfo['airmediaID'], 'multimedia', false, null, null);
		$Airmedia->AddCommand('Player actuel AirMedia', 'ActualAirmedia', 'info', 'string', 'Freebox_OS::Freebox_OS_AirMedia_Recever', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 1, '0', false, true);
		$Airmedia->AddCommand('Start', 'airmediastart', 'action', 'message', 'Freebox_OS::Freebox_OS_AirMedia_Start', null, null, 1, 'default', 'default', 0, $iconeAirPlayOn, 0, 'default', 'default', 2, '0', $updateiconeAirPlay, false);
		$Airmedia->AddCommand('Stop', 'airmediastop', 'action', 'message', 'Freebox_OS::Freebox_OS_AirMedia_Start', null, null, 1, 'default', 'default', 0, $iconeAirPlayOff, 0, 'default', 'default', 3, '0', $updateiconeAirPlay, false);
		log::add('Freebox_OS', 'debug', '└─────────');
		log::add('Freebox_OS', 'debug', '┌───────── Ajout des commandes : Player');
		self::addPlayer();
		log::add('Freebox_OS', 'debug', '└─────────');
		if (config::byKey('FREEBOX_SERVER_TRACK_ID') != '') {
			$FreeboxAPI = new FreeboxAPI();
			$FreeboxAPI->disques();
			$FreeboxAPI->universal_get();
			$FreeboxAPI->universal_get('planning');
			$FreeboxAPI->universal_get('system', null, 4);
			$FreeboxAPI->universal_get('4G');
			$FreeboxAPI->adslStats();
			$FreeboxAPI->nb_appel_absence();
			$FreeboxAPI->universal_get('DownloadStats');
			//self::addReseau();
			//self::addTiles();
			//self::addHomeAdapters();
		}
	}
	public function preSave()
	{
		switch ($this->getLogicalId()) {
			case 'AirPlay':
				$FreeboxAPI = new FreeboxAPI();
				$parametre["enabled"] = $this->getIsEnable();
				$parametre["password"] = $this->getConfiguration('password');
				$FreeboxAPI->airmedia('config', $parametre);
				break;
		}
		if ($this->getConfiguration('waite') == '') {
			$this->setConfiguration('waite', 300);
		}
	}
	public function postSave()
	{
		if ($this->getIsEnable()) {
			$this->CreateDemon();
		} else {
			$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation', array('Freebox_id' => $this->getId()));
			if (is_object($cron)) {
				$cron->stop();
				$cron->remove();
			}
		}
		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new Freebox_OSCmd();
			$refresh->setLogicalId('refresh');
			$refresh->setIsVisible(1);
			$refresh->setName(__('Rafraichir', __FILE__));
			$refresh->setType('action');
			$refresh->setSubType('other');
			$refresh->setEqLogic_id($this->getId());
			$refresh->save();
		}
	}
	public static function RefreshToken()
	{
		$FreeboxAPI = new FreeboxAPI();
		$FreeboxAPI->close_session();
		if ($FreeboxAPI->getFreeboxOpenSession() === false) self::deamon_stop();
	}
	public static function RefreshInformation($_option)
	{
		$FreeboxAPI = new FreeboxAPI();
		$Equipement = eqlogic::byId($_option['Freebox_id']);
		if (is_object($Equipement) && $Equipement->getIsEnable()) {
			while (true) {
				switch ($Equipement->getLogicalId()) {
					case 'AirPlay':
						break;
					case 'ADSL':
						$result = $FreeboxAPI->adslStats();
						if ($result != false) {
							foreach ($Equipement->getCmd('info') as $Command) {
								if (is_object($Command)) {
									switch ($Command->getLogicalId()) {
										case "rate_down":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['rate_down']);
											break;
										case "rate_up":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['rate_up']);
											break;
										case "bandwidth_up":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['bandwidth_up']);
											break;
										case "bandwidth_down":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['bandwidth_down']);
											break;
										case "media":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['media']);
											break;
										case "state":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['state']);
											break;
									}
								}
							}
						}
						break;
					case 'Downloads':
						$result = $FreeboxAPI->universal_get('DownloadStats');
						if ($result != false) {
							foreach ($Equipement->getCmd('info') as $Command) {
								if (is_object($Command)) {
									switch ($Command->getLogicalId()) {
										case "nb_tasks":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks']);
											break;
										case "nb_tasks_downloading":
											$result = $result[''];
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_downloading']);
											break;
										case "nb_tasks_done":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_done']);
											break;
										case "rx_rate":
											$result = $result['rx_rate'];
											if (function_exists('bcdiv'))
												$result = bcdiv($result, 1048576, 2);
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
											break;
										case "tx_rate":
											$result = $result['tx_rate'];
											if (function_exists('bcdiv'))
												$result = bcdiv($result, 1048576, 2);
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
											break;
										case "nb_tasks_active":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_active']);
											break;
										case "nb_tasks_stopped":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_stopped']);
											break;
										case "nb_tasks_queued":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_queued']);
											break;
										case "nb_tasks_repairing":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_repairing']);
											break;
										case "nb_tasks_extracting":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_extracting']);
											break;
										case "nb_tasks_error":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_error']);
											break;
										case "nb_tasks_checking":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks_checking']);
											break;
									}
								}
							}
						}
						break;
					case 'System':
						foreach ($Equipement->getCmd('info') as $Command) {
							$logicalId = $Command->getConfiguration('logicalId');

							switch ($Command->getConfiguration('logicalId')) {
								case "sensors":
									foreach ($FreeboxAPI->universal_get('system', null, 1) as $system) {
										if ($system['id'] == $Command->getLogicalId()) {
											$value = $system['value'];
											log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $value);
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $value);
											break;
										}
									}
									break;
								case "fans":
									foreach ($FreeboxAPI->universal_get('system', null, 2) as $system) {
										if ($system['id'] == $Command->getLogicalId()) {
											$value = $system['value'];
											log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $value);
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $value);
											break;
										}
									}
									break;
								case "expansions":
									foreach ($FreeboxAPI->universal_get('system', null, 3) as $system) {
										if ($system['slot'] == $Command->getLogicalId()) {
											$value = $system['present'];
											log::add('Freebox_OS', 'debug', '│──────────> Update pour Type : ' . $logicalId . ' -- Id : ' . $Command->getLogicalId() . ' -- valeur : ' . $value);
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $value);
											break;
										}
									}
									break;
								default:
									if (is_object($Command)) {
										if ($Command->getLogicalId() == "4GStatut") {
											$result = $FreeboxAPI->universal_get('4G');
										} else {
											$result = $FreeboxAPI->universal_get('system', null, 4);
										}
										switch ($Command->getLogicalId()) {
											case "mac":
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['mac']);
												break;
											case "fan_rpm":
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['fan_rpm']);
												break;
											case "temp_sw":
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['temp_sw']);
												break;
											case "uptime":
												$result = $result['uptime'];
												$result = str_replace(' heure ', 'h ', $result);
												$result = str_replace(' heures ', 'h ', $result);
												$result = str_replace(' minute ', 'min ', $result);
												$result = str_replace(' minutes ', 'min ', $result);
												$result = str_replace(' secondes', 's', $result);
												$result = str_replace(' seconde', 's', $result);
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
												break;
											case "board_name":
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['board_name']);
												break;
											case "temp_cpub":
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['temp_cpub']);
												break;
											case "temp_cpum":
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['temp_cpum']);
												break;
											case "serial":
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['serial']);
												break;
											case "firmware_version":
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['firmware_version']);
												break;
											case "4GStatut":
												$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
												break;
										}
									}
									break;
							}
						}
						break;
					case 'Wifi':
						foreach ($Equipement->getCmd('info') as $Command) {
							if (is_object($Command)) {
								switch ($Command->getLogicalId()) {
									case "wifiStatut":
										$result = $FreeboxAPI->universal_get('wifi');
										$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
										break;
									case "wifiPlanning":
										$result = $FreeboxAPI->universal_get('planning');
										$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
										break;
								}
							}
						}
						break;
					case 'Disque':
						foreach ($Equipement->getCmd('info') as $Command) {
							if (is_object($Command)) {
								$result = $FreeboxAPI->universal_get('disques', $Command->getLogicalId());
								if ($result != false) {
									$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
								}
							}
						}
						break;
					case 'Phone':
						$result = $FreeboxAPI->nb_appel_absence();
						if ($result != false) {
							foreach ($Equipement->getCmd('info') as $Command) {
								if (is_object($Command)) {
									switch ($Command->getLogicalId()) {
										case "nbAppelsManquee":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['missed']);
											break;
										case "nbAppelRecus":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['accepted']);
											break;
										case "nbAppelPasse":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['outgoing']);
											break;
										case "listAppelsManquee":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['list_missed']);
											break;
										case "listAppelsRecus":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['list_accepted']);
											break;
										case "listAppelsPasse":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['list_outgoing']);
											break;
									}
								}
							}
						}
						break;
					case 'Reseau':
						foreach ($Equipement->getCmd('info') as $Command) {
							if (is_object($Command)) {
								$result = $FreeboxAPI->universal_get('reseau_ping', $Command->getLogicalId());
								if (!$result['success']) {
									if ($result['error_code'] == "internal_error") {
										$Command->remove();
										$Command->save(true);
									}
								} else {
									if (isset($result['result']['l3connectivities'])) {
										foreach ($result['result']['l3connectivities'] as $Ip) {
											if ($Ip['active']) {
												if ($Ip['af'] == 'ipv4') {
													$Command->setConfiguration('IPV4', $Ip['addr']);
												} else {
													$Command->setConfiguration('IPV6', $Ip['addr']);
												}
											}
										}
									}
									$Command->setConfiguration('host_type', $result['result']['host_type']);
									$Command->save();
									if (isset($result['result']['active'])) {
										if ($result['result']['active'] == 'true') {
											$Command->setOrder($Command->getOrder() % 1000);
											$Command->save();
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), true);
										} else {
											$Command->setOrder($Command->getOrder() % 1000 + 1000);
											$Command->save();
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), false);
										}
									} else {
										$Equipement->checkAndUpdateCmd($Command->getLogicalId(), false);
									}
								}
							}
						}
						break;
					case 'HomeAdapters':
						foreach ($Equipement->getCmd('info') as $Command) {
							$result = $FreeboxAPI->universal_get('HomeAdapters_status', $Command->getLogicalId());
							if ($result != false) {
								if ($result['status'] == 'active') {
									$HomeAdapters_value = 1;
								} else {
									$HomeAdapters_value = 0;
								}
								$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $HomeAdapters_value);
							}
						}
						break;
					default:
						if ($Equipement->getConfiguration('type') == 'parental') {
							foreach ($Equipement->getCmd('info') as $Command) {
								if (!$Equipement->getIsEnable()) break;
								$results = $FreeboxAPI->universal_get('parental', $Equipement->getLogicalId());
								//log::add('Freebox_OS', 'debug', '│ Id : ' . $Equipement->getLogicalId() . ' -- Value : ' . $results['current_mode']);
								$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $results['current_mode']);
								//log::add('Freebox_OS', 'debug', '└─────────');
								break;
							}
							break;
						} else if ($Equipement->getConfiguration('type') == 'player') {
							foreach ($Equipement->getCmd('info') as $Command) {
								if (!$Equipement->getIsEnable()) break;

								if ($Command->getLogicalId() == 'power_state') {
									$results = $FreeboxAPI->universal_get('player_ID', $Equipement->getLogicalId());
									log::add('Freebox_OS', 'debug', '│ Id : ' . $Equipement->getLogicalId() . ' -- Value : ' . $results['power_state']);
									$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $results['power_state']);
									log::add('Freebox_OS', 'debug', '└─────────');
								}
								break;
							}
							break;
						} else {
							$results = $FreeboxAPI->universal_get('tiles_ID', $Equipement->getLogicalId());
							//log::add('Freebox_OS', 'debug', '│ Label : ' . $data['label'] . ' -- Name : ' . $data['name'] . ' -- Id : ' . $data['ep_id'] . ' -- Value : ' . $data['value']);

							if ($results != false) {
								foreach ($results as $result) {
									foreach ($result['data'] as $data) {
										if (!$Equipement->getIsEnable()) break;
										$cmd = $Equipement->getCmd('info', $data['ep_id']);

										if (!is_object($cmd)) break;

										log::add('Freebox_OS', 'debug', '│ Label : ' . $data['label'] . ' -- Name : ' . $data['name'] . ' -- Id : ' . $data['ep_id'] . ' -- Value : ' . $data['value']);
										if ($data['name'] == 'pushed') {
											$nb_pushed = count($data['history']);
											$nb_pushed_k = $nb_pushed - 1;
											$_value_history = $data['history'][$nb_pushed_k]['value'];
											log::add('Freebox_OS', 'debug', '│ Nb pushed -1  : ' . $nb_pushed_k . ' -- Valeur historique récente  : ' . $_value_history);
										};


										switch ($cmd->getSubType()) {
											case 'numeric':
												if ($cmd->getConfiguration('inverse')) {
													$_value = ($cmd->getConfiguration('maxValue') - $cmd->getConfiguration('minValue')) - $data['value'];
												} else {
													if ($data['name'] == 'pushed') {
														$_value = $_value_history;
													} else {
														$_value = $data['value'];
													}
												}
												break;
											case 'string':
												if ($data['name'] == 'state' && $data['ep_id'] == 11) {
													log::add('Freebox_OS', 'debug', '│──────────> Update commande spécifique pour Homebridge');
													$_Alarm_stat_value = '0';
													$_Alarm_enable_value = '1';

													switch ($data['value']) {
														case 'alarm1_arming':
															$_Alarm_mode_value = 'Alarme principale';
															log::add('Freebox_OS', 'debug', '│ Mode 1 : Alarme principale');
															break;
														case 'alarm1_armed':
															$_Alarm_mode_value = 'Alarme principale';
															log::add('Freebox_OS', 'debug', '│ Mode 1 : Alarme principale');
															break;
														case 'alarm2_arming':
															$_Alarm_mode_value = 'Alarme secondaire';
															log::add('Freebox_OS', 'debug', '│ Mode 2 : Alarme secondaire');
															break;
														case 'alarm2_armed':
															$_Alarm_mode_value = 'Alarme secondaire';
															log::add('Freebox_OS', 'debug', '│ Mode 2 : Alarme secondaire');
															break;
														case 'alert':
															$_Alarm_stat_value = '1';
															log::add('Freebox_OS', 'debug', '│ Alarme');
															break;
														case 'alarm1_alert_timer':
															$_Alarm_stat_value = '1';
															log::add('Freebox_OS', 'debug', '│ Alarme');
															break;
														case 'alarm2_alert_timer':
															$_Alarm_stat_value = '1';
															log::add('Freebox_OS', 'debug', '│ Alarme');
															break;
														case 'idle':
															$_Alarm_enable_value = '0';
															log::add('Freebox_OS', 'debug', '│ Alarme désactivée');
															break;
														default:
															$_Alarm_mode_value = null;
															log::add('Freebox_OS', 'debug', '│ Aucun Mode');
															break;
													}

													$Equipement->checkAndUpdateCmd('ALARM_state', $_Alarm_stat_value);
													log::add('Freebox_OS', 'debug', '│ Label : ' . 'Statut' . ' -- Id : ' . 'ALARM_state' . ' -- Value : ' . $_Alarm_stat_value);
													$Equipement->checkAndUpdateCmd('ALARM_enable', $_Alarm_enable_value);
													log::add('Freebox_OS', 'debug', '│ Label : ' . 'Actif' . ' -- Id : ' . 'ALARM_enable' . ' -- Value : ' . $_Alarm_enable_value);
													$Equipement->checkAndUpdateCmd('ALARM_mode', $_Alarm_mode_value);
													log::add('Freebox_OS', 'debug', '│ Label : ' . 'Mode' . ' -- Id : ' . 'ALARM_mode' . ' -- Value : ' . $_Alarm_mode_value);
													log::add('Freebox_OS', 'debug', '│──────────> Fin Update commande spécifique pour Homebridge');
												};

												$_value = $data['value'];
												break;
											case 'binary':
												if ($cmd->getConfiguration('inverse')) {
													$_value = !$data['value'];
												} else {
													$_value = $data['value'];
												}
												break;
										}
										$Equipement->checkAndUpdateCmd($data['ep_id'], $_value);
									}
								}
							}
							log::add('Freebox_OS', 'debug', '└─────────');
						}
						break;
				}
				if ($Equipement->getConfiguration('waite') == '') {
					sleep(300);
				} else {
					sleep($Equipement->getConfiguration('waite'));
				}
			}
		}
	}
	public static function dependancy_info()
	{
		$return = array();
		$return['log'] = 'Freebox_OS_update';
		$return['progress_file'] = '/tmp/compilation_Freebox_OS_in_progress';
		if (exec('dpkg -s netcat | grep -c "Status: install"') == 1) {
			$return['state'] = 'ok';
		} else {
			$return['state'] = 'nok';
		}
		return $return;
	}
	public static function dependancy_install()
	{
		if (file_exists('/tmp/compilation_Freebox_OS_in_progress')) {
			return;
		}
		log::remove('Freebox_OS_update');
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh';
		$cmd .= ' >> ' . log::getPathToLog('Freebox_OS_update') . ' 2>&1 &';
		exec($cmd);
	}


	public static function getlogicalinfo()
	{
		return array(
			'connexionID' => 'connexion',
			'connexionName' => 'Réseau',
			'airmediaID' => 'airmedia',
			'airmediaName' => 'Air Média',
			'disqueID' => 'Disque',
			'disqueName' => 'Disque Dur',
			'reseauID' => 'reseau',
			'reseauName' => 'Appareils connectés',
			'systemID' => 'System',
			'systemName' => 'Système',
			'downloadsID' => 'Downloads',
			'downloadsName' => 'Téléchargements',
			'phoneID' => 'Phone',
			'phoneName' => 'Téléphone',
			'wifiID' => 'wifi',
			'wifiName' => 'Wifi',
			'homeadaptersID' => 'Homeadapters',
			'homeadaptersName' => 'Home Adapters'
		);
	}

	public static function updateLogicalID($_version, $_update = false)
	{
		$eqLogics = eqLogic::byType('Freebox_OS');
		$logicalinfo = Freebox_OS::getlogicalinfo();
		log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Start Update');
		foreach ($eqLogics as $eqLogic) {

			if ($eqLogic->getConfiguration('VersionLogicalID', 0) == $_version) continue;

			$eqName = $eqLogic->getName();

			log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update eqLogic : ' . $eqLogic->getLogicalId());
			switch ($eqLogic->getLogicalId()) {
				case 'ADSL':
				case 'connexion':
					$eqLogic->setLogicalId($logicalinfo['connexionID']);
					$eqLogic->setName("Freebox " . $logicalinfo['connexionName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update logicalID : "' . $logicalinfo['connexionID'] . '" et Update name : "' . $logicalinfo['connexionName'] . '"');
					break;
				case 'AirPlay':
				case 'airmedia':
				case '':
					$eqLogic->setLogicalId($logicalinfo['airmediaID']);
					$eqLogic->setName("Freebox " . $logicalinfo['airmediaName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update ' . $logicalinfo['airmediaID']);
					break;
				case 'Disque':
					$eqLogic->setLogicalId($logicalinfo['disqueID']);
					$eqLogic->setName("Freebox " . $logicalinfo['disqueName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update ' . $logicalinfo['disqueID']);
					break;
				case 'reseau':
					$eqLogic->setLogicalId($logicalinfo['reseauID']);
					$eqLogic->setName("Freebox " . $logicalinfo['reseauName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update ' . $logicalinfo['reseauID']);
					break;
				case 'System':
					$eqLogic->setLogicalId($logicalinfo['systemID']);
					$eqLogic->setName("Freebox " . $logicalinfo['systemName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update ' . $logicalinfo['systemID']);
					break;
				case 'Downloads':
					$eqLogic->setLogicalId($logicalinfo['downloadsID']);
					$eqLogic->setName("Freebox " . $logicalinfo['downloadsName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update ' . $logicalinfo['downloadsID']);
					break;
				case 'Phone':
					$eqLogic->setLogicalId($logicalinfo['phoneID']);
					$eqLogic->setName("Freebox " . $logicalinfo['phoneName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update ' . $logicalinfo['phoneID']);
					break;
				case 'Wifi':
				case 'wifi':
					$eqLogic->setLogicalId($logicalinfo['wifiID']);
					$eqLogic->setName("Freebox " . $logicalinfo['wifiName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update ' . $logicalinfo['wifiID']);
					break;
				case 'HomeAdapters':
				case 'Homeadapters':
					$eqLogic->setLogicalId($logicalinfo['homeadaptersID']);
					$eqLogic->setName("Freebox " . $logicalinfo['homeadaptersName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update ' . $logicalinfo['homeadaptersID']);
					break;
			}

			if (!$_update) $eqLogic->setName($eqName);
			//$eqLogic->setConfiguration('VersionLogicalID', strval($_version));
			log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update V' . $_version);
			$eqLogic->save(true);
			log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update save');
		}
		log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update Finish');
	}
}
class Freebox_OSCmd extends cmd
{
	/*	public function dontRemoveCmd()
	{
		return true;
	}*/
	public function execute($_options = array())
	{
		log::add('Freebox_OS', 'debug', '┌───────── Début de Mise à jour ');
		log::add('Freebox_OS', 'debug', '│ Connexion sur la freebox pour mise à jour de : ' . $this->getName());
		$FreeboxAPI = new FreeboxAPI();
		switch ($this->getEqLogic()->getLogicalId()) {
			case 'ADSL':
				break;
			case 'Downloads':
				$result = $FreeboxAPI->universal_get('DownloadStats');
				if ($result != false) {
					switch ($this->getLogicalId()) {
						case "stop_dl":
							$FreeboxAPI->Downloads(0);
							break;
						case "start_dl":
							$FreeboxAPI->Downloads(1);
							break;
					}
				}
				break;
			case 'System':
				switch ($this->getLogicalId()) {
					case "reboot":
						$FreeboxAPI->reboot();
						break;
					case "update":
						$FreeboxAPI->UpdateSystem();
						break;
					case '4GOn':
						$result = $FreeboxAPI->universal_get('4G');
						$FreeboxAPI->universal_put(1, '4G');
						break;
					case '4GOff':
						$result = $FreeboxAPI->universal_get('4G');
						log::add('Freebox_OS', 'debug', '┌───────── 4F ');
						$FreeboxAPI->universal_put(0, '4G');
						break;
				}
				break;
			case 'Wifi':
				switch ($this->getLogicalId()) {
					case "wifiOnOff":
						$result = $FreeboxAPI->universal_get();
						if ($result == true) {
							$FreeboxAPI->universal_put(0);
						} else {
							$FreeboxAPI->universal_put(1);
						}
						break;
					case 'wifiOn':
						$result = $FreeboxAPI->universal_get();
						$FreeboxAPI->universal_put(1);
						break;
					case 'wifiOff':
						$result = $FreeboxAPI->universal_get();
						$FreeboxAPI->universal_put(0);
						break;
					case 'wifiPlanningOn':
						$result = $FreeboxAPI->universal_get('planning');
						$FreeboxAPI->universal_put(1, 'planning');
						break;
					case 'wifiPlanningOff':
						$result = $FreeboxAPI->universal_get('planning');
						$FreeboxAPI->universal_put(0, 'planning');
						break;
				}
				break;
			case 'Phone':
				$result = $FreeboxAPI->nb_appel_absence();
				if ($result != false) {
					switch ($this->getLogicalId()) {
						case "sonnerieDectOn":
							$FreeboxAPI->ringtone('ON');
							break;
						case "sonnerieDectOff":
							$FreeboxAPI->ringtone('OFF');
							break;
					}
				}
				break;
			case 'AirPlay':
				$receivers = $this->getEqLogic()->getCmd(null, "ActualAirmedia");
				if (!is_object($receivers) || $receivers->execCmd() == "" || $_options['titre'] == null) {
					log::add('Freebox_OS', 'debug', '│ [AirPlay] Impossible d\'envoyer la demande les paramètres sont incomplet équipement' . $receivers->execCmd() . ' type:' . $_options['titre']);
					break;
				}
				$Parameter["media_type"] = $_options['titre'];
				$Parameter["media"] = $_options['message'];
				$Parameter["password"] = $this->getConfiguration('password');
				switch ($this->getLogicalId()) {
					case "airmediastart":
						log::add('Freebox_OS', 'debug', '│ [AirPlay] AirMedia Start : ' . $Parameter["media"]);
						$Parameter["action"] = "start";
						$return = $FreeboxAPI->airmedia('action', $Parameter, $receivers->execCmd());
						break;
					case "airmediastop":
						$Parameter["action"] = "stop";
						$return = $FreeboxAPI->airmedia('action', $Parameter, $receivers->execCmd());
						break;
				}
				break;
			default:
				$logicalId = $this->getLogicalId();
				if ($this->getEqLogic()->getconfiguration('type') == 'parental') {
					$FreeboxAPI->universal_put($logicalId, 'parental', $this->getEqLogic()->getLogicalId());
					break;
				} else {
					switch ($this->getSubType()) {
						case 'slider':
							if ($this->getConfiguration('inverse')) {
								$parametre['value'] = ($this->getConfiguration('maxValue') - $this->getConfiguration('minValue')) - $_options['slider'];
							} else {
								$parametre['value'] = (int) $_options['slider'];
							}
							$parametre['value_type'] = 'int';
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
							if ($this->getConfiguration('logicalId') >= 0 && ($this->getLogicalId() == 'PB_On' || $this->getLogicalId() == 'PB_Off')) {
								$logicalId = $this->getConfiguration('logicalId');
								log::add('Freebox_OS', 'debug', '│ Paramétrage spécifique BP ON/OFF : ' . $this->getLogicalId());
								if ($this->getLogicalId() == 'PB_On') {
									$parametre['value'] = true;
								} else {
									$parametre['value'] = false;
								}

								break;
							} else {
								$logicalId = $this->getLogicalId();
								$parametre['value'] = true;
								$Listener = cmd::byId(str_replace('#', '', $this->getValue()));

								if (is_object($Listener)) {
									$parametre['value'] = $Listener->execCmd();
								}
								if ($this->getConfiguration('inverse')) {
									$parametre['value'] = !$parametre['value'];
								}
							}
							break;

							$FreeboxAPI->setTile($this->getEqLogic()->getLogicalId(), $logicalId, $parametre);
							//	log::add('Freebox_OS', 'debug', '└─────────');
					}
					break;
				}
		}
	}
}
