var ScreenHotspotsView = function(data) {
    data = data || {};

    var defaults = {
        'versionSlider':     '.version-slider',
        'versionSliderItem': '.slider-item',

        'hotspotPopover':         '#hotspot_popover',
        'hotspotPopoverScreen':   '.hotspot-popover-screen',
        'hotspotPopoverUrlInput': '#hotspot_popover_url_input',
        'hotspotPopoverUrlBtn':   '#hotspot_popover_url_btn',
        'hotspotsWrapper':        '#hotspots_wrapper',
        'hotspot':                '.hotspot',

        // hotspots bulk panel
        'bulkPanel':                '#hotspots_bulk_panel',
        'bulkScreensPopover':       '#hotspots_bulk_screens_popover',
        'bulkScreensPopoverItem':   '.popover-thumb',
        'bulkScreensPopoverToggle': '#hotspots_bulk_screens_select',
        'bulkDeleteHandle':         '#hotspots_bulk_delete',
        'bulkResetHandle':          '.hotspots-bulk-reset',

        // hotspot context menu
        'contextMenu':             '.hotspot-context-menu',
        'contextDuplicateHandle':  '.duplicate-handle',
        'contextBulkSelectHandle': '.bulk-select-handle',
        'contextDeleteHandle':     '.delete-handle',

        'ajaxSaveHotspotsUrl':    '/screens/ajax-save-hotspots',
    };

    this.settings = $.extend({}, defaults, data);

    this.generalXHR = null;
    this.pressedKey = null;

    this.hotspotsInst = new Hotspots({
        appendContainer: this.settings.hotspotsWrapper
    });

    this.init();
};

/**
 * Init method
 */
