if (firstTime === undefined)
    var firstTime = undefined;

console.log(firstTime);

var UserFolderView =
    {
        _openLastCredential: undefined,

        loadFolderList: function () {
            var pageUrl = REST_SERVER_PATH + "folders/";

            $.ajax({
                type: "GET",
                dataType: "json",
                cache: false,
                data: {
                    search: ($(window).width() > 992 ? $("#pesquisa-desktop").val() : $("#pesquisa-mobile").val()),
                    authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey'),
                    folderId: UserFolderView.getCurrentFolder()
                },
                url: pageUrl,
                success: function (response) {
                    if (response['search'] !== ($(window).width() > 992 ? $("#pesquisa-desktop").val() : $("#pesquisa-mobile").val()) && response['search'] !== '%')
                        return;

                    var sortedFolders = response['folders'].sort(function (a, b) {
                        return a['name'].localeCompare(b['name']);
                    });

                    var sortedCredentials = response['credentials'].sort(function (a, b) {
                        return a['title'].localeCompare(b['title']);
                    });

                    var html = "<thead>\
                                <tr>\
                                    <th colspan='2'>Name</th>\
                                    <th class='hide-on-med-and-down'>Description</th>\
                                    <th class='hide-on-med-and-down created-by-pre-text'></th>\
                                    <th colspan='3' class='hide-on-med-and-down center-align'></th>\
                                </tr>\
                            </thead>\
                            <tbody>";

                    $.each(sortedFolders, function (key, val) {
                        html += "<tr onclick='ensoConf.goToPage(\"folders\", {id : " + val['idFolders'] + "})'>\
                                    <td>\
                                        <i class='enso-orange-text material-icons'>folder</i>\
                                    </td>\
                                    <td>\
                                        <span class='flow-text'>" + val['name'] + "</span>\
                                    </td>\
                                    <td class='hide-on-med-and-down' >\
                                        " + val['folderChildren'] + " <span class='folder-label'></span>; " + val['credentialChildren'] + " <span class='credential-label'></span> \
                                    </td>\
                                    <td class='hide-on-med-and-down' >\
                                        <p>" + val['createdById'] + "</p>\
                                    </td>\
                                    <td colspan='3' class='hide-on-med-and-down center-align'>\
                                    </td>\
                                </tr>";
                    });

                    $.each(sortedCredentials, function (key, val) {
                        var path = "";

                        $.each(val['path'], function (ind, val) {
                            path += '/' + val.name;
                        });

                        path += '/';

                        html += "<tr>\
                                    <td onclick='UserFolderView.launchCredentialEditModal(" + val['idCredentials'] + ")'>\
                                        <i class='enso-orange-text material-icons'>lock_outline</i>\
                                    </td>\
                                    <td onclick='UserFolderView.launchCredentialEditModal(" + val['idCredentials'] + ")'>\
                                        <span class='flow-text'>" + val['title'] + "</span>\
                                    </td>\
                                    <td class='hide-on-med-and-down' onclick='UserFolderView.launchCredentialEditModal(" + val['idCredentials'] + ")'>\
                                        " + path + "\
                                    </td>\
                                    <td class='hide-on-med-and-down' onclick='UserFolderView.launchCredentialEditModal(" + val['idCredentials'] + ")'>\
                                        <p>" + val['createdById'] + "</p>\
                                    </td>\
                                    <td " + (val['username'] != "" ?
                                            "onclick='UserFolderView.copyUsername(" + val['idCredentials'] + ")' class='hide-on-med-and-down center-align" :
                                            "class='not-clickable hide-on-med-and-down center-align") + "'>\
                                        <p><i class='" + (val['username'] == "" ? "grey-text " : "enso-orange-text ") + "material-icons circle'>content_copy</i></p>\
                                        <p style='font-size: 0.8em;' class='copy-user-label'></p>\
                                    </td >\
                                    <td onclick='UserFolderView.copyPassword(" + val['idCredentials'] + ")' class='hide-on-med-and-down center-align'>\
                                        <p><i class='enso-orange-text material-icons circle'>content_copy</i></p>\
                                        <p style='font-size: 0.8em;' class='copy-password-label'></p>\
                                    </td >\
                                    <td " + (val['url'] != "" ?
                                        "onclick='UserFolderView.openUrl(" + val['idCredentials'] + ")' class='hide-on-med-and-down center-align" :
                                        "class='not-clickable hide-on-med-and-down center-align") + "'>\
                                        <p><i class='" + (val['url'] == "" ? "grey-text " : "enso-orange-text ") + "material-icons circle'>open_in_new</i></p>\
                                        <p style='font-size: 0.8em;' class='open-url-label'></p>\
                                    </td>\
                                </tr>";
                    });

                    html += "</tbody>";

                    $("#tabela-folders").html(html);

                    LocalizationManager.applyLocaleSettings();
                    UserFolderView.updateFolderPath();
                },
                error: function (response) {
                    dealWithErrorStatusCodes(response, null);
                }
            });
        },

        getCredentialOnlyFabHtml: function () {
            return "\
            <div class='fixed-action-btn' id='addMenu'>\
                <a onclick='UserFolderView.launchCredentialAddModal()' id='create-new-credential-button' class='btn-floating btn-large enso-main-color' >\
                    <i class='material-icons'>lock_outline</i>\
                </a>\
            </div >\
            ";
        },

        getFullFabHtml: function () {
            return "\
                <div class='fixed-action-btn' id='addMenu'>\
                    <a class='btn-floating btn-large enso-main-color' >\
                        <i class='large material-icons'>add</i>\
                    </a >\
                    <ul>\
                        <li>\
                            <a class='btn-floating enso-main-color tooltipped' id='create-new-folder-button' data-position='left' data-delay='50' onclick='UserFolderView.launchFolderAddModal()'>\
                                <i class='material-icons'>create_new_folder</i>\
                            </a>\
                        </li>\
                        <li>\
                            <a class='btn-floating enso-main-color tooltipped' id='create-new-credential-button' data-position='left' data-delay='50' onclick='UserFolderView.launchCredentialAddModal()'>\
                                <i class='material-icons'>lock_outline</i>\
                            </a>\
                        </li>\
                    </ul>\
                </div >\
                ";
        },

        getCurrentFolder: function () {
            if ($("#current-folder").val() == "")
                return null;
            else
                return $("#current-folder").val();
        },
        copyUsername: function (id) {
            var username = "";
            CredentialActions.requestCredentialInfo(id, function (credentialInfo) {
                username = credentialInfo['username'];
            }, function() {console.log("error copying username")}, false);

            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(username).select();
            document.execCommand("copy");
            $temp.remove();
            Materialize.toast(LocalizationManager.getStringFromView('credential_modal', "user-copy"), 2000);
        },
        copyPassword: function (id) {
            var password = "";
            CredentialActions.requestCredentialInfo(id, function (credentialInfo) {
                password = credentialInfo['password'];
            }, undefined, false);

            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(password).select();
            document.execCommand("copy");
            $temp.remove();
            Materialize.toast(LocalizationManager.getStringFromView('credential_modal', "password-copy"), 2000);
        },
        openUrl: function (id) {
            CredentialActions.requestCredentialInfo(id, function (credentialInfo) {
                if (credentialInfo['url'] != "") {
                    var win = window.open(credentialInfo['url'], '_blank');
                    win.focus();
                }
            }, undefined, false);
        },
        changeCurrentFolder: function (newFolder) {
            $("#current-folder").val(newFolder);
        },
        updateFolderPath: function () {
            FolderActions.getFolderPath(UserFolderView.getCurrentFolder(), function (path) {
                $("#breadcrumbs").empty();
                var html = "";
                $.each(path, function (ind, val) {
                    html += "<a class='breadcrumb valign-wrapper' style='cursor:pointer' onclick='UserFolderView.loadFolder(" + val['idFolders'] + ")'>" + val['name'] + "</a>";
                });

                $("#breadcrumbs").append(html);
            });
        },
        goToRootFolder: function () {
            ensoConf.goToPage('folders');
        },
        goToParentFolder: function () {
            if (UserFolderView.getCurrentFolder() === null)
                return;

            folderInfo = FolderActions.requestFolderInfo(UserFolderView.getCurrentFolder(), function (folderInfo) {
                if (folderInfo['folderInfo']['parent'] == null)
                    UserFolderView.goToRootFolder();
                else
                    ensoConf.goToPage('folders', { id: folderInfo['folderInfo']['parent'] });
            });
        },
        loadFolder: function (id = UserFolderView.getCurrentFolder()) {

            $('html,body').animate({ scrollTop: 0 }, 0);
            //Show / Hide add button
            if (id == null || id == "" || id == undefined) {
                if (firstTime == undefined) {
                    firstTime = false;
                    $("#top-bar").hide();
                }
                else
                    $("#top-bar").slideUp();

                $("#addMenu").hide();

            } else {

                if (firstTime === undefined) {
                    firstTime = false;
                    $("#top-bar").show();
                }
                else {
                    $("#top-bar").hide().slideDown();
                }

                FolderActions.requestFolderInfo(id, function (folderInfo) {

                    var userHasPermission = false;

                    $.each(folderInfo['permissions'], function (key, val) {

                        if (val['userId'] === Cookies.get('username')) {
                            userHasPermission = true;

                            if (val['hasAdmin'] == 1) {
                                $("#addMenu").remove();
                                $("#edit-folder-icon").show();
                                $("#main-content").append(UserFolderView.getFullFabHtml());
                            } else {
                                $("#addMenu").remove();
                                $("#edit-folder-icon").hide();
                                $("#main-content").append(UserFolderView.getCredentialOnlyFabHtml());
                            }
                        }
                    });
                    if (!userHasPermission) {
                        ensoConf.goToPage('folders');
                    }
                });
            }

            UserFolderView.changeCurrentFolder(id);
            UserFolderView.loadFolderList();
        },
        launchFolderAddModal: function () {
            var pageUrl = ensoConf.viewsPath + "modal_folders_add_folder.html";
            $.ajax({
                type: "GET",
                dataType: "html",
                cache: false,
                url: pageUrl,
                success: function (response) {
                    $('#folder-modal').empty().append(response);
                    $('#folder-modal').modal('open');

                    FolderActions.requestFolderInfo(UserFolderView.getCurrentFolder(), function (folderInfo) {
                        FolderModal.parsePermissionsAndAddToModal(folderInfo['permissions']);
                    });
                },
                error: function (response) {
                    ensoConf.switchApp(ensoConf.defaultApp);
                }
            });
        },
        launchCredentialAddModal: function () {
            var pageUrl = ensoConf.viewsPath + "modal_folders_add_credential.html";
            $.ajax({
                type: "GET",
                dataType: "html",
                cache: false,
                url: pageUrl,
                success: function (response) {
                    response = response.replace('{RandomName}', 'name="' + PasswordUtil.generate(16, true, true, true, false) + '"');
                    $('#folder-modal').html(response);
                    $('#folder-modal').modal('open');
                },
                error: function (response) {
                    ensoConf.switchApp(ensoConf.defaultApp);
                }
            });
        },
        initModals: function () {
            $('.modal').modal({
                ready: ModalUtils.coverNavbar,
                complete: function () {
                    if (ensoConf.getUrlArgs() == null || ensoConf.getUrlArgs()['id'] == "" || ensoConf.getUrlArgs()['id'] == null)
                        $("#current-folder").val(null);
                    else
                        $("#current-folder").val(ensoConf.getUrlArgs()['id']);

                    UserFolderView.loadFolderList();
                    ModalUtils.refreshTooltips();

                    if (UserFolderView._openLastCredential != undefined) {
                        setTimeout(function () {
                            UserFolderView.launchCredentialEditModal(UserFolderView._openLastCredential);
                            UserFolderView._openLastCredential = undefined;
                        }, 300);

                    }


                }
            });
        },
        launchFolderEditModal: function () {
            //prepare modal html

            var pageUrl = ensoConf.viewsPath + "modal_folders_edit_folder.html";
            $.ajax({
                type: "GET",
                dataType: "html",
                cache: false,
                url: pageUrl,
                success: function (response) {

                    $('#folder-modal').html(response);
                    $('#folder-modal').modal('open');

                    FolderActions.requestFolderInfo(UserFolderView.getCurrentFolder(), function (folderInfo) {
                        FolderModal.parsePermissionsAndAddToModal(folderInfo['permissions']);
                        $("#edit-name").val(folderInfo['folderInfo']['name']);
                        FolderModal.enableLookMode();
                        Materialize.updateTextFields();
                    });
                },
                error: function (response) {
                }
            });
            //launchmodal

            $('#folder-modal').modal('open');
        },
        launchCredentialEditModal: function (id) {
            //prepare modal html

            var pageUrl = ensoConf.viewsPath + "modal_folders_edit_credential.html";
            $.ajax({
                type: "GET",
                dataType: "html",
                cache: false,
                url: pageUrl,
                success: function (response) {
                    $('#folder-modal').empty().append(response);
                    $('#folder-modal').modal('open');

                    CredentialActions.requestCredentialInfo(id, function (credentialInfo) {
                        $("#edit-title").val(credentialInfo['title']);
                        $("#edit-username").val(credentialInfo['username']);
                        $("#edit-password").val(credentialInfo['password']);
                        $("#edit-description").val(credentialInfo['description']);
                        $("#edit-url").val(credentialInfo['url']);
                        if ($("#edit-url").val() == "")
                            $("#url-btn").hide();
                        $("#edit-credential-id").val(credentialInfo['idCredentials']);
                        $("#current-folder").val(credentialInfo['belongsToFolder']);
                        Materialize.updateTextFields();
                        $('#edit-description').trigger('autoresize');
                    });
                },
                error: function (response) {
                }
            });
            //launchmodal

            $('#folder-modal').modal('open');
        },

        saveCredential: function () {
            if (ModalUtils.modalIsValid())
                CredentialActions.saveCredentialInfo(
                    false,
                    $("#edit-credential-id").val(),
                    $("#edit-title").val(),
                    $("#edit-username").val(),
                    $("#edit-password").val(),
                    $("#edit-description").val(),
                    $("#edit-url").val(),
                    UserFolderView.getCurrentFolder(),
                    function () {
                        $('#folder-modal').modal('close');
                    }
                );
        },

        removeCredential: function () {
            CredentialActions.removeCredential(
                $("#edit-credential-id").val(),
                function () {
                    $('#folder-modal').modal('close');
                }
            );
        },

        saveFolder: function () {
            if (ModalUtils.modalIsValid())
                FolderActions.saveFolderInfo(
                    false,
                    UserFolderView.getCurrentFolder(),
                    $("#edit-name").val(),
                    FolderModal.getCurrentPermissionsList(),
                    function () {
                        $('#folder-modal').modal('close');
                    }
                );
        },

        createFolder: function () {
            if (ModalUtils.modalIsValid())
                FolderActions.saveFolderInfo(
                    true,
                    UserFolderView.getCurrentFolder(),
                    $("#edit-name").val(),
                    FolderModal.getCurrentPermissionsList(),
                    function () {
                        $('#folder-modal').modal('close');
                    }
                );

        },

        removeFolder: function () {
            FolderActions.requestFolderInfo(UserFolderView.getCurrentFolder(), function (folderInfo) {
                FolderActions.removeFolder(UserFolderView.getCurrentFolder());

                UserFolderView.changeCurrentFolder(folderInfo['folderInfo']['parent']);

                $('#folder-modal').modal('close');
            });
        },

        createCredential: function () {

            if (ModalUtils.modalIsValid()) {

                $("#folder-modal.modal-content").children().each(function () {
                    if ($(this).hasClass('loader'))
                        $(this).show();
                    else
                        $(this).hide();
                });

                $("#folder-modal.modal-content").hide();

                CredentialActions.saveCredentialInfo(
                    true,
                    null,
                    $("#edit-title").val(),
                    $("#edit-username").val(),
                    $("#edit-password").val(),
                    $("#edit-description").val(),
                    $("#edit-url").val(),
                    UserFolderView.getCurrentFolder(),
                    function (id) {
                        UserFolderView._openLastCredential = id;
                        $('#folder-modal').modal('close');
                    },
                    function () {
                        $("#folder-modal.modal-content").children().each(function () {
                            if ($(this).hasClass('loader'))
                                $(this).hide();
                            else
                                $(this).show();
                        });

                        $("#folder-modal.modal-content").show();
                    }
                );
            }
        }
    };

UserFolderView.loadFolder(ensoConf.getUrlArgs() == null ? null : ensoConf.getUrlArgs()['id']);
attachSearchAction(UserFolderView.loadFolderList);
UserFolderView.initModals();
LocalizationManager.applyLocaleSettings();
$('.tooltipped').tooltip({ delay: 50 });

//# sourceURL=folders.js