<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('Freebox_OS');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<style type="text/css">
.freeOSHidenDiv {
    display:none;
}

.eqLogicThumbnailDisplay .eqLogicThumbnailContainer {
    z-index: 0;
    margin-top: 5px;
    margin-bottom: 30px;
    background-color: rgba(var(--defaultBkg-color), var(--opacity)) !important;
    transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
}

</style>


<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">

            <div class="cursor authentification logoWarning">
                <i class="fas fa-rss"></i>
                <br>
                <span>{{Appairage}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i>
                <br>
                <span>{{Configuration}}</span>
            </div>
			<div class="cursor eqLogicAction logoPrimary" data-action="eqlogic_standard">
				<i class="fas fa-bullseye"></i>
				<br />
				<span>{{Scan}}<br />{{équipements standards}}</span>
			</div>
			<div class="cursor eqLogicAction logoPrimary" data-action="control_parental">
				<i class="fas fa-user-shield"></i>
				<br>
				<span>{{Scan}}<br />{{Contrôle parental}}</span>
			</div>
            <div class="cursor eqLogicAction logoPrimary titleAction" data-action="tile">
                <i class="fas fa-search"></i>
                <br>
                <span>{{Scan}}<br />{{Tiles}}</span>
            </div>
		</div>
		<div class="input-group" style="margin:5px;">
			<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
			<div class="input-group-btn">
				<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i> </a>
			</div>
		</div>
        <div class="divEquipements">
            <legend><i class="fas fa-table"></i> {{Mes Equipements}}</legend>
            <div class="eqLogicThumbnailContainer">
                <?php
                $status = 0;
                foreach ($eqLogics as $eqLogic) {
                    if ($eqLogic->getConfiguration('type') == 'player') {
                        $template = $eqLogic->getConfiguration('type');
                    } else {
                        $template = $eqLogic->getLogicalId();
                    }
                    switch ($template) {
                        case 'airmedia':
                        case 'connexion':
                        case 'downloads':
                        case 'system':
                        case 'disk':
                        case 'phone':
                        case 'wifi':
                        case 'player':
                        case 'network':
                        case 'networkwifiguest':
                            $status = 1;
                            $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                            echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
                            echo '<img src="plugins/Freebox_OS/core/images/' . $template . '.png"/>';
                            echo '<br>';
                            echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                            echo '</div>';
                            break;
                    }
                }
                if ($status == 0) {
                    $divEquipements = false;
                    echo "<br/><br/><br/><center><span style='color:#767676;font-size:1em;font-weight: bold;'>{{Aucun équipement détecté. Lancez un \"Scan équipements standards\".}}</span></center>";
                } else {
                    $divEquipements = true;
                }
                ?>
            </div>
        </div>
        <div class="divTiles">
            <legend><i class="fas fa-home"></i> {{Mes Equipements Home - Tiles}}</legend>
            <div class="eqLogicThumbnailContainer">
                <?php
                    $status = 0;
                    foreach ($eqLogics as $eqLogic) {
                        if ($eqLogic->getConfiguration('type') == 'parental' || $eqLogic->getConfiguration('type') == 'player' || $eqLogic->getConfiguration('type') == 'alarm_control' || $eqLogic->getConfiguration('type') == 'alarm_sensor' || $eqLogic->getConfiguration('type') == 'alarm_remote') {
                            $template = $eqLogic->getConfiguration('type');
                            $icon = $template;
                        } else {
                            $template = $eqLogic->getLogicalId();
                            if ($template == 'homeadapters') {
                                $icon = $template;
                            } else {
                                $icon = 'default';
                            }
                        }
                        switch ($template) {
                            case 'airmedia':
                            case 'connexion':
                            case 'downloads':
                            case 'system':
                            case 'disk':
                            case 'phone':
                            case 'wifi':
                            case 'player':
                            case 'parental':
                            case 'network':
                            case 'networkwifiguest':
                                break;
                            default:
                                $status = 1;
                                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                                echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
                                echo '<img src="plugins/Freebox_OS/core/images/' . $icon . '.png"/>';
                                echo '<br>';
                                echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                                echo '</div>';
                                break;
                        }
                    }
                    if ($status == 0) {
                        $divTiles = false;
                        echo "<br/><br/><br/><center><span style='color:#767676;font-size:1em;font-weight: bold;'>{{Aucun équipement Home - Tiles détecté. Lancez un \"Scan Tiles\".}}</span></center>";
                    } else {
                        $divTiles = true;
                    }
                ?>
            </div>
        </div>
        <div class="divParental">
            <legend><i class="fas fa-user-shield"></i> {{Mes Contrôles parentaux}}</legend>
            <div class="eqLogicThumbnailContainer">
                <?php
                $status = 0;
                foreach ($eqLogics as $eqLogic) {
                    if ($eqLogic->getConfiguration('type') == 'parental') {
                        $status = 1;
                        $template = $eqLogic->getConfiguration('type');
                        $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                        echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
                        echo '<img src="plugins/Freebox_OS/core/images/' . $template . '.png"/>';
                        echo '<br>';
                        echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                        echo '</div>';
                    }
                }
                if ($status == 1) {
                    echo '</div>';
                    $parental = true;
                } else {
                    echo "<br/><br/><br/><center><span style='color:#767676;font-size:1em;font-weight: bold;'>{{Aucun équipement Contrôle Parental détecté. Lancez un \"Scan Contrôle parental\".}}</span></center>";
                    $parental = false;
                }
                ?>
		    </div>
        </div>
	</div>

    <?php
        sendVarToJS('divEquipements', $divEquipements);
        sendVarToJS('divTiles', $divTiles);
        sendVarToJS('divParental', $parental);
    ?>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-sm btn-info eqLogicAction Equipement"><i class="fas fa-search"></i> {{Recherche des équipements supplémentaires}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation">
				<a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
					<i class="fas fa-arrow-circle-left"></i>
				</a>
			</li>
			<li role="presentation" class="active">
				<a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
					<i class="fas fa-tachometer-alt"></i>
					{{Equipement}}
				</a>
			</li>
			<li role="presentation">
				<a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fas fa-list-alt"></i>
					{{Commandes}}
				</a>
			</li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br />
				<form class="form-horizontal col-sm-10">
					<fieldset>
						<div class="form-group ">
							<label class="col-sm-2 control-label">{{Nom de l'équipement}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="logicalId" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
									foreach (jeeObject::all() as $object)
										echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Catégorie}}</label>
							<div class="col-sm-9">
								<?php
								foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
									echo '<label class="checkbox-inline">';
									echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
									echo '</label>';
								}
								?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-9">
								<label class="checkbox-inline">
									<input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />
									{{Activer}}
								</label>
								<label class="checkbox-inline">
									<input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />
									{{Visible}}
								</label>
							</div>
						</div>
					</fieldset>
				</form>

				<form class="form-horizontal col-sm-2">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-8">
								<img src="plugins/Freebox_OS/core/images/default.png" data-original=".jpg" id="img_device" class="img-responsive" style="width:120px" onerror="this.src='plugins/Freebox_OS/core/images/default.png'" />
							</div>
						</div>
					</fieldset>
				</form>
				<br />

				<hr>
				<legend><i class="fas fa-cog"></i> {{Paramètres}}</legend>
				<form class="form-horizontal col-sm-10">
					<fieldset>
                        <div class="form-group IPV4">
                            <label class="col-sm-2 control-label">{{Affichage IPv4 sur le widget}}
                                <sup><i class="fas fa-question-circle" title="{{Si la case est cochée cela affiche l'IPv6 sur le widget}}"></i></sup>
                            </label>
                            <div class="col-sm-3">
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="IPV4" /></label>
                            </div>
                        </div>
						<div class="form-group IPV6">
							<label class="col-sm-2 control-label">{{Affichage IPv6 sur le widget}}
								<sup><i class="fas fa-question-circle" title="{{Si la case est cochée cela affiche l'IPv6 sur le widget}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="IPV6" /></label>
							</div>
						</div>
                    </br>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Temps de rafraichissement (cron)}}
                                <sup><i class="fas fa-question-circle" title="{{Cron }}"></i></sup>
                            </label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="autorefresh" placeholder="{{*/5 * * * *}}" />
                                    <span class="input-group-btn">
										<a class="btn btn-default cursor jeeHelper" data-helper="cron">
											<i class="fas fa-question-circle"></i>
										</a>
									</span>
                                </div>
                            </div>
                        </div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{logicalId équipement}}
								<sup><i class="fas fa-question-circle" title="{{logicalId de l'équipement Freebox}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<span class="eqLogicAttr tooltips label label-default" data-l1key="configuration" data-l2key="logicalID"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Type d'équipement}}
								<sup><i class="fas fa-question-circle" title="{{Type équipement Freebox}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<span class="eqLogicAttr tooltips label label-default" data-l1key="configuration" data-l2key="type"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Type d'actions de l'équipement}}
								<sup><i class="fas fa-question-circle" title="{{Type action Freebox}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<span class="eqLogicAttr tooltips label label-default" data-l1key="configuration" data-l2key="action"></span>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">

				<br /><br />
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width: 10px;"> </th>
							<th style="width: 650px;">{{Nom}}</th>
							<th style="width: 110px;">{{Sous-Type}}</th>
							<th style="width: 350px;">{{Min/Max - Unité}}</th>
							<th>{{Paramètres}}</th>
							<th style="width: 250px;">{{Options}}</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php
include_file('desktop', 'Freebox_OS', 'js', 'Freebox_OS');
include_file('core', 'plugin.template', 'js');
?>