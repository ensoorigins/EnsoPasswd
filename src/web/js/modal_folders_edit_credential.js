var CredentialModal =
        {
            isEditMode: null,

            enableLookMode: function ()
            {
                $("#edit-title").prop('disabled', true);
                $("#edit-username").prop('disabled', true);
                $("#edit-password").prop('disabled', true);
                $("#edit-description").prop('disabled', true);
                $("#edit-url").prop('disabled', true);

                $("#edit-title").addClass("readonly");
                $("#edit-username").addClass("readonly");
                $("#edit-password").addClass("readonly");
                $("#edit-description").addClass("readonly");
                $("#edit-url").addClass("readonly");



                $("#edit-generate-password").hide();

                $(".modal-footer").hide();

                $("#view-mode").text("lock_outline");
                $("#view-mode").attr("data-tooltip", LocalizationManager.getEnumFromView('credential_modal', 'lockButtonState')['locked']);
                $('#view-mode').tooltip({delay: 50});

                CredentialModal.isEditMode = false;
            },

            enableEditMode: function ()
            {
                $("#edit-title").prop('disabled', false);
                $("#edit-username").prop('disabled', false);
                $("#edit-password").prop('disabled', false);
                $("#edit-description").prop('disabled', false);
                $("#edit-url").prop('disabled', false);
                $("#edit-title").focus();

                $("#edit-title").removeClass("readonly");
                $("#edit-username").removeClass("readonly");
                $("#edit-password").removeClass("readonly");
                $("#edit-description").removeClass("readonly");
                $("#edit-url").removeClass("readonly");

                $("#edit-generate-password").show();

                $(".modal-footer").show();

                $("#view-mode").text("lock_open");
                $("#view-mode").attr("data-tooltip", LocalizationManager.getEnumFromView('credential_modal', 'lockButtonState')['unlocked']);
                $('#view-mode').tooltip({delay: 50});

                CredentialModal.isEditMode = true;
            },

            switchViewMode: function ()
            {
                if (CredentialModal.isEditMode)
                    CredentialModal.enableLookMode();
                else
                    CredentialModal.enableEditMode();
            },

            copyPasswordToClipboard: function ()
            {
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val($("#edit-password").val()).select();
                document.execCommand("copy");
                $temp.remove();
                Materialize.toast(LocalizationManager.getStringFromView('credential_modal', "password-copy"), 2000);
            },

            copyUsernameToClipboard: function ()
            {
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val($("#edit-username").val()).select();
                document.execCommand("copy");
                $temp.remove();
                Materialize.toast(LocalizationManager.getStringFromView('credential_modal', "user-copy"), 2000);
            },

            openUrl: function ()
            {
                var win = window.open($("#edit-url").val(), '_blank');
                win.focus();
            }


        };