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
	public static function AddEqLogic($Name, $_logicalId, $category = null)
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
		return $EqLogic;
	}
	public static function addReseau()
	{
		$FreeboxAPI = new FreeboxAPI();
		$Reseau = self::AddEqLogic('Réseau', 'Reseau');
		foreach ($FreeboxAPI->getReseau() as $Equipement) {
			if ($Equipement['primary_name'] != '') {
				$Command = $Reseau->AddCommand($Equipement['primary_name'], $Equipement['id'], "info", 'binary', 'Freebox_OS_Reseau');
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
		$HomeAdapters = self::AddEqLogic('Home Adapters', 'HomeAdapters');
		foreach ($FreeboxAPI->getHomeAdapters() as $Equipement) {
			if ($Equipement['label'] != '') {
				$HomeAdapters->AddCommand($Equipement['label'], $Equipement['id'], "info", 'binary');
				$HomeAdapters->checkAndUpdateCmd($Equipement['id'], $Equipement['status']);
			}
		}
	}
	public static function addTiles()
	{
		$FreeboxAPI = new FreeboxAPI();
		foreach ($FreeboxAPI->getTiles() as $Equipement) {
			if ($Equipement['type'] != 'camera') {
				if ($Equipement['type'] == 'alarm_sensor' || $Equipement['type'] == 'alarm_control' || $Equipement['type'] == 'alarm_remote') {
					$category = 'security';
				} elseif ($Equipement['type'] == 'light') {
					$category = 'light';
				} else {
					$category = null;
				}
				$Equipement['label'] = preg_replace('/\'+/', ' ', $Equipement['label']); // Suppression '
				if (isset($Equipement['label'])) {
					$Tile = self::AddEqLogic($Equipement['label'], $Equipement['node_id'], $category);
				} else {
					$Tile = self::AddEqLogic($Equipement['type'], $Equipement['node_id'], $category);
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
						event::add('Freebox_OS::camera', json_encode($parameter));
						continue;
					}
					if (!is_object($Tile)) continue;
					log::add(__CLASS__, 'debug', '┌───────── Commande trouvée pour l\'équipement FREEBOX : ' . $Equipement['label'] . ' (Node ID ' . $Equipement['node_id'] . ')');
					$Command['label'] = preg_replace('/É+/', 'E', $Command['label']); // Suppression É
					$Command['label'] = preg_replace('/\'+/', ' ', $Command['label']); // Suppression '
					log::add(__CLASS__, 'debug', '│ label : ' . $Command['label'] . ' -- name : ' . $Command['name']);
					log::add(__CLASS__, 'debug', '│ type  : ' . $Equipement['type'] . ' -- action : ' . $Equipement['action']);
					log::add(__CLASS__, 'debug', '│ Index : ' . $Command['ep_id'] . ' -- Value Type : ' . $Command['value_type'] . ' -- Access : ' . $Command['ui']['access']);
					log::add(__CLASS__, 'debug', '│ valeur actuelle : ' . $Command['value'] . ' -- Unité : ' . $Command['ui']['unit']);

					switch ($Command['value_type']) {
						case "void":
							if ($Command['name'] == 'up') {
								$generic_type = 'FLAP_UP';
								$icon = 'fas fa-arrow-up';
							} elseif ($Command['name'] == 'stop') {
								$generic_type = 'FLAP_STOP';
								$icon = 'fas fa-stop';
							} elseif ($Command['name'] == 'down') {
								$generic_type = 'FLAP_DOWN';
								$icon = 'fas fa-arrow-down';
							} else {
								$generic_type = null;
								$icon = null;
							}
							$action = $Tile->AddCommand($Command['label'], $Command['ep_id'], 'action', 'other', '', $Command['ui']['unit'], $generic_type, 1, '', '', '', '', $icon);
							break;
						case "int":
							foreach (str_split($Command['ui']['access']) as $access) {
								if ($access == "r") {
									if ($Command['ui']['access'] == "rw") {
										$label_sup = 'Etat ';
									}
									if ($Command['name'] == "luminosity" && $Equipement['action'] != "color_picker") {
										$infoCmd = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'info', 'numeric', '', $Command['ui']['unit'], 'LIGHT_STATE', 0, '', $Command['ep_id'], 'light');
										$Tile->AddCommand($Command['label'], $Command['ep_id'], 'action', 'slider', '', $Command['ui']['unit'], 'LIGHT_SLIDER', 1, $infoCmd, $Command['ep_id'], 'light');
									} elseif ($Command['name'] != "luminosity" && $Equipement['action'] == "color_picker") {
										$infoCmd = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'info', 'numeric', '', $Command['ui']['unit'], 'LIGHT_COLOR', 0, '', $Command['ep_id'], 'light');
										$Tile->AddCommand($Command['label'], $Command['ep_id'], 'action', 'slider', '', $Command['ui']['unit'], 'LIGHT_SET_COLOR', 1, $infoCmd, $Command['ep_id'], 'light');
									} elseif ($Command['name'] == "battery_warning") {
										$Tile->AddCommand($Command['label'], $Command['ep_id'], 'info', 'numeric', '', $Command['ui']['unit'], 'BATTERY', 0, '', '');
									} else {
										$info = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'info', 'numeric', '', $Command['ui']['unit'], $generic_type, $IsVisible, '', '');
									}
									$label_sup = '';
									$Tile->checkAndUpdateCmd($Command['ep_id'], $Command['value']);
									if ($Command['name'] == "battery_warning") {
										//$Tile->batteryStatus($Command['value']);
									}
								}
								if ($access == "w") {
									if ($Command['name'] != "luminosity" && $Equipement['action'] != "color_picker") {
										$action = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'action', 'slider', '', $Command['ui']['unit'], $generic_type, $IsVisible, '', '');
									}
								}
							}
							break;
						case "bool":
							foreach (str_split($Command['ui']['access']) as $access) {
								if ($access == "r") {
									if ($Equipement['action'] == "store") {
										$generic_type = 'FLAP_STATE';
										$Templatecore = '';
									} elseif ($Equipement['type'] == "alarm_sensor" && $Command['name'] == 'cover') {
										$generic_type = 'SABOTAGE';
										$Templatecore = 'alert';
										$invertBinary = 1;
									} elseif ($Equipement['type'] == "alarm_sensor" && $Command['name'] == 'trigger' && $Command['label'] != 'Détection') {
										$generic_type = 'OPENING';
										$Templatecore = 'door';
									} elseif ($Equipement['type'] == "alarm_sensor" && $Command['name'] == 'trigger' && $Command['label'] == 'Détection') {
										$generic_type = 'PRESENCE';
										$Templatecore = 'presence';
										$invertBinary = 0;
									} else {
										$generic_type = null;
										$Templatecore = null;
										$invertBinary = 0;
									}
									if ($Command['label'] == 'Enclenché') {
										$infoCmd = $Tile->AddCommand('Etat', $Command['ep_id'], 'info', 'binary', '', $Command['ui']['unit'], 'LIGHT_STATE', 0, '', $Command['ep_id'], 'light');
										$Tile->AddCommand('On', 'PB_On', 'action', 'other', '', $Command['ui']['unit'], 'LIGHT_ON', 1, $infoCmd, $Command['ep_id'], 'light', $invertBinary);
										$Tile->AddCommand('Off', 'PB_Off', 'action', 'other', '', $Command['ui']['unit'], 'LIGHT_OFF', 1, $infoCmd, $Command['ep_id'], 'light', $invertBinary);
									} else {
										$infoCmd = $Tile->AddCommand($Command['label'], $Command['ep_id'], 'info', 'binary', '', $Command['ui']['unit'], $generic_type, 1, '', '', $Templatecore, $invertBinary);
									}
									$Tile->checkAndUpdateCmd($Command['ep_id'], $Command['value']);
									$label_sup = null;
									$generic_type = null;
									$Templatecore = null;
									$invertBinary = null;
								}
								if ($access == "w") {
									if ($Command['label'] != 'Enclenché') {
										$action = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'action', 'other', '', $Command['ui']['unit'], $generic_type, $IsVisible, '', '');
									}
								}
							}
							break;
						case "string":
							foreach (str_split($Command['ui']['access']) as $access) {
								if ($Command['name'] == "pin") {
									$IsVisible = 0;
								} else {
									$IsVisible = 1;
								}
								if ($access == "r") {
									if ($Command['ui']['access'] == "rw") {
										$label_sup = 'Etat ';
									}
									$info = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'info', 'string', '', $Command['ui']['unit'], $generic_type, $IsVisible, '', '');
									$Tile->checkAndUpdateCmd($Command['ep_id'], $Command['value']);
									$label_sup = '';
								}
								if ($access == "w") {
									$action = $Tile->AddCommand($label_sup . $Command['label'], $Command['ep_id'], 'action', 'message', '', $Command['ui']['unit'], $generic_type, $IsVisible, '', '');
								}
							}
							break;
					}
					if (is_object($info) && is_object($action)) {
						$action->setValue($info->getId());
						$action->save();
					}
					log::add(__CLASS__, 'debug', '└─────────');
				}
			}
		}
	}

	public function AddCommand($Name, $_logicalId, $Type = 'info', $SubType = 'binary', $Template = 'default', $unite = null, $generic_type = null, $IsVisible = 1, $linkedInfoCmd = null, $linkedlogicalId = 'NO_LINK', $Templatecore = 'default', $invertBinary = null, $icon = null)
	{
		log::add(__CLASS__, 'debug', '│ Type : ' . $Type . ' -- LogicalID : ' . $_logicalId . ' -- Type de générique : ' . $generic_type . ' -- Inverser : ' . $invertBinary . ' -- Icône : ' . $icon);

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
			$Command->setGeneric_type($generic_type);

			$Command->setIsVisible($IsVisible);
			$Command->setIsHistorized(0);

			$Command->setUnite($unite);

			if ($Template != 'default') {
				$Command->setTemplate('dashboard', 'Freebox_OS::' . $Template);
				$Command->setTemplate('mobile', 'Freebox_OS::' . $Template);
			}
			if ($Templatecore != 'default') {
				$Command->setTemplate('dashboard', 'core::' . $Templatecore);
				$Command->setTemplate('mobile', 'core::' . $Templatecore);
			}
			if ($invertBinary != null) {
				$Command->setdisplay('invertBinary', 1);
			}
			if ($icon != null) {
				$Command->setdisplay('icon', '<i class="' . $icon . '"></i>');
			}
			if ($linkedlogicalId >= 0 && $Type == "action") {
				$Command->setconfiguration('logicalId', $linkedlogicalId);
			}
			if (is_object($linkedInfoCmd) && $Type == 'action') {
				$Command->setValue($linkedInfoCmd->getId());
			}

			$Command->save();
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
		return $Command;
	}
	public static function CreateArchi()
	{
		self::AddEqLogic('Home Adapters', 'HomeAdapters');
		self::AddEqLogic('Réseau', 'Reseau');
		self::AddEqLogic('Disque Dur', 'Disque');
		// ADSL
		log::add(__CLASS__, 'debug', '┌───────── Ajout des commandes : ADSL');
		$ADSL = self::AddEqLogic('ADSL', 'ADSL');
		$ADSL->AddCommand('Freebox rate down', 'rate_down', "info", 'numeric', '', 'Ko/s');
		$ADSL->AddCommand('Freebox rate up', 'rate_up', "info", 'numeric', '', 'Ko/s');
		$ADSL->AddCommand('Freebox bandwidth up', 'bandwidth_up', "info", 'numeric', '', 'Mb/s');
		$ADSL->AddCommand('Freebox bandwidth down', 'bandwidth_down', "info", 'numeric', '', 'Mb/s');
		$ADSL->AddCommand('Freebox media', 'media', "info", 'string');
		$ADSL->AddCommand('Freebox state', 'state', "info", 'string');
		log::add(__CLASS__, 'debug', '└─────────');
		// System
		log::add(__CLASS__, 'debug', '┌───────── Ajout des commandes : Système');
		$System = self::AddEqLogic('Système', 'System');
		$System->AddCommand('Update', 'update', "action", 'other', 'Freebox_OS_System');
		$System->AddCommand('Reboot', 'reboot', "action", 'other', 'Freebox_OS_System');
		$System->AddCommand('Freebox firmware version', 'firmware_version', "info", 'string', 'Freebox_OS_System');
		$System->AddCommand('Mac', 'mac', "info", 'string', 'Freebox_OS_System');
		$System->AddCommand('Allumée depuis', 'uptime', "info", 'string', 'Freebox_OS_System');
		$System->AddCommand('board name', 'board_name', "info", 'string', 'Freebox_OS_System');
		$System->AddCommand('serial', 'serial', "info", 'string', 'Freebox_OS_System');
		$System->AddCommand('Vitesse ventilateur', 'fan_rpm', "info", 'numeric', 'Freebox_OS_System', 'tr/min');
		$System->AddCommand('temp cpub', 'temp_cpub', "info", 'numeric', 'Freebox_OS_System', '°C');
		$System->AddCommand('temp cpum', 'temp_cpum', "info", 'numeric', 'Freebox_OS_System', '°C');
		$System->AddCommand('temp sw', 'temp_sw', "info", 'numeric', 'Freebox_OS_System', '°C');
		$cmdPF = $System->AddCommand('Redirection de ports', 'port_forwarding', "action", 'message', 'Freebox_OS_System', '', '', 0, '', '', '', '', '');
		$cmdPF->save();
		log::add(__CLASS__, 'debug', '└─────────');
		//Wifi
		log::add(__CLASS__, 'debug', '┌───────── Ajout des commandes : Wifi');
		$Wifi = self::AddEqLogic('Wifi', 'Wifi');
		$StatusWifi = $Wifi->AddCommand('Status du wifi', 'wifiStatut', "info", 'binary', 'Freebox_OS_Wifi', '', '', 1, '', '', '', '', '');
		$Wifi->AddCommand('Active Désactive le wifi', 'wifiOnOff', "action", 'other', '', '', '', 0, $StatusWifi, $StatusWifi);
		$Wifi->AddCommand('Wifi On', 'wifiOn', "action", 'other', '', '', '', 0, $StatusWifi, $StatusWifi, '', '', 'fas fa-wifi');
		$Wifi->AddCommand('Wifi Off', 'wifiOff', "action", 'other', '', '', '', 0, $StatusWifi, $StatusWifi, '', '', 'fas fa-times');
		log::add(__CLASS__, 'debug', '└─────────');
		//Downloads
		//Phone
		log::add(__CLASS__, 'debug', '┌───────── Ajout des commandes : Téléphone');
		$Phone = self::AddEqLogic('Téléphone', 'Phone');
		$Phone->AddCommand('Nombre Appels Manqués', 'nbAppelsManquee', "info", 'numeric', 'Freebox_OS_Phone');
		$Phone->AddCommand('Nombre Appels Reçus', 'nbAppelRecus', "info", 'numeric', 'Freebox_OS_Phone');
		$Phone->AddCommand('Nombre Appels Passés', 'nbAppelPasse', "info", 'numeric', 'Freebox_OS_Phone');
		$Phone->AddCommand('Liste Appels Manqués', 'listAppelsManquee', "info", 'string', 'Freebox_OS_Phone');
		$Phone->AddCommand('Liste Appels Reçus', 'listAppelsRecus', "info", 'string', 'Freebox_OS_Phone');
		$Phone->AddCommand('Liste Appels Passés', 'listAppelsPasse', "info", 'string', 'Freebox_OS_Phone');
		$Phone->AddCommand('Faire sonner les téléphones DECT', 'sonnerieDectOn', "action", 'other', 'Freebox_OS_Phone');
		$Phone->AddCommand('Arrêter les sonneries des téléphones DECT', 'sonnerieDectOff', "action", 'other', 'Freebox_OS_Phone');
		log::add(__CLASS__, 'debug', '└─────────');
		//Downloads
		log::add(__CLASS__, 'debug', '┌───────── Ajout des commandes : Téléchargements');
		$Downloads = self::AddEqLogic('Téléchargements', 'Downloads');
		$Downloads->AddCommand('Nombre de tâche(s)', 'nb_tasks', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Nombre de tâche(s) active', 'nb_tasks_active', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Nombre de tâche(s) en extraction', 'nb_tasks_extracting', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Nombre de tâche(s) en réparation', 'nb_tasks_repairing', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Nombre de tâche(s) en vérification', 'nb_tasks_checking', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Nombre de tâche(s) en attente', 'nb_tasks_queued', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Nombre de tâche(s) en erreur', 'nb_tasks_error', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Nombre de tâche(s) stoppée(s)', 'nb_tasks_stopped', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Nombre de tâche(s) terminée(s)', 'nb_tasks_done', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Téléchargement en cours', 'nb_tasks_downloading', "info", 'numeric', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Vitesse réception', 'rx_rate', "info", 'numeric', 'Freebox_OS_Downloads', 'Mo/s');
		$Downloads->AddCommand('Vitesse émission', 'tx_rate', "info", 'numeric', 'Freebox_OS_Downloads', 'Mo/s');
		$Downloads->AddCommand('Start DL', 'start_dl', "action", 'other', 'Freebox_OS_Downloads');
		$Downloads->AddCommand('Stop DL', 'stop_dl', "action", 'other', 'Freebox_OS_Downloads');
		log::add(__CLASS__, 'debug', '└─────────');
		// AirPlay
		log::add(__CLASS__, 'debug', '┌───────── Ajout des commandes : AirPlay');
		$AirPlay = self::AddEqLogic('AirPlay', 'AirPlay', 'multimedia');
		$AirPlay->AddCommand('Player actuel AirMedia', 'ActualAirmedia', "info", 'string', 'Freebox_OS_AirMedia_Recever');
		$AirPlay->AddCommand('Start', 'airmediastart', "action", 'message', 'Freebox_OS_AirMedia_Start', '', '', 1, '', '', '', '', 'fas fa-play');
		$AirPlay->AddCommand('Stop', 'airmediastop', "action", 'message', 'Freebox_OS_AirMedia_Start', '', '', 1, '', '', '', '', 'fas fa-stop');
		log::add(__CLASS__, 'debug', '└─────────');
		if (config::byKey('FREEBOX_SERVER_TRACK_ID') != '') {
			$FreeboxAPI = new FreeboxAPI();
			$FreeboxAPI->disques();
			$FreeboxAPI->wifi();
			$FreeboxAPI->system();
			$FreeboxAPI->adslStats();
			$FreeboxAPI->nb_appel_absence();
			$FreeboxAPI->DownloadStats();
			self::addReseau();
			self::addTiles();
			self::addHomeAdapters();
		}
	}
	public function preSave()
	{
		switch ($this->getLogicalId()) {
			case 'AirPlay':
				$FreeboxAPI = new FreeboxAPI();
				$parametre["enabled"] = $this->getIsEnable();
				$parametre["password"] = $this->getConfiguration('password');
				$FreeboxAPI->airmediaConfig($parametre);
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
						$result = $FreeboxAPI->DownloadStats();
						if ($result != false) {
							foreach ($Equipement->getCmd('info') as $Command) {
								if (is_object($Command)) {
									switch ($Command->getLogicalId()) {
										case "nb_tasks":
											$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['nb_tasks']);
											break;
										case "nb_tasks_downloading":
											$return = $result[''];
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
							if (is_object($Command)) {
								$result = $FreeboxAPI->system();
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
								}
							}
						}
						break;
					case 'Wifi':
						foreach ($Equipement->getCmd('info') as $Command) {
							if (is_object($Command)) {
								$result = $FreeboxAPI->wifi();
								switch ($Command->getLogicalId()) {
									case "wifiStatut":
										$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result);
										break;
								}
							}
						}
						break;
					case 'Disque':
						foreach ($Equipement->getCmd('info') as $Command) {
							if (is_object($Command)) {
								$result = $FreeboxAPI->getdisque($Command->getLogicalId());
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
								$result = $FreeboxAPI->ReseauPing($Command->getLogicalId());
								if (!$result['success']) {
									if ($result['error_code'] == "internal_error") {
										$Command->remove();
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
							$result = $FreeboxAPI->getHomeAdapterStatus($Command->getLogicalId());
							if ($result != false) {
								$Equipement->checkAndUpdateCmd($Command->getLogicalId(), $result['status']);
							}
						}
						break;
					default:
						$results = $FreeboxAPI->getTile($Equipement->getLogicalId());
						if ($results != false) {
							foreach ($results as $result) {
								foreach ($result['data'] as $data) {
									if (!$Equipement->getIsEnable()) break;
									$cmd = $Equipement->getCmd('info', $data['ep_id']);

									if (!is_object($cmd)) break;

									switch ($cmd->getSubType()) {
										case 'numeric':
											if ($cmd->getConfiguration('inverse')) {
												$_value = ($cmd->getConfiguration('maxValue') - $cmd->getConfiguration('minValue')) - $data['value'];
											} else {
												$_value = $data['value'];
											}
											break;
										case 'string':
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
}
class Freebox_OSCmd extends cmd
{
	//public function dontRemoveCmd(){
	//	return true;
	//}
	public function execute($_options = array())
	{
		log::add('Freebox_OS', 'debug', '>───────── Connexion sur la freebox pour mise à jour de : ' . $this->getName());
		$FreeboxAPI = new FreeboxAPI();
		switch ($this->getEqLogic()->getLogicalId()) {
			case 'ADSL':
				break;
			case 'Downloads':
				$result = $FreeboxAPI->DownloadStats();
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
				$result = $FreeboxAPI->system();
				switch ($this->getLogicalId()) {
					case "reboot":
						$FreeboxAPI->reboot();
						break;
					case "update":
						$FreeboxAPI->UpdateSystem();
						break;
				}
				break;
			case 'Wifi':
				$result = $FreeboxAPI->wifi();
				switch ($this->getLogicalId()) {
					case "wifiOnOff":
						if ($result == true) {
							$FreeboxAPI->wifiPUT(0);
						} else {
							$FreeboxAPI->wifiPUT(1);
						}
						break;
					case 'wifiOn':
						$FreeboxAPI->wifiPUT(1);
						break;
					case 'wifiOff':
						$FreeboxAPI->wifiPUT(0);
						break;
				}
				break;
			case 'Phone':
				$result = $FreeboxAPI->nb_appel_absence();
				if ($result != false) {
					switch ($this->getLogicalId()) {
						case "sonnerieDectOn":
							$FreeboxAPI->ringtone_on();
							break;
						case "sonnerieDectOff":
							$FreeboxAPI->ringtone_off();
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
						$return = $FreeboxAPI->AirMediaAction($receivers->execCmd(), $Parameter);
						break;
					case "airmediastop":
						$Parameter["action"] = "stop";
						$return = $FreeboxAPI->AirMediaAction($receivers->execCmd(), $Parameter);
						break;
				}
				break;
			default:
				$logicalId = $this->getLogicalId();
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
							log::add('Freebox_OS', 'debug', '│ Parametrage spécifique BP ON/OFF ' . $logicalId);
							$logicalId = $this->getConfiguration('logicalId');
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
				}
				$FreeboxAPI->setTile($this->getEqLogic()->getLogicalId(), $logicalId, $parametre);
				break;
		}
	}
}
