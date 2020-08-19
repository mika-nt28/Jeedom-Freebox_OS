<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
    <fieldset>
        <ul class="nav nav-tabs expertModeVisible" role="tablist">
            <li class="active"><a href="#PortForwarding" role="tab" data-toggle="tab">{{Gestion des ports}}</a></li>
            <li><a href="#WakeOnLAN" role="tab" data-toggle="tab">{{Wake on LAN}}</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="PortForwarding">
                <br />
                <table id="table_cmd" class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <td>{{Activer}}</td>
                            <td>{{Regles ID}}</td>
                            <td>{{Ip source}}</td>
                            <td>{{Debut port source}}</td>
                            <td>{{Fin port source}}</td>
                            <td>{{Type}}</td>
                            <td>{{Ip de destination}}</td>
                            <td>{{Port de destination}}</td>
                            <td>{{Commentaires}}</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane" id="WakeOnLAN">
                <br />
                <legend>Wake on LAN</legend>
                <a class="btn btn-success lanEqLogic" data-action="wakeonlan"><i class="fas fa-check-circle"></i> {{Réveiller}}</a>
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
            console.log(data)
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
            .append($('<input class="redirPort" data-l1key="id" disabled/>')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="src_ip" disabled/>')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="wan_port_start" disabled/>')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="wan_port_end" disabled/>')));
        tr.append($('<td>')
            .append($('<select class="redirPort form-control input-sm" data-l1key="ip_proto" disabled>')
                .append($('<option value=tcp>').text('TCP'))
                .append($('<option value=udp>').text('UDP'))));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="lan_ip" disabled/>')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="lan_port" disabled/>')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="comment" disabled/>')));
        tr.append($('<td>')
            .append($('<a class="btn btn-xs btn-success redirPort" data-action="AddPortForwarding">')
                .append($('<i class="fas fa-check-circle">'))
                .text('{{Sauvegarder}}')));
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
    $('.redirPort[data-action=AddPortForwarding]').on('click', function() {
        $.ajax({
            type: 'POST',
            async: false,
            url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
            data: {
                action: 'AddPortForwarding',
                id: $(this).closest('.redir').find('.redirPort[data-l1key=id]').val(),
                enabled: $(this).closest('.redir').find('.redirPort[data-l1key=enabled]').val(),
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
                } else {
                    if (data.result == true) {
                        $('#div_alert').showAlert({
                            message: "Update rule successful",
                            level: 'success'
                        });
                    }
                }

            }
        });
    });
</script>