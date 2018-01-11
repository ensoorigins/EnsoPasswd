var UserActions =
        {
            requestUserInfo: function (username, successFunction)
            {

                pageUrl = REST_SERVER_PATH + "users/";

                $.ajax({
                    type: "GET",
                    dataType: "json",
                    cache: false,
                    url: pageUrl,
                    data: {username: username, sessionkey: Cookies.get('sessionkey'), authusername: Cookies.get('username')},
                    success: function (response) {
                        successFunction(response);
                    },
                    error: function (response)
                    {
                        dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('user_edit_modal', 'validationErrors'));
                    }
                });
            },
            saveUserInfo: function (create, username, email, ldap, sysadmin, password, successFunction = undefined, failFunction = undefined )
            {
                dataArray = {username: username,
                    email: email,
                    ldap: ldap,
                    sysadmin: sysadmin,
                    password: password,
                    authusername: Cookies.get('username'),
                    sessionkey: Cookies.get('sessionkey')
                };

                pageUrl = REST_SERVER_PATH + "users/";

                $.ajax({
                    type: (create === true ? "POST" : "PUT"),
                    dataType: "json",
                    cache: false,
                    data: dataArray,
                    url: pageUrl,
                    success: function (response) {
                        if(successFunction !== undefined)
                            successFunction(response);
                    },
                    error: function (response) {
                        dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('user_edit_modal', 'validationErrors'));
                        
                        if(failFunction !== undefined)
                            failFunction(response);
                    }
                });
            },

            removeUser: function (username, successFunction = undefined, failFunction = undefined)
            {
                pageUrl = REST_SERVER_PATH + "users/";

                $.ajax({
                    type: "DELETE",
                    dataType: "json",
                    cache: false,
                    data: {username: username, authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey')},
                    url: pageUrl,
                    success: function (response) {
                        if(successFunction !== undefined)
                            successFunction(response);
                    },
                    error: function (response) {
                        dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('user_edit_modal', 'validationErrors'));
                        
                        if(failFunction !== undefined)
                            failFunction(response);
                    }
                });
            },

            getUserList: function (search, successFunction = undefined, failFunction = undefined)
            {
                pageUrl = REST_SERVER_PATH + "users/search";

                $.ajax({
                    type: "GET",
                    dataType: "json",
                    cache: false,
                    data: {search: search,
                        authusername: Cookies.get('username'), sessionkey: Cookies.get('sessionkey')},
                    url: pageUrl,
                    success: function (response) {
                        if(successFunction !== undefined)
                            successFunction(response);
                    },
                    error: function (response) {
                        dealWithErrorStatusCodes(response, LocalizationManager.getEnumFromView('user_edit_modal', 'validationErrors'));
                        
                        if(failFunction !== undefined)
                            failFunction(response);
                    }
                });
            }
        };