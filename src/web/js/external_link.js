var ExternalLink = {
    getParameterByName: function (name, url) {  //@ https://stackoverflow.com/a/901144
        if (!url)
            url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                results = regex.exec(url);
        if (!results)
            return null;
        if (!results[2])
            return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    },

    load: function ()
    {       
        LocalizationManager.applyLocaleSettingsToGivenView('message_modal');
        LocalizationManager.applyLocaleSettingsToGivenView('share_modal');


        externalKey = ExternalLink.getParameterByName('externalKey');

        if (externalKey == '' || externalKey == null)
        {
            ExternalLink.externalMessageNotFound();
        } else
        {

            ExternalMessageActions.getExternalMessage(externalKey, function (message) {
                $("#edit-title").val(message['title']);
                $("#edit-username").val(message['username']);
                $("#edit-password").val(message['password']);
                $("#edit-description").val(message['description']);
                $("#edit-url").val(message['url']);
                $("#edit-message").val(message['message']);
                $("#edit-message-id").val(message['idMessages']);
                $("#intro-text").empty().text(LocalizationManager.getStringFromView('external_link', 'external-found'));

                Materialize.updateTextFields();
            },
                    function () {
                        ExternalLink.externalMessageNotFound();
                    });
        }
    },

    externalMessageNotFound: function ()
    {
        $("#validation-form").remove();
        $("#intro-text").empty().text(LocalizationManager.getStringFromView('external_link', 'external-not-found'));
    }
};

ExternalLink.load();

