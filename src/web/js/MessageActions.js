var MessageActions = {
    getInbox: function (successFunction = undefined, failFunction = undefined) {
        pageUrl = REST_SERVER_PATH + "inbox/";

        $.ajax({
            type: "GET",
            dataType: "json",
            cache: false,
            data: {
                authusername: Cookies.get('username'),
                sessionkey: Cookies.get('sessionkey')
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
    getOutbox: function (successFunction = undefined, failFunction = undefined) {
        pageUrl = REST_SERVER_PATH + "outbox/";

        $.ajax({
            type: "GET",
            dataType: "json",
            cache: false,
            data: {
                authusername: Cookies.get('username'),
                sessionkey: Cookies.get('sessionkey')
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

    shareExisting: function (receiver, credential, message, timeToDie, successFunction = undefined, failFunction = undefined) {
        pageUrl = REST_SERVER_PATH + "share/";

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
                timeToDie: timeToDie
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

    shareNew: function (title, username, password, description, url, receiver, message, timeToDie, successFunction = undefined, failFunction = undefined) {
        password = EnsoShared.networkEncode(password);

        pageUrl = REST_SERVER_PATH + "share/";

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
                url: url
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

    getMessage: function (messageId, successFunction = undefined, failFunction = undefined) {
        pageUrl = REST_SERVER_PATH + "message/";

        $.ajax({
            type: "GET",
            dataType: "json",
            cache: false,
            data: {
                authusername: Cookies.get('username'),
                sessionkey: Cookies.get('sessionkey'),
                messageId: messageId
            },
            url: pageUrl,
            success: function (response) {
                response['password'] = EnsoShared.networkDecode(response['password']);

                if (successFunction !== undefined)
                    successFunction(response);

            },
            error: function (response) {
                dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('message_modal', 'validationErrors'));

                if (failFunction !== undefined)
                    failFunction(response);
            }
        });
    },

    receiveCredential: function (messageId, title, username, password, description, url, belongsTo, successFunction = undefined, failFunction = undefined) {
        password = EnsoShared.networkEncode(password);

        dataArray = {
            title: title,
            username: username,
            password: password,
            description: description,
            url: url,
            belongsTo: belongsTo,
            messageId: messageId,
            authusername: Cookies.get('username'),
            sessionkey: Cookies.get('sessionkey')
        };

        pageUrl = REST_SERVER_PATH + "message/";

        $.ajax({
            type: "POST",
            dataType: "json",
            cache: false,
            data: dataArray,
            url: pageUrl,
            success: function (response) {

                if (successFunction !== undefined)
                    successFunction(response);
            },
            error: function (response) {
                dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('credential_modal', 'validationErrors'));

                if (failFunction !== undefined)
                    failFunction(response);
            }
        });
    },
    removeMessage: function (messageId, successFunction = undefined, failFunction = undefined) {
        pageUrl = REST_SERVER_PATH + "message/";

        $.ajax({
            type: "DELETE",
            dataType: "json",
            cache: false,
            data: {
                messageId: messageId,
                authusername: Cookies.get('username'),
                sessionkey: Cookies.get('sessionkey')
            },
            url: pageUrl,
            success: function (response) {
                if (successFunction !== undefined)
                    successFunction(response);
            },
            error: function (response) {
                dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('credential_modal', 'validationErrors'));

                if (failFunction !== undefined)
                    failFunction(response);
            }
        });
    },
    getInboxCount: function (successFunction = undefined, failFunction = undefined) {
        pageUrl = REST_SERVER_PATH + "inboxCount/";

        $.ajax({
            type: "GET",
            dataType: "json",
            cache: false,
            data: {
                authusername: Cookies.get('username'),
                sessionkey: Cookies.get('sessionkey')
            },
            url: pageUrl,
            success: function (response) {

                if (successFunction !== undefined)
                    successFunction(response);

            },
            error: function (response) {
                dealWithErrorStatusCodes(response, undefined);

                if (failFunction !== undefined)
                    failFunction(response);
            }
        });
    }
};