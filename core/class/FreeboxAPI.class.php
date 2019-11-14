<?php
class FreeboxAPI{	
	public function track_id() 	{
		try {
			$serveur=trim(config::byKey('FREEBOX_SERVER_IP','Freebox_OS'));
			$app_id =trim(config::byKey('FREEBOX_SERVER_APP_ID','Freebox_OS'));
			$app_name=trim(config::byKey('FREEBOX_SERVER_APP_NAME','Freebox_OS'));
			$app_version=trim(config::byKey('FREEBOX_SERVER_APP_VERSION','Freebox_OS'));
			$device_name=trim(config::byKey('FREEBOX_SERVER_DEVICE_NAME','Freebox_OS'));
			$http = new com_http($serveur . '/api/v3/login/authorize/');
			$http->setPost(
				json_encode(
					array(
						'app_id' => $app_id,
						'app_name' => $app_name,
						'app_version' => $app_version,
						'device_name' => $device_name
					)
				)
			);
			$result = $http->exec(30, 2);
			if (is_json($result)) 
		    		return json_decode($result, true);
			return $result;
		} catch (Exception $e) {
		    log::add('Freebox_OS','error', $e->getCode());
		}
	}
	public function ask_track_authorization(){
		try {
			$serveur		=trim(config::byKey('FREEBOX_SERVER_IP','Freebox_OS'));
			$track_id 		=config::byKey('FREEBOX_SERVER_TRACK_ID','Freebox_OS');
			$http = new com_http($serveur . '/api/v3/login/authorize/' . $track_id);
			$result = $http->exec(30, 2);
			if (is_json($result)) {
			    return json_decode($result, true);
			}
			return $result;
		} catch (Exception $e) {
		    log::add('Freebox_OS','error', $e->getCode());
		}
	}
	public function open_session(){
		try {
			log::add('Freebox_OS','debug', 'opening session');
			$serveur=trim(config::byKey('FREEBOX_SERVER_IP','Freebox_OS'));
			$app_token=config::byKey('FREEBOX_SERVER_APP_TOKEN','Freebox_OS');
			$app_id =trim(config::byKey('FREEBOX_SERVER_APP_ID','Freebox_OS'));

			$http = new com_http($serveur . '/api/v3/login/');
			$json=$http->exec(30, 2);
			$json_retour = json_decode($json, true);

			$challenge = $json_retour['result']['challenge'];
			$password = hash_hmac('sha1', $challenge, $app_token);

			$http = new com_http($serveur . '/api/v3/login/session/');
			$http->setPost( json_encode( array(
					'app_id' => $app_id,
					'password' => $password
					)
				)
			);
			$json=$http->exec(30, 2);
			$json_connect=json_decode($json, true);
			if ($json_connect['success']){
				cache::set('Freebox_OS::SessionToken', $json_connect['result']['session_token'], 0);
			}
			else 
				return false;
			return true;
		} catch (Exception $e) {
		    log::add('Freebox_OS','error', $e->getCode());
		}
	}
	public function fetch($api_url,$params=array(), $method='GET') {
		try {
			$cache = cache::byKey('Freebox_OS::SessionToken');
			$session_token = $cache->getValue('');
			if($session_token == ''){
				if($this->open_session()===false)
					break;
			}
			$serveur=trim(config::byKey('FREEBOX_SERVER_IP','Freebox_OS'));
			log::add('Freebox_OS','debug','Connexion ' . $method .' sur la l\'adresse '. $serveur.$api_url .'('.json_encode($params).')');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $serveur.$api_url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			if ($method=="POST") {
			    curl_setopt($ch, CURLOPT_POST, true);
			} elseif ($method=="DELETE") {
			    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			} elseif ($method=="PUT") {
			    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			}
			if ($params)
			    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Fbx-App-Auth: $session_token"));
			$content = curl_exec($ch);
			curl_close($ch);
			log::add('Freebox_OS','debug', $content);
			$result=json_decode($content, true);
			if($result == null){
            			log::add('Freebox_OS','error',json_last_error_msg());
				return false;
			}
			if(!$result['success']){
				$this->close_session();
				$this->fetch($api_url,$params,$method);
			}
			return $result;	
		} catch (Exception $e) {
		    log::add('Freebox_OS','error', $e->getCode());
		}
    	}
	public function close_session(){
		try {
			log::add('Freebox_OS','debug', 'closing session');
			$serveur=trim(config::byKey('FREEBOX_SERVER_IP','Freebox_OS'));
			$http = new com_http($serveur . '/api/v3/login/logout/');
			$http->setPost(array());
			$json_close=$http->exec(2,2);
			$cache = cache::byKey('Freebox_OS::SessionToken');
			$cache->remove();
			return $json_close;
		} catch (Exception $e) {
		    log::add('Freebox_OS','error', $e->getCode());
		}
	}
	public function WakeOnLAN($Mac){
		$return=self::fetch('/api/v3/lan/wol/pub/',array("mac"=> $Mac,"password"=> ""),"POST");	
		return $return['success'];
	}
   	public function Downloads($Etat){
		$List_DL=self::fetch('/api/v3/downloads/');
		$nbDL=count($List_DL['result']);
		for($i = 0; $i < $nbDL; ++$i){
			if ($Etat==0)
				$Downloads=self::fetch('/api/v3/downloads/'.$List_DL['result'][$i]['id'],array("status"=>"stopped"),"PUT");
			if ($Etat==1)
				$Downloads=self::fetch('/api/v3/downloads/'.$List_DL['result'][$i]['id'],array("status"=>"downloading"),"PUT");
		}        
		if($Downloads['success'])
			return $Downloads['success'];
		else
			return false;                                                                                                                                                                                     
	}
	public function DownloadStats(){
		$DownloadStats = self::fetch('/api/v3/downloads/stats/');
		if($DownloadStats['success'])
			return $DownloadStats['result'];
		else
			return false;
	}
	public function PortForwarding($Port){
		$PortForwarding = self::fetch('/api/v3/fw/redir/');
		$nbPF=count($PortForwarding['result']);
		for($i = 0; $i < $nbPF; ++$i)
		{
			if ($PortForwarding['result'][$i]['wan_port_start'] == $Port){
				if ($PortForwarding['result'][$i]['enabled'])
					$PortForwarding=self::fetch('/api/v3/fw/redir/'.$PortForwarding['result'][$i]['id'],array("enabled"=>false),"PUT");
				else
					$PortForwarding=self::fetch('/api/v3/fw/redir/'.$PortForwarding['result'][$i]['id'],array("enabled"=>true),"PUT");
			}
		}
		if($PortForwarding['success'])	
			return $PortForwarding['result'];
		else
			return false;
	}
	public function disques(){
		$reponse = self::fetch('/api/v3/storage/disk/');
		if($reponse['success']){
			$value=0;
			foreach($reponse['result'] as $Disques){
				$total_bytes=$Disques['partitions'][0]['total_bytes'];
				$used_bytes=$Disques['partitions'][0]['used_bytes'];
				$value=round($used_bytes/$total_bytes*100,2);
				log::add('Freebox_OS','debug','Occupation ['.$Disques['type'].'] - '.$Disques['id'].': '. $used_bytes.'/'.$total_bytes.' => '.$value.'%');
				$Disque=Freebox_OS::AddEqLogic('Disque Dur','Disque');
				$commande=$Disque->AddCommande('Occupation ['.$Disques['type'].'] - '.$Disques['id'],$Disques['id'],"info",'numeric','Freebox_OS_Disque','%');
				$commande->event($value);
			}
		}
	}
	public function getdisque($logicalId=''){
		$reponse = self::fetch('/api/v3/storage/disk/'.$logicalId);
		if($reponse['success']){
			$value=0;
			foreach($reponse['result'] as $Disques){
				$total_bytes=$Disques['partitions'][0]['total_bytes'];
				$used_bytes=$Disques['partitions'][0]['used_bytes'];
				return round($used_bytes/$total_bytes*100,2);
			}
		}
	}
	public function wifi(){
		$data_json = self::fetch('/api/v3/wifi/config/');
		if($data_json['success']){
			$value=0;
			if($data_json['result']['enabled'])
				$value=1;
			log::add('Freebox_OS','debug','L\'état du wifi est '.$value);
			return $value;
		}
		else
			return false;
	}
	public function wifiPUT($parametre) {
		log::add('Freebox_OS','debug','Mise dans l\'état '.$parametre.' du wifi');
		if ($parametre==1)
			$return=self::fetch('/api/v3/wifi/config/',array("enabled" => true),"PUT");	
		else
			$return=self::fetch('/api/v2/wifi/config/',array("enabled" => false),"PUT");	
		if($return['success'])
		{
			return $return['result']['enabled'];
		}
	}
	public function reboot() {
		$content=self::fetch('/api/v3/system/reboot/',null,"POST");	
		if($content['success'])
			return $content;
		else
			return false;
	}
	public function ringtone_on() {
		$content=self::fetch('/api/v3/phone/dect_page_start/',"","POST");	
		if($content['success'])
			return $content;
		else
			return false;
	}
	public function ringtone_off() {
		$content=self::fetch('/api/v3/phone/dect_page_stop/',"","POST");	
		if($content['success'])
			return $content;
		else
			return false;
	}
	public function system() {		
		$systemArray = self::fetch('/api/v3/system/');
		if($systemArray['success']){	
			return $systemArray['result'];
		}
		else
			return false;
	}	
	public function UpdateSystem() {	
		try {
			$System=Freebox_OS::AddEqLogic('Système','System');
			$Commande=$System->AddCommande('Update','update',"action",'other','Freebox_OS_System');
			log::add('Freebox_OS','debug','Vérification d\'une mise a jours du serveur');
			$firmwareOnline=file_get_contents("http://dev.freebox.fr/blog/?cat=5");
			preg_match_all('|<h1><a href=".*">Mise à jour du Freebox Server (.*)</a></h1>|U', $firmwareOnline , $parseFreeDev, PREG_PATTERN_ORDER);			
			if(intval($Commande->execCmd()) < intval($parseFreeDev[1][0]))
				self::reboot();
		} catch (Exception $e) {
		    log::add('Freebox_OS','error', $e->getCode());
		}
	}
	public function adslStats(){	
		$adslRateJson = self::fetch('/api/v3/connection/');
		if($adslRateJson['success']){		
			$vdslRateJson = self::fetch('/api/v3/connection/xdsl/');				
			if($vdslRateJson['result']['status']['modulation'] == "vdsl")
				$adslRateJson['result']['media'] = $vdslRateJson['result']['status']['modulation'];

			$retourFbx = array(	'rate_down' 	=> round($adslRateJson['result']['rate_down']/1024,2), 
								'rate_up' 		=> round($adslRateJson['result']['rate_up']/1024,2), 
								'bandwidth_up' 	=> round($adslRateJson['result']['bandwidth_up']/1000000,2), 
								'bandwidth_down' => round($adslRateJson['result']['bandwidth_down']/1000000,2), 
								'media'			=> $adslRateJson['result']['media'],
								'state' 		=> $adslRateJson['result']['state'] );
			return $retourFbx;
		}
		else
			return false;
	}
	public function getTiles(){
		self::open_session();
		$listEquipement = self::fetch('/api/v6/home/tileset/all');
		self::close_session();
		if($listEquipement['success'])
			return $listEquipement['result'];
		else
			return false;
	}
	public function getTile($id=''){
		$Status = self::fetch('/api/v6/home/tileset/'.$id);
		if($Status['success'])
			return $Status['result'];
		else
			return false;
	}
	public function setTile($nodeId,$endpointId,$parametre) {
		$return=self::fetch('/api/v6/home/endpoints/'.$nodeId.'/'.$endpointId.'/',$parametre,"PUT");   	         	
		if($return['success'])
			return $return['result'];
		else
			return false;
	}
	public function getHomeAdapters(){
		self::open_session();
		$listEquipement = self::fetch('/api/v6/home/adapters');
		self::close_session();
		if($listEquipement['success'])
			return $listEquipement['result'];
		else
			return false;
	}
	public function getHomeAdapterStatus($id=''){
		$Status = self::fetch('/api/v6/home/adapters/'.$id);
		if($Status['success'])
			return $Status['result'];
		else
			return false;
	}
	public function getReseau(){
		self::open_session();
		$listEquipement = self::fetch('/api/v3/lan/browser/pub/');
		self::close_session();
		if($listEquipement['success'])
			return $listEquipement['result'];
		else
			return false;
	}
	public function ReseauPing($id=''){
		$Ping = self::fetch('/api/v3/lan/browser/pub/'.$id);

		if($Ping['success'])
			return $Ping['result'];
		else
			return false;
	}
	public function nb_appel_absence() {
		$listNumber_missed='';
		$listNumber_accepted='';
		$listNumber_outgoing='';
		$pre_check_con = self::fetch('/api/v3/call/log/');
		if($pre_check_con['success']){			
			$timestampToday = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
			if(isset($pre_check_con['result'])){
				$nb_call = count($pre_check_con['result']);

				$cptAppel_outgoing = 0;
				$cptAppel_missed = 0;
				$cptAppel_accepted = 0;
				for ($k=0; $k<$nb_call; $k++) 
				{
					$jour = $pre_check_con['result'][$k]['datetime'];

					$time = date('H:i', $pre_check_con['result'][$k]['datetime']);
					if ($timestampToday <= $jour) 
					{
						if($pre_check_con['result'][$k]['name']==$pre_check_con['result'][$k]['number'])
						{
							$name="N.C.";
						}
						else						
						{
							$name=$pre_check_con['result'][$k]['name'];
						}

						if ($pre_check_con['result'][$k]['type'] == 'missed')
						{
							$cptAppel_missed++;
							$listNumber_missed.= $pre_check_con['result'][$k]['number'].": ".$name." à ".$time." - de ".$pre_check_con['result'][$k]['duration']."s<br>";
						}
						if ($pre_check_con['result'][$k]['type'] == 'accepted')
						{
							$cptAppel_accepted++;
							$listNumber_accepted.= $pre_check_con['result'][$k]['number'].": ".$name." à ".$time." - de ".$pre_check_con['result'][$k]['duration']."s<br>";
						}
						if ($pre_check_con['result'][$k]['type'] == 'outgoing')
						{
							$cptAppel_outgoing++;
							$listNumber_outgoing.= $pre_check_con['result'][$k]['number'].": ".$name." à ".$time." - de ".$pre_check_con['result'][$k]['duration']."s<br>";
						}
					}
				}
				$retourFbx = array('missed' => $cptAppel_missed, 'list_missed' => $listNumber_missed, 'accepted' => $cptAppel_accepted, 'list_accepted' => $listNumber_accepted, 'outgoing' => $cptAppel_outgoing,'list_outgoing' => $listNumber_outgoing );
			}
			else	
				$retourFbx = array('missed' => 0, 'list_missed' => "", 'accepted' => 0, 'list_accepted' => "", 'outgoing' => 0,'list_outgoing' => "" );

			return $retourFbx;
		}
		else
			return false;
	}
	public function airmediaConfig($parametre) {
		$return=self::fetch('/api/v3/airmedia/config/',$parametre,"PUT");   	         	
		if($return['success'])
			return $return['result'];
		else
			return false;
	}
	public static function airmediaReceivers() {
		$return=self::fetch('/api/v3/airmedia/receivers/');   

		if($return['success'])
			return $return['result'];
		else
			return false;
	}
	public function AirMediaAction($receiver,$Parameter) {
		$return=self::fetch('/api/v3/airmedia/receivers/'.$receiver.'/',$Parameter,'POST');
		if($return['success'])
			return true;
		else
			return false;
	}
}
