var UserModal =
        {
            isEditMode: null,

            enableLookMode: function ()
            {
                $("#edit-email").prop('disabled', true);
                $("#edit-password").prop('disabled', true);
                $("#edit-ldap").prop('disabled', true);
                $("#edit-sysadmin").prop('disabled', true);
                
                $("#edit-username").addClass("readonly");
                $("#edit-email").addClass("readonly");
                $("#edit-password").addClass("readonly");
                $("#edit-ldap").addClass("readonly");
                $("#edit-sysadmin").addClass("readonly");

                $(".modal-footer").hide();

                $("#view-mode").text("lock_outline");
                $("#view-mode").attr("data-tooltip", LocalizationManager.getEnumFromView('user_edit_modal', 'lockButtonState')['unlocked']);
                $('#view-mode').tooltip({delay: 50});

                UserModal.isEditMode = false;
            },

            enableEditMode: function ()
            {
                $("#edit-email").prop('disabled', false);
                $("#edit-password").prop('disabled', false);
                $("#edit-ldap").prop('disabled', false);
                $("#edit-sysadmin").prop('disabled', false);
                $("#edit-email").focus();
                
                $("#edit-email").removeClass("readonly");
                $("#edit-password").removeClass("readonly");
                $("#edit-ldap").removeClass("readonly");
                $("#edit-sysadmin").removeClass("readonly");

                $(".modal-footer").show();

                $("#view-mode").text("lock_open");
                $("#view-mode").attr("data-tooltip", LocalizationManager.getEnumFromView('user_edit_modal', 'lockButtonState')['locked']);
                $('#view-mode').tooltip({delay: 50});

                UserModal.isEditMode = true;
            },

            switchViewMode: function ()
            {
                if (UserModal.isEditMode)
                    UserModal.enableLookMode();
                else
                    UserModal.enableEditMode();
            }
        };