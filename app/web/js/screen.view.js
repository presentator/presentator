// @todo Refact and cmerge common logic with PreviewView
var ScreenView = function (data) {
    data = data || {};

    var defaults = {
        'maxUploadSize':   15,

        // screens list
        'uploadContainer':    '#upload_container',
        'uploadPopup':        '#screens_upload_popup',

        'screensWrapperTabs':     '#version_screens_tabs',
        'screensWrapper':         '.version-screens',
        'screenItem':             '.screen-item',
        'screenDeleteHandle':     '.screen-delete',
        'screenUploadHandle':     '.action-box',
        'screenTitlteHolder':     '.screen-title',
        'activeSlideTitleHolder': '.active-slide-title',
        'activeSlideOrderHolder': '.active-slide-order',

        // bulk
        'bulkPanel':          '#screens_bulk_panel',
        'bulkReset':          '.screen-bulk-reset',
        'bulkInput':          '.screen-bulk-checkbox',
        'bulkDeleteHandle':   '#bulk_delete_btn',
        'bulkVersionsSelect': '#bulk_versions_select',

        // scren edit
        'screenImgHolder':             '.screen-img',
        'replaceScreenImageContainer': '#replace_image_container',
        'editPopup':                   '#screens_edit_popup',
        'openHandle':                  '.open-screen-edit',
        'closeHandle':                 '.close-screen-edit',
        'versionSlider':               '.version-slider',
        'versionSliderItem':           '.slider-item',
        'settingsHandle':              '#panel_settings_handle',
        'nextSlideHandle':             '#slider_next_handle',
        'prevSlideHandle':             '#slider_prev_handle',

        // mode handles
        'previewModeHandle':  '#panel_preview_handle',
        'hotspotsModeHandle': '#panel_hotspots_handle',
        'commentsModeHandle': '#panel_comments_handle',

        // texts
        'versionOptionText':       'Version',
        'confirmDeleteText':       'Do you really want to delete the selected screen?',
        'confirmBulkDeleteText':   'Do you really want to delete the selected screens?',
        'hotspotsTooltipText':     'Click and drag to create hotspot',
        'commentsTooltipText':     'Click to leave a comment',
        'replaceImageConfirmText': 'Do you really want to replace the screen image?',

        // urls
        'ajaxReplaceScreenImageUrl': '/admin/screens/ajax-replace',
        'ajaxUploadUrl':             '/admin/screens/ajax-upload',
        'ajaxDeleteUrl':             '/admin/screens/ajax-delete',
        'ajaxGetSettingsUrl':        '/admin/screens/ajax-get-settings',
        'ajaxSaveSettingsFormUrl':   '/admin/screens/ajax-save-settings-form',
        'ajaxReorderUrl':            '/admin/screens/ajax-reorder',
        'ajaxMoveScreensUrl':        '/admin/screens/ajax-move-screens',
        'ajaxGetThumbsUrl':          '/admin/screens/ajax-get-thumbs',
        'ajaxGetScreensSliderUrl':   '/admin/versions/ajax-get-screens-slider',

        'hotspotsViewSettings':  {},
        'commentsViewSettings':  {},
        'versionViewSettings':   {},
        'screenFitViewSettings': {}
    };

    this.settings = $.extend({}, defaults, data);

    // commonly used "static" selectors
    this.$screensWrapperTabs = $(this.settings.screensWrapperTabs);
    this.$bulkPanel          = $(this.settings.bulkPanel);
    this.$bulkVersionsSelect = $(this.settings.bulkVersionsSelect);
    this.$uploadContainer    = $(this.settings.uploadContainer);
    this.$uploadPopup        = $(this.settings.uploadPopup);
    this.$editPopup          = $(this.settings.editPopup);

    // cache helpers
    this.generalXHR           = null;
    this.updateXHR            = null;
    this.reorderXHR           = null;
    this.deleteXHR            = null;
    this.pressedKey           = null;
    this.$activeVersionSlider = null;

    this.hotspotsView  = new ScreenHotspotsView(this.settings.hotspotsViewSettings);
    this.commentsView  = new ScreenCommentsView(this.settings.commentsViewSettings);
    this.versionView   = new VersionView(this.settings.versionViewSettings);
    this.screenFitView = new ScreenFitView(this.settings.screenFitViewSettings);

    this.init();
};

