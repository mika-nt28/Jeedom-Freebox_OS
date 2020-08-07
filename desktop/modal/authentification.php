<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../core/php/Freebox_OS.inc.php';
include_file('core', 'authentification', 'php');

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
config::save('FREEBOX_SERVER_APP_NAME', config::byKey('product_name'), 'Freebox_OS');
config::save('FREEBOX_SERVER_DEVICE_NAME', config::byKey('product_name'), 'Freebox_OS');

?>
<div id="div_AlertJeeasyInclude"></div>
<div class="row row-overflow">
  <div class="col-lg-2">
    <div class="bs-sidebar">
      <ul class="nav nav-list bs-sidenav">
        <li class="cursor li_Freebox_OS_Summary active" data-href="home"><a><i class="fab fa-ello"></i> {{Accueil}}</a></li>
        <li class="cursor li_Freebox_OS_Summary" data-href="setting"><a><i class="fas fa-cogs"></i> {{Réglages}}</a></li>
        <li class="cursor li_Freebox_OS_Summary" data-href="authentification"><a><i class="fas fa-rss"></i> {{Authentification}}</a></li>
        <li class="cursor li_Freebox_OS_Summary" data-href="rights"><a><i class="fas fa-balance-scale-right"></i> {{Droits}}</a></li>
        <li class="cursor li_Freebox_OS_Summary" data-href="scan"><a><i class="fas fa-search-plus"></i> {{Scan des équipements}}</a></li>
        <li class="cursor li_Freebox_OS_Summary" data-href="end"><a><i class="fas fa-check"></i> {{Fin}}</a></li>
      </ul>
    </div>
  </div>

  <div class="col-lg-10" id="div_jeeasyIncludeDisplay">
    <a class="btn btn-sm btn-success pull-right bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a>
    <a class="btn btn-sm btn-default pull-right bt_Freebox_OS_Previous"><i class="fas fa-angle-double-left"></i> {{Précédent}}</a>
    <br /><br />
    <div class="Freebox_OS_Display home">
      <center><i class="fab fa-ello" style="font-size: 10em;"></i></center>
      <br />
      <center>
        <div class="alert alert-info">{{Bienvenue, nous allons commencer l'authentification sur la Freebox}}</div>
      </center>
      <center>{{Cliquez sur suivant pour commencer}}</center>
      <br />
      <center><a class="btn btn-sm btn-success bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a></center>
    </div>

    <div class="Freebox_OS_Display setting" style="display:none;">
      <center><i class="fas fa-cogs" style="font-size: 10em;"></i></center>
      <br />
      <center>
        <div class="alert alert-info">{{C'est partie, lançons nous. Pour commencer nous allons valider les réglages}}</div>
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-md-3 control-label">{{IP Freebox}}</label>
              <div class="col-md-3">
                <input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_IP" />
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-3 control-label">{{Id de l'application Freebox serveur}}</label>
              <div class="col-md-3">
                <input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_APP_ID" />
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-3 control-label">{{Nom de l'application Freebox serveur}}</label>
              <div class="col-md-3">
                <input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_APP_NAME" />
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-3 control-label">{{Version de l'application Freebox serveur}}</label>
              <div class="col-md-3">
                <input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_APP_VERSION" />
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-3 control-label">{{Nom de l'équipement connecté}}</label>
              <div class="col-md-3">
                <input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_DEVICE_NAME" />
              </div>
            </div>
            </br>
            <div class="form-group">
              <label class="col-lg-3 control-label">{{Ajouter automatiquement les équipements détectés dans :}}</label>
              <div class="col-lg-3">
                <select id="sel_object" class="configKey form-control" data-l1key="defaultParentObject">
                  <option value="">{{Aucune}}</option>
                  <?php
                  foreach (jeeObject::all() as $object) {
                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </fieldset>
        </form>

      </center>
      <center>
        <div class="alert alert-info">{{Puis une fois validé, cliquez simplement sur le bouton suivant ci-dessous}}</div>
      </center>
      <br />
      <center><a class="btn btn-sm btn-success bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a></center>
    </div>

    <div class="Freebox_OS_Display authentification" style="display:none;">
      <img class="img-responsive center-block" src="plugins/Freebox_OS/core/images/authentification/authentification.jpg" height="600" width="600" />
      <br />

      <br />
      <center>
        <h3 class="textFreebox">{{}}</h3>
      </center>
      <center>
        <div class="alert alert-info Freebox_OK">{{Cliquez simplement sur le bouton suivant ci-dessous}}</div>
      </center>
      <br />
      <center><a class="btn btn-sm btn-success bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a></center>
    </div>

    <div class="Freebox_OS_Display rights" style="display:none;">
      <center><i class="fas fa-balance-scale-right" style="font-size: 5em;"></i></center>
      <br />
      <img class="img-responsive center-block" src="plugins/Freebox_OS/core/images/authentification/modification_droit.png" height="500" width="500" />
      <br />
      <center>
        <div class="alert alert-info">{{Se connecter à l’interface de la Freebox puis ouvrir les paramètres de la Freebox}}
          <br>
          {{Ensuite Ouvrir la gestion des accès de la Freebox (ce réglage se trouve dans le mode avancé)}}
          <br>
          {{Cliquer sur l’onglet Applications et dans la liste, choisir l’Application déclarée lors de l’installation du Plugin (par défaut : Jeedom Core)}}
          <br>
          {{Cocher les cases comme ci-dessus et cliquer sur les boutons OK}}
        </div>
      </center>
      <center>
        <div class="alert alert-info">{{Puis une fois les droits modifiés, cliquez simplement sur le bouton suivant ci-dessous}}</div>
      </center>
      <br />
      <center><a class="btn btn-sm btn-success bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a></center>
    </div>

    <div class="Freebox_OS_Display scan" style="display:none;">
      <center><i class="fas fa-search-plus" style="font-size: 10em;"></i></center>
      <br />
      <center>
        <div class="alert alert-info">{{Cette partie vous permet de rechercher les différents équipements sur votre freebox}}</div>
      </center>
      <br />
      <div>
        <div id="colonne1">
          <div class="thumbnail" style="box-shadow: 1px 1px 12px #872428; height: 310px;"><img src="plugins/Freebox_OS/core/images/system.png" alt="" style="border-radius:5px 5px 0 0; height: 100px;WIDTH: 100px">
            <div class="caption">
              <h4>{{Mes Equipements}}</h4>
              <p></p>
              <p class="text-center"><a class="btn btn-info bt_eqlogic_standard">{{Scan des équipements standards}} <i class="fas fa-bullseye"></i></a></p>
              <p></p>
              <p>{{Cette partie vous permet de scanner les équipements système de la Freebox}}</p>
            </div>
          </div>
        </div>
        <div id="colonne2">
          <div class="thumbnail" style="box-shadow: 1px 1px 12px #872428; height: 310px;"><img src="plugins/Freebox_OS/core/images/parental.png" alt="" style="border-radius:5px 5px 0 0; height: 100px;WIDTH: 100px">
            <div class="caption">
              <h4>{{Mes Contrôles parentaux}}</h4>
              <p></p>
              <p class="text-center"><a class="btn btn-info bt_eqlogic_control_parental">{{Scan des Contrôles parentaux}} <i class="fas fa-user-shield"></i></a></p>
              <p></p>
              <p>{{Cette partie vous permet de scanner les contrôles parentaux présents dans la Freebox}}</p>
            </div>
          </div>
        </div>
        <div id="centre">
          <div class="thumbnail" style="box-shadow: 1px 1px 12px #872428; height: 310px;"><img src="plugins/Freebox_OS/core/images/homeadapters.png" alt="" style="border-radius:5px 5px 0 0; height: 100px;WIDTH: 100px">
            <div class="caption">
              <h4>{{Mes Equipements Home - Tiles}}</h4>
              <p></p>
              <p class="text-center"><a class="btn btn-info bt_eqlogic_tiles">{{Scan des Tiles}} <i class="fas fa-search"></i></a></p>
              <p></p>
              <p>{{Cette partie vous permet de scanner les équipements de type Home et Tiles (Maison). Uniquement sur la Freebox Delta}}</p>
            </div>
          </div>
        </div>
      </div>
      <br />
      <div>
        <center>
          <div class="alert alert-info">{{Une fois les scans effectués, cliquez simplement sur le bouton suivant}}</div>
        </center>
      </div>
    </div>

    <div class="Freebox_OS_Display end" style="display:none;">
      <center><i class="fas fa-check" style="font-size: 10em;"></i></center>
      <br />
      <img class="img-responsive center-block" src="core/img/logo-jeedom-freebox-grand-nom-couleur.png" height="500" width="500" />
      <center>
        <br />
        <div class="alert alert-success">{{Bravo !!!}}</div>
      </center>
      <center>
        <div class="alert alert-info Freebox_OK">{{Authentification réussi}}</div>
      </center>

    </div>
    <br /><br />
    <div class="col-md-12 text-center">
      <div id="contenuTextSpan" class="progress">
        <div class="progress-bar progress-bar-striped progress-bar-animated active" id="div_progressbar" role="progressbar" style="width: 0; height:20px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
      </div>
    </div>

  </div>
</div>
<script type="text/javascript">
  progress(20);
  eqLogic_id = null;
  $('.bt_Freebox_OS_Next').off('click').on('click', function() {
    $('.li_Freebox_OS_Summary.active').next().click();
    if ($(this).attr('data-href') == 'rights') {
      progress(50);
    } else if ($(this).attr('data-href') == 'scan') {
      progress(60);
    }
  });

  $('.bt_eqlogic_standard').on('click', function() {
    SearchArchi();
    progress(70);
  });

  $('.bt_eqlogic_tiles').on('click', function() {
    SearchTile();
    progress(80);
  });
  $('.bt_eqlogic_control_parental').on('click', function() {
    SearchParental();
    progress(90);
  });

  $('.bt_Freebox_OS_Previous').off('click').on('click', function() {
    $('.li_Freebox_OS_Summary.active').prev().click();
    if ($(this).attr('data-href') == 'rights') {
      progress(50);
    } else if ($(this).attr('data-href') == 'scan') {
      progress(60);
    } else {
      progress(100);
    }
  });
  $('.li_Freebox_OS_Summary').off('click').on('click', function() {
    $('.li_Freebox_OS_Summary.active').removeClass('active');
    $(this).addClass('active');
    $('.Freebox_OS_Display').hide();
    $('.Freebox_OS_Display.' + $(this).attr('data-href')).show();
    if ($(this).attr('data-href') == 'authentification') {
      $('.Freebox_OK').hide();
      $('.bt_Freebox_OS_Next').hide();
      $('.bt_Freebox_OS_Previous').hide();
      autorisationFreebox()
    } else if ($(this).attr('data-href') == 'rights') {
      progress(50);
    } else if ($(this).attr('data-href') == 'scan') {
      progress(60);
    } else if ($(this).attr('data-href') == 'end') {
      progress(100);
      //good();
    }
    $(this).attr('data-display', 1);
  });


  function autorisationFreebox() {
    $.ajax({
      type: "POST",
      url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
      data: {
        action: "connect",
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function(data) {
        if (!data.result.success) {
          $('#div_alert').showAlert({
            message: data.result.msg,
            level: 'danger'
          });
          if (data.result.error_code == "new_apps_denied")
            $('#div_alert').append(".<br>Pour activer l'option, il faut se rendre dans : mafreebox.freebox.fr -> Paramètres de la Freebox -> Gestion des accès <br> Et cocher : <b>Permettre les nouvelles demandes d'associations</b>  -> Appliquer<br>De nouveau, cliquez sur <b>Etape 1</b>");
          return;
        } else {
          sendToBdd(data.result);
          $('.textFreebox').text('{{Merci d\'appuyer sur le bouton V de votre Freebox, afin de confirmer l\'autorisation d\'accès à votre Freebox.}}');
          $('.img-freeboxOS').attr('src', 'plugins/Freebox_OS/core/images/authentification/authentification.jpg');
          progress(40);
          setTimeout(AskTrackAuthorization, 3000);
        }
      }
    });
  }

  function SearchArchi() {
    $.ajax({
      type: "POST",
      url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
      data: {
        action: "SearchArchi",
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function(data) {

      }
    });
  }

  function SearchTile() {
    $.ajax({
      type: "POST",
      url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
      data: {
        action: "SearchTile",
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function(data) {

      }
    });
  }

  function SearchParental() {
    $.ajax({
      type: "POST",
      url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
      data: {
        action: "SearchParental",
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function(data) {

      }
    });
  }

  function sendToBdd(jsonParser) {
    var fbx_app_token = jsonParser.result.app_token;
    var fbx_track_id = jsonParser.result.track_id;
    $.ajax({
      type: "POST",
      url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
      data: {
        action: "sendToBdd",
        app_token: fbx_app_token,
        track_id: fbx_track_id
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function(data) {
        if (!data) {
          $('#div_alert').showAlert({
            message: data,
            level: 'danger'
          });
          return;
        }
      }
    });
  }

  function AskTrackAuthorization() {
    $('.textFreebox').hide();
    $('.bt_Freebox_OS_Next').hide();
    $('.bt_Freebox_OS_Previous').hide();
    $('.Freebox_OK').hide();
    $('.Freebox_OK_NEXT').hide();
    progress(40);
    $.ajax({
      type: "POST",
      url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
      data: {
        action: "ask_track_authorization",
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function(data) {
        if (!data.result.success) {
          $('#div_alert').showAlert({
            message: data.result.msg,
            level: 'danger'
          });
        } else {
          $('.textFreebox').show();
          $('.bt_Freebox_OS_Next').show();
          $('.bt_Freebox_OS_Previous').show();
          switch (data.result.result.status) {

            case "unknown":
              $('.textFreebox').text('{{Vous n\'avez pas validé à temps, il faut relancer l\'association. Merci}}');
              Good();
              progress(-1);
              break;
            case "pending":
              setTimeout(AskTrackAuthorization, 3000);
              break;
            case "timeout":
              $('.textFreebox').text('{{Vous n\'avez pas validé à temps, il faut relancer l\'association. Merci}}');
              Good();
              progress(-1);
              break;
            case "granted":
              $('.textFreebox').text('{{Félicitation votre Freebox est maintenant reliée à Jeedom.}}');
              $('.Freebox_OK').show();
              $('.Freebox_OK_NEXT').show();
              $('.Freebox_OS_Display.' + $(this).attr('rights')).show();
              progress(60);
              break;

            case "denied":
              $('.textFreebox').text('{{Vous avez refusé, il faut relancer l\'association. Merci}}');
              progress(-1);
              Good();
              break;
            default:
              $('#div_alert').showAlert({
                message: "REST OK : track_authorization -> Error 4 : Inconnue",
                level: 'danger'
              });
              Good();
              break;
          }
        }
      }
    });
  }

  function Good() {
    $('.bt_Freebox_OS_Previous').hide();
    $('.bt_Freebox_OS_NEXT').hide();
    $('.alert-info Freebox_OK').text('{{Authentification réussi}}');
  }

  function progress(ProgressPourcent) {
    if (ProgressPourcent == -1) {
      $('#div_progressbar').removeClass('progress-bar-success progress-bar-info progress-bar-warning');
      $('#div_progressbar').addClass('active progress-bar-danger');
      $('#div_progressbar').width('100%');
      $('#div_progressbar').attr('aria-valuenow', 100);
      $('#div_progressbar').html('N/A');
      return;
    }
    if (ProgressPourcent == 100) {
      $('#div_progressbar').removeClass('active progress-bar-info progress-bar-danger progress-bar-warning');
      $('#div_progressbar').addClass('progress-bar-success');
      $('#div_progressbar').width(ProgressPourcent + '%');
      $('#div_progressbar').attr('aria-valuenow', ProgressPourcent);
      $('#div_progressbar').html('FIN');
      return;
    }
    $('#div_progressbar').removeClass('active progress-bar-info progress-bar-danger progress-bar-warning');
    $('#div_progressbar').addClass('progress-bar-success');
    $('#div_progressbar').width(ProgressPourcent + '%');
    $('#div_progressbar').attr('aria-valuenow', ProgressPourcent);
    $('#div_progressbar').html(ProgressPourcent + '%');
  }
</script>
<style>
  div#colonne1 {
    float: left;
    width: 300px;
    margin-right: 120px;

  }

  div#colonne2 {
    float: right;
    width: 300px;
    margin-left: 120px;
    margin-right: 30px;

  }

  div#centre {
    width: 300px;
    overflow: hidden;

  }
</style>