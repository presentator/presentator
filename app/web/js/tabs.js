/**
 * Presentator tabs jQuery plugin.
 *
 * @version 0.1
 * @author: Gani Georgiev <gani.georgiev@gmail.com>
 */
;(function($) {
    var defaults = {
        changeHash: false,
        resetFormOnChange: false,

        // selectors
        wrapper:     '.pr-tabs',
        header:      '.tabs-header',
        content:     '.tabs-content',
        headerItem:  '.tab-item',
        contentItem: '.tab-item',

        // animation options (http://api.jquery.com/animate/#animate-properties-options)
        animDuration:  300,
        animStart:     false, // or `function (animation) {...}`
        animProgress:  false, // or `function (animation, progress, ramainingMs) {...}`
        animEnd:       false, // or `function () {...}`
    };

    var settings = {};

    var isAnimationStart = false;

    var toggleTab = function(tabContentId, wrapper, animate) {
        if (!tabContentId) {
            console.warn('Missing tab id!');
            return;
        }

        var $targetTab = $((tabContentId.substr(1) == '#') ? tabContentId : ('#' + tabContentId));

        if (!$targetTab.length) {
            console.warn('Missing tab item with id ' + tabContentId + '!');
            return;
        }

        if ($targetTab.hasClass('active') && !isAnimationStart) {
            return; // nothing to do
        }

        animate = typeof animate !== 'undefined' ? animate : true;

        var $wrapper      = $(wrapper) || $targetTab.parent().parent();
        var settings      = $wrapper.data('pr.tabs');
        var $header       = $wrapper.children(settings.header);
        var $content      = $wrapper.children(settings.content);
        var $headerItems  = $header.children(settings.headerItem);
        var $contentItems = $content.children(settings.contentItem);

        $headerItems.removeClass('active');
        $headerItems.filter('[href="#' + tabContentId + '"], [data-target="#' + tabContentId + '"]').addClass('active');

        $content.finish().animate({
            'height': $targetTab.outerHeight(true) + ($content.outerHeight() - $content.height())
        }, {
            duration: animate ? settings.animDuration : 0,
            start: function (animation) {
                isAnimationStart = true;

                // helper to handle the tab items animation via css
                $contentItems.filter('.active').css({
                    'animation': 'tab-hide ' + (settings.animDuration + 50) + 'ms'
                });

                if (settings.animStart && $.isFunction(settings.animStart)) {
                    settings.animStart(animation);
                }
            },
            progress: function(animation, progress, ramainingMs) {
                if (settings.animProgress && $.isFunction(settings.animProgress)) {
                    settings.animProgress(animation, progress, ramainingMs);
                }
            },
            complete: function() {
                isAnimationStart = false;

                // helpers to handle the tab items animation via css
                $wrapper.removeClass('change-start');
                $contentItems.removeClass('active').css('animation', 'none'); // reset
                $targetTab.addClass('active').css({
                    'animation': 'tab-show ' + settings.animDuration + 'ms'
                });

                if (settings.animEnd && $.isFunction(settings.animEnd)) {
                    settings.animEnd();
                }

                $content.css('height', ''); // unset height

                $wrapper.trigger('tabChange.pr', [tabContentId, $targetTab]);
            }
        });

        if (settings.changeHash) {
            window.location.hash = tabContentId;
        }

        if (settings.resetFormOnChange) {
            $contentItems.find('form').each(function(i, form) {
                form.reset();
            });
        }
    };

    var methods = {
        init: function(options) {
            return $(this).each(function(i, wrapper) {
                methods.destroy.call(wrapper, options); // reset on reinit

                var $wrapper = $(wrapper);

                settings = $wrapper.data('pr.tabs');
                if(typeof(settings) == 'undefined') {
                    settings = $.extend({}, defaults, options);
                    $wrapper.data('pr.tabs', settings);
                } else {
                    settings = $.extend({}, settings, options);
                }

                var $tabHeader    = $wrapper.find(settings.header).first();
                var $headerItems  = $tabHeader.find(settings.headerItem);;
                var $tabContent   = $wrapper.find(settings.content).first();
                var $contentItems = $tabContent.find(settings.contentItem);

                if (!$wrapper.hasClass(settings.wrapper.substr(1))) {
                    $wrapper.addClass(settings.wrapper.substr(1));
                }

                $headerItems.off('click.pr.tabs');
                $headerItems.on('click.pr.tabs', function(e) {
                    e.preventDefault();

                    if ($(this).data('target')) {
                        toggleTab($(this).data('target').substr(1), $wrapper);
                    } else if ($(this).is('a')) {
                        toggleTab($(this).attr('href').substr(1), $wrapper);
                    }
                });

                // auto set active tab by default (if is not set already)
                var initialActiveId = '';
                if (window.location.hash && $contentItems.filter('#' + window.location.hash.substr(1)).length) {
                    initialActiveId = window.location.hash.substr(1);
                } else if ($contentItems.filter('.active').length) {
                    initialActiveId = $contentItems.filter('.active').attr('id');
                } else {
                    initialActiveId = $contentItems.first().attr('id');
                }

                toggleTab(initialActiveId, $wrapper, false);
            });
        },
        goTo: function(tabContentId, animate) {
            animate = typeof animate !== 'undefined' ? animate : false;

            return $(this).each(function(i, wrapper) {
                toggleTab(tabContentId, wrapper, animate);
            });
        },
        destroy: function() {
            return $(this).each(function(i, wrapper) {
                $(wrapper).removeData('pr.tabs');
                // something else...
            });
        }
    };

    $.fn.tabs = function(methodOrOptions) {
        if (methods[methodOrOptions]) {
            return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
            // Default to "init"
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  methodOrOptions + ' does not exist on jQuery.tabs');
        }
    };
})(jQuery);
