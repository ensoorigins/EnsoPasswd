var CredentialActions =
    {
        saveCredentialInfo: function (create, id, title, username, password, description, url, belongsTo, successFunction = undefined, failFunction = undefined) //Se create == true folderId  é o pai, senão é a folder a editar, ver API
        {
            password = EnsoShared.networkEncode(password);

            dataArray = {
                title: title,
                username: username,
                password: password,
                description: description,
                url: url,
                belongsTo: belongsTo,
                id: id,
                authusername: Cookies.get('username'),
                sessionkey: Cookies.get('sessionkey')
            };

            pageUrl = REST_SERVER_PATH + "credential/";


            $.ajax({
                type: (create === true ? "POST" : "PUT"),
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

                    //ensoConf.switchApp(ensoConf.defaultApp);
                }
            });

        },
        requestCredentialInfo: function (credentialId, successFunction, failFunction = undefined, async = true) {
            pageUrl = REST_SERVER_PATH + "credential/";

            $.ajax({
                type: "GET",
                dataType: "json",
                cache: false,
                url: pageUrl,
                async: async,
                data: { credentialId: credentialId, sessionkey: Cookies.get('sessionkey'), authusername: Cookies.get('username') },
                success: function (response) {
                    response['password'] = EnsoShared.networkDecode(response['password']);

                    successFunction(response);
                },
                error: function (response) {
                    dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('credential_modal', 'validationErrors'));

                    if (failFunction !== undefined)
                        failFunction(response);
                }
            });
        },
        removeCredential: function (credentialId, successFunction = undefined, failFunction = undefined) {
            pageUrl = REST_SERVER_PATH + "credential/";

            $.ajax({
                type: "DELETE",
                dataType: "json",
                cache: false,
                url: pageUrl,
                data: { credentialId: credentialId, sessionkey: Cookies.get('sessionkey'), authusername: Cookies.get('username') },
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
        }
    };