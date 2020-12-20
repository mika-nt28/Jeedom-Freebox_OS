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
			$option = array(
				"mac" => cmd::byId(init('id'))->getConfiguration('mac_address', '00:00:00:00:00:00'),
				"password" => init('password')
			);
			ajax::success($Free_API->universal_put(null, 'universal_put', $Mac, null, 'lan/wol/pub/', null, $option));
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
			foreach ((jeeObject::buildTree(null, false)) as $object) {
				$decay = $object->getConfiguration('parentNumber');
				$objects .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $decay) . $object->getName() . '</option>';
			}
			$objects = $objects . '</select>';
			$result = array(
				"pieces" => Free_CreateTil::createTil('Tiles_group'),
				"objects" => $objects,
				"config" =>  config::bykey('FREEBOX_PIECE', 'Freebox_OS', "")
			);

			ajax::success($result);
			break;
		case 'SearchArchi':
			Free_CreateEq::createEq();
			Free_CreateTV::createTV();
			ajax::success(true);
			break;
		case 'Search':
			if (init('search') == 'homeadapters') {
				$Search = 'homeadapters_SP';
				Free_CreateTil::createTil($Search);
			} else {
				$Search = init('search');
				Free_CreateEq::createEq($Search);
			}

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
