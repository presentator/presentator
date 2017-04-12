/**
 * Generic pins/targets creating js class.
 *
 * @param {Object} data
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
var Pins = function(data) {
    data = typeof data === 'object' ? data : {};

    var defaults = {
        pinIdPrefix:   '#target_',
        pinClass:      '.comment-target',
        layer:         '.target-layer',
        layerWrapper:  '.target-layer-wrapper',
        appendWrapper: null,
        dragTolerance: 10
    };

    this.settings = $.extend({}, defaults, data);

    this.$document = $(document);

    // action flags
    this.enableCreate  = true;
    this.enableDrag    = true;

    this.init();
};

/**
 * Main class initialization method.
 */
Pins.prototype.init = function() {
    var self = this;

    self.createBind();
    self.dragBind();
};

/* Pins event bind methods
------------------------------------------------------------------- */
/**
 * Binds pin create events.
 */
Pins.prototype.createBind = function() {
    var self = this;

    self.$document.off('click.pins.createBind', self.settings.layer);
    self.$document.on('click.pins.createBind', self.settings.layer, function(e) {
        if (PR.isMouseLeftBtn(e) && self.enableCreate) {
            e.preventDefault();

            var $item = $(self.getPinHtml());

            if (self.settings.appendWrapper) {
                $(this).closest(self.settings.layerWrapper)
                    .find(self.settings.appendWrapper).append($item);
            } else {
                $(this).closest(self.settings.layerWrapper).append($item);
            }

            $item.css({
                'left': e.offsetX - ($item.width() / 2),
                'top': e.offsetY - ($item.height() / 2)
            })

            $item.trigger('created.pins', [$item, e.offsetX, e.offsetY]);
        }
    });
};

/**
 * Binds pin drag/select events.
 */
Pins.prototype.dragBind = function() {
    var self = this;

    var isSelected = false;
    var isDragging = false;

    var diffX         = 0;
    var diffY         = 0;
    var initialPinPos = {};
    var newPinPos     = {};
    var clickPos      = {};

    var $activePin = null;

    // Drag start listener
    self.$document.off('mousedown.pins.dragBind touchstart.pins.dragBind ', self.settings.pinClass);
    self.$document.on('mousedown.pins.dragBind touchstart.pins.dragBind ', self.settings.pinClass, function(e) {
        if (PR.isMouseLeftBtn(e)) {
            e.preventDefault();

            $activePin = $(this);

            isSelected = true;
            isDragging = false;

            initialPinPos.x = $activePin.position().left;
            initialPinPos.y = $activePin.position().top;

            clickPos.x = e.offsetX;
            clickPos.y = e.offsetY;

            $activePin.trigger('dragStart.pins', [$activePin]);

            if (self.enableDrag) {
                $activePin.css('pointer-events', 'none');
            }
        }
    });

    // Drag move listener
    self.$document.off('mousemove.pins.dragBind touchmove.pins.dragBind', self.settings.layerWrapper);
    self.$document.on('mousemove.pins.dragBind touchmove.pins.dragBind', self.settings.layerWrapper, function(e) {
        if (
            self.enableDrag &&
            (isDragging || isSelected) &&
            ($activePin && $activePin.length)
        ) {
            e.preventDefault();

            newPinPos.x = e.offsetX - clickPos.x;
            newPinPos.y = e.offsetY - clickPos.y;

            diffX = Math.abs(initialPinPos.x - newPinPos.x);
            diffY = Math.abs(initialPinPos.y - newPinPos.y);

            if (isDragging ||
                (diffX > self.settings.dragTolerance || diffY > self.settings.dragTolerance)
            ) {
                if (!isDragging) {
                    isSelected = false;
                    isDragging = true;
                    $('body').addClass('pins-drag');
                }

                $activePin.css({
                    'left': newPinPos.x,
                    'top':  newPinPos.y
                });

                // edges check
                self.keepInside($activePin);
            }
        }
    });

    // Drag end
    self.$document.off('mouseup.pins.dragBind touchend.pins.dragBind', self.settings.layerWrapper);
    self.$document.on('mouseup.pins.dragBind touchend.pins.dragBind', self.settings.layerWrapper, function(e) {
        if (
            (isDragging || isSelected) &&
            ($activePin && $activePin.length)
        ) {
            e.preventDefault();

            if (isSelected) {
                if (self.enableDrag || $activePin.is(e.target) || $activePin.has(e.target).length) {
                    $activePin.trigger('clicked.pins', [$activePin]);
                }
            } else {
                $('body').removeClass('pins-drag');
                $activePin.trigger('dragEnd.pins', [$activePin]);
            }

            // reset
            $activePin.css('pointer-events', '');
            isSelected    = false;
            isDragging    = false;
            $activePin    = null;
            initialPinPos = {};
            newPinPos     = {};
            clickPos      = {};
        }
    });
};

/* Helpers
------------------------------------------------------------------- */
/**
 * Generates new pin html element string.
 * @return {String}
 */
Pins.prototype.getPinHtml = function() {
    return (
        '<div id="' + (this.settings.pinIdPrefix + Date.now())+ '" class="' + this.settings.pinClass.substr(1) + '"></div>'
    );
};

/**
 * Removes pin element(s).
 * @param {Mixed} item
 */
Pins.prototype.removePin = function(item) {
    var $item = $(item);

    $(document).trigger('removeStart.pins', [$item]);
    $item.addClass('remove-start').stop(true, true).delay(300).queue(function(next) {
        $(document).trigger('removeEnd.pins', [$item.remove()]);

        next();
    });
};

/**
 * Ensure that an pin item is in the layer boundaries.
 * @param {Mixed} item
 */
Pins.prototype.keepInside = function(item) {
    var $item  = $(item);
    var $layer = $item.closest(this.settings.layerWrapper).find(this.settings.layer);

    if (!$item.length || !$layer.length) {
        return;
    }

    var position    = $item.position();
    var cssSettings = {};

    if (position.left < 0) {
        cssSettings.left = 0;
    }
    if ((position.left + $item.outerWidth(true)) > $layer.width()) {
        cssSettings.left = $layer.width() - $item.outerWidth(true);
    }
    if (position.top < 0) {
        cssSettings.top = 0;
    }
    if (position.top + $item.outerHeight(true) > $layer.height()) {
        cssSettings.top = $layer.height() - $item.outerHeight(true);
    }

    $item.css(cssSettings);

};

/**
 * Turn ON pin action flags.
 * @param {String} type Default to 'all'
 */
Pins.prototype.enable = function(type) {
    type = type || 'all';

    if (type === 'all') {
        this.enableCreate = true;
        this.enableDrag   = true;
    } else if (type === 'create') {
        this.enableCreate = true;
    } else if (type === 'drag') {
        this.enableDrag = true;
    }
};

/**
 * Turn OFF pin action flags.
 * @param {String} type Default to 'all'
 */
Pins.prototype.disable = function(type) {
    type = type || 'all';

    if (type === 'all') {
        this.enableCreate = false;
        this.enableDrag   = false;
    } else if (type === 'create') {
        this.enableCreate = false;
    } else if (type === 'drag') {
        this.enableDrag = false;
    }
};