/**
 * Init method
 */
ScreenView.prototype.init = function () {
    var self = this;

    if (typeof Dropzone !== 'undefined') {
        Dropzone.autoDiscover = false;
    }

    var $document = $(document);
    var $body     = $('body');

    self.initScreensDropzone();

    self.initSortable();

    // Screen delete
    $document.on('click.pr.screenView', self.settings.screenDeleteHandle, function (e) {
        e.preventDefault();

        if (window.confirm(self.settings.confirmDeleteText)) {
            self.deleteScreen($(this).closest(self.settings.screenItem).data('screen-id'));
        }
    });

    // Bulk selection
    $document.on('change', self.settings.bulkInput, function (e) {
        if ($(this).is(':checked')) {
            $(this).closest(self.settings.screenItem).addClass('bulk-selected');
        } else {
            $(this).closest(self.settings.screenItem).removeClass('bulk-selected');
        }

        self.toggleBulkPanel();
    });

    // Bulk shortcut select
    $document.on('click', self.settings.screenItem, function (e) {
        if (self.pressedKey == PR.keys.shift) {
            e.preventDefault();
            e.stopPropagation();

            if ($(this).hasClass('bulk-selected')) {
                $(this).find(self.settings.bulkInput).prop('checked', false).trigger('change');
            } else {
                $(this).find(self.settings.bulkInput).prop('checked', true).trigger('change');
            }
        }
    });

    // Reset bulk selection
    $document.on('click.pr.screenView', self.settings.bulkReset, function (e) {
        e.preventDefault();

        self.resetBulkSelection();
    });

    // Bulk delete
    $document.on('click.pr.screenView', self.settings.bulkDeleteHandle, function (e) {
        e.preventDefault();

        if (window.confirm(self.settings.confirmBulkDeleteText)) {
            self.deleteScreen(self.getBulkSelectedIds());
        }
    });

    // Bulk screens move
    self.$bulkVersionsSelect.on('change', function () {
        self.moveScreensToVersion(self.getBulkSelectedIds(), $(this).val());
    });

    // Versions manipulation event handlers
    self.$screensWrapperTabs.on('tabChange.pr', function (e, tabContentId) {
        self.resetBulkSelection();
    });

    $document.on('versionCreated', function () {
        self.initSortable();
        self.resetBulkSelection();
    });

    $document.on('versionDeleted', function () {
        self.resetBulkSelection();
    });

    // Open screen edit container
    $document.on('click', self.settings.screenItem + ' ' + self.settings.openHandle, function (e) {
        e.preventDefault();

        if (self.pressedKey == PR.keys.shift) {
            // bulk selection is active
            return;
        }

        var $screenItem = $(this).closest(self.settings.screenItem);
        self.showScreensSlider(
            $screenItem.closest(self.settings.screensWrapper).data('version-id'),
            $screenItem.data('screen-id')
        );
    });

    // Close screen edit container
    $document.on('click', self.settings.closeHandle, function (e) {
        e.preventDefault();

        self.hideScreensSlider();
    });

    // Slider keyboard nav
    $document.on('keydown', function (e) {
        self.pressedKey = e.which;

        if (self.$activeVersionSlider &&
            self.$activeVersionSlider.length &&
            !$body.hasClass('popup-active') &&
            !$body.hasClass('hotspot-active') &&
            !$body.hasClass('comment-active')
        ) {
            if (e.which == PR.keys.left) {
                e.preventDefault();
                self.$activeVersionSlider.slider('goTo', 'prev');
            } else if (e.which == PR.keys.right) {
                e.preventDefault();
                self.$activeVersionSlider.slider('goTo', 'next');
            }
        }
    });
    $document.on('keyup', function (e) {
        self.pressedKey = null;
    });

    // Custom slider nav
    $document.on('click', self.settings.nextSlideHandle, function (e) {
        e.preventDefault();
        $(self.settings.versionSlider).slider('goTo', 'next');
    });
    $document.on('click', self.settings.prevSlideHandle, function (e) {
        e.preventDefault();
        $(self.settings.versionSlider).slider('goTo', 'prev');
    });

    // Screen alignment
    $document.on('sliderChange sliderInit', function (e, $activeSlide) {
        PR.horizontalAlign($activeSlide);

        self.updateSliderCaption($activeSlide);

        self.hotspotsView.deselectHotspot();
        self.commentsView.deselectCommentTarget();
        self.commentsView.updateCommentsCounter();
        self.commentsView.updateResolvedCommentsCounter();
        self.commentsView.checkResolvedCommentsToggle();
    });

    // Screen settings
    $document.on('click', self.settings.settingsHandle, function (e) {
        e.preventDefault();

        if (self.$activeVersionSlider && self.$activeVersionSlider.length) {
            self.openScreenSettingsPopup(self.$activeVersionSlider.slider('getActive').data('screen-id'));
        }
    });

    // Reset edit popup content
    self.$editPopup.on('popupClose', function () {
        $(this).find('.content').children().remove(); // removes also the binded events to children elements
    });

    // Preview mode handle
    $document.on('click', self.settings.previewModeHandle, function (e) {
        e.preventDefault();

        self.activatePreviewMode();
    });

    // Hotspots mode handle
    $document.on('click', self.settings.hotspotsModeHandle, function (e) {
        e.preventDefault();

        self.activateHotspotsMode();
    });

    // Comments mode handle
    $document.on('click', self.settings.commentsModeHandle, function (e) {
        e.preventDefault();

        self.activateCommentsMode();
    });

    // Hotspots navigation
    $document.on('click', '.hotspot', function(e) {
        if ($body.hasClass('preview-mode')) {
            e.preventDefault();
            e.stopPropagation();

            var link       = $(this).data('link');
            var transition = $(this).data('transition');

            if (!isNaN(link)) {
                var $slider = $(self.settings.versionSlider);
                if ($slider.length) {
                    $slider.slider(
                        'goTo',
                        $slider.find(self.settings.versionSliderItem + '[data-screen-id="' + link + '"]').index(),
                        true,
                        transition
                    );
                }
            } else if (PR.isValidUrl(link)) {
                window.open(PR.htmlDecode(link), '_blank');
            }
        }
    });

    // Preview mode hints
    $document.on('click', self.settings.versionSliderItem, function(e) {
        if ($body.hasClass('preview-mode')) {
            e.preventDefault();

            $body.addClass('preview-mode-hint').stop(true, true).delay(500).queue(function(next) {
                $body.removeClass('preview-mode-hint');
                next();
            });
        }
    });

    // Keyboard shortcut to toggle hotspots visibility
    $document.on('keydown', function(e) {
        if (
            e.shiftKey &&
            e.which === PR.keys.h &&
            $body.hasClass('preview-mode')
        ) {
            e.preventDefault();

            $body.toggleClass('hotspots-force-show');
        }
    });

    // auto open screen edit based on query param
    var params = yii.getQueryParams(window.location.href);
    if (params.screen) {
        // clean the query params from the url
        if (history.pushState) {
            var cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.pushState({path: cleanUrl},'', cleanUrl);
        }

        PR.showLoader();
        var $screenItem = $(self.settings.screenItem + '[data-screen-id="' + params.screen + '"]');

        self.showScreensSlider(
            $screenItem.closest(self.settings.screensWrapper).data('version-id'),
            params.screen,
            function (response) {
                if (response.success && params.comment_target) {
                    self.activateCommentsMode();
                    self.commentsView.selectCommentTarget(
                        $(self.commentsView.settings.commentTarget + '[data-comment-id="' + params.comment_target + '"]'),
                        params.reply_to
                    );
                }
            }
        );
    }
};

