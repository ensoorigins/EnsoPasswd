setHeight();

function activateInboxTab()
{
    var pageUrl =ensoConf.viewsPath + "messages_inbox.html";

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

function activateOutboxTab()
{
    var pageUrl =ensoConf.viewsPath + "messages_outbox.html";

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

//# sourceURL=messages.js