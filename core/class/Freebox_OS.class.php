<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
include_file('core', 'FreeboxAPI', 'class', 'Freebox_OS');

class Freebox_OS extends eqLogic {
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'Freebox_OS';
		if (trim(config::byKey('FREEBOX_SERVER_IP','Freebox_OS'))!='' && config::byKey('FREEBOX_SERVER_APP_TOKEN','Freebox_OS')!='' && trim(config::byKey('FREEBOX_SERVER_APP_ID','Freebox_OS'))!='') {
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
	public static function deamon_start($_debug = false) {
		log::remove('Freebox_OS');
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') return;
		if ($deamon_info['state'] == 'ok') return;
		$cron =cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('Freebox_OS');
			$cron->setFunction('RefreshToken');
			$cron->setEnable(1);
			$cron->setSchedule('15 * * * *');$cron->setTimeout('1');
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
	public static function deamon_stop() {
		$cron =cron::byClassAndFunction('Freebox_OS', 'RefreshToken');
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
	private function CreateDemon() {
		$cron =cron::byClassAndFunction('Freebox_OS', 'RefreshInformation', array('Freebox_id' => $this->getId()));
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
	public static function AddEqLogic($Name,$_logicalId,$category=null) {
		$EqLogic = self::byLogicalId($_logicalId, 'Freebox_OS');
		if (!is_object($EqLogic)) {
			$EqLogic = new Freebox_OS();
			$EqLogic->setLogicalId($_logicalId);
			$EqLogic->setObject_id(null);
			$EqLogic->setEqType_name('Freebox_OS');
			$EqLogic->setIsEnable(1);
			$EqLogic->setIsVisible(0);
			$EqLogic->setName($Name);
			if ($category!=null) {
				$EqLogic->setcategory($category,1);
			}
			$EqLogic->setConfiguration('waite','300');
			$EqLogic->save();
		}
		return $EqLogic;
	}
	public static function addReseau() {
		$FreeboxAPI= new FreeboxAPI();
		$Reseau=self::AddEqLogic('Réseau','Reseau');
		foreach ($FreeboxAPI->getReseau() as $Equipement) {
			if ($Equipement['primary_name']!='') {
				$Commande=$Reseau->AddCommande($Equipement['primary_name'],$Equipement['id'],"info",'binary','Freebox_OS_Reseau');
				$Commande->setConfiguration('host_type',$Equipement['host_type']);
				if (isset($Equipement['l3connectivities']))	{
					foreach ($Equipement['l3connectivities'] as $Ip) {
						if ($Ip['active']) {
							if ($Ip['af']=='ipv4'){
								$Commande->setConfiguration('IPV4',$Ip['addr']);
							} else {
								$Commande->setConfiguration('IPV6',$Ip['addr']);
							}
						}
					}
				}
				if ($Commande->execCmd() != $Equipement['active']) {
					$Commande->setCollectDate(date('Y-m-d H:i:s'));
					$Commande->setConfiguration('doNotRepeatEvent', 1);
					$Commande->event($Equipement['active']);
				}
				$Commande->save();
			}
		}
	}
	public static function addHomeAdapters() {
		$FreeboxAPI= new FreeboxAPI();
		$HomeAdapters=self::AddEqLogic('Home Adapters','HomeAdapters');
		foreach ($FreeboxAPI->getHomeAdapters() as $Equipement) {
			if ($Equipement['label']!=''){
				$HomeAdapters->AddCommande($Equipement['label'],$Equipement['id'],"info",'binary');
				$HomeAdapters->checkAndUpdateCmd($Equipement['id'],$Equipement['status']);
			}
		}
	}
	public static function addTiles() {
		$FreeboxAPI= new FreeboxAPI();
		foreach ($FreeboxAPI->getTiles() as $Equipement){
			if ($Equipement['type'] != 'camera'){
				if ($Equipement['type'] =='alarm_sensor' || $Equipement['type'] =='alarm_control' || $Equipement['type'] =='alarm_remote' ) {
					$category = 'security';
				} elseif ($Equipement['type'] =='light') {
					$category = 'light';
				} else {
					$category = null;
				}
				$Equipement['label'] = preg_replace('/\'+/',' ',$Equipement['label']); // Suppression '
				if (isset($Equipement['label']) ) {
					$Tile=self::AddEqLogic($Equipement['label'],$Equipement['node_id'],$category);
				} else {
					$Tile=self::AddEqLogic($Equipement['type'],$Equipement['node_id'],$category);
				}
			}
			foreach ($Equipement['data'] as $Commande) {
				if ($Commande['label'] != '') {
					$info = null;
					$action = null;
					$generic_type = null;
					$label_sup = null;
					$infoCmd = null;
					$IsVisible = 1;
					if ($Equipement['type'] == 'camera' && method_exists('camera','getUrl')) {
						$parameter['name']=$Commande['label'];
						$parameter['id']=$Commande['ep_id'];
						$parameter['url']=$Commande['value'];
						event::add('Freebox_OS::camera', json_encode($parameter));
						continue;
					}
					if (!is_object($Tile)) continue;
					log::add(__CLASS__,'debug','┌───────── Commande trouvée pour l\'équipement FREEBOX : '.$Equipement['label'] .' (Node ID '.$Equipement['node_id'] .')');
					$Commande['label'] = preg_replace('/É+/','E',$Commande['label']);// Suppression É
					$Commande['label'] = preg_replace('/\'+/',' ',$Commande['label']);// Suppression '
					log::add(__CLASS__,'debug','│ label : ' .$Commande['label'] .' -- name : '.$Commande['name']);
					log::add(__CLASS__,'debug','│ type  : ' .$Equipement['type'] .' -- action : ' .$Equipement['action']);
					log::add(__CLASS__,'debug','│ Index : ' .$Commande['ep_id'] .' -- Value Type : ' .$Commande['value_type'] .' -- Access : '.$Commande['ui']['access']);
					log::add(__CLASS__,'debug','│ valeur actuelle : '.$Commande['value'] .' -- Unité : ' .$Commande['ui']['unit']);

					switch ($Commande['value_type']) {
						case "void":
							if ($Commande['name'] == 'up') {
								$generic_type ='FLAP_UP';
							} elseif ($Commande['name'] == 'stop') {
								$generic_type ='FLAP_STOP';
							} elseif ($Commande['name'] == 'down') {
								$generic_type ='FLAP_DOWN';
							} else {
								$generic_type = null;
							}
							$action = $Tile->AddCommandTiles($Commande['label'],$Commande['ep_id'],'action','other','',$Commande['ui']['unit'],$generic_type,1,'','');
							break;
                        case "int":
							foreach (str_split($Commande['ui']['access']) as $access) {
								if ($access == "r") {
									if ($Commande['ui']['access'] == "rw") {
										$label_sup ='Etat ';
									}
									if ($Commande['name'] =="luminosity" && $Equipement['action'] !="color_picker") {
										$infoCmd = $Tile->AddCommandTiles($label_sup .$Commande['label'],$Commande['ep_id'],'info','numeric','',$Commande['ui']['unit'], 'LIGHT_STATE',0,'',$Commande['ep_id'],'light');
										$Tile->AddCommandTiles($Commande['label'],$Commande['ep_id'],'action','slider','',$Commande['ui']['unit'],'LIGHT_SLIDER', 1, $infoCmd,$Commande['ep_id'],'light');

									} elseif ($Commande['name'] !="luminosity" && $Equipement['action'] =="color_picker") {
										$infoCmd = $Tile->AddCommandTiles($label_sup .$Commande['label'],$Commande['ep_id'],'info','numeric','',$Commande['ui']['unit'], 'LIGHT_COLOR',0,'',$Commande['ep_id'],'light');
										$Tile->AddCommandTiles($Commande['label'],$Commande['ep_id'],'action','slider','',$Commande['ui']['unit'],'LIGHT_SET_COLOR',1, $infoCmd,$Commande['ep_id'],'light');

									} elseif ($Commande['name'] =="battery_warning" ) {
										$Tile->AddCommandTiles($Commande['label'],$Commande['ep_id'],'info', 'numeric','',$Commande['ui']['unit'],'BATTERY',1,'','');

									} else {
										$info = $Tile->AddCommandTiles($label_sup. $Commande['label'],$Commande['ep_id'],'info','numeric','',$Commande['ui']['unit'],$generic_type,$IsVisible,'','');
									}
									$label_sup ='';
									$Tile->checkAndUpdateCmd($Commande['ep_id'],$Commande['value']);
									if ($Commande['name'] =="battery_warning") {
										//$Tile->batteryStatus($Commande['value']);
									}
								}
								if ($access == "w") {
									if ($Commande['name'] !="luminosity" && $Equipement['action'] !="color_picker") {
										$action = $Tile->AddCommandTiles($label_sup. $Commande['label'],$Commande['ep_id'],'action','slider','',$Commande['ui']['unit'],$generic_type,$IsVisible,'','');
									}
								}
							}
							break;
                        case "bool":
							foreach (str_split($Commande['ui']['access']) as $access) {
								if ($access == "r") {
									if ($Equipement['action'] == "store") {
										$generic_type ='FLAP_STATE';
									} elseif ($Equipement['type'] == "alarm_sensor" && $Commande['name'] =='cover') {
										$generic_type ='SABOTAGE';
									} elseif ($Equipement['type'] == "alarm_sensor" && $Commande['name'] =='trigger') {
										$generic_type ='OPENING';
									}
									if ($Commande['label']=='Enclenché') {
										$infoCmd = $Tile->AddCommandTiles('Etat', $Commande['ep_id'],'info','binary','',$Commande['ui']['unit'],'LIGHT_STATE', 0,'',$Commande['ep_id'],'light');
										$Tile->AddCommandTiles('On','PB_On','action','other','',$Commande['ui']['unit'],'LIGHT_ON',1,$infoCmd,$Commande['ep_id'],'light');
										$Tile->AddCommandTiles('Off','PB_Off','action','other','',$Commande['ui']['unit'],'LIGHT_OFF',1,$infoCmd,$Commande['ep_id'],'light');
									} else {
										$infoCmd = $Tile->AddCommandTiles($Commande['label'],$Commande['ep_id'],'info','binary','',$Commande['ui']['unit'],$generic_type,1,'','');
									}
									$Tile->checkAndUpdateCmd($Commande['ep_id'],$Commande['value']);
									$label_sup ='';
									$generic_type ='';
								}
								if ($access == "w") {
									if ($Commande['label']!='Enclenché') {
										$action = $Tile->AddCommandTiles($label_sup. $Commande['label'],$Commande['ep_id'],'action','other','',$Commande['ui']['unit'],$generic_type,$IsVisible,'','');
									}
								}
							}
							break;
						case "string":
							foreach (str_split($Commande['ui']['access']) as $access) {
								if ($Commande['name'] == "pin") {
									$IsVisible = 0;
								} else {
									$IsVisible = 1;
								}
								if ($access == "r") {
									if ($Commande['ui']['access'] == "rw") {
										$label_sup ='Etat ';
									}
									$info = $Tile->AddCommandTiles($label_sup. $Commande['label'],$Commande['ep_id'],'info','string','',$Commande['ui']['unit'],$generic_type,$IsVisible,'','');
									$Tile->checkAndUpdateCmd($Commande['ep_id'],$Commande['value']);
									$label_sup ='';
								}
								if ($access == "w") {
									$action = $Tile->AddCommandTiles($label_sup. $Commande['label'],$Commande['ep_id'],'action','message','',$Commande['ui']['unit'],$generic_type,$IsVisible,'','');
								}
							}
							break;
					}
					if (is_object($info) && is_object($action)) {
						$action->setValue($info->getId());
						$action->save();
					}
					log::add(__CLASS__,'debug','└─────────');
				}
			}
		}
	}
	public function AddCommandTiles($Name, $_logicalId, $Type='info',$SubType='binary',$Template='default',$unite=null,$generic_type=null,$IsVisible=1, $linkedInfoCmd=null,$linkedlogicalId='NO_LINK',$Templatecore='default') {
		log::add(__CLASS__, 'debug', '│ Type : ' .$Type .' -- LogicalID : '.$_logicalId.' -- Type de générique : '.$generic_type);

		$Commande= $this->getCmd($Type, $_logicalId);
		if (!is_object($Commande)){
			$VerifName=$Name;
			$Commande = new cmd();
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
			$count=0;
			while (is_object(cmd::byEqLogicIdCmdName($this->getId(),$VerifName)) ) {
				$count++;
				$VerifName=$Name.'('.$count.')';
			}
			$Commande->setName($VerifName);

			$Commande->setType($Type);
			$Commande->setSubType($SubType);
			$Commande->setGeneric_type($generic_type);

			$Commande->setIsVisible($IsVisible);
			$Commande->setIsHistorized(0);

			$Commande->setUnite($unite);

			if ($Template !='default') {
				$Commande->setTemplate('dashboard','Freebox_OS::'.$Template);
				$Commande->setTemplate('mobile', 'Freebox_OS::'.$Template);
			}
			if ($Templatecore !='default') {
				$Commande->setTemplate('dashboard','core::'.$Templatecore);
				$Commande->setTemplate('mobile','core::'.$Templatecore);
			}

			if ($linkedlogicalId >= 0 && $Type=="action" ) {
				log::add(__CLASS__, 'debug', '│ Type : ' .$Type);
				$Commande->setconfiguration('logicalId',$linkedlogicalId);
			}

			if (is_object($linkedInfoCmd) && $Type=='action') {
				$Commande->setValue($linkedInfoCmd->getId());
			}
			$Commande->save();
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
		return $Commande;
	}
	public function AddCommande($Name,$_logicalId,$Type='info',$SubType='binary',$Template='default',$unite=null,$generic_type=null,$IsVisible=1) {
		log::add(__CLASS__, 'debug', '│ Type : ' .$Type .' -- LogicalID : '.$_logicalId.' -- Type de générique : '.$generic_type);

		$Commande = $this->getCmd($Type,$_logicalId);
		if (!is_object($Commande)){
			$VerifName=$Name;

			$Commande = new Freebox_OSCmd();
			$Commande->setId(null);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
			$count=0;
			while (is_object(cmd::byEqLogicIdCmdName($this->getId(),$VerifName)) ) {
				$count++;
				$VerifName=$Name.'('.$count.')';
			}
			$Commande->setName($VerifName);

			$Commande->setType($Type);
			$Commande->setSubType($SubType);
			$Commande->setGeneric_type($generic_type);

			$Commande->setIsVisible($IsVisible);
			$Commande->setIsHistorized(0);

            $Commande->setUnite($unite);

			if ($Template !='default') {
				$Commande->setTemplate('dashboard','Freebox_OS::'.$Template);
				$Commande->setTemplate('mobile', 'Freebox_OS::'.$Template);
			} else {
				$Commande->setTemplate('dashboard','default');
				$Commande->setTemplate('mobile','default');
			}
			$Commande->save();
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
		return $Commande;
	}
	public static function CreateArchi() {
		self::AddEqLogic('Home Adapters','HomeAdapters');
		self::AddEqLogic('Réseau','Reseau');
		self::AddEqLogic('Disque Dur','Disque');
		// ADSL
		$ADSL=self::AddEqLogic('ADSL','ADSL');
		$ADSL->AddCommande('Freebox rate down','rate_down',"info",'numeric','','Ko/s');
		$ADSL->AddCommande('Freebox rate up','rate_up',"info",'numeric','','Ko/s');
		$ADSL->AddCommande('Freebox bandwidth up','bandwidth_up',"info",'numeric','','Mb/s');
		$ADSL->AddCommande('Freebox bandwidth down','bandwidth_down',"info",'numeric','','Mb/s');
		$ADSL->AddCommande('Freebox media','media',"info",'string');
		$ADSL->AddCommande('Freebox state','state',"info",'string');
		// System
		$System=self::AddEqLogic('Système','System');
		$System->AddCommande('Update','update',"action",'other','Freebox_OS_System');
		$System->AddCommande('Reboot','reboot',"action",'other','Freebox_OS_System');
		$System->AddCommande('Freebox firmware version','firmware_version',"info",'string','Freebox_OS_System');
		$System->AddCommande('Mac','mac',"info",'string','Freebox_OS_System');
		$System->AddCommande('Vitesse ventilateur','fan_rpm',"info",'string','Freebox_OS_System','tr/min');
		$System->AddCommande('temp sw','temp_sw',"info",'string','Freebox_OS_System','°C');
		$System->AddCommande('Allumée depuis','uptime',"info",'string','Freebox_OS_System');
		$System->AddCommande('board name','board_name',"info",'string','Freebox_OS_System');
		$System->AddCommande('temp cpub','temp_cpub',"info",'string','Freebox_OS_System','°C');
		$System->AddCommande('temp cpum','temp_cpum',"info",'string','Freebox_OS_System','°C');
		$System->AddCommande('serial','serial',"info",'string','Freebox_OS_System');
		$cmdPF=$System->AddCommande('Redirection de ports','port_forwarding',"action",'message','Freebox_OS_System');
		$cmdPF->setIsVisible(0);
		$cmdPF->save();
		// Wifi
		$StatusWifi=$System->AddCommande('Status du wifi','wifiStatut',"info",'binary','Freebox_OS_Wifi');
		$StatusWifi->setIsVisible(0);
		$StatusWifi->save();
		$ActiveWifi=$System->AddCommande('Active/Désactive le wifi','wifiOnOff',"action",'other','Freebox_OS_Wifi');
		$ActiveWifi->setValue($StatusWifi->getId());
		$ActiveWifi->save();
		$WifiOn=$System->AddCommande('Wifi On','wifiOn',"action",'other','Freebox_OS_Wifi');
		$WifiOn->setIsVisible(0);
		$WifiOn->save();
		$WifiOff=$System->AddCommande('Wifi Off','wifiOff',"action",'other','Freebox_OS_Wifi');
		$WifiOff->setIsVisible(0);
		$WifiOff->save();
        //Phone
		$Phone=self::AddEqLogic('Téléphone','Phone');
		$Phone->AddCommande('Nombre Appels Manqués','nbAppelsManquee',"info",'numeric','Freebox_OS_Phone');
		$Phone->AddCommande('Nombre Appels Reçus','nbAppelRecus',"info",'numeric','Freebox_OS_Phone');
		$Phone->AddCommande('Nombre Appels Passés','nbAppelPasse',"info",'numeric','Freebox_OS_Phone');
		$Phone->AddCommande('Liste Appels Manqués','listAppelsManquee',"info",'string','Freebox_OS_Phone');
		$Phone->AddCommande('Liste Appels Reçus','listAppelsRecus',"info",'string','Freebox_OS_Phone');
		$Phone->AddCommande('Liste Appels Passés','listAppelsPasse',"info",'string','Freebox_OS_Phone');
		$Phone->AddCommande('Faire sonner les téléphones DECT','sonnerieDectOn',"action",'other','Freebox_OS_Phone');
		$Phone->AddCommande('Arrêter les sonneries des téléphones DECT','sonnerieDectOff',"action",'other','Freebox_OS_Phone');
		//Downloads
		$Downloads=self::AddEqLogic('Téléchargements','Downloads');
		$Downloads->AddCommande('Nombre de tâche(s)','nb_tasks',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) active','nb_tasks_active',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) en extraction','nb_tasks_extracting',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) en réparation','nb_tasks_repairing',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) en vérification','nb_tasks_checking',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) en attente','nb_tasks_queued',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) en erreur','nb_tasks_error',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) stoppée(s)','nb_tasks_stopped',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Nombre de tâche(s) terminée(s)','nb_tasks_done',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Téléchargement en cours','nb_tasks_downloading',"info",'string','Freebox_OS_Downloads');
		$Downloads->AddCommande('Vitesse réception','rx_rate',"info",'string','Freebox_OS_Downloads','Mo/s');
		$Downloads->AddCommande('Vitesse émission','tx_rate',"info",'string','Freebox_OS_Downloads','Mo/s');
		$Downloads->AddCommande('Start DL','start_dl',"action",'other','Freebox_OS_Downloads');
		$Downloads->AddCommande('Stop DL','stop_dl',"action",'other','Freebox_OS_Downloads');
		// AirPlay
		$AirPlay=self::AddEqLogic('AirPlay','AirPlay');
		$AirPlay->AddCommande('Player actuel AirMedia','ActualAirmedia',"info",'string','Freebox_OS_AirMedia_Recever');
		$AirPlay->AddCommande('AirMedia Start','airmediastart',"action",'message','Freebox_OS_AirMedia_Start');
		$AirPlay->AddCommande('AirMedia Stop','airmediastop',"action",'message','Freebox_OS_AirMedia_Start');
		if (config::byKey('FREEBOX_SERVER_TRACK_ID')!='') {
			$FreeboxAPI= new FreeboxAPI();
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
	public function preSave() {
		switch($this->getLogicalId()){
			case 'AirPlay':
				$FreeboxAPI = new FreeboxAPI();
				$parametre["enabled"]=$this->getIsEnable();
				$parametre["password"]=$this->getConfiguration('password');
				$FreeboxAPI->airmediaConfig($parametre);
				break;
		}
		if ($this->getConfiguration('waite') == '') {
			$this->setConfiguration('waite',300);
		}
	}
	public function postSave() {
		if ($this->getIsEnable()) {
			$this->CreateDemon();
		} else {
			$cron = cron::byClassAndFunction('Freebox_OS', 'RefreshInformation', array('Freebox_id' => $this->getId()));
			if (is_object($cron)) {
				$cron->stop();
				$cron->remove();
			}
		}
	}
	public static function RefreshToken() {
		$FreeboxAPI = new FreeboxAPI();
		$FreeboxAPI->close_session();
		if($FreeboxAPI->getFreeboxOpenSession() === false) self::deamon_stop();
	}
	public static function RefreshInformation($_option) {
		$FreeboxAPI = new FreeboxAPI();
		$Equipement = eqlogic::byId($_option['Freebox_id']);
		if (is_object($Equipement) && $Equipement->getIsEnable()) {
			while (true) {
				switch ($Equipement->getLogicalId()) {
					case 'AirPlay':
						break;
					case 'ADSL':
						$result = $FreeboxAPI->adslStats();
						if ($result!=false) {
							foreach ($Equipement->getCmd('info') as $Commande) {
								if (is_object($Commande)) {
									switch ($Commande->getLogicalId()) {
										case "rate_down":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['rate_down']);
											break;
										case "rate_up":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['rate_up']);
											break;
										case "bandwidth_up":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['bandwidth_up']);
											break;
										case "bandwidth_down":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['bandwidth_down']);
											break;
										case "media":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['media']);
											break;
										case "state":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['state']);
											break;
									}
								}
							}
						}
						break;
					case 'Downloads':
						$result = $FreeboxAPI->DownloadStats();
						if ($result!=false) {
							foreach ($Equipement->getCmd('info') as $Commande) {
								if (is_object($Commande)) {
									switch ($Commande->getLogicalId()) {
										case "nb_tasks":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks']);
											break;
										case "nb_tasks_downloading":
											$return= $result[''];
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_downloading']);
											break;
										case "nb_tasks_done":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_done']);
											break;
										case "rx_rate":
											$result= $result['rx_rate'];
											if (function_exists('bcdiv') )
												$result= bcdiv($result,1048576,2);
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
											break;
										case "tx_rate":
											$result= $result['tx_rate'];
											if (function_exists('bcdiv') )
												$result= bcdiv($result,1048576,2);
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
											break;
										case "nb_tasks_active":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_active']);
											break;
										case "nb_tasks_stopped":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_stopped']);
											break;
										case "nb_tasks_queued":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_queued']);
											break;
										case "nb_tasks_repairing":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_repairing']);
											break;
										case "nb_tasks_extracting":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_extracting']);
											break;
										case "nb_tasks_error":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_error']);
											break;
										case "nb_tasks_checking":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['nb_tasks_checking']);
											break;
									}
								}
							}
						}
						break;
					case 'System':
						foreach ($Equipement->getCmd('info') as $Commande) {
							if (is_object($Commande)) {
								if ($Commande->getLogicalId()=="wifiStatut") {
									$result = $FreeboxAPI->wifi();
								} else {
									$result = $FreeboxAPI->system();
								}
								switch ($Commande->getLogicalId()) {
									case "mac":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['mac']);
										break;
									case "fan_rpm":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['fan_rpm']);
										break;
									case "temp_sw":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['temp_sw']);
										break;
									case "uptime":
										$result= $result['uptime'];
										$result=str_replace(' heure ','h ',$result);
										$result=str_replace(' heures ','h ',$result);
										$result=str_replace(' minute ','min ',$result);
										$result=str_replace(' minutes ','min ',$result);
										$result=str_replace(' secondes','s',$result);
										$result=str_replace(' seconde','s',$result);
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
										break;
									case "board_name":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['board_name']);
										break;
									case "temp_cpub":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['temp_cpub']);
										break;
									case "temp_cpum":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['temp_cpum']);
										break;
									case "serial":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['serial']);
										break;
									case "firmware_version":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['firmware_version']);
										break;
									case "wifiStatut":
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
										break;
								}
							}
						}
						break;
					case 'Disque':
						foreach ($Equipement->getCmd('info') as $Commande) {
							if (is_object($Commande)) {
								$result = $FreeboxAPI->getdisque($Commande->getLogicalId());
								if ($result!=false) {
									$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result);
								}
							}
						}
						break;
					case 'Phone':
						$result = $FreeboxAPI->nb_appel_absence();
						if ($result!=false) {
							foreach ($Equipement->getCmd('info') as $Commande) {
								if (is_object($Commande)) {
									switch ($Commande->getLogicalId()) {
										case "nbAppelsManquee":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['missed']);
											break;
										case "nbAppelRecus":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['accepted']);
											break;
										case "nbAppelPasse":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['outgoing']);
											break;
										case "listAppelsManquee":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['list_missed']);
											break;
										case "listAppelsRecus":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['list_accepted']);
											break;
										case "listAppelsPasse":
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['list_outgoing']);
											break;
									}
								}
							}
						}
						break;
					case 'Reseau':
						foreach ($Equipement->getCmd('info') as $Commande) {
							if (is_object($Commande) ) {
								$result=$FreeboxAPI->ReseauPing($Commande->getLogicalId());
								if (!$result['success']) {
									if ($result['error_code'] == "internal_error") {
										$Commande->remove();
									}
								} else {
									if (isset($result['result']['l3connectivities'])) {
										foreach ($result['result']['l3connectivities'] as $Ip) {
											if ($Ip['active']){
												if($Ip['af']=='ipv4') {
													$Commande->setConfiguration('IPV4',$Ip['addr']);
												} else {
													$Commande->setConfiguration('IPV6',$Ip['addr']);
												}
											}
										}
									}
									$Commande->setConfiguration('host_type',$result['result']['host_type']);
									$Commande->save();
									if (isset($result['result']['active'])) {
										if ($result['result']['active'] == 'true') {
											$Commande->setOrder($Commande->getOrder() % 1000);
											$Commande->save();
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),true);
										} else {
											$Commande->setOrder($Commande->getOrder() % 1000 + 1000);
											$Commande->save();
											$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),false);
										}
									} else {
										$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),false);
									}
								}
							}
						}
						break;
					case'HomeAdapters':
						foreach ($Equipement->getCmd('info') as $Commande) {
							$result=$FreeboxAPI->getHomeAdapterStatus($Commande->getLogicalId());
							if ($result!=false) {
								$Equipement->checkAndUpdateCmd($Commande->getLogicalId(),$result['status']);
							}
						}
						break;
					default:
						$results=$FreeboxAPI->getTile($Equipement->getLogicalId());
						if ($results!=false) {
							foreach ($results as $result) {
								foreach($result['data'] as $data){
									if (!$Equipement->getIsEnable() ) break;
									$cmd = $Equipement->getCmd('info', $data['ep_id']);

									if (!is_object($cmd)) break;

									switch ($cmd->getSubType()) {
										case 'numeric':
											if ($cmd->getConfiguration('inverse') ) {
												$_value = ($cmd->getConfiguration('maxValue') - $cmd->getConfiguration('minValue')) - $data['value'];
											} else {
												$_value = $data['value'];
											}
											break;
										case 'string':
											$_value = $data['value'];
											break;
										case 'binary':
											if ($cmd->getConfiguration('inverse') ) {
												$_value = !$data['value'];
											} else {
												$_value = $data['value'];
											}
											break;
									}
									$Equipement->checkAndUpdateCmd($data['ep_id'],$_value);
								}
							}
						}
						break;
				}
				if ($Equipement->getConfiguration('waite') == ''){
					sleep(300);
				} else {
					sleep($Equipement->getConfiguration('waite'));
				}
			}
		}
	}
	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'Freebox_OS_update';
		$return['progress_file'] = '/tmp/compilation_Freebox_OS_in_progress';
		if (exec('dpkg -s netcat | grep -c "Status: install"') ==1) {
            $return['state'] = 'ok';
		} else {
			$return['state'] = 'nok';
		}
		return $return;
	}
	public static function dependancy_install() {
		if (file_exists('/tmp/compilation_Freebox_OS_in_progress')) {
			return;
		}
		log::remove('Freebox_OS_update');
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh';
		$cmd .= ' >> ' . log::getPathToLog('Freebox_OS_update') . ' 2>&1 &';
		exec($cmd);
	}
}
class Freebox_OSCmd extends cmd {
	public function dontRemoveCmd(){
		return true;
	}
	public function execute($_options = array()){
		log::add('Freebox_OS','debug','>───────── Connexion sur la freebox pour mise à jour de : '.$this->getName());
		$FreeboxAPI= new FreeboxAPI();
		switch ($this->getEqLogic()->getLogicalId()){
			case 'ADSL':
				break;
			case 'Downloads':
				$result = $FreeboxAPI->DownloadStats();
				if($result!=false){
					switch ($this->getLogicalId()){
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
				if($this->getLogicalId()=="wifiStatut"||$this->getLogicalId()=="wifiOnOff"||$this->getLogicalId()=='wifiOn'||$this->getLogicalId()=='wifiOff') {
					$result = $FreeboxAPI->wifi();
				} else {
					$result = $FreeboxAPI->system();
				}
				switch ($this->getLogicalId()) {
					case "reboot":
						$FreeboxAPI->reboot();
						break;
					case "update":
						$FreeboxAPI->UpdateSystem();
						break;
					case "wifiOnOff":
						if($result==true) {
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
					case 'port_forwarding':
						$FreeboxAPI->PortForwarding($_options['message']);
						break;
				}
				break;
			case 'Phone':
				$result = $FreeboxAPI->nb_appel_absence();
				if($result!=false){
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
			case'AirPlay':
				$receivers=$this->getEqLogic()->getCmd(null,"ActualAirmedia");
				if(!is_object($receivers) ||$receivers->execCmd() == "" || $_options['titre'] ==null){
					log::add('│ Freebox_OS','debug','[AirPlay] Impossible d\'envoyer la demande les paramètres sont incomplet équipement'.$receivers->execCmd().' type:'.$_options['titre']);
					break;
				}
				$Parameter["media_type"] = $_options['titre'];
				$Parameter["media"] = $_options['message'];
				$Parameter["password"]=$this->getConfiguration('password');
				switch($this->getLogicalId()){
					case "airmediastart":
						log::add('│ Freebox_OS','debug','[AirPlay] AirMedia Start : '.$Parameter["media"]);
						$Parameter["action"] = "start";
						$return = $FreeboxAPI->AirMediaAction($receivers->execCmd(),$Parameter);
						break;
					case "airmediastop":
						$Parameter["action"] = "stop";
						$return = $FreeboxAPI->AirMediaAction($receivers->execCmd(),$Parameter);
						break;
				}
				break;
			default:
				$logicalId = $this->getLogicalId();
				switch ($this->getSubType()) {
					case 'slider':
						if($this->getConfiguration('inverse')) {
							$parametre['value'] = ($this->getConfiguration('maxValue') - $this->getConfiguration('minValue')) - $_options['slider'];
						} else {
							$parametre['value'] = (int)$_options['slider'];
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

						if($this->getConfiguration('logicalId')>=0 &&( $this->getLogicalId() =='PB_On' || $this->getLogicalId() =='PB_Off')) {
							log::add('│ Freebox_OS', 'debug','Parametrage spécifique BP ON/OFF ' .$logicalId);
							$logicalId = $this->getConfiguration('logicalId');
							if($this->getLogicalId() =='PB_On') {
								$parametre['value'] = true;
							} else {
								$parametre['value'] = false;
							}

							break;
						} else {
							$logicalId = $this->getLogicalId();
							$parametre['value'] = true;
							$Listener=cmd::byId(str_replace('#','',$this->getValue()));

							if(is_object($Listener)) {
								$parametre['value'] = $Listener->execCmd();
							}
							if($this->getConfiguration('inverse')) {
								$parametre['value'] = !$parametre['value'];
							}

						}
						break;
                }
				$FreeboxAPI->setTile($this->getEqLogic()->getLogicalId(),$logicalId,$parametre);
                break;
		}
	}
}
