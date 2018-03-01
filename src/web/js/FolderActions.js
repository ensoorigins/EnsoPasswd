var FolderActions =
        {
            saveFolderInfo: function (create, folderId, name, permissions, successFunction = undefined, failFunction = undefined) //Se create == true folderId  é o pai, senão é a folder a editar, ver API
            {
                dataArray = {name: name,
                    permissions: permissions,
                    authusername: Cookies.get('username'),
                    sessionkey: Cookies.get('sessionkey'),
                    folderId: folderId
                };
                pageUrl = REST_SERVER_PATH + "folder/";
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
                        dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('folder_modal', 'validationErrors'));

                        if (failFunction !== undefined)
                            failFunction(response);
                    }
                });
            },

            requestFolderInfo: function (folderId, successFunction)
            {
                pageUrl = REST_SERVER_PATH + "folder/";

                $.ajax({
                    type: "GET",
                    dataType: "json",
                    cache: false,
                    url: pageUrl,
                    data: {folderId: folderId, sessionkey: Cookies.get('sessionkey'), authusername: Cookies.get('username')},
                    success: function (response) {
                        successFunction(response);
                    },
                    error: function (response) {
                        dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('folder_modal', 'validationErrors'));
                    }
                });
            },

            getFolderPath: function (folderId, successFunction)
            {
                pageUrl = REST_SERVER_PATH + "folder/getPath/";
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    cache: false,
                    data: {authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey'),
                        folderId: folderId},
                    url: pageUrl,
                    success: function (response) {
                        successFunction(response);
                    },
                    error: function (response) {
                        dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('folder_modal', 'validationErrors'));
                    }
                });
            },

            removeFolder: function (folderId, successFunction = undefined, failFunction = undefined)
            {
                pageUrl = REST_SERVER_PATH + "folder/";

                $.ajax({
                    type: "DELETE",
                    dataType: "json",
                    cache: false,
                    data: {folderId: folderId, authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey')},
                    url: pageUrl,
                    success: function (response) {
                        if (successFunction !== undefined)
                            successFunction(response);
                    },
                    error: function (response) {
                        dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('folder_modal', 'validationErrors'));

                        if (failFunction !== undefined)
                            failFunction(response);
                    }
                });
            }
        };

        //# sourceURL=FolderActions.js