/**
 * Returns screen item element by its id.
 * @param {Number} screenId
 * @return {jQuery}
 */
ScreenView.prototype.getScreenItem = function (screenId) {
    return $(this.settings.screenItem + '[data-screen-id="' + screenId + '"]');
};

/**
 * Returns the current active version div container.
 * @return {jQuery}
 */
ScreenView.prototype.getActiveScreensWrapper = function () {
    return $(this.settings.screensWrapper + '.active');
};


/**
 * Populates the bulk versions select with the latest project versions data.
 */
ScreenView.prototype.populateBulkVersionsSelect = function () {
    var self = this;

    // append select prompt option
    if (self.$bulkVersionsSelect.data('prompt-option')) {
        self.$bulkVersionsSelect.html('<option disabled selected>' + self.$bulkVersionsSelect.data('prompt-option') + '</option>');
    }

    if ($(self.settings.screensWrapper).length > 1) {
        self.$bulkVersionsSelect.prop('disabled', false);

        // append version options
        $(self.settings.screensWrapper).each(function(i, container) {
            // skip the current one
            if ($(container).hasClass('active')) {
                return true;
            }

            var versionId    = $(container).data('version-id');
            var versionTitle = $(container).data('version-title') || (self.settings.versionOptionText + ' ' + (i + 1));

            self.$bulkVersionsSelect.append('<option value="' + versionId + '" data-version-id="' + versionId + '">' + versionTitle + '</option>');
        });
    } else {
        self.$bulkVersionsSelect.prop('disabled', true);
    }
}

