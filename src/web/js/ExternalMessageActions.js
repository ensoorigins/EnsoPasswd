var ExternalMessageActions = {
    getExternalMessage: function (externalKey, successFunction = undefined, failFunction = undefined) {
        pageUrl = REST_SERVER_PATH + "externalMessage/";

        $.ajax({
            type: "GET",
            dataType: "json",
            cache: false,
            data: {
                externalKey: externalKey,
            },
            url: pageUrl,
            success: function (response) {
                response['password'] = EnsoShared.networkDecode(response['password']);

                if (successFunction !== undefined)
                    successFunction(response);

            },
            error: function (response) {
                dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('share_modal', 'validationErrors'));

                if (failFunction !== undefined)
                    failFunction(response);
            }
        });
    },
    shareExisting: function (receiver, credential, message, timeToDie, serverpath, successFunction = undefined, failFunction = undefined) {
        pageUrl = REST_SERVER_PATH + "shareExternal/";

        $.ajax({
            type: "POST",
            dataType: "json",
            cache: false,
            data: {
                authusername: Cookies.get('username'),
                sessionkey: Cookies.get('sessionkey'),
                receiver: receiver,
                referencedCredential: credential,
                message: message,
                timeToDie: timeToDie,
                serverpath: serverpath
            },
            url: pageUrl,
            success: function (response) {
                if (successFunction !== undefined)
                    successFunction(response);
            },
            error: function (response) {
                dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('share_modal', 'validationErrors'));

                if (failFunction !== undefined)
                    failFunction(response);
            }
        });
    },

    shareNew: function (title, username, password, description, url, receiver, message, timeToDie, serverpath, successFunction = undefined, failFunction = undefined) {
        password = EnsoShared.networkEncode(password);

        pageUrl = REST_SERVER_PATH + "shareExternal/";

        $.ajax({
            type: "POST",
            dataType: "json",
            cache: false,
            data: {
                authusername: Cookies.get('username'),
                sessionkey: Cookies.get('sessionkey'),
                receiver: receiver,
                message: message,
                timeToDie: timeToDie,
                title: title,
                username: username,
                password: password,
                description: description,
                url: url,
                serverpath: serverpath
            },
            url: pageUrl,
            success: function (response) {
                if (successFunction !== undefined)
                    successFunction(response);
            },
            error: function (response) {
                dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('share_modal', 'validationErrors'));

                if (failFunction !== undefined)
                    failFunction(response);
            }
        });
    }

};

//# sourceURL=ExternalMessageActions.js