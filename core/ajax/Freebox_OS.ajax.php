<?php
try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	require_once dirname(__FILE__) . '/../../core/php/Freebox_OS.inc.php';
	include_file('core', 'authentification', 'php');
	if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}
	$Free_API = new Free_API();
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
			ajax::success($Free_API->track_id());
			break;
		case 'ask_track_authorization':
			ajax::success($Free_API->ask_track_authorization());
			break;
		case 'AddPortForwarding':
			ajax::success($Free_API->PortForwarding(init('id'), "put", init('enabled')));
			break;
		case 'PortForwarding':
			ajax::success($Free_API->PortForwarding(init('id'), "get"));
			break;
		case 'WakeOnLAN':
			$Mac = cmd::byId(init('id'))->getConfiguration('mac_address', '00:00:00:00:00:00');
			ajax::success($Free_API->universal_put(null, 'WakeOnLAN', $Mac, null, null));
			break;
		case 'get_airmediareceivers':
			ajax::success($Free_API->airmedia('receivers', null, null));
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
			Free_CreateTil::createTil('homeadapters');
			Free_CreateTil::createTil();
			ajax::success(true);
			break;
		case 'SearchArchi':
			Free_CreateEq::createEq();
			ajax::success(true);
			break;
		case 'Searchairmedia':
			Free_CreateEq::createEq('airmedia');
			ajax::success(true);
			break;
		case 'Searchconnexion':
			Free_CreateEq::createEq('connexion');
			ajax::success(true);
			break;
		case 'Searchdownloads':
			Free_CreateEq::createEq('downloads');
			ajax::success(true);
			break;
		case 'Searchhomeadapters':
			Free_CreateTil::createTil('homeadapters_SP');
			ajax::success(true);
			break;
		case 'SearchParental':
			Free_CreateEq::createEq('parental');
			ajax::success(true);
			break;
		case 'Searchnetwork':
			Free_CreateEq::createEq('network');
			ajax::success(true);
			break;
		case 'Searchphone':
			Free_CreateEq::createEq('phone');
			ajax::success(true);
			break;
		case 'Searchsystem':
			Free_CreateEq::createEq('system');
			ajax::success(true);
			break;
		case 'Searchwifi':
			Free_CreateEq::createEq('wifi');
			ajax::success(true);
			break;
		case 'Searchdisk':
			ajax::success($Free_API->disk());
			break;
	}
	throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
