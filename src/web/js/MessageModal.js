var MessageModal = {
    show: function (external, viewOnly, messageId, divToHide = undefined)
    {
        pageUrl = ensoConf.viewsPath + "message_modal.html";
        $.ajax({
            type: "GET",
            dataType: "html",
            cache: false,
            url: pageUrl,
            success: function (response) {
                $("#main-content").append(response);

                LocalizationManager.applyLocaleSettings();

                $(".tooltipped").tooltip();

                $("#message-modal").modal(
                        {
                            endingTop: '20%',
                            ready: ModalUtils.coverNavbar,
                            complete: function ()
                            {
                                if (divToHide !== undefined)
                                    $('#' + divToHide).show();

                                $("#message-modal").remove();
                                Moda//user pressed ctrl+flUtils.refreshTooltips();
                            }
                        });

                if (divToHide !== undefined)
                    $('#' + divToHide).hide();

                if (external === true)
                {
                    ExternalMessageActions.getExternalMessage($.md5(messageId), function (message)
                    {
                        $("#edit-title").val(message['title']);
                        $("#edit-username").val(message['username']);
                        $("#edit-password").val(message['password']);
                        $("#edit-description").val(message['description']);
                        $("#edit-url").val(message['url']);
                        $("#edit-message").val(message['message']);
                        $("#edit-message-id").val(message['idMessages']);
                        
                        Materialize.updateTextFields();
                    });
                } else
                {
                    MessageActions.getMessage(messageId, function (message)
                    {
                        $("#edit-title").val(message['title']);
                        $("#edit-username").val(message['username']);
                        $("#edit-password").val(message['password']);
                        $("#edit-description").val(message['description']);
                        $("#edit-url").val(message['url']);
                        $("#edit-message").val(message['message']);
                        $("#edit-message-id").val(message['idMessages']);
                        
                        Materialize.updateTextFields();
                    });
                }

                if (viewOnly === true)
                {
                    $("#edit-title").attr("disabled", true);
                    $("#edit-username").attr("disabled", true);
                    $("#edit-password").attr("disabled", true);
                    $("#edit-description").attr("disabled", true);
                    $("#edit-url").attr("disabled", true);
                    $("#edit-message").attr("disabled", true);
                    $("#edit-message-id").attr("disabled", true);
                    
                    $("#edit-generate-password").remove();
                    $("#edit-tree-search").parent().parent().remove();
                    $("#message-modal-save-button").remove();
                } else
                {
                    MessageModal.showTreeView();
                    $("#edit-tree-search").on('input', function ()
                    {
                        MessageModal.showTreeView();
                    });
                }

                LocalizationManager.applyLocaleSettingsToGivenView('message_modal');

                $("#message-modal").modal('open');
            },
            error: function (response) {
            }
        });
    },
    showTreeView: function ()
    {
        pageUrl = REST_SERVER_PATH + "folderTreeView/";

        $.ajax({
            type: "GET",
            dataType: "json",
            cache: false,
            url: pageUrl,
            data: {sessionkey: Cookies.get('sessionkey'), authusername: Cookies.get('username')},
            success: function (response) {

                if ($('input[name=folder]:checked').length === 1)
                {
                    MessageModal.selectedFolder = $('input[name=folder]:checked').val();
                }

                $("#tree-view").empty();
                search = $("#edit-tree-search").val().toLowerCase();

                MessageModal.tree = response;

                level = 0;

                $.each(response, function (ind, val)
                {
                    if (search === "" || val['name'].toLowerCase().indexOf(search) !== -1 || MessageModal.folderContainsFolder(val, search))
                    {
                        $("#tree-view").append("<div id='folder-container-" + val['id'] + "'><div style='padding-left: " + (level + 1) + "em; text-align:left' >\
                                                            <input class='with-gap' name='folder' type='radio' value='" + val['id'] + "' id='folder-" + val['id'] + "' />\
                                                            <label class='enso-main-color-text' for='folder-" + val['id'] + "'>\
                                                                <div style='display:inline-flex'>\
                                                                    <i class='material-icons'>folder</i>\
                                                                    <span style='display:inline-block; line-height: 24px; vertical-align:middle; padding-left: 1em'>" + val['name'] + "</span>\
                                                                </div>\
                                                            </label>\
                                                        </div> </div>");

                        MessageModal.generateHtmlForFolder(val, level + 1, search);
                    }
                });

                $("#folder-" + MessageModal.selectedFolder).click();

            },
            error: function (response) {
                dealWithErrorStatusCodes(response, undefined);
            }
        });
    },
    generateHtmlForFolder: function (folderObject, level, search)
    {
        $.each(folderObject['childFolders'], function (ind, val)
        {

            if (search === "" || val['name'].toLowerCase().indexOf(search) !== -1 || MessageModal.folderContainsFolder(val, search))
            {
                $("#folder-container-" + folderObject['id']).append("<div id='folder-container-" + val['id'] + "'><div style='padding-left: " + (level + 1) + "em; text-align:left' >\
                                                            <input class='with-gap' name='folder' type='radio' value='" + val['id'] + "' id='folder-" + val['id'] + "' />\
                                                            <label class='enso-main-color-text' for='folder-" + val['id'] + "'>\
                                                                <div style='display:inline-flex'>\
                                                                    <i class='material-icons'>folder</i>\
                                                                    <span style='display:inline-block; line-height: 24px; vertical-align:middle; padding-left: 1em'>" + val['name'] + "</span>\
                                                                </div>\
                                                            </label>\
                                                        </div></div>");

                MessageModal.generateHtmlForFolder(val, level + 1, search);
            }
        });

    },

    folderContainsFolder: function (parent, search)
    {
        if (parent === null)
            parent = MessageModal.tree;

        sai = false;

        $.each(parent['childFolders'], function (ind, val)
        {
            if (sai === false)
            {
                if (val['name'].toLowerCase().indexOf(search) !== -1)
                {
                    sai = true;
                } else
                {
                    if (MessageModal.folderContainsFolder(val, search))
                        sai = true;
                }
            }
        });

        return sai;
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
    createCredential: function ()
    {
        if (ModalUtils.modalIsValid())
            MessageActions.receiveCredential(
                    $("#edit-message-id").val(),
                    $("#edit-title").val(),
                    $("#edit-username").val(),
                    $("#edit-password").val(),
                    $("#edit-description").val(),
                    $("#edit-url").val(),
                    MessageModal.selectedFolder = $('input[name=folder]:checked').val(),
                    function () {
                        $('#message-modal').modal('close');
                    }
            );
    },
    removeMessage: function ()
    {
        MessageActions.removeMessage($("#edit-message-id").val(), function ()
        {
            $('#message-modal').modal('close');
        });
    },
    selectedFolder: undefined,
    tree: undefined,
    userOnConfirm: undefined,
    userOnCancel: undefined
};

LocalizationManager.applyLocaleSettingsToGivenView('message_modal')