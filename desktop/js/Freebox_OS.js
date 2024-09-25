
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
				url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
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
						$.fn.showAlert({
							message: data.result,
							level: 'danger'
						});
						return;
					}
					$.fn.showAlert({
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
	$.fn.showAlert({
		message: '{{Recherche des Équipements standards}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: true,
		url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
		data: {
			action: 'SearchArchi'
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$.fn.showAlert({
				message: '{{Erreur recherche des Équipements standards}}',
				level: 'danger'
			});
		},
		success: function (data) {
			$.fn.showAlert({
				message: "{{Opération réalisée avec succès. Appuyez sur Ctrl + F5 (sur Mac CMD + R) si votre écran ne s'est pas actualisé}}",
				level: 'success'

			});
			window.location.reload();
		}
	});

});

$('.eqLogicAction[data-action=control_parental]').on('click', function () {
	$.fn.showAlert({
		message: '{{Recherche Contrôle Parental}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: true,
		url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
		data: {
			action: 'SearchParental'
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$.fn.showAlert({
				message: '{{Erreur recherche Contrôle Parental}}',
				level: 'danger'
			});
		},
		success: function (data) {
			$.fn.showAlert({
				message: "{{Opération réalisée avec succès.Appuyez sur Ctrl + F5 (sur Mac CMD + R) si votre écran ne s'est pas actualisé}}",
				level: 'success'

			});
			window.location.reload();
		}
	});

});

$('.eqLogicAction[data-action=search_debugTile]').on('click', function () {
	$.fn.showAlert({
		message: '{{Recherche Debug Tiles}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: true,
		url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
		data: {
			action: 'SearchDebugTile'
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$.fn.showAlert({
				message: '{{Erreur recherche Debug Tiles}}',
				level: 'danger'
			});
		},
		success: function (data) {
			$.fn.showAlert({
				message: "{{Opération réalisée avec succès. Vous pouvez télécharger les logs}}",
				level: 'success'

			});
			window.location.reload();
		}
	});

});

$('.eqLogicAction[data-action=tile]').on('click', function () {
	$.fn.showAlert({
		message: '{{Recherche des Tiles}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: true,
		url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
		data: {
			action: 'SearchTile'
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$.fn.showAlert({
				message: '{{Erreur recherche des Tiles}}',
				level: 'danger'
			});
		},
		success: function (data) {
			$.fn.showAlert({
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
	$.fn.showAlert({
		message: '{{Recherche des commandes}}',
		level: 'warning'
	});
	$.ajax({
		type: 'POST',
		async: false,
		url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
		data: {
			action: 'Search',
			search: $('.eqLogicAttr[data-l1key=configuration][data-l2key=logicalID]').value()
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			$.fn.showAlert({
				message: '{{Erreur recherche des commandes}}',
				level: 'danger'
			});

		},
		success: function (data) {
			$.fn.showAlert({
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
		case 'management':
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
		case 'management':
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
  	tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande information liée}}">'
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
	tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>'; 
	tr += '</td>';
	tr += '<td>'
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure" title="{{Configuration avancée}}"><i class="fas fa-cogs"></i></a> '
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
	}
	tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
	tr += '</tr>'
	if (jeeFrontEnd.jeedomVersion.substr(0, 3) < 4.4 && typeof jQuery === 'function') {      
		/* 
			à supprimer lorsque le require sera >= 4.4 
		*/
		$('#table_cmd tbody').append(tr)
		var tr = $('#table_cmd tbody tr').last()
		jeedom.eqLogic.buildSelectCmd({
		  id:  $('.eqLogicAttr[data-l1key=id]').value(),
		  filter: {type: 'info'},
			error: function (error) {
				$.fn.showAlert({
				  message: error.message, level: 'danger'
			  })
		  },
		  success: function (result) {
			tr.find('.cmdAttr[data-l1key=value]').append(result)
			tr.setValues(_cmd, '.cmdAttr')
			jeedom.cmd.changeType(tr, init(_cmd.subType))
		  }
		})
		
	  } else {
		/* 
			garder que cette partie lorsque le require sera >= 4.4 
		*/
		let newRow = document.createElement('tr')
		newRow.innerHTML = tr
		newRow.addClass('cmd')
		newRow.setAttribute('data-cmd_id', init(_cmd.id))
		document.getElementById('table_cmd').querySelector('tbody').appendChild(newRow)
		jeedom.eqLogic.buildSelectCmd({
		  id: document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue(),
		  filter: { type: 'info' },
		  error: function(error) {
			jeedomUtils.showAlert({ message: error.message, level: 'danger' })
		  },
		  success: function(result) {
			newRow.querySelector('.cmdAttr[data-l1key="value"]').insertAdjacentHTML('beforeend', result)
			newRow.setJeeValues(_cmd, '.cmdAttr')
			jeedom.cmd.changeType(newRow, init(_cmd.subType))
		  }
		})
	  }


	/*$('#table_cmd tbody').append(tr);
	var tr = $('#table_cmd tbody tr').last();
	jeedom.eqLogic.buildSelectCmd({
		id: $('.eqLogicAttr[data-l1key=id]').value(),
		filter: { type: 'info' },
		error: function (error) {
			$.fn.showAlert({
			message: error.message, level: 'danger' });
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
   
	jeedom.cmd.changeType($('#table_cmd tbody tr').last(), init(_cmd.subType));*/

}
function setupCron($icon,$icon_type) {
	$.ajax({	
		type: "POST",
		url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
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
			$('.ADD_EQLOGIC').hide();
			$('#CRON_TILES').show();
			$('#CRON_TILES_INFO').hide();
			switch ($icon) {
				case 'disk':
					$('.ADD_EQLOGIC').show();
					break;
				case 'network':
				case 'networkwifiguest':
					$('#CRON_TILES').show();
					$('#CRON_TILES_INFO').hide();
					$('.IPV').show();
					$('.ADD_EQLOGIC').show();
					break;
				case 'homeadapters':
					$('.IPV').hide();
					$('#CRON_TILES').show();
					$('#CRON_TILES_INFO').hide();
					$('.ADD_EQLOGIC').show();
					break;
				case 'airmedia':
				case 'connexion':
				case 'downloads':
				case 'LCD':
				case 'system':
				case 'freeplug':
				case 'phone':
				case 'wifi':
				case 'parental':
				case 'player':
				case 'netshare':
				case 'management':
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
