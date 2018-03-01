var FolderActions =
    {
        saveFolderInfo: function (create, folderId, name, permissions, successFunction = undefined, failFunction = undefined) //Se create == true folderId  é o pai, senão é a folder a editar, ver API
        {
            var pageUrl = REST_SERVER_PATH + "folder/";
            $.ajax({
                type: (create === true ? "POST" : "PUT"),
                dataType: "json",
                cache: false,
                data: {
                    name: name,
                    permissions: permissions,
                    authusername: Cookies.get('username'),
                    sessionkey: Cookies.get('sessionkey'),
                    folderId: folderId
                },
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

        requestFolderInfo: function (folderId, successFunction) {
            var pageUrl = REST_SERVER_PATH + "folder/";

            $.ajax({
                type: "GET",
                dataType: "json",
                cache: false,
                url: pageUrl,
                data: { folderId: folderId, sessionkey: Cookies.get('sessionkey'), authusername: Cookies.get('username') },
                success: function (response) {
                    successFunction(response);
                },
                error: function (response) {
                    dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('folder_modal', 'validationErrors'));
                }
            });
        },

        getFolderPath: function (folderId, successFunction) {
            var pageUrl = REST_SERVER_PATH + "folder/getPath/";
            $.ajax({
                type: "GET",
                dataType: "json",
                cache: false,
                data: {
                    authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey'),
                    folderId: folderId
                },
                url: pageUrl,
                success: function (response) {
                    successFunction(response);
                },
                error: function (response) {
                    dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('folder_modal', 'validationErrors'));
                }
            });
        },

        removeFolder: function (folderId, successFunction = undefined, failFunction = undefined) {
            var pageUrl = REST_SERVER_PATH + "folder/";

            $.ajax({
                type: "DELETE",
                dataType: "json",
                cache: false,
                data: { folderId: folderId, authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey') },
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