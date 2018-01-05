var LocalizationManager =
        {
            localization: {},
            defaultLanguage: "pt",
            availableLanguages: undefined,
            LocaleLocation: "localization/",
            language: undefined,
            init: function ()
            {
                $.ajax({//Em caso de erro dá defualt para  porque a lingua pedida não foi encontrada
                    type: "GET",
                    dataType: "json",
                    cache: false,
                    url: LocalizationManager.LocaleLocation + "/localization.json",
                    async: false,
                    success: function (response) {
                        LocalizationManager.defaultLanguage = response["defaultLanguage"];
                        LocalizationManager.availableLanguages = response["languages"];
                    },
                    error: function (response) {
                        console.log("Localization not found on server");
                    }
                });
                if (Cookies.get('preferredLanguage') === undefined)
                {
                    LocalizationManager.language = window.navigator.language.split('-')[0];
                    Cookies.set('preferredLanguage', LocalizationManager.language);
                } else
                {
                    LocalizationManager.language = Cookies.get('preferredLanguage');
                }

                ensoConf.addAfterViewCallback(LocalizationManager.applyLocaleSettings);
                LocalizationManager.applyLocaleSettings();
            },
            changeLanguage: function (newLanguage)
            {
                LocalizationManager.language = newLanguage;
                Cookies.set('preferredLanguage', LocalizationManager.language);
                LocalizationManager.localization = {};
                LocalizationManager.applyLocaleSettings();
            },
            fetchLocalizationForView: function (which)
            {                
                if (which == "" || LocalizationManager.localization[which] !== undefined)
                    return;
                
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    cache: false,
                    url: LocalizationManager.LocaleLocation + LocalizationManager.language + "/" + which + ".json",
                    async: false,
                    success: function (response) {
                        $.extend(LocalizationManager.localization, response);
                    },
                    error: function (response) {
                        $.ajax({//Em caso de erro dá default para  porque a lingua pedida não foi encontrada
                            type: "GET",
                            dataType: "json",
                            cache: false,
                            url: LocalizationManager.LocaleLocation + LocalizationManager.defaultLanguage + "/" + which + ".json",
                            async: false,
                            success: function (response) {
                                $.extend(LocalizationManager.localization, response);
                            },
                            error: function (response) {
                                console.log("Both requested language and default language were not found on the server.");
                            }
                        });
                    }
                });
            },
            applyLocaleSettingsToGivenView(view)
            {
                if (LocalizationManager.localization[view] === undefined)
                {
//the locales for this view have not been loaded yet

                    LocalizationManager.fetchLocalizationForView(view); //Fetch app-wide locales
                }

                $.each(LocalizationManager.localization[view], function (key, val)
                {
                    searchString = (val['applyTo'] === "class" ? "." : "#");
                    searchString += key;
                    if (val['text'] != "")
                        $(searchString).text(val['text']);
                    $.each(val['attributes'], function (key, val)
                    {
                        $(searchString).attr(key, val);
                    });
                });
            },
            applyLocaleSettings: function ()
            {
                //Apply app-wide settings
                LocalizationManager.applyLocaleSettingsToGivenView('app-wide');
                //apply currentView settings
                LocalizationManager.applyLocaleSettingsToGivenView(ensoConf.getCurrentPage());
            },
            getEnumFromView: function (view, enumerator)
            {
                if (LocalizationManager.localization[view] === undefined)
                {
                    //the locales for this view have not been loaded yet

                    LocalizationManager.fetchLocalizationForView(view); //Fetch app-wide locales
                }

                return LocalizationManager.localization[view]['enums'][enumerator];
            },

            getStringFromView: function (view, string)
            {
                if (LocalizationManager.localization[view] === undefined)
                {
                    //the locales for this view have not been loaded yet

                    LocalizationManager.fetchLocalizationForView(view); //Fetch app-wide locales
                }

                return LocalizationManager.localization[view]['strings'][string];
            }
        }
;
LocalizationManager.init();