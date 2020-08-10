<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
?>
<form class="form-horizontal">
	<fieldset>
		<!--<div class="form-group">
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
			<label class="col-md-3 control-label">{{L'appairage doit être lancé après une sauvegarde des paramètres pour leurs prises en compte.}}</label>
			<div class="col-md-3">
				<a class="btn btn-sm btn-info FreeboxAppaire"><i class="fas fa-rss"></i> {{Appairage}}</a>
			</div>
		</div>
		</br></br>
		<div class="form-group">
			<label class="col-lg-3 control-label">{{Ajouter automatiquement les équipements détectés dans :}}</label>
			<div class="col-lg-3">
				<select id="sel_object" class="configKey form-control" data-l1key="defaultParentObject">
					<option value="">{{Aucune}}</option>
					<?php
					// foreach (jeeObject::all() as $object) {
					//	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
					//}
					?>
				</select>
			</div>
		</div>-->
	</fieldset>
</form>
<?php include_file('desktop', 'configuration', 'js', 'Freebox_OS'); ?>