/**
 * Handles screens bulk panel visibility.
 */
ScreenView.prototype.toggleBulkPanel = function () {
    var self = this;

    if (self.getActiveScreensWrapper().find(self.settings.bulkInput + ':checked').length) {
        self.$bulkPanel.stop(true, true).slideDown(300);
        self.populateBulkVersionsSelect();
    } else {
        self.$bulkPanel.stop(true, true).slideUp(300);
    }
};

/**
 * Returns array list with the bulk selected screen ids.
 * @return {Array}
 */
ScreenView.prototype.getBulkSelectedIds = function () {
    var result = [];

    this.getActiveScreensWrapper().find(this.settings.screenItem + '.bulk-selected').each(function(i, item) {
        result.push($(item).data('screen-id'));
    });

    return result;
};

/**
 * Resets currently bulk selected screens.
 */
ScreenView.prototype.resetBulkSelection = function () {
    var $activeScreensWrapper = this.getActiveScreensWrapper();

    $activeScreensWrapper.find(this.settings.bulkInput + ':checked').prop('checked', false);
    $activeScreensWrapper.find(this.settings.screenItem + '.bulk-selected').removeClass('bulk-selected');

    this.toggleBulkPanel();
};

/**
 * Moves screens from one version to another via ajax.
 * @param {Array}  screenIds Ids of the screens to move.
 * @param {Number} versionId Id of the new version.
 */
ScreenView.prototype.moveScreensToVersion = function (screenIds, versionId) {
    var self = this;

    if (!versionId) {
        console.warn('Moving failed! Missing version id.');
        return;
    }

    if (!PR.isArray(screenIds)) {
        screenIds = [screenIds];
    }

    if (!screenIds.length) {
        return; // nothing to move
    }

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url: self.settings.ajaxMoveScreensUrl,
        type: 'POST',
        data: {
            'screenIds': screenIds,
            'versionId': versionId,
        }
    }).done(function(response) {
        self.resetBulkSelection();

        if (response.success) {
            var $screensWrapper = $(self.settings.screensWrapper + '[data-version-id="' + versionId + '"]');

            var $screens = $();
            for (var i = 0; i < screenIds.length; i++) {
                $screens = $screens.add(self.settings.screenItem + '[data-screen-id="' + screenIds[i] + '"]');
            }

            // move screens
            self.insertScreens($screens, $screensWrapper);

            // show the targeted version
            self.versionView.activateVersion(versionId);
        }
    });
};

