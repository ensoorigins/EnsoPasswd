var SysAdminLogView =
        {
            timeFormat: "DD/MM/YYYY hh:mm",
            initView: function ()
            {
                $("#facility-select").change(SysAdminLogView.loadNewLogList);
                $("#severity-select").change(SysAdminLogView.loadNewLogList);
                $("#filter-from").change(SysAdminLogView.loadNewLogList);
                $("#filter-to").change(SysAdminLogView.loadNewLogList);
                $("#pesquisa-desktop").on("input", SysAdminLogView.loadNewLogList);
                $("#pesquisa-mobile").on("input", SysAdminLogView.loadNewLogList);
                $("#actual-user-search").change(SysAdminLogView.loadNewLogList);
                $("#autocomplete-input").on('input', function () {
                    if ($("#autocomplete-input").val() === "")
                        $("#actual-user-search").val("").trigger('change');
                });

            },
            loadFilters: function ()
            {
                $('select').material_select('destroy');

                LogActions.getFilterInfo(function (filters) {

                    $.each(filters['facilities'], function (index, val)
                    {
                        $("#facility-select").append("<option value='" + val + "' >" + val + "</option>")
                    });

                    $.each(filters['severities'], function (index, val)
                    {
                        $("#severity-select").append("<option value='" + val + "' >" + LocalizationManager.getEnumFromView(ensoConf.getCurrentPage(), 'severityStrings')[val] + "</option>")
                    });

                    names = new Object();

                    $.each(filters['users'], function (key, val)
                    {
                        names[val] = null;
                    });

                    $('input.autocomplete').autocomplete({
                        data: names,
                        limit: 20, // The max amount of results that can be shown at once. Default: Infinity.
                        onAutocomplete: function (val) {
                            $("#actual-user-search").val(val).trigger('change');
                        },
                        minLength: 1, // The minimum length of the input for the autocomplete to start. Default: 1.
                    });

                    $('select').material_select();
                });
            },

            loadNewLogList: function ()
            {
                $("#log-list-body").empty();
                
                InfiniteScroller.init(function () {

                    startTime = new Date($("#filter-from").val()).getTime() / 1000;
                    startTime = (isNaN(startTime) ? '' : startTime);

                    endTime = new Date($("#filter-to").val()).getTime() / 1000;
                    endTime = (isNaN(endTime) ? '' : endTime);

                    LogActions.getLogs(
                            InfiniteScroller.currentIndex,
                            InfiniteScroller.scrollSize,
                            startTime,
                            endTime,
                            $("#facility-select").val(),
                            $("#severity-select").val(),
                            $("#actual-user-search").val(),
                            ($(window).width() > 992 ? $("#pesquisa-desktop").val() : $("#pesquisa-mobile").val()),
                            function (logs)
                            {
                                if (logs.length == 0)
                                    InfiniteScroller.disable();
                                else
                                    $.each(logs, function (key, val)
                                    {
                                        
                                        $("#log-list-body").append("<tr>\
                                                                        <td>" + new Date(val['inserted_timestamp'] * 1000).toLocaleString() + "</td>\
                                                                        <td>" + val['facility'] + "</td>\
                                                                        <td>" + LocalizationManager.getEnumFromView(ensoConf.getCurrentPage(), 'severityStrings')[val['severity_level']] + "</td>\
                                                                        <td>" + val['action'] + "</td>\
                                                                        <td>" + val['ownerid'] + "</td>\
                                                                    </tr>");
                                    });

                                InfiniteScroller.finishedLoading();
                            },
                        function()
                    {
                        $("#log.list-body").append(
                            "<tr>\
                            <td colspan='5'>" + LocalizationManager.getStringFromView('sysadmin', 'log-load-failure') + "</td>\
                        </tr>"
                        )
                    });
                }, 50, 0);

                InfiniteScroller.onReachBottom(); //ManualTrigger
            }
        };


SysAdminLogView.initView();
SysAdminLogView.loadFilters();
$('.datepicker').pickadate({
    selectMonths: true, // Creates a dropdown to control month
    selectYears: 5, // Creates a dropdown of 15 years to control year,
    today: LocalizationManager.getStringFromView('sysadmin', 'today'),
    clear: LocalizationManager.getStringFromView('sysadmin', 'clear'),
    close: LocalizationManager.getStringFromView('sysadmin', 'close'),
    closeOnSelect: false, // Close upon selecting a date,
    max: new Date()
});

SysAdminLogView.loadNewLogList();
