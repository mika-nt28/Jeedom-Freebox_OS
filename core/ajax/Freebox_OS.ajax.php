<?php
try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'FreeboxAPI', 'class', 'Freebox_OS');
	include_file('core', 'authentification', 'php');
	if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}
	$FreeboxAPI = new FreeboxAPI();
	switch (init('action')) {
		case 'createCamera':
			$EqLogic = eqLogic::byLogicalId(init('id'), 'camera');
			if (!is_object($EqLogic)) {
				$url = explode('@', explode('://', init('url'))[1]);
				log::add('Freebox_OS', 'debug', '┌───────── Création de la caméra : ' . init('name'));
				$username = explode(':', $url[0])[0];
				$password = explode(':', $url[0])[1];

				$adresse = explode(':', explode('/', $url[1])[0]);
				$ip = $adresse[0];
				$port = $adresse[1];

				$EqLogic = new camera();
				$EqLogic->setName(init('name'));
				$EqLogic->setLogicalId(init('id'));
				$EqLogic->setObject_id(null);
				$EqLogic->setEqType_name('camera');
				$EqLogic->setIsEnable(1);
				$EqLogic->setIsVisible(0);
				$EqLogic->setconfiguration("protocole", "http");
				$EqLogic->setconfiguration("ip", $ip);
				$EqLogic->setconfiguration("port", $port);
				log::add('Freebox_OS', 'debug', '│ IP : ' . $ip . ' - Port : ' . $port);
				$EqLogic->setconfiguration("username", $username);
				$EqLogic->setconfiguration("password", $password);
				$EqLogic->setconfiguration("videoFramerate", 15);
				$EqLogic->setconfiguration("device", "rocketcam");
				$URL_snaphot = "img/snapshot.cgi?size=4&quality=1";
				$EqLogic->setconfiguration("urlStream", $URL_snaphot);
				$URLrtsp = init('url');
				$URLrtsp = str_replace("http", "rtsp", $URLrtsp);
				$URLrtsp = str_replace("/stream.m3u8", "/live", $URLrtsp);
				$URLrtsp = str_replace($ip, "#ip#", $URLrtsp);
				$URLrtsp = str_replace($username, "#username#", $URLrtsp);
				$URLrtsp = str_replace($password, "#password#", $URLrtsp);
				log::add('Freebox_OS', 'debug', '│ URL du flux : ' . $URLrtsp . ' - URL de snaphot : ' . $URL_snaphot);
				$EqLogic->setconfiguration('cameraStreamAccessUrl', $URLrtsp);
				$EqLogic->save();
				log::add('Freebox_OS', 'debug', '└─────────');
			}
			ajax::success(true);
			break;
		case 'sendToBdd':
			config::save('FREEBOX_SERVER_TRACK_ID', init('track_id'), 'Freebox_OS');
			config::save('FREEBOX_SERVER_APP_TOKEN', init('app_token'), 'Freebox_OS');
			ajax::success(true);
			break;
		case 'connect':
			ajax::success($FreeboxAPI->track_id());
			break;
		case 'ask_track_authorization':
			ajax::success($FreeboxAPI->ask_track_authorization());
			break;
		case 'SearchHomeAdapters':
			Freebox_OS::addHomeAdapters();
			ajax::success(true);
			break;
		case 'SearchParental':
			Freebox_OS::addparental();
			ajax::success(true);
			break;
		case 'SearchReseau':
			Freebox_OS::addReseau();
			ajax::success(true);
			break;
		case 'SearchSystem':
			Freebox_OS::addSystem();
			ajax::success(true);
			break;
		case 'SearchDisque':
			ajax::success($FreeboxAPI->disques());
			break;
		case 'AddPortForwarding':
			$PortForwarding = array(
				"enabled"		=> 	init('enabled'),
				"comment"		=> 	init('comment'),
				"lan_port"		=> 	init('lan_port'),
				"wan_port_end"	=> 	init('wan_port_end'),
				"wan_port_start" => 	init('wan_port_start'),
				"lan_ip" 		=>	init('lan_ip'),
				"ip_proto" 		=> 	init('ip_proto'),
				"src_ip"		=> 	init('src_ip')
			);
			ajax::success();
			break;
		case 'PortForwarding':
			ajax::success();
			break;
		case 'WakeOnLAN':
			$Command = cmd::byId(init('id'));
			if (is_object($Command)) {
				$Mac = str_replace('ether-', '', $Command->getLogicalId());
				ajax::success($FreeboxAPI->universal_put($Mac, 'WakeOnLAN'));
			}
			ajax::success(false);
			break;
			/*case 'sendCmdPlayer':
			$Player = eqLogic::byId(init('id'));
			if (is_object($Player)) {
				$Cmd = $Player->getCmd('action', init('cmd'));
				if (is_object($Cmd))
					ajax::success($Cmd->execute());
			}
			ajax::success(false);
			break;*/
		case 'get_airmediareceivers':
			ajax::success($FreeboxAPI->airmedia('receivers'));
			break;
		case 'set_airmediareceivers':
			$cmd = cmd::byId(init('id'));
			if (is_object($cmd)) {
				$cmd->setCollectDate('');
				$cmd->event(init('value'));
				ajax::success(true);
			}
			ajax::success(false);
			break;
		case 'SearchTile':
			Freebox_OS::addTiles();
			ajax::success(true);
			break;
		case 'SearchArchi':
			Freebox_OS::CreateArchi();
			ajax::success(true);
			break;
	}
	throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