/**
 * Handles screens Dropzone upload initialization.
 */
ScreenView.prototype.initScreensDropzone = function () {
    var self = this;

    if (typeof Dropzone === 'undefined') {
        console.warn("Dropzone is missing or is not initialized yet.");
        return;
    }

    var myDropzone = new Dropzone(self.settings.uploadContainer, {
        url:                   self.settings.ajaxUploadUrl,
        paramName:             'ScreensUploadForm[images]',
        parallelUploads:       5,
        uploadMultiple:        true,
        thumbnailWidth:        null,
        thumbnailHeight:       null,
        addRemoveLinkss:       false,
        createImageThumbnails: false,
        previewTemplate:       '<div style="display: none"></div>',
        acceptedFiles:         '.jpg, .jpeg, .png',
        maxFilesize:           self.settings.maxUploadSize
    });

    var $activeScreensWrapper = $();

    myDropzone.on('sending', function (file, xhr, formData) {
        $activeScreensWrapper = self.getActiveScreensWrapper();

        formData.append(yii.getCsrfParam(), yii.getCsrfToken());
        formData.append('versionId', $activeScreensWrapper.data('version-id'));

        self.$uploadContainer.addClass('loading');
        self.$uploadPopup.find('.popup-close').hide();
    });

    myDropzone.on('error', function (file, errorMessage) {
        PR.addNotification('An error occured while uploading "' + file.name + '".', 'danger');
    });

    myDropzone.on('successmultiple', function (files, response) {
        if (response.success) {
            self.insertScreens(response.listItemsHtml, $activeScreensWrapper);
        }

        PR.addNotification(response.message, response.success ? 'success' : 'danger');
    });

    myDropzone.on('queuecomplete', function (files) {
        PR.closePopup(self.$uploadPopup);

        self.$uploadContainer.removeClass('loading');
    });

    self.$uploadPopup.on('popupClose', function () {
        self.$uploadPopup.find('.popup-close').show();
    });
};

/**
 * Handles single image screen replace Dropzone upload initialization.
 * @param {Number} Screen id       Id of the screen to replace.
 * @param {String} uploadContainer Upload container selector.
 */
ScreenView.prototype.initReplaceScreenImageDropzone = function (screenId, uploadContainer) {
    var self = this;

    if (typeof Dropzone === 'undefined') {
        console.warn("Dropzone is missing or is not initialized yet.");
        return;
    }

    screenId        = screenId        || (self.$activeVersionSlider && self.$activeVersionSlider.slider('getActive').data('screen-id'));
    uploadContainer = uploadContainer || self.settings.replaceScreenImageContainer;

    var $uploadContainer = $(uploadContainer);
    var $uploadPopup     = $uploadContainer.closest('.popup');


    var myDropzone = new Dropzone(uploadContainer, {
        url:                   self.settings.ajaxReplaceScreenImageUrl,
        paramName:             'ScreenReplaceForm[image]',
        parallelUploads:       1,
        uploadMultiple:        false,
        thumbnailWidth:        null,
        thumbnailHeight:       null,
        addRemoveLinkss:       false,
        createImageThumbnails: false,
        previewTemplate:       '<div style="display: none"></div>',
        acceptedFiles:         '.jpg, .jpeg, .png',
        maxFilesize:           self.settings.maxUploadSize
    });

    myDropzone.on('addedfile', function (file) {
        if (!window.confirm(self.settings.replaceImageConfirmText)) {
            myDropzone.removeAllFiles();
        }
    });

    myDropzone.on('sending', function (file, xhr, formData) {
        formData.append(yii.getCsrfParam(), yii.getCsrfToken());
        formData.append('screenId', screenId);

        $uploadContainer.addClass('loading');
        $uploadPopup.find('.popup-close').hide();
    });

    myDropzone.on('error', function (file, errorMessage) {
        PR.addNotification('An error occured while uploading "' + file.name + '".', 'danger');
    });

    myDropzone.on('success', function (file, response) {
        if (response.success) {
            PR.closePopup($uploadPopup);

            // replace scren image placeholders
            if (response.screen) {
                $('[data-screen-id="' + screenId + '"]')
                    .find(self.settings.screenImgHolder)
                    .attr('src', response.screen.imageUrl)
                    .data('src', response.screen.imageUrl);

                self.updateScreenTitle(screenId, response.screen.title);

                self.updateSliderCaption();

                // reload screen slider
                self.showScreensSlider(
                    self.getActiveScreensWrapper().data('version-id'),
                    screenId
                );
            }
        }

        PR.addNotification(response.message, response.success ? 'success' : 'danger');
    });

    myDropzone.on('queuecomplete', function (files) {
        $uploadPopup.find('.popup-close').show();

        $uploadContainer.removeClass('loading');
    });
};