ScreenHotspotsView.prototype.init = function() {
    var self = this;

    var $window   = $(window);
    var $document = $(document);
    var $body     = $('body');

    // Detect pressed nav
    $document.on('keydown', function(e) {
        self.pressedKey = e.which;
    });
    $document.on('keyup', function(e) {
        self.pressedKey = null;
    });

    $document.on('drawStart.hotspot', function(e, $hotspot) {
        $hotspot.data('isNew', true);
    });

    $document.on('drawEnd.hotspot', function(e, $hotspot) {
        self.selectHotspot($hotspot);
    });

    $document.on('clicked.hotspot', function(e, $hotspot) {
        if ($hotspot.hasClass('selected')) {
            return;
        }

        if (!$hotspot.data('isNew')) {
            if (self.pressedKey == PR.keys.ctrl) {
                self.duplicateHotspot($hotspot);
            } else if (self.pressedKey == PR.keys.shift) {
                if ($hotspot.hasClass('bulk-select')) {
                    $hotspot.removeClass('bulk-select');
                } else {
                    $hotspot.addClass('bulk-select');
                }

                self.toggleBulkPanel();
            } else {
                self.selectHotspot($hotspot);
            }
        } else {
            self.selectHotspot($hotspot);
        }
    });

    $document.on('resizeEnd.hotspot dragEnd.hotspot', function(e, $hotspot) {
        if (!$hotspot.data('isNew')) {
            if ($hotspot.hasClass('selected')) {
                self.repositionPopover($hotspot);
            }
            self.saveHotspots($hotspot.closest(self.settings.versionSliderItem).data('screen-id'));
        } else {
            self.repositionPopover($hotspot);
        }
    });

    $document.on('removeStart.hotspot', function(e, $hotspot) {
        if (!$hotspot.data('isNew')) {
            self.saveHotspots($hotspot.closest(self.settings.versionSliderItem).data('screen-id'));
        }

        self.deselectHotspot($hotspot);
    });

    $document.on('removeEnd.hotspot', function(e) {
        self.toggleBulkPanel();
    });

    $window.on('resize', function() {
        if ($('body').hasClass('hotspot-active')) {
            self.repositionPopover($('body').find('.hotspot.selected'));
        }
    });

    // select hotspot screen
    $document.on('click', self.settings.hotspotPopover + ' ' + self.settings.hotspotPopoverScreen, function(e) {
        e.preventDefault();

        var $activeHotspot = $('body').find('.hotspot.selected');
        if (!$activeHotspot.length) {
            return;
        }

        $(this).addClass('active').siblings().removeClass('active');

        PR.setData($activeHotspot, 'link', $(this).data('screen-id'));
        self.saveHotspots($activeHotspot.closest(self.settings.versionSliderItem).data('screen-id'), function(response) {
            if (response.success) {
                self.deselectHotspot($activeHotspot);
            }

            if ($activeHotspot.data('isNew')) {
                $activeHotspot.data('isNew', false);
                $activeHotspot.trigger('newHotspotSaved', [$activeHotspot]);
            }
        });
    });

    // outside hotspot click
    $document.on('mousedown touchstart', function(e) {
        if ($body.hasClass('hotspot-active')) {
            var $activeHotspot = self.getActiveScreenSliderItem().find('.hotspot.selected');

            if (
                $activeHotspot.length &&
                !$(self.settings.hotspotPopover).is(e.target) &&
                !$(self.settings.hotspotPopover).has(e.target).length &&
                !$activeHotspot.is(e.target) &&
                !$activeHotspot.has(e.target).length
            ) {
                e.preventDefault();

                if ($activeHotspot.data('isNew')) {
                    self.hotspotsInst.removeHotspot($activeHotspot);
                } else {
                    self.deselectHotspot($activeHotspot);
                }
            }
        }
    });

    // popover url handler
    $document.on('keydown', self.settings.hotspotPopoverUrlInput, function(e) {
        if (e.which === PR.keys.enter) {
            e.preventDefault();

            self.saveHotspotUrlInput(this);
        }
    });
    $document.on('click', self.settings.hotspotPopoverUrlBtn, function(e) {
        e.preventDefault();

        self.saveHotspotUrlInput(self.settings.hotspotPopoverUrlInput);
    });

    // deselect/remove on esc
    $document.on('keydown', function(e) {
        if (e.which === PR.keys.esc && $('body').hasClass('hotspot-active')) {
            e.preventDefault();
            var $activeHotspot = $('body').find('.hotspot.selected');
            if ($activeHotspot.data('isNew')) {
                self.hotspotsInst.removeHotspot($activeHotspot);
            } else {
                self.deselectHotspot($activeHotspot);
            }
        }
    });

    // Bind context menu
    $document.on('newHotspotSaved', function(e, $hotspot) {
        PR.setData($hotspot, 'context-menu', '#hotspot_context_menu');
        $hotspot.find('.remove-handle, .resize-handle').addClass('context-menu-ignore');
    });

    /* Hotspot bulk actions
    --------------------------------------------------------------- */
    // Reset bulk selection
    $document.on('click', self.settings.bulkPanel + ' ' + self.settings.bulkResetHandle, function(e) {
        e.preventDefault();

        self.resetBulkSelectedHotspots();
    });

    // Remove bulk selected hotspots
    $document.on('click', self.settings.bulkPanel + ' ' + self.settings.bulkDeleteHandle, function(e) {
        e.preventDefault();

        self.removeBulkSelectedHotspots();
    });

    // Hotspots bulk screens popover handle
    var $bulkScreensPopoverToggle = $();
    $document.on('click', self.settings.bulkScreensPopoverToggle, function(e) {
        e.preventDefault();
        $bulkScreensPopoverToggle = $(this);

        if ($bulkScreensPopoverToggle.hasClass('active')) {
            self.hideBulkScreensPopover();
        } else {
            self.showBulkScreensPopover();
        }
    });
    $document.on('click', function(e) {
        // close on outside click
        if (
            $bulkScreensPopoverToggle.hasClass('active') &&
            !$bulkScreensPopoverToggle.is(e.target) &&
            !$bulkScreensPopoverToggle.find(e.target).length &&
            self.$activeBulkScreensPopover &&
            !self.$activeBulkScreensPopover.is(e.target) &&
            !self.$activeBulkScreensPopover.find(e.target).length
        ) {
            self.hideBulkScreensPopover();
        }
    });

    // Clone bulk selected hotspots
    $document.on('click', self.settings.bulkScreensPopover + ' ' + self.settings.bulkScreensPopoverItem, function(e) {
        e.preventDefault();
        e.stopPropagation();

        $(this).addClass('active');
        self.cloneBulkSelectedHotspots($(this).data('screen-id'));
    });

    /* Hotspot context menu actions
    --------------------------------------------------------------- */
    // Duplicate/clone
    $document.on('click', self.settings.contextMenu + ' ' + self.settings.contextDuplicateHandle, function(e) {
        e.preventDefault();

        var $hotspot = self.getActiveScreenSliderItem().find(self.settings.hotspot + '.context-menu-item-active');

        self.duplicateHotspot($hotspot);
    });

    // Bulk select
    $document.on('click', self.settings.contextMenu + ' ' + self.settings.contextBulkSelectHandle, function(e) {
        e.preventDefault();

        var $hotspot = self.getActiveScreenSliderItem().find(self.settings.hotspot + '.context-menu-item-active');
        $hotspot.addClass('bulk-select');

        self.toggleBulkPanel();
    });

    // Delete
    $document.on('click', self.settings.contextMenu + ' ' + self.settings.contextDeleteHandle, function(e) {
        e.preventDefault();

        var $hotspot = self.getActiveScreenSliderItem().find(self.settings.hotspot + '.context-menu-item-active');

        self.hotspotsInst.removeHotspot($hotspot);
    });
};

