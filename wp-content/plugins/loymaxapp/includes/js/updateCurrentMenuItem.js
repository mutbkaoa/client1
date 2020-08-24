(function($) {
    $(function () {
        var currentItemClass = 'current-menu-item';

        $(window).on('hashchange', updateCurrentMenuItem);
        updateCurrentMenuItem();
        bindScrollChangeOnClick();

        function updateCurrentMenuItem() {
            $('li.loymax-menu-item').each(function() {
                var linkElement = $(this).children('a')[0];

                if (
                    linkElement.pathname === window.location.pathname &&
                    trimHash(window.location.hash).match(new RegExp('^' + trimHash(linkElement.hash) + '($|\/)'))
                ) {
                    if (!$(this).hasClass(currentItemClass)) {
                        $(this).addClass(currentItemClass);
                    }
                } else {
                    if ($(this).hasClass(currentItemClass)) {
                        $(this).removeClass(currentItemClass);
                    }
                }
            });
        }

        function bindScrollChangeOnClick() {
            var loymaxContainer = $('.loymax-container:not(\'.loymax-modal\'):first');

            $('li.loymax-menu-item').each(function() {
                $(this).click(function() {
                    $(document).scrollTop(loymaxContainer.scrollTop());
                })
            });
        }

        function trimHash(hash) {
            return hash.replace(/#\/?/, '');
        }
    });
})(jQuery);