/**
 * Handles screen elements insertion.
 * @param  {String|Object} screens
 * @param  {String|Object} container
 */
ScreenView.prototype.insertScreens = function (screens, container) {
    var self       = this;
    var $container = $(container || self.getActiveScreensWrapper());

    var $screens = $(screens);
    // $container.find(this.settings.screenUploadHandle).before(screens);
    $container.append($screens);

    $screens.filter(self.settings.screenItem).each(function(i, screen) {
        self.loadThumb(screen);
    });

    if ($container.find(this.settings.screenItem).length) {
        $('#global_wrapper').stop(true, true).animate({
            'scrollTop': $('#global_wrapper').get(0).scrollHeight
        }, 300);
    }
};

/**
 * Load (and generate if doesn't exist) screen thumb via ajax.
 * @param  {String|Object} screen
 * @param  {String}        thumbSize
 */
ScreenView.prototype.loadThumb = function (screen, thumbSize) {
    thumbSize = thumbSize || 'medium';

    var self    = this;
    var $screen = $(screen);
    var $img    = $screen.find('.img').hide();

    if (!$screen.length || !$screen.data('screen-id') || !$img.length) {
        console.warn('Missing screen item!');
        return;
    }

    PR.AUTO_LOADER = false;


    $.ajax({
        url: self.settings.ajaxGetThumbsUrl,
        type: 'GET',
        data: {
            'id': $screen.data('screen-id') || '',
        },
    }).done(function(response) {
        if (response.success && response.thumbs && response.thumbs[thumbSize]) {
            $img.show().addClass('lazy-load').attr('data-src', response.thumbs[thumbSize]);
        }
    });
};

/**
 * Deletes screen item via ajax.
 * @param {Number|Array} screenId
 */
ScreenView.prototype.deleteScreen = function (screenId) {
    var self = this;

    PR.abortXhr(self.deleteXHR);
    self.deleteXHR = $.ajax({
        url: self.settings.ajaxDeleteUrl,
        type: 'POST',
        data: {
            'id': screenId,
        },
    }).done(function(response) {
        if (response.success) {
            var ids = [];
            if (PR.isArray(screenId)) {
                ids = screenId;
            } else {
                ids = [screenId];
            }

            for (var i = 0; i < ids.length; i++) {
                self.getScreenItem(ids[i]).remove();
            }

            self.toggleBulkPanel();
        }
    });
};

/**
 * Persists screen position change via ajax.
 * @param {Number} screenId
 * @param {Number} position
 */
ScreenView.prototype.reorderScreen = function (screenId, position) {
    var self = this;

    PR.abortXhr(self.reorderXHR);
    self.reorderXHR = $.ajax({
        url: self.settings.ajaxReorderUrl,
        type: 'POST',
        data: {
            'id': screenId,
            'position': position
        },
    });
};

