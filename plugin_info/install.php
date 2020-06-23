<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Freebox_OS_install()
{
	//Freebox_OS::CreateArchi(); // Fonction désactiver 20200623
}
function Freebox_OS_update()
{
	try {
		log::add('Freebox_OS', 'debug', '│ Mise à jour Plugin');

		log::add('Freebox_OS', 'debug', '│ Etape 1/5 : Suppression FreeboxTv');
		foreach (eqLogic::byLogicalId('FreeboxTv', 'Freebox_OS', true) as $eqLogic) {
			$eqLogic->remove();
			log::add('Freebox_OS', 'debug', '│ Etape 1 : OK pour la suppression');
		}
		$WifiEX = 0;
		foreach (eqLogic::byLogicalId('Wifi', 'Freebox_OS', true) as $eqLogic) {
			$WifiEX = 1;
			log::add('Freebox_OS', 'debug', '│ Etape 2/5 : Migration Wifi déjà faite (' . $WifiEX . ')');
		}
		if ($WifiEX != 1) {
			$Wifi = Freebox_OS::AddEqLogic('Wifi', 'Wifi');
			$link_IA = $Wifi->getId();
			log::add('Freebox_OS', 'debug', '│ Etape 2/5 : Création Equipement WIFI -- ID N° : ' . $link_IA);
		}

		log::add('Freebox_OS', 'debug', '│ Etape 3/5 : Update nouveautés + corrections commandes');

		$eqLogics = eqLogic::byType('Freebox_OS');
		foreach ($eqLogics as $eqLogic) {
			if ($WifiEX != 1) {
				UpdateLogicId($eqLogic, 'wifiStatut', $link_IA); // Amélioration 20200616
				UpdateLogicId($eqLogic, 'wifiOff', $link_IA); // Amélioration 20200616
				UpdateLogicId($eqLogic, 'wifiOn', $link_IA); // Amélioration 20200616
				UpdateLogicId($eqLogic, 'wifiOnOff', $link_IA); // Amélioration 20200616
			}
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
		log::add('Freebox_OS', 'debug', '│ Etape 4/5 : Sauvegarde de l\'ensemble des équipements');
		/*$eqs = eqLogic::byType('Freebox_OS');
		foreach ($eqs as $eq) {
			$eq->save();
		}*/
		log::add('Freebox_OS', 'debug', '│ Etape 5/5 : Mise à jour de l\'ensemble des composants Freebox Hors Tiles');
		Freebox_OS::CreateArchi(); //relance Update composant Freebox
		/*resave eqLogics for new cmd: */
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
