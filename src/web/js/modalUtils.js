var ModalUtils =
        {
            coverNavbar: function ()
            {
                lowestZ = 9999;

                $(".modal-overlay").each(function () {
                    
                    if ($(this).css('z-index') < lowestZ)
                        lowestZ = $(this).css('z-index');
                });

                $(".navbar-fixed").css('z-index', lowestZ);
            },
            modalIsValid : function()
            {
                isValid = true;
                
                $("#validation-form").find(".validate").each(function() {
                    if($(this).hasClass("invalid"))
                        isValid = false;
                });
                
                if(!isValid)
                    $("#validation").click();
                
                return isValid;
            },
            refreshTooltips: function()
            {
                $('.material-tooltip').remove();
                $('.tooltipped').tooltip();
            }
        };

        //# sourceURL=modalUtils.js