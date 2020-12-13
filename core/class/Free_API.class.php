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
        $this->track_id = config::byKey('FREEBOX_SERVER_TRACK_ID', 'Freebox_OS');
        $this->app_token = config::byKey('FREEBOX_SERVER_APP_TOKEN', 'Freebox_OS');
    }

    public function track_id() //Doit correspondre a la donction "auth" de freboxsession.js homebridge freebox
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
            log::add('Freebox_OS', 'error', '[Freebox TrackId] : ' . $e->getCode());
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
            log::add('Freebox_OS', 'error', '[Freebox Autorisation] : ' . $e->getCode());
        }
    }

    public function getFreeboxPassword()
    {
        try {
            $http = new com_http($this->serveur . '/api/v8/login/');
            $json = $http->exec(30, 2);
            log::add('Freebox_OS', 'debug', '[Freebox Password] : ' . $json);
            $json_connect = json_decode($json, true);
            if ($json_connect['success'])
                cache::set('Freebox_OS::Challenge', $json_connect['result']['challenge'], 0);
            else
                return false;
            return true;
        } catch (Exception $e) {
            log::add('Freebox_OS', 'error', '[Freebox Password] : ' . $e->getCode());
        }
    }

    public function getFreeboxOpenSession() //Doit correspondre a la donction session de freboxsession.js homebridge freebox
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
                'app_version' => $this->app_version, // Ajout suivant fonction session Free homebridge
                'password' => hash_hmac('sha1', $challenge->getValue(''), $this->app_token)
            )));
            $json = $http->exec(30, 2);
            log::add('Freebox_OS', 'debug', '[Freebox Open Session] : ' . $json);
            $result = json_decode($json, true);

            if (!$result['success']) {
                $this->ErrorLoop++;
                $this->close_session();
                if ($this->ErrorLoop < 5) {
                    if ($this->getFreeboxOpenSession() === false)
                        return false;
                }
                log::add('Freebox_OS', 'debug', '[Freebox Etat Session] : NOK / ' . $result['success']);
            } else {
                cache::set('Freebox_OS::SessionToken', $result['result']['session_token'], 0);
                log::add('Freebox_OS', 'debug', '[Freebox Etat Session] : OK / ' . $result['success']);
                return true;
            }
            return false;
        } catch (Exception $e) {
            log::add('Freebox_OS', 'error', '[Freebox Open Session] : ' . $e->getCode());
        }
    }

    public function getFreeboxOpenSessionData()
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
            log::add('Freebox_OS', 'debug', '[get Freebox Open Session Data] : ' . $json);
            $result = json_decode($json, true);
            return $result;
        } catch (Exception $e) {
            log::add('Freebox_OS', 'error', '[get Freebox Open Session Data] : ' . $e->getCode());
        }
    }

    public function fetch($api_url, $params = array(), $method = 'GET', $log_update = false, $log_createeq = false)
    {
        try {
            $session_token = cache::byKey('Freebox_OS::SessionToken');
            while ($session_token->getValue('') == '') {
                $session_token = cache::byKey('Freebox_OS::SessionToken');
            }

            if ($log_update == false) {
                log::add('Freebox_OS', 'debug', '┌───────── Début de Mise à jour ');
            };
            log::add('Freebox_OS', 'debug', '│ [Freebox Request Connexion] : ' . $method . ' sur la l\'adresse ' . $this->serveur . $api_url . '(' . json_encode($params) . ')');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->serveur . $api_url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            if ($method == "POST") {
                curl_setopt($ch, CURLOPT_POST, true);
            } elseif ($method == "DELETE" || $method == "PUT") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
            if ($params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Fbx-App-Auth: " . $session_token->getValue('')));
            $content = curl_exec($ch);
            $errorno = 0;
            if (curl_errno($ch) !== 0) {
                $error = curl_error($ch);
                $errorno = curl_errno($ch);
            }
            curl_close($ch);

            log::add('Freebox_OS', 'debug', '│ [Freebox Request Result] : ' . $content);

            if ($errorno !== 0) {
                return '│ Erreur de connexion cURL vers ' . $this->serveur . $api_url . ': ' . $error;
            } else {
                $result = json_decode($content, true);
                if ($result == null) return false;
                if (!$result['success']) {
                    if ($result['error_code'] == "insufficient_rights" || $result['error_code'] == 'missing_right') {
                        log::add('Freebox_OS', 'error', 'Erreur Droits : '  . $result['msg']);
                        return false;
                    } else if ($result['error_code'] == "auth_required") {
                        log::add('Freebox_OS', 'Debug', '[Redémarrage session à cause de l\'erreur] : ' . $result['error_code']);
                        $this->close_session();
                        $this->getFreeboxOpenSessionData();
                        log::add('Freebox_OS', 'Debug', '[Redémarrage session Terminée à cause de l\'erreur] : ' . $result['error_code']);
                        $result = 'auth_required';
                        return $result;
                    } else if ($result['error_code'] == 'denied_from_external_ip') {
                        log::add('Freebox_OS', 'error', 'Erreur Accès : '  . $result['msg']);
                        return false;
                    } else if ($result['error_code'] == 'new_apps_denied' || $result['error_code'] == 'apps_denied') {
                        log::add('Freebox_OS', 'error', 'Erreur Application : '  . $result['msg']);
                        return false;
                    } else if ($result['error_code'] == 'invalid_token' || $result['error_code'] == 'pending_token') {
                        log::add('Freebox_OS', 'error', 'Erreur Token : ' . $result['msg']);
                        return false;
                    } else if ($result['error_code'] == "invalid_request" || $result['error_code'] == 'ratelimited') {
                        log::add('Freebox_OS', 'error', 'Erreur AUTRE : '  . $result['msg']);
                        return false;
                    }
                }
                log::add('Freebox_OS', 'debug', '└─────────');
                return $result;
            }
        } catch (Exception $e) {
            log::add('Freebox_OS', 'error', '│ [Freebox Request] : '  . $e->getCode());
            log::add('Freebox_OS', 'debug', '└─────────');
        }
    }

    public function close_session()
    {
        log::add('Freebox_OS', 'debug', '│──────────> Close Session  ');
        try {
            $Challenge = cache::byKey('Freebox_OS::Challenge');
            if (is_object($Challenge)) {
                $Challenge->remove();
                log::add('Freebox_OS', 'debug', '[Freebox Close Session] : Remove Challenge');
            }
            $session_token = cache::byKey('Freebox_OS::SessionToken');
            if (!is_object($session_token) || $session_token->getValue('') == '') {
                log::add('Freebox_OS', 'debug', '[Freebox Close Session] : Token Vide');
                return;
            }

            $http = new com_http($this->serveur . '/api/v8/login/logout/');
            $http->setPost(array());
            $json = $http->exec(2, 2);
            log::add('Freebox_OS', 'debug', '[Freebox Close Session] : ' . $json);
            $SessionToken = cache::byKey('Freebox_OS::SessionToken');

            if (is_object($SessionToken)) {
                $SessionToken->remove();
                log::add('Freebox_OS', 'debug', '[Freebox Close Session] : Remove Token');
            }
            log::add('Freebox_OS', 'debug', '│──────────> Fin Close Session  ');
            return $json;
        } catch (Exception $e) {
            log::add('Freebox_OS', 'debug', '[Freebox Close Session] : ' . $e->getCode() . ' ou session déjà fermée');
        }
    }

    public function PortForwarding($id, $fonction = "get", $active = null)
    {
        $PortForwarding = $this->fetch('/api/v8/fw/redir/');
        if ($PortForwarding === false)
            return false;

        if ($fonction == "get") {
            $result = array();
            foreach ($PortForwarding['result'] as $value) {
                if ($value['host']['id'] != $id) continue;
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
        } elseif ($fonction == "PUT") {
            if ($active == 1) {
                $this->fetch('/api/v8/fw/redir/' . $id, array("enabled" => true), $fonction);
                return true;
            } elseif ($active == 0) {
                $this->fetch('/api/v8/fw/redir/' . $id, array("enabled" => false), $fonction);
                return true;
            } elseif ($active == 3) {
                $this->fetch('/api/v8/fw/redir/' . $id, null, "DELETE");
                return true;
            }
        }
    }

    public function universal_get($update = 'wifi', $id = null, $boucle = 4, $update_type = 'config')
    {
        $config_log = null;
        $fonction = "GET";
        $Parameter = null;
        if ($id != null) {
            $id = '/' . $id;
        } else if ($id == null && $update == 'tiles') {
            $id = '/all';
        }
        switch ($update) {
            case 'airmedia':
                $config = 'api/v8/airmedia/receivers/';
                break;
            case 'connexion':
                $config = 'api/v8/connection/' . $update_type;
                $config_log = 'Traitement de la Mise à jour de ' . $update_type . ' avec la valeur';
                break;
            case 'disk':
                $config = 'api/v8/storage/disk';
                break;
            case 'download':
                $config = 'api/v8/downloads/' . $update_type;
                break;
            case 'homeadapters':
                $config = 'api/v8/home/adapters' . $id;
                break;
            case 'notification':
                $config = 'api/v8/notif/targets';
                $config_log = 'Liste des notifications';
                break;
            case 'notification_ID':
                $config = 'api/v8/notif/targets' . $id;
                $config_log = 'Etat des notifications';
                break;
            case 'parental':
                $config = 'api/v8/network_control' . $id;
                $config_log = 'Etat Contrôle Parental';
                break;
            case 'parentalprofile':
                $config = 'api/v8/profile';
                break;
            case 'player':
                $config = 'api/v8/player';
                break;
            case 'player_ID':
                $config = 'api/v8/player' . $id . '/api/v6/status';
                $config_log = 'Traitement de la Mise à jour de l\'id ';
                break;
            case 'network':
            case 'network_ping':
                $config = 'api/v8/lan/' . $update_type;
                break;
            case 'netshare':
                $config = 'api/v8/' . $update_type;
                break;
            case 'network_ID':
                $config = 'api/v8/lan/browser/' . $update_type  . $id;
                break;
            case 'system':
                $config = 'api/v8/system';
                break;
            case 'switch':
                $config = 'api/v8/switch/status';
                break;
            case 'tiles':
                $config = 'api/v8/home/tileset' . $id;
                $config_log = 'Traitement de la Mise à jour de l\'id ';
                break;
            case 'wifi':
                $config = 'api/v8/wifi/' . $update_type;
                $config_log = 'Traitement de la Mise à jour de wifi/' . $update_type . ' avec la valeur';
                break;
            case 'PortForwarding':
                $config = '/api/v8/fw/redir/';
                $config_log = 'Redirection de port';
        }

        $result = $this->fetch('/' . $config, $Parameter, $fonction);
        if ($result == 'auth_required') {
            $result = $this->fetch('/' . $config, $Parameter, $fonction);
        }
        if ($result === false) {
            return false;
        }
        if ($result['success']) {
            $value = 0;
            switch ($update) {
                case 'connexion':
                    if ($update_type == 'lte/config' && $boucle == 4) {
                        if ($result['result']['enabled']) {
                            $value = 1;
                        }
                        log::add('Freebox_OS', 'debug', '>───────── ' . $config_log . ' : ' . $value);
                        return $value;
                    } else {
                        return $result['result'];
                    }
                    break;
                case 'disk':
                case 'network_ping':
                case 'network':
                case 'wifi':
                    return $result;
                    break;
                case 'system':
                    if ($boucle != null) {
                        return $result['result'][$boucle];
                    } else {
                        return $result['result'];
                    }
                    break;
                default:
                    if ($config_log != null && $id != null && $id != '/all') {
                        log::add('Freebox_OS', 'debug', '>───────── ' . $config_log . ' : ' . $id);
                    }
                    return $result['result'];
                    break;
            }

            return $value;
        } else {
            if ($update == "network_ping" || $update == "network_ID") {
                return $result;
            } else if ($update_type == 'lte/config') {
                return $result['msg'];
            } else {
                return false;
            }
        }
    }
    public function downloads_put($Etat)
    {
        $result = $this->fetch('/api/v8/downloads/');
        if ($result == 'auth_required') {
            $result = $this->fetch('/api/v8/downloads/');
        }
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
    public function universal_put($parametre, $update = 'wifi', $id = null, $nodeId = null, $_options, $_status_cmd = null, $_options_2 = null)
    {
        $fonction = "PUT";
        $config_log = null;
        switch ($update) {
            case '4G':
                $config = 'api/v8/connection/lte/config';
                $config_log = 'Mise à jour de : Activation 4G';
                $config_commande = 'enabled';
                break;
            case 'download':
                $config = 'api/v8/downloads/throttling';
                break;
            case 'notification_ID':
                $config = 'api/v8/notif/targets/' . $id;
                if ($_options == 'DELETE') {
                    $fonction = $_options;
                }
                break;
            case 'lcd':
                $config = 'api/v8/lcd/config';
                $config_commande = 'hide_wifi_key';
                break;
            case 'netshare':
                $config = 'api/v8/' . $id;
                $config_commande = $_options;
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
                    if ($_status_cmd == 'denied') {
                        $jsontestprofile['override_mode'] = "allowed";
                    } else {
                        $jsontestprofile['override_mode'] = "denied";
                    }
                } else {
                    $jsontestprofile['override'] = false;
                }
                $parametre = $jsontestprofile;
                $config = "api/v8/network_control/" . $id;
                break;
            case 'player_ID_ctrl':
                $config = 'api/v8/player/' . $id . '/api/v6/control/mediactrl';
                $config_log = 'Traitement de la Mise à jour de l\'id ';
                $config_commande = 'name';
                $fonction = "POST";
                break;
            case 'player_ID_open':
                $config = 'api/v8/player/' . $id . '/api/v6/control/open';
                $config_log = 'Traitement de la Mise à jour de l\'id ';
                $config_commande = 'url';
                $fonction = "POST";
                break;
            case 'phone':
                $config = 'api/v8/call/log/' . $_options;
                $fonction = "POST";
                break;
            case 'reboot':
                $config = 'api/v8/system/reboot';
                $fonction = "POST";
                break;
            case 'WakeonLAN':
                $config = 'api/v8/lan/wol/pub/';
                $fonction = "POST";
                $config_log = 'Mise à jour de : WakeOnLAN';
                break;
            case 'wifi':
                $config = 'api/v8/wifi/' . $_options;
                if ($_options == 'planning') {
                    $config_commande = 'use_planning';
                } else if ($_options == 'wps/start') {
                    $fonction = "POST";
                    $config_commande = 'bssid';
                } else if ($_options == 'wps/stop') {
                    $fonction = "POST";
                } else if ($_options == 'mac_filter') {
                    $fonction = $id['function'];
                    if ($fonction != 'POST') {
                        $id = $id['mac_address'] . '-' . $id['filter'];
                        $parametre = null;
                    } else {
                        $_filter = $id['filter'];
                        $mac_adress = $id['mac_address'];
                        $comment = $id['comment'];
                        $id = null;
                        $parametre = array("mac" => $mac_adress, "type" => $_filter, "comment" => $comment);
                    }
                } else if ($_options == 'config' && $_options_2 == 'mac_filter_state') {
                    $config_commande = 'mac_filter_state';
                } else {
                    $config_commande = 'enabled';
                }
                if ($_options == 'planning' || $_options == 'wifi') {
                    $config_log = 'Mise à jour de : Etat du Wifi ' . $_options;
                } else {
                    $config_log = null;
                }
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
                $config_log = 'Mise à jour de : ';
                break;
        }
        if ($parametre === 1) {
            $parametre = true;
        } elseif ($parametre === 0) {
            $parametre = false;
        }
        if ($update == 'parental' || $update == 'donwload') {
            $return = $this->fetch('/' . $config . '', $parametre, $fonction, true);
        } else if ($update == 'WakeonLAN') {
            $return = $this->fetch('/' . $config, array("mac" => $id, "password" => $_options_2), $fonction);
            return $return['success'];
        } else if ($update == 'set_tiles') {
            $return = $this->fetch('/' . $config . $nodeId . '/' . $id, $parametre, "PUT");
        } else if ($_options == 'mac_filter') {
            $return = $this->fetch('/' . $config  . '/' . $id, $parametre, $fonction);
        } else if ($update == 'phone') {
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
                case '4G':
                    if ($_options == 'planning') {
                        return $return['result']['use_planning'];
                    } else {
                        return $return['result']['enabled'];
                    };
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

    public function nb_appel_absence()
    {
        $listNumber_missed = null;
        $listNumber_accepted = null;
        $listNumber_outgoing = null;
        $result = $this->fetch('/api/v8/call/log/');
        if ($result == 'auth_required') {
            $result = $this->fetch('/api/v8/call/log/');
        }
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
                            $listNumber_missed .= '<br>' . $result['result'][$k]['number'] . " : " . $name . " à " . $time . " de " . $this->fmt_duree($result['result'][$k]['duration']);
                        }
                        if ($result['result'][$k]['type'] == 'accepted') {
                            $cptAppel_accepted++;
                            $listNumber_accepted .= '<br>' . $result['result'][$k]['number'] . " : " . $name . " à " . $time . " de " . $this->fmt_duree($result['result'][$k]['duration']);
                        }
                        if ($result['result'][$k]['type'] == 'outgoing') {
                            $cptAppel_outgoing++;
                            $listNumber_outgoing .= '<br>' . $result['result'][$k]['number'] . " : " . $name . " à " . $time . " de " . $this->fmt_duree($result['result'][$k]['duration']);
                        }
                    }
                }
                $retourFbx = array('missed' => $cptAppel_missed, 'list_missed' => $listNumber_missed, 'accepted' => $cptAppel_accepted, 'list_accepted' => $listNumber_accepted, 'outgoing' => $cptAppel_outgoing, 'list_outgoing' => $listNumber_outgoing);
            } else {
                $retourFbx = array('missed' => 0, 'list_missed' => "", 'accepted' => 0, 'list_accepted' => "", 'outgoing' => 0, 'list_outgoing' => "");
            }
            return $retourFbx;
        } else
            return false;
    }

    function fmt_duree($duree)
    {
        if (floor($duree) == 0) return '0s';
        $h = floor($duree / 3600);
        $m = floor(($duree % 3600) / 60);
        $s = $duree % 60;
        $fmt = '';
        if ($h > 0) $fmt .= $h . 'h ';
        if ($m > 0) $fmt .= $m . 'min ';
        if ($s > 0) $fmt .= $s . 's';
        return ($fmt);
    }

    public function mac_filter_list()
    {
        $listmac_whitelist = null;
        $listmac_blacklist = null;
        $result = $this->fetch('/api/v8/wifi/mac_filter/');
        if ($result == 'auth_required') {
            $result = $this->fetch('/api/v8/wifi/mac_filter/');
        }
        if ($result === false)
            return false;
        if ($result['success']) {
            if (isset($result['result'])) {
                $nb_mac = count($result['result']);

                for ($k = 0; $k < $nb_mac; $k++) {
                    $name = $result['result'][$k]['hostname'];
                    if ($result['result'][$k]['type'] == 'whitelist') {
                        $listmac_whitelist  .= '<br>' . $name . " - " . $result['result'][$k]['mac'] . " - " . $result['result'][$k]['comment'];
                    }
                    if ($result['result'][$k]['type'] == 'blacklist') {
                        $listmac_blacklist .= '<br>' . $name . " - " . $result['result'][$k]['mac'] . " - " . $result['result'][$k]['comment'];
                    }
                }
                $return = array('listmac_blacklist' => $listmac_blacklist, 'listmac_whitelist' => $listmac_whitelist);
            } else {
                $return = array('listmac_blacklist' => '', 'listmac_whitelist' => "");
            }
            return $return;
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
        if ($result == 'auth_required') {
            $result = $this->fetch('/api/v8/airmedia/' . $config, $parametre, $fonction);
        }
        if ($result === false)
            return false;
        if ($result['success'])
            return $result['result'];
        else
            return false;
    }
}
