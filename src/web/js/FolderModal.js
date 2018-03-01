var FolderModal =
    {
        isEditMode: null,

        enableLookMode: function () {
            $("#edit-name").prop('disabled', true);
            $("#autocomplete-row").hide();

            $("#edit-name").addClass("readonly");

            $("#user-list-body tr").each(function (index) {
                $($($(this).children()[1]).children()[0]).prop("disabled", true);
                $($($(this).children()[2]).children()[0]).hide();
            });

            $(".modal-footer").hide();

            $("#view-mode").text("lock_outline");
            $("#view-mode").attr("data-tooltip", LocalizationManager.getEnumFromView('folder_modal', 'lockButtonState')['locked']);
            $('#view-mode').tooltip({ delay: 50 });

            FolderModal.isEditMode = false;
        },

        enableEditMode: function () {
            $("#edit-name").prop('disabled', false);
            $("#edit-name").focus();
            $("#autocomplete-row").show();

            $("#edit-name").removeClass("readonly");

            $("#user-list-body tr").each(function (index) {
                $($($(this).children()[1]).children()[0]).prop("disabled", false);
                $($($(this).children()[2]).children()[0]).show();
            });

            $(".modal-footer").show();

            $("#view-mode").text("lock_open");
            $("#view-mode").attr("data-tooltip", LocalizationManager.getEnumFromView('folder_modal', 'lockButtonState')['unlocked']);
            $('#view-mode').tooltip({ delay: 50 });

            FolderModal.isEditMode = true;
        },

        switchViewMode: function () {
            if (FolderModal.isEditMode)
                FolderModal.enableLookMode();
            else
                FolderModal.enableEditMode();
        },

        getCurrentPermissionsList: function () {
            var perms = new Object();

            $("#user-list-body tr").each(function (index) {
                perms[$($($(this).children()[0]).children()[1]).text()] = ($($($(this).children()[1]).children()[0]).is(":checked") == true ? 1 : 0);
            });

            return perms;
        },

        permissionChanged: function (which) {
            if ($(which).is(":checked")) {
                $("#label-for-" + $(which).attr("id").split("-").pop()).text(LocalizationManager.getEnumFromView('folder_modal', 'userType')["1"]);
            } else
                $("#label-for-" + $(which).attr("id").split("-").pop()).text(LocalizationManager.getEnumFromView('folder_modal', 'userType')["0"]);
        },

        parsePermissionsAndAddToModal: function (permissions) {
            $.each(permissions, function (key, val) {
                FolderModal.addPermission(val['userId'], val['hasAdmin'], val['sysadmin']);
            });

            LocalizationManager.applyLocaleSettingsToGivenView('folder_modal');
        },

        addPermission: function (userId, hasAdmin, sysadmin) {
            $("#user-list-body").append("<tr>\
                <td><i class='material-icons tiny' style='padding-right: 1em'>" + (sysadmin == 1 ? "build" : "person") + "</i><span>" + userId + "</span> </td>\
                <td><input onchange='FolderModal.permissionChanged(this)' type='checkbox' id='admin-" + userId + "' " + (hasAdmin == 1 ? "checked" : "") + "/>\
                    <label id='label-for-" + userId + "' for='admin-" + userId + "'>" + LocalizationManager.getEnumFromView('folder_modal', 'userType')[hasAdmin] + "</label></td>\
                <td onclick='$(this).parent().remove()'><button data-target='user-edit-modal' class='right enso-main-color btn modal-trigger remove-access-button'></button></td>\
             </tr>");
        },

        feedAutoComplete: function () {
            var pageUrl = REST_SERVER_PATH + "users/search/";

            $.ajax({
                type: "GET",
                dataType: "json",
                cache: false,
                data: { authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey') },
                url: pageUrl,
                success: function (response) {
                    var names = new Object();

                    $.each(response, function (key, val) {
                        names[val['username']] = null;
                    });

                    $('input.autocomplete').autocomplete({
                        data: names,
                        limit: 20, // The max amount of results that can be shown at once. Default: Infinity.
                        onAutocomplete: function (val) {
                            if (FolderModal.getCurrentPermissionsList()[val] !== undefined)
                                Materialize.toast('Utilizador já existe na lista de permissões.', 3000, 'rounded');
                            else {

                                UserActions.requestUserInfo(val, function (user) {

                                    FolderModal.addPermission(val, 0, user['sysadmin']);

                                    LocalizationManager.applyLocaleSettingsToGivenView('folder_modal');

                                    $('input.autocomplete').val('');
                                });

                            }
                        },
                        minLength: 1, // The minimum length of the input for the autocomplete to start. Default: 1.
                    });
                },
                error: function (response) {

                    console.log(response);
                    dealWithErrorStatusCodes(response, undefined);



                    //ensoConf.switchApp(ensoConf.defaultApp);
                }
            });
        }
    };

FolderModal.feedAutoComplete();

//# sourceURL=FolderModal.js