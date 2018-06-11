var SysAdminUserManagement = {

    loadUserList: function ()
    {
        $("#user-list-body").empty();
        UserActions.getUserList(
                ($(window).width() > 992 ? $("#pesquisa-desktop").val() : $("#pesquisa-mobile").val()),
                function (userList)
                {
                    $.each(userList, function (key, val) {
                        $("#user-list-body").append(
                                                   "<tr onclick='SysAdminUserManagement.launchUserEditModal(\"" + val['username'] + "\")' style='cursor: pointer;'>\
                                                        <td>" + val['username'] + "</td>\
                                                        <td>" + val['email'] + "</td>\
                                                    </tr>\
                                                    <tr>");
                    });
                });
    },
    launchUserEditModal: function (username)
    {
        //prepare modal html

        var pageUrl =ensoConf.viewsPath + "modal_user_edit.html";
        $.ajax({
            type: "GET",
            dataType: "html",
            cache: false,
            url: pageUrl,
            success: function (response) {
                $('.modal').empty().append(response);

                UserActions.requestUserInfo(username, function (userInfo)
                {
                    $("#edit-username").val(userInfo['username']);
                    $("#edit-email").val(userInfo['email']);
                    $("#edit-ldap").prop("checked", userInfo['ldap'] == 1 ? true : false);
                    $("#edit-sysadmin").prop("checked", userInfo['sysadmin'] == 1 ? true : false);
                    Materialize.updateTextFields();
                });
            },
            error: function (response) {
                //ensoConf.switchApp(ensoConf.defaultApp);
            }
        });
        //launchmodal

        $('#user-modal').modal('open');
    },
    launchUserAddModal: function ()
    {
        var pageUrl =ensoConf.viewsPath + "modal_user_add.html";
        $.ajax({
            type: "GET",
            dataType: "html",
            cache: false,
            url: pageUrl,
            success: function (response) {
                $('.modal').empty().append(response);
            },
            error: function (response) {
                ensoConf.switchApp(ensoConf.defaultApp);
            }
        });
        //launchmodal

        $('#user-modal').modal('open');
    },
    initModals: function ()
    {
        $('.modal').modal({
            ready: ModalUtils.coverNavbar,
            complete: function () {
                SysAdminUserManagement.loadUserList();
                ModalUtils.refreshTooltips();
            }
        });
    },
    saveUserInfo: function ()
    {
        if (ModalUtils.modalIsValid())
            UserActions.saveUserInfo(
                    false,
                    $("#edit-username").val(),
                    $("#edit-email").val(),
                    ($("#edit-ldap").is(":checked") == true ? 1 : 0),
                    ($("#edit-sysadmin").is(":checked") == true ? 1 : 0),
                    $("#edit-password").val(),
                    function () {
                        $('#user-modal').modal('close');
                    }
            );

    },
    createUser: function ()
    {
        if (ModalUtils.modalIsValid())
            UserActions.saveUserInfo(
                    true,
                    $("#edit-username").val(),
                    $("#edit-email").val(),
                    ($("#edit-ldap").is(":checked") == true ? 1 : 0),
                    ($("#edit-sysadmin").is(":checked") == true ? 1 : 0),
                    $("#edit-password").val(),
                    function () {
                        $('#user-modal').modal('close');
                    }
            );
    },
    removeUser: function ()
    {
        UserActions.removeUser($("#edit-username").val(),
                function () {
                    $('#user-modal').modal('close');
                }
        );
    }
}

if (!hasAction('listUsers') || !hasAction('manageUsers'))
{
    ensoConf.switchApp(ensoConf.defaultApp);
}

SysAdminUserManagement.initModals();
SysAdminUserManagement.loadUserList();
attachSearchAction(SysAdminUserManagement.loadUserList);
LocalizationManager.applyLocaleSettings();
$('.tooltipped').tooltip({delay: 50});

//# sourceURL=sysadmin_user_list.js