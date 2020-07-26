<?php
class Free_API
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

	public function downloads($Etat)
	{
		$result = $this->fetch('/api/v8/downloads/');
		if ($result === false)
			return false;
		$nbDL = count($result['result']);
		for ($i = 0; $i < $nbDL; ++$i) {
			if ($Etat == 0)
				$downloads = $this->fetch('/api/v8/downloads/' . $result['result'][$i]['id'], array("status" => "stopped"), "PUT");
			if ($Etat == 1)
				$downloads = $this->fetch('/api/v8/downloads/' . $result['result'][$i]['id'], array("status" => "downloading"), "PUT");
		}
		if ($downloads === false)
			return false;
		if ($downloads['success'])
			return $downloads['success'];
		else
			return false;
	}

	public function PortForwarding($id, $fonction = "get", $active = null)
	{
		$PortForwarding = $this->fetch('/api/v8/fw/redir/');
		if ($PortForwarding === false)
			return false;


		if ($fonction == "get") {
			$result = array();
			$_ip = cmd::byId($id)->getConfiguration('IPV4', '192.168.0.0');

			foreach ($PortForwarding['result'] as $value) {
				if ($value['lan_ip'] != $_ip) continue;
				$enabled = "0";
				if ($value['enabled'] == true) $enabled = "1";
				array_push($result, array(
					'id' => $value['id'],
					'enabled' => $enabled,
					'src_ip' => $value['src_ip'],
					'wan_port_start' => $value['wan_port_start'],
					'wan_port_end' => $value['wan_port_end'],
					'ip_proto' => $value['ip_proto'],
					'lan_ip' => $value['lan_ip'],
					'lan_port' => $value['lan_port'],
					'comment' => $value['comment']
				));
			}
			return $result;
		} elseif ($fonction == "put") {
			if ($active == 1) {
				$this->fetch('/api/v8/fw/redir/' . $id, array("enabled" => true), "PUT");
				return true;
			} else {
				$this->fetch('/api/v8/fw/redir/' . $id, array("enabled" => false), "PUT");
				return true;
			}
		}
	}

	public function disk()
	{
		$reponse = $this->fetch('/api/v8/storage/disk/');
		if ($reponse === false)
			return false;
		if ($reponse['success']) {
			$value = 0;
			foreach ($reponse['result'] as $disks) {
				$total_bytes = $disks['partitions'][0]['total_bytes'];
				$used_bytes = $disks['partitions'][0]['used_bytes'];
				$value = round($used_bytes / $total_bytes * 100, 2);
				log::add('Freebox_OS', 'debug', '┌───────── Update Disque ');
				log::add('Freebox_OS', 'debug', '│ Occupation [' . $disks['type'] . '] - ' . $disks['id'] . ': ' . $used_bytes . '/' . $total_bytes . ' => ' . $value . '%');

				$logicalinfo = Freebox_OS::getlogicalinfo();
				$disk = Freebox_OS::AddEqLogic($logicalinfo['diskName'], $logicalinfo['diskID'], 'default', false, null, null);

				$command = $disk->AddCommand('Occupation [' . $disks['type'] . '] - ' . $disks['id'], $disks['id'], 'info', 'numeric', 'Freebox_OS::Freebox_OS_Disque', '%', null, 1, 'default', 'default', 0, 'fas fa-save', 0, '0', 100,  null, '0', false);
				$command->event($value);
				log::add('Freebox_OS', 'debug', '└─────────');
			}
		}
	}

	public function universal_get($update = 'wifi', $id = null, $boucle = 4)
	{
		$config_log = null;
		switch ($update) {
			case '4G':
				$config = 'api/v8/connection/lte/config';
				$config_log = 'Etat 4G';
				break;
			case 'disk':
				$config = 'api/v8/storage/disk/' . $id;
				break;
			case 'download_stats':
				$config = 'api/v8/downloads/stats/';
				break;
			case 'homeadapters':
				$config = 'api/v8/home/adapters';
				break;
			case 'homeadapters_status':
				$config = 'api/v8/home/adapters/' . $id;
				break;
			case 'parental':
				$config = 'api/v8/network_control';
				$config_log = 'Etat Contrôle Parental';
				break;
			case 'parental_ID':
				$config = 'api/v8/network_control/' . $id;
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
			case 'PortForwarding':
				$config = '/api/v8/fw/redir/';
				$config_log = 'Redirection de port';
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
				case 'disk':
					$total_bytes = $result['result']['partitions'][0]['total_bytes'];
					$used_bytes = $result['result']['partitions'][0]['used_bytes'];
					break;
				case 'planning':
					if ($result['result']['use_planning']) {
						$value = 1;
					}
					break;
				case 'system':
					if ($boucle != null) {
						return $result['result'][$boucle];
					} else {
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
			if ($update == 'disks') {
				return round($used_bytes / $total_bytes * 100, 2);
			} else {
				return $value;
			}
		} else {
			return false;
		}
	}

	public function universal_put($parametre, $update = 'wifi', $id = null, $nodeId = null, $_options)
	{
		$fonction = "PUT";
		$config_log = null;
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
					$jsontestprofile['override_mode'] = "denied";
				} else if ($parametre == "tempDenied") {
					$date = new DateTime();
					$timestamp = $date->getTimestamp();
					$jsontestprofile['override_until'] = $timestamp + $_options['select'];
					$jsontestprofile['override'] = true;
					$jsontestprofile['override_mode'] = "denied";
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
			case 'phone_dell_call':
				$config = 'api/v8/call/log/delete_all';
				$fonction = "POST";
				break;
			case 'phone_read_call':
				$config = 'api/v8/call/log/mark_all_as_read';
				$fonction = "POST";
				break;
			case 'reboot':
				$config = 'api/v8/system/reboot';
				$fonction = "POST";
				break;
			case 'WakeOnLAN':
				$config = 'api/v8/lan/wol/pub/';
				$fonction = "POST";
				$config_log = 'Mise à jour de : WakeOnLAN';
				break;
			case 'wifi':
				$config = 'api/v8/wifi/config';
				$config_commande = 'enabled';
				$config_log = 'Mise à jour de : Etat du Wifi';
				break;
			case 'set_tiles':
				if ($id != null) {
					$id = $id . '/';
				} elseif ($id != 'refresh') {
					$id = null;
				}
				log::add('Freebox_OS', 'debug', '>───────── Info nodeid : ' . $nodeId . ' -- Id: ' . $id . ' -- Paramètre : ' . $parametre);
				$config = 'api/v8/home/endpoints/';
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
			$return = $this->fetch('/' . $config, array("mac" => $id, "password" => ""), $fonction);
		} else if ($update == 'set_tiles') {
			$return = $this->fetch('/' . $config . $nodeId . '/' . $id, $parametre, "PUT");
		} else if ($update == 'reboot' || $update == 'phone_dell_call' || $update == 'phone_read_call') {
			$return = $this->fetch('/' . $config . '/', null, $fonction);
		} else {
			if ($config_log != null) {
				log::add('Freebox_OS', 'debug', '>───────── ' . $config_log . ' avec la valeur : ' . $parametre);
			}
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
				case 'settile':
					return $return['result'];
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

	public function connexion_stats()
	{
		$adslRateJson = $this->fetch('/api/v8/connection/');
		if ($adslRateJson === false)
			return false;
		if ($adslRateJson['success']) {
			$vdslRateJson = $this->fetch('/api/v8/connection/xdsl/');
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

	public function nb_appel_absence()
	{
		$listNumber_missed = null;
		$listNumber_accepted = null;
		$listNumber_outgoing = null;
		$result = $this->fetch('/api/v8/call/log/');
		if ($result === false)
			return false;
		if ($result['success']) {
			$timestampToday = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
			if (isset($result['result'])) {
				$nb_call = count($result['result']);

				$cptAppel_outgoing = 0;
				$cptAppel_missed = 0;
				$cptAppel_accepted = 0;
				for ($k = 0; $k < $nb_call; $k++) {
					$jour = $result['result'][$k]['datetime'];

					$time = date('H:i', $result['result'][$k]['datetime']);
					if ($timestampToday <= $jour) {
						if ($result['result'][$k]['name'] == $result['result'][$k]['number']) {
							$name = "N.C.";
						} else {
							$name = $result['result'][$k]['name'];
						}

						if ($result['result'][$k]['type'] == 'missed') {
							$cptAppel_missed++;
							$listNumber_missed .= $result['result'][$k]['number'] . ": " . $name . " à " . $time . " - de " . $result['result'][$k]['duration'] . "s" . "\r\n";
						}
						if ($result['result'][$k]['type'] == 'accepted') {
							$cptAppel_accepted++;
							$listNumber_accepted .= $result['result'][$k]['number'] . ": " . $name . " à " . $time . " - de " . $result['result'][$k]['duration'] . "s" . "\r\n";
						}
						if ($result['result'][$k]['type'] == 'outgoing') {
							$cptAppel_outgoing++;
							$listNumber_outgoing .= $result['result'][$k]['number'] . ": " . $name . " à " . $time . " - de " . $result['result'][$k]['duration'] . "s" . "\r\n";
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
}
