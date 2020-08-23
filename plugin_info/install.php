<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Freebox_OS_install()
{
	updateConfig();
}
function Freebox_OS_update()
{

	updateConfig();

	try {
		log::add('Freebox_OS', 'debug', '│ Mise à jour Plugin');

		$WifiEX = 0;
		foreach (eqLogic::byLogicalId('Wifi', 'Freebox_OS', true) as $eqLogic) {
			$WifiEX = 1;
			log::add('Freebox_OS', 'debug', '│ Etape 1/3 : Migration Wifi déjà faite (' . $WifiEX . ')');
		}
		if ($WifiEX != 1) {
			$Wifi = Freebox_OS::AddEqLogic('Wifi', 'wifi', 'default', false, null, null);
			$link_IA = $Wifi->getId();
			log::add('Freebox_OS', 'debug', '│ Etape 1/3 : Création Equipement WIFI -- ID N° : ' . $link_IA);
		}

		log::add('Freebox_OS', 'debug', '│ Etape 2/3 : Update(s) nouveautée(s) + correction(s) commande(s)');

		while (is_object($cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation')))
			$cron->remove();

		$eqLogics = eqLogic::byType('Freebox_OS');
		foreach ($eqLogics as $eqLogic) {
			if ($WifiEX != 1) {
				UpdateLogicId($eqLogic, 'wifiOff', $link_IA); // Amélioration 20200616
				UpdateLogicId($eqLogic, 'wifiOn', $link_IA); // Amélioration 20200616
				UpdateLogicId($eqLogic, 'wifiStatut'); // Amélioration 20200820
			}

			removeLogicId($eqLogic, 'wifiOnOff', $link_IA); // Amélioration 20200820
			removeLogicId($eqLogic, 'port_forwarding'); // Amélioration 20200820
			removeLogicId($eqLogic, 'nbAppelsManquee'); // Amélioration 20200820
			removeLogicId($eqLogic, 'nbAppelRecus'); // Amélioration 20200820
			removeLogicId($eqLogic, 'nbAppelPasse'); // Amélioration 20200820
			removeLogicId($eqLogic, 'listAppelsManquee'); // Amélioration 20200820
			removeLogicId($eqLogic, 'listAppelsRecus'); // Amélioration 20200820
			removeLogicId($eqLogic, 'listAppelsPasse'); // Amélioration 20200820
			removeLogicId($eqLogic, 'sonnerieDectOn'); // Amélioration 20200820
			removeLogicId($eqLogic, 'sonnerieDectOff'); // Amélioration 20200820
			removeLogicId($eqLogic, 'rate_down'); // Amélioration 20200823
			removeLogicId($eqLogic, 'rate_up'); // Amélioration 20200823
			removeLogicId($eqLogic, 'bandwidth_up'); // Amélioration 20200823
			removeLogicId($eqLogic, 'bandwidth_down'); // Amélioration 20200823
			removeLogicId($eqLogic, 'media'); // Amélioration 20200823
			removeLogicId($eqLogic, 'state'); // Amélioration 20200823
		}

		log::add('Freebox_OS', 'debug', '│ Etape 3/3 : Changement de nom de certains équipements');
		Freebox_OS::updateLogicalID(1, true);

		message::add('Freebox_OS', 'Merci pour la mise à jour de ce plugin, n\'oubliez pas de lancer les divers Scans afin de bénéficier des nouveautés');
	} catch (Exception $e) {
		$e = print_r($e, 1);
		log::add('Freebox_OS', 'error', 'Freebox_OS update ERROR : ' . $e);
	}
}
function Freebox_OS_remove()
{
	while (is_object($cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation')))
		$cron->remove();
	if (is_object($cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken')))
		$cron->remove();
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

function removeLogicId($eqLogic, $from)
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

	$version = 1;
	if (config::byKey('FREEBOX_CONFIG_V', 'Freebox_OS', 0) != $version) {
		Freebox_OS::resetConfig();
		config::save('FREEBOX_CONFIG_V', $version, 'Freebox_OS');
	}
}
