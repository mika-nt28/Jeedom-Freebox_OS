progress(0);
eqLogic_id = null;

$('.bt_Freebox_Next').off('click').on('click', function () {
    funNext();
});

$('.bt_Freebox_Previous').off('click').on('click', function () {
    funPrev();
});

$('.bt_eqlogic_standard').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Lancement}}:/fg: {{recherche des équipements standards}} ◀︎───────────");
    SearchArchi();
    progress(85);
});

$('.bt_eqlogic_tiles').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Lancement}}:/fg: {{recherche des tiles}} ◀︎───────────");
    SearchTile();
    progress(90);
});

$('.bt_eqlogic_control_parental').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Lancement}}:/fg: {{recherche des contrôles parentaux}} ◀︎───────────");
    SearchParental();
    progress(95);
});

$('.bt_Freebox_Save').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Sauvegarde des Paramètres}}:/fg: ◀︎───────────");
    ip = $('#input_freeboxIP').val();
    //VersionAPP = $('#input_freeAppVersion').val();
    VersionAPP = null;
    Categorie = $('#sel_object_default').val();
    SetSetting(ip, VersionAPP, Categorie);
});

$('.bt_Freebox_Autorisation').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Lancement}}:/fg: {{Autorisation Freebox}} ◀︎───────────");
    autorisationFreebox();
});
$('.bt_Freebox_resetAPI').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Lancement}}:/fg: {{Reset de la version API}} ◀︎───────────");
    ResetAPI();
});

$('.bt_Freebox_droitVerif').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Lancement}}:/fg: {{vérification des droits}} ◀︎───────────");
    GetSessionData();
});
$('.bt_Freebox_droitVerif_pass').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Ignorer la vérification des droits}}:/fg: ◀︎───────────");
    funNext();
});

$('.bt_Freebox_ResetConfig').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Reset de la configuration}}:/fg: ◀︎───────────");
    SetDefaultSetting();
    GetSetting();
    $('.bt_Freebox_Next').show();
});

$('.bt_Freebox_Room').on('click', function () {
    logs('info', "──────────▶︎ :fg-warning:{{Lancement}}:/fg: {{Recherche des pièces}} ◀︎───────────");
    //SearchTile_room();
});

$('.bt_Freebox_Save_room').on('click', function () {
    checkvalue = $('.checkbox_freeboxTiles:checked').val();
    if (checkvalue == null) {
        logs('info', "───▶︎ :fg-info:Cron Global Titles ::/fg: NOK");
        cron_tiles = '0';
    } else {
        logs('info', "───▶︎ :fg-info:Cron Global Titles ::/fg: OK:");
        cron_tiles = '1';
    };
    console.log('CRON TILES : ' + cron_tiles)
    SetSettingTiles(cron_tiles);
    logs('info', "──────────▶︎ :fg-warning:{{Sauvegarde des Pièces des Tiles}}:/fg: ◀︎───────────");
});


function updateMenu(objectclass) {
    $('.li_FreeboxOS_Summary.active').removeClass('active');
    $(objectclass).addClass('active');
    $('.FreeboxOS_Display').hide();
    $('.FreeboxOS_Display.' + $(objectclass).attr('data-href')).show();
}

function autorisationFreebox() {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "connect",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {

            if (!data.result.success) {
                $('div_alert').showAlert({
                    message: data.result.msg,
                    level: 'danger'
                });
                if (data.result.error_code == "new_apps_denied")
                    $('.textFreebox').text('{{L\'association de nouvelles applications est désactivée. Merci de modifier les réglages de votre Freebox et relancer ensuite l\'authentification}}');
                logs('error', "{{L\'association de nouvelles applications est désactivée ou la version du Freebox Server n'est pas correct}}");
                return;
            } else {
                sendToBdd(data.result);
                $('.textFreebox').text('{{Merci d\'appuyer sur le bouton V de votre Freebox, afin de confirmer l\'autorisation d\'accès à votre Freebox.}}');
                logs('info', '(' + data.result.error_code + ') ' + "{{Attente appuie sur le bouton V}}");
                $('.img-freeboxOS').attr('src', 'plugins/Freebox_OS/core/img/authentification.jpg');
                progress(40);
                setTimeout(AskTrackAuthorization, 3000);
            }
        }
    });
}

function SearchArchi() {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "SearchArchi",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {

        }
    });
}

