/**
 * Presentator tabs jQuery plugin.
 *
 * @version 0.1
 * @author: Gani Georgiev <gani.georgiev@gmail.com>
 */
;(function($) {
    var defaults = {
        nav:          true,
        animDuration: 300,
        itemClass:    '.slider-item'
    };

    var settings = {};

    var isAnimationStart = false;

    /**
     * Returns slider settings.
     * @param  {Mixed} slider Slider selector, DOM element, or jQuery object.
     * @return {Object}
     */
    function getSettings(slider) {
        return $(slider).data('pr.slider') || {};
    }

    /**
     * Active slider item change handler.
     * @param {Mixed}         slider  Slider selector, DOM element, or jQuery object.
     * @param {Number|String} slide   The targeted slide index (use 'prev', 'next' for relative target).
     * @param {Boolean}       animate Whether to animate the sliders change or not.
     */
    function changeSlide(slider, slide, animate) {
        if (isAnimationStart) {
            return;
        }

        var $slider = $(slider);
        if (!$slider.length) {
            console.warn('Slider selector is missing!');
            return;
        }

        animate = typeof animate !== 'undefined' ? animate : true;

        var settings = getSettings($slider);

        var $slides        = $(settings.itemClass);
        var $currentSlide  = $slides.filter('.active');
        var $targetedSlide = $();

        // Find the targeted slider item
        if (typeof slide === 'number') {
            // normalize slide index
            if (slide < 0) {
                slide = 0;
            } else if (slide + 1 > $slides.length) {
                slide = $slides.length - 1;
            }

            $targetedSlide = $slides.eq(slide);
        } else if (slide === 'prev') {
            if ($currentSlide.is(':first-child')) {
                $targetedSlide = $slides.last(); // loop
            } else {
                $targetedSlide = $currentSlide.prev(settings.itemClass);
            }
        } else if (slide === 'next') {
            if ($currentSlide.is(':last-child')) {
                $targetedSlide = $slides.first(); // loop
            } else {
                $targetedSlide = $currentSlide.next(settings.itemClass);
            }
        }

        if ($targetedSlide.length && $currentSlide.is($targetedSlide)) {
            return; // nothing to change
        }

        // Change slides
        $slider.trigger('sliderChangeBefore', [$targetedSlide, $currentSlide, $slides]);
        if (animate && $currentSlide.length) {
            isAnimationStart = true;

            $currentSlide.addClass('change-start').delay(settings.animDuration).queue(function(next) {
                $currentSlide.removeClass('change-start active');
                $targetedSlide.addClass('active');

                isAnimationStart = false;
                $slider.trigger('sliderChange', [$targetedSlide, $currentSlide, $slides]);

                next();
            });
        } else {
            $currentSlide.removeClass('active');
            $targetedSlide.addClass('active');

            isAnimationStart = false;
            $slider.trigger('sliderChange', [$targetedSlide, $currentSlide, $slides]);
        }
        $slider.trigger('sliderChangeAfter', [$targetedSlide, $currentSlide, $slides]);
    }

    var methods = {
        init: function(options) {
            return $(this).each(function(i, slider) {
                var $slider = $(slider);

                settings = $slider.data('pr.slider');

                if (typeof settings !== 'undefined') {
                    methods.destroy.call(slider); // reset on reinit
                }

                settings = $.extend({}, defaults, options);
                $slider.data('pr.slider', settings);

                if (settings.nav) {
                    var $prevHandle = $('<nav class="slider-nav-item prev"><i class="ion ion-android-arrow-back"></i></nav>');
                    var $nextHandle = $('<nav class="slider-nav-item next"><i class="ion ion-android-arrow-forward"></i></nav>');
                    $slider.append($prevHandle);
                    $slider.append($nextHandle);

                    $prevHandle.on('click', function(e) {
                        e.preventDefault();
                        changeSlide($slider, 'prev');
                    });

                    $nextHandle.on('click', function(e) {
                        e.preventDefault();
                        changeSlide($slider, 'next');
                    });

                    // // mouse cursor slider side detection on hover
                    // var mouseTrottle = null;
                    // $slider.on('mousemove', function(e) {
                    //     if (mouseTrottle) {
                    //         clearTimeout(mouseTrottle);
                    //     }
                    //     mouseTrottle = setTimeout(function() {
                    //         var activePart = $slider.width() / 5;
                    //         if (e.pageX < activePart) {
                    //             $slider.addClass('left-highlight').removeClass('right-highlight');
                    //         } else if (e.pageX > $slider.width() - activePart) {
                    //             $slider.addClass('right-highlight').removeClass('left-highlight');
                    //         } else if ($slider.hasClass('left-highlight')) {
                    //             $slider.removeClass('left-highlight');
                    //         } else if ($slider.hasClass('right-highlight')) {
                    //             $slider.removeClass('right-highlight');
                    //         }
                    //     }, 100);
                    // });
                }

                // select the first slide by default
                if (!$slider.find(settings.itemClass + '.active').length) {
                    changeSlide($slider, 0, false);
                }

                // add helper class if only 1 slide exist
                if ($slider.find(settings.itemClass).length <= 1) {
                    $slider.addClass('no-slides');
                } else if ($slider.hasClass('no-slides')) {
                    $slider.removeClass('no-slides');
                }

                $slider.trigger('sliderInit', [$slider.find(settings.itemClass + '.active'), $slider.find(settings.itemClass)]);
            });
        },
        goTo: function(slideIndex, animate) {
            animate = typeof animate !== 'undefined' ? animate : true;

            return $(this).each(function(i, slider) {
                changeSlide(slider, slideIndex, animate);
            });
        },
        getActive: function() {
            var settings = getSettings(this);

            return $(this).find(settings.itemClass + '.active');
        },
        destroy: function() {
            return $(this).each(function(i, slider) {
                $(slider).removeData('pr.slider')
                    .find('slider-nav-item').remove();
            });
        }
    };

    $.fn.slider = function(methodOrOptions) {
        if (methods[methodOrOptions]) {
            return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
            // Default to "init"
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  methodOrOptions + ' does not exist on jQuery.slider');
        }
    };
})(jQuery);
