var SysAdminFolderManagement =
    {
        loadFolderList: function () {
            var pageUrl =REST_SERVER_PATH + "sysadmin/folders/";

            $("#root-folder-list").empty();
            $("#list-view").empty();

            $.ajax({
                type: "GET",
                dataType: "json",
                cache: false,
                data: {
                    search: ($(window).width() > 992 ? $("#pesquisa-desktop").val() : $("#pesquisa-mobile").val()),
                    authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey')
                },
                url: pageUrl,
                success: function (response) {
                    var html = "<thead>\
                                <tr>\
                                    <th colspan='2'>Name</th>\
                                    <th class='hide-on-med-and-down'>Description</th>\
                                    <th class='hide-on-med-and-down' class='created-by-pre-text'></th>\
                                </tr>\
                            </thead>\
                            <tbody>";

                            response = response.sort(function (a, b) {        
                                return a['name'].localeCompare(b['name']);
                            });

                    $.each(response, function (key, val) {
                        html += "<tr onclick='SysAdminFolderManagement.launchFolderEditModal(" + val['idFolders'] + ")'>\
                                    <td>\
                                        <i class='enso-orange-text material-icons'>folder</i>\
                                    </td>\
                                    <td>\
                                        <span class='flow-text'>" + val['name'] + "</span>\
                                    </td>\
                                    <td class='hide-on-med-and-down'>\
                                        " + val['folderChildren'] + " <span class='folder-label'></span>; " + val['credentialChildren'] + " <span class='credential-label'></span> \
                                    </td>\
                                    <td class='hide-on-med-and-down'>\
                                        <p>" + val['createdById'] + "</p>\
                                    </td>\
                                </tr>";
                    });

                    html += "</tbody>";

                    $("#tabela-folders").html(html);

                    LocalizationManager.applyLocaleSettingsToGivenView('folders');
                },
                error: function (response) {
                    dealWithErrorStatusCodes(response, FolderActions.errors);
                    //ensoConf.switchApp(ensoConf.defaultApp);
                }
            });
        },
        launchFolderEditModal: function (id) {
            //prepare modal html

            var pageUrl =ensoConf.viewsPath + "modal_root_folder_edit.html";
            $.ajax({
                type: "GET",
                dataType: "html",
                cache: false,
                url: pageUrl,
                success: function (response) {
                    $('.modal').empty().append(response);
                    FolderActions.requestFolderInfo(id, function (folderInfo) {
                        $("#edit-name").val(folderInfo['folderInfo']['name']);
                        $("#edit-folder-id").val(id);
                        FolderModal.parsePermissionsAndAddToModal(folderInfo['permissions']);
                        FolderModal.enableLookMode();
                        Materialize.updateTextFields();
                        $('#folder-modal').modal('open');
                    });
                },
                error: function (response) {
                    //ensoConf.switchApp(ensoConf.defaultApp);
                }
            });
        },
        launchFolderAddModal: function () {
            var pageUrl =ensoConf.viewsPath + "modal_root_folder_add.html";
            $.ajax({
                type: "GET",
                dataType: "html",
                cache: false,
                url: pageUrl,
                success: function (response) {
                    $('.modal').empty().append(response);
                    $('#folder-modal').modal('open');
                    FolderModal.addPermission(Cookies.get('username'), 1);
                    LocalizationManager.applyLocaleSettingsToGivenView('folder_modal');
                    FolderModal.enableEditMode();
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
                    SysAdminFolderManagement.loadFolderList();
                    ModalUtils.refreshTooltips();
                }
            });
        },
        saveFolder: function () {
            if (ModalUtils.modalIsValid())
                FolderActions.saveFolderInfo(
                    false,
                    $("#edit-folder-id").val(),
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
                    $("#edit-folder-id").val(),
                    $("#edit-name").val(),
                    FolderModal.getCurrentPermissionsList(),
                    function () {
                        $('#folder-modal').modal('close');
                    });
        },
        removeFolder: function () {
            FolderActions.removeFolder($("#edit-folder-id").val(),
                function () {
                    $('#folder-modal').modal('close');
                });
        }
    };

if (!hasAction('seeFolderContents')) {
    ensoConf.switchApp(ensoConf.defaultApp);
}

SysAdminFolderManagement.initModals();
SysAdminFolderManagement.loadFolderList();
attachSearchAction(SysAdminFolderManagement.loadFolderList);
$('#root-folder-list').hide();
$('.tooltipped').tooltip({ delay: 50 });

//# sourceURL=sysadmin_manage_folders.js