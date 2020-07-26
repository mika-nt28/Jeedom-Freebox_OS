<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../core/php/Freebox_OS.inc.php';

class Freebox_OS extends eqLogic
{
	public static function cron()
	{
		$eqLogics = eqLogic::byType('Freebox_OS');
		foreach ($eqLogics as $eqLogic) {
			$autorefresh = $eqLogic->getConfiguration('autorefresh', '*/5 * * * *');
			try {
				$c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
				if ($c->isDue($dateRun)) {
					log::add('Freebox_OS', 'debug', '================= CRON pour l\'actualisation de : ' . $eqLogic->getName() . ' ==================');
					Free_Refresh::RefreshInformation($eqLogic->getId());
				}
			} catch (Exception $exc) {
				log::add('Freebox_OS', 'error', __('Expression cron non valide pour ', __FILE__) . $eqLogic->getHumanName() . ' : ' . $autorefresh);
			}
		}
	}

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
	}
	public static function deamon_stop()
	{
		$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
		if (is_object($cron)) {
			$cron->stop();
			$cron->remove();
		}
		$Free_API = new Free_API();
		$Free_API->close_session();
	}
	public static function AddEqLogic($Name, $_logicalId, $category = null, $tiles, $eq_type, $eq_action, $logicalID_equip = null, $_autorefresh = null)
	{
		$EqLogic = self::byLogicalId($_logicalId, 'Freebox_OS');
		if (!is_object($EqLogic)) {
			$defaultRoom = intval(config::byKey('defaultParentObject', "Freebox_OS", '', true));
			$EqLogic = new Freebox_OS();
			$EqLogic->setLogicalId($_logicalId);
			if ($defaultRoom) $EqLogic->setObject_id($defaultRoom);
			$EqLogic->setEqType_name('Freebox_OS');
			$EqLogic->setIsEnable(1);
			$EqLogic->setIsVisible(0);
			$EqLogic->setName($Name);
			if ($category != null) {
				$EqLogic->setcategory($category, 1);
			}
			if ($_autorefresh != null) {
				$EqLogic->setConfiguration('autorefresh', $_autorefresh);
			} else {
				$EqLogic->setConfiguration('autorefresh', '*/5 * * * *');
			}
			$EqLogic->save();
		}
		$EqLogic->setConfiguration('logicalID', $_logicalId);
		if ($EqLogic->getConfiguration('autorefresh') == null && $tiles != true && $EqLogic->getLogicalId() != 'disk') {
			$EqLogic->setConfiguration('autorefresh', '*/5 * * * *');
		} elseif ($EqLogic->getConfiguration('autorefresh') == null && $EqLogic->getLogicalId() == 'disk') {
			$EqLogic->setConfiguration('autorefresh', '1 * * * *');
		}
		if ($tiles == true) {
			$EqLogic->setConfiguration('type', $eq_type);
			$EqLogic->setConfiguration('action', $eq_action);
			if ($_autorefresh != null) {
				if ($EqLogic->getConfiguration('autorefresh') == null && $EqLogic->getConfiguration('type', $eq_type) != 'parental' && $EqLogic->getConfiguration('type', $eq_type) != 'player' && $EqLogic->getConfiguration('type', $eq_type) != 'alarm_remote') {
					$EqLogic->setConfiguration('autorefresh', '* * * * *');
				} elseif ($EqLogic->getConfiguration('autorefresh') == null && $EqLogic->getConfiguration('type', $eq_type) == 'alarm_remote') {
					$EqLogic->setConfiguration('autorefresh', '*/5 * * * *');
				} else {
					$EqLogic->setConfiguration('autorefresh', '*/5 * * * *');
				}
			}

			if ($EqLogic->getConfiguration('type', $eq_type) == 'parental' || $EqLogic->getConfiguration('type', $eq_type) == 'player') {
				$EqLogic->setConfiguration('action', $logicalID_equip);
			}
		}
		$EqLogic->save();
		return $EqLogic;
	}
	public static function templateWidget()
	{
		return Free_Template::getTemplate();
	}
	public static function addTiles()
	{
		$Free_API = new Free_API();

		Free_CreateEq::createEq('homeadapters');

		if (version_compare(jeedom::version(), "4", "<")) {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V3 ');
			$templatecore_V4 = null;
		} else {
			log::add('Freebox_OS', 'debug', '│ Application des Widgets ou Icônes pour le core V4');
			$templatecore_V4  = 'core::';
		};
		foreach ($Free_API->universal_get('tiles') as $Equipement) {
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
									if ($Equipement['action'] != "store_slider" && $Command['name'] == 'position') {
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
											log::add('Freebox_OS', 'debug', '│ Valeur de Batterie  Nulle : ' . $Command['value']);
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
	public function AddCommand($Name, $_logicalId, $Type = 'info', $SubType = 'binary', $Template = null, $unite = null, $generic_type = null, $IsVisible = 1, $link_I = 'default', $link_logicalId = 'default',  $invertBinary = '0', $icon, $forceLineB = '0', $valuemin = 'default', $valuemax = 'default', $_order = null, $IsHistorized = '0', $forceIcone_widget = false, $repeatevent = false, $_logicalId_slider = null, $_iconname = null, $_home_mode_set = null)
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
		if ($_logicalId_slider != null) { // logical Id spécial Slider
			$Command->setConfiguration('logicalId_slider', $link_I);
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

		if ($_logicalId == "tempDenied") {
			$Command->setConfiguration('listValue', '1800|30 minutes;3600|1 heure;7200|2 heure');
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

		Free_CreateEq::createEq();

		if (config::byKey('FREEBOX_SERVER_TRACK_ID') != '') {
			$Free_API = new Free_API();
			$Free_API->disk();
			$Free_API->universal_get();
			$Free_API->universal_get('download_stats');
			$Free_API->universal_get('planning');
			$Free_API->universal_get('system', null, 4);
			$Free_API->connexion_stats();
			$Free_API->nb_appel_absence();
		}
	}
	public function preSave()
	{
		switch ($this->getLogicalId()) {
			case 'AirPlay':
				$Free_API = new Free_API();
				$parametre["enabled"] = $this->getIsEnable();
				$parametre["password"] = $this->getConfiguration('password');
				$Free_API->airmedia('config', $parametre);
				break;
		}
		if ($this->getConfiguration('waite') == '') {
			$this->setConfiguration('waite', 300);
		}
	}
	public function preUpdate()
	{
		if (!$this->getIsEnable()) return;

		if ($this->getConfiguration('autorefresh') == '') {
			throw new Exception(__('Le champ "Temps de rafraichissement (cron)" ne peut être vide', __FILE__));
			log::add(__CLASS__, 'error', '│ Configuration : Temps de rafraichissement (cron) : ' . $this->getConfiguration('autorefresh'));
		}
	}
	public function postSave()
	{
		if ($this->getIsEnable()) {
			Free_Refresh::RefreshInformation($this->getId());
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
		$Free_API = new Free_API();
		$Free_API->close_session();
		if ($Free_API->getFreeboxOpenSession() === false) self::deamon_stop();
	}
	public static function getlogicalinfo()
	{
		return array(
			'4GID' => '4G',
			'4GName' => '4G',
			'airmediaID' => 'airmedia',
			'airmediaName' => 'Air Média',
			'connexionID' => 'connexion',
			'connexionName' => 'Freebox Débits',
			'diskID' => 'disk',
			'diskName' => 'Disque Dur',
			'downloadsID' => 'downloads',
			'downloadsName' => 'Téléchargements',
			'homeadaptersID' => 'homeadapters',
			'homeadaptersName' => 'Home Adapters',
			'networkID' => 'network',
			'networkName' => 'Appareils connectés',
			'phoneID' => 'phone',
			'phoneName' => 'Téléphone',
			'systemID' => 'system',
			'systemName' => 'Système',
			'wifiID' => 'wifi',
			'wifiName' => 'Wifi'
		);
	}
	public static function updateLogicalID($_version, $_update = false)
	{
		$eqLogics = eqLogic::byType('Freebox_OS');
		$logicalinfo = Freebox_OS::getlogicalinfo();
		log::add('Freebox_OS', 'debug', '┌───────── Fonction updateLogicalID : Start Update');
		foreach ($eqLogics as $eqLogic) {

			if ($eqLogic->getConfiguration('VersionLogicalID', 0) == $_version) continue;

			$eqName = $eqLogic->getName();

			log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update eqLogic : ' . $eqLogic->getLogicalId());
			switch ($eqLogic->getLogicalId()) {
				case 'ADSL':
				case 'connexion':
					$eqLogic->setLogicalId($logicalinfo['connexionID']);
					$eqLogic->setName($logicalinfo['connexionName']);
					log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update logicalID : "' . $logicalinfo['connexionID'] . '" et Update name : "' . $logicalinfo['connexionName'] . '"');
					break;
				case 'AirPlay':
				case 'airmedia':
				case '':
					$eqLogic->setLogicalId($logicalinfo['airmediaID']);
					$eqLogic->setName($logicalinfo['airmediaName']);
					log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update ' . $logicalinfo['airmediaID']);
					break;
				case 'Disque':
				case 'Disques':
					$eqLogic->setLogicalId($logicalinfo['diskID']);
					$eqLogic->setName($logicalinfo['diskName']);
					log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update ' . $logicalinfo['diskID']);
					break;
				case 'Reseau':
				case 'reseau':
					$eqLogic->setLogicalId($logicalinfo['networkID']);
					$eqLogic->setName($logicalinfo['networkName']);
					log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update ' . $logicalinfo['networkID']);
					break;
				case 'System':
					$eqLogic->setLogicalId($logicalinfo['systemID']);
					$eqLogic->setName($logicalinfo['systemName']);
					log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update ' . $logicalinfo['systemID']);
					break;
				case 'Downloads':
					$eqLogic->setLogicalId($logicalinfo['downloadsID']);
					$eqLogic->setName($logicalinfo['downloadsName']);
					log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update ' . $logicalinfo['downloadsID']);
					break;
				case 'Phone':
					$eqLogic->setLogicalId($logicalinfo['phoneID']);
					$eqLogic->setName($logicalinfo['phoneName']);
					log::add('Freebox_OS', 'debug', 'Fonction updateLogicalID : Update ' . $logicalinfo['phoneID']);
					break;
				case 'Wifi':
				case 'wifi':
					$eqLogic->setLogicalId($logicalinfo['wifiID']);
					$eqLogic->setName($logicalinfo['wifiName']);
					log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update ' . $logicalinfo['wifiID']);
					break;
				case 'HomeAdapters':
				case 'Home Adapters':
				case 'Homeadapters':
					$eqLogic->setLogicalId($logicalinfo['homeadaptersID']);
					$eqLogic->setName($logicalinfo['homeadaptersName']);
					log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update ' . $logicalinfo['homeadaptersID']);
					break;
			}

			if (!$_update) $eqLogic->setName($eqName);
			log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update V' . $_version);
			$eqLogic->save(true);
			log::add('Freebox_OS', 'debug', '│ Fonction updateLogicalID : Update save');
		}
		log::add('Freebox_OS', 'debug', '└─────────');
	}
}

class Freebox_OSCmd extends cmd
{
	public function execute($_options = array())
	{
		log::add('Freebox_OS', 'debug', '┌───────── Début de Mise à jour ');
		$logicalId = $this->getLogicalId();
		$logicalId_type = $this->getSubType();
		$logicalId_value = $this->getvalue();
		$logicalId_name = $this->getName();
		$logicalId_conf = $this->getConfiguration('logicalId');
		$logicalId_eq = $this->getEqLogic();

		log::add('Freebox_OS', 'debug', '│ Connexion sur la freebox pour mise à jour de : ' . $logicalId_name);

		Free_Update::UpdateAction($logicalId, $logicalId_type, $logicalId_name, $logicalId_value, $logicalId_conf, $logicalId_eq, $_options, $this);
	}
}
