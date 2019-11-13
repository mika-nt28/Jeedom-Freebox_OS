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
		<div class="form-group">
			<label class="col-md-2 control-label" >{{IP Freebox}}</label>
			<div class="col-md-3">
				<input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_IP" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-2 control-label" >{{Id de l'application Freebox serveur}}</label>
			<div class="col-md-3">
				<input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_APP_ID" />
			</div>
		</div>
		 <div class="form-group">
			<label class="col-md-2 control-label" >{{Nom de l'application Freebox serveur}}</label>
			<div class="col-md-3">
				<input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_APP_NAME" />
			</div>
		</div>
		 <div class="form-group">
			<label class="col-md-2 control-label" >{{Version de l'application Freebox serveur}}</label>
			<div class="col-md-3">
				<input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_APP_VERSION" />
			</div>
		</div>
		 <div class="form-group">
			<label class="col-md-2 control-label" >{{Nom de l'équipement connecté}}</label>
			<div class="col-md-3">
				<input type="text" class="configKey form-control" data-l1key="FREEBOX_SERVER_DEVICE_NAME" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-2 control-label" >{{L'appairage doit etre lancé après une sauvegarde des parametres pour leurs prises en compte.}}</label>
			<div class="col-md-3">
				<a class="btn btn-primary FreeboxAppaire" ><i class="fa fa-rss"></i> {{Appairage}}</a>
			</div>
		</div>	
	</fieldset> 
</form>
<?php include_file('desktop', 'configuration', 'js' , 'Freebox_OS'); ?>