/**
 * Screen items sortable initialization.
 */
ScreenView.prototype.initSortable = function () {
    var self = this;

    var oldIndex = 0;

    $(self.settings.screensWrapper).sortable({
        revert: false,
        helper: 'clone',
        distance: 10,
        containment: 'parent',
        tolerance: 'pointer',
        placeholder: 'box sortable-placeholder',
        items: '.box:not(.disable-sort)',
        start: function(event, ui) {
            oldIndex = ui.item.index();
            ui.item.parent().append(ui.item); // fix css grid layout while dragging
        },
        stop: function(event, ui) {
            if (oldIndex != ui.item.index()) {
                self.reorderScreen(ui.item.data('screen-id'), ui.item.index());
            }
        }
    });
};


/* Screens slider / Screen edit
------------------------------------------------------------------- */
/**
 * Loads version screens slider.
 * @params {Number}   versionId
 * @params {Number}   screenId
 * @params {Function} callback
 */
ScreenView.prototype.showScreensSlider = function (versionId, screenId, callback) {
    var self = this;

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url: self.settings.ajaxGetScreensSliderUrl,
        type: 'GET',
        data: {
            'versionId': versionId,
            'screenId':  screenId
        },
    }).done(function(response) {
        if (response.success) {
            var $body = $('body');

            $(self.settings.versionSlider).remove(); // remove previously generated sliders

            $body.addClass('screen-edit-active')
                .append(response.screensSliderHtml);

            self.$activeVersionSlider = $(self.settings.versionSlider).first();
            self.$activeVersionSlider.slider({nav: false});

            self.$activeVersionSlider.find(self.settings.versionSliderItem).on('scroll', function (e) {
                if ($body.hasClass('hotspot-active')) {
                    self.hotspotsView.repositionPopover();
                    // self.hotspotsView.deselectHotspot();
                } else if ($body.hasClass('comment-active')) {
                    self.commentsView.repositionPopover();
                    // self.commentsView.deselectCommentTarget();
                }
            });

            // updates container width to prevent displaying unnecessary horizontal scrollbar
            if (!self.$activeVersionSlider.hasClass('desktop')) {
                self.$activeVersionSlider.find('.hotspot-layer').on('load', function (e) {
                    PR.updateScrollContainerWidth(this, $(this).closest(self.settings.versionSliderItem))
                });
            }

            self.activateHotspotsMode();

            if (PR.isFunction(callback)) {
                callback(response);
            }
        }
    });
};

/**
 * Closes screen edit container.
 */
ScreenView.prototype.hideScreensSlider = function () {
    var self = this;

    $('body').removeClass('screen-edit-active hotspot-active comment-active hotspots-mode comments-mode preview-mode');
    $(self.settings.versionSlider).addClass('close-anim').delay(400).queue(function(next) {
        $(this).remove();

        self.$activeVersionSlider = null;
        next();
    });
};

/**
 * Fetches screen settings form via ajax and open a popup with it.
 * @param {Number} screenId
 */
ScreenView.prototype.openScreenSettingsPopup = function (screenId) {
    var self = this;

    if (!screenId) {
        console.warn('Missing screen id!');
        return;
    }

    PR.abortXhr(self.updateXHR);
    self.updateXHR = $.ajax({
        url: self.settings.ajaxGetSettingsUrl,
        type: 'GET',
        data: {
            'id': screenId
        }
    }).done(function(response) {
        if (response.success && response.settingsHtml) {
            self.$editPopup.find('.content').html(response.settingsHtml);
            PR.openPopup(self.$editPopup);

            self.$editPopup.find('.tabs').tabs();

            self.initReplaceScreenImageDropzone(screenId);

            self.$editPopup.find('form').on('beforeSubmit', function (e) {
                e.preventDefault();

                self.saveSettingsForm(this, screenId);

                return false;
            })
        }
    });
};

