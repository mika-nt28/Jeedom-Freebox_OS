<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Freebox_OS_install()
{
	Freebox_OS::CreateArchi();
}
function Freebox_OS_update()
{
	log::add('Freebox_OS', 'debug', '│ Mise à jour Plugin');

	log::add('Freebox_OS', 'debug', '│ Etape 1/5 : Suppression FreeboxTv');
	foreach (eqLogic::byLogicalId('FreeboxTv', 'Freebox_OS', true) as $eqLogic) {
		$eqLogic->remove();
		log::add('Freebox_OS', 'debug', '│ Etape 1 : OK pour la suppression');
	}

	log::add('Freebox_OS', 'debug', '│ Etape 2/5 : Création Equipement WIFI');
	$Wifi = Freebox_OS::AddEqLogic('Wifi', 'Wifi');
	//Freebox_OS::newWifi();

	$Wificmd = $Wifi->getId();
	log::add('Freebox_OS', 'debug', '│ Etape 3/5 : Création Equipement WIFI -- ID : ' . $Wificmd);


	log::add('Freebox_OS', 'debug', '│ Etape 4/5 : Update nouveautés + corrections commandes');

	$plugin = plugin::byId('Freebox_OS');
	$eqLogics = eqLogic::byType($plugin->getId());
	foreach ($eqLogics as $eqLogic) {
		UpdateLogicId($eqLogic, 'wifiOnOff', $Wificmd); // Amélioration 20200616
		UpdateLogicId($eqLogic, 'wifiStatut', $Wificmd); // Amélioration 20200616
		UpdateLogicId($eqLogic, 'wifiOff', $Wificmd); // Amélioration 20200616
		UpdateLogicId($eqLogic, 'wifiOn', $Wificmd); // Amélioration 20200616
		UpdateLogicId($eqLogic, 'fan_rpm', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'temp_cpub', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'temp_cpum', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'temp_sw', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks_active', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks_extracting', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks_repairing', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks_checking', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks_queued', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks_error', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks_stopped', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks_done', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'nb_tasks_downloading', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'rx_rate', '', 'numeric'); // Correction sous Type 20200616
		UpdateLogicId($eqLogic, 'tx_rate', '', 'numeric'); // Correction sous Type 20200616
	}
	log::add('Freebox_OS', 'debug', '│ Etape 5/5 : Sauvegarde de l\'ensemble des équipements');
	//resave eqLogics for new cmd:
	try {
		$eqs = eqLogic::byType('Freebox_OS');
		foreach ($eqs as $eq) {
			$eq->save();
		}
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

function UpdateLogicId($eqLogic, $from, $to = null, $SubType = null)
{
	//  Fonction pour renommer une commande
	$command = $eqLogic->getCmd(null, $from);
	if (is_object($command)) {
		if ($to != null) {
			$command->seteqLogic_id($to);
		}
		if ($SubType != null) {
			$command->setSubType($SubType);
		}
		$command->save();
	}
}
