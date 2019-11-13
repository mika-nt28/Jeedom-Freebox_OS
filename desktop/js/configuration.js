$('.FreeboxAppaire').on('click',function(){
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
					$('#div_alert').showAlert({message: data.result.msg, level: 'danger'});
					if(data.result.error_code=="new_apps_denied")
						$('#div_alert').append(".<br>Pour activer l'option, il faut se rendre dans : mafreebox.free.fr -> Paramètres de la Freebox -> Gestion des accès <br> Et cocher : <b>Permettre les nouvelles demandes d'associations</b>  -> Appliquer<br>De nouveau, cliquez sur <b>Etape 1</b>");
					return;
				}else{
					sendToBdd(data.result);		
					bootbox.confirm('{{Il faut aller valider Jeedom sur la Freebox Server, cliquez sur la flèche OUI }}', function (result) {
						if (result)
							AskTrackAuthorization();					
					});
				}
		}
    	});
});
function AskTrackAuthorization(){
	$.ajax({
		type: "POST", 
		url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php", 
		data:{
			action: "ask_track_authorization",
		},
		dataType: 'json',
		error: function(request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function(data) { 
			//console.log(data);
			if (!data.result.success) {
				$('#div_alert').showAlert({message: data.result.msg, level: 'danger'});
			} else 	{
				switch(data.result.result.status){
					case "unknown":
						$('#div_alert').showAlert({message: "Tu n'as pas validé à temps l'application, merci de re-sauvgarder", level: 'danger'});
						break;
						
					case "pending":
						$('#div_alert').showAlert({message: "Tu n'es toujours pas allé valider l'application sur la Freebox Server", level: 'danger'});
						break;
						
					case "timeout":
						$('#div_alert').showAlert({message: "Tu n'as pas validé à temps l'application, merci de re-sauvgarder", level: 'danger'});
						break;
						
					case "granted":
						//console.log("Accès Granted !");
							TryAPI();
						break;
						
					case "denied":
						$('#div_alert').showAlert({message: "Tu as refusé la demande d'autorisation, merci de cliquer sur : ETAPE 1", level: 'danger'});
						break;
					default:
						$('#div_alert').showAlert({message: "REST OK : track_authorization -> Error 4 : Inconnue", level: 'danger'});
						break;
				}
			}
		}
	});
}
function TryAPI(){
	$.ajax({
		type: "POST", 
		url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php", 
		data: {
			action	: "SearchReseau",
		},
		dataType: 'json',
		error: function(request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function(data) { 
			//console.log(data);
			//var fbxRetour= JSON.parse(data.result);
			if (!data.result) {
			   $('#div_alert').showAlert({message: "Problème d'enregistrement avec l'API.", level: 'danger'});
			   return;
			}else{
				//console.log("Test de L'API ... :");
				var messageOut="L'application est validée et peut être utilisée. Vous avez fini la configuration.<br>";
				messageOut+="Toutefois, il faut activer manuellement une option si vous désirez redémarrer la Freebox depuis Jeedom. Il faut vous rendre sur : mafreebox.free.fr <br>";
				messageOut+="Et suivre les étapes suivantes : Cliquez sur : <b>Paramètres de la Freebox</b> -> <b>Gestion des accès</b><br>";
				messageOut+="Onglet : <b>Applications</b> -> Puis cliquez sur la fenêtre d'édition sur la droite (A gauche de la poubelle) au nom de votre application Jeedom<br>";
				messageOut+="Cochez : <b>Modification des réglages de la Freebox</b> et cliquez sur : <b>OK</b>. Vous pouvez fermer le site.";
				
				$('#div_alert').showAlert({message: messageOut, level: 'success'});
			}
		}
	});
}
function sendToBdd(jsonParser){
	var fbx_app_token	= jsonParser.result.app_token;
	var fbx_track_id	= jsonParser.result.track_id;
	$.ajax({
		type: "POST", 
		url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php", 
		data: {
		    action		: "sendToBdd",
		    app_token	: fbx_app_token,
				track_id	: fbx_track_id
		},
		dataType: 'json',
		error: function(request, status, error) {
				handleAjaxError(request, status, error);
		},
		success: function(data) { 
			if (!data) {
				$('#div_alert').showAlert({message: data, level: 'danger'});
		 		return;
			}
		}
	});
}
