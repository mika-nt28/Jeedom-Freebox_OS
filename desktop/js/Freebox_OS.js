
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

/*
* Permet la réorganisation des commandes dans l'équipement
*/
$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

/*
* Fonction spécifique Freebox
*/

$('.cmdAction[data-action=add]').on('click', function() {
	addCmdToTable()
	$('.cmd:last .cmdAttr[data-l1key=type]').trigger('change')
	modifyWithoutSave = true
  })

$('body').off('Freebox_OS::camera').on('Freebox_OS::camera', function (_event, _options) {
	var camera = jQuery.parseJSON(_options);


	//bootbox.confirm("{{Une caméra Freebox a été détectée (<b>" + camera.name + "</b>)<br>Voulez-vous l’ajouter au Plugin Caméra ?}}", function (result) {
		//if (result) {
			$.ajax({
				type: 'POST',
				url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
				data: {
					action: 'createCamera',
					name: camera.name,
					id: camera.id,
					room: camera.room,
					url: camera.url
				},
				dataType: 'json',
				global: true,
				error: function (request, status, error) {},
				success: function (data) {
					if (data.state != 'ok') {
						$('#div_alert').showAlert({
							message: data.result,
							level: 'danger'
						});
						return;
					}
					$('#div_alert').showAlert({
						message: "{{La caméra (<b>" + camera.name + "</b>) a été ajoutée avec succès}}",
						level: 'success'
					});
					window.location.reload();
				}
			});
		//}
	//});
});

$('.authentification').on('click', function () {
    $('#md_modal').dialog({title: "{{Authentification Freebox}}"});
    $('#md_modal').load('index.php?v=d&plugin=Freebox_OS&modal=authentification').dialog('open');
})

$('.health').on('click', function () {
    $('#md_modal').dialog({title: "{{Santé Freebox}}"});
    $('#md_modal').load('index.php?v=d&plugin=Freebox_OS&modal=health').dialog('open');
})

$('.eqLogicAction[data-action=eqlogic_standard]').on('click', function () {
	$('#div_alert').showAlert({
		message: '{{Recherche des <b>Equipements standards</b>}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: true,
		url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
		data: {
			action: 'SearchArchi'
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$('#div_alert').showAlert({
				message: '{{Erreur recherche des <b>Equipements standards</b>}}',
				level: 'danger'
			});
		},
		success: function (data) {
			$('#div_alert').showAlert({
				message: "{{Opération réalisée avec succès. Appuyez sur Ctrl + F5 (sur Mac CMD + R) si votre écran ne s'est pas actualisé}}",
				level: 'success'

			});
			window.location.reload();
		}
	});

});

$('.eqLogicAction[data-action=control_parental]').on('click', function () {
	$('#div_alert').showAlert({
		message: '{{Recherche <b>Contrôle Parental</b>}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: true,
		url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
		data: {
			action: 'SearchParental'
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$('#div_alert').showAlert({
				message: '{{Erreur recherche <b>Contrôle Parental</b>}}',
				level: 'danger'
			});
		},
		success: function (data) {
			$('#div_alert').showAlert({
				message: "{{Opération réalisée avec succès.Appuyez sur Ctrl + F5 (sur Mac CMD + R) si votre écran ne s'est pas actualisé}}",
				level: 'success'

			});
			window.location.reload();
		}
	});

});

$('.eqLogicAction[data-action=search_debugTile]').on('click', function () {
	$('#div_alert').showAlert({
		message: '{{Recherche <b>Debug Tiles</b>}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: true,
		url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
		data: {
			action: 'SearchDebugTile'
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$('#div_alert').showAlert({
				message: '{{Erreur recherche <b>Debug Tiles</b>}}',
				level: 'danger'
			});
		},
		success: function (data) {
			$('#div_alert').showAlert({
				message: "{{Opération réalisée avec succès. Vous pouvez télécharger les logs}}",
				level: 'success'

			});
			window.location.reload();
		}
	});

});

$('.eqLogicAction[data-action=tile]').on('click', function () {
	$('#div_alert').showAlert({
		message: '{{Recherche des <b>Tiles</b>}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: true,
		url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
		data: {
			action: 'SearchTile'
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$('#div_alert').showAlert({
				message: '{{Erreur recherche des <b>Tiles</b>}}',
				level: 'danger'
			});
		},
		success: function (data) {
			$('#div_alert').showAlert({
				message: "{{Opération réalisée avec succès. Appuyez sur Ctrl + F5 (sur Mac CMD + R) si votre écran ne s'est pas actualisé}}",
				level: 'success'

			});
			if (!data.result) {
				//window.location.reload();
			}
		}
	});

});

$('.Equipement').on('click', function () {
	$('#div_alert').showAlert({
		message: '{{Recherche des <b>commandes</b>}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: false,
		url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
		data: {
			action: 'Search',
			search: $('.eqLogicAttr[data-l1key=configuration][data-l2key=logicalID]').value()
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$('#div_alert').showAlert({
				message: '{{Erreur recherche des <b>commandes</b>}}',
				level: 'danger'
			});

		},
		success: function (data) {
			$('#div_alert').showAlert({
				message: "{{Opération réalisée avec succès.}}",
				level: 'success'

			});
			location.reload();
		}
	});
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=logicalID]').on('change', function () {
	$icon = $('.eqLogicAttr[data-l1key=configuration][data-l2key=logicalID]').value();
	$icon_type = $('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value();
	$icon_type2 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=type2]').value();
	setupCron($icon,$icon_type);
	switch ($icon) {
		case 'airmedia':
		case 'connexion':
		case 'downloads':
		case 'homeadapters':
		case 'LCD':
		case 'system':
		case 'disk':
		case 'phone':
		case 'wifi':
		case 'player':
		case 'network':
		case 'netshare':
		case 'networkwifiguest':
			$('#img_device').attr("src", 'plugins/Freebox_OS/core/img/' + $icon + '.png');
			break;
		default:
			$('#img_device').attr("src", 'plugins/Freebox_OS/core/img/' + $icon_type + '.png');
			break;
	}
});
$('.eqLogicAttr[data-l1key=configuration][data-l2key=type2]').on('change', function () {
	$icon_type2 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=type2]').value();
	$icon_type = $('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value();
	switch ($icon_type2) {
		case 'kfb':
			$('#img_device').attr("src", 'plugins/Freebox_OS/core/img/' + $icon_type + '.png');
			break;
		case 'dws':
		case 'plug':
		case 'opener':
		case 'basic_shutter':
		case 'shutter':
			$('#img_device').attr("src", 'plugins/Freebox_OS/core/img/' + $icon_type2 + '.png');
			break;
	}

});

