// Adds custom remove event (https://github.com/jquery/jquery-ui/blob/master/ui/jquery.ui.widget.js#L16)
var _oldCreanData = $.cleanData;
$.cleanData = function(elems) {
    if (elems.length) {
        try {
            $(document).triggerHandler('remove', elems);
        } catch(e) {}
    }

    _oldCreanData(elems);
};

jQuery(function($) {
    var $window        = $(window);
    var $document      = $(document);
    var $globalWrapper = $('#global_wrapper');
    var $pageHeader    = $('#page_header');
    var $pageContent   = $('#page_content');
    var $pageFooter    = $('#page_footer');

    $document.on('click', '[data-action-confirm]', function(e) {
        if (!window.confirm($(this).data('action-confirm'))) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    PR.checkToggle();
    PR.clickToggle();
    PR.visibilityToggle();
    PR.lazyLoad();
    PR.colorPicker('.color-picker-input');
    PR.bindAjaxPopupAnimations();

    PR.cursorTooltipInit();

    $('.selectify-select').selectify();

    // Language select
    if ($('.language-select').length) {
        $('.language-select').selectify();
        $('.language-select').on('change', function(e) {
            e.preventDefault();

            window.location.href = $(this).val();
        });
    }

    // Header scroll
    var prevScrollTop = 0;
    var scrollTop = 0;
    $globalWrapper.on('scroll', function() {
        scrollTop = $globalWrapper.scrollTop();

        if (scrollTop > prevScrollTop) {
            // down
            if (scrollTop > 150) {
                if (!$globalWrapper.hasClass('scrolling')) {
                    $globalWrapper.addClass('scrolling');
                }

                $pageHeader.css('top', $globalWrapper.scrollTop());
            }
        } else {
            // up
            if ($globalWrapper.hasClass('scrolling')) {
                if (PR.hasVerticalScrollbar($globalWrapper)) {
                    $globalWrapper.addClass('scroll-back').stop(true, true).delay(300).queue(function(next) {
                        $globalWrapper.removeClass('scrolling scroll-back');
                        $pageHeader.css('top', 0);

                        next();
                    });
                } else {
                    $globalWrapper.removeClass('scrolling scroll-back');
                    $pageHeader.css('top', 0);
                }
            }
        }

        prevScrollTop = scrollTop;
    });

    // hide dropdowns on box mouseout
    $document.on('mouseleave', '.featured', function() {
        $(this).find('.dropdown-menu').removeClass('active');
    });

    // new popup window links
    $document.on('click', 'a[data-window]', function(e) {
        e.preventDefault();

        PR.windowOpen(
            $(this).attr('href'),
            $(this).data('width'),
            $(this).data('height'),
            $(this).data('window')
        );
    });

    // alert manual and auto close handles
    $document.on('click', '.alert .close', function() {
        $(this).closest('.alert').stop(true, true).slideUp(300);
    });

    // global ajax events
    var requestLoaderTimeout = null;
    $document.ajaxStart(function() {
        if (requestLoaderTimeout) {
            clearTimeout(requestLoaderTimeout);
        }
        requestLoaderTimeout = setTimeout(function() {
            if (PR.AUTO_LOADER) {
                PR.showLoader();
            }
        }, 500);
    }).ajaxSuccess(function (event, request, settings) {
        setTimeout(function() {
            PR.lazyLoad();
        }, 0); // reorder execution queue

        PR.colorPicker('.color-picker-input');
        $('.selectify-select').selectify();

        if (PR.isObject(request) && PR.isObject(request.responseJSON)) {
            PR.addNotification(request.responseJSON.message, request.responseJSON.success ? 'success' : 'danger');
        }
    }).ajaxError(function(event, request, settings) {
        PR.addNotification('An error occured! Please try again.', 'danger');
    }).ajaxComplete(function() {
        if (requestLoaderTimeout) {
            clearTimeout(requestLoaderTimeout);
        }
        PR.hideLoader();
        PR.visibilityToggle();

        if (!PR.AUTO_LOADER) {
            PR.AUTO_LOADER = true;
        }
    });

    // Page load identifier
    $window.on('load', function() {
        $('html').addClass('page-loaded');

        // auto hide/slideUp items
        $('[data-auto-hide]').each(function(i, item) {
            setTimeout(function() {
                $(item).stop(true, true).slideUp(300);
            }, $(item).data('auto-hide') || 2000);
        });
    });
});