function SearchTile() {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "SearchTile",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {

        }
    });
}
function SearchTile_room() { // Ligne 148
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "SearchTile_group", //Ligne 153
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            pieces = data.result.piece;
            object = data.result.objects;
            $("#table_room tr").remove();
            $('#table_room thead').append("<tr><th style=\"width: 320px\">{{Pièces Freebox}}</th><th>{{Objects Jeedom}}</th></tr>");
            for (var i = 0; i < pieces; i++) { // Ligne 164
                var piece = pieces[i];
                var tr = '<tr class="piece">';
                tr += '<td>';
                tr += '<input class="titleRoomAttr form-control" data-l1key="PieceName" value="' + piece + '" disabled/>';
                tr += '</td>';
                tr += '<td><select id="' + piece + '" class="titleRoomAttr form-control" data-l1key="object_id">' + object + '</td>';
                tr += '</tr>';
                $('#table_room tbody').append(tr);
                value = data.result.config[piece];
                $('#' + piece).val(value);
            }
        }
    });
}

function SearchParental() {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "SearchParental",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {

        }
    });
}

function sendToBdd(jsonParser) {
    var fbx_app_token = jsonParser.result.app_token;
    var fbx_track_id = jsonParser.result.track_id;
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
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
                logs('error', +data);
                return;
            }
        }
    });
}

function AskTrackAuthorization() {
    if ($('.li_FreeboxOS_Summary.active').attr('data-href') == "authentification") {

        $('.textFreebox').hide();
        $('.bt_Freebox_Next').hide();
        $('.bt_Freebox_Previous').hide();
        $('.FreeboxOS_OK').hide();
        $('.FreeboxOS_OK_NEXT').hide();

        $.ajax({
            type: "POST",
            url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
            data: {
                action: "ask_track_authorization",
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
                    logs('error', +data.result.msg);
                } else {
                    $('.textFreebox').show();
                    $('.bt_Freebox_Next').show();
                    $('.bt_Freebox_Previous').show();
                    switch (data.result.result.status) {
                        case "unknown":
                            $('.textFreebox').text('{{L\'application a un token invalide ou a été révoqué, il faut relancer l\'authentification. Merci}}');
                            logs('error', "ERREUR : " + '(' + data.result.result.status + ') ' + "{{L\'application a un token invalide ou a été révoqué, il faut relancer l\'authentification}}");
                            Good();
                            progress(-1);
                            break;
                        case "pending":
                            $('.textFreebox').text('{{Vous n\'avez pas encore validé l\'application sur la Freebox.}}');
                            setTimeout(AskTrackAuthorization, 3000);
                            break;
                        case "timeout":
                            $('.textFreebox').text('{{Vous n\'avez pas validé à temps, il faut relancer l\'authentification. Merci}}');
                            logs('error', "ERREUR : " + '(' + data.result.result.status + ') ' + "{{Vous n\'avez pas validé à temps, il faut relancer l\'authentification}}");
                            Good();
                            progress(-1);
                            break;
                        case "granted":
                            $('.textFreebox').text('{{Félicitation votre Freebox est maintenant reliée à Jeedom.}}');
                            logs('info', '(' + data.result.result.status + ') ' + "{{Félicitation votre Freebox est maintenant reliée à Jeedom}}");
                            $('.FreeboxOS_OK').show();
                            $('.FreeboxOS_OK_NEXT').show();
                            $('.FreeboxOS_Display.' + $(this).attr('rights')).show();
                            progress(45);
                            break;
                        case "denied":
                            $('.textFreebox').text('{{Vous avez refusé, il faut relancer l\'authentification. Merci}}');
                            logs('error', '(' + data.result.result.status + ') ' + "{{Vous avez refusé, il faut relancer l\'authentification}}");
                            progress(-1);
                            Good();
                            break;
                        default:
                            $('.textFreebox').text('{{REST OK : track_authorization -> Error 4 : Inconnue}}');
                            logs('error', '(' + data.result.result.status + ') ' + "{{REST OK : track_authorization -> Error 4 : Inconnue}}");
                            Good();
                            break;
                    }
                }
            }
        });
    } else {
        $('.textFreebox').show();
        $('.bt_Freebox_Next').show();
        $('.bt_Freebox_Previous').show();
        $('.FreeboxOS_OK').show();
        $('.FreeboxOS_OK_NEXT').show();
    }
}

function Good() {
    $('.bt_Freebox_Previous').hide();
    $('.bt_Freebox_NEXT').hide();
    $('.alert-info FreeboxOS_OK').text('{{Authentification réussi}}');
    logs('info', "Authentification réussi");
}