//setupPage();
/*
* Fonction permettant l'affichage des commandes dans l'équipement
*/
function addCmdToTable(_cmd) {
	if (!isset(_cmd)) {
		var _cmd = {};
	}
	if (!isset(_cmd.configuration)) {
		_cmd.configuration = {};
	}
	if (init(_cmd.logicalId) == 'refresh') {
		return;
	}
	var template = $('.eqLogicAttr[data-l1key=configuration][data-l2key=logicalID]').value();
	switch (template) {
		case 'airmedia':
		case 'connexion':
		case 'disk':
		case 'downloads':
		case 'homeadapters':
		case 'LCD':
		case 'network':
		case 'netshare':
		case 'networkwifiguest':
		case 'system':
		case 'wifi':
		case 'phone':
			$('.Equipement').show();
			break;
		default:
			$('.Equipement').hide();
			break;
	}
	var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
	tr += '<td class="hidden-xs">'
  	tr += '<span class="cmdAttr" data-l1key="id"></span>'
  	tr += '</td>'
  	tr += '<td>'
  	tr += '<div class="input-group">'
  	tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  	tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  	tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  	tr += '</div>'
  	tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
  	tr += '<option value="">{{Aucune}}</option>'
  	tr += '</select>'
  	tr += '</td>'
  	tr += '<td>'
  	tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
  	tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
  	tr += '</td>'
	tr += '<td>'
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
  	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
	if ((init(_cmd.type) == 'action' && init(_cmd.subType) == 'slider')) {
		tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="invertslide"/>{{Inverser Curseur}}</label> ';
	}
  	tr += '<div style="margin-top:7px;">'
  	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  	tr += '</div>'
  	tr += '</td>'
  	tr += '<td>'
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>'
	}
	tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
	tr += '</tr>'
	$('#table_cmd tbody').append(tr);
	var tr = $('#table_cmd tbody tr').last();
	jeedom.eqLogic.builSelectCmd({
		id: $('.eqLogicAttr[data-l1key=id]').value(),
		filter: { type: 'info' },
		error: function (error) {
			$('#div_alert').showAlert({ message: error.message, level: 'danger' });
		},
		success: function (result) {
			tr.find('.cmdAttr[data-l1key=value]').append(result);
			tr.setValues(_cmd, '.cmdAttr');
			jeedom.cmd.changeType(tr, init(_cmd.subType));
		}
	});
	 $('#table_cmd tbody tr').last().setValues(_cmd, '.cmdAttr');
	if (isset(_cmd.type)) {
		$('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
	}
   
	jeedom.cmd.changeType($('#table_cmd tbody tr').last(), init(_cmd.subType));

}
function setupCron($icon,$icon_type) {
	$.ajax({
		type: "POST",
		url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
		data: {
			action: "GetSettingTiles",
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			result = data.result.CronTiles;
			console.log('Type : ' + $icon +' Type 2 : ' + $icon_type);
			if ($icon_type === 'VM'|| $icon_type === 'parental'|| $icon_type === 'player'|| $icon_type === 'freeplug') {
				$icon = $icon_type ;
			}
			$('.IPV').hide();
			$('#CRON_TILES').show();
			$('#CRON_TILES_INFO').hide();
			switch ($icon) {
				case 'network':
				case 'networkwifiguest':
					$('#CRON_TILES').show();
					$('#CRON_TILES_INFO').hide();
					$('.IPV').show();
					break;
				case 'airmedia':
				case 'connexion':
				case 'downloads':
				case 'homeadapters':
				case 'LCD':
				case 'system':
				case 'disk':
				case 'freeplug':
				case 'phone':
				case 'wifi':
				case 'parental':
				case 'player':
				case 'netshare':
				case 'VM':
					$('.IPV').hide();
					$('#CRON_TILES').show();
					$('#CRON_TILES_INFO').hide();
					break;
				default:
					$('.IPV').hide();
					console.log('CRON TILES : ' + result)
					if (result == "1") {
						$('#CRON_TILES').hide();
						$('#CRON_TILES_INFO').show();
					}
					break;
			}
		}
	});
}
/*
function setupPage() {
	if (!divEquipements) {
		$(".eqLogicThumbnailDisplay .divEquipements").addClass('freeOSHidenDiv');
	}
	if (!divTiles) {
		$(".eqLogicThumbnailDisplay .divTiles").addClass('freeOSHidenDiv');
	}
	if (!divParental) {
		$(".eqLogicThumbnailDisplay .divParental").addClass('freeOSHidenDiv');
	}

	$.ajax({
		type: "POST",
		url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
		data: {
			action: "GetBox",
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			result = data.result.Type_box_tiles;

			if (result !== "OK") {
				$(".titleAction").addClass('freeOSHidenDiv');
			}
		}
	});
}*/