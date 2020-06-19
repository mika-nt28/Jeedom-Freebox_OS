<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Freebox_OS_install()
{
	Freebox_OS::CreateArchi();
}
function Freebox_OS_update()
{
	log::add('Freebox_OS', 'debug', '│ Mise à jour Plugin');

	log::add('Freebox_OS', 'debug', '│ Etape 1/4 : Suppression FreeboxTv');
	foreach (eqLogic::byLogicalId('FreeboxTv', 'Freebox_OS', true) as $eqLogic) {
		$eqLogic->remove();
		log::add('Freebox_OS', 'debug', '│ Etape 1 : OK pour la suppression');
	}
	$Wifi = Freebox_OS::AddEqLogic('Wifi', 'Wifi');
	$Wificmd = $Wifi->getId();
	log::add('Freebox_OS', 'debug', '│ Etape 2/4 : Création Equipement WIFI -- ID N° : ' . $Wificmd);

	log::add('Freebox_OS', 'debug', '│ Etape 3/4 : Update nouveautés + corrections commandes');

	$eqLogics = eqLogic::byType('Freebox_OS');
	foreach ($eqLogics as $eqLogic) {
		UpdateLogicId($eqLogic, 'wifiStatut', $Wificmd); // Amélioration 20200616
		UpdateLogicId($eqLogic, 'wifiOff', $Wificmd, '', 'wifiStatut'); // Amélioration 20200616
		UpdateLogicId($eqLogic, 'wifiOn', $Wificmd, '', 'wifiStatut'); // Amélioration 20200616
		UpdateLogicId($eqLogic, 'wifiOnOff', $Wificmd, '', 'wifiStatut'); // Amélioration 20200616
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
	log::add('Freebox_OS', 'debug', '│ Etape 4/4 : Sauvegarde de l\'ensemble des équipements');
	$eqs = eqLogic::byType('Freebox_OS');
	foreach ($eqs as $eq) {
		$eq->save();
	}
	/*resave eqLogics for new cmd:
	try {
		
	} catch (Exception $e) {
		$e = print_r($e, 1);
		log::add('Freebox_OS', 'error', 'Freebox_OS update ERROR : ' . $e);
	}*/
}
function Freebox_OS_remove()
{
	while (is_object($cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation')))
		$cron->remove();
	if (is_object($cron = cron::byClassAndFunction('Freebox_OS', 'RefreshToken')))
		$cron->remove();
}

function UpdateLogicId($eqLogic, $from, $to = null, $SubType = null, $linkedlogicalId = null)
{
	//  Fonction update commande (Changement equipement, changement sous type)
	$cmd = $eqLogic->getCmd(null, $from);
	if (is_object($cmd)) {
		if ($to != null) {
			$cmd->seteqLogic_id($to);
		}
		if ($SubType != null) {
			$cmd->setSubType($SubType);
		}
		if ($linkedlogicalId != null) {
			$cmd->getCmd($linkedlogicalId, 'logicalId')->getId();
		}
		$cmd->save();
	}
}
