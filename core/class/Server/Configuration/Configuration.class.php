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
			$serveur = trim(config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS'));
			$track_id = config::byKey('FREEBOX_SERVER_TRACK_ID', 'Freebox_OS');
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

	}*/

	public function system()
	{
		$systemArray = self::fetch('/api/v3/system/');

		if ($systemArray['success']) {
			return $systemArray['result'];
		} else
			return false;
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
	
}
