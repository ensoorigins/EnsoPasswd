function checkTabs() {
    if (ensoConf.getCurrentPage() != '') {
        pageUrl = ensoConf.viewsPath + ensoConf.getCurrentPage() + "_tabs.html";

        $.ajax({
            type: "GET",
            dataType: "html",
            cache: false,
            url: pageUrl,
            success: function (response, status) {
                $('.nav-content').empty().append(response);
                $('a.enso-main-color-text.active').click(); //call default active tab action
                setHeight(); //tabs loaded move content down
                $('ul.tabs').tabs();
            },
            error: function (response) {
                $('.nav-content').empty();
                setHeight(); //tabs loaded move content down
            }
        });
    }
    else {
        $('.nav-content').empty();
    }
}

ensoConf.addAfterViewCallback(function () {
    $('.nav-content').empty();
    setHeight();
});

//# sourceURL=tabChecker.js