function progress(ProgressPourcent) {
    if (ProgressPourcent == -1) {
        $('#div_progressbar').removeClass('progress-bar-success progress-bar-info progress-bar-warning');
        $('#div_progressbar').addClass('active progress-bar-danger');
        $('#div_progressbar').width('100%');
        $('#div_progressbar').attr('aria-valuenow', 100);
        $('#div_progressbar').html('N/A');
        return;
    }
    if (ProgressPourcent == 100) {
        $('#div_progressbar').removeClass('active progress-bar-info progress-bar-danger progress-bar-warning');
        $('#div_progressbar').addClass('progress-bar-success');
        $('#div_progressbar').width(ProgressPourcent + '%');
        $('#div_progressbar').attr('aria-valuenow', ProgressPourcent);
        $('#div_progressbar').html('{{FIN}}');
        return;
    }
    $('#div_progressbar').removeClass('active progress-bar-info progress-bar-danger progress-bar-warning');
    $('#div_progressbar').addClass('progress-bar-success');
    $('#div_progressbar').width(ProgressPourcent + '%');
    $('#div_progressbar').attr('aria-valuenow', ProgressPourcent);
    $('#div_progressbar').html(ProgressPourcent + '%');
}

function GetSetting() {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "GetSetting",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            $('#input_freeboxIP').val(data.result.ip);
            logs('info', "───▶︎ :fg-info:IP ::/fg: " + data.result.ip);
            $('#input_freeNameAPP').val(data.result.NameAPP);
            logs('info', "───▶︎ :fg-info:{{Nom API}} ::/fg: " + data.result.NameAPP);
            $('#input_IdApp').val(data.result.IdApp);
            logs('info', "───▶︎ :fg-info:Id API ::/fg: " + data.result.IdApp);
            $('#input_DeviceName').val(data.result.DeviceName);
            logs('info', "───▶︎ :fg-info:{{Nom de votre Jeedom}} ::/fg: " + data.result.DeviceName);
            $('#sel_object_default').val(data.result.Categorie);
            logs('info', "───▶︎ :fg-info:{{Objet par défaut}} ::/fg: " + data.result.Categorie);
            logs('info', "───▶︎ :fg-info:{{Version API Freebox}} ::/fg: " + data.result.API);
            $('#input_API').val(data.result.API);

            console.log('IP : ' + data.result.ip)
            console.log('Nom API : ' + data.result.DeviceName)
            console.log('Objet par défaut : ' + data.result.Categorie)
            console.log('Version API : ' + data.result.API)
            if (data.result.DeviceName == null || data.result.DeviceName == "") {
                $('.bt_Freebox_Next').hide();
                $('.textFreebox').text('{{Votre Jeedom n\'a pas de Nom, il est impossible de continuer l\'appairage}}');
                logs('error', "ERREUR : " + "{{Votre Jeedom n\'a pas de Nom, il est impossible de continuer l\'appairage}}");
                $('#div_alert').showAlert({
                    message: '{{Votre Jeedom n\'a pas de Nom, il est impossible de continuer l\'appairage}}',
                    level: 'danger'
                });
            } else {
                $('.textFreebox').text('');
                $('.FreeboxOS_OK_NEXT').show();
            }
            if (data.result.LogLevel == 100) {
                var debugHides = document.getElementsByClassName('debugFreeOS');
                for (var i = 0; i < debugHides.length; i++) {
                    var debugHide = debugHides[i];
                    debugHide.classList.remove("debugHide");
                }
            } else {
                var debugShows = document.getElementsByClassName('debugFreeOS');
                for (var i = 0; i < debugShows.length; i++) {
                    var debugShow = debugShows[i];
                    debugShow.classList.add("debugHide");
                }
            }
        }
    });
}
function GetSettingTiles() {
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
            if (data.result.CronTiles == 0) {
                logs('info', "───▶︎ :fg-warning:Cron Global Titles ACTIVATION ::/fg: NOK");
                console.log('Cron Global Titles ACTIVATION - FALSE - : ' + data.result.CronTiles);
                $('.checkbox_freeboxTiles').prop('checked', false);
            } else {
                logs('info', "───▶︎ :fg-info:Cron Global Titles ACTIVATION ::/fg: OK");
                console.log('Cron Global Titles ACTIVATION - TRUE - : ' + data.result.CronTiles);
                $('.checkbox_freeboxTiles').prop('checked',true);
            };
        }
    });
}

function ResetAPI() {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "ResetAPI",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            //GetSetting();
        }
    });
}

function SetSetting(ip, VersionAPP, Categorie) {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "SetSetting",
            ip: ip,
            Categorie: Categorie,
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            GetSetting();
        }
    });
}
function SetSettingTiles(CronTiles) {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "SetSettingTiles",
            cron_tiles: cron_tiles,
            //fCmdbyCmd: CmdbyCmd,
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            GetSetting();
            $('.FreeboxOS_OK_NEXT').show();
        }
    });
}

function SetDefaultSetting() {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "resetSetting",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            GetSetting();
        }
    });
}

