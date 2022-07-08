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
	updateConfig();
}
function Freebox_OS_update()
{
	$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('Freebox_OS');
		$cron->setFunction('RefreshToken');
		$cron->setEnable(1);
		//$cron->setDeamon(1);
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
		//$cron->setDeamonSleepTime(1);
		$cron->setSchedule('* * * * *');
		$cron->setTimeout('1440');
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
		foreach ($eqLogics as $eqLogic) {
			removeLogicId($eqLogic, 'slow'); // Amélioration 20210627
			removeLogicId($eqLogic, 'normal'); // Amélioration 20210627
			removeLogicId($eqLogic, 'hibernate'); // Amélioration 20210627
			removeLogicId($eqLogic, 'schedule'); // Amélioration 20210627
			removeLogicId($eqLogic, ' schedule'); // Amélioration 20210627

		}

		log::add('Freebox_OS', 'debug', '│ Etape 2/4 : Changement de nom de certains équipements');
		$eq_version = '2.1';
		Freebox_OS::updateLogicalID($eq_version, true);
		log::add('Freebox_OS', 'debug', '│ Etape 3/4 : Update paramétrage Plugin tiles');
		if ($eq_version === '2') {
			/*if (config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS') == 'OK') {
				if (!is_object(config::byKey('FREEBOX_TILES_CRON', 'Freebox_OS'))) {
					config::save('FREEBOX_TILES_CRON', '1', 'Freebox_OS');
					Free_CreateTil::createTil('SetSettingTiles');
				}
			}*/
			/*if (!is_object(config::byKey('FREEBOX_TILES_CmdbyCmd', 'Freebox_OS'))) {
				config::save('FREEBOX_TILES_CmdbyCmd', '1', 'Freebox_OS');
			}*/
		}
		log::add('Freebox_OS', 'debug', '│ Etape 4/4 : Mise à jour Version API freebox');
		if (!is_object(config::byKey('API_FREEBOX', 'Freebox_OS'))) {
			config::save('API_FREEBOX', config::byKey('API_FREEBOX', 'Freebox_OS', 'v8'), 'Freebox_OS');
		}

		//message::add('Freebox_OS', 'Merci pour la mise à jour de ce plugin, n\'oubliez pas de lancer les divers Scans afin de bénéficier des nouveautés');
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

function removeLogicId($eqLogic, $from, $link_IA = null)
{
	//  suppression fonction
	$cmd = $eqLogic->getCmd(null, $from);
	if (is_object($cmd)) {
		$cmd->remove();
	}
}

function updateConfig()
{
	config::save('FREEBOX_SERVER_IP', config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS', "mafreebox.freebox.fr"), 'Freebox_OS');
	config::save('FREEBOX_SERVER_APP_VERSION', config::byKey('FREEBOX_SERVER_APP_VERSION', 'Freebox_OS', "v5.0.0"), 'Freebox_OS');
	config::save('FREEBOX_SERVER_APP_NAME', config::byKey('FREEBOX_SERVER_APP_NAME', 'Freebox_OS', "Plugin Freebox OS"), 'Freebox_OS');
	config::save('FREEBOX_SERVER_APP_ID', config::byKey('FREEBOX_SERVER_APP_ID', 'Freebox_OS', "plugin.freebox.jeedom"), 'Freebox_OS');
	config::save('FREEBOX_SERVER_DEVICE_NAME', config::byKey('FREEBOX_SERVER_DEVICE_NAME', 'Freebox_OS', config::byKey("name")), 'Freebox_OS');
	//config::save('FREEBOX_API', config::byKey('FREEBOX_API', 'Freebox_OS', 'v8'), 'Freebox_OS');
	$version = 1;
	if (config::byKey('FREEBOX_CONFIG_V', 'Freebox_OS', 0) != $version) {
		Freebox_OS::resetConfig();
		config::save('FREEBOX_CONFIG_V', $version, 'Freebox_OS');
	}
}