/**
 * Returns the current active screen slider item element.
 * @return {jQuery}
 */
ScreenHotspotsView.prototype.getActiveScreenSliderItem = function() {
    return $(this.settings.versionSlider).find(this.settings.versionSliderItem + '.active');
};

/**
 * Returns object with screen hotspots coordinates
 * @param  {Number} screenId
 * @return {Object}
 */
ScreenHotspotsView.prototype.getHotspotsCoordinates = function(screenId) {
    var self   = this;
    var result = {};

    var $screenSliderItem = $(self.settings.versionSliderItem + '[data-screen-id="' + screenId + '"]');
    if (!$screenSliderItem.length) {
        console.warn('Missing screen slider item with id ' + screenId);
        return result;
    }

    var scaleFactor = $screenSliderItem.data('scale-factor') || 1;

    var position = {};
    var $hotspot = null;
    $screenSliderItem.find(self.settings.hotspot).not('.remove-start').each(function(i, hotspot) {
        $hotspot = $(hotspot);

        if ($hotspot.is(':hidden')) {
            // get the element position from the style attr
            position.left = $hotspot.css('left') << 0;
            position.top  = $hotspot.css('top') << 0;
        } else {
            position = $hotspot.position();
        }

        result[$hotspot.attr('id')] = {
            'left':   position.left * scaleFactor,
            'top':    position.top * scaleFactor,
            'width':  $hotspot.outerWidth(true) * scaleFactor,
            'height': $hotspot.outerHeight(true) * scaleFactor,
            'link':   $hotspot.data('link')
        };
    });

    return result;
};

/**
 * Persists single screen hotspots.
 * @param {Number} screenId
 */
ScreenHotspotsView.prototype.saveHotspots = function(screenId, callback) {
    var self = this;

    if (!screenId) {
        console.warn('Missing screen id!');
        return;
    }

    var hotspots = self.getHotspotsCoordinates(screenId);

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url: self.settings.ajaxSaveHotspotsUrl,
        type: 'POST',
        data: {
            'id': screenId,
            'hotspots': hotspots
        }
    }).done(function(response) {
        if (PR.isFunction(callback)) {
            callback(response);
        }
    });
};

/**
 * Selects a hotspot element.
 * @param {Mixed} hotspot
 */
ScreenHotspotsView.prototype.selectHotspot = function (hotspot) {
    var self = this;

    var $hotspot = $(hotspot);
    if (!$hotspot.length) {
        console.warn('Missing hotspot!');
        return;
    }

    $('body').addClass('hotspot-active');
    $hotspot.addClass('selected');

    self.repositionPopover($hotspot);
};

/**
 * Deselects a hotspot element.
 * @param {Mixed} hotspot
 */
ScreenHotspotsView.prototype.deselectHotspot = function (hotspot) {
    var self = this;

    var $hotspot = $(hotspot || (self.settings.hotspot + '.selected'));
    if (!$hotspot.length) {
        // nothing to deselect
        return;
    }

    self.hideHotspotLinkPopover();

    $hotspot.removeClass('selected');
    $('body').removeClass('hotspot-active');
};

