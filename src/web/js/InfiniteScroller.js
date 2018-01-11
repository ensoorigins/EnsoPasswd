var InfiniteScroller =
        {
            scrollSize: 0,
            currentIndex: 0,
            isLoading: false,
            init: function (onReachBottom, scrollSize, initialIndex)
            { 
                InfiniteScroller.userOnReachBottom = onReachBottom;
                InfiniteScroller.scrollSize = scrollSize;
                InfiniteScroller.currentIndex = initialIndex;
                
                $(window).scroll(function ()
                {
                    if ($(window).scrollTop() + $(window).height() == $(document).height())
                        InfiniteScroller.onReachBottom();
                });
            },
            userOnReachBottom: undefined,
            onReachBottom: function ()
            {
                if (!InfiniteScroller.isLoading && InfiniteScroller.userOnReachBottom !== undefined)
                
                {
                    InfiniteScroller.isLoading = true;
                    $("#main-content").append("<div class='progress' id='loader'>\
                                                <div class='indeterminate'></div>\
                                                </div>");
                    InfiniteScroller.userOnReachBottom();
                }
            },
            finishedLoading: function ()
            {
                $("#loader").remove();
                InfiniteScroller.currentIndex += InfiniteScroller.scrollSize;
                InfiniteScroller.isLoading = false;
            },
            disable: function ()
            {
                InfiniteScroller.userOnReachBottom = undefined;
            }
        };
ensoConf.addAfterViewCallback(InfiniteScroller.disable);