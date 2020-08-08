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

require_once __DIR__ . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../core/php/Freebox_OS.inc.php';

include_file('core', 'authentification', 'php');

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
config::save('FREEBOX_SERVER_APP_NAME', config::byKey('product_name'), 'Freebox_OS');
config::save('FREEBOX_SERVER_DEVICE_NAME', config::byKey('product_name'), 'Freebox_OS');

?>
<div id="div_Alert_Freebox_Include"></div>
<div class="row row-overflow">
    <div class="col-lg-2">
        <div class="bs-sidebar">
            <ul class="nav nav-list bs-sidenav">
                <li class="cursor li_Freebox_OS_Summary active" data-href="home"><a><i class="fab fa-ello"></i>
                        {{Accueil}}</a></li>
                <li class="cursor li_Freebox_OS_Summary" data-href="setting"><a><i class="fas fa-cogs"></i> {{Réglages}}</a>
                </li>
                <li class="cursor li_Freebox_OS_Summary" data-href="authentification"><a><i class="fas fa-rss"></i>
                        {{Authentification}}</a></li>
                <li class="cursor li_Freebox_OS_Summary" data-href="rights"><a><i class="fas fa-balance-scale-right"></i> {{Droits}}</a></li>
                <li class="cursor li_Freebox_OS_Summary" data-href="scan"><a><i class="fas fa-search-plus"></i> {{Scan
                        des équipements}}</a></li>
                <li class="cursor li_Freebox_OS_Summary" data-href="end"><a><i class="fas fa-check"></i> {{Fin}}</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-lg-10" id="div_Freebox_IncludeDisplay">
        <div class="col-md-12 text-center">
            <div id="contenuTextSpan" class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated active" id="div_progressbar" role="progressbar" style="width: 0; height:20px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%
                </div>
            </div>
        </div>
        <div class="Freebox_OS_Display home">
            <a class="btn btn-sm btn-success pull-right bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a>
            <br /><br />
            <center><i class="fab fa-ello" style="font-size: 10em;"></i></center>
            <br />
            <center>
                <div class="alert alert-info">{{Bienvenue, nous allons commencer l'authentification sur la Freebox}}
                </div>
            </center>
            <center>{{Cliquez sur suivant pour commencer}}</center>
        </div>

        <div class="Freebox_OS_Display setting" style="display:none;">
            <a class="btn btn-sm btn-success pull-right bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a>
            <a class="btn btn-sm btn-default pull-right bt_Freebox_OS_Previous"><i class="fas fa-angle-double-left"></i>
                {{Précédent}}</a>
            <a class="btn btn-sm btn-success pull-right bt_Freebox_OS_Save"><i class="fas fa-save"></i> {{Sauvegarder}}</a>
            <br /><br />
            <center><i class="fas fa-cogs" style="font-size: 10em;"></i></center>
            <br />
            <center>
                <div class="alert alert-info">{{C'est partie, lançons nous. Pour commencer nous allons valider les
                    réglages}}
                </div>
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-md-5 control-label">{{IP Freebox}}</label>
                            <div class="col-md-4">
                                <input id="imput_freeboxIP" type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_IP" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-5 control-label">{{Version de l'application Freebox serveur}}</label>
                            <div class="col-md-4">
                                <input id="imput_freeAppVersion" type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_APP_VERSION" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-5 control-label">{{Ajouter automatiquement les équipements détectés
                                dans :}}</label>
                            <div class="col-md-4">
                                <select id="sel_catego" class="configKey form-control" data-l1key="defaultParentObject">
                                    <option value="">{{Aucune}}</option>
                                    <?php
                                    foreach (jeeObject::all() as $object) {
                                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!--<div class="form-group">
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
                          div class="form-group">
                            <label class="col-md-3 control-label">{{Nom de l'équipement connecté}}</label>
                            <div class="col-md-3">
                              <input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_DEVICE_NAME" />
                            </div>
                          </div>-->
                        <br />
                    </fieldset>
                </form>

            </center>
            <center>
                <div class="alert alert-info">{{Puis une fois validé, cliquez sur le bouton Sauvegarder}}</div>
            </center>
        </div>

        <div class="Freebox_OS_Display authentification" style="display:none;">
            <a class="btn btn-sm btn-success pull-right bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a>
            <a class="btn btn-sm btn-default pull-right bt_Freebox_OS_Previous"><i class="fas fa-angle-double-left"></i>
                {{Précédent}}</a>
            <br /><br />
            <img class="img-responsive center-block" src="plugins/Freebox_OS/core/images/authentification/authentification.jpg" height="600" width="600" />
            <br />

            <br />
            <center>
                <h3 class="textFreebox">{{}}</h3>
            </center>
            <center>
                <div class="alert alert-info Freebox_Autorisation">{{Si votre box n'est pas encore connectée, cliquez sur le bouton si dessous, sinon cliquez sur suivant}}
                </div>
            </center>
            <br />
            <center>
                <a class="btn btn-sm btn-warning bt_Freebox_Autorisation">{{Lancement authentification}} <i class="fas fa-exclamation-circle"></i></a>
            </center>
        </div>

        <div class="Freebox_OS_Display rights" style="display:none;">
            <a class="btn btn-sm btn-success pull-right bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a>
            <a class="btn btn-sm btn-default pull-right bt_Freebox_OS_Previous"><i class="fas fa-angle-double-left"></i> {{Précédent}}</a>
            <br /><br />
            <center><i class="fas fa-balance-scale-right" style="font-size: 5em;"></i></center>
            <br />
            <img class="img-responsive center-block" src="plugins/Freebox_OS/core/images/authentification/modification_droit.png" height="500" width="500" />
            <br />
            <center>
                <div class="alert alert-info">{{Se connecter à l’interface de la Freebox puis ouvrir les paramètres de
                    la Freebox}}
                    <br>
                    {{Ensuite Ouvrir la gestion des accès de la Freebox (ce réglage se trouve dans le mode avancé)}}
                    <br>
                    {{Cliquer sur l’onglet Applications et dans la liste, choisir l’Application déclarée lors de
                    l’installation du Plugin (par défaut : Jeedom Core)}}
                    <br>
                    {{Cocher les cases comme ci-dessus et cliquer sur les boutons OK}}
                </div>
            </center>
            <center>
                <div class="alert alert-info">{{Puis une fois les droits modifiés, cliquez simplement sur le bouton
                    suivant ci-dessous}}
                </div>
            </center>
            <table id="table_packages" class="table table-condensed">
                <thead>
                    <tr>
                        <th style="width: 120px">Nom</th>
                        <th style="width: 70px">Status</th>
                        <th>Description</th>
                        <th style="width: 120px">Nom</th>
                        <th style="width: 70px">Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Calls</td>
                        <td id="calls" class="alert-danger">NOK</td>
                        <td>Permission Appel</td>
                        <td>Player</td>
                        <td id="player" class="alert-danger">NOK</td>
                        <td>Permission Player</td>
                    </tr>
                    <tr>
                        <td>Camera</td>
                        <td id="camera" class="alert-danger">NOK</td>
                        <td>Permission Cammera</td>
                        <td>Profile</td>
                        <td id="profile" class="alert-danger">NOK</td>
                        <td>Permission Profile</td>
                    </tr>
                    <tr>
                        <td>Contacts</td>
                        <td id="contacts" class="alert-danger">NOK</td>
                        <td>Permission Contacts</td>
                        <td>PVR</td>
                        <td id="pvr" class="alert-danger">NOK</td>
                        <td>Permission PVR</td>
                    </tr>
                    <tr>
                        <td>Download</td>
                        <td id="downloader" class="alert-danger">NOK</td>
                        <td>Permission Download</td>
                        <td>Settings</td>
                        <td id="settings" class="alert-danger">NOK</td>
                        <td>Permission Setting</td>
                    </tr>
                    <tr>
                        <td>Explorer</td>
                        <td id="explorer" class="alert-danger">NOK</td>
                        <td>Permission Explorer</td>
                        <td>TV</td>
                        <td id="tv" class="alert-danger">NOK</td>
                        <td>Permission TV</td>
                    </tr>
                    <tr>
                        <td>Home</td>
                        <td id="home" class="alert-danger">NOK</td>
                        <td>Permission Home</td>
                        <td>VM</td>
                        <td id="vm" class="alert-danger">NOK</td>
                        <td>Permission VM</td>
                    </tr>
                    <tr>
                        <td>Parental</td>
                        <td id="parental" class="alert-danger">NOK</td>
                        <td>Permission Parental</td>
                        <td>WDO</td>
                        <td id="wdo" class="alert-danger">NOK</td>
                        <td>Permission WDO</td>
                    </tr>
                </tbody>
            </table>
            <br />
            <center><a id="bt_Freebox_droitVerif" class="btn btn-sm btn-warning bt_Freebox_droitVerif">{{Verification
                    des droits}}</a></center>
            <br />
        </div>

        <div class="Freebox_OS_Display scan" style="display:none;">
            <a class="btn btn-sm btn-success pull-right bt_Freebox_OS_Next">{{Suivant}} <i class="fas fa-angle-double-right"></i></a>
            <a class="btn btn-sm btn-default pull-right bt_Freebox_OS_Previous"><i class="fas fa-angle-double-left"></i>
                {{Précédent}}</a>
            <br /><br />
            <center><i class="fas fa-search-plus" style="font-size: 10em;"></i></center>
            <br />
            <center>
                <div class="alert alert-info">{{Cette partie vous permet de rechercher les différents équipements sur
                    votre freebox}}
                </div>
            </center>
            <br />
            <div>
                <div id="colonne1">
                    <div class="thumbnail" style="box-shadow: 1px 1px 12px #872428; height: 310px;"><img src="plugins/Freebox_OS/core/images/system.png" alt="" style="border-radius:5px 5px 0 0; height: 100px;WIDTH: 100px">
                        <div class="caption">
                            <h4>{{Mes Equipements}}</h4>
                            <p></p>
                            <p class="text-center"><a class="btn bt_eqlogic_standard">{{Scan des équipements standards
                                    }} <i class="fas fa-bullseye logoPrimary"></i></a></p>
                            <p></p>
                            <p>{{Ici vous scannez les équipements <b>systèmes</b> de la Freebox}}</p>
                        </div>
                    </div>
                </div>
                <div id="colonne2">
                    <div class="thumbnail" style="box-shadow: 1px 1px 12px #872428; height: 310px;"><img src="plugins/Freebox_OS/core/images/parental.png" alt="" style="border-radius:5px 5px 0 0; height: 100px;WIDTH: 100px">
                        <div class="caption">
                            <h4>{{Mes Contrôles parentaux}}</h4>
                            <p></p>
                            <p class="text-center"><a class="btn bt_eqlogic_control_parental">{{Scan des Contrôles
                                    parentaux }} <i class="fas fa-user-shield logoPrimary"></i></a></p>
                            <p></p>
                            <p>{{Ici vous scannez les <b>contrôles parentaux</b> présents dans la Freebox}}</p>
                        </div>
                    </div>
                </div>
                <div id="centre">
                    <div class="thumbnail" style="box-shadow: 2px 2px 12px #872428; height: 310px;"><img src="plugins/Freebox_OS/core/images/homeadapters.png" alt="" style="border-radius:5px 5px 0 0; height: 100px;WIDTH: 100px">
                        <div class="caption">
                            <h4>{{Mes Equipements Home - Tiles}}</h4>
                            <p></p>
                            <p class="text-center"><a class="btn bt_eqlogic_tiles">{{Scan des Tiles }} <i class="fas fa-search logoPrimary"></i></a></p>
                            <p></p>
                            <p>{{Ici vous scannez les équipements de type <b>Home et Tiles</b> (Maison).}}</p>
                            <p><i>{{Uniquement sur la Freebox Delta}}</i></p>
                        </div>
                    </div>
                </div>
            </div>
            <br />
            <div>
                <center>
                    <div class="alert alert-info">{{Une fois les scans effectués, cliquez simplement sur le bouton
                        suivant}}
                    </div>
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

    </div>
</div>

<?php
include_file('desktop', 'authentification', 'js', 'Freebox_OS');
?>

<style>
    div#colonne1 {
        float: left;
        width: 300px;
        margin-right: 150px;

    }

    div#colonne2 {
        float: right;
        width: 300px;
        margin-left: 100px;
        margin-right: 30px;

    }

    div#centre {
        width: 300px;
        overflow: hidden;
        margin-right: 5px;
        margin-left: 5px;

    }
</style>