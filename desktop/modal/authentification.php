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
?>
<div class=" row row-overflow">
    <div class="col-lg-2">
        <div class="bs-sidebar">
            <ul class="nav nav-list bs-sidenav hidden-xs">
                <li class="cursor li_FreeboxOS_Summary active" data-href="home" title="{{Accueil}}"><a><i class="fab fa-ello"></i> <span class="hidden-xs"> {{Accueil}}</span></a></li>
                <li class="cursor li_FreeboxOS_Summary" data-href="setting" title="{{Réglages}}"><a><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Réglages}}</span></a></li>
                <li class="cursor li_FreeboxOS_Summary" data-href="authentification" title="{{Authentification}}"><a><i class="fas fa-rss"></i><span class="hidden-xs"> {{Authentification}}</span></a> </li>
                <li class="cursor li_FreeboxOS_Summary" data-href="rights" title="{{Droits}}"><a><i class="fas fa-balance-scale-right"></i><span class="hidden-xs"> {{Droits}}</span></a> </li>
                <li class="cursor li_FreeboxOS_Summary" data-href="room" title="{{Objets}}"><a><i class="fas fa-bezier-curve"></i><span class="hidden-xs"> {{Freebox Delta}}</span></a> </li>
                <li class="cursor li_FreeboxOS_Summary" data-href="scan" title="{{Scan des équipements}}"><a><i class="fas fa-search-plus"></i><span class="hidden-xs"> {{Scan des équipements}}</span></a> </li>
                <li class="cursor li_FreeboxOS_Summary" data-href="end" title="{{C'est Fini !!}}"><a><i class="fas fa-check"></i><span class="hidden-xs"> {{Fin}}</span></a>
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
        <div class="FreeboxOS_Display home">
            <div class="input-group pull-right" style="display:inline-flex;">
                <span class="input-group-btn">
                    <a class="btn btn-sm btn-primary bt_FreeboxOS_doc roundedLeft" title="{{Documentation}}" target='_blank' href='https://jealg.github.io/documentation/plugin-freebox_os/fr_FR/'><i class="fas fa-book"></i><span class="hidden-xs"> {{Documentation}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Next roundedRight" title="{{Suivant}}"><span class="hidden-xs">{{Suivant}} </span><i class="fas fa-angle-double-right"></i>
                    </a>
                </span>
            </div>
            <br /><br /> <br />
            <BR>
            <center>
                <center><i class="fab fa-ello" style="font-size: 8em;"></i></center>
                <br />
                <div class="alert alert-info">{{Bienvenue, nous allons commencer l'authentification sur la Freebox}}
                </div>
            </center>
            <center>{{Cliquez sur suivant pour commencer}}</center>
        </div>

        <div class="FreeboxOS_Display setting" style="display:none;">
            <div class="input-group pull-right" style="display:inline-flex;">
                <span class="input-group-btn">
                    <a class="btn btn-sm btn-primary bt_FreeboxOS_doc roundedLeft" title="{{Documentation}}" target='_blank' href='https://jealg.github.io/documentation/plugin-freebox_os/fr_FR/'><i class="fas fa-book"></i><span class="hidden-xs"> {{Documentation}}</span>
                    </a><a class="btn btn-sm btn-danger bt_FreeboxOS_ResetConfig" title="{{Reset de la configuration}}"><i class="fas fa-trash"></i><span class="hidden-xs">{{Reset de la configuration}}</span>
                    </a><a class="btn btn-sm btn-success bt_FreeboxOS_Save"><i class="fas fa-save"></i><span class="hidden-xs"> {{Sauvegarder}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Previous" title="{{Précedent}}"><i class="fas fa-angle-double-left"></i><span class="hidden-xs"> {{Précédent}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Next roundedRight" title="{{Suivant}}"><span class="hidden-xs">{{Suivant}} </span><i class="fas fa-angle-double-right"></i>
                    </a>
                </span>
            </div>
            <br /><br /> <br />
            <BR>
            <center>
                <center><i class="fas fa-cogs" style="font-size: 8em;"></i></center>
                <br />
                <h3 class="textFreebox">{{}}</h3>
                <div class="alert alert-info">{{C'est parti, lançons nous. Pour commencer nous allons valider les réglages}}
                </div>
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-md-5 control-label">{{IP Freebox :}}</label>
                            <div class="col-md-4">
                                <input id="input_freeboxIP" type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_IP" />
                            </div>
                        </div>
                        <div class="form-group debugFreeOS debugHide">
                            <label class="col-md-5 control-label">{{Nom de l'application Freebox serveur :}}</label>
                            <div class="col-md-4">
                                <input id="input_freeNameAPP" type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_APP_NAME" disabled />
                            </div>
                        </div>
                        <div class="form-group debugFreeOS debugHide">
                            <label class="col-md-5 control-label">{{Id de l'application Freebox serveur :}}</label>
                            <div class="col-md-4">
                                <input id="input_IdApp" type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_APP_ID" disabled />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-5 control-label">{{Nom de l'équipement connecté :}}</label>
                            <div class="col-md-4">
                                <input id="input_DeviceName" type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_DEVICE_NAME" disabled />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-5 control-label">{{Version API de la Freebox :}}</label>
                            <div class="col-md-4">
                                <input id="input_API" type="text" class="configKey form-control" data-l1key="FREEBOX_API" disabled />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-5 control-label">{{Ajouter automatiquement les équipements détectés dans :}}</label>
                            <div class="col-md-4">
                                <select id="sel_object_default" class="configKey form-control" data-l1key="defaultParentObject">
                                    <option value="">{{Aucune}}</option>
                                    <?php
                                    $options = '';
                                    foreach ((jeeObject::buildTree(null, false)) as $object) {
                                        $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                                    }
                                    echo $options;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <br />
                    </fieldset>
                </form>

            </center>
            <center>
                <div class="alert alert-info">{{Une fois validé, cliquez sur le bouton Sauvegarder}}</div>
            </center>
        </div>

        <div class="FreeboxOS_Display authentification" style="display:none;">
            <div class="input-group pull-right" style="display:inline-flex;">
                <span class="input-group-btn">
                    <a class="btn btn-sm btn-primary bt_FreeboxOS_doc roundedLeft" title="{{Documentation}}" target='_blank' href='https://jealg.github.io/documentation/plugin-freebox_os/fr_FR/'><i class="fas fa-book"></i><span class="hidden-xs"> {{Documentation}}</span>
                    </a><a class="btn btn-sm btn-warning bt_Freebox_Autorisation" title="{{Lancer la procédure d'authentification}}"><i class="fas fa-exclamation-circle"></i><span class="hidden-xs"> {{Lancement de l'authentification}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Previous" title="{{Précedent}}"><i class="fas fa-angle-double-left"></i><span class="hidden-xs"> {{Précédent}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Next roundedRight" title="{{Suivant}}"><span class="hidden-xs">{{Suivant}} </span><i class="fas fa-angle-double-right"></i>
                    </a>
                </span>
            </div>
            <br /><br /> <br />
            <BR>
            <center>
                <center><i class="fas fa-rss" style="font-size: 8em;"></i></center>
                <br />
                <img class="img-responsive center-block hidden-xs" src="plugins/Freebox_OS/core/img/authentification.jpg" height="450" width="350" />
                <br />

                <br />

                <h3 class="textFreebox">{{}}</h3>

                <div class="alert alert-info Freebox_Autorisation">{{Si votre box n'est pas encore connectée, cliquez sur le bouton ci-dessous, sinon cliquez sur suivant}}
                </div>
                <div class="alert alert-warning Freebox_Autorisation">{{Avertissement ! Le temps de validation sur la Freebox étant de quelques secondes positionnez vous ou une personne tiers devant la box afin de valider dans ce court délais !}}
                </div>

                <a class="btn btn-sm btn-warning bt_Freebox_Autorisation" title="{{Lancer la procédure d'authentification}}">{{Lancement de l'authentification}} <i class="fas fa-exclamation-circle"></i></a>
            </center>
        </div>

        <div class="FreeboxOS_Display rights" style="display:none;">
            <div class="input-group pull-right" style="display:inline-flex;">
                <span class="input-group-btn">
                    <a class="btn btn-sm btn-primary bt_FreeboxOS_doc roundedLeft" title="{{Documentation}}" target='_blank' href='https://jealg.github.io/documentation/plugin-freebox_os/fr_FR/'><i class="fas fa-book"></i><span class="hidden-xs"> {{Documentation}}</span>
                    </a><a class="btn btn-sm btn-danger bt_FreeboxOS_droitVerif_pass" title="{{Ignorer la vérification des droits}}"><i class="fas fa-balance-scale"></i><span class="hidden-xs"> {{Ignorer Vérification des droits}}</span>
                    </a><a id="bt_FreeboxOS_droitVerif" class="btn btn-sm btn-warning bt_FreeboxOS_droitVerif" title="{{Lancer la vérification des droits}}"><i class="fas fa-balance-scale"></i><span class="hidden-xs"> {{Vérification des droits}}</span>
                    </a><a id="bt_FreeboxOS" class="btn btn-sm btn-default bt_FreeboxOS" target='_blank' href='http://mafreebox.freebox.fr'><i class="far fa-hand-point-right"></i> <span class="hidden-xs">{{Ouvrir Interface Freebox}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Previous" title="{{Précedent}}"><i class="fas fa-angle-double-left"></i><span class="hidden-xs"> {{Précédent}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Next roundedRight" title="{{Suivant}}"><span class="hidden-xs">{{Suivant}} </span><i class="fas fa-angle-double-right"></i>
                    </a>
                </span>
            </div>
            <br /><br /> <br />
            <BR>
            <center>
                <center><i class="fas fa-balance-scale-right" style="font-size: 5em;"></i></center>
                <br />
                <img class="img-responsive center-block hidden-xs" src="plugins/Freebox_OS/core/img/modification_droit.png" height="400" width="400" />
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
                    <div class="alert alert-info">{{Puis une fois les droits modifiés, cliquez sur le bouton Vérification des droits, si OK cliquez sur le bouton suivant}}
                    </div>
                </center>
                <br />
                <br />
                <table id="table_packages" class="table table-condensed">
                    <thead>
                        <tr>
                            <th style="width: 120px">{{Nom}}</th>
                            <th style="width: 70px">{{Status}}</th>
                            <th>{{Description}}</th>
                            <th style="width: 120px">{{Nom}}</th>
                            <th style="width: 70px">{{Status}}</th>
                            <th>{{Description}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b>Parental</b></td>
                            <td id="parental" class="alert-danger">NOK</td>
                            <td>{{Accès au contrôle parental}}</td>
                            <td>TV</td>
                            <td id="tv" class="alert-danger">NOK</td>
                            <td>{{Accès au guide TV}}</td>
                        </tr>
                        <tr>
                            <td>Explorer</td>
                            <td id="explorer" class="alert-danger">NOK</td>
                            <td>{{Accès aux fichiers de la Freebox}}</td>
                            <td>Contacts</td>
                            <td id="contacts" class="alert-danger">NOK</td>
                            <td>{{Accès à la base de contacts de la Freebox}}</td>
                        </tr>
                        <tr>
                            <td>WDO</td>
                            <td id="wdo" class="alert-danger">NOK</td>
                            <td>{{Provisionnement des équipements}}</td>
                            <td><b>Camera</b></td>
                            <td id="camera" class="alert-danger">NOK</td>
                            <td>{{Accès aux caméras}}</td>
                        </tr>
                        <tr>
                            <td><b>Profile</b></td>
                            <td id="profile" class="alert-danger">NOK</td>
                            <td>{{Gestion des profils utilisateur}}</td>
                            <td><b>Player</b></td>
                            <td id="player" class="alert-danger">NOK</td>
                            <td>{{Contrôle du Freebox Player}}</td>
                        </tr>
                        <tr>
                            <td><b>Settings</b></td>
                            <td id="settings" class="alert-danger">NOK</td>
                            <td>{{Modification des réglages de la Freebox}}</td>
                            <td><b>Calls</b></td>
                            <td id="calls" class="alert-danger">NOK</td>
                            <td>{{Accès au journal d'appels}}</td>
                        </tr>
                        <tr>
                            <td><b>Home</b></td>
                            <td id="home" class="alert-danger">NOK</td>
                            <td>{{Gestion de l'alarme et maison connectée}}</td>
                            <td>PVR</td>
                            <td id="pvr" class="alert-danger">NOK</td>
                            <td>{{Programmation des enregistrements}}</td>
                        </tr>
                        <tr>
                            <td><b>VM</b></td>
                            <td id="vm" class="alert-danger">NOK</td>
                            <td>{{Contrôle de la VM}}</td>
                            <td><b>Download</b></td>
                            <td id="downloader" class="alert-danger">NOK</td>
                            <td>{{Accès au gestionnaire de téléchargements}}</td>
                        </tr>
                    </tbody>
                </table>

            </center>
        </div>

        <div class="FreeboxOS_Display room" style="display:none;">
            <div class="input-group pull-right" style="display:inline-flex;">
                <span class="input-group-btn">
                    <a class="btn btn-sm btn-primary bt_FreeboxOS_doc roundedLeft" title="{{Documentation}}" target='_blank' href='https://jealg.github.io/documentation/plugin-freebox_os/fr_FR/'><i class="fas fa-book"></i><span class="hidden-xs"> {{Documentation}}</span>
                    </a><a class="btn btn-sm btn-warning bt_FreeboxOS_Room" title="{{Lancer le scan des Pièces}}"><i class="fas fa-exclamation-circle"></i><span class="hidden-xs"> {{Recherche des Pièces}}</span>
                    </a><a class="btn btn-sm btn-success bt_FreeboxOS_Save_room"><i class="fas fa-save"></i><span class="hidden-xs"> {{Sauvegarder}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Previous" title="{{Précedent}}"><i class="fas fa-angle-double-left"></i><span class="hidden-xs"> {{Précédent}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Next roundedRight" title="{{Suivant}}"><span class="hidden-xs">{{Suivant}} </span><i class="fas fa-angle-double-right"></i>
                    </a>
                </span>
            </div>
            <br /><br /> <br />
            <BR>
            <center>
                <center><i class="fas fa-bezier-curve" style="font-size: 5em;"></i></center>
                <br />
                <br />
                <center>
                    <div class="alert alert-info">{{Cette partie vous permet de paramétrer les options spécifiques}}
                        <br>
                        <i>{{Uniquement sur la Freebox Delta}}</i>
                    </div>
                    <div class="alert alert-danger">{{Il est conseillé de désactiver l'option "Actualisation Globale des Tiles", si vous avez des volets sous protocole IO}}
                    </div>
                    <br>
                    <form class="form-horizontal">
                        <fieldset>
                            <div class="form-group">
                                <label class="col-md-5 control-label">{{Actualisation Globale des Tiles :}}
                                    <sup><i class="fas fa-question-circle" title="{{si la case est cochée, l'actualisation des tiles est faite de façon globale}}"></i></sup>
                                </label>
                                <div class="col-xs-2">
                                    <input id="checkbox_freeboxTiles" type="checkbox" class="configKey checkbox_freeboxTiles" data-l1key="FREEBOX_TILES_CRON" />
                                </div>
                            </div>
                            <br />
                        </fieldset>
                    </form>

                    <table id="table_room" class="table table-condensed">
                        <thead>
                            <tr>
                                <th style="width: 320px">{{Pièce Freebox}}</th>
                                <th>{{Objets Jeedom}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <br />
                    <br />
                    <center>
                        <div class="alert alert-info">{{Une fois les options choisies , cliquez sur le bouton Sauvegarder}}</div>
                    </center>
                </center>
        </div>
        <div class="FreeboxOS_Display scan" style="display:none;">
            <div class="input-group pull-right" style="display:inline-flex;">
                <span class="input-group-btn">
                    <a class="btn btn-sm btn-primary bt_FreeboxOS_doc roundedLeft" title="{{Documentation}}" target='_blank' href='https://jealg.github.io/documentation/plugin-freebox_os/fr_FR/'><i class="fas fa-book"></i><span class="hidden-xs"> {{Documentation}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Previous" title="{{Précedent}}"><i class="fas fa-angle-double-left"></i><span class="hidden-xs"> {{Précédent}}</span>
                    </a><a class="btn btn-sm bt_FreeboxOS_Next roundedRight" title="{{Suivant}}"><span class="hidden-xs">{{Suivant}} </span><i class="fas fa-angle-double-right"></i>
                    </a>
                </span>
            </div>
            <br /><br /> <br />
            <BR>
            <center>
                <center><i class="fas fa-search-plus" style="font-size: 8em;"></i></center>
                <br />

                <div class="alert alert-info">{{Cette partie vous permet de rechercher les différents équipements de votre freebox}}
                </div>

                <br />
                <div>
                    <div id="colonne1">
                        <div class="thumbnail" style="box-shadow: 1px 1px 12px #872428; height: 310px;"><img src="plugins/Freebox_OS/core/img/system.png" alt="" style="border-radius:5px 5px 0 0; height: 100px;WIDTH: 100px">
                            <div class="caption">
                                <h4>{{Mes Equipements}}</h4>
                                <p></p>
                                <p class="text-center"><a class="btn bt_eqlogic_standard">{{Scan des équipements standards}} <i class="fas fa-bullseye logoPrimary"></i></a></p>
                                <p></p>
                                <p>{{Ici vous scannez les équipements <b>systèmes</b> de la Freebox}}</p>
                            </div>
                        </div>
                    </div>
                    <div id="colonne2">
                        <div class="thumbnail" style="box-shadow: 1px 1px 12px #872428; height: 310px;"><img src="plugins/Freebox_OS/core/img/parental.png" alt="" style="border-radius:5px 5px 0 0; height: 100px;WIDTH: 100px">
                            <div class="caption">
                                <h4>{{Mes Contrôles parentaux}}</h4>
                                <p></p>
                                <p class="text-center"><a class="btn bt_eqlogic_control_parental">{{Scan des Contrôles parentaux}} <i class="fas fa-user-shield logoPrimary"></i></a></p>
                                <p></p>
                                <p>{{Ici vous scannez les <b>contrôles parentaux</b> présents dans la Freebox}}</p>
                            </div>
                        </div>
                    </div>
                    <div id="centre">
                        <div class="thumbnail" style="box-shadow: 2px 2px 12px #872428; height: 310px;"><img src="plugins/Freebox_OS/core/img/homeadapters.png" alt="" style="border-radius:5px 5px 0 0; height: 100px;WIDTH: 100px">
                            <div class="caption">
                                <h4>{{Mes Equipements}} Home - Tiles</h4>
                                <p></p>
                                <p class="text-center"><a class="btn bt_eqlogic_tiles">{{Scan des Tiles}} <i class="fas fa-search logoPrimary"></i></a></p>
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
                        <div class="alert alert-info">{{Une fois les scans effectués, cliquez simplement sur le bouton suivant}}
                        </div>
                    </center>
                </div>
            </center>
        </div>

        <div class="FreeboxOS_Display end" style="display:none;">
            <center><i class="fas fa-check" style="font-size: 8em;"></i></center>
            <br />
            <img class="img-responsive center-block" src="core/img/logo-jeedom-freebox-grand-nom-couleur.png" height="500" width="500" />
            <center>
                <br />
                <div class="alert alert-success">{{Bravo}} !!!</div>
            </center>
            <center>
                <div class="alert alert-info FreeboxOS_OK">{{Authentification réussie, Vous pouvez fermer cette fenêtre}}</div>
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
        width: 280px;
        margin-right: 130px;

    }

    div#colonne2 {
        float: right;
        width: 280px;
        margin-left: 100px;
        margin-right: 5px;

    }

    div#centre {
        width: 280px;
        overflow: hidden;
        margin-right: 5px;
        margin-left: 5px;

    }

    .debugHide {
        display: none;
        visibility: hidden;
    }
</style>