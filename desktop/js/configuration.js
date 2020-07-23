$('.FreeboxAppaire').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
		data: {
			action: "connect",
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {

			if (!data.result.success) {
				$('#div_alert').showAlert({
					message: data.result.msg,
					level: 'danger'
				});
				if (data.result.error_code == "new_apps_denied")
					$('#div_alert').append(".<br>Pour activer l'option, il faut se rendre dans : mafreebox.free.fr -> Paramètres de la Freebox -> Gestion des accès <br> Et cocher : <b>Permettre les nouvelles demandes d'associations</b>  -> Appliquer<br>De nouveau, cliquez sur <b>Etape 1</b>");
				return;
			} else {
				sendToBdd(data.result);
				bootbox.confirm('{{Valider l’application directement sur l’écran de la Freebox avant de cliquer sur Ok}}', function (result) {
					if (result)
						AskTrackAuthorization();
				});
			}
		}
	});
});

function AskTrackAuthorization() {
	$.ajax({
		type: "POST",
		url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
		data: {
			action: "ask_track_authorization",
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			//console.log(data);
			if (!data.result.success) {
				$('#div_alert').showAlert({
					message: data.result.msg,
					level: 'danger'
				});
			} else {
				switch (data.result.result.status) {
					case "unknown":
						$('#div_alert').showAlert({
							message: "l'application p'as pas validé à temps, merci de re-sauvgarder",
							level: 'danger'
						});
						break;

					case "pending":
						$('#div_alert').showAlert({
							message: "L'application n'as toujours pas été validée sur la Freebox Server",
							level: 'danger'
						});
						break;

					case "timeout":
						$('#div_alert').showAlert({
							message: "l'application p'as pas validé à temps, merci de re-sauvgarder",
							level: 'danger'
						});
						break;

					case "granted":
						//console.log("Accès Granted !");
						TryAPI();
						break;

					case "denied":
						$('#div_alert').showAlert({
							message: "La demande d'autorisation a été refusée, merci de cliquer sur : Appairage",
							level: 'danger'
						});
						break;
					default:
						$('#div_alert').showAlert({
							message: "REST OK : track_authorization -> Error 4 : Inconnue",
							level: 'danger'
						});
						break;
				}
			}
		}
	});
}

function TryAPI() {
	$.ajax({
		type: "POST",
		url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
		data: {
			//action: "Searchnetwork",
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			//console.log(data);
			//var fbxRetour= JSON.parse(data.result);
			if (!data.result) {
				$('#div_alert').showAlert({
					message: "Problème d'enregistrement avec l'API.",
					level: 'danger'
				});
				return;
			} else {
				//console.log("Test de L'API ... :");
				var messageOut = "L’application est validée et peut être utilisée. La configuration est terminée.<br>";
				messageOut += "Il faut modifier les droits d’accès pour l’application dans l’OS de la Freebox afin d’avoir accès à toute ces fonctionnalités.<br>";
				messageOut += "Suivre les infos indiquées dans le paragraphe « Appairage » de la documentation du plugin.<br>";
				$('#div_alert').showAlert({
					message: messageOut,
					level: 'success'
				});
			}
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
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
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