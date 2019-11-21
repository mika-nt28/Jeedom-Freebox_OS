<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Freebox_OS_install() {	
	Freebox_OS::CreateArchi();
}
function Freebox_OS_update() {
	log::add('Freebox_OS','debug','Lancement du script de mise à jour'); 
	foreach(eqLogic::byLogicalId('FreeboxTv','Freebox_OS',true) as $eqLogic){
		$eqLogic->remove();
	}
	foreach(eqLogic::type('Freebox_OS') as $eqLogic){
		if($eqLogic->getConfiguration('waite') == ''){
			$eqLogic->setConfiguration('waite',config::byKey('DemonSleep','Freebox_OS'));
			$eqLogic->save();
		}
	}
	log::add('Freebox_OS','debug','Fin du script de mise à jour');
}
function Freebox_OS_remove() {
}
?>
