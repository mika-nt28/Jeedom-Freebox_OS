<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Freebox_OS_install()
{
	$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('Freebox_OS');
		$cron->setFunction('RefreshToken');
		$cron->setEnable(1);
		//$cron->setDeamon(1);
		$cron->setDeamonSleepTime(1);
		$cron->setSchedule('*/30 * * * *');
		$cron->setTimeout('10');
		$cron->save();
	}
	$cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxPUT');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('Freebox_OS');
		$cron->setFunction('FreeboxPUT');
		$cron->setDeamon(1);
		$cron->setEnable(1);
		$cron->setSchedule('* * * * *');
		//$cron->setDeamonSleepTime(1);
		$cron->setTimeout('1440');
		$cron->save();
	}
	$cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxAPI');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('Freebox_OS');
		$cron->setFunction('FreeboxAPI');
		//$cron->setDeamon(1);
		$cron->setEnable(1);
		$cron->setSchedule('0 0 * * 1');
		//$cron->setDeamonSleepTime(1);
		$cron->setTimeout('15');
		$cron->save();
	}
	updateConfig();
	config::save('FREEBOX_API', config::byKey('FREEBOX_API', 'Freebox_OS', 'v10'), 'Freebox_OS');
}
function Freebox_OS_update()
{
	$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('Freebox_OS');
		$cron->setFunction('RefreshToken');
		$cron->setEnable(1);
		$cron->setSchedule('*/30 * * * *');
		$cron->setTimeout('10');
		$cron->save();
	}
	$cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxPUT');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('Freebox_OS');
		$cron->setFunction('FreeboxPUT');
		$cron->setEnable(1);
		$cron->setDeamon(1);
		$cron->setSchedule('* * * * *');
		$cron->setTimeout('1440');
		$cron->save();
	}
	$cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxAPI');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('Freebox_OS');
		$cron->setFunction('FreeboxAPI');
		$cron->setEnable(1);
		$cron->setSchedule('0 0 * * 1');
		$cron->setTimeout('15');
		$cron->save();
	}
	updateConfig();


	try {
		log::add('Freebox_OS', 'debug', '│ Mise à jour Plugin');

		/*$WifiEX = 0;
		foreach (eqLogic::byLogicalId('Wifi', 'Freebox_OS', true) as $eqLogic) {
			$WifiEX = 1;
			log::add('Freebox_OS', 'debug', '│ Etape 1/3 : Migration Wifi déjà faite (' . $WifiEX . ')');
		}
		if ($WifiEX != 1) {
			$Wifi = Freebox_OS::AddEqLogic('Wifi', 'wifi', 'default', false, null, null);
			$link_IA = $Wifi->getId();
			log::add('Freebox_OS', 'debug', '│ Etape 1/3 : Création Equipement WIFI -- ID N° : ' . $link_IA);
		}*/

		log::add('Freebox_OS', 'debug', '│ Etape 1/4 : Update(s) nouveautée(s) + correction(s) commande(s)');

		$eqLogics = eqLogic::byType('Freebox_OS');

		log::add('Freebox_OS', 'debug', '[WARNING] - DEBUT DE NETTOYAGE LORS MIGRATION DE BOX');
		if (config::byKey('TYPE_FREEBOX', 'Freebox_OS') == 'fbxgw9r') {
			// Amélioration - Suppression des commandes en cas de migration de freebox de la delta a l'ultra
			removeLogicId2($eqLogics, 'temp_cpu_cp_master');
			removeLogicId2($eqLogics, 'temp_cpu_ap');
			removeLogicId2($eqLogics, 'temp_cpu_cp_slave');
			removeLogicId2($eqLogics, 'temp_hdd0');
			removeLogicId2($eqLogics, 'temp_t1');
			removeLogicId2($eqLogics, 'temp_t2');
			removeLogicId2($eqLogics, 'temp_t3');
			removeLogicId2($eqLogics, 'fan1_speed');
			// Amélioration - Suppression des commandes en cas de migration de freebox de la revolution a l'ultra
			removeLogicId2($eqLogics, 'temp_cpum');
			removeLogicId2($eqLogics, 'temp_cpub');
			removeLogicId2($eqLogics, 'temp_sw');
			removeLogicId2($eqLogics, 'tx_used_rate_xdsl');
			removeLogicId2($eqLogics, 'rx_used_rate_xdsl');
			removeLogicId2($eqLogics, 'rx_max_rate_xdsl');
		}

		/*	function potager_update() {
			$eqLogics = eqLogic::byType('potager');
			foreach ($eqLogics as $eqLogic) {
			  $cmd = $eqLogic->getCmd(null, 'date_semis');
			  if (is_object($cmd)) {
				$cmd->remove();
			  }
			}
		  }*/

		foreach ($eqLogics as $eqLogic) {
			//=> Suppression des anciennes commandes Airmedia
			//removeLogicId($eqLogic, 'ActualAirmedia'); // Amélioration 20220806
			//removeLogicId($eqLogic, 'airmediastart'); // Amélioration 20220806
			//removeLogicId($eqLogic, 'airmediastop'); // Amélioration 20220806

			// 4G => Nouvelle API
			//removeLogicId($eqLogic, 'protocol'); // Amélioration 20221208
			//removeLogicId($eqLogic, 'modulation'); // Amélioration 20221208


			// a faire plus tard
			// removeLogicId($eqLogic, 'add_del_mac'); // Amélioration 20220827
			// removeLogicId($eqLogic, 'WakeonLAN'); // Amélioration 20220827
			// removeLogicId($eqLogic, 'mac_filter_state'); // Amélioration 20220827
			// removeLogicId($eqLogic, 'redir'); // Amélioration 20220827
			//
			// removeLogicId($eqLogic, 'host_info'); // Amélioration 20220827
			// removeLogicId($eqLogic, 'host'); // Amélioration 20220827
			// removeLogicId($eqLogic, 'host_mac'); // Amélioration 20220827
			//
			//removeLogicId($eqLogic, 'wifimac_filter_state'); // Amélioration 20220827
			//removeLogicId($eqLogic, 'mac_filter_state'); // Amélioration 20220827
			//=> Libre
			//removeLogicId($eqLogic, 'schedule'); // Amélioration 20210627
			//removeLogicId($eqLogic, ' schedule'); // Amélioration 20210627

		}

		log::add('Freebox_OS', 'debug', '│ Etape 2/4 : Changement de nom de certains équipements');
		$eq_version = '2.1';
		Freebox_OS::updateLogicalID($eq_version, true);
		log::add('Freebox_OS', 'debug', '│ Etape 3/4 : Update paramétrage Plugin tiles');
		if ($eq_version === '2') {
			/* CRON GLOBAL TITLES
			if (config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS') == 'OK') {
				$Config_KEY = config::byKey('FREEBOX_TILES_CRON', 'Freebox_OS');
				if (empty($Config_KEY)) {
					config::save('FREEBOX_TILES_CRON', '1', 'Freebox_OS');
					Free_CreateTil::createTil('SetSettingTiles');
				}
			}*/
			/* UPDATE CMD BY CMD
			$Config_KEY = config::byKey('FREEBOX_TILES_CmdbyCmd', 'Freebox_OS');
			if (empty($Config_KEY)) {
				config::save('FREEBOX_TILES_CmdbyCmd', '1', 'Freebox_OS');
			}*/
		}
		log::add('Freebox_OS', 'debug', '│ Etape 4/4 : Création API');
		$Config_KEY = config::byKey('FREEBOX_API', 'Freebox_OS');
		if (empty($Config_KEY)) {
			config::save('FREEBOX_API', 'v10', 'Freebox_OS');
			log::add('Freebox_OS', 'debug', '│ Update Version API en V10');
		}
		$Config_KEY = config::byKey('FREEBOX_REBOOT_DEAMON', 'Freebox_OS');
		if (empty($Config_KEY)) {
			config::save('FREEBOX_REBOOT_DEAMON', FALSE, 'Freebox_OS');
		}

		//message::add('Freebox_OS', '{{Cette mise nécessite de lancer les divers Scans afin de bénéficier des nouveautés et surtout des correctifs}}');
	} catch (Exception $e) {
		$e = print_r($e, 1);
		log::add('Freebox_OS', 'error', 'Freebox_OS update ERROR : ' . $e);
	}
}
function Freebox_OS_remove()
{
	$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
	if (is_object($cron)) {
		$cron->stop();
		$cron->remove();
	}
	$cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxPUT');
	if (is_object($cron)) {
		$cron->stop();
		$cron->remove();
	}
	$cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxGET');
	if (is_object($cron)) {
		$cron->stop();
		$cron->remove();
	}
	$cron = cron::byClassAndFunction('Freebox_OS', 'FreeboxAPI');
	if (is_object($cron)) {
		$cron->stop();
		$cron->remove();
	}
}