function GetSessionData() {

    $('.textFreebox').hide();
    $('.bt_Freebox_Next').hide();
    $('.bt_Freebox_Previous').hide();
    $('.FreeboxOS_OK').hide();
    $('.FreeboxOS_OK_NEXT').hide();
    $('.bt_Freebox_droitVerif').show();
    $('.bt_Freebox').show();

    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "GetSessionData",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.result.success) {
                var permissions = data.result.result.permissions;
                UpdateStatus("calls", permissions.calls);
                UpdateStatus("camera", permissions.camera);
                UpdateStatus("contacts", permissions.contacts);
                UpdateStatus("downloader", permissions.downloader);
                UpdateStatus("explorer", permissions.explorer);
                UpdateStatus("home", permissions.home);
                UpdateStatus("parental", permissions.parental);
                UpdateStatus("player", permissions.player);
                UpdateStatus("profile", permissions.profile);
                UpdateStatus("pvr", permissions.pvr);
                UpdateStatus("settings", permissions.settings);
                UpdateStatus("tv", permissions.tv);
                UpdateStatus("vm", permissions.vm);
                UpdateStatus("wdo", permissions.wdo);

                if (permissions.calls &&
                    permissions.camera &&
                    permissions.downloader &&
                    permissions.home &&
                    permissions.parental &&
                    permissions.player &&
                    permissions.vm &&
                    permissions.profile &&
                    permissions.settings) {
                    logs('info', "───▶︎ :fg-info:{{Les droits sont}}:/fg: OK");
                    $('.textFreebox').show();
                    $('.bt_Freebox_Next').show();
                    $('.bt_Freebox_Previous').show();
                    $('.FreeboxOS_OK').show();
                    $('.FreeboxOS_OK_NEXT').show();
                    $('.bt_Freebox_droitVerif').hide();
                    $('.bt_Freebox').hide();

                    progress(65);
                }
            }
        }
    });
}

function getBox(type) {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
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
                if (type == "next") {
                    funNext();
                } else {
                    funPrev()
                }
                logs('info', "───▶︎ :fg-info:{{Compatibilité avec la partie domotique}} ::/fg: NOK");
            } else {
                logs('info', "───▶︎ :fg-info:{{Compatibilité avec la partie domotique}} ::/fg: OK");
                //SearchTile_room();
            }
        }
    });
}

function UpdateStatus(item, index) {

    if (index == true) {
        document.getElementById(item).classList.add('alert-success');
        document.getElementById(item).classList.remove('alert-danger');
        document.getElementById(item).innerHTML = "OK";
    } else {
        document.getElementById(item).classList.add('alert-danger');
        document.getElementById(item).classList.remove('alert-success');
        document.getElementById(item).innerHTML = "NOK";
    }
}

function SaveTitelRoom() {
    titelRoomArrays = $('#table_room').find('.piece').getValues('.titleRoomAttr');
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "setRoomID",
            data: titelRoomArrays
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            //SearchTile_room();
        }
    });
}

function funNext() {
    updateMenu($('.li_FreeboxOS_Summary.active').next());

    $('.bt_Freebox_Next').show();
    $('.bt_Freebox_Previous').show();

    logs('info', "──────────▶︎ :fg-warning:{{Étape}} ::/fg: " + $('.li_FreeboxOS_Summary.active').attr('data-href'));

    switch ($('.li_FreeboxOS_Summary.active').attr('data-href')) {
        case 'home':
            progress(0);
            break;
        case 'setting':
            progress(15);
            GetSetting();
            break;
        case 'authentification':
            progress(25);
            break;
        case 'rights':
            progress(50);
            GetSessionData();
            break;
        case 'room':
            progress(75);
            GetSettingTiles();
            getBox("next");
            break;
        case 'scan':
            progress(80);
            break;
        case 'end':
            progress(100);
            break;
    }
}

function funPrev() {
    updateMenu($('.li_FreeboxOS_Summary.active').prev());

    $('.bt_Freebox_Next').show();
    $('.bt_Freebox_Previous').show();

    switch ($('.li_FreeboxOS_Summary.active').attr('data-href')) {
        case 'home':
            progress(0);
            break;
        case 'setting':
            progress(15);
            GetSetting();
            break;
        case 'authentification':
            progress(25);
            break;
        case 'rights':
            progress(50);
            GetSessionData();
            break;
        case 'room':
            progress(75);
            getBox("prev");
            break;
        case 'scan':
            progress(80);
            break;
        case 'end':
            progress(100);
            break;
    }
}

function logs(loglevel, logText) {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/FreeboxOS.ajax.php",
        data: {
            action: "setLogs",
            loglevel: loglevel,
            logsText: logText
        },
        dataType: 'json',
        error: function (request, status, error) {},
        success: function (data) {}
    });
}