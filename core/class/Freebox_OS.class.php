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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../core/php/Freebox_OS.inc.php';

class Freebox_OS extends eqLogic
{
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */
	public static function cron()
	{
		$eqLogics = eqLogic::byType('Freebox_OS');
		$deamon_info = self::deamon_info();
		foreach ($eqLogics as $eqLogic) {
			$autorefresh = $eqLogic->getConfiguration('autorefresh', '*/5 * * * *');
			try {
				$c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
				if ($c->isDue() && $deamon_info['state'] == 'ok') {
					log::add('Freebox_OS', 'debug', '================= CRON pour l\'actualisation de : ' . $eqLogic->getName() . ' ==================');
					Free_Refresh::RefreshInformation($eqLogic->getId());
				}
				if ($deamon_info['state'] != 'ok') {
					log::add('Freebox_OS', 'debug', '================= PAS DE CRON pour d\'actualisation ' . $eqLogic->getName() . ' à cause du Démon : ' . $deamon_info['state'] . ' ==================');
				}
			} catch (Exception $exc) {
				log::add('Freebox_OS', 'error', __('Expression cron non valide pour ', __FILE__) . $eqLogic->getHumanName() . ' : ' . $autorefresh);
			}
		}
	}
	public static function cronDaily()
	{
		$deamon_info = self::deamon_info();
		if ($deamon_info['state'] == 'ok') {
			log::add('Freebox_OS', 'debug', '================= CRON JOUR ' . ' ==================');
			if (config::byKey('TYPE_FREEBOX_MODE', 'Freebox_OS') == 'router') {
				Free_CreateEq::createEq('network');
				Free_CreateEq::createEq('networkwifiguest');
			}
			Free_CreateEq::createEq('disk');
			if (config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS') == 'OK') {
				Free_CreateTil::createTil('homeadapters_SP');
			}
			log::add('Freebox_OS', 'debug', '================= FIN CRON JOUR ' . ' ==================');
		} else {
			log::add('Freebox_OS', 'debug', '================= PAS DE CRON JOUR à cause du Démon : ' . $deamon_info['state'] . ' ==================');
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
		//log::remove('Freebox_OS');
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
			$cron->setSchedule('*/48 * * * *');
			$cron->setTimeout('5');
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

	public static function resetConfig()
	{
		config::save('FREEBOX_SERVER_IP', "mafreebox.freebox.fr", 'Freebox_OS');
		config::save('FREEBOX_SERVER_APP_VERSION', "v5.0.0", 'Freebox_OS');
		config::save('FREEBOX_SERVER_APP_NAME', "Plugin Freebox OS", 'Freebox_OS');
		config::save('FREEBOX_SERVER_APP_ID', "plugin.freebox.jeedom", 'Freebox_OS');
		config::save('FREEBOX_SERVER_DEVICE_NAME', config::byKey("name"), 'Freebox_OS');
	}

	public static function AddEqLogic($Name, $_logicalId, $category = null, $tiles, $eq_type, $eq_action, $logicalID_equip = null, $_autorefresh = null, $_Room = null, $Player = null)
	{
		$EqLogic = self::byLogicalId($_logicalId, 'Freebox_OS');
		log::add('Freebox_OS', 'debug', '│ Name: ' . $Name . ' -- LogicalID : ' . $_logicalId . ' -- catégorie : ' . $category . ' -- Equipement Type : ' . $eq_type . ' -- Logical ID Equip : ' . $logicalID_equip . ' -- Cron : ' . $_autorefresh . ' -- Objet : ' . $_Room);
		if (!is_object($EqLogic)) {

			$EqLogic = new Freebox_OS();
			$EqLogic->setLogicalId($_logicalId);
			if ($_Room == null) {
				$defaultRoom = intval(config::byKey('defaultParentObject', "Freebox_OS", '', true));
			} else {
				// Fonction NON désactiver A TRAITER => Pose des soucis chez certain utilisateurs (Voir Fil d'actualité du Plugin)
				$defaultRoom = intval($_Room);
			}
			if ($defaultRoom != null) {
				$EqLogic->setObject_id($defaultRoom);
			}
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
			if ($tiles == true) {
				$EqLogic->setConfiguration('type', $eq_type);
				$EqLogic->setConfiguration('action', $eq_action);
				if ($EqLogic->getConfiguration('type', $eq_type) == 'parental' || $EqLogic->getConfiguration('type', $eq_type) == 'player') {
					$EqLogic->setConfiguration('action', $logicalID_equip);
				}
				if ($Player != null) {
					$EqLogic->setConfiguration('player', $Player);
				}
			}
			$EqLogic->save();
		}
		$EqLogic->setConfiguration('logicalID', $_logicalId);
		if ($_autorefresh == null) {
			if ($tiles == true && ($EqLogic->getConfiguration('type', $eq_type) != 'parental' && $EqLogic->getConfiguration('type', $eq_type) != 'player' && $EqLogic->getConfiguration('type', $eq_type) != 'alarm_remote')) {
				$EqLogic->setConfiguration('autorefresh', '* * * * *');
			} elseif ($tiles == true && ($EqLogic->getConfiguration('type', $eq_type) == 'alarm_remote')) {
				$EqLogic->setConfiguration('autorefresh', '*/5 * * * *');
			} elseif ($EqLogic->getLogicalId() == 'disk') {
				$EqLogic->setConfiguration('autorefresh', '1 * * * *');
			} else {
				$EqLogic->setConfiguration('autorefresh', '*/5 * * * *');
			}
		}
		if ($tiles == true) {
			$EqLogic->setConfiguration('type', $eq_type);
			$EqLogic->setConfiguration('action', $eq_action);
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

	public function AddCommand($Name, $_logicalId, $Type = 'info', $SubType = 'binary', $Template = null, $unite = null, $generic_type = null, $IsVisible = 1, $link_I = 'default', $link_logicalId = 'default',  $invertBinary = '0', $icon, $forceLineB = '0', $valuemin = 'default', $valuemax = 'default', $_order = null, $IsHistorized = '0', $forceIcone_widget = false, $repeatevent = false, $_logicalId_slider = null, $_iconname = null, $_home_mode_set = null, $_calculValueOffset = null, $_historizeRound = null, $_noiconname = null, $invertSlide = null)
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

			if ($invertSlide != null) {
				$Command->setdisplay('invertslide', 1);
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
			if ($_noiconname != null) {
				$Command->setdisplay('showNameOndashboard', 0);
			}
			if ($_calculValueOffset != null) {
				$Command->setConfiguration('calculValueOffset', $_calculValueOffset);
				$Command->setConfiguration('historizeRound', $_historizeRound);
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

			if ($_iconname != null) {
				$Command->setdisplay('showIconAndNamedashboard', 1);
			}
		}

		if ($_logicalId === "tempDenied") {
			$Command->setConfiguration('listValue', '1800|0h30;3600|1h00;5400|1h30;7200|2h00;10800|3h00;14400|4h00');
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
	/*     * *********************Méthodes d'instance************************* */
	public function preInsert()
	{
	}

	public function postInsert()
	{
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

	public function preUpdate()
	{
		if (!$this->getIsEnable()) return;

		if ($this->getConfiguration('autorefresh') == '') {
			throw new Exception(__('Le champ "Temps de rafraichissement (cron)" ne peut être vide', __FILE__));
			log::add(__CLASS__, 'error', '│ Configuration : Temps de rafraichissement (cron) : ' . $this->getConfiguration('autorefresh'));
		}
	}

	public function postUpdate()
	{
	}

	public function preRemove()
	{
	}

	public function postRemove()
	{
	}

	/*     * **********************Getteur Setteur*************************** */

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
			'networkwifiguestID' => 'networkwifiguest',
			'networkwifiguestName' => 'Appareils connectés Wifi Invité',
			'notificationID' => 'notification',
			'notificationName' => 'notification',
			'phoneID' => 'phone',
			'phoneName' => 'Téléphone',
			'systemID' => 'system',
			'systemName' => 'Système',
			'wifiID' => 'wifi',
			'wifiName' => 'Wifi',
			'wifiguestID' => 'wifiguest',
			'wifiguestName' => 'Wifi Invité',
			'wifiWPSID' => 'wifiWPS',
			'wifiWPSName' => 'Wifi WPS'
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
