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
			Free_CreateTil::createTil('camera');
			ajax::success(true);
			break;
		case 'connect':
			ajax::success($Free_API->track_id());
			break;
		case 'ask_track_authorization':
			ajax::success($Free_API->ask_track_authorization());
			break;
		case 'UpdatePortForwarding':
			ajax::success($Free_API->PortForwarding(init('id'), "PUT", init('enabled')));
			break;
		case 'PortForwarding':
			$id_logical = cmd::byId(init('id'))->getLogicalId();
			ajax::success($Free_API->PortForwarding($id_logical, "get"));
			break;
		case 'WakeOnLAN':
			$Mac = cmd::byId(init('id'))->getConfiguration('mac_address', '00:00:00:00:00:00');
			ajax::success($Free_API->universal_put(null, 'WakeonLAN', $Mac, null, null, null, init('password')));
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
			$result = Free_CreateTil::createTil();
			ajax::success($result);
			break;
		case 'SearchTile_group':
			$objects = "";
			$objects = $objects . '<option value="">Default</option>';
			foreach (jeeObject::all() as $object) {
				$objects = $objects . '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
			}
			$objects = $objects . '</select>';
			$result = array(
				piece => Free_CreateTil::createTil('Tiles_group'),
				objects => $objects,
				config =>  config::bykey('FREEBOX_PIECE', 'Freebox_OS', "")
			);

			ajax::success($result);
			break;
		case 'SearchArchi':
			Free_CreateEq::createEq();
			Free_CreateTV::createTV();
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
		case 'Searchnetshare':
			Free_CreateEq::createEq('netshare');
			ajax::success(true);
			break;
		case 'Searchnetworkwifiguest':
			Free_CreateEq::createEq('networkwifiguest');
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
			Free_CreateEq::createEq('disk');
			ajax::success(true);
			break;
		case 'GetBox':
			$deamon = Freebox_OS::deamon_info();
			$Type_box = config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS');
			if ($deamon['state'] == 'ok' && $Type_box != 'OK') {
				Free_CreateTil::createTil('box');
			}
			$result = array(
				"Type_box" => config::byKey('TYPE_FREEBOX', 'Freebox_OS', "null"),
				"Type_box_name" => config::byKey('TYPE_FREEBOX_NAME', 'Freebox_OS', "null"),
				"Type_box_tiles" => config::byKey('TYPE_FREEBOX_TILES', 'Freebox_OS', "NOK")
			);
			ajax::success($result);
			break;
		case 'GetSetting':
			$result = array(
				"ip" => config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS'),
				"VersionAPP" => config::byKey('FREEBOX_SERVER_APP_VERSION', 'Freebox_OS'),
				"NameAPP" => config::byKey('FREEBOX_SERVER_APP_NAME', 'Freebox_OS'),
				"IdApp" => config::byKey('FREEBOX_SERVER_APP_ID', 'Freebox_OS'),
				"DeviceName" => config::byKey('FREEBOX_SERVER_DEVICE_NAME', 'Freebox_OS'),
				"Categorie" => config::byKey('defaultParentObject', 'Freebox_OS'),
				"LogLevel" => log::getLogLevel('Freebox_OS')
			);
			ajax::success($result);
			break;
		case 'SetSetting':
			config::save('FREEBOX_SERVER_IP', init('ip'), 'Freebox_OS');
			config::save('FREEBOX_SERVER_APP_VERSION', init('VersionAPP'), 'Freebox_OS');
			config::save('defaultParentObject', init('Categorie'), 'Freebox_OS');
			ajax::success(true);
			break;
		case 'GetSessionData':
			Freebox_OS::deamon_start();
			ajax::success($Free_API->getFreeboxOpenSessionData());
			break;
		case 'resetSetting':
			Freebox_OS::resetConfig();
			ajax::success(true);
			break;
		case 'sendToBdd':
			config::save('FREEBOX_SERVER_TRACK_ID', init('track_id'), 'Freebox_OS');
			config::save('FREEBOX_SERVER_APP_TOKEN', init('app_token'), 'Freebox_OS');
			ajax::success(true);
			break;
		case  'setRoomID':
			$result = "";
			$data = init('data');
			$piecefinal = [];
			foreach ($data as $piece) {
				$piecename = $piece["PieceName"];
				$value = ($piece['object_id'] != '' ? $piece['object_id'] : config::byKey('defaultParentObject', 'Freebox_OS'));
				$piecefinal[$piecename] = $value;
			}
			config::save('FREEBOX_PIECE', $piecefinal, 'Freebox_OS');
			$result = $piecefinal;
			ajax::success($result);
			break;
		case  'setLogs':
			log::add('Freebox_OS', init('loglevel'), init('logsText'));
			ajax::success();
			break;
	}
	throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
