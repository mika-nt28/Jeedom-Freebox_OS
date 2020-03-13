$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$('body').off('Freebox_OS::camera').on('Freebox_OS::camera', function (_event,_options) {
	var camera=jQuery.parseJSON(_options);
	bootbox.confirm({
		message: "{{Une camera freebox a ete détécté ("+camera.name+"), Voulez vous l'ajouter au plugin Camera?}}",
		buttons: {
			confirm: {
				label: '{{Oui}}',
				className: 'btn-success'
			},
			cancel: {
				label: '{{Non}}',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if( result){
				$.ajax({
					type: 'POST',   
					url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
					data:
					{
						action: 'createCamera',
						name:camera.name,
						id:camera.id,
						url:camera.url
					},
					dataType: 'json',
					global: true,
					error: function(request, status, error) {},
					success: function(data) {
						if (data.state != 'ok') {
							$('#div_alert').showAlert({message: data.result, level: 'danger'});
							return;
						}
						$('#div_alert').showAlert({message: "{{Camera ajouté avec sucess}}", level: 'success'});
					}
				});
			}
		}
	});
});	   
$('.MaFreebox').on('click', function() {
	$('#md_modal').dialog({
		title: "{{Parametre Freebox}}",
		height: 700,
		width: 850});
	$('#md_modal').load('index.php?v=d&modal=MaFreebox&plugin=Freebox_OS&type=Freebox_OS').dialog('open');
}); 
$('.eqLogicAction[data-action=tile]').on('click', function() {
	$.ajax({
		type: 'POST',            
		async: true,
		url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
		data:{
			action: 'SearchTile'
		},
		dataType: 'json',
		global: false,
		error: function(request, status, error) {},
		success: function(data) {
			//location.reload();
		}
	});
});
$('.Equipement').on('click', function() {
	$.ajax({
		type: 'POST',            
		async: false,
		url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
		data:{
			action: 'Search'+$('.eqLogicAttr[data-l1key=logicalId]').val()
		},
		dataType: 'json',
		global: false,
		error: function(request, status, error) {},
		success: function(data) {
			location.reload();
		}
	});
});
function addCmdToTable(_cmd) {
	var inverse = $('<span>');
	switch($('.eqLogicAttr[data-l1key=logicalId]').val()){
		case 'Home Adapters':
			$('.Equipement').show();
		break;
		case 'Reseau':
			$('.Equipement').show();
		break;
		case 'Disque':
			$('.Equipement').show();
			var inverse = $('<span>');
		break;
		case 'System':
		case 'ADSL':
		case 'AirPlay':
		case 'Downloads':
		case 'Phone':
			$('.Equipement').hide();
		break;
		default:
			$('.Equipement').hide();
			inverse.append('{{Inverser}}');
			inverse.append($('<input type="checkbox" class="cmdAttr" data-size="mini" data-label-text="{{Inverser}}" data-l1key="configuration" data-l2key="inverse"/>'));
		break;
	}
	if (!isset(_cmd)) {
        	var _cmd = {};
    	}
    	if (!isset(_cmd.configuration)) {
    	  	 _cmd.configuration = {};
	}
	var tr =$('<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">');
  	tr.append($('<td>')
		.append($('<div>')
			.append($('<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove">')))
		.append($('<div>')
			.append($('<i class="fa fa-arrows-v pull-left cursor bt_sortable">'))));
	tr.append($('<td>')
		.append($('<div>')
			.append($('<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;">'))
			.append($('<input class="cmdAttr form-control input-sm" data-l1key="name" value="' + init(_cmd.name) + '" placeholder="{{Name}}" title="Name">'))));
	tr.append($('<td>')
		  .append($('<span class="type" type="' + init(_cmd.type) + '">')
			.append(jeedom.cmd.availableType()))
		  .append($('<span class="subType" subType="'+init(_cmd.subType)+'">'))	
		  .append($('<div class="input-group">')
			.append($('<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" >')))
		.append($('<div class="input-group">')
			.append($('<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" >')))	
		
		.append($('<div>')
			.append($('<span>')
				.append('{{Historiser}}')
				.append($('<input type="checkbox" class="cmdAttr" data-size="mini" data-label-text="{{Historiser}}" data-l1key="isHistorized" checked/>')))
			.append($('</br>'))
			.append($('<span>')
				.append('{{Afficher}}')
				.append($('<input type="checkbox" class="cmdAttr" data-size="mini" data-label-text="{{Afficher}}" data-l1key="isVisible" checked/>'))))
			.append(inverse)
		  	.append($('<div>')	
				.append($('<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="{{Unité}}" title="Unité"/>'))));  
	var parmetre=$('<td>');
	if (is_numeric(_cmd.id)) {
		parmetre.append($('<a class="btn btn-default btn-xs cmdAction" data-action="test">')
			.append($('<i class="fas fa-rss">')
				.text('{{Tester}}')));
	}
	parmetre.append($('<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure">')
		.append($('<i class="fas fa-cogs">')));
	tr.append(parmetre);
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');	
	jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