function UpdateLogicId($eqLogic, $from, $to = null, $SubType = null, $unite = null, $_calculValueOffset = null, $_historizeRound = null)
{
	//  Fonction update commande (Changement equipement, changement sous type)
	$cmd = $eqLogic->getCmd(null, $from);
	if (is_object($cmd)) {
		//changement equipement
		if ($to != null) {
			$cmd->seteqLogic_id($to);
		}
		//Update sous type
		if ($SubType != null) {
			$cmd->setSubType($SubType);
		}

		$cmd->save();
	}
}
function removeLogicId2($eqLogic, $cmdDel)
{
	$eqLogics = eqLogic::byType('Freebox_OS');
	foreach ($eqLogics as $eqLogic) {
		$cmd = $eqLogic->getCmd(null, '$cmdDel');
		if (is_object($cmd)) {
			$cmd->remove();
		}
	}
}
function removeLogicId($eqLogic, $cmdDell)
{
	//  suppression fonction
	$cmd = $eqLogic->getCmd(null, $cmdDell);
	if (is_object($cmd)) {
		$cmd->remove();
	}
}

function updateConfig()
{
	config::save('FREEBOX_SERVER_IP', config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS', "mafreebox.freebox.fr"), 'Freebox_OS');
	//config::save('FREEBOX_SERVER_APP_VERSION', config::byKey('FREEBOX_SERVER_APP_VERSION', 'Freebox_OS', "v5.0.0"), 'Freebox_OS');
	config::save('FREEBOX_SERVER_APP_NAME', config::byKey('FREEBOX_SERVER_APP_NAME', 'Freebox_OS', "Plugin Freebox OS"), 'Freebox_OS');
	config::save('FREEBOX_SERVER_APP_ID', config::byKey('FREEBOX_SERVER_APP_ID', 'Freebox_OS', "plugin.freebox.jeedom"), 'Freebox_OS');
	config::save('FREEBOX_SERVER_DEVICE_NAME', config::byKey('FREEBOX_SERVER_DEVICE_NAME', 'Freebox_OS', config::byKey("name")), 'Freebox_OS');
	config::save('FREEBOX_REBOOT_DEAMON', config::byKey('FREEBOX_REBOOT_DEAMON', 'Freebox_OS', FALSE), 'Freebox_OS');

	$version = 1;
	if (config::byKey('FREEBOX_CONFIG_V', 'Freebox_OS', 0) != $version) {
		Freebox_OS::resetConfig();
		config::save('FREEBOX_CONFIG_V', $version, 'Freebox_OS');
	}
}
