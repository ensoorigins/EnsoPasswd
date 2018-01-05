var LanguageModal = {
    show: function ()
    {
        pageUrl = ensoConf.viewsPath + "language_modal.html";
        $.ajax({
            type: "GET",
            dataType: "html",
            cache: false,
            url: pageUrl,
            success: function (response) {
                $("#main-content").append(response);

                $(".tooltipped").tooltip();

                $("#language-modal").modal(
                        {
                            dismissible: true,
                            endingTop: '30%',
                            ready: ModalUtils.coverNavbar,
                            complete: function ()
                            {
                                $("#language-modal").remove();
                                ModalUtils.refreshTooltips();
                            }
                        });

                //fill language options
                
                html = "<div class='row'><form>";
                
                $.each(LocalizationManager.availableLanguages, function(key, val)
                {
                    html += "<div class='col s4'>\
                                                            <input class='with-gap' name='language-group' type='radio' value='" + key + "' id='language-" + key + "' />\
                                                            <label for='language-" + key + "'><p class='valign-wrapper' style='margin: 0px;'><img style='padding-right: 5px;' src='" + val["image"] + "' ><span>" + val["name"] + "</span></p></label>\
                                                        </div>";
                });
                
                html += "</form></div>";
                
                $("#language-modal-content").append(html);
                
                $("#language-" + LocalizationManager.language).attr("checked", true);

                $("#language-modal").modal('open');
            },
            error: function (response) {
            }
        });
    },

    saveInfo: function ()
    {

        LocalizationManager.changeLanguage( $('input[name=language-group]:checked', '#language-modal-content').val());
        //LocalizationManager.applyLocaleSettings();

        $("#language-modal").modal('close');


    },
    close: function ()
    {
        $("#language-modal").modal('close');
    }
};