/**
 * Handles screen settings form submit via ajax.
 * @param {Mixed} form
 * @param {Number} screenId
 */
ScreenView.prototype.saveSettingsForm = function (form, screenId) {
    var self = this;

    var $form = $(form);

    if (!$form.length || !screenId) {
        return;
    }

    PR.AUTO_LOADER = false;

    PR.abortXhr(self.updateXHR);
    self.updateXHR = $.ajax({
        url: self.settings.ajaxSaveSettingsFormUrl + '?id=' + screenId,
        type: 'POST',
        data: $form.serialize()
    }).done(function(response) {
        if (response.success) {
            setTimeout(function() {
                PR.closePopup();
            }, 580); // animations delay

            var $sliderItem = $(self.settings.versionSliderItem + '[data-screen-id="' + screenId + '"]');

            self.updateScreenTitle(screenId, response.settings.title);

            self.updateSliderCaption($sliderItem);

            // update alignment
            PR.setData($sliderItem, 'alignment', response.settings.alignment);
            PR.horizontalAlign($sliderItem);

            // update background
            $sliderItem.css('background', response.settings.background || '#eff2f8');
        }
    });
};

/**
 * Updates screen title across all placeholders.
 * @param {Number} screenId
 * @param {String} newTitle
 */
ScreenView.prototype.updateScreenTitle = function (screenId, newTitle) {
    PR.setData('[data-screen-id="' + screenId + '"]', 'title', PR.htmlEncode(newTitle));

    $('[data-screen-id="' + screenId + '"]').find(this.settings.screenTitlteHolder)
        .attr('title', PR.htmlEncode(newTitle))
        .text(newTitle);
};

/**
 * Updates active slide info.
 * @param {Null|jQuery} [$activeSlide]
 */
ScreenView.prototype.updateSliderCaption = function ($activeSlide) {
    $activeSlide = $activeSlide || $();
    if (!$activeSlide.length && this.$activeVersionSlider) {
        $activeSlide = this.$activeVersionSlider.slider('getActive');
    }

    var title = $activeSlide.data('title') || '';

    $(this.settings.activeSlideTitleHolder).text(title).attr('title', title);
    $(this.settings.activeSlideOrderHolder).text($activeSlide.index() + 1);
};

/**
 * Activates hotspot preview mode.
 */
ScreenView.prototype.activatePreviewMode = function () {
    $('body').addClass('preview-mode').removeClass('comments-mode hotspots-mode');
    $(this.settings.previewModeHandle).addClass('active');
    $(this.settings.hotspotsModeHandle).removeClass('active');
    $(this.settings.commentsModeHandle).removeClass('active');
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip', '');
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip-class', '');

    this.hotspotsView.disable();
    this.commentsView.disable();
};

/**
 * Activates hotspots edit mode.
 */
ScreenView.prototype.activateHotspotsMode = function () {
    $('body').addClass('hotspots-mode').removeClass('comments-mode preview-mode');
    $(this.settings.hotspotsModeHandle).addClass('active');
    $(this.settings.commentsModeHandle).removeClass('active');
    $(this.settings.previewModeHandle).removeClass('active');
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip', this.settings.hotspotsTooltipText);
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip-class', 'hotspots-mode-tooltip');

    this.hotspotsView.enable();
    this.commentsView.disable();
};

/**
 * Activates comments edit mode.
 */
ScreenView.prototype.activateCommentsMode = function () {
    $('body').removeClass('hotspots-mode').addClass('comments-mode preview-mode');
    $(this.settings.hotspotsModeHandle).removeClass('active');
    $(this.settings.commentsModeHandle).addClass('active');
    $(this.settings.previewModeHandle).removeClass('active');
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip', this.settings.commentsTooltipText);
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip-class', 'comments-mode-tooltip');

    this.hotspotsView.disable();
    this.commentsView.enable();
};
