/**
 * Global object that contains variaty of commonly
 * used helper methods and properties.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
var PR = {
    AUTO_LOADER: true,

    keys: {
        'backspace': 8,
        'tab':       9,
        'esc':       27,
        'enter':     13,
        'shift':     16,
        'ctrl':      17,
        'alt':       18,
        'capslock':  20,
        'space':     32,
        'left':      37,
        'up':        38,
        'right':     39,
        'down':      40,
        'a':         65,
        'b':         66,
        'c':         67,
        'd':         68,
        'e':         69,
        'f':         70,
        'g':         71,
        'h':         72,
        'i':         73,
        'j':         74,
        'k':         75,
        'l':         76,
        'm':         77,
        'n':         78,
        'o':         79,
        'p':         80,
        'q':         81,
        'r':         82,
        's':         83,
        't':         84,
        'u':         85,
        'v':         86,
        'w':         87,
        'x':         88,
        'y':         89,
        'z':         90
    },

    /**
     * Encodes html special chars.
     * It is the opposite of `PR.htmlDecode`.
     * @param  {String} str
     * @return {String}
     */
    htmlEncode: function(str) {
        if (!str || !str.length) {
            return '';
        }

        return str.replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\//g, '&#x2F;');
    },

    /**
     * Decodes html special chars.
     * It is the opposite of `PR.htmlEncode`.
     * @param  {String} str
     * @return {String}
     */
    htmlDecode: function(str) {
        if (!str || !str.length) {
            return '';
        }

        return str.replace(/&quot;/g, '"')
            .replace(/&#39;/g, "'")
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&amp;/g, '&')
            .replace(/&#x2F;/g, '/');
    },

    /**
     * Custom html data setter (updates both jQuery memory and dom attribute).
     * @param {Mixed}  selector
     * @param {String} dataKey
     * @param {Mixed}  value
     */
    setData: function(selector, dataKey, dataValue) {
        $(selector).data(dataKey, dataValue)
            .attr('data-' + dataKey, dataValue);
    },

    /**
     * Checkes whether a LEFT mouse button is clicked.
     * @param  {Object} e Mouse event.
     * @return {Boolean}
     */
    isMouseLeftBtn: function(e) {
        e = e || window.event;

        return (e.which || e.button) == 1;
    },

    /**
     * Checkes whether a RIGHT mouse button is clicked.
     * @param  {Object} e Mouse event.
     * @return {Boolean}
     */
    isMouseRightBtn: function(e) {
        e = e || window.event;

        return (e.which || e.button) == 3;
    },

    /**
     * Aborts incomplete ajax request
     * @param {Object} xhr
     */
    abortXhr: function(xhr) {
        if (xhr && xhr.readyState != 4) {
            xhr.abort();
        }
    },

    /**
     * Checks if `item` is an object.
     * @param  {Mixed} item
     * @return {Boolean}
     */
    isObject: function(item) {
        if (typeof item === 'object') {
            return true;
        }

        return false;
    },

    /**
     * Checks if `item` is an array.
     * @param  {Mixed} item
     * @return {Boolean}
     */
    isArray: function(item) {
        if (typeof item === 'object' && item.constructor === Array) {
            return true;
        }

        return false;
    },

    /**
     * Checks if `item` is valid function/closure.
     * @param  {Mixed} item
     * @return {Boolean}
     */
    isFunction: function(item) {
        if (item && typeof item === 'function') {
            return true;
        }

        return false;
    },

    /**
     * Checkes whether an item is a jQuery object.
     * @param  {Mixed}  item
     * @return {Boolean}
     */
    isJquery: function(item) {
        if (item && item instanceof jQuery) {
            return true;
        }

        return false;
    },

    /**
     * Checkes whether a string is valid url.
     * @link https://github.com/angular/angular.js/blob/master/src/ng/directive/input.js#L25
     * @param  {String} url
     * @return {Boolean}
     */
    isValidUrl: function(url) {
        var urlRegex = /^[a-z][a-z\d.+-]*:\/*(?:[^:@]+(?::[^@]+)?@)?(?:[^\s:/?#]+|\[[a-f\d:]+])(?::\d+)?(?:\/[^?#]*)?(?:\?[^#]*)?(?:#.*)?$/i;

        return urlRegex.test(url);
    },

    /**
     * Adds disabled to empty form fields to prevent serializing.
     * @param {String} form selector
     */
    ignoreEmptyFields: function(form) {
        form = form || '.ignore-empty';

        $(document).on('submit', form, function() {
            $(this).find(':input').each(function(i, field) {
                if (!$(field).val() || $(field).val().length === 0) {
                    $(field).attr('disabled', 'disabled');
                }
            });
        });
    },

    /**
     * Returns a jQuery element based on data attribute options.
     *
     * The available `data` options are:
     * `data-target`  - the targeted selector (could be class, id, attr, etc.)
     * `data-isolate`
     *  + 'parent'  - search the targeted element whithin selector parent
     *  + 'closest' - search the targeted element via the `$.closest` method
     *
     * @param  {String|Object} selector
     * @return {jQuery}
     */
    getTarget: function(selector) {
        var $selector = $(selector);
        var isolateType = '';

        if ($selector.data('target')) {
            isolateType = $selector.data('isolate');
            if (!isolateType) {
                return $($selector.data('target'));
            } else if (isolateType === 'parent') {
                return $selector.parent().find($selector.data('target'));
            } else if (isolateType === 'closest') {
                return $selector.closest($selector.data('target'));
            }
        }

        return $selector.parent();
    },

    /**
     * CSS class toggle on checkbox/radio state change.
     *
     * The available `data` options are:
     * `data-target` - the targeted selector
     * `data-class`  - the toggled class (default to 'active')
     * @see getTarget() for more options how to handle the target

     * @param {String} selector
     */
    checkToggle: function(selector) {
        var self = this;

        selector = selector || '[data-bind="checkToggle"]';

        var $input      = null;
        var $target     = null;
        var toggleClass = '';

        $(document).off('change.pr.checkToggle', selector);
        $(document).on('change.pr.checkToggle', selector, function(e) {
            $input      = $(this);
            $target     = self.getTarget($input);
            toggleClass = $input.data('class') || 'active';

            if ($input.is(':checked')) {
                $target.addClass(toggleClass);
            } else {
                $target.removeClass(toggleClass);
            }
        });
    },

    /**
     * Generic CSS class toggle on element click.
     *
     * The available `data` options are:
     * `data-target` - the targeted element
     * `data-class`  - the toggled class (default to 'active')
     * @see getTarget() for more options how to handle the target
     *
     * @param {String} selector
     */
    clickToggle: function(selector) {
        var self = this;

        selector = selector || '[data-bind="clickToggle"]';

        // run only once on multiple clickToggle calls
        if (!self.isObject(self.clickToggleCache)) {
            self.clickToggleCache = {};
            self.clickToggleCache.$target     = null;
            self.clickToggleCache.$selector   = null;
            self.clickToggleCache.toggleClass = '';

            // close on outside click
            $(document).off('mousedown.pr.clickToggle touchstart.pr.clickToggle');
            $(document).on('mousedown.pr.clickToggle touchstart.pr.clickToggle', function(e) {
                if (self.clickToggleCache.$target &&
                    self.clickToggleCache.$selector &&
                    self.clickToggleCache.$target.length &&
                    self.clickToggleCache.$selector.length &&
                    !self.clickToggleCache.$selector.is(e.target) &&
                    !self.clickToggleCache.$selector.has(e.target).length &&
                    !self.clickToggleCache.$target.is(e.target) &&
                    !self.clickToggleCache.$target.has(e.target).length
                ) {
                    e.preventDefault();
                    self.clickToggleCache.$target.removeClass(self.clickToggleCache.toggleClass || 'active');
                    self.clickToggleCache.$target     = null;
                    self.clickToggleCache.$selector   = null;
                    self.clickToggleCache.toggleClass = '';
                }
            });
        }

        // class toggle
        $(document).off('click.pr.clickToggle', selector);
        $(document).on('click.pr.clickToggle', selector, function(e) {
            self.clickToggleCache.$selector   = $(this);
            self.clickToggleCache.$target     = self.getTarget(this);
            self.clickToggleCache.toggleClass = self.clickToggleCache.$selector.data('class') || 'active';

            if (self.clickToggleCache.$selector.is('a,button')) {
                e.preventDefault();
            }

            if (self.clickToggleCache.$target.hasClass(self.clickToggleCache.toggleClass)) {
                self.clickToggleCache.$target.removeClass(self.clickToggleCache.toggleClass);
            } else {
                self.clickToggleCache.$target.addClass(self.clickToggleCache.toggleClass);
            }
        });
    },

    /**
     * Simple helper to hide/show a div based on checkbox/radio state.
     * @example
     * ```
     * <input type="checkbox" data-toggle="#my_div">
     * <div id="my_div">Some content...</div>
     * ```
     */
    visibilityToggle: function() {
        var $target;
        function toggle(input, animation) {
            $target = $($(input).data('toggle'));

            animation = typeof animation !== 'undefined' ? animation : 350;

            if ($(input).is(':checked')) {
                $target.stop(true, true).slideDown(animation);
            } else {
                $target.stop(true, true).slideUp(animation);
            }
        }

        $(document).off('change.pr.visibilityToggle', 'input[data-toggle]');
        $(document).on('change.pr.visibilityToggle', 'input[data-toggle]', function(e) {
            toggle(this);
        });

        // set init values
        $('input[data-toggle]').each(function(i, input) {
            toggle(input, 0);
            $(input).closest('form').off('reset.pr.visibilityToggle');
            $(input).closest('form').on('reset.pr.visibilityToggle', function() {
                setTimeout(function() {
                    toggle(input, 0);
                }, 50); // @see yiiactiveform.js:431
            });
        });
    },

    /**
     * Checkes if an element has a VERTICAL scrollbar
     * @param  {String} selector
     * @return {Boolean}
     */
    hasVerticalScrollbar: function(selector) {
        var elem = $(selector || 'html').get(0);

        if (elem && elem.scrollHeight > elem.clientHeight) {
            return true
        }

        return false;
    },

    /**
     * Checkes if an element has a HORIZONTAL scrollbar
     * @param  {String} selector
     * @return {Boolean}
     */
    hasHorizontalScrollbar: function(selector) {
        var elem = $(selector || 'html').get(0);

        if (elem && elem.scrollWidth > elem.clientWidth) {
            return true
        }

        return false;
    },

    /**
     * Opens a single url addres within a new popup window.
     * @param {String} url
     * @param {Number} width  (Default: 600)
     * @param {Number} height (Default: 480)
     * @param {String} name   The name of the created window. It is used as an id. (optional, Default: 'popup')
     *
     * @example
     * PR.windowOpen('http://google.bg', 600, 400)
     *
     * @return {Object} A reference to the newly created window
     */
    windowOpen: function(url, width, height, name) {
        width  = width  || 600;
        height = height || 480;
        name   = name   || 'popup';

        var windowWidth  = $(window).width();
        var windowHeight = $(window).height();

        // normalize window size
        width  = width > windowWidth ? windowWidth : width;
        height = height > windowHeight ? windowHeight : height;

        var left = (windowWidth / 2) - (width / 2);
        var top  = (windowHeight / 2) - (height / 2);

        return window.open(
            url,
            name,
            'width='+width+',height='+height+',top='+top+',left='+left+',resizable,menubar=no'
        );
    },

    /**
     * Outputs a auto closing notification message.
     * @param {String} text
     * @param {String} type
     * @param {Number} timeout
     */
    addNotification: function(text, type, timeout) {
        if (typeof text !== 'string' || text.length === 0) {
            return;
        }

        var $notification = $('<div class="alert alert-' + type + '">' + text + '</div>');

        timeout = timeout || 3500;

        if (!$('#notifications_wrapper').length) {
            $('body').append('<div id="notifications_wrapper" class="notifications-wrapper"></div>');
        }

        $('#notifications_wrapper').prepend($notification);

        setTimeout(function() {
            $notification.addClass('remove-start').stop(true, true).delay(400).queue(function(next) {
                $notification.remove();
                next();
            });
        }, timeout);
    },

    /**
     * Safely adds version query paramater to file url to prevent browser cache.
     * @param  {String} url
     * @return {String}
     */
    nocacheUrl: function(url) {
        if (!url) {
            return '';
        }

        if (url.indexOf('?') > 0) {
            return url + '&v=' + Date.now();
        }

        return url + '?v=' + Date.now();
    },

    /**
     * Hides all broken images from the DOM tree.
     */
    hideBrokenImages: function() {
        $('img').each(function() {
            if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
                $(this).hide();
            }
        });
    },

    /**
     * Lazy load images with option to specify load priority.
     * Available priorities: 'high', 'medium' (default) , 'low'.
     *
     * @example
     * <img class="lazy-load" data-src="/my/image/path.png" data-priority="high">
     */
    lazyLoad: function(selector) {
        var groups = {'high': [], 'medium': [], 'low': []};

        // build priority groups
        var priority = '';
        $('.lazy-load:not(.loaded)').each(function(i, img) {
            var $img = $(img);
            $img.$parent = $img.parent();
            priority = $img.data('priority');

            if (priority === 'high') {
                groups['high'].push($img);
            } else if (!priority || priority === 'medium') {
                groups['medium'].push($img);
            } else if (priority === 'low') {
                groups['low'].push($img);
            }
        });

        function loadGroups(priorities) {
            if (!priorities.length) {
                return;
            }

            var loadedImages = 0;
            var recursiveCall = function() {
                if (typeof priorities[1] !== 'undefined' &&
                    $.isArray(groups[priorities[0]]) &&
                    groups[priorities[0]].length === loadedImages
                ) {
                    priorities.shift();
                    loadGroups(priorities);
                }
            };

            if (!groups[priorities[0]].length) {
                recursiveCall();
            } else {
                $.each(groups[priorities[0]], function(i, $img) {
                    if ($img.data('nocache')) {
                        $img.attr('src', PR.nocacheUrl($img.data('src')));
                    } else {
                        $img.attr('src', $img.data('src'));
                    }
                    $img.$parent.addClass('lazy-load-start');

                    $img.on('load', function() {
                        $img.addClass('loaded');
                        $img.$parent.removeClass('lazy-load-start').addClass('loaded');

                        loadedImages++;
                        recursiveCall();
                    }).on('error', function() {
                        $img.hide();

                        loadedImages++;
                        recursiveCall();
                    });
                });
            }
        }

        loadGroups(['high', 'medium', 'low']);
    },

    /**
     * Save the current state of a form,
     * so when call `form.reset()` the values will be not removed.
     * @param {string} formSelector
     */
    saveFormState: function(formSelector) {
        var type = 'text';
        $(formSelector).find('input').each(function(j, input) {
            type = $(input).attr('type');
            if (type === 'checkbox' || type === 'radio') {
                // checkbox/radio
                if ($(input).prop('checked')) {
                    $(input).attr('checked', 'checked');
                } else {
                    $(input).removeAttr('checked');
                }
            } else {
                // general input
                $(input).attr('value', $(input).val());
            }
        });

        // select
        $(formSelector).find('option').each(function(j, option) {
            if ($(option).is(':selected')) {
                $(option).attr('selected', 'selected');
            } else {
                $(option).removeAttr('selected');
            }
        });

        // textarea
        $(formSelector).find('textarea').each(function(j, textarea) {
            $(textarea).html($(textarea).val());
        });
    },

    /**
     * Binds animation helpers related to the ajax actions inside a popup.
     */
    bindAjaxPopupAnimations: function() {
        var loadingInterval = null;
        var $btn            = $();
        var $btnProgress    = $();
        var width           = 0;

        $(document).ajaxStart(function() {
            if ($('.popup.active').length) {
                $btn         = $('.popup.active').find('.btn-loader');
                $btnProgress = $btn.find('.progress');

                // add progress element if missing
                if (!$btnProgress.length) {
                    $btnProgress = $('<span class="progress"></span>');
                    $btn.append($btnProgress);
                }

                $btn.addClass('loading');

                width = 0;
                loadingInterval = setInterval(function() {
                    if (width < 95) {
                        $btnProgress.css('width', (width++) + '%');
                    } else {
                        clearInterval(loadingInterval);
                    }
                }, 100);
            }
        }).ajaxComplete(function(event, request, settings) {
            if (loadingInterval) {
                clearTimeout(loadingInterval);
            }

            $btnProgress.css('width', '100%').addClass('anim-end').delay(800).queue(function(next) {
                $btnProgress.removeClass('anim-end').css('width', '0%');
                $btn.removeClass('loading');

                next();
            });
        });
    },

    /**
     * Shows global loader.
     */
    showLoader: function() {
        if (!$('#global_loader').length) {
            $('body').append('<div id="global_loader" class="global-loader"></div>');
        }

        $('#global_loader').addClass('active');
    },

    /**
     * Hides global loader.
     */
    hideLoader: function() {
        $('#global_loader').addClass('close-start').delay(400).queue(function(next) {
            $(this).removeClass('active close-start');

            next();
        });
    },

    /**
     * Inits color picker control.
     * @param {Mixed} selector
     */
    colorPicker: function(selector) {
        var $input;
        return $(selector).each(function(i, input) {
            $input = $(input);

            if ($input.data('pickerInstance')) {
                return true; // already inited
            }

            var initialColor = $(input).val() || '#eff2f8';

            var $colorPickerHandle = $('<span class="color-picker-input-handle"></span>');
            var $resetHandle       = $('<span class="color-picker-ctrl reset"><i class="ion ion-close"></i></span>');
            var $applyHandle       = $('<span class="color-picker-ctrl apply"><i class="ion ion-checkmark"></i></span>');

            $input.wrap('<div class="color-picker-input-wrapper"></div>');
            $colorPickerHandle.insertAfter(input);

            var picker = new CP($colorPickerHandle.get(0));
            $input.data('pickerInstance', picker);

            picker.set(initialColor);
            $(picker.picker).append($resetHandle).append($applyHandle);

            // event handlers
            // --------------
            $resetHandle.on('click', function(e) {
                e.preventDefault();

                picker.trigger("change", [initialColor.substr(1)]);

                picker.exit();
            });

            $applyHandle.on('click', function(e) {
                e.preventDefault();

                picker.exit();
            });

            picker.on('change', function(color) {
                $input.val('#' + color);
                $colorPickerHandle.css('background', '#' + color);
            });

            $input.on('change', function() {
                picker.set($input.val());
                $colorPickerHandle.css('background', $input.val());
            });
        });
    },

    /**
     * Takes care for the cursor following tooltip initialization.
     */
    cursorTooltipInit: function() {
        if ($('#cursor_tooltip').length) {
            console.warn('Cursor tooltip seems to be already binded!');
            return;
        }

        var coords         = {};
        var spacing        = 8;
        var $window        = $(window);
        var $document      = $(document);
        var $cursorTooltip = $('<div id="cursor_tooltip" class="cursor-tooltip"></div>');

        $('body').append($cursorTooltip);

        $document.on('remove', function(e, elems) {
            if ($cursorTooltip.hasClass('active')) {
                $cursorTooltip.removeClass('active');
            }
        });
        $document.on('mousemove', '[data-cursor-tooltip]', function(e) {
            if (!$(this).data('cursor-tooltip')) {
                $cursorTooltip.removeClass('active');
                $cursorTooltip[0].className = 'cursor-tooltip';
                return;
            }

            if (!$cursorTooltip.hasClass('active')) {
                $cursorTooltip.addClass('active');
                $cursorTooltip.text($(this).data('cursor-tooltip'));

                if ($(this).data('cursor-tooltip-class')) {
                    $cursorTooltip.addClass($(this).data('cursor-tooltip-class'));
                }
            }

            coords = {
                'left': e.pageX + spacing - $window.scrollLeft(),
                'top':  e.pageY + spacing - $window.scrollTop()
            };

            if (coords.left + $cursorTooltip.outerWidth() > $window.width()) {
                coords.left = coords.left - $cursorTooltip.outerWidth() - 2*spacing;
            }

            $cursorTooltip.css(coords);
        })
        .on('mouseleave', '[data-cursor-tooltip]', function(e) {
            // remove all other classes
            $cursorTooltip[0].className = 'cursor-tooltip';
        });

        $window.on('scroll', function(e) {
            if ($cursorTooltip.hasClass('active')) {
                $cursorTooltip.removeClass().addClass('cursor-tooltip');
            }
        });
    },

    /**
     * Calculates and sets popover position based on a DOM element.
     * @param {Mixed} item
     * @param {Mixed} popover
     * @param {Mixed} view
     */
    repositionPopover: function(item, popover, view) {
        var $item = $(item);
        if (!$item.length) {
            console.warn('The realated popover item was not found!');
            return;
        }

        var $popover      = $(popover);
        var popoverWidth  = $popover.outerWidth(true);
        var popoverHeight = $popover.outerHeight(true);
        $popover.css({'maxHeight': '', 'overflowY': ''}); // reset

        var $view      = $(view || 'body');
        var viewWidth  = $view.width();
        var viewHeight = $view.height();

        $item.css({'transform': 'none', 'animation': 'none'}); // css scale affects `.offset()`
        var itemWidth    = $item.outerWidth(true);
        var itemHeight   = $item.outerHeight(true);
        var itemPosition = $item.offset();
        // substract view position (required if the view container has `position:relative`)
        itemPosition.top  = itemPosition.top - $view.position().top;
        itemPosition.left = itemPosition.left - $view.position().left;
        $item.css({'transform': '', 'animation': ''}); // reset

        var cssSettings = {};
        var popoverArrowClass = '';


        // detect horizontal position
        if (itemPosition.left + itemWidth + popoverWidth > viewWidth) {
            // left
            popoverArrowClass = 'right'; // opposite
            cssSettings.left = itemPosition.left - popoverWidth;

            // normalize
            if (cssSettings.left < 0) {
                popoverArrowClass = 'left';
                cssSettings.left = itemPosition.left;
            }
        } else {
            // right
            popoverArrowClass = 'left'; // opposite
            cssSettings.left = itemPosition.left + itemWidth;
        }

        // detect vertical position
        if (itemPosition.top + popoverHeight > viewHeight) {
            // top
            popoverArrowClass += '-bottom'; // opposite
            cssSettings.top = itemPosition.top - popoverHeight + 40;
        } else {
            // bottom
            popoverArrowClass += '-top'; // opposite
            cssSettings.top = itemPosition.top;
        }

        if (cssSettings.top < 0) {
            cssSettings.top       = 0;
            cssSettings.overflowY = 'auto';
            cssSettings.maxHeight = $view.height();
        }

        $popover.css(cssSettings)
            .removeClass('left-top left-bottom right-top right-bottom')
            .addClass(popoverArrowClass);
    },

    /**
     * Horizontal item alignment based on data attribute.
     * @param {Mixed} item
     */
    horizontalAlign: function(item) {
        var $item = $(item);
        if (!$item.length) {
            // console.warn('Missing item element!');
            return;
        }

        var alignment = $item.data('alignment') || 'center';

        if (alignment === 'center') {
            $item.scrollLeft(($item.get(0).scrollWidth / 2) - $item.width() / 2);
        } else if (alignment === 'left') {
            $item.scrollLeft(0);
        } else if (alignment === 'right') {
            $item.scrollLeft($item.get(0).scrollWidth);
        }
    },

    /**
     * Common helper to toggle elements visibility based on "data-group" attributes.
     * @see 'ProjectIndex.init()'
     * @see `ProjectView.init()`
     * @param {Mixed} typeSelect
     * @param {Mixed} subtypeSelect
     */
    bindSubtypesToggle: function(typeSelect, subtypeSelect, animationOnInit) {
        animationOnInit = typeof animationOnInit !== 'undefined' ? animationOnInit : true;

        var $typeSelect    = $(typeSelect);
        var $subtypeSelect = $(subtypeSelect);

        var defaultSubtypes      = $subtypeSelect.data('default');
        var $customSubtypeSelect = $subtypeSelect.closest('.custom-select');
        var $activeSubtypes;

        var toggle = function(typeVal, animations) {
            animations = typeof animations !== 'undefined' ? animations : true;

            $activeSubtypes = $customSubtypeSelect.find('.option').hide()
                .filter('[data-group="' + typeVal + '"]').show();

            if (!$activeSubtypes.length) {
                if (animations) {
                    $subtypeSelect.closest('.form-group').stop(true, true).slideUp(300);
                } else {
                    $subtypeSelect.closest('.form-group').hide();
                }
            } else {
                if ($activeSubtypes.filter('.active').length) {
                    $subtypeSelect.val($activeSubtypes.filter('.active').data('value')).trigger('change');
                } else if (defaultSubtypes[typeVal]) {
                    $subtypeSelect.val(defaultSubtypes[typeVal]).trigger('change');
                } else {
                    $subtypeSelect.val($activeSubtypes.first().data('value')).trigger('change');
                }


                if (animations) {
                    $subtypeSelect.closest('.form-group').stop(true, true).slideDown(300);
                } else {
                    $subtypeSelect.closest('.form-group').show();
                }
            }
        };

        $typeSelect.off('change.pr.subtypesToggle');
        $typeSelect.on('change.pr.subtypesToggle', function() {
            toggle($(this).val());
        });
        $typeSelect.closest('form').on('reset', function() {
            setTimeout(function() {
                toggle($typeSelect.filter(':checked').val());
            }, 50); // @see yiiactiveform.js:431
        });

        toggle($typeSelect.filter(':checked').val(), animationOnInit);
    },

    /**
     * Highlight last string part after a delimiter by wrapping it within strong tag.
     * @param  {String} url
     * @param  {String} delimiter
     * @return {String}
     */
    highlightLastStringPart: function(url, delimiter) {
        delimiter = delimiter || '/';

        var parts = url.split(delimiter);
        var end   = parts.pop();

        return parts.join(delimiter) + (delimiter) + '<strong>' + end + '</strong>';
    },

    /**
     * Updates scroll container width to prevent displaying unnecessary horizontal scrollbar.
     * @see https://github.com/ganigeorgiev/presentator/issues/23
     * @param {Mixed} item
     * @param {Mixed} scrollContainer
     */
    updateScrollContainerWidth: function(item, scrollContainer) {
        var $item            = $(item);
        var $scrollContainer = $(scrollContainer);

        if (!$scrollContainer.length) {
            return; // nothing to do here
        }

        if ($scrollContainer.width() == $item.width()) {
            var scrollbarWidth = $scrollContainer[0].offsetWidth - $scrollContainer[0].clientWidth;

            $scrollContainer.css({
                // 'width': 'auto' // NB! Unfortunately works only in Chrome (http://stackoverflow.com/questions/39738265/firefox-displays-unnecessary-horizontal-scrollbar)
                'width': $scrollContainer.width() + (scrollbarWidth || 0)
            });
        }
    }
};
