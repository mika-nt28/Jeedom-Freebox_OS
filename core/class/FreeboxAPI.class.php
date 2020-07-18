<?php
class FreeboxAPI
{
	private $ErrorLoop = 0;
	private $serveur;
	private $app_id;
	private $app_name;
	private $app_version;
	private $device_name;
	private $track_id;
	private $app_token;
	public function __construct()
	{
		$this->serveur = trim(config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS'));
		$this->app_id = trim(config::byKey('FREEBOX_SERVER_APP_ID', 'Freebox_OS'));
		$this->app_name = trim(config::byKey('FREEBOX_SERVER_APP_NAME', 'Freebox_OS'));
		$this->app_version = trim(config::byKey('FREEBOX_SERVER_APP_VERSION', 'Freebox_OS'));
		$this->device_name = trim(config::byKey('FREEBOX_SERVER_DEVICE_NAME', 'Freebox_OS'));
		$this->track_id	= config::byKey('FREEBOX_SERVER_TRACK_ID', 'Freebox_OS');
		$this->app_token = config::byKey('FREEBOX_SERVER_APP_TOKEN', 'Freebox_OS');
	}
	public function track_id()
	{
		try {
			$http = new com_http($this->serveur . '/api/v8/login/authorize/');
			$http->setPost(
				json_encode(
					array(
						'app_id' => $this->app_id,
						'app_name' => $this->app_name,
						'app_version' => $this->app_version,
						'device_name' => $this->device_name
					)
				)
			);
			$result = $http->exec(30, 2);
			if (is_json($result)) {
				return json_decode($result, true);
			}
			return $result;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', '[FreeboxTrackId]' . $e->getCode());
		}
	}
	public function ask_track_authorization()
	{
		try {
			$http = new com_http($this->serveur . '/api/v8/login/authorize/' . $this->track_id);
			$result = $http->exec(30, 2);
			if (is_json($result)) {
				return json_decode($result, true);
			}
			return $result;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', '[FreeboxAutorisation]' . $e->getCode());
		}
	}

	public function getFreeboxPassword()
	{
		try {
			$http = new com_http($this->serveur . '/api/v8/login/');
			$json = $http->exec(30, 2);
			log::add('Freebox_OS', 'debug', '[FreeboxPassword]' . $json);
			$json_connect = json_decode($json, true);
			if ($json_connect['success'])
				cache::set('Freebox_OS::Challenge', $json_connect['result']['challenge'], 0);
			else
				return false;
			return true;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', '[FreeboxPassword]' . $e->getCode());
		}
	}
	public function getFreeboxOpenSession()
	{
		try {
			$challenge = cache::byKey('Freebox_OS::Challenge');
			if (!is_object($challenge) || $challenge->getValue('') == '') {
				if ($this->getFreeboxPassword() === false)
					return false;
				$challenge = cache::byKey('Freebox_OS::Challenge');
			}

			$http = new com_http($this->serveur . '/api/v8/login/session/');
			$http->setPost(json_encode(array(
				'app_id' => $this->app_id,
				'password' => hash_hmac('sha1', $challenge->getValue(''), $this->app_token)
			)));
			$json = $http->exec(30, 2);
			log::add('Freebox_OS', 'debug', '[FreeboxOpenSession]' . $json);
			$result = json_decode($json, true);

			if (!$result['success']) {
				$this->ErrorLoop++;
				$this->close_session();
				if ($this->ErrorLoop < 5) {
					if ($this->getFreeboxOpenSession() === false)
						return false;
				}
			} else {
				cache::set('Freebox_OS::SessionToken', $result['result']['session_token'], 0);
				return true;
			}
			return false;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', '[FreeboxOpenSession]' . $e->getCode());
		}
	}
	public function fetch($api_url, $params = array(), $method = 'GET', $update_log = false)
	{
		try {
			$session_token = cache::byKey('Freebox_OS::SessionToken');
			while ($session_token->getValue('') == '') {
				sleep(1);
				$session_token = cache::byKey('Freebox_OS::SessionToken');
			}
			if ($update_log == false) {
				log::add('Freebox_OS', 'debug', '┌───────── Début de Mise à jour');
			};
			log::add('Freebox_OS', 'debug', '│ [FreeboxRequest] Connexion ' . $method . ' sur la l\'adresse ' . $this->serveur . $api_url . '(' . json_encode($params) . ')');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->serveur . $api_url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			if ($method == "POST") {
				curl_setopt($ch, CURLOPT_POST, true);
			} elseif ($method == "DELETE") {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			} elseif ($method == "PUT") {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			}
			if ($params)
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Fbx-App-Auth: " . $session_token->getValue('')));
			$content = curl_exec($ch);
			curl_close($ch);
			log::add('Freebox_OS', 'debug', '│ [FreeboxRequest] ' . $content);
			$result = json_decode($content, true);
			if ($result == null) return false;
			log::add('Freebox_OS', 'debug', '└─────────');
			return $result;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', '│ [FreeboxRequest]' . $e->getCode());
			log::add('Freebox_OS', 'debug', '└─────────');
		}
	}
	public function close_session()
	{
		try {
			$Challenge = cache::byKey('Freebox_OS::Challenge');
			if (is_object($Challenge)) {
				$Challenge->remove();
			}
			$session_token = cache::byKey('Freebox_OS::SessionToken');
			if (!is_object($session_token) || $session_token->getValue('') == '')
				return;
			$http = new com_http($this->serveur . '/api/v8/login/logout/');
			$http->setPost(array());
			$json = $http->exec(2, 2);
			log::add('Freebox_OS', 'debug', 'closing session :' . $json);
			$SessionToken = cache::byKey('Freebox_OS::SessionToken');
			if (is_object($SessionToken))
				$SessionToken->remove();
			return $json;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', '[FreeboxCloseSession]' . $e->getCode());
		}
	}
	public function WakeOnLAN($Mac)
	{
		$return = $this->fetch('/api/v8/lan/wol/pub/', array("mac" => $Mac, "password" => ""), "POST");
		if ($return === false)
			return false;
		return $return['success'];
	}
	public function Downloads($Etat)
	{
		$List_DL = $this->fetch('/api/v8/downloads/');
		if ($List_DL === false)
			return false;
		$nbDL = count($List_DL['result']);
		for ($i = 0; $i < $nbDL; ++$i) {
			if ($Etat == 0)
				$Downloads = $this->fetch('/api/v8/downloads/' . $List_DL['result'][$i]['id'], array("status" => "stopped"), "PUT");
			if ($Etat == 1)
				$Downloads = $this->fetch('/api/v8/downloads/' . $List_DL['result'][$i]['id'], array("status" => "downloading"), "PUT");
		}
		if ($Downloads === false)
			return false;
		if ($Downloads['success'])
			return $Downloads['success'];
		else
			return false;
	}
	public function DownloadStats()
	{
		$DownloadStats = $this->fetch('/api/v8/downloads/stats/');
		if ($DownloadStats === false)
			return false;
		if ($DownloadStats['success'])
			return $DownloadStats['result'];
		else
			return false;
	}
	public function PortForwarding($Port)
	{
		$PortForwarding = $this->fetch('/api/v8/fw/redir/');
		if ($PortForwarding === false)
			return false;
		$nbPF = count($PortForwarding['result']);
		for ($i = 0; $i < $nbPF; ++$i) {
			if ($PortForwarding['result'][$i]['wan_port_start'] == $Port) {
				if ($PortForwarding['result'][$i]['enabled'])
					$PortForwarding = $this->fetch('/api/v8/fw/redir/' . $PortForwarding['result'][$i]['id'], array("enabled" => false), "PUT");
				else
					$PortForwarding = $this->fetch('/api/v8/fw/redir/' . $PortForwarding['result'][$i]['id'], array("enabled" => true), "PUT");
			}
		}
		if ($PortForwarding === false)
			return false;
		if ($PortForwarding['success'])
			return $PortForwarding['result'];
		else
			return false;
	}
	public function disques()
	{
		$reponse = $this->fetch('/api/v8/storage/disk/');
		if ($reponse === false)
			return false;
		if ($reponse['success']) {
			$value = 0;
			foreach ($reponse['result'] as $Disques) {
				$total_bytes = $Disques['partitions'][0]['total_bytes'];
				$used_bytes = $Disques['partitions'][0]['used_bytes'];
				$value = round($used_bytes / $total_bytes * 100, 2);
				log::add('Freebox_OS', 'debug', '┌───────── Update Disque ');
				log::add('Freebox_OS', 'debug', '│ Occupation [' . $Disques['type'] . '] - ' . $Disques['id'] . ': ' . $used_bytes . '/' . $total_bytes . ' => ' . $value . '%');
				$Disque = Freebox_OS::AddEqLogic('Disque Dur', 'Disque', 'default', false, null, null);
				$command = $Disque->AddCommand('Occupation [' . $Disques['type'] . '] - ' . $Disques['id'], $Disques['id'], 'info', 'numeric', 'Freebox_OS::Freebox_OS_Disque', '%', null, 1, 'default', 'default', 0, null, 0, '0', 100,  null, '0', false);
				$command->event($value);
				log::add('Freebox_OS', 'debug', '└─────────');
			}
		}
	}
	public function getdisque($logicalId = '')
	{
		$reponse = $this->fetch('/api/v8/storage/disk/' . $logicalId);
		if ($reponse === false)
			return false;
		if ($reponse['success']) {
			$total_bytes = $reponse['result']['partitions'][0]['total_bytes'];
			$used_bytes = $reponse['result']['partitions'][0]['used_bytes'];
			return round($used_bytes / $total_bytes * 100, 2);
		}
		return false;
	}
	public function universal_get($update = 'wifi')
	{
		switch ($update) {
			case 'planning':
				$config = 'api/v8/wifi/planning';
				$config_log = 'Etat du Planning du Wifi';
				break;
			case 'wifi':
				$config = 'api/v8/wifi/config';
				$config_log = 'Etat du Wifi';
				break;
			case '4G':
				$config = 'api/v8/connection/lte/config';
				$config_log = 'Etat 4G';
				break;
		}

		$data_json = $this->fetch('/' . $config . '/');
		if ($data_json === false)
			return false;
		if ($data_json['success']) {
			$value = 0;
			switch ($update) {
				case 'planning':
					if ($data_json['result']['use_planning']) {
						$value = 1;
					}
					break;
				case 'wifi':
					if ($data_json['result']['enabled']) {
						$value = 1;
					}
					break;
				case '4G':
					if ($data_json['result']['enabled']) {
						$value = 1;
					}
					break;
			}
			log::add('Freebox_OS', 'debug', '>───────── ' . $config_log . ' : ' . $value);
			return $value;
		} else {
			return false;
		}
	}
	public function getHomeAdapters_player($update = 'HomeAdapters')
	{
		switch ($update) {
			case 'HomeAdapters':
				$config = 'api/v8/home/adapters';
				break;
			case 'Player':
				$config = 'api/v8/player';
				break;
		}
		$listEquipement = $this->fetch('/' . $config);
		if ($listEquipement === false)
			return false;
		if ($listEquipement['success'])
			return $listEquipement['result'];
		else
			return false;
	}
	public function universal_put($parametre, $update = 'wifi', $id = null)
	{
		switch ($update) {
			case 'wifi':
				$config = 'api/v8/wifi/config';
				$config_commande = 'enabled';
				$config_log = 'Mise à jour de : Etat du Wifi';
				break;
			case 'planning':
				$config = 'api/v8/wifi/planning';
				$config_log = 'Mise à jour : Planning du Wifi';
				$config_commande = 'use_planning';
				break;
			case 'Parental':
				$config = 'api/v8/network_control'; //. $id . '/rules';
				$config_log = 'Mise à jour du : Contrôle Parental';
				$config_commande = 'enabled';
				break;
			case '4G':
				$config = 'api/v8/connection/lte/config';
				$config_log = 'Mise à jour du : Activation 4G';
				$config_commande = 'enabled';
				break;
		}
		if ($parametre === 1) {
			$parametre = true;
		} elseif ($parametre === 0) {
			$parametre = false;
		} else {
			//	$parametre;
		}

		log::add('Freebox_OS', 'debug', '>───────── Mise à jour : ' . $config_log . ' avec la valeur : ' . $parametre);
		$return = $this->fetch('/' . $config . '/', array($config_commande => $parametre), "PUT");
		if ($return === false) {
			return false;
		}
		switch ($update) {
			case 'wifi':
				return $return['result']['enabled'];
				break;
			case 'planning':
				return $return['result']['use_planning'];
				break;
			case '4G':
				return $return['result']['enabled'];
				break;
			case 'Parental':
				return $return['result']['current_mode'];
				break;
		}
	}
	public function reboot()
	{
		log::add('Freebox_OS', 'debug', '>───────── Reboot Freebox');
		$content  = $this->fetch('/api/v8/system/reboot/', null, "POST");
		if ($content === false)
			return false;
		if ($content['success']) {
			return $content;
		} else {
			return false;
		}
	}
	public function ringtone_on()
	{
		log::add('Freebox_OS', 'debug', '>───────── Ringtone ON');
		$content = $this->fetch('/api/v8/phone/dect_page_start/', "", "POST");
		if ($content === false)
			return false;
		if ($content['success'])
			return $content;
		else
			return false;
	}
	public function ringtone_off()
	{
		log::add('Freebox_OS', 'debug', '>───────── Ringtone OFF');
		$content = $this->fetch('/api/v8/phone/dect_page_stop/', "", "POST");
		if ($content === false)
			return false;
		if ($content['success'])
			return $content;
		else
			return false;
	}

	public function adslStats()
	{
		$adslRateJson = $this->fetch('/api/v8/connection/');
		if ($adslRateJson === false)
			return false;
		if ($adslRateJson['success']) {
			$vdslRateJson = $this->fetch('/api/v38connection/xdsl/');
			if ($vdslRateJson === false)
				return false;
			if ($vdslRateJson['result']['status']['modulation'] == "vdsl")
				$adslRateJson['result']['media'] = $vdslRateJson['result']['status']['modulation'];

			$retourFbx = array(
				'rate_down' 	=> round($adslRateJson['result']['rate_down'] / 1024, 2),
				'rate_up' 		=> round($adslRateJson['result']['rate_up'] / 1024, 2),
				'bandwidth_up' 	=> round($adslRateJson['result']['bandwidth_up'] / 1000000, 2),
				'bandwidth_down' => round($adslRateJson['result']['bandwidth_down'] / 1000000, 2),
				'media'			=> $adslRateJson['result']['media'],
				'state' 		=> $adslRateJson['result']['state']
			);
			return $retourFbx;
		} else
			return false;
	}
	public function systemV8($update = 4)
	{

		$listEquipement = $this->fetch('/api/v8/system/');
		if ($listEquipement === false)
			return false;
		if ($listEquipement['success']) {
			switch ($update) {
				case 1:
					return $listEquipement['result']['sensors'];
				case 2:
					return $listEquipement['result']['fans'];
				case 3:
					return $listEquipement['result']['expansions'];
				case 4:
					return $listEquipement['result'];
			}
		} else {
			return false;
		}
	}

	/*	public function system() // FONCTION A SUPPRIMER APRES BASCULE V8 SYSTEM
	{
		$systemArray = $this->fetch('/api/v5/system/');
		if ($systemArray === false)
			return false;
		if ($systemArray['success']) {
			return $systemArray['result'];
		} else
			return false;
	}*/
	public function UpdateSystem()
	{
		try {
			$System = Freebox_OS::AddEqLogic('Système', 'System', 'default', false, null, null);
			$Command = $System->AddCommand('Update', 'update', 'action', 'other', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  null, '0', false, true);
			log::add('Freebox_OS', 'debug', '│ Vérification d\'une mise a jours du serveur');
			$firmwareOnline = file_get_contents("http://dev.freebox.fr/blog/?cat=5");
			preg_match_all('|<h1><a href=".*">Mise à jour du Freebox Server (.*)</a></h1>|U', $firmwareOnline, $parseFreeDev, PREG_PATTERN_ORDER);
			if (intval($Command->execCmd()) < intval($parseFreeDev[1][0]))
				$this->reboot();
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', '[FreeboxUpdateSystem]' . $e->getCode());
		}
	}
	public function getTiles($update = 'tiles')
	{
		switch ($update) {
			case 'tiles':
				$config = 'api/v8/home/tileset/all';
				break;
			case 'controlparental':
				$config = 'api/v8/profile';
				break;
		}
		$listEquipement = $this->fetch('/' . $config);
		if ($listEquipement === false)
			return false;
		if ($listEquipement['success'])
			return $listEquipement['result'];
		else
			return false;
	}
	public function getTile($id = '', $update = 'tiles')
	{
		$config_sup = null;
		switch ($update) {
			case 'tiles':
				$config = 'api/v8/home/tileset/';
				break;
			case 'Parental':
				$config = 'api/v8/network_control/';
				break;
			case 'Player':
				$config = 'api/v8/player/';
				$config_sup = '/api/v6/status';
				break;
		}

		$Status = $this->fetch('/' . $config . $id . $config_sup);
		log::add('Freebox_OS', 'debug', '┌───────── Traitement de la Mise à jour de l\'id : ' . $id);
		if ($Status === false)
			return false;
		if ($Status['success']) {
			return $Status['result'];
		} else {
			return false;
		}
	}
	public function setTile($nodeId, $endpointId, $parametre, $update = 'tiles')
	{
		switch ($update) {
			case 'tiles':
				$config = 'api/v8/home/endpoints/';
				break;
			case 'Parental':
				$config = 'api/v8/network_control/';
				break;
		}
		if ($endpointId != null) {
			$endpointId = $endpointId . '/';
		} elseif ($endpointId != 'refresh') {
			$endpointId = null;
		}
		log::add('Freebox_OS', 'debug', '└───────── Info nodeid : ' . $nodeId . ' -- endpointId : ' . $endpointId . ' -- Paramètre : ' . $parametre);
		$return = $this->fetch('/' . $config . $nodeId . '/' . $endpointId, $parametre, "PUT");
		if ($return === false)
			return false;
		if ($return['success'])
			return $return['result'];
		else
			return false;
	}

	public function getHomeAdapterStatus($id = '')
	{
		$Status = $this->fetch('/api/v8/home/adapters/' . $id);
		if ($Status === false)
			return false;
		if ($Status['success'])
			return $Status['result'];
		else
			return false;
	}
	public function getReseau()
	{
		$listEquipement = $this->fetch('/api/v8/lan/browser/pub/');
		if ($listEquipement === false)
			return false;
		if ($listEquipement['success'])
			return $listEquipement['result'];
		else
			return false;
	}
	public function ReseauPing($id = '')
	{
		$Ping = $this->fetch('/api/v8/lan/browser/pub/' . $id);
		if ($Ping === false)
			return false;
		if ($Ping['success'])
			return $Ping;
		else
			return false;
	}
	public function nb_appel_absence()
	{
		$listNumber_missed = null;
		$listNumber_accepted = null;
		$listNumber_outgoing = null;
		$pre_check_con = $this->fetch('/api/v8/call/log/');
		if ($pre_check_con === false)
			return false;
		if ($pre_check_con['success']) {
			$timestampToday = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
			if (isset($pre_check_con['result'])) {
				$nb_call = count($pre_check_con['result']);

				$cptAppel_outgoing = 0;
				$cptAppel_missed = 0;
				$cptAppel_accepted = 0;
				for ($k = 0; $k < $nb_call; $k++) {
					$jour = $pre_check_con['result'][$k]['datetime'];

					$time = date('H:i', $pre_check_con['result'][$k]['datetime']);
					if ($timestampToday <= $jour) {
						if ($pre_check_con['result'][$k]['name'] == $pre_check_con['result'][$k]['number']) {
							$name = "N.C.";
						} else {
							$name = $pre_check_con['result'][$k]['name'];
						}

						if ($pre_check_con['result'][$k]['type'] == 'missed') {
							$cptAppel_missed++;
							$listNumber_missed .= $pre_check_con['result'][$k]['number'] . ": " . $name . " à " . $time . " - de " . $pre_check_con['result'][$k]['duration'] . "s" . "\r\n";
						}
						if ($pre_check_con['result'][$k]['type'] == 'accepted') {
							$cptAppel_accepted++;
							$listNumber_accepted .= $pre_check_con['result'][$k]['number'] . ": " . $name . " à " . $time . " - de " . $pre_check_con['result'][$k]['duration'] . "s" . "\r\n";
						}
						if ($pre_check_con['result'][$k]['type'] == 'outgoing') {
							$cptAppel_outgoing++;
							$listNumber_outgoing .= $pre_check_con['result'][$k]['number'] . ": " . $name . " à " . $time . " - de " . $pre_check_con['result'][$k]['duration'] . "s" . "\r\n";
						}
					}
				}
				$retourFbx = array('missed' => $cptAppel_missed, 'list_missed' => $listNumber_missed, 'accepted' => $cptAppel_accepted, 'list_accepted' => $listNumber_accepted, 'outgoing' => $cptAppel_outgoing, 'list_outgoing' => $listNumber_outgoing);
			} else
				$retourFbx = array('missed' => 0, 'list_missed' => "", 'accepted' => 0, 'list_accepted' => "", 'outgoing' => 0, 'list_outgoing' => "");

			return $retourFbx;
		} else
			return false;
	}
	public function airmediaConfig($parametre)
	{
		$return = $this->fetch('/api/v8/airmedia/config/', $parametre, "PUT");
		if ($return === false)
			return false;
		if ($return['success'])
			return $return['result'];
		else
			return false;
	}
	public function airmediaReceivers()
	{
		$return = $this->fetch('/api/v8/airmedia/receivers/');
		if ($return === false)
			return false;

		if ($return['success'])
			return $return['result'];
		else
			return false;
	}
	public function AirMediaAction($receiver, $Parameter)
	{
		$return = $this->fetch('/api/v8/airmedia/receivers/' . $receiver . '/', $Parameter, 'POST');
		if ($return === false)
			return false;
		if ($return['success'])
			return true;
		else
			return false;
	}
}
