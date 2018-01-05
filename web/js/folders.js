if (firstTime === undefined)
    var firstTime = undefined;

var UserFolderView =
    {
        loadFolderList: function () {
            pageUrl = REST_SERVER_PATH + "folders/";

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

                    objectList = $.merge($.merge([], response['folders']), response['credentials']);

                    objectList = objectList.sort(function (a, b) {

                        comparableA = (a['name'] === undefined ? a['title'] : a['name']);
                        comparableB = (b['name'] === undefined ? b['title'] : b['name']);

                        return comparableA.localeCompare(comparableB);
                    });

                    html = "<thead>\
                                <tr>\
                                    <th colspan='2'>Name</th>\
                                    <th>Description</th>\
                                    <th class='created-by-pre-text'></th>\
                                    <th colspan='3'></th>\
                                </tr>\
                            </thead>\
                            <tbody>";

                    $.each(objectList, function (key, val) {
                        if (val['name'] !== undefined) {
                            html += "<tr onclick='ensoConf.goToPage(\"folders\", {id : " + val['idFolders'] + "})'>\
                                    <td>\
                                        <i class='enso-orange-text material-icons'>folder</i>\
                                    </td>\
                                    <td>\
                                        <span class='flow-text'>" + val['name'] + "</span>\
                                    </td>\
                                    <td>\
                                        " + val['folderChildren'] + " <span class='folder-label'></span>; " + val['credentialChildren'] + " <span class='credential-label'></span> \
                                    </td>\
                                    <td>\
                                        <p>" + val['createdById'] + "</p>\
                                    </td>\
                                    <td colspan='3'>\
                                    </td>\
                                </tr>";
                        }
                        else {
                            path = "";

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
                                    <td onclick='UserFolderView.launchCredentialEditModal(" + val['idCredentials'] + ")'>\
                                        " + path + "\
                                    </td>\
                                    <td onclick='UserFolderView.launchCredentialEditModal(" + val['idCredentials'] + ")'>\
                                        <p>" + val['createdById'] + "</p>\
                                    </td>\
                                    <td onclick='UserFolderView.copyUsername(" + val['idCredentials'] + ")' class='center-align'>\
                                        <p><i class='enso-orange-text material-icons circle'>content_copy</i></p>\
                                        <p style='font-size: 0.8em;' class='copy-user-label'></p>\
                                    </td >\
                                    <td onclick='UserFolderView.copyPassword(" + val['idCredentials'] + ")' class='center-align'>\
                                        <p><i class='enso-orange-text material-icons circle'>content_copy</i></p>\
                                        <p style='font-size: 0.8em;' class='copy-password-label'></p>\
                                    </td >\
                                    <td onclick='UserFolderView.openUrl(" + val['idCredentials'] + ")' class='center-align'>\
                                        <p><i class='enso-orange-text material-icons circle'>open_in_new</i></p>\
                                        <p style='font-size: 0.8em;' class='open-url-label'></p>\
                                    </td>\
                                </tr>";

                        }
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
            username = "";
            CredentialActions.requestCredentialInfo(id, function (credentialInfo) {
                username = credentialInfo['username'];
            }, undefined, false);

            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(username).select();
            document.execCommand("copy");
            $temp.remove();
            Materialize.toast(LocalizationManager.getStringFromView('credential_modal', "user-copy"), 2000);
        },
        copyPassword: function (id) {
            password = "";
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
                var win = window.open(credentialInfo['url'], '_blank');
                win.focus();
            }, undefined, false);
        },
        changeCurrentFolder: function (newFolder) {
            $("#current-folder").val(newFolder);
        },
        updateFolderPath: function () {
            FolderActions.getFolderPath(UserFolderView.getCurrentFolder(), function (path) {
                $("#breadcrumbs").empty();
                html = "";
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
                if (firstTime === undefined) {
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

                    userHasPermission = false;

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
            pageUrl = ensoConf.viewsPath + "modal_folders_add_folder.html";
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
                    switchApp(ENSO_DEFAULT_APP);
                }
            });
        },
        launchCredentialAddModal: function () {
            pageUrl = ensoConf.viewsPath + "modal_folders_add_credential.html";
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
                    switchApp(ENSO_DEFAULT_APP);
                }
            });
        },
        initModals: function () {
            $('.modal').modal({
                ready: ModalUtils.coverNavbar,
                complete: function () {
                    UserFolderView.loadFolderList();
                    ModalUtils.refreshTooltips();
                }
            });
        },
        launchFolderEditModal: function () {
            //prepare modal html

            pageUrl = ensoConf.viewsPath + "modal_folders_edit_folder.html";
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

            pageUrl = ensoConf.viewsPath + "modal_folders_edit_credential.html";
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
                CredentialActions.saveCredentialInfo(
                    true,
                    null,
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
            }
        }
    };

UserFolderView.loadFolder(ensoConf.getUrlArgs() == null ? null : ensoConf.getUrlArgs()['id']);
attachSearchAction(UserFolderView.loadFolderList);
UserFolderView.initModals();
LocalizationManager.applyLocaleSettings();
$('.tooltipped').tooltip({ delay: 50 });
