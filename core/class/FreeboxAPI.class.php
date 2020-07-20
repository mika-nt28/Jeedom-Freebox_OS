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

	public function Downloads($Etat)
	{
		$result = $this->fetch('/api/v8/downloads/');
		if ($result === false)
			return false;
		$nbDL = count($result['result']);
		for ($i = 0; $i < $nbDL; ++$i) {
			if ($Etat == 0)
				$Downloads = $this->fetch('/api/v8/downloads/' . $result['result'][$i]['id'], array("status" => "stopped"), "PUT");
			if ($Etat == 1)
				$Downloads = $this->fetch('/api/v8/downloads/' . $result['result'][$i]['id'], array("status" => "downloading"), "PUT");
		}
		if ($Downloads === false)
			return false;
		if ($Downloads['success'])
			return $Downloads['success'];
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
				$logicalinfo = Freebox_OS::getlogicalinfo();

				$Disque = Freebox_OS::AddEqLogic($logicalinfo['diskID'], $logicalinfo['diskName'], 'default', false, null, null);
				$command = $Disque->AddCommand('Occupation [' . $Disques['type'] . '] - ' . $Disques['id'], $Disques['id'], 'info', 'numeric', 'Freebox_OS::Freebox_OS_Disque', '%', null, 1, 'default', 'default', 0, 'fas fa-save', 0, '0', 100,  null, '0', false);
				$command->event($value);
				log::add('Freebox_OS', 'debug', '└─────────');
			}
		}
	}
	/*public function getdisque($logicalId = '') // Fonction plus appelé à supprimer => Intégrer dans "universal_get"
	{
		$result = $this->fetch('/api/v8/storage/disk/' . $logicalId);
		if ($result === false)
			return false;
		if ($result['success']) {
			$total_bytes = $result['result']['partitions'][0]['total_bytes'];
			$used_bytes = $result['result']['partitions'][0]['used_bytes'];
			return round($used_bytes / $total_bytes * 100, 2);
		}
		return false;
	}*/
	/*public function DownloadStats() // Fonction plus appelé à supprimer => Intégrer dans "universal_get"
	{
		$result = $this->fetch('/api/v8/downloads/stats/');
		if ($result === false) {
			return false;
		}
		if ($result['success'])
			return $result['result'];
		else
			return false;
	}*/
	public function universal_get($update = 'wifi', $id = null, $boucle = 4)
	{
		$config_log = null;
		switch ($update) {
			case '4G':
				$config = 'api/v8/connection/lte/config';
				$config_log = 'Etat 4G';
				break;
			case 'disques':
				$config = '/api/v8/storage/disk/' . $id;
				break;
			case 'DownloadStats':
				$config = 'api/v8/downloads/stats/';
				break;
			case 'HomeAdapters':
				$config = 'api/v8/home/adapters';
				break;
			case 'HomeAdapters_status':
				$config = 'api/v8/home/adapters/' . $id;
				break;
			case 'parental':
				$config = 'api/v8/network_control';
				$config_log = 'Etat Contrôle Parental';
				break;
			case 'parentalprofile':
				$config = 'api/v8/profile';
				break;
			case 'planning':
				$config = 'api/v8/wifi/planning';
				$config_log = 'Etat du Planning du Wifi';
				break;
			case 'player':
				$config = 'api/v8/player';
				break;
			case 'player_ID':
				$config = 'api/v8/player/' . $id . '/api/v6/status';
				$config_log = 'Traitement de la Mise à jour de l\'id ';
				break;
			case 'network':
				$config = 'api/v8/lan/browser/pub';
				break;
			case 'network_ping':
				$config = 'api/v8/lan/browser/pub/' . $id;
				break;
			case 'system':
				$config = 'api/v8/system';
				break;
			case 'tiles':
				$config = 'api/v8/home/tileset/all';
				break;
			case 'tiles_ID':
				$config = 'api/v8/home/tileset/' . $id;
				$config_log = 'Traitement de la Mise à jour de l\'id ';
				break;
			case 'wifi':
				$config = 'api/v8/wifi/config';
				$config_log = 'Etat du Wifi';
				break;
		}

		$result = $this->fetch('/' . $config);
		if ($result === false) {
			return false;
		}
		if ($result['success']) {
			$value = 0;
			switch ($update) {
				case '4G':
					if ($result['result']['enabled']) {
						$value = 1;
					}
					break;
				case 'disques':
					$total_bytes = $result['result']['partitions'][0]['total_bytes'];
					$used_bytes = $result['result']['partitions'][0]['used_bytes'];
					break;
				case 'planning':
					if ($result['result']['use_planning']) {
						$value = 1;
					}
					break;
				case 'system':
					switch ($boucle) {
						case 1:
							return $result['result']['sensors'];
						case 2:
							return $result['result']['fans'];
						case 3:
							return $result['result']['expansions'];
						case 4:
							return $result['result'];
					}
					break;
				case 'wifi':
					if ($result['result']['enabled']) {
						$value = 1;
					}
					break;
				default:
					return $result['result'];
					break;
			}
			if ($config_log != null && $id == null) {
				log::add('Freebox_OS', 'debug', '>───────── ' . $config_log . ' : ' . $value);
			} else if ($config_log != null && $id != null) {
				log::add('Freebox_OS', 'debug', '>───────── ' . $config_log . ' : ' . $id);
			}
			if ($update == 'disques') {
				return round($used_bytes / $total_bytes * 100, 2);
			} else {
				return $value;
			}
		} else {
			return false;
		}
	}
	/*public function getTile($id = '', $update = 'tiles') // Fonction plus appelé à supprimer => Intégrer dans "universal_get"
	{
		$config_sup = null;
		switch ($update) {
			case 'tiles':
				$config = 'api/v8/home/tileset/';
				break;
		}

		$result = $this->fetch('/' . $config . $id . $config_sup);
		log::add('Freebox_OS', 'debug', '┌───────── Traitement de la Mise à jour de l\'id : ' . $id);
		if ($result === false)
			return false;
		if ($result['success']) {
			return $result['result'];
		} else {
			return false;
		}
	}*/
	/*public function getHomeAdapterStatus($id = '') // Fonction plus appelé à supprimer => Intégrer dans "universal_get"
	{
		$result = $this->fetch('/api/v8/home/adapters/' . $id);
		if ($result === false)
			return false;
		if ($result['success'])
			return $result['result'];
		else
			return false;
	}*/

	/*public function networkPing($id = '') // Fonction plus appelé à supprimer => Intégrer dans "universal_get"
	{
		$result = $this->fetch('/api/v8/lan/browser/pub/' . $id);
		if ($result === false)
			return false;
		if ($result['success'])
			return $result;
		else
			return false;
	}*/
	/*public function getnetwork()  // Fonction plus appelé à supprimer => Intégrer dans "universal_get"
	{
		$result = $this->fetch('/api/v8/lan/browser/pub/');
		if ($result === false)
			return false;
		if ($result['success'])
			return $result['result'];
		else
			return false;
	}*/
	/*public function systemV8($update = 'system', $id = null, $boucle = 4) // Fonction plus appelé à supprimer => Intégrer dans "universal_get"
	{

		$result = $this->fetch('/api/v8/system');
		if ($result === false)
			return false;
		if ($result['success']) {
			switch ($boucle) {
				case 1:
					return $result['result']['sensors'];
				case 2:
					return $result['result']['fans'];
				case 3:
					return $result['result']['expansions'];
				case 4:
					return $result['result'];
			}
		} else {
			return false;
		}
	}*/
	/*public function getHomeAdapters_player($update = 'HomeAdapters') // Fonction plus appelé à supprimer => Intégrer dans "universal_get"
	{
		switch ($update) {
			case 'HomeAdapters':
				$config = 'api/v8/home/adapters';
				break;
			case 'player':
				$config = 'api/v8/player';
				break;
		}
		$result = $this->fetch('/' . $config);
		if ($result === false) {
			return false;
		}
		if ($result['success']) {
			return $result['result'];
		} else {
			return false;
		}
	}*/
	public function WakeOnLAN($Mac)
	{
		$return = $this->fetch('/api/v8/lan/wol/pub/', array("mac" => $Mac, "password" => ""), "POST");
		if ($return === false)
			return false;
		return $return['success'];
	}
	public function universal_put($parametre, $update = 'wifi', $id = null)
	{
		$fonction = "PUT";
		switch ($update) {
			case '4G':
				$config = 'api/v8/connection/lte/config';
				$config_log = 'Mise à jour de : Activation 4G';
				$config_commande = 'enabled';
				break;
			case 'parental':
				$config_log = 'Mise à jour du : Contrôle Parental';
				$config_commande = 'parental';

				$jsontestprofile = $this->fetch("/api/v8/network_control/" . $id);
				$jsontestprofile = $jsontestprofile['result'];
				if ($parametre == "denied") {
					$jsontestprofile['override_until'] = 0;
					$jsontestprofile['override'] = true;
				} else if ($parametre == "denied_30m") {
					$jsontestprofile['override_until'] = 0;
					$jsontestprofile['override'] = true;
				} else if ($parametre == "denied_1h") {
					$jsontestprofile['override_until'] = 0;
					$jsontestprofile['override'] = true;
				} else if ($parametre == "denied_2h") {
					$jsontestprofile['override_until'] = 0;
					$jsontestprofile['override'] = true;
				} else {
					$jsontestprofile['override'] = false;
				}
				$parametre = $jsontestprofile;
				$config = "api/v8/network_control/" . $id;
				break;
			case 'planning':
				$config = 'api/v8/wifi/planning';
				$config_log = 'Mise à jour : Planning du Wifi';
				$config_commande = 'use_planning';
				break;
			case 'WakeOnLAN':
				$config = '/api/v8/lan/wol/pub/';
				$fonction = "POST";
				$config_log = 'Mise à jour de : WakeOnLAN';
				break;
			case 'wifi':
				$config = 'api/v8/wifi/config';
				$config_commande = 'enabled';
				$config_log = 'Mise à jour de : Etat du Wifi';
				break;
		}
		if ($parametre === 1) {
			$parametre = true;
		} elseif ($parametre === 0) {
			$parametre = false;
		}

		if ($update == 'parental') {
			$return = $this->fetch('/' . $config . '', $parametre, $fonction, true);
		} else if ($update == 'WakeOnLAN') {
			$return = $this->fetch($config, array("mac" => $id, "password" => ""), $fonction);
		} else {
			log::add('Freebox_OS', 'debug', '>───────── ' . $config_log . ' avec la valeur : ' . $parametre);
			$return = $this->fetch('/' . $config . '/', array($config_commande => $parametre), $fonction);
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
				default:
					return $return;
					break;
			}
		}
	}
	public function ringtone($update = 'ON')
	{
		switch ($update) {
			case 'ON':
				$config = 'dect_page_start';
				break;
			case 'OFF':
				$config = 'dect_page_stop';
				break;
		}
		log::add('Freebox_OS', 'debug', '>───────── Ringtone ' . $update);
		$result = $this->fetch('/api/v8/phone/' . $config . '/', "", "POST");
		if ($result === false)
			return false;
		if ($result['success'])
			return $result;
		else
			return false;
	}
	/*public function ringtone_off() // Fonction plus appelé à supprimer => Intégrer dans "ringtone_on" renomé en ringtone
	{
		log::add('Freebox_OS', 'debug', '>───────── Ringtone OFF');
		$result = $this->fetch('/api/v8/phone/dect_page_stop/', "", "POST");
		if ($result === false)
			return false;
		if ($result['success'])
			return $result;
		else
			return false;
	}*/

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


	public function Updatesystem()
	{
		try {
			$logicalinfo = Freebox_OS::getlogicalinfo();

			$system = Freebox_OS::AddEqLogic($logicalinfo['systemName'], $logicalinfo['systemID'], 'default', false, null, null);
			$Command = $system->AddCommand('Update', 'update', 'action', 'other', null, null, null, 0, 'default', 'default', 0, null, 0, 'default', 'default',  null, '0', false, true);
			log::add('Freebox_OS', 'debug', '│ Vérification d\'une mise a jours du serveur');
			$firmwareOnline = file_get_contents("http://dev.freebox.fr/blog/?cat=5");
			preg_match_all('|<h1><a href=".*">Mise à jour du Freebox Server (.*)</a></h1>|U', $firmwareOnline, $parseFreeDev, PREG_PATTERN_ORDER);
			if (intval($Command->execCmd()) < intval($parseFreeDev[1][0]))
				$this->reboot();
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', '[FreeboxUpdatesystem]' . $e->getCode());
		}
	}
	/*public function getTiles($update = 'tiles')  // Fonction plus appelé à supprimer => Intégrer dans "ringtone_on" renomé en ringtone
	{
		switch ($update) {
			case 'tiles':
				$config = 'api/v8/home/tileset/all';
				break;
		}
		$result = $this->fetch('/' . $config);
		if ($result === false)
			return false;
		if ($result['success'])
			return $result['result'];
		else
			return false;
	}*/

	public function setTile($nodeId, $endpointId, $parametre, $update = 'tiles')
	{
		switch ($update) {
			case 'tiles':
				$config = 'api/v8/home/endpoints/';
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
	public function airmedia($update = 'config', $parametre, $receiver)
	{
		switch ($update) {
			case 'config':
				$config = 'config/';
				$fonction = "PUT";
				break;
			case 'receivers':
				$config = 'receivers/';
				$fonction = null;
				break;
			case 'action':
				$config = 'receivers/' . $receiver . '/';
				$fonction = "POST";
				break;
		}
		$result = $this->fetch('/api/v8/airmedia/' . $config, $parametre, $fonction);
		if ($result === false)
			return false;
		if ($result['success'])
			return $result['result'];
		else
			return false;
	}
	/*public function airmediaConfig($parametre) // Fonction plus appelé à supprimer => Intégrer dans "airmedia"
	{
		$result = $this->fetch('/api/v8/airmedia/config/', $parametre, "PUT");
		if ($result === false)
			return false;
		if ($result['success'])
			return $result['result'];
		else
			return false;
	}*/
	/*public function airmediaReceivers() // Fonction plus appelé à supprimer => Intégrer dans "airmedia" => Fonction non appelé
	{
		$result = $this->fetch('/api/v8/airmedia/receivers/');
		if ($result === false)
			return false;

		if ($result['success'])
			return $result['result'];
		else
			return false;
	}*/
	/*public function AirMediaAction($receiver, $Parameter) // Fonction plus appelé à supprimer => Intégrer dans "airmedia"
	{
		$result = $this->fetch('/api/v8/airmedia/receivers/' . $receiver . '/', $Parameter, 'POST');
		if ($result === false)
			return false;
		if ($result['success'])
			return true;
		else
			return false;
	}*/
}
