var ShareModal = {
    createNew: false,
    _credential: undefined,
    show: function (preSelectedCredential = undefined, divToHide = undefined)
    {
        pageUrl = ensoConf.viewsPath + "share_modal" + (preSelectedCredential === undefined ? "_new" : "") + ".html";
        $.ajax({
            type: "GET",
            dataType: "html",
            cache: false,
            url: pageUrl,
            success: function (response) {
                $("#main-content").append(response);
                LocalizationManager.applyLocaleSettings();
                $("#share-modal").modal(
                        {
                            endingTop: '8em',
                            ready: ModalUtils.coverNavbar,
                            complete: function ()
                            {
                                if (divToHide !== undefined)
                                    $('#' + divToHide).show();
                                $("#share-modal").remove();
                                $('a.enso-main-color-text.active').click(); //call default active tab action
                                ModalUtils.refreshTooltips();
                            }
                        });
                if (divToHide !== undefined)
                    $('#' + divToHide).hide();
                
                if (preSelectedCredential !== undefined)
                    ShareModal._credential = preSelectedCredential;
                else
                    ShareModal.createNew = true;
                
                    

                ShareModal.feedAutoComplete();
                LocalizationManager.applyLocaleSettingsToGivenView('share_modal');
                LocalizationManager.applyLocaleSettingsToGivenView('credential_modal');
                $(".tooltipped").tooltip();
                $('select').material_select();
                $("#share-modal").modal('open');
                $("#autocomplete-input").focus();
            },
            error: function (response) {
            }
        });
    },
    showTreeView: function (preSelectedCredential)
    {
        pageUrl = REST_SERVER_PATH + "folderTreeView/";
        $.ajax({
            type: "GET",
            dataType: "json",
            cache: false,
            url: pageUrl,
            data: {sessionkey: Cookies.get('sessionkey'), authusername: Cookies.get('username')},
            success: function (response) {

                level = 0;
                $.each(response, function (ind, val)
                {
                    $("#tree-view").append("<div style='padding-left: " + level + "em; ' id='folder-" + val['id'] + "'>\
                                                            <p onclick='ShareModal.toggleFolder(" + val['id'] + ")' style='display:flex; padding-bottom: 0.5em; margin: 0'>\
                                                                <i class='material-icons drop-button'>keyboard_arrow_down</i>\
                                                                <i class='material-icons'>folder</i>\
                                                                <span style='display:inline-block; line-height: 24px; vertical-align:middle; padding-left: 1em'> " + val['name'] + "</span>\n\
                                                            </p>\
                                                        </div>");
                    ShareModal.generateHtmlForFolder(val, level + 1);
                    $.each(val['credentials'], function (ind, cred)
                    {

                        $("#folder-" + val['id']).append("<div style='padding-left: " + (level + 1) + "em; text-align:left' >\
                                                            <input class='with-gap' name='credential' type='radio' value='" + cred['idCredentials'] + "' id='credential-" + cred['idCredentials'] + "' " + (cred['idCredentials'] == preSelectedCredential ? "checked" : "") + "/>\
                                                            <label class='enso-main-color-text' for='credential-" + cred['idCredentials'] + "'>\
                                                                <div style='display:inline-flex'>\
                                                                    <i class='material-icons'>vpn_key</i>\
                                                                    <span style='display:inline-block; line-height: 24px; vertical-align:middle; padding-left: 1em'>" + cred['title'] + "</span>\
                                                                </div>\
                                                            </label>\
                                                        </div>");
                    });
                });
                $("#credential-" + preSelectedCredential).attr('selected', true);
            },
            error: function (response) {
                dealWithErrorStatusCodes(response, undefined);
            }
        });
    },
    generateHtmlForFolder: function (folderObject, level)
    {
        $.each(folderObject['childFolders'], function (ind, val)
        {
            $("#folder-" + folderObject['id']).append("<div style='padding-left: " + level + "em; ' id='folder-" + val['id'] + "'>\
                                                            <p onclick='ShareModal.toggleFolder(" + val['id'] + ")' style='display:flex; padding-bottom: 0.5em; margin: 0'>\
                                                                <i class='material-icons drop-button'>keyboard_arrow_down</i>\
                                                                <i class='material-icons'>folder</i>\
                                                                <span style='display:inline-block; line-height: 24px; vertical-align:middle; padding-left: 1em'> " + val['name'] + "</span>\n\
                                                            </p>\
                                                        </div>");
            ShareModal.generateHtmlForFolder(val, level + 1);
            $.each(val['credentials'], function (ind, cred)
            {
                $("#folder-" + val['id']).append("<div style='padding-left: " + (level + 1) + "em; text-align:left' >\
                                                            <input class='with-gap' name='credential' type='radio' value='" + cred['idCredentials'] + "' id='credential-" + cred['idCredentials'] + "'/>\
                                                            <label class='enso-main-color-text' for='credential-" + cred['idCredentials'] + "'>\
                                                                <div style='display:inline-flex'>\n\
                                                                    <i class='material-icons'>vpn_key</i>\
                                                                    <span style='display:inline-block; line-height: 24px; vertical-align:middle; padding-left: 1em'>" + cred['title'] + "</span>\
                                                                </div>\
                                                            </label>\
                                                        </div>");
            });
        });
    },
    toggleFolder: function (id)
    {
        if ($("#folder-" + id).children("p").children(".drop-button").text() == "keyboard_arrow_down")
            $("#folder-" + id).children("p").children(".drop-button").text('keyboard_arrow_right');
        else
            $("#folder-" + id).children("p").children(".drop-button").text('keyboard_arrow_down');
        $.each($("#folder-" + id).children(), function (ind, ele)
        {
            if (!$(ele).is("p"))
                $(ele).toggleClass("hiddendiv");
        });
    },
    feedAutoComplete: function ()
    {
        pageUrl = REST_SERVER_PATH + "users/search/";
        $.ajax({
            type: "GET",
            dataType: "json",
            cache: false,
            data: {authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey')},
            url: pageUrl,
            success: function (response) {

                names = new Object();
                $.each(response, function (key, val) {
                    names[val['username']] = null;
                });
                $('input.autocomplete').autocomplete({
                    data: names,
                    limit: 20, // The max amount of results that can be shown at once. Default: Infinity.
                    minLength: 1, // The minimum length of the input for the autocomplete to start. Default: 1.
                });
            },
            error: function (response) {
                dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('folder_modal', 'validationErrors'));
            }
        });
    },
    share: function ()
    {
        if (ModalUtils.modalIsValid())
        {
            if ($("#external-toggle").is(":checked"))
            {
                if (ShareModal.createNew === false)
                    ExternalMessageActions.shareExisting(
                            $("#autocomplete-input").val(),
                            ShareModal._credential,
                            $('#edit-message').val(),
                            $("#timeToDie-select").val(),
                            FRONT_SERVER_PATH + "#!external_link?externalKey=",
                            function (externalKey) {
                                $("#share-modal").find('.modal-footer').empty();
                                $("#share-modal").find('.modal-content').empty().append(
                                    "<i class='material-icons right' style='cursor:pointer' onclick='$(\"#share-modal\").modal(\"close\")'>close</i>\
                                        <p style='clear: both'>" + LocalizationManager.getStringFromView('share_modal', 'external-share-success') + "</p>\n\
                                         <p id='external-link'>" + FRONT_SERVER_PATH + "#!external_link?externalKey=" + externalKey + "</p>\n\
                                         <p><i class='material-icons' style='cursor:pointer;' onclick='ShareModal.copyExternalLink()'>content_copy</i></p>");
                            }
                    );
                else
                {
                    ExternalMessageActions.shareNew(
                            $("#edit-title").val(),
                            $("#edit-username").val(),
                            $("#edit-password").val(),
                            $("#edit-description").val(),
                            $("#edit-url").val(),
                            $("#autocomplete-input").val(),
                            $('#edit-message').val(),
                            $("#timeToDie-select").val(),
                            FRONT_SERVER_PATH + "#!external_link?externalKey=",
                            function (externalKey) {
                                $("#share-modal").find('.modal-footer').empty();
                                $("#share-modal").find('.modal-content').empty().append(
                                    "<i class='material-icons right' style='cursor:pointer' onclick='$(\"#share-modal\").modal(\"close\")'>close</i>\
                                        <p style='clear: both'>" + LocalizationManager.getStringFromView('share_modal', 'external-share-success') + "</p>\n\
                                         <p id='external-link'>" + FRONT_SERVER_PATH + "#!external_link?externalKey=" + externalKey + "</p>\n\
                                         <p><i class='material-icons' style='cursor:pointer;' onclick='ShareModal.copyExternalLink()'>content_copy</i></p>");
                            }
                    );
                }
            } else
            {
                if (ShareModal.createNew === false)
                    MessageActions.shareExisting(
                            $("#autocomplete-input").val(),
                            ShareModal._credential,
                            $('#edit-message').val(),
                            $("#timeToDie-select").val(),
                            function () {
                                $("#share-modal").modal('close');
                            }
                    );
                else
                {
                    MessageActions.shareNew(
                            $("#edit-title").val(),
                            $("#edit-username").val(),
                            $("#edit-password").val(),
                            $("#edit-description").val(),
                            $("#edit-url").val(),
                            $("#autocomplete-input").val(),
                            $('#edit-message').val(),
                            $("#timeToDie-select").val(),
                            function () {
                                $('#share-modal').modal('close');
                            }
                    );
                }
            }
        }

    },
    copyExternalLink: function ()
    {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($("#external-link").text()).select();
        document.execCommand("copy");
        $temp.remove();
        Materialize.toast(LocalizationManager.getStringFromView('external_link', "link-copy"), 2000);
    },

    userOnConfirm: undefined,
    userOnCancel: undefined
};
LocalizationManager.applyLocaleSettingsToGivenView('share_modal')

//# sourceURL=shareModal.js