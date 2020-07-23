<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
    <fieldset>
        <ul class="nav nav-tabs expertModeVisible" role="tablist">
            <li class="active"><a href="#PortForwarding" role="tab" data-toggle="tab">{{Géstion des ports}}</a></li>
            <li><a href="#WakeOnLAN" role="tab" data-toggle="tab">{{Wake on LAN}}</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="PortForwarding">
                <br />
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
                            <th>{{Commentaires}}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <a class="btn btn-success lanEqLogic" data-action="portForwarding"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
            </div>
            <div class="tab-pane" id="WakeOnLAN">
                <br />
                <legend>Wake on LAN</legend>
                <a class="btn btn-success lanEqLogic" data-action="wakeonlan"><i class="fas fa-check-circle"></i> {{Reveiller}}</a>
            </div>
        </div>
    </fieldset>
</form>

<script>
    $.ajax({
        type: 'POST',
        async: false,
        url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
        data: {
            action: 'PortForwarding',
            id: SelectEquipement
        },
        dataType: 'json',
        global: false,
        error: function(request, status, error) {},
        success: function(data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({
                    message: data.result,
                    level: 'danger'
                });
                return;
            }
            for (var i in data.result) {
                addRedirToTable(data.result[i])
            }
        }
    });

    function addRedirToTable(_cmd) {
        var tr = $('<tr class="redir">');
        tr.append($('<td>')
            .append($('<select class="redirPort form-control input-sm" data-l1key="enabled">')
                .append($('<option value="1">').text('Activer'))
                .append($('<option value="0">').text('Desactiver'))
                .append($('</select>'))));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="src_ip" />')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="wan_port_start" />')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="wan_port_end" />')));
        tr.append($('<td>')
            .append($('<select class="redirPort form-control input-sm" data-l1key="ip_proto">')
                .append($('<option value=tcp>').text('TCP'))
                .append($('<option value=udp>').text('UDP'))));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="lan_ip"/>')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="lan_port"/>')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="comment"/>')));
        $('#table_cmd tbody').append(tr);
        $('#table_cmd tbody tr:last').setValues(_cmd, '.redirPort');
    }
    $('.lanEqLogic[data-action=wakeonlan]').on('click', function() {
        $.ajax({
            type: 'POST',
            async: false,
            url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
            data: {
                action: 'WakeOnLAN',
                id: SelectEquipement,
            },
            dataType: 'json',
            global: false,
            error: function(request, status, error) {},
            success: function(data) {}
        });
    });
    $('.lanEqLogic[data-action=portForwarding]').on('click', function() {
        $.ajax({
            type: 'POST',
            async: false,
            url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
            data: {
                action: 'AddPortForwarding',
                enabled: $(this).closest('.redir').find('.redirPort[data-l1key=enabled]').val(),
                comment: $(this).closest('.redir').find('.redirPort[data-l1key=comment]').val(),
                lan_port: $(this).closest('.redir').find('.redirPort[data-l1key=lan_port]').val(),
                wan_port_end: $(this).closest('.redir').find('.redirPort[data-l1key=wan_port_end]').val(),
                wan_port_start: $(this).closest('.redir').find('.redirPort[data-l1key=wan_port_start]').val(),
                lan_ip: $(this).closest('.redir').find('.redirPort[data-l1key=lan_ip]').val(),
                p_proto: $(this).closest('.redir').find('.redirPort[data-l1key=p_proto]').val(),
                src_ip: $(this).closest('.redir').find('.redirPort[data-l1key=src_ip]').val(),
            },
            dataType: 'json',
            global: false,
            error: function(request, status, error) {},
            success: function(data) {}
        });
    });
</script>