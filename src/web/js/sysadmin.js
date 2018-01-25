setHeight();

function activateUserListTab()
{
    pageUrl = ensoConf.viewsPath + "sysadmin_user_list.html";

    $.ajax({
        type: "GET",
        dataType: "html",
        cache: false,
        url: pageUrl,
        success: function (response) {
            $("#main-content").empty().append(response);
            
            LocalizationManager.applyLocaleSettings();
        },
        error: function (response) {
        }
    });
}

function activateManageFoldersTab()
{
    pageUrl = ensoConf.viewsPath + "sysadmin_manage_folders.html";

    $.ajax({
        type: "GET",
        dataType: "html",
        cache: false,
        url: pageUrl,
        success: function (response) {
            $("#main-content").empty().append(response);
            LocalizationManager.applyLocaleSettings();
        },
        error: function (response) {
        }
    });
}

function activateViewLogsTab()
{
    pageUrl = ensoConf.viewsPath + "sysadmin_view_logs.html";

    $.ajax({
        type: "GET",
        dataType: "html",
        cache: false,
        url: pageUrl,
        success: function (response) {
            $("#main-content").empty().append(response);
            LocalizationManager.applyLocaleSettings();
        },
        error: function (response) {
        }
    });
}

/* Verificação de acesso */

if (!hasAction('accessSysAdminArea'))
{
    ensoConf.switchApp(ensoConf.defaultApp);
}