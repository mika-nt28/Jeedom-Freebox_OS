progress(0);
eqLogic_id = null;

$('.bt_Freebox_OS_Next').off('click').on('click', function () {
    $('.li_Freebox_OS_Summary.active').next().click();

    switch ($('.li_Freebox_OS_Summary.active').attr('data-href')) {
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
            progress(70);
            break;
        case 'scan':
            progress(80);
            break;
        case 'end':
            progress(100);
            break;
    }
});

$('.bt_Freebox_OS_Previous').off('click').on('click', function () {
    $('.li_Freebox_OS_Summary.active').prev().click();

    switch ($('.li_Freebox_OS_Summary.active').attr('data-href')) {
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
            progress(70);
            break;
        case 'scan':
            progress(80);
            break;
        case 'end':
            progress(100);
            break;
    }
});

$('.bt_eqlogic_standard').on('click', function () {
    SearchArchi();
    progress(70);
});

$('.bt_eqlogic_tiles').on('click', function () {
    SearchTile();
    progress(80);
});

$('.bt_eqlogic_control_parental').on('click', function () {
    SearchParental();
    progress(90);
});

$('.li_Freebox_OS_Summary').off('click').on('click', function () {
    $('.li_Freebox_OS_Summary.active').removeClass('active');
    $(this).addClass('active');
    $('.Freebox_OS_Display').hide();
    $('.Freebox_OS_Display.' + $(this).attr('data-href')).show();

    switch ($('.li_Freebox_OS_Summary.active').attr('data-href')) {
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
            break;
        case 'scan':
            progress(70);
            break;
        case 'end':
            progress(100);
            break;
    }

    $(this).attr('data-display', 1);
});

$('.bt_Freebox_OS_Save').on('click', function () {

    ip = $('#imput_freeboxIP').val();
    VersionAPP = $('#imput_freeAppVersion').val();
    Categorie = $('#sel_catego').val();
    SetSetting(ip, VersionAPP, Categorie);
});

$('.bt_Freebox_Autorisation').on('click', function () {
    autorisationFreebox();
});

function autorisationFreebox() {
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
            console.log(data)
            if (!data.result.success) {
                $('#div_alert').showAlert({
                    message: data.result.msg,
                    level: 'danger'
                });
                if (data.result.error_code == "new_apps_denied")
                    $('#div_alert').append(".<br>Pour activer l'option, il faut se rendre dans : mafreebox.freebox.fr -> Paramètres de la Freebox -> Gestion des accès <br> Et cocher : <b>Permettre les nouvelles demandes d'associations</b>  -> Appliquer<br>De nouveau, cliquez sur <b>Etape 1</b>");
                return;
            } else {
                sendToBdd(data.result);
                $('.textFreebox').text('{{Merci d\'appuyer sur le bouton V de votre Freebox, afin de confirmer l\'autorisation d\'accès à votre Freebox.}}');
                $('.img-freeboxOS').attr('src', 'plugins/Freebox_OS/core/images/authentification/authentification.jpg');
                progress(40);
                setTimeout(AskTrackAuthorization, 3000);
            }
        }
    });
}

function SearchArchi() {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
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
        url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
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

function SearchParental() {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
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

function AskTrackAuthorization() {
    if ($('.li_Freebox_OS_Summary.active').attr('data-href') == "authentification") {

        $('.textFreebox').hide();
        $('.bt_Freebox_OS_Next').hide();
        $('.bt_Freebox_OS_Previous').hide();
        $('.Freebox_OK').hide();
        $('.Freebox_OK_NEXT').hide();

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
                console.log(data)
                if (!data.result.success) {
                    $('#div_alert').showAlert({
                        message: data.result.msg,
                        level: 'danger'
                    });
                } else {
                    $('.textFreebox').show();
                    $('.bt_Freebox_OS_Next').show();
                    $('.bt_Freebox_OS_Previous').show();
                    switch (data.result.result.status) {

                        case "unknown":
                            $('.textFreebox').text('{{Vous n\'avez pas validé à temps, il faut relancer l\'association. Merci}}');
                            Good();
                            progress(-1);
                            break;
                        case "pending":
                            setTimeout(AskTrackAuthorization, 3000);
                            break;
                        case "timeout":
                            $('.textFreebox').text('{{Vous n\'avez pas validé à temps, il faut relancer l\'association. Merci}}');
                            Good();
                            progress(-1);
                            break;
                        case "granted":
                            $('.textFreebox').text('{{Félicitation votre Freebox est maintenant reliée à Jeedom.}}');
                            $('.Freebox_OK').show();
                            $('.Freebox_OK_NEXT').show();
                            $('.Freebox_OS_Display.' + $(this).attr('rights')).show();
                            progress(60);
                            break;

                        case "denied":
                            $('.textFreebox').text('{{Vous avez refusé, il faut relancer l\'association. Merci}}');
                            progress(-1);
                            Good();
                            break;
                        default:
                            $('#div_alert').showAlert({
                                message: "REST OK : track_authorization -> Error 4 : Inconnue",
                                level: 'danger'
                            });
                            Good();
                            break;
                    }
                }
            }
        });
    } else {
        $('.textFreebox').show();
        $('.bt_Freebox_OS_Next').show();
        $('.bt_Freebox_OS_Previous').show();
        $('.Freebox_OK').show();
        $('.Freebox_OK_NEXT').show();
    }
}

function Good() {
    $('.bt_Freebox_OS_Previous').hide();
    $('.bt_Freebox_OS_NEXT').hide();
    $('.alert-info Freebox_OK').text('{{Authentification réussi}}');
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
        $('#div_progressbar').html('FIN');
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
        url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
        data: {
            action: "GetSetting",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            $('#imput_freeboxIP').val(data.result.ip);
            $('#imput_freeAppVersion').val(data.result.VersionAPP);
            $('#sel_catego').val(data.result.Categorie);
        }
    });
}

function SetSetting(ip, VersionAPP, Categorie) {
    $.ajax({
        type: "POST",
        url: "plugins/Freebox_OS/core/ajax/Freebox_OS.ajax.php",
        data: {
            action: "SetSetting",
            ip: ip,
            VersionAPP: VersionAPP,
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