/**
 * Handles hiding animation for hotspot links popover.
 */
ScreenHotspotsView.prototype.hideHotspotLinkPopover = function () {
    var self = this;

    var $popover = $(self.settings.hotspotPopover);
    if ($popover.is(':visible')) {
        $popover.addClass('close-start').stop(true, true).delay(400).queue(function(next) {
            $popover.removeClass('close-start').find(self.settings.hotspotPopoverScreen).removeClass('active');
            $popover.find(self.settings.hotspotPopoverUrlInput).val('');

            next();
        });
    }
};

/**
 * Takes care for hotspot popover position based on a hotspot element.
 * @see `PR.repositionPopover()`
 * @param {Mixed} hotspot
 */
ScreenHotspotsView.prototype.repositionPopover = function (hotspot) {
    var self = this;

    var $hotspot = hotspot ? $(hotspot) : self.getActiveScreenSliderItem().find(self.settings.hotspot + '.selected');
    if (!$hotspot.length) {
        console.warn('Missing hotspot!');
        return;
    }

    var $popover = $(self.settings.hotspotPopover);

    PR.repositionPopover($hotspot, $popover, '.version-slider-content');

    var hotspotLink = $hotspot.data('link');
    if (!isNaN(hotspotLink)) {
        // screens tab
        $popover.find('.tabs').tabs('goTo', 'hotspot_tab_screens', false);
        $popover.find(self.settings.hotspotPopoverScreen).removeClass('active')
            .filter('[data-screen-id="' + hotspotLink + '"]').addClass('active');
    } else if (PR.isValidUrl(hotspotLink)) {
        // url tab
        $popover.find('.tabs').tabs('goTo', 'hotspot_tab_url', false);
        $popover.find(self.settings.hotspotPopoverUrlInput).val(PR.htmlDecode(hotspotLink));
    }
};

/**
 * Clone and save an existing hotspot to the current screen.
 * @param {Mixed} hotspot
 */
ScreenHotspotsView.prototype.duplicateHotspot = function (hotspot) {
    var $hotspot = $(hotspot);
    var $clone   = $hotspot.clone(true);
    var position = $hotspot.position();

    $clone.attr('id', 'hotspot_' + Date.now()).css({
        'left': position.left + 20,
        'top':  position.top + 20
    });

    $hotspot.parent().append($clone);

    this.saveHotspots($hotspot.closest(this.settings.versionSliderItem).data('screen-id'));
};

/**
 * Persists custom hotspot url link.
 * @param {Mixed} input
 */
ScreenHotspotsView.prototype.saveHotspotUrlInput = function (input) {
    var self = this;

    var $input = $(input);
    if (!$input.length) {
        console.warn('Hotspot popover input not found!');
        return;
    }

    // validate url
    if (!PR.isValidUrl($input.val())) {
        $input.closest('.form-group').addClass('has-error');
        return;
    }

    $input.closest('.form-group').removeClass('has-error');

    var $activeHotspot = self.getActiveScreenSliderItem().find('.hotspot.selected');
    PR.setData($activeHotspot, 'link', PR.htmlEncode($input.val()));

    self.saveHotspots($activeHotspot.closest(self.settings.versionSliderItem).data('screen-id'), function(response) {
        if (response.success) {
            self.deselectHotspot($activeHotspot);
        }

        if ($activeHotspot.data('isNew')) {
            $activeHotspot.data('isNew', false);
            $activeHotspot.trigger('newHotspotSaved', [$activeHotspot]);
        }
    });
};

/**
 * Enables hotspot actions.
 */
ScreenHotspotsView.prototype.enable = function() {
    this.hotspotsInst.enable();

    $(this.settings.hotspotPopover).find('.tabs').tabs();
};

/**
 * Disables hotspot actions.
 */
ScreenHotspotsView.prototype.disable = function() {
    this.hotspotsInst.disable();
};

/* Hotspots bulk actions
------------------------------------------------------------------- */
/**
 * Shows hotspots bulk panel.
 */
ScreenHotspotsView.prototype.showBulkPanel = function() {
    $(this.settings.bulkPanel).stop(true, true).slideDown(300);
};

/**
 * Hides hotspots bulk panel.
 */
ScreenHotspotsView.prototype.hideBulkPanel = function() {
    var self = this;

    self.hideBulkScreensPopover();
    $(self.settings.bulkPanel).stop(true, true).slideUp(300)
        .find(self.settings.bulkScreensPopoverToggle).removeClass('active');
};

/**
 * Auto show/hide bulk panel based on the number of bulk selected hotspots.
 */
ScreenHotspotsView.prototype.toggleBulkPanel = function() {
    var $bulkHotspots = this.getBulkSelectedHotspots();
    if ($bulkHotspots.length) {
        this.showBulkPanel();
    } else {
        this.hideBulkPanel();
    }
};

/**
 * Shows hotspots bulk panel screens popover.
 */
ScreenHotspotsView.prototype.showBulkScreensPopover = function() {
    var self = this;

    $(self.settings.bulkScreensPopoverToggle).addClass('active');

    self.$activeBulkScreensPopover = $(self.settings.bulkScreensPopover); // object cache for later usage
    self.$activeBulkScreensPopover.addClass('active');
    self.$activeBulkScreensPopover.find(self.settings.bulkScreensPopoverItem).show().removeClass('active')
        .filter('[data-screen-id="' + self.getActiveScreenSliderItem().data('screen-id') + '"]').hide();
};

/**
 * Hides hotspots bulk panel screens popover.
 */
ScreenHotspotsView.prototype.hideBulkScreensPopover = function() {
    var self = this;

    self.$activeBulkScreensPopover = $(this.settings.bulkScreensPopover);

    if (self.$activeBulkScreensPopover.hasClass('active')) {
        $(self.settings.bulkScreensPopoverToggle).removeClass('active');

        self.$activeBulkScreensPopover.addClass('close-start').stop(true, true).delay(400).queue(function(next) {
            self.$activeBulkScreensPopover.removeClass('close-start active');

            self.$activeBulkScreensPopover = null; // clear cached selector

            next();
        });
    }
};

/**
 * Returns collection with all bulk selected hotspot elements.
 * @return {Array}
 */
ScreenHotspotsView.prototype.getBulkSelectedHotspots = function() {
    return $(this.settings.hotspot + '.bulk-select');
};

/**
 * Resets hotspot bulk selection.
 */
ScreenHotspotsView.prototype.resetBulkSelectedHotspots = function() {
    var $bulkHotspots = this.getBulkSelectedHotspots();

    $bulkHotspots.removeClass('bulk-select');
    this.hideBulkPanel();
};

/**
 * Removes the bulk selected hotspots.
 */
ScreenHotspotsView.prototype.removeBulkSelectedHotspots = function() {
    this.hotspotsInst.removeHotspot(this.getBulkSelectedHotspots());
    this.hideBulkPanel();
};

/**
 * Clones the bulk selected hotspots to another screen.
 */
ScreenHotspotsView.prototype.cloneBulkSelectedHotspots = function(screenId) {
    var self               = this;
    var $versionSliderItem = $(self.settings.versionSlider).find(self.settings.versionSliderItem + '[data-screen-id="' + screenId + '"]');
    var $versionSlider     = $versionSliderItem.closest(self.settings.versionSlider);

    if (!$versionSliderItem.length) {
        console.warn(screenId + ' is missing.');
        return;
    }

    var $hotspotLayer      = $versionSliderItem.find('.hotspot-layer');
    var hotspotLayerWidth  = $hotspotLayer.width();
    var hotspotLayerHeight = $hotspotLayer.height();

    var $clones       = $();
    var $hotspot      = $();
    var $bulkHotspots = self.getBulkSelectedHotspots();
    $bulkHotspots.each(function(i, hotspot) {
        $hotspot = $(hotspot);
        if ($hotspot.position().top + $hotspot.height() > hotspotLayerHeight ||
            $hotspot.position().left + $hotspot.width() > hotspotLayerWidth
        ) {
            return true; // skip
        }

        $clones = $clones.add(
            $(hotspot).clone(true).attr('id', ('hotstpot_' + (Date.now() + i)))
        );
    });

    $versionSliderItem.find(self.settings.hotspotsWrapper).append($clones);

    $versionSlider.slider('goTo', $versionSliderItem.index());

    self.saveHotspots(screenId);
    self.resetBulkSelectedHotspots()
};
