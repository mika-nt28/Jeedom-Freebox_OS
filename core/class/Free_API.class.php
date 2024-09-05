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
    private $API_version;

    public function __construct()
    {
        $this->serveur = trim(config::byKey('FREEBOX_SERVER_IP', 'Freebox_OS'));
        $this->app_id = trim(config::byKey('FREEBOX_SERVER_APP_ID', 'Freebox_OS'));
        $this->app_name = trim(config::byKey('FREEBOX_SERVER_APP_NAME', 'Freebox_OS'));
        $this->app_version = trim(config::byKey('FREEBOX_SERVER_APP_VERSION', 'Freebox_OS'));
        $this->device_name = trim(config::byKey('FREEBOX_SERVER_DEVICE_NAME', 'Freebox_OS'));
        $this->track_id = config::byKey('FREEBOX_SERVER_TRACK_ID', 'Freebox_OS');
        $this->app_token = config::byKey('FREEBOX_SERVER_APP_TOKEN', 'Freebox_OS');
        $this->API_version = config::byKey('FREEBOX_API', 'Freebox_OS');
        // Gestion API
        $Config_KEY = config::byKey('FREEBOX_API', 'Freebox_OS');
        if (empty($Config_KEY)) {
            log::add('Freebox_OS', 'debug', '───▶︎ Version API Non Défini Compatible avec la Freebox : ' . $this->API_version);
            $this->API_version = 'v10';
        } else {
            $this->API_version = config::byKey('FREEBOX_API', 'Freebox_OS');
        }
    }

    public function track_id() //Doit correspondre a la donction "auth" de freboxsession.js homebridge freebox
    {
        try {
            $API_version = $this->API_version;
            if ($API_version == null) {
                $API_version = 'v10';
                log::add('Freebox_OS', 'debug', '───▶︎ La version API est nulle mise en place version provisoire : ' . $API_version);
            };
            $_URL = $this->serveur . '/api/' . $API_version . '/login/authorize/';
            $http = new com_http($_URL);
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
            $API_version = $this->API_version;
            if ($API_version == null) {
                $API_version = 'v10';
                log::add('Freebox_OS', 'debug', '───▶︎ La version API est nulle mise en place version provisoire : ' . $API_version);
            };
            $_URL = $this->serveur . '/api/' . $API_version . '/login/authorize/';
            $http = new com_http($_URL . $this->track_id);
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
            $API_version = $this->API_version;
            if ($API_version == null) {
                $API_version = 'v10';
                log::add('Freebox_OS', 'debug', '───▶︎ La version API est nulle mise en place version provisoire : ' . $API_version);
            };
            $_URL = $this->serveur . '/api/' . $API_version . '/login/';
            $http = new com_http($_URL);
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
            $API_version = $this->API_version;
            if ($API_version == null) {
                $API_version = 'v10';
                log::add('Freebox_OS', 'debug', '───▶︎ La version API est nulle mise en place version provisoire : ' . $API_version);
            };
            $_URL = $this->serveur . '/api/' . $API_version . '/login/session/';
            $http = new com_http($_URL);
            $http->setPost(json_encode(array(
                'app_id' => $this->app_id,
                'app_version' =>  $API_version, // Ajout suivant fonction session Free homebridge
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
                log::add('Freebox_OS', 'debug', '[Freebox Etat Session] : KO / ' . $result['success']);
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
            $API_version = $this->API_version;
            if ($API_version == null) {
                $API_version = 'v10';
                log::add('Freebox_OS', 'debug', '───▶︎ La version API est nulle mise en place version provisoire : ' . $API_version);
            };
            $_URL = $this->serveur . '/api/' . $API_version . '/login/session';
            $http = new com_http($_URL);
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

    public function fetch($api_url, $params = array(), $method = 'GET', $log_request = false, $log_result = false)
    {
        try {
            $session_token = cache::byKey('Freebox_OS::SessionToken');
            while ($session_token->getValue('') == '') {
                $session_token = cache::byKey('Freebox_OS::SessionToken');
            }
            $requetURL = '[Freebox Request Connexion] : ' . $method . ' sur la l\'adresse ' . $this->serveur . $api_url . '(' . json_encode($params) . ')';
            if ($log_request  != false) {
                log::add('Freebox_OS', 'debug', $requetURL);
            };
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

            if ($log_result  != false) {

                log::add('Freebox_OS', 'debug', '[Freebox Request Result] : ' . $content);
            }
            if ($errorno !== 0) {
                return '[WARNING] Erreur de connexion cURL vers ' . $this->serveur . $api_url . ': ' . $error;
            } else {
                $result = json_decode($content, true);
                if ($result == null) return false;
                if (isset($result['success']) || isset($result['error_code'])) {
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
                        } else if ($result['error_code'] == 'invalid_api_version') {
                            log::add('Freebox_OS', 'error', 'API NON COMPATIBLE : ' . $result['msg'] . ' - ' . $requetURL);
                            $result = $result['error_code'];
                            return $result;
                        } else if ($result['error_code'] == "invalid_request" || $result['error_code'] == 'ratelimited') {
                            log::add('Freebox_OS', 'error', 'Erreur AUTRE : '  . $result['msg']);
                            return false;
                        } else if ($result['error_code'] == "no_such_vm") {
                            log::add('Freebox_OS', 'error', 'Erreur VM : '  . $result['msg']);
                            return false;
                        }
                    }
                }
                return $result;
            }
        } catch (Exception $e) {
            log::add('Freebox_OS', 'error', '[Freebox Request] : '  . $e->getCode());
        }
    }

    public function close_session()
    {
        log::add('Freebox_OS', 'debug', ' OK  Close Session  ');
        try {
            $Challenge = cache::byKey('Freebox_OS::Challenge');
            if (is_object($Challenge)) {
                $Challenge->remove();
            }
            $session_token = cache::byKey('Freebox_OS::SessionToken');
            if (!is_object($session_token) || $session_token->getValue('') == '') {
                return;
            }
            $API_version = $this->API_version;
            if ($API_version == null) {
                $API_version = 'v10';
                log::add('Freebox_OS', 'debug', '───▶︎ La version API est nulle mise en place version provisoire : ' . $API_version);
            };
            $_URL = $this->serveur . '/api/' . $API_version . '/login/logout/';
            $http = new com_http($_URL);
            $http->setPost(array());
            $json = $http->exec(2, 2);
            log::add('Freebox_OS', 'debug', '[Freebox Close Session] : ' . $json);
            $SessionToken = cache::byKey('Freebox_OS::SessionToken');

            if (is_object($SessionToken)) {
                $SessionToken->remove();
            }
            return $json;
        } catch (Exception $e) {
            log::add('Freebox_OS', 'debug', '[Freebox Close Session] : ' . $e->getCode() . ' ou session déjà fermée');
        }
    }

    public function PortForwarding($id, $fonction = "GET", $active = null, $Mac = null)
    {
        $API_version = $this->API_version;
        $PortForwardingUrl = '/' . 'api/' . $API_version . '/fw/redir/';
        $PortForwarding = $this->fetch($PortForwardingUrl, null, "GET", true, true);
        $id = str_replace("ether-", "", $id);
        $id = strtoupper($id);
        log::add('Freebox_OS', 'debug', '───▶︎ Lecture des Ports l\'adresse Mac : '  . $Mac . ' - FONCTION ' . $fonction . ' - action ' . $active);
        if ($PortForwarding === false) {
            log::add('Freebox_OS', 'debug', '───▶︎ Aucune donnée');
            return false;
        }
        if ($fonction == "GET") {
            $result = array();
            foreach ($PortForwarding['result'] as $value) {
                if ($value['host']['l2ident']['id'] == $Mac) {
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
                };
            }
            return $result;
        } elseif ($fonction == "PUT") {
            if ($active == 1) {
                $this->fetch($PortForwardingUrl . $id, array("enabled" => true), $fonction, true, true);
                return true;
            } elseif ($active == 0) {
                $this->fetch($PortForwardingUrl . $id, array("enabled" => false), $fonction, true, true);
                return true;
            } elseif ($active == 3) {
                $this->fetch($PortForwardingUrl . $id, null, "DELETE", true, true);
                return true;
            }
        }
    }

    public function universal_get($update = 'wifi', $id = null, $boucle = 4, $update_type = 'config', $log_request = true, $log_result = true, $_onlyresult = false)
    {
        $API_version = $this->API_version;
        if ($API_version == null) {
            $API_version = 'v10';
            log::add('Freebox_OS', 'debug', '───▶︎ La version API est nulle mise en place version provisoire : ' . $API_version);
        };
        $config_log = null;
        $fonction = "GET";
        $Parameter = null;
        if ($id != null) {
            $id = '/' . $id;
        } else if ($id == null && $update == 'tiles') {
            $id = '/all';
        }
        switch ($update) {
            case 'connexion':
                $config = 'api/' . $API_version . '/connection/' . $update_type;
                $config_log = 'Traitement de la Mise à jour de ' . $update_type . ' avec la valeur';
                break;
            case 'notification_ID':
                $config = 'api/' . $API_version . '/notif/targets' . $id;
                $config_log = 'Etat des notifications';
                break;
            case 'parental':
                $config = 'api/' . $API_version . '/network_control' . $id;
                $config_log = 'Etat Contrôle Parental';
                break;
            case 'parentalprofile':
                $config = 'api/' . $API_version . '/profile';
                break;
            case 'player':
                $config = 'api/' . $API_version . '/player';
                break;
            case 'player_ID':
                $config = 'api/' . $API_version . '/player' . $id . '/api/v6/status';
                $config_log = 'Traitement de la Mise à jour de l\'id ';
                break;
            case 'network':
                $config = 'api/' . $API_version . '/' . $update_type;
                break;
            case 'universalAPI':
                if ($update_type == 'api_version') {
                    $config =   $update_type;
                } else {
                    $config = 'api/' . $API_version . '/' . $update_type . $id;
                }
                if ($update_type != 'vm/') {
                    $config_log = 'Traitement de la Mise à jour de l\'id ';
                }
                break;
            case 'network_ID':
                $config = 'api/' . $API_version . '/lan/browser/' . $update_type  . $id;
                break;
            case 'system':
                $config = 'api/' . $API_version . '/system';
                break;
            case 'switch':
                $config = 'api/' . $API_version . '/switch/status';
                break;
            case 'tiles':
                $config = 'api/' . $API_version . '/home/tileset' . $id;
                $config_log = 'Traitement de la Mise à jour de l\'id ';
                break;
            case 'WebSocket':
                $config = 'api/' . $API_version . '/ws/event';
                $config_log = 'Traitement de la Mise à jour de WebSocket';
                $Parameter = array(
                    "action" => 'notification',
                    "success" => true,
                    "source" => 'vm',
                    "event" => 'VmStateChange',
                );
                break;
            case 'PortForwarding':
                $config = '/api/' . $API_version . '/fw/redir/';
                $config_log = 'Redirection de port';
                break;
            case 'upload':
                $config = 'api/' . $API_version . '/ws/';
                $config_log = 'Upload Progress tracking API';
                break;
        }
        $result = $this->fetch('/' . $config, $Parameter, $fonction, $log_request, $log_result);
        if ($result == 'auth_required') {
            $result = $this->fetch('/' . $config, $Parameter, $fonction);
        }
        if ($result === 'invalid_api_version') {
            $result = 'invalid_api_version';
            return $result;
        }
        if ($result === false) {
            return false;
        }
        if (isset($result['success'])) {

            $value = 0;
            if ($update_type == 'freeplug') {
                $update = 'freeplug';
            }
            switch ($update) {
                case 'connexion':
                    return $result['result'];
                    break;
                case 'notification':
                case 'freeplug':
                    //case 'wifi':
                    return $result;
                    break;
                case 'system':
                    if ($boucle != null) {
                        if (isset($result['result'][$boucle])) {
                            return $result['result'][$boucle];
                        } else {
                            $result = null;
                            return $result;
                        }
                    } else {
                        return $result['result'];
                    }
                    break;
                default:
                    if ($config_log != null && $id != null && $id != '/all') {
                        if ($log_request == true) {
                            log::add('Freebox_OS', 'debug', '───▶︎ ' . $config_log . ' : ' . $id);
                        }
                    }

                    if (isset($result['result'])) {
                        if ($_onlyresult == false) {
                            return $result['result'];
                        } else {
                            return $result;
                        }
                    } else {
                        $result = null;
                        return $result;
                    }
                    break;
            }


            return $value;
        } else {
            if ($update == "network_ping" || $update == "network_ID" || $update_type == "api_version") {
                return $result;
            } else if ($update_type == 'lte/config' || $update == 'parental') {
                return $result['msg'];
            } else {
                return false;
            }
        }
    }
    public function downloads_put($Etat)
    {
        $API_version = $this->API_version;
        $DownloadUrl = '/api/' . $API_version . '/downloads/';
        $result = $this->fetch($DownloadUrl);

        if ($result == 'auth_required') {
            $result = $this->fetch($DownloadUrl);
        }
        if ($result === false)
            return false;
        $nbDL = count($result['result']);
        for ($i = 0; $i < $nbDL; ++$i) {
            if ($Etat == 0)
                $downloads = $this->fetch($DownloadUrl  . $result['result'][$i]['id'], array("status" => "stopped"), "PUT");
            if ($Etat == 1)
                $downloads = $this->fetch($DownloadUrl . $result['result'][$i]['id'], array("status" => "downloading"), "PUT");
        }
        if ($downloads === false)
            return false;
        if ($downloads['success'])
            return $downloads['success'];
        else
            return false;
    }
    public function universal_put($parametre, $update = 'wifi', $id = null, $nodeId = null, $_options = null, $_status_cmd = null, $_options_2 = null)
    {
        $API_version = $this->API_version;
        $fonction = "PUT";
        $config_log = null;
        if ($id != null) {
            $id = $id . '/';
        }
        switch ($update) {
            case 'notification_ID':
                $config = 'api/' . $API_version . '/notif/targets/' . $id;
                if ($_options == 'DELETE') {
                    $fonction = $_options;
                }
                break;
            case 'parental':
                $config_log = 'Mise à jour du : Contrôle Parental';
                $cmd_config = 'parental';
                $config = "/api/" . $API_version . "/network_control/" . $id;
                $jsontestprofile = $this->fetch($config);
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
                $config = "api/" . $API_version . "/network_control/" . $id;
                break;
            case 'player_ID_ctrl':
                $config = 'api/' . $API_version . '/player' . $id . '/api/v6/control/mediactrl';
                $config_log = 'Traitement de la Mise à jour de l\'id ';
                $cmd_config = 'name';
                $fonction = "POST";
                break;
            case 'player_ID_open':
                $config = 'api/' . $API_version . '/player' . $id . '/api/v6/control/open';
                $config_log = 'Traitement de la Mise à jour de l\'id ';
                $cmd_config = 'url';
                $fonction = "POST";
                break;
            case 'reboot':
                $config = 'api/' . $API_version . '/system/reboot';
                $fonction = "POST";
                break;
            case 'universalAPI':
                $config = 'api/' . $API_version . '/' . $_options_2;
                $cmd_config = $_options;
                break;
            case 'universal_put':
                if ($_status_cmd == "DELETE" || $_status_cmd == "PUT" || $_status_cmd == "device") {
                    $config = 'api/' . $API_version . '/' . $_options  . $id;
                    $fonction = $_status_cmd;
                } else {
                    log::add('Freebox_OS', 'debug', '───▶︎ Type de requête : ' . $_options);
                    $config = 'api/' . $API_version . '/' . $_options;
                    $fonction = "POST";
                }
                log::add('Freebox_OS', 'debug', '───▶︎ Type de requête : ' . $fonction);
                break;
            case 'VM':
                $config = 'api/' . $API_version . '/vm/' . $id  . '/' . $_options_2;
                $fonction = "POST";
                break;
            case 'wifi':
                $config = 'api/' . $API_version . '/wifi/' . $_options;
                if ($_options == 'planning') {
                    $cmd_config = 'use_planning';
                } else if ($_options == 'wps/start') {
                    $fonction = "POST";
                    $cmd_config = 'bssid';
                } else if ($_options == 'wps/stop') {
                    $fonction = "POST";
                    $cmd_config = 'session_id';
                } else if ($_options == 'mac_filter') {
                    log::add('Freebox_OS', 'debug', '───▶︎ Fonction : ' . $_options_2['function']);
                    $fonction = $_options_2['function'];
                    if ($fonction != 'POST') {
                        $id = $_options_2['mac_address'] . '-' . $_options_2['filter'];
                        $parametre = null;
                    } else {
                        $_filter = $_options_2['filter'];
                        $mac_adress = $_options_2['mac_address'];
                        $comment = $_options_2['comment'];
                        $id = null;
                        $parametre = array("mac" => $mac_adress, "type" => $_filter, "comment" => $comment);
                    }
                    log::add('Freebox_OS', 'debug', '───▶︎ Fonction 2 : ' . $fonction);
                } else if ($_options == 'config' && $_options_2 == 'mac_filter_state') {
                    $cmd_config = 'mac_filter_state';
                } else {
                    $cmd_config = 'enabled';
                }
                if ($_options == 'planning' || $_options == 'wifi') {
                    $config_log = 'Mise à jour de : Etat du Wifi ' . $_options;
                } else {
                    $config_log = null;
                }
                break;
            case 'set_tiles':
                //log::add('Freebox_OS', 'debug', '───▶︎ Info nodeid : ' . $nodeId . ' -- Id: ' . $id . ' -- Paramètre : ' . $parametre);
                $config = 'api/' . $API_version . '/home/endpoints/';
                $cmd_config = 'enabled';
                $config_log = 'Mise à jour de : ';
                break;
        }
        if ($parametre['value_type'] === 'bool' && $parametre['value'] === 1) {
            $parametre['value'] = 'true';
        } elseif ($parametre['value_type'] === 'bool' && $parametre['value'] === 0) {
            $parametre['value'] = 'false';
        } elseif ($parametre == '0') {
            $parametre = false;
        } elseif ($parametre == '1') {
            if ($_options != 'wps/stop') {
                $parametre = true;
            }
        }
        if ($update == 'parental' || $update == 'VM') {
            $return = $this->fetch('/' . $config . '', $parametre, $fonction, true, true);
        } else if ($update == 'universal_put') {
            $return = $this->fetch('/' . $config,  $_options_2, $fonction, true, true);
            return $return['success'];
        } else if ($update == 'set_tiles') {
            $return = $this->fetch('/' . $config . $nodeId . '/' . $id, $parametre, "PUT", true, true);
        } else if ($_options == 'mac_filter') {
            $return = $this->fetch('/' . $config  . '/' . $id, $parametre, $fonction, true, true);
        } else if ($update == 'phone') {
            $return = $this->fetch('/' . $config . '/', null, $fonction, true, true);
        } else {
            if ($config_log != null) {
                log::add('Freebox_OS', 'debug', '───▶︎ ' . $config_log . ' avec la valeur : ' . $parametre);
            }
            if ($cmd_config != null) {
                $requet = array($cmd_config => $parametre);
            } else {
                $requet = null;
            }
            $return = $this->fetch('/' . $config . '/', $requet, $fonction, true, true);

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
        $Free_API = new Free_API();
        $result = $Free_API->universal_get('universalAPI', null, null, 'call/log/', true, true, true);
        $retourFbx = array('missed' => 0, 'listmissed' => "", 'accepted' => 0, 'listaccepted' => "", 'outgoing' => 0, 'listoutgoing' => "");
        if ($result === false) {
            return false;
        }
        if (isset($result['success'])) {
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
                            if ($result['result'][$k]['name'] == null) {
                                $name = $result['result'][$k]['number'];
                            } else {
                                $name = $result['result'][$k]['name'];
                            }

                            if ($result['result'][$k]['type'] == 'missed') {
                                $cptAppel_missed++;
                                if ($listNumber_missed == NULL) {
                                    $newligne = null;
                                } else {
                                    $newligne = '<br>';
                                }
                                $listNumber_missed .= $newligne . $name . " à " . $time . " de " . $this->fmt_duree($result['result'][$k]['duration']);
                            }
                            if ($result['result'][$k]['type'] == 'accepted') {
                                $cptAppel_accepted++;
                                if ($listNumber_accepted != NULL) {
                                    $newligne = null;
                                } else {
                                    $newligne = '<br>';
                                }
                                $listNumber_accepted .= $newligne . $name . " à " . $time . " de " . $this->fmt_duree($result['result'][$k]['duration']);
                            }
                            if ($result['result'][$k]['type'] == 'outgoing') {
                                $cptAppel_outgoing++;
                                if ($listNumber_outgoing != NULL) {
                                    $newligne = null;
                                } else {
                                    $newligne = '<br>';
                                }
                                $listNumber_outgoing .= $newligne . $name . " à " . $time . " de " . $this->fmt_duree($result['result'][$k]['duration']);
                            }
                        }
                    }
                    $retourFbx = array('missed' => $cptAppel_missed, 'listmissed' => $listNumber_missed, 'accepted' => $cptAppel_accepted, 'listaccepted' => $listNumber_accepted, 'outgoing' => $cptAppel_outgoing, 'listoutgoing' => $listNumber_outgoing);
                }
                return $retourFbx;
            } else {
                return false;
            }
        } else {
            log::add('Freebox_OS', 'debug', ':fg-warning: ───▶︎ ' . 'AUCUN APPEL' .  ':/fg:');
            return $retourFbx;
        }
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
        $API_version = $this->API_version;
        $whitelist = null;
        $blacklist = null;
        $result = $this->fetch('/api/' . $API_version . '/wifi/mac_filter/', null, null, true, true);
        if ($result === false)
            return false;
        if ($result['success']) {
            if (isset($result['result'])) {
                $nb_mac = count($result['result']);

                for ($k = 0; $k < $nb_mac; $k++) {
                    $name = $result['result'][$k]['hostname'];
                    if ($result['result'][$k]['type'] == 'whitelist') {
                        if ($whitelist == null) {
                            $whitelist  = $name . " - " . $result['result'][$k]['mac'] . " - " . $result['result'][$k]['comment'];
                        } else {
                            $whitelist  .= '<br>' . $name . " - " . $result['result'][$k]['mac'] . " - " . $result['result'][$k]['comment'];
                        }
                    }
                    if ($result['result'][$k]['type'] == 'blacklist') {
                        if ($blacklist == null) {
                            $blacklist .= $name . " - " . $result['result'][$k]['mac'] . " - " . $result['result'][$k]['comment'];
                        } else {
                            $blacklist .= '<br>' . $name . " - " . $result['result'][$k]['mac'] . " - " . $result['result'][$k]['comment'];
                        }
                    }
                }
                $return = array('blacklist' => $blacklist, 'whitelist' => $whitelist);
            } else {
                $return = array('blacklist' => 'vide', 'whitelist' => 'vide');
            }
            return $return;
        } else {
            return false;
        }
    }
}
