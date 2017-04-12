/**
 * Very simple jQuery plugin for generating custom selects.
 *
 * @version 0.1
 * @author: Gani Georgiev <gani.georgiev@gmail.com>
 */
;(function($) {
    var defaults = {
        // selectors
        wrapperClass:  '.custom-select',
        fieldClass:    '.select-field',
        dropdownClass: '.select-dropdown',
        optionClass:   '.option'
    };

    var settings = {};

    function getSettings(select) {
        return $(select).data('pr.selectify') || {};
    }

    function buildOptions(select) {
        var $select  = $(select);
        var settings = getSettings($select);

        var optionsHtml = '';
        var $option     = null;
        var groupLabel  = '';
        $select.find('option').each(function(i, option) {
            $option = $(option);
            if ($option.parent('optgroup').length) {
                groupLabel = $option.parent('optgroup').attr('label');
            } else {
                groupLabel = '';
            }

            optionsHtml += (
                '<div class="' + settings.optionClass.substr(1) + ($option.is(':selected') ? ' active ' : '') + ($option.is(':disabled') ? ' disabled ' : '') + '" ' +
                    ' data-value="' + $option.attr('value') + '" ' +
                    (groupLabel.length ? (' data-group="' + groupLabel + '" ') : '') +
                '>' + $option.text() + '</div>'
            );
        });

        var $wrapper = $(select).closest(settings.wrapperClass);

        $wrapper.find(settings.dropdownClass).html(optionsHtml);
        $wrapper.find(settings.fieldClass).html($select.find('option:selected').text());
    }

    var methods = {
        init: function(options) {
            return $(this).each(function(i, select) {
                var $select = $(select);

                if (!$select.is('select')) {
                    console.warn('PR Select plugin requires a select html element!');
                    return true;
                }

                settings = $select.data('pr.selectify');
                if (typeof settings === 'undefined') {
                    settings = $.extend({}, defaults, options);
                    $select.data('pr.selectify', settings);
                } else {
                    methods.destroy.refresh(select); // refresh on reinit

                    return true;
                }

                $select.wrap('<div class="' + settings.wrapperClass.substr(1) + '"></div>');

                var $wrapper  = $select.closest(settings.wrapperClass);
                $wrapper.append('<div class="' + settings.fieldClass.substr(1) + '"></div><div class="' + settings.dropdownClass.substr(1) + '"></div>');
                var $field    = $wrapper.children(settings.fieldClass);
                var $dropdown = $wrapper.children(settings.dropdownClass);

                buildOptions(select);

                // actions
                $field.off('click.pr.selectify.field');
                $field.on('click.pr.selectify.field', function(e) {
                    e.preventDefault();
                    if ($wrapper.hasClass('active')) {
                        methods.closeDropdown.call(select);
                    } else {
                        methods.openDropdown.call(select);
                    }
                });

                $wrapper.off('click.pr.selectify.option', settings.optionClass);
                $wrapper.on('click.pr.selectify.option', settings.optionClass, function(e) {
                    e.preventDefault();

                    methods.select.call(select, $(this).data('value'));
                });

                $select.off('change.pr.selectify');
                $select.on('change.pr.selectify', function(e, call) {
                    if (call !== false) {
                        methods.select.call(this, $(this).val());
                    }
                });
            });
        },
        refresh: function() {
            return $(this).each(function(i, select) {
                buildOptions(select);
            });
        },
        openDropdown: function() {
            var $select, $wrapper;
            return $(this).each(function(i, select) {
                $select  = $(select);
                $wrapper = $select.closest(getSettings(select).wrapperClass);

                $select.trigger('pr.selectify.open', [$select, $wrapper]);
                $wrapper.addClass('active');
            });
        },
        closeDropdown: function() {
            var $select, $wrapper;
            return $(this).each(function(i, select) {
                $select  = $(select);
                $wrapper = $select.closest(getSettings(select).wrapperClass);

                $select.trigger('pr.selectify.close', [$select, $wrapper]);
                $wrapper.removeClass('active');
            });
        },
        select: function(optionValue) {
            var $select, $option, settings, $wrapper, $field, $dropdown;

            return $(this).each(function(i, select) {
                $select = $(select);

                settings = getSettings($select);

                $wrapper  = $select.closest(settings.wrapperClass);
                $field    = $wrapper.find(settings.fieldClass).first();
                $dropdown = $wrapper.find(settings.dropdownClass).first();

                $option = $dropdown.find('[data-value="' + optionValue + '"]');
                if ($option.hasClass('disabled')) {
                    return;
                }

                $option.addClass('active').siblings().removeClass('active');
                $field.text($option.text());

                methods.closeDropdown.call(select);

                $select.val(optionValue)
                    .trigger('change', false)
                    .trigger('pr.selectify.change', [$select, $wrapper, $option]);
            });
        },
        destroy: function() {
            var settings, $parent, $select;

            return $(this).each(function(i, select) {
                $select = $(select);
                settings = $select.data('pr.selectify');
                if (!settings) {
                    return true;
                }

                $parent = $select.closest(settings.wrapperClass);
                $select.insertAfter($parent);

                $select.removeData('pr.selectify');
                $parent.remove();
            });
        }
    };

    // close on outside click
    var $openSelectWrappers = $();
    $(document).on('pr.selectify.open', function(e, $select, $wrapper) {
        e.preventDefault();

        $openSelectWrappers = $openSelectWrappers.add($wrapper);
    }).on('pr.selectify.close', function(e, $select, $wrapper) {
        e.preventDefault();

        $openSelectWrappers = $openSelectWrappers.not($wrapper);
    }).on('click.pr.selectify', function(e) {
        if (
            $openSelectWrappers.length &&
            !$openSelectWrappers.is(e.target) &&
            !$openSelectWrappers.find(e.target).length
        ) {
            methods.closeDropdown.call($openSelectWrappers.find('select'));
        }
    });

    $.fn.selectify = function(methodOrOptions) {
        if (methods[methodOrOptions]) {
            return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
            // Default to "init"
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  methodOrOptions + ' does not exist on jQuery.selectify');
        }
    };
})(jQuery);
