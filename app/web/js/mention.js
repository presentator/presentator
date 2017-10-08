/**
 * Presentator @user mention autocomplete jQuery plugin.
 *
 * @version 0.1
 * @author: Gani Georgiev <gani.georgiev@gmail.com>
 */
;(function($) {
    var defaults = {
        appendTo:      'parent',
        minChars:      1,
        autohide:      true, // hide on blur
        autoshow:      true, // show on type
        maxResults:    Infinity,
        listClass:     'mention-list',
        listItemClass: 'mention-list-item',
        missingText:   '<p class="mention-list-missing-item">No results found</p>', // or empty to disable
        triggerChars:  ['+', '@'],
        filter:        false, // or `{Boolean} function (item, searchStr)`
        render:        false, // or `{String}  function (item, input)`
        select:        false, // or `{String}  function (props, wordInfo)`
        mentionsList:  [],
    };

    var $document = $(document);

    /**
     * Return plugin instance settings from an input.
     *
     * @param  {Mixed} input Input selector.
     * @return {Object}
     */
    var getSettings = function (input) {
        return $(input).data('pr.mention') || {};
    };

    /**
     * Update plugin instance settings for an input.
     *
     * @param  {Mixed} input Input selector.
     * @return {Object}
     */
    var setSettings = function (input, settings) {
        return $(input).data('pr.mention', settings || {});
    };

    /**
     * @param {Object} wordInfo
     * @param {Mixed} input
     * @param {Mixed} item
     */
    var onSelect = function (wordInfo, input, item) {
        var $item      = $(item);
        var $input     = $(input);
        var val        = $input.val();
        var settings   = getSettings(input);
        var itemData   = $item.data('pr.mention.itemData') || {};
        var replaceVal = '';

        if (
            typeof wordInfo.end !== 'undefined' &&
            typeof wordInfo.start !== 'undefined' &&
            typeof itemData.query !== 'undefined'
        ) {
            if (typeof settings.select === 'function') {
                replaceVal = settings.select(itemData, wordInfo);
            } else {
                replaceVal = itemData.value || itemData.query.toLowerCase().replace(/\s+/g, '');
            }

            $input.val(
                val.substr(0, wordInfo.start) +
                settings.triggerChars[0] +
                replaceVal +
                val.substr(wordInfo.end + 1) +
                ' '
            );

            $input.trigger('mentionListSelect');

            $input.trigger('focus');

            methods.hideList.call(input);
        }
    };

    /**
     * Return a word at specific position.
     *
     * @param  {String} str
     * @param  {Number} pos
     * @return {String}
     */
    var getWordInfoAt = function (str, index) {
        var words = str.replace(/(\r\n|\n|\r)/gm, ' ').split(' ');

        var prevWordsLength = 0;
        var startIndex      = 0;
        var endIndex        = 0;

        for (var i = 0; i < words.length; i++) {
            startIndex = prevWordsLength + i;
            endIndex   = (words[i].length - 1 + prevWordsLength + i);

            if (index >= startIndex && index <= endIndex) {
                return {
                    'start': startIndex,
                    'end':   endIndex,
                    'word':  words[i]
                };
            }

            prevWordsLength += words[i].length;
        }

        return {};
    };

    /**
     * @see `this.getWordInfoAt()`
     * @param  {String} str
     * @param  {Number} currentPos
     * @param  {Array}  startChars
     * @return {Object} Mention word item info object
     */
    var findMentionWord = function (str, currentPos, startChars) {
        var wordInfo = getWordInfoAt(str, currentPos);

        if (wordInfo.word && startChars.indexOf(wordInfo.word[0]) >= 0) {
            return wordInfo;
        }

        return {};
    };

    var methods = {
        /**
         * @param  {Object} options
         * @return {jQuery}
         */
        init: function (options) {
            return $(this).each(function (i, input) {
                var $input   = $(input);
                var wordInfo = {};
                var settings = getSettings($input);

                // reset on reinit
                if (typeof settings !== 'undefined' || Object.keys(settings).length == 0) {
                    methods.destroy.call(input);
                }

                settings = $.extend({}, defaults, options);
                setSettings($input, settings);

                // create mention list elem
                var $mentionsList = $('<div class="' + settings.listClass + '"></div>');
                if (settings.appendTo !== 'parent') {
                    $mentionsList.appendTo(settings.appendTo);
                } else {
                    $mentionsList.appendTo($input.parent());
                }
                $input.data('pr.mention.listEl', $mentionsList);
                $mentionsList.data('pr.mention.inputEl', $input);

                var cursorPos = 0;
                $input.off('input.pr.mention');
                $input.on('input.pr.mention', function (e) {
                    if (!settings.autoshow) {
                        return true;
                    }

                    cursorPos = typeof input.selectionStart !== 'undefined' ? input.selectionStart : $input.val().length;

                    wordInfo = findMentionWord($input.val(), cursorPos - 1, settings.triggerChars);
                    if (wordInfo.word && wordInfo.word.length >= settings.minChars) {
                        methods.showList.call(input, wordInfo.word);
                    } else {
                        methods.hideList.call(input);
                    }
                });

                $document.on('click.pr.mention', function (e) {
                    if (
                        !$input.is(e.target) &&
                        !$mentionsList.is(e.targert) &&
                        !$mentionsList.has(e.targert).length
                    ) {
                        if (!settings.autohide) {
                            return true;
                        }

                        methods.hideList.call(input);
                    }
                });

                var $children    = $();
                var $activeChild = $();
                $input.off('keydown.pr.mention')
                $input.on('keydown.pr.mention', function (e) {
                    if (
                        !$mentionsList.hasClass('active') ||
                        (e.which !== 38 && e.which !== 40 && e.which !== 13)
                    ) {
                        return true;
                    }

                    e.preventDefault();
                    e.stopImmediatePropagation();

                    $children    = $mentionsList.children();
                    $activeChild = $children.filter('.active');

                    $children.removeClass('active'); // reset

                    if (e.which === 38) { // up
                        if (!$activeChild.length || $activeChild.is(':first-child')) {
                            $activeChild = $children.last().addClass('active');
                        } else {
                            $activeChild = $activeChild.prev().addClass('active');
                        }
                    }

                    if (e.which === 40) { // down
                        if (!$activeChild.length || $activeChild.is(':last-child')) {
                            $activeChild = $children.first().addClass('active');
                        } else {
                            $activeChild = $activeChild.next().addClass('active');
                        }
                    }

                    if (e.which === 13) { // enter
                        onSelect(wordInfo, input, $activeChild);
                    }
                });

                $mentionsList.off('mouseenter.pr.mention', '.' + settings.listItemClass);
                $mentionsList.on('mouseenter.pr.mention', '.' + settings.listItemClass, function (e) {
                    $(this).addClass('active').siblings().removeClass('active');
                });

                $mentionsList.off('click.pr.mention', '.' + settings.listItemClass);
                $mentionsList.on('click.pr.mention', '.' + settings.listItemClass, function (e) {
                    e.preventDefault();

                    onSelect(wordInfo, input, this);
                });
            });
        },

        /**
         * Each `list` argument should have at least `query` and `value` properties.
         * Sample format:
         * ```js
         * [
         *     {
         *         query: 'John Doe'
         *     },
         *     {
         *         query: 'Lorem Ipsum',
         *         value: 'lorem.ipsum' // optional
         *     },
         *     {
         *         // you can define any other data and make use of it by overwriting
         *         // the default `filter`, `render` and `select` options.
         *         job:       'Bounty hunter (Space Cowboy)',
         *         query: 'Spike Spiegel',
         *         value: 'spike',
         *         spaceship: 'Bebop/Swordfish'
         *     }
         * ]
         * ```
         *
         * @param  {Array} list
         * @return {jQuery}
         */
        setList: function (list) {
            var mentionsList = [];

            if ($.isArray(list)) {
                $.each(list, function (i, item) {
                    if (
                        typeof item === 'object' &&
                        typeof item.query !== 'undefined'
                    ) {
                        mentionsList.push(item);
                    }
                });
            }

            var settings = getSettings(this);
            settings.mentionsList = mentionsList;

            setSettings(this, settings);
        },

        /**
         * @return {Array}
         */
        getList: function () {
            var settings = getSettings(this);

            return settings.mentionsList || [];
        },

        /**
         * Clear and hide mention list.
         *
         * @return {jQuery}
         */
        hideList: function () {
            return $(this).each(function (i, input) {
                var $input = $(input);
                var $list  = $($input.data('pr.mention.listEl'));

                if (!$list.hasClass('active')) {
                    return true; // already hidden
                }

                $input.trigger('mentionListBeforeHide', [$list]);

                $list.empty().removeClass('active');

                $input.trigger('mentionListAfterHide', [$list]);
            });
        },

        /**
         * Show and populate mention list.
         *
         * @param  {String} searchStr
         * @return {jQuery}
         */
        showList: function (searchStr) {
            return $(this).each(function (i, input) {
                var settings = getSettings(input);
                var $input   = $(input);
                var $list    = $($input.data('pr.mention.listEl'));
                var items    = methods.find.call(input, searchStr);
                var $items   = $();

                $input.trigger('mentionListBeforeShow', [$list, items]);

                // populate list items
                var $item;
                $.each(items, function (i, item) {
                    if (typeof settings.render === 'function') {
                        $item = $(settings.render(item, input));
                    } else {
                        $item = $('<div class="mention-list-item">' + item.query + '</div>');
                    }

                    $item.addClass(settings.listItemClass);
                    $item.data('pr.mention.itemData', item);

                    $items = $items.add($item);
                });

                $list.empty().removeClass('active'); // reset

                if ($items.length) {
                    $list.addClass('active').append($items);
                    $list.children().first().addClass('active');
                } else if (settings.missingText) {
                    $list.html(settings.missingText).addClass('active');
                }

                $input.trigger('mentionListAfterShow', [$list, items]);
            });
        },

        /**
         * Search and returns matching list items.
         *
         * @param  {String} searchStr
         * @return {Array}
         */
        find: function (searchStr) {
            var result   = [];
            var settings = getSettings(this);

            if (typeof searchStr !== 'string' || !searchStr.length) {
                return result;
            }

            var normalizedSearchStr = searchStr.toLowerCase().replace(/\s+/g, '');

            if (settings.triggerChars.indexOf(normalizedSearchStr[0]) >= 0) {
                normalizedSearchStr = normalizedSearchStr.slice(1);
            }

            var match = false;
            $.each(settings.mentionsList, function (i, item) {
                if (i + 1 > settings.maxResults) {
                    return false;
                }

                match = false;
                if (typeof settings.filter === 'function') {
                    match = settings.filter(item, searchStr);
                } else {
                    match = (item.query + (item.value || ''))
                        .toLowerCase()
                        .replace(/\s+/g, '')
                        .indexOf(normalizedSearchStr) >= 0;
                }

                if (match) {
                    result.push(item);
                }
            });

            return result;
        },

        /**
         * @return {jQuery}
         */
        destroy: function () {
            return $(this).each(function (i, input) {
                if ($(input).data('pr.mention.listEl')) {
                    $($(input).data('pr.mention.listEl')).remove();
                }

                $(input).removeData('pr.mention');
            });
        }
    };

    $.fn.mention = function (methodOrOptions) {
        if (methods[methodOrOptions]) {
            return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
            // Default to "init"
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  methodOrOptions + ' does not exist on jQuery.mention');
        }
    };
})(jQuery);
