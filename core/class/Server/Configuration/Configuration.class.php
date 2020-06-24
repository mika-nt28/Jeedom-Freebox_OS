<?php
class FreeboxAPI
{
	public function track_id()
	{
		try {
			$serveur		= trim(config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS'));
			$app_id 		= trim(config::byKey('FREEBOX_SERVER_APP_ID', 'Freebox_OS'));
			$app_name 		= trim(config::byKey('FREEBOX_SERVER_APP_NAME', 'Freebox_OS'));
			$app_version 	= trim(config::byKey('FREEBOX_SERVER_APP_VERSION', 'Freebox_OS'));
			$device_name 	= trim(config::byKey('FREEBOX_SERVER_DEVICE_NAME', 'Freebox_OS'));
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
			log::add('Freebox_OS', 'error', $e->getCode());
		}
	}
	public function ask_track_authorization()
	{
		try {
			$serveur		= trim(config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS'));
			$track_id 		= config::byKey('FREEBOX_SERVER_TRACK_ID', 'Freebox_OS');
			$http = new com_http($serveur . '/api/v3/login/authorize/' . $track_id);
			$result = $http->exec(30, 2);
			if (is_json($result)) {
				return json_decode($result, true);
			}
			return $result;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', $e->getCode());
		}
	}
	public static function open_session()
	{
		try {
			log::add('Freebox_OS', 'debug', 'opening session');
			$serveur = trim(config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS'));
			$app_token = config::byKey('FREEBOX_SERVER_APP_TOKEN', 'Freebox_OS');
			$app_id = trim(config::byKey('FREEBOX_SERVER_APP_ID', 'Freebox_OS'));

			$http = new com_http($serveur . '/api/v3/login/');
			$json = $http->exec(30, 2);
			$json_retour = json_decode($json, true);

			$challenge = $json_retour['result']['challenge'];
			$password = hash_hmac('sha1', $challenge, $app_token);

			$http = new com_http($serveur . '/api/v3/login/session/');
			$http->setPost(json_encode(array(
				'app_id' => $app_id,
				'password' => $password
			)));
			$json = $http->exec(30, 2);
			$json_connect = json_decode($json, true);
			if ($json_connect['success']) {
				cache::set('Freebox_OS::SessionToken', $json_connect['result']['session_token'], 0);
			} else
				return false;
			return true;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', $e->getCode());
		}
	}
	public static function fetch($api_url, $params = array(), $method = 'GET')
	{
		try {
			$serveur = trim(config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS'));
			$cache = cache::byKey('Freebox_OS::SessionToken');
			$session_token = $cache->getValue('');
			log::add('Freebox_OS', 'debug', '┌───────── Update');
			log::add('Freebox_OS', 'debug', '│Connexion ' . $method . ' sur la l\'adresse ' . $serveur . $api_url . '(' . json_encode($params) . ')');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $serveur . $api_url);
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
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Fbx-App-Auth: $session_token"));
			$content = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($content, true);
			log::add('Freebox_OS', 'debug', '│ ' . $content);
			if (!$result['success']) {
				log::add('Freebox_OS', 'debug', '│ success KO');
				if (isset($result["error_code"])) {
					log::add('Freebox_OS', 'debug', '│ error_code exists');
					if ($result["error_code"] == "auth_required") {
						log::add('Freebox_OS', 'debug', '│ auth_required');
						self::deamon_stop();
						log::add('Freebox_OS', 'debug', '│ deamon stoped');
					}
				}
			}
			return $result;
		} catch (Exception $e) {
			log::add('Freebox_OS', '│ error', $e->getCode());
		}
		log::add('Freebox_OS', 'debug', '└─────────');
	}
	public static function close_session()
	{
		try {
			log::add('Freebox_OS', 'debug', 'closing session');
			$serveur = trim(config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS'));
			$http = new com_http($serveur . '/api/v3/login/logout/');
			$http->setPost(array());
			$json_close = $http->exec(2, 2);
			return $json_close;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', $e->getCode());
		}
	}
	public function WakeOnLAN($Mac)
	{
		$return = self::fetch('/api/v3/lan/wol/pub/', array("mac" => $Mac, "password" => ""), "POST");
		return $return['success'];
	}
	public function Downloads($Etat)
	{
		$List_DL = self::fetch('/api/v3/downloads/');
		$nbDL = count($List_DL['result']);
		for ($i = 0; $i < $nbDL; ++$i) {
			if ($Etat == 0)
				$Downloads = self::fetch('/api/v3/downloads/' . $List_DL['result'][$i]['id'], array("status" => "stopped"), "PUT");
			if ($Etat == 1)
				$Downloads = self::fetch('/api/v3/downloads/' . $List_DL['result'][$i]['id'], array("status" => "downloading"), "PUT");
		}
		if ($Downloads['success'])
			return $Downloads['success'];
		else
			return false;
	}
	public function DownloadStats()
	{
		$DownloadStats = self::fetch('/api/v3/downloads/stats/');
		if ($DownloadStats['success'])
			return $DownloadStats['result'];
		else
			return false;
	}
	public function PortForwarding($Port)
	{
		$PortForwarding = self::fetch('/api/v3/fw/redir/');

		$nbPF = count($PortForwarding['result']);
		for ($i = 0; $i < $nbPF; ++$i) {
			if ($PortForwarding['result'][$i]['wan_port_start'] == $Port)
				if ($PortForwarding['result'][$i]['enabled'])
					$PortForwarding = self::fetch('/api/v3/fw/redir/' . $PortForwarding['result'][$i]['id'], array("enabled" => false), "PUT");
				else
					$PortForwarding = self::fetch('/api/v3/fw/redir/' . $PortForwarding['result'][$i]['id'], array("enabled" => true), "PUT");
		}
		if ($PortForwarding['success'])
			return $PortForwarding['result'];
		else
			return false;
	}
	/*	public function disques($logicalId = '')  // Voir si on peut supprimer cette fonction de ce fichier => FreeboxAPI
	{
		$reponse = self::fetch('/api/v3/storage/disk/');
		if ($reponse['success']) {
			$value = 0;
			foreach ($reponse['result'] as $Disques) {
				$total_bytes = $Disques['partitions'][0]['total_bytes'];
				$used_bytes = $Disques['partitions'][0]['used_bytes'];
				$value = round($used_bytes / $total_bytes * 100, 2);
				log::add('Freebox_OS', 'debug', 'Occupation [' . $Disques['type'] . '] - ' . $Disques['id'] . ': ' . $used_bytes . '/' . $total_bytes . ' => ' . $value . '%', null, 1, 'default', 'default', 0, null, 0, "0", 100, 'default', null, 0, false);
				$Disque = self::AddEqLogic('Disque Dur', 'Disque');
				$commande = self::AddCommand($Disque, 'Occupation [' . $Disques['type'] . '] - ' . $Disques['id'], $Disques['id'], 'info', 'numeric', 'Freebox_OS::Freebox_OS_Disque', '%', null, 1, 'default', 'default', 0, null, 0, "0", 100, 'default', null, 0, false);
				$commande->setCollectDate(date('Y-m-d H:i:s'));
				$commande->setConfiguration('doNotRepeatEvent', 1);
				$commande->event($value);
			}
		}
	}*/

	public function system()
	{
		$systemArray = self::fetch('/api/v3/system/');

		if ($systemArray['success']) {
			return $systemArray['result'];
		} else
			return false;
	}
	public function UpdateSystem()
	{
		try {
			$System = self::AddEqLogic('Système', 'System');
			$Commande = self::AddCommand($System, 'Update', 'update', "action", 'other', null, null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 'default', null, 0, false);
			log::add('Freebox_OS', 'debug', 'Vérification d\'une mise a jours du serveur');
			$firmwareOnline = file_get_contents("http://dev.freebox.fr/blog/?cat=5");
			preg_match_all('|<h1><a href=".*">Mise à jour du Freebox Server (.*)</a></h1>|U', $firmwareOnline, $parseFreeDev, PREG_PATTERN_ORDER);
			if (intval($Commande->execCmd()) < intval($parseFreeDev[1][0]))
				self::reboot();
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', $e->getCode());
		}
	}
	public function adslStats()
	{
		$adslRateJson = self::fetch('/api/v3/connection/');
		if ($adslRateJson['success']) {
			$vdslRateJson = self::fetch('/api/v3/connection/xdsl/');
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
	public function freeboxPlayerPing()
	{
		self::open_session();
		$listEquipement = self::fetch('/api/v3/lan/browser/pub/');
		self::close_session();
		if ($listEquipement['success']) {
			$Reseau = Freebox_OS::AddEqLogic('Réseau', 'Reseau');
			foreach ($listEquipement['result'] as $Equipement) {
				if ($Equipement['primary_name'] != '') {
					$Commande = $Reseau->AddCommand($Equipement['primary_name'], $Equipement['id'], 'info', 'binary', 'Freebox_OS::Freebox_OS_Reseau', null, null, 1, 'default', 'default', 0, null, 0, 'default', 'default', 'default', null, 0, false);
					$Commande->setConfiguration('host_type', $Equipement['host_type']);
					if (isset($Equipement['l3connectivities'])) {
						foreach ($Equipement['l3connectivities'] as $Ip) {
							if ($Ip['active']) {
								if ($Ip['af'] == 'ipv4')
									$Commande->setConfiguration('IPV4', $Ip['addr']);
								else
									$Commande->setConfiguration('IPV6', $Ip['addr']);
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
		return true;
	}
	public function ReseauPing($id = '')
	{
		$Ping = self::fetch('/api/v3/lan/browser/pub/' . $id);

		if ($Ping['success'])
			return $Ping['result'];
		else
			return false;
	}
	public function nb_appel_absence()
	{
		$listNumber_missed = '';
		$listNumber_accepted = '';
		$listNumber_outgoing = '';
		$pre_check_con = self::fetch('/api/v3/call/log/');
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
							$listNumber_missed .= $pre_check_con['result'][$k]['number'] . ": " . $name . " à " . $time . " - de " . $pre_check_con['result'][$k]['duration'] . "s<br>";
						}
						if ($pre_check_con['result'][$k]['type'] == 'accepted') {
							$cptAppel_accepted++;
							$listNumber_accepted .= $pre_check_con['result'][$k]['number'] . ": " . $name . " à " . $time . " - de " . $pre_check_con['result'][$k]['duration'] . "s<br>";
						}
						if ($pre_check_con['result'][$k]['type'] == 'outgoing') {
							$cptAppel_outgoing++;
							$listNumber_outgoing .= $pre_check_con['result'][$k]['number'] . ": " . $name . " à " . $time . " - de " . $pre_check_con['result'][$k]['duration'] . "s<br>";
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
	public function send_cmd_fbxtv($key)
	{
		try {
			$serveur = trim($this->getConfiguration('FREEBOX_TV_IP'));
			$tv_code = trim($this->getConfiguration('FREEBOX_TV_CODE'));
			$http = new com_http($serveur . '/pub/remote_control?code=' . $tv_code . '&key=' . $key);
			$result = $http->exec(2, 2);
			return $result;
		} catch (Exception $e) {
			log::add('Freebox_OS', 'error', $e->getCode());
		}
	}
	public function airmediaConfig()
	{
		$parametre["enabled"] = $this->getIsEnable();
		$parametre["password"] = $this->getConfiguration('password');
		$return = self::fetch('/api/v3/airmedia/config/', $parametre, "PUT");

		if ($return['success'])
			return $return['result'];
		else
			return false;
	}
	public static function airmediaReceivers()
	{
		$return = self::fetch('/api/v3/airmedia/receivers/');

		if ($return['success'])
			return $return['result'];
		else
			return false;
	}
	public function AirMediaAction($receiver, $action, $media_type, $media = null)
	{
		if ($receiver != "" && $media_type != null) {
			log::add('Freebox_OS', 'debug', 'AirMedia Start Video: ' . $media);
			$parametre["action"] = $action;
			$parametre["media_type"] = $media_type;
			if ($media != null)
				$parametre["media"] = $media;
			$parametre["password"] = $this->getConfiguration('password');
			$return = self::fetch('/api/v3/airmedia/receivers/' . ($receiver) . '/', $parametre, 'POST');
			if ($return['success'])
				return true;
			else
				return false;
		} else
			return false;
	}
}
