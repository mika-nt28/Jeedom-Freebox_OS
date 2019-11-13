<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
   <form class="form-horizontal">
            <fieldset>
				<ul class="nav nav-tabs expertModeVisible" role="tablist">
					<li class="active"><a href="#WakeOnLAN" role="tab" data-toggle="tab">{{Wake on LAN}}</a></li>
					<li><a href="#PortForwarding" role="tab" data-toggle="tab">{{Gestion des ports}}</a></li>
				</ul>
				<div class="tab-content">             	
					<div class="tab-pane active" id="WakeOnLAN">
						<br/>
						<legend>Wake on LAN</legend>
						<a class="btn btn-success lanEqLogic" data-action="wakeonlan"><i class="fa fa-check-circle"></i> {{Reveiller}}</a>
					</div>
					<div class="tab-pane" id="PortForwarding">
						<br/>
						<legend>{{Redirection des port}}</legend>
						<table id="table_cmd" class="table table-bordered table-condensed">
							<thead>
								<tr>
									<th>{{Activer}}</th>
									<th>{{Ip source}}</th>
									<th>{{Debut port source}}</th>
									<th>{{Fin port source}}</th>
									<th>{{Type}}</th>
									<th>{{Ip de destination}}</th>
									<th>{{Port de destination}}</th>
									<th>{{Commantaire}}</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
						<a class="btn btn-success lanEqLogic" data-action="portForwarding"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
					</div>
				</div>
			</fieldset> 
        </form>

<script>
$.ajax({
	type: 'POST',            
	async: false,
	url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
	data:
		{
		action: 'PortForwarding',
		},
	dataType: 'json',
	global: false,
	error: function(request, status, error) {},
	success: function(data) {
		if (data.state != 'ok') {
			$('#div_alert').showAlert({message: data.result, level: 'danger'});
			return;
		}
		for (var i in data.result) {
			addRedirToTable(data.result[i])
		}
	}
});
function addRedirToTable(_cmd) {
	var tr =$('<tr class="redir">');
	tr.append($('<td>')
		.append($('<select class="redirPort form-control input-sm" data-l1key="enabled" style="display : none;">')
			.append($('<option value=true>').text('Activer'))
			.append($('<option value=false>').text('Desactiver'))));
	tr.append($('<td>')
		.append($('<input class="redirPort" data-l1key="src_ip" />')));
	tr.append($('<td>')
		.append($('<input class="redirPort" data-l1key="wan_port_start" />')));
	tr.append($('<td>')
		.append($('<input class="redirPort" data-l1key="wan_port_end" />')));
	tr.append($('<td>')
		.append($('<select class="redirPort form-control input-sm" data-l1key="ip_proto" style="display : none;">')
			.append($('<option value=tcp>').text('TCP'))
			.append($('<option value=udp>').text('UDP'))));
	tr.append($('<td>')
		.append($('<input class="redirPort" data-l1key="lan_ip"/>')));
	tr.append($('<td>')
		.append($('<input class="redirPort" data-l1key="lan_port"/>')));
	tr.append($('<td>')
		.append($('<input class="redirPort" data-l1key="comment"/>')));
	tr.append($('<td>')
		.append($('<a class="btn btn-xs btn-success redirPort" data-action="save">')
			.append($('<i class="fa fa-check-circle">'))
			.text('{{Sauvegarder}}')));
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.redirPort');
}
$('.lanEqLogic[data-action=wakeonlan]').on('click', function() {
	$.ajax({
		type: 'POST',            
		async: false,
		url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
		data:
			{
			action: 'WakeOnLAN',
			id:SelectEquipement,
			},
		dataType: 'json',
		global: false,
		error: function(request, status, error) {},
		success: function(data) {
			}
		});
});
$('.lanEqLogic[data-action=portForwarding]').on('click', function() {
	$.ajax({
		type: 'POST',            
		async: false,
		url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
		data:
			{
			action: 'AddPortForwarding',
			enabled:$(this).closest('.redir').find('.redirPort[data-l1key=enabled]').val(),
			comment:$(this).closest('.redir').find('.redirPort[data-l1key=comment]').val(),
			lan_port:$(this).closest('.redir').find('.redirPort[data-l1key=lan_port]').val(),
			wan_port_end:$(this).closest('.redir').find('.redirPort[data-l1key=wan_port_end]').val(),
			wan_port_start:$(this).closest('.redir').find('.redirPort[data-l1key=wan_port_start]').val(),
			lan_ip:$(this).closest('.redir').find('.redirPort[data-l1key=lan_ip]').val(),
			p_proto:$(this).closest('.redir').find('.redirPort[data-l1key=p_proto]').val(),
			src_ip:$(this).closest('.redir').find('.redirPort[data-l1key=src_ip]').val(),
			},
		dataType: 'json',
		global: false,
		error: function(request, status, error) {},
		success: function(data) {
			}
		});
});
/*{
	"enabled":true,
	"comment":"",
	"id":1,
	"host":{
		"l2ident":{"id":"00:0C:29:66:4B:18","type":"mac_address"},
		"active":true,
		"id":"ether-00:0c:29:66:4b:18",
		"last_time_reachable":1445856883,
		"persistent":true,
		"names":[{"name":"Jeedom","source":"dhcp"},{"name":"JeedomRPI","source":"mdns"}],
		"vendor_name":"VMware,Inc.",
		"host_type":"workstation",
		"l3connectivities":[{
			"addr":"fe80::20c:29ff:fe66:4b18",
			"active":true,
			"reachable":true,
			"last_activity":1445856868,
			"af":"ipv6","last_time_reachable":1445856863},
			{"addr":"192.168.0.2","active":false,"reachable":false,"last_activity":1444923614,"af":"ipv4","last_time_reachable":1444923614},
			{"addr":"192.168.0.100","active":true,"reachable":true,"last_activity":1445856883,"af":"ipv4","last_time_reachable":1445856883}],
		"reachable":true,
		"last_activity":1445856883,
		"primary_name_manual":false,
		"primary_name":"Jeedom"},
	"src_ip":"0.0.0.0",
	"hostname":"Jeedom",
	"lan_port":80,
	"wan_port_end":80,
	"wan_port_start":80,
	"lan_ip":"192.168.0.100",
	"ip_proto":"tcp"
}*/
</script>