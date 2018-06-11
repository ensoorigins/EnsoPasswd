var loginInvalid;

if (browserHasGoodCookies())
    loginInvalid = false;
else
    loginInvalid = true;

function clearSearchAction() {
    $("#pesquisa-desktop").off("input");
    $("#pesquisa-mobile").off("input");
}

function attachSearchAction(action, removePreviousAction = true) {
    if (removePreviousAction)
        clearSearchAction();

    $("#pesquisa-desktop").on("input", action);
    $("#pesquisa-mobile").on("input", action);
}

function hasAction(action) {
    var actions = $.parseJSON(Cookies.get('actions'));
    var hasfound = false;

    $.each(actions, function (key, val) {
        if (val == action) {
            hasfound = true;
        }
    });

    return hasfound;
}

function setHeight() { //Move content div down and make it fill the height of the remaining screen

    var windowHeight = $(window).height();
    var headerHeight = $('#header').height();
    var headerTabsHeight = $('#header-tabs').height();

    $('#main-content').css('min-height', windowHeight - headerHeight - headerTabsHeight);
    $('#main-content').css('margin-top', headerTabsHeight);
}

function resetSession() {
    Cookies.remove('sessionkey');
    Cookies.remove('actions');
    Cookies.remove('username');

    hideUI();
    ensoConf.switchApp('login');
}

function browserHasGoodCookies() {
    return Cookies.get('sessionkey') !== undefined && Cookies.get('actions') !== undefined && Cookies.get('username') !== undefined;
}

function checkCredentials() {
    if (ensoConf.getCurrentPage() == 'external_link') {
        hideUI();
        return;
    }

    if (!browserHasGoodCookies()) //Não há sessionkey, fazer login e desaparecer menus nav
    {
        resetSession();
    }
    else {
        if (ensoConf.getCurrentPage() == "login")
            ensoConf.switchApp(ensoConf.defaultApp);
        else
            adaptUIToUser();
    }
}

function hideUI() {
    $(".search-wrapper").addClass("hiddendiv");
    $(".user-info").addClass("hiddendiv");
    $(".user-message").addClass("hiddendiv");
    $(".user-sysadmin").addClass("hiddendiv");
    $(".user-logout").addClass("hiddendiv");
}

function showUI() {
    $(".search-wrapper").removeClass("hiddendiv");
    $(".user-info").removeClass("hiddendiv");
    $(".user-message").removeClass("hiddendiv");
    $(".user-sysadmin").removeClass("hiddendiv");
    $(".user-logout").removeClass("hiddendiv");
}

function adaptUIToUser() {
    if (!hasAction('accessSysAdminArea'))
        $(".user-sysadmin").addClass("hiddendiv");

    $(".username-label").empty().append(Cookies.get('username'));
}

function logout() {
    resetSession();

    ensoConf.switchApp('login');
}

function dealWithErrorStatusCodes(response, requestSpecificErrors) {

    if (response.status == EnsoShared.ENSO_REST_FORBIDDEN) {
        Materialize.toast('Não tem permissões para aceder a estes dados, por favor contacte o administrador da aplicação.', 3000, 'rounded');

    } else if (response.status == EnsoShared.ENSO_REST_NOT_AUTHORIZED) {
        if (!browserHasGoodCookies() || !loginInvalid) {
            loginInvalid = true;
            resetSession();
            ensoConf.switchApp('login');
        }

    } else if (response.status == EnsoShared.ENSO_REST_INTERNAL_SERVER_ERROR) {
        Materialize.toast('Ocorreu um erro interno, se este problema persistir contacte o administrador da aplicação', 3000, 'rounded');
    } else if (response.status == EnsoShared.ENSO_REST_NOT_ACCEPTABLE) {
        Materialize.toast(requestSpecificErrors[response.responseText], 3000, 'rounded');
    }
    else {
        console.log("Unknown error " + response.status);
        console.log(response);
        console.trace();
    }
}

function updateInboxCount() {
    if (ensoConf.getCurrentPage() !== "login" && ensoConf.getCurrentPage() !== "external_link") { //Only perform in internal pages

        MessageActions.getInboxCount(function (numero) {
            $(".number-badge").text(numero);
        });
    }

}

$(document).ready(function () {
    setHeight();

    $(".button-collapse").sideNav();

    $(window).resize(function () {
        setHeight();
    });

    $("#pesquisa-desktop").keypress(function (e) {
        e.stopPropagation();
    });

    $("#pesquisa-desktop").on("focusin", function () {
        $("#pesquisa-desktop").css("width", "calc(100% - 4em - 42px)");
        $("#pesquisa-desktop").parent().attr("style", "background-color: white !important");
        setTimeout(function () { $("#clear-search-button").show() }, 100);
    });

    $("#pesquisa-desktop").on("focusout", function () {
        $("#pesquisa-desktop").css("width", "calc(100% - 4em)");
        $("#pesquisa-desktop").parent().css("background-color", "");
        setTimeout(function () { $("#clear-search-button").hide() }, 100);
    });

    checkCredentials();

    ensoConf.addAfterViewCallback(updateInboxCount);
    ensoConf.addAfterViewCallback(function () {
        $("#pesquisa-desktop").val("").trigger('input');
        $("#pesquisa-mobile").val("").trigger("input");
    });

    $('.dropdown-button').dropdown({
        inDuration: 300,
        outDuration: 225,
        constrainWidth: false, // Does not change width of dropdown to that of the activator
        hover: false, // Activate on hover
        gutter: 0, // Spacing from edge
        belowOrigin: true, // Displays dropdown below the button
        alignment: 'left', // Displays dropdown with edge aligned to the left of button
        stopPropagation: false // Stops event propagation
    }
    );

    var keys = {};

    function isKey(key) {

        return keys.hasOwnProperty(key);
    }

    window.onkeyup = function (e) {
        delete keys[e.keyCode];
    }

    window.onkeydown = function (e) {
        if($(".modal.open").length !== 0)
            return;
            
        keys[e.keyCode] = true;

        if ((isKey(91) || isKey(93) || isKey(224) || isKey(17)) && (e.keyCode == 83 || e.keyCode == 70)) {
            if ($(document.activeElement).prop('nodeName').toLowerCase() != 'input' && $(window).width() > 992) {
                e.preventDefault();
                $("#pesquisa-desktop").focus();
            }
        }
    }
});

//# sourceURL=materialize_init.js