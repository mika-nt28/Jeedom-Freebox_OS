$("#table_cmd").sortable({
    axis: "y",
    cursor: "move",
    items: ".cmd",
    placeholder: "ui-state-highlight",
    tolerance: "intersect",
    forcePlaceholderSize: true
});
$('body').off('Freebox_OS::camera').on('Freebox_OS::camera', function (_event, _options) {
    var camera = jQuery.parseJSON(_options);
    bootbox.confirm("{{Une caméra Freebox a été détectée (<b>" + camera.name + "</b>)<br>Voulez-vous l’ajouter au Plugin Caméra ?}}", function (result) {
        if (result) {
            $.ajax({
                type: 'POST',
                url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
                data: {
                    action: 'createCamera',
                    name: camera.name,
                    id: camera.id,
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
                }
            });
        }
    });

});
$('.MaFreebox').on('click', function () {
    $('#md_modal').dialog({
        title: "{{Parametre Freebox}}",
        height: 700,
        width: 850
    });
    $('#md_modal').load('index.php?v=d&modal=MaFreebox&plugin=Freebox_OS&type=Freebox_OS').dialog('open');
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
                message: '{{Opération réalisée avec succès, penser à rafraichir la page}}',
                level: 'success'
            });
            //location.reload();
        }
    });

});
$('.Equipement').on('click', function () {
    $.ajax({
        type: 'POST',
        async: false,
        url: 'plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php',
        data: {
            action: 'Search' + $('.eqLogicAttr[data-l1key=logicalId]').val()
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {},
        success: function (data) {
            location.reload();
        }
    });
});

function addCmdToTable(_cmd) {
    if (init(_cmd.logicalId) == 'refresh') {
        return;
    }
    var inverse = $('<span>');
    switch ($('.eqLogicAttr[data-l1key=logicalId]').val()) {
        case 'Home Adapters':
            $('.Equipement').show();
            $('.Add_Equipement').hide();
            break;
        case 'HomeAdapters':
            $('.Equipement').show();
            $('.Add_Equipement').hide();
            break;
        case 'Reseau':
            $('.Equipement').show();
            $('.Add_Equipement').show();
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
            $('.Add_Equipement').hide();
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
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '" virtualAction="' + init(_cmd.configuration.virtualAction) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display: none;" ></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 250px;" placeholder="{{Nom}}"></td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="margin-bottom : 5px;" />';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + ' "  disabled></span>';
    tr += '</td>';
    tr += '<td>';
    if (_cmd.subType == "numeric") {
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;display:inline-block;margin-right:5px;">';
    }
    tr += '</td>';
    tr += '<td>';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
    if (_cmd.subType == "numeric" || _cmd.subType == "binary") {
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
    }
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span> ';
    tr += '</td>';

    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr').last().setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr').last(), init(_cmd.subType));

}
