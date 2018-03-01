var ConfirmationModal = {
    show: function (text, onConfirm = undefined, onCancel = undefined, divToHide = undefined)
    {
        pageUrl = ensoConf.viewsPath + "confirmation_modal.html";
        $.ajax({
            type: "GET",
            dataType: "html",
            cache: false,
            url: pageUrl,
            success: function (response) {
                $("#main-content").append(response);
                
                $("#conf-modal .modal-content").append("<p class='flow-text'>");
                
                $("#conf-modal .modal-content").append(text);
                
                $("#conf-modal .modal-content").append("</p>");
                
                LocalizationManager.applyLocaleSettings();
                
                $(".tooltipped").tooltip();

                ConfirmationModal.userOnCancel = onCancel;
                ConfirmationModal.userOnConfirm = onConfirm;

                $("#conf-modal").modal(
                        {
                            dismissible: false,
                            endingTop: '40%',
                            ready: ModalUtils.coverNavbar,
                            complete: function ()
                            {                                
                                if(divToHide !== undefined)
                                    $('#' + divToHide).show();
                                
                                $("#conf-modal").remove();
                                ModalUtils.refreshTooltips();
                            }
                        });

                if(divToHide !== undefined)
                    $('#' + divToHide).hide();

                $("#conf-modal").modal('open');
            },
            error: function (response) {
            }
        });
    },

    onConfirm: function ()
    {
        if (ConfirmationModal.userOnConfirm !== undefined)
            ConfirmationModal.userOnConfirm();

        $("#conf-modal").modal('close');
    },
    onCancel: function ()
    {
        if (ConfirmationModal.userOnCancel !== undefined)
            ConfirmationModal.userOnCancel();

        $("#conf-modal").modal('close');
    },

    userOnConfirm: undefined,
    userOnCancel: undefined
};

//# sourceURL=ConfirmationModal.js