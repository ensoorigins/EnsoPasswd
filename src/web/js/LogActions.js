var LogActions =
        {
            getFilterInfo: function (successFunction = undefined, failFunction = undefined)
            {
                pageUrl = REST_SERVER_PATH + "logFilters/";
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    cache: false,
                    data: {authusername: Cookies.get('username'),
                        sessionkey: Cookies.get('sessionkey')},
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
            },

            getLogs: function (startIndex, advance, startTime, endTime, facility, severity, userSearch, searchString, successFunction, failFunction = undefined)
            {
                pageUrl = REST_SERVER_PATH + "logs/";
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    cache: false,
                    data: {authusername: Cookies.get('username'),
                        sessionkey: Cookies.get('sessionkey'),
                        startTime: startTime,
                        endTime: endTime,
                        facility: facility,
                        severity: severity,
                        userSearch: userSearch,
                        startIndex: startIndex,
                        advance: advance,
                        search: searchString
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

        //# sourceURL=LogActions.js