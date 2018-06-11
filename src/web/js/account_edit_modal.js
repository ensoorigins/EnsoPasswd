var AccountModal = {
    show: function ()
    {
        var pageUrl =ensoConf.viewsPath + "modal_normal_user_user_edit.html";
        $.ajax({
            type: "GET",
            dataType: "html",
            cache: false,
            url: pageUrl,
            success: function (response) {
                $("#main-content").append(response);

                $(".tooltipped").tooltip();

                $("#account-modal").modal(
                        {
                            dismissible: true,
                            endingTop: '30%',
                            ready: ModalUtils.coverNavbar,
                            complete: function ()
                            {
                                $("#account-modal").remove();
                                ModalUtils.refreshTooltips();
                            }
                        });

                UserActions.requestUserInfo(Cookies.get('username'), function (userInfo) {

                    $("#edit-username").val(userInfo['username']);
                    $("#edit-email").val(userInfo['email']);

                    Materialize.updateTextFields();
                    $("#edit-email").focus();
                });

                $("#account-modal").modal('open');
            },
            error: function (response) {
            }
        });
    },

    saveInfo: function ()
    {
        if (ModalUtils.modalIsValid())
            UserActions.saveUserInfo(
                    false,
                    $("#edit-username").val(),
                    $("#edit-email").val(),
                    null,
                    null,
                    $("#edit-password").val(),
                    function () {
                        $("#account-modal").modal('close');
                    }
            );


    },
    close: function ()
    {
        $("#account-modal").modal('close');
    },
};

//# sourceURL=account_edit_modal.js