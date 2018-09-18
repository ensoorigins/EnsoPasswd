function performLogin() {
    var pageUrl = REST_SERVER_PATH + "auth/";
    $.ajax({
        type: "POST",
        dataType: "json",
        cache: false,
        data: { username: $("#edit-username-login").val(), password: $("#edit-password-login").val() },
        url: pageUrl,
        success: function (response) {
            loginInvalid = true;
            if (logginIn) {
                Cookies.set('sessionkey', response['sessionkey']);
                Cookies.set('actions', response['actions']);
                Cookies.set('username', response['username']);
                ensoConf.switchApp('passwd');
            }
        },
        error: function (response) {
            if (response.status == EnsoShared.ENSO_REST_NOT_AUTHORIZED) {
                Materialize.toast('Autenticação falhada.', 3000, 'rounded');
            }
            loginInvalid = true;
            logginIn = false;
        }
    });
}

function validateSession() {
    loginInvalid = true;

    var pageUrl = REST_SERVER_PATH + "validity/";
    $.ajax({
        type: "GET",
        dataType: "json",
        cache: false,
        async: false,
        data: { authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey') },
        url: pageUrl,
        success: function (response) {
            if (response == "1")
                loginInvalid = false;

        },
        error: function (response) {
        }
    });

    return !loginInvalid;
}

function checkCredentials() {
    if (browserHasGoodCookies() && validateSession()) //Não há sessionkey, fazer login e desaparecer menus nav
    {
        ensoConf.switchApp('passwd');
    }
    else {
        resetSession();
    }
}

function resetSession() {
    Cookies.remove('sessionkey');
    Cookies.remove('actions');
    Cookies.remove('username');
}

function browserHasGoodCookies() {
    return Cookies.get('sessionkey') !== undefined && Cookies.get('actions') !== undefined && Cookies.get('username') !== undefined;
}

var loginInvalid = true;
var logginIn = false;

$(document).ready(function () {
    checkCredentials();
    $("input").keypress(function (event) {

        if (event.keyCode == 13 && !logginIn) {
            logginIn = true;
            performLogin();
        }
    });

    Materialize.updateTextFields();
});

//# sourceURL=login.js