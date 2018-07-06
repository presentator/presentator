// TODO: Update hotspot container on slider change

var Hotspots = function(data) {
    data = data || {};

    // Load settings
    var defaults = {
        minHeight:       0,
        minWidth:        0,
        squareScale:     false,
        maxHotspots:     Infinity,
        drawContainer:   '.hotspot-layer-wrapper',
        drawLayer:       '.hotspot-layer',
        appendContainer: null,
        hotspot:         '.hotspot',
        resizeHandle:    '.resize-handle',
        removeHandle:    '.remove-handle',
    };
    this.settings = $.extend({}, defaults, data);

    // Selectors
    this.$body          = $('body');
    this.$window        = $(window);
    this.$document      = $(document);
    this.$drawLayer     = $(this.settings.drawLayer).first();
    this.$drawContainer = this.$drawLayer.closest(this.settings.drawContainer);
    this.$hotspot       = null;

    // add helper classes (if the selectors are different from the default ones)
    this.$drawContainer.addClass('hotspot-layer-wrapper');
    this.$drawLayer.addClass('hotspot-layer');

    // Flags
    this.isDrawing  = false;
    this.isDragging = false;
    this.isResizing = false;
    this.isSelected = false;

    this.enableDraw = true;
    this.enableDrag = true;
    this.enableResize = true;

    this.removeTimeout = null;

    this.init();
};

Hotspots.prototype.init = function() {
    var self = this;

    self.drawBind();

    self.dragBind();

    self.resizeBind();

    self.removeBind();
};

/* Main hotspots manipulation bind methods
=============================================================== */
/**
 * Binds hotspot draw events.
 */
Hotspots.prototype.drawBind = function() {
    var self = this;

    var drawStartPosition = {};

    // Draw start
    self.$document.off('mousedown.hotspots.drawBind', self.settings.drawLayer);
    self.$document.on('mousedown.hotspots.drawBind', self.settings.drawLayer, function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (PR.isMouseLeftBtn(e) && self.enableDraw) {
            self.$drawContainer = $(this).closest(self.settings.drawContainer);

            if (self.$drawContainer.find(self.settings.hotspot).length < self.settings.maxHotspots) {
                self.setActiveState('draw');

                drawStartPosition.x = e.offsetX;
                drawStartPosition.y = e.offsetY;

                self.$hotspot = $(
                    '<div id="hotspot_' + Date.now() + '" class="' + self.settings.hotspot.substr(1) + '" data-transition="fade">' +
                        '<span class="' + self.settings.removeHandle.substr(1) + '"><i class="ion ion-md-trash"></i></span>' +
                        '<span class="' + self.settings.resizeHandle.substr(1) + '"></span>' +
                    '</div>'
                );
                self.$hotspot.css({
                    'left': drawStartPosition.x,
                    'top':  drawStartPosition.y
                })

                if (self.settings.appendContainer) {
                    self.$drawContainer.find(self.settings.appendContainer).append(self.$hotspot);
                } else {
                    self.$drawContainer.append(self.$hotspot);
                }

                self.$drawContainer.trigger('drawStart.hotspot', [self.$hotspot]);
            }
        }
    });

    // Draw move listener
    self.$document.off('mousemove.hotspots.drawBind', self.settings.drawLayer);
    self.$document.on('mousemove.hotspots.drawBind', self.settings.drawLayer, function (e) {
        if (self.enableDraw && self.isDrawing && self.$hotspot) {
            self.$hotspot.css(self.normalizeDraw(drawStartPosition, { 'x': e.offsetX, 'y': e.offsetY}));
        }
    });

    // Draw end
    self.$document.off('mouseup.hotspots.drawBind');
    self.$document.on('mouseup.hotspots.drawBind', function (e) {
        if (self.enableDraw && self.isDrawing) {
            if (self.$hotspot.width() < self.settings.minWidth ||
                self.$hotspot.height() < self.settings.minHeight
            ) {
                self.$hotspot.remove();
                self.$hotspot = null;
            }

            self.$document.trigger('drawEnd.hotspot', [self.$hotspot]);

            self.setActiveState(null);
            self.$hotspot = null;
            drawStartPosition = {};
        }
    });
};

/**
 * Binds hotspot drag/select events.
 */
Hotspots.prototype.dragBind = function() {
    var self = this;

    var tolerance              = 3;
    var hotspotInitialPosition = {};
    var hotspotClickPosition   = {};
    var hotspotNewPosition     = {};
    var diffX                  = 0;
    var diffY                  = 0;

    // Drag start
    self.$document.off('mousedown.hotspots.dragBind', self.settings.hotspot);
    self.$document.on('mousedown.hotspots.dragBind', self.settings.hotspot, function(e) {
        if (PR.isMouseLeftBtn(e) && self.enableDrag) {
            e.preventDefault();
            e.stopPropagation();

            self.$hotspot = $(this);

            self.setActiveState('selected');

            hotspotInitialPosition.x = self.$hotspot.position().left;
            hotspotInitialPosition.y = self.$hotspot.position().top;

            hotspotClickPosition.x = e.offsetX;
            hotspotClickPosition.y = e.offsetY;

            self.$document.trigger('dragStart.hotspot', [self.$hotspot]);
        }
    });

    // Drag move listener
    self.$document.off('mousemove.hotspots.dragBind', self.settings.drawContainer);
    self.$document.on('mousemove.hotspots.dragBind', self.settings.drawContainer, function(e) {
        if ((self.isDragging || self.isSelected) && self.$hotspot) {
            hotspotNewPosition.x = e.offsetX - hotspotClickPosition.x;
            hotspotNewPosition.y = e.offsetY - hotspotClickPosition.y;
            diffX = Math.abs(hotspotInitialPosition.x - hotspotNewPosition.x);
            diffY = Math.abs(hotspotInitialPosition.y - hotspotNewPosition.y);

            if (self.isDragging ||
                (diffX > tolerance || diffY > tolerance)
            ) {
                if (!self.isDragging) {
                    self.setActiveState('drag');
                }

                self.$hotspot.css({
                    'left': hotspotNewPosition.x,
                    'top':  hotspotNewPosition.y,
                });

                self.keepInside(self.$hotspot, $(this).find(self.settings.drawLayer));
            }
        }
    });

    // Drag end
    self.$document.off('mouseup.hotspots.dragBind');
    self.$document.on('mouseup.hotspots.dragBind', function(e) {
        if ((self.isSelected || self.isDragging) && self.$hotspot) {
            if (self.isSelected) {
                self.$hotspot.trigger('clicked.hotspot', [self.$hotspot]);
            } else {
                self.$hotspot.trigger('dragEnd.hotspot', [self.$hotspot]);
            }

            self.setActiveState(null);
            self.$hotspot = null;
            dragStartPosition = {};
        }
    });
};

/**
 * Binds hotspot resize events.
 */
Hotspots.prototype.resizeBind = function() {
    var self = this;

    var handleSize             = 12;
    var hotspotInitialPosition = {};
    var handleClickPosition    = {};

    // Resize start
    self.$document.off('mousedown.hotspots.resizeBind', self.settings.hotspot + ' ' + self.settings.resizeHandle);
    self.$document.on('mousedown.hotspots.resizeBind', self.settings.hotspot + ' ' + self.settings.resizeHandle, function(e) {
        if (PR.isMouseLeftBtn(e) && self.enableResize) {
            e.preventDefault();
            e.stopPropagation();

            self.setActiveState('resize');

            self.$hotspot = $(this).closest(self.settings.hotspot);
            self.$drawContainer = self.$hotspot.closest(self.settings.drawContainer);

            if (self.$hotspot) {
                var position = self.$hotspot.position();
                hotspotInitialPosition.x = position.left;
                hotspotInitialPosition.y = position.top;

                handleClickPosition.x = handleSize - e.offsetX;
                handleClickPosition.y = handleSize - e.offsetY;

                self.$hotspot.trigger('resizeStart.hotspot', [self.$hotspot]);
            }
        }
    });

    // Resize move listener
    self.$document.off('mousemove.hotspots.resizeBind', self.settings.drawContainer);
    self.$document.on('mousemove.hotspots.resizeBind', self.settings.drawContainer, function (e) {
        if (self.isResizing && self.$hotspot) {
            self.$hotspot.css(self.normalizeDraw(hotspotInitialPosition, {
                'x': e.offsetX + handleClickPosition.x,
                'y': e.offsetY + handleClickPosition.y
            }));
        }
    });

    // Resize end
    self.$document.off('mouseup.hotspots.resizeBind');
    self.$document.on('mouseup.hotspots.resizeBind', function(e) {
        if (self.isResizing) {
            self.$hotspot.trigger('resizeEnd.hotspot', [self.$hotspot]);

            self.setActiveState(null);
            self.$hotspot = null;
            handleClickPosition = {};
        }
    });
};

/**
 * Binds remove handle events.
 */
Hotspots.prototype.removeBind = function() {
    var self = this;

    // stop mousedown bubbling
    self.$document.off('mousedown.hotspots.removeBind', self.settings.hotspot + ' ' + self.settings.removeHandle);
    self.$document.on('mousedown.hotspots.removeBind', self.settings.hotspot + ' ' + self.settings.removeHandle, function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    self.$document.off('mouseup.hotspots.removeBind', self.settings.hotspot + ' ' + self.settings.removeHandle);
    self.$document.on('mouseup.hotspots.removeBind', self.settings.hotspot + ' ' + self.settings.removeHandle, function(e) {
        e.preventDefault();
        e.stopPropagation();

        self.removeHotspot($(this).closest(self.settings.hotspot));
    });
};

/* Helpers
=============================================================== */
Hotspots.prototype.normalizeDraw = function(_startPos, _newPos) {
    var left   = 0;
    var top    = 0;
    var width  = _newPos.x - _startPos.x;
    var height = _newPos.y - _startPos.y;

    if (this.settings.squareScale) {
        width  = height;
        height = width;
    }

    if (width > 0) {
        left = _startPos.x;
    } else {
        left = _newPos.x;
    }

    if (height > 0) {
        top = _startPos.y;
    } else {
        top = _newPos.y;
    }

    return {
        'top':    top,
        'left':   left,
        'width':  Math.abs(width),
        'height': Math.abs(height),
    };
};

Hotspots.prototype.keepInside = function($hotspot, $drawLayer) {
    if (!PR.isJquery($hotspot)) {
        console.warn('$hotspot must be a valid jQuery object!');
        return;
    }

    if (!PR.isJquery($drawLayer)) {
        console.warn('$drawLayer must be a valid jQuery object!');
        return;
    }

    var position    = $hotspot.position();
    var cssSettings = {};

    if (position.left < 0) {
        cssSettings.left = 0;
    }
    if ((position.left + $hotspot.outerWidth(true)) > $drawLayer.width()) {
        cssSettings.left = $drawLayer.width() - $hotspot.outerWidth(true);
    }
    if (position.top < 0) {
        cssSettings.top = 0;
    }
    if (position.top + $hotspot.outerHeight(true) > $drawLayer.height()) {
        cssSettings.top = $drawLayer.height() - $hotspot.outerHeight(true);
    }

    $hotspot.css(cssSettings);
};

Hotspots.prototype.removeHotspot = function($hotspot) {
    var self = this;

    if (!PR.isJquery($hotspot)) {
        console.warn('$hotspot must be a valid jQuery object!');
        return;
    }

    $hotspot.addClass('remove-start');

    self.$document.trigger('removeStart.hotspot', [$hotspot]);

    $hotspot.addClass('remove-start').delay(400).queue(function(next) {
        self.$document.trigger('removeEnd.hotspot', [$hotspot.remove()]);

        next();
    });
};

Hotspots.prototype.setActiveState = function(state) {
    if (state === 'draw') {
        this.isDrawing = true;
        this.isDragging = false;
        this.isResizing = false;
        this.isSelected = false;

        this.$body.addClass('hotspot-action-active hotspot-draw')
            .removeClass('hotspot-drag hotspot-resize hotspot-selected');
    } else if (state === 'drag') {
        this.isDrawing  = false;
        this.isDragging = true;
        this.isResizing = false;
        this.isSelected = false;

        this.$body.addClass('hotspot-action-active hotspot-drag')
            .removeClass('hotspot-draw hotspot-resize hotspot-selected');
    } else if (state === 'selected') {
        this.isDrawing  = false;
        this.isDragging = false;
        this.isResizing = false;
        this.isSelected = true;

        this.$body.addClass('hotspot-action-active hotspot-selected')
            .removeClass('hotspot-draw hotspot-drag hotspot-resize');
    } else if (state === 'resize') {
        this.isDrawing  = false;
        this.isDragging = false;
        this.isResizing = true;
        this.isSelected = false;

        this.$body.addClass('hotspot-action-active hotspot-resize')
            .removeClass('hotspot-draw hotspot-drag hotspot-selected');
    } else {
        this.isDrawing  = false;
        this.isDragging = false;
        this.isResizing = false;
        this.isSelected = false;

        this.$body.removeClass('hotspot-action-active hotspot-draw hotspot-drag hotspot-resize hotspot-selected');
    }
};

Hotspots.prototype.enable = function(type) {
    type = type || 'all';

    if (type === 'all') {
        this.enableDraw   = true;
        this.enableDrag   = true;
        this.enableResize = true;
    } else if (type === 'draw') {
        this.enableDraw = true;
    } else if (type === 'drag') {
        this.enableDrag = true;
    } else if (type === 'resize') {
        this.enableResize = true;
    }
};

Hotspots.prototype.disable = function(type) {
    type = type || 'all';

    if (type === 'all') {
        this.enableDraw   = false;
        this.enableDrag   = false;
        this.enableResize = false;
    } else if (type === 'draw') {
        this.enableDraw = false;
    } else if (type === 'drag') {
        this.enableDrag = false;
    } else if (type === 'resize') {
        this.enableResize = false;
    }
};
