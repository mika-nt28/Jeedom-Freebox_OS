<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('Freebox_OS');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<style type="text/css">
	.freeOSHidenDiv {
		display: none;
	}

	.eqLogicThumbnailDisplayEquipement {
		z-index: 0;
		margin-top: 5px;
		margin-bottom: 30px;
		background-color: rgba(var(--defaultBkg-color), var(--opacity)) !important;
		transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
	}
</style>

<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<!-- Boutons de gestion du plugin -->
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
			<div class="cursor logoSecondary health">
				<i class="fas fa-medkit"></i>
				<br />
				<span>{{Santé}}</span>
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
			<?php
			if (log::getLogLevel('Freebox_OS') <= 200) :
			?>
				<div class="cursor eqLogicAction logoWarning titleAction" data-action="search_debugTile">
					<i class="fas fa-question-circle"></i>
					<br />
					<span>{{Debug Tiles}}</span>
				</div>
			<?php
			endif;
			?>
		</div>
		<!-- Champ de recherche -->
		<div class="input-group" style="margin-bottom:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
			<div class="input-group-btn">
				<a id="bt_resetObjectSearch" class="btn" style="width:30px"><i class="fas fa-times"></i>
				</a><a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>
			</div>
		</div>
		<!-- Liste des équipements du plugin "Mes équipements" -->
		<div class="divEquipements">
			<legend><i class="fas fa-table"></i> {{Mes Equipements}}</legend>
			<div class="eqLogicThumbnailContainer eqLogicThumbnailDisplayEquipement">
				<?php
				$status = 0;
				foreach ($eqLogics as $eqLogic) {
					if ($eqLogic->getConfiguration('eq_group') === 'system' || $eqLogic->getConfiguration('eq_group') == null) {
						if ($eqLogic->getConfiguration('type') == 'player') {
							$template = $eqLogic->getConfiguration('type');
						} else {
							$template = $eqLogic->getLogicalId();
						}

						$status = 1;
						$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
						echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
						echo '<img src="plugins/Freebox_OS/core/images/' . $template . '.png"/>';
						echo '<br>';
						echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
						echo '<span class="hidden hiddenAsCard displayTableRight">' . $eqLogic->getConfiguration('autorefresh')  .  '</span>';
						echo '</div>';
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
		<!-- Liste des équipements du plugin "Mes équipements Home - Tiles" -->
		<div class="divTiles">
			<legend><i class="fas fa-home"></i> {{Mes Equipements Home - Tiles}}</legend>
			<div class="eqLogicThumbnailContainer eqLogicThumbnailDisplayEquipement">
				<?php
				$status = 0;
				foreach ($eqLogics as $eqLogic) {
					if ($eqLogic->getConfiguration('type') === 'player' || $eqLogic->getConfiguration('type') === 'alarm_control' || $eqLogic->getConfiguration('type') === 'camera' || $eqLogic->getConfiguration('type') === 'light' || $eqLogic->getConfiguration('type') === 'alarm_remote') {
						$template = $eqLogic->getConfiguration('type');
						$icon = $template;
					} elseif ($eqLogic->getConfiguration('type') == 'alarm_sensor') {
						if ($eqLogic->getConfiguration('type2') == 'dws') {
							$template = $eqLogic->getConfiguration('type2');
						} else {
							$template = $eqLogic->getConfiguration('type');
						}
						$icon = $template;
					} elseif ($eqLogic->getConfiguration('type') == 'info') {
						if (strpos($eqLogic->getConfiguration('type2'), 'shutter') !== false) {
							$template = 'shutter';
						} else if ($eqLogic->getConfiguration('type2') == 'plug' || $eqLogic->getConfiguration('type2') == 'opener') {
							$template = $eqLogic->getConfiguration('type2');
						} else {
							$template  = 'default';
						}
						$icon = $template;
					} else {
						$template = $eqLogic->getLogicalId();
						if ($template == 'homeadapters') {
							$icon = $template;
						} else {
							$icon = 'default';
						}
					}
					if ($eqLogic->getConfiguration('eq_group') === 'tiles' || $eqLogic->getConfiguration('eq_group') === 'nodes' || $eqLogic->getConfiguration('eq_group') === 'tiles_SP') {

						$status = 1;
						$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
						echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
						echo '<img src="plugins/Freebox_OS/core/images/' . $icon . '.png"/>';
						echo '<br>';
						echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
						echo '<span class="hidden hiddenAsCard displayTableRight">' . $eqLogic->getConfiguration('autorefresh')  .  '</span>';
						echo '</div>';
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
		<!-- Liste des équipements du plugin "Mes contrôles parentaux" -->
		<div class="divParental">
			<legend><i class="fas fa-user-shield"></i> {{Mes Contrôles parentaux}}</legend>
			<div class="eqLogicThumbnailContainer eqLogicThumbnailDisplayEquipement">
				<?php
				$status = 0;
				foreach ($eqLogics as $eqLogic) {
					if ($eqLogic->getConfiguration('eq_group') == 'parental_controls') {
						$status = 1;
						$template = $eqLogic->getConfiguration('type');
						$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
						echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
						echo '<img src="plugins/Freebox_OS/core/images/' . $template . '.png"/>';
						echo '<br>';
						echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
						echo '<span class="hidden hiddenAsCard displayTableRight">' . $eqLogic->getConfiguration('autorefresh')  .  '</span>';
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
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<?php
	sendVarToJS('divEquipements', $divEquipements);
	sendVarToJS('divTiles', $divTiles);
	sendVarToJS('divParental', $parental);
	?>
	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display: none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces. Ne pas modifier -->
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure" title="{{Configuration de l'équipement}}"><i class="fa fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-info eqLogicAction Equipement" title="{{Recherche les commandes supplémentaire de l'équipement}}"><i class="fas fa-search"></i><span class="hidden-xs"> {{Recherche des commandes}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i><span class="hidden-xs"> {{Supprimer}}</span>
				</a>
			</span>
		</div>
		<!-- Onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i><span class="hidden-xs"> {{Équipement}}</span></a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i><span class="hidden-xs"> {{Commandes}}</span></a></li>
		</ul>
		<div class="tab-content">
			<!-- Onglet de configuration de l'équipement -->
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<!-- Partie gauche de l'onglet "Equipements" -->
				<!-- Paramètres généraux de l'équipement -->
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-7">
							<legend> {{}}</legend> <!-- Partie générale non affiché <i class="fas fa-wrench"></i> {{Général}}" -->
							<div class="form-group ">
								<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Objet parent}}</label>
								<div class="col-sm-7">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
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
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Catégorie}}</label>
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
								<label class="col-sm-3 control-label">{{Options}}</label>
								<div class="col-sm-7">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" title="Activer l'équipement" checked />{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" title="Rendre l'équipement visible" checked />{{Visible}}</label>
								</div>
							</div>
							<br>

							<!-- Paramètres spéficique de l'équipement -->
							<legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>
							<!-- Champ de saisie du cron d'auto-actualisation + assistant cron -->
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Auto-actualisation}}
									<sup><i class="fas fa-question-circle" title="{{Fréquence de rafraîchissement de l'équipement}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<div class="input-group">
										<input type="text" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="autorefresh" placeholder="{{Cliquer sur ? pour afficher l'assistant cron}}" />
										<span class="input-group-btn">
											<a class="btn btn-default cursor jeeHelper roundedRight" data-helper="cron" title="Assistant cron">
												<i class="fas fa-question-circle"></i>
											</a>
										</span>
									</div>
								</div>
							</div>
							<div class="form-group IPV">
								<label class="col-sm-3 control-label">{{Affichage IP sur le widget}}
									<sup><i class="fas fa-question-circle" title="{{Si la case est cochée cela affiche l'IPv4 our l'IPv6 sur le widget}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" title="Affiche l\'IPv4 sur le widget" data-l1key="configuration" data-l2key="IPV4" />{{IPv4}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" title="Affiche l\'IPv6 sur le widget" data-l1key="configuration" data-l2key="IPV6" />{{IPv6}}</label>
								</div>
							</div>
						</div>

						<!-- Partie droite de l'onglet "Equipement" -->
						<!-- Affiche l'icône du plugin par défaut mais vous pouvez y afficher les informations de votre choix -->
						<div class="col-lg-5">
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class=" form-group">
								<label class="col-sm-4 control-label"></label>
								<div class="col-sm-7 text-center">
									<img src="plugins/Freebox_OS/core/images/default.png" data-original=".jpg" id="img_device" class="img-responsive" style="width:120px" onerror="this.src='plugins/Freebox_OS/core/images/default.png'" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{logicalId équipement}}
									<sup><i class="fas fa-question-circle" title="{{logicalId de l'équipement Freebox}}"></i></sup>
								</label>
								<div class="col-sm-3">
									<span class="eqLogicAttr cmdAttr label label-primary" data-l1key="configuration" data-l2key="logicalID" style="font-size : 1em"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Type d'équipement}}
									<sup><i class="fas fa-question-circle" title="{{Type équipement Freebox}}"></i></sup>
								</label>
								<div class="col-sm-3">
									<span class="eqLogicAttr cmdAttr label label-primary" data-l1key="configuration" data-l2key="type" style="font-size : 1em">
									</span> <span class="eqLogicAttr cmdAttr label label-primary" data-l1key="configuration" data-l2key="type2" style="font-size : 1em">
									</span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Type d'actions de l'équipement}}
									<sup><i class="fas fa-question-circle" title="{{Type action Freebox}}"></i></sup>
								</label>
								<div class="col-sm-3">
									<span class="eqLogicAttr cmdAttr label label-primary" data-l1key="configuration" data-l2key="action"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Type d'infos d'équipement}}
									<sup><i class="fas fa-question-circle" title="{{Type d'information Freebox}}"></i></sup>
								</label>
								<div class="col-sm-3">
									<span class="eqLogicAttr cmdAttr label label-primary" data-l1key="configuration" data-l2key="info" style="font-size : 1em"></span>
								</div>
							</div>
					</fieldset>
				</form>
				<hr>
			</div> <!-- /.tabpanel #eqlogictab-->

			<!-- Onglet des commandes de l'équipement -->
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<!-- <a class="btn btn-default btn-sm pull-right cmdAction" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a> -->
				<br /><br />
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>{{Id}}</th>
								<th>{{Nom}}</th>
								<th>{{Type}}</th>
								<th>{{Options}}</th>
								<th>{{Paramètres}}</th>
								<th>{{Action}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div><!-- /.tabpanel #commandtab-->

		</div><!-- /.tab-content -->
	</div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->
<?php
include_file('desktop', 'Freebox_OS', 'js', 'Freebox_OS');
include_file('core', 'plugin.template', 'js');
?>