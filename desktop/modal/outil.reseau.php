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
                <div class="EnregistrementDisplay">
                    <br />
                </div>
                <div class="table-responsive">
                    <table id="table_cmd" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <td>{{ID}}</td>
                                <td style="width: 70px;">{{Activer}}</td>
                                <td>{{IP source}}</td>
                                <td style="width: 40px;">{{Port de début}}</td>
                                <td style="width: 40px;">{{Port de Fin}}</td>
                                <td>{{Type}}</td>
                                <td>{{IP Destination}}</td>
                                <td style="width: 40px;">{{Port de destination}}</td>
                                <td>{{Commentaires}}</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="WakeOnLAN">
                <br />
                <div class="EnregistrementDisplaywakeonlan">
                    <br />
                </div>
                <div class="col-lg-7">
                    <form class="form-horizontal">
                        <fieldset>
                            <div class="form-group ">
                                <label class="col-sm-3 control-label">{{Mot de Passe}}</label>
                                <div class="col-xs-11 col-sm-7">
                                    <input type="text" class="eqLogicAttr form-control redirPort" data-l1key="password" placeholder="{{Password}}" />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label class="col-sm-3 control-label">{{Envoyer la commande}}</label>
                                <div class="col-xs-11 col-sm-7">
                                    <a class="btn btn-sm btn-success lanEqLogic" data-action="wakeonlan"><i class="fas fa-check-circle"></i> {{Réveiller}}
                                    </a>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </fieldset>
</form>

<script>
    $.ajax({
        type: 'POST',
        async: false,
        url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
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
    $('.lanEqLogic[data-action=PortForwarding]').on('click', function() {
        $.ajax({
            type: 'POST',
            async: false,
            url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
            data: {
                action: 'PortForwarding',
                id: SelectEquipement,
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
    });

    function addRedirToTable(_cmd) {
        var tr = $('<tr class="redir">');
        tr.append($('<td style="width:30px;">')
            .append($('<input style="width:30px;" class="redirPort" data-l1key="id" disabled/>'))
        );
        tr.append($('<td style="width:100px;">')
            .append($('<select style="width:100px;" class="redirPort form-control input-sm" data-l1key="enabled">')
                .append($('<option value="1">').text('Activer'))
                .append($('<option value="0">').text('Désactiver'))
                .append($('<option value="3">').text('Supprimer'))
                .append($('</select>')))
        );

        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="src_ip" disabled/>'))
        );
        tr.append($('<td style="width:150px;">')
            .append($('<input style="width:150px;"class="redirPort" data-l1key="wan_port_start" disabled/>'))
        );
        tr.append($('<td style="width:150px;">')
            .append($('<input style="width:150px;" class="redirPort" data-l1key="wan_port_end" disabled/>'))
        );
        tr.append($('<td>')
            .append($('<select style="width:80px; class="redirPort form-control input-sm" data-l1key="ip_proto" />')
                .append($('<option value=tcp>').text('TCP'))
                .append($('<option value=udp>').text('UDP'))
                .append($('</select>')))
        );
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="lan_ip" disabled/>'))
        );
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="lan_port" disabled/>')));
        tr.append($('<td>')
            .append($('<input class="redirPort" data-l1key="comment" disabled/>')));
        tr.append($('<td style="width:250px;">')
            .append($('<a class="btn btn-sm btn-success redirPort fas fa-check-circle" data-action="UpdatePortForwarding">')
                .text('{{Mise à jour}}')));
        $('#table_cmd tbody').append(tr);
        $('#table_cmd tbody tr:last').setValues(_cmd, '.redirPort');
    }
    $('.lanEqLogic[data-action=wakeonlan]').on('click', function() {
        $.ajax({
            type: 'POST',
            async: false,
            url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
            data: {
                action: 'WakeOnLAN',
                id: SelectEquipement,
                password: $('.redirPort[data-l1key=password]').val(),
            },
            dataType: 'json',
            global: false,
            error: function(request, status, error) {},
            success: function(data) {
                if (data.state != 'ok') {
                    $('.EnregistrementDisplaywakeonlan').showAlert({
                        message: data.result,
                        level: 'danger'
                    });
                    return;
                } else {
                    if (data.result == true) {
                        $('.EnregistrementDisplaywakeonlan').showAlert({
                            message: "Commande envoyée avec succès",
                            level: 'success'
                        });
                    } else if (data.result != true) {
                        $('.EnregistrementDisplaywakeonlan').showAlert({
                            message: "Wake on LAN : ".data.result,
                            level: 'warning'
                        });
                    }
                }
            }
        });
    });
    $('.redirPort[data-action=UpdatePortForwarding]').on('click', function() {
        $.ajax({
            type: 'POST',
            async: false,
            url: 'plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php',
            data: {
                action: 'UpdatePortForwarding',
                id: $(this).closest('.redir').find('.redirPort[data-l1key=id]').val(),
                enabled: $(this).closest('.redir').find('.redirPort[data-l1key=enabled]').val(),
                id2: SelectEquipement,
            },
            dataType: 'json',
            global: false,
            error: function(request, status, error) {},
            success: function(data) {
                //console.log(data)
                if (data.state != 'ok') {
                    $('.EnregistrementDisplay').showAlert({
                        message: data.result,
                        level: 'danger'
                    });
                    return;
                } else {
                    if (data.result == true) {
                        $('.EnregistrementDisplay').showAlert({
                            message: "Commande envoyée avec succès ",
                            level: 'success'
                        });
                    }
                }

            }
        });
    });
</script>