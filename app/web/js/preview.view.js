var PreviewView = function(data) {
    data = data || {};

    var defaults = {
        'grantedAccess': false,

        'accessForm':        '#project_access_form',
        'accessFormWrapper': '#access_form_wrapper',
        'previewWrapper':    '#preview_wrapper',

        // dynamic preview selectors
        'versionSlider':          '.version-slider',
        'versionSliderItem':      '.slider-item',
        'versionSliderCaption':   '.slider-caption',
        'versionsSelect':         '.versions-select',
        'hotspot':                '.hotspot',
        'activeSlideTitleHolder': '.active-slide-title',
        'activeSlideOrderHolder': '.active-slide-order',

        // control panel
        'controlPanel':           '.version-slider-panel',
        'contorlPanelToggle':     '#panel_toggle_handle',
        'previewThumbsContainer': '#preview_thumbs_container',
        'previewThumb':           '.preview-thumb',
        'previewThumbsHandle':    '#panel_screens_handle',
        'previewModeHandle':      '#panel_preview_handle',
        'commentsModeHandle':     '#panel_comments_handle',
        'nextSlideHandle':        '#slider_next_handle',
        'prevSlideHandle':        '#slider_prev_handle',

        // screen comments
        'ajaxCommentCreateUrl': '/screen-comments/ajax-create',
        'ajaxCommentReplyUrl':  '/screen-comments/ajax-reply',
        'ajaxCommentDeleteUrl': '/screen-comments/ajax-delete',
        'ajaxCommentsListUrl':  '/screen-comments/ajax-get-comments',

        // ajax urls
        'ajaxInvokeAccessUrl': '',

        // texts
        'commentsTooltipText': 'Click to leave a comment'
    };

    this.settings = $.extend({}, defaults, data);

    // cached selectors
    this.$document          = $(document);
    this.$body              = $('body');
    this.$accessForm        = $(this.settings.accessForm);
    this.$accessFormWrapper = $(this.settings.accessFormWrapper);
    this.$previewWrapper    = $(this.settings.previewWrapper);

    this.generalXHR = null;

    this.commentsView = new ScreenCommentsView({
        'ajaxCommentCreateUrl': this.settings.ajaxCommentCreateUrl,
        'ajaxCommentReplyUrl':  this.settings.ajaxCommentReplyUrl,
        'ajaxCommentDeleteUrl': this.settings.ajaxCommentDeleteUrl,
        'ajaxCommentsListUrl':  this.settings.ajaxCommentsListUrl,
    });

    this.versionHashPattern  = /^v\d+$/;
    this.screenHashPattern   = /^s\d+$/;
    this.combinedHashPattern = /^v\d+-s\d+$/;

    this.init();
};

/**
 * Init method
 */
PreviewView.prototype.init = function() {
    var self = this;

    if (self.settings.grantedAccess) {
        self.invokeAccess();
    }

    self.$accessForm.on('beforeSubmit.yii', function(e) {
        self.invokeAccess();

        return false;
    });

    // keyboard shortcuts
    self.$document.on('keydown', function(e) {
        if (e.which === PR.keys.esc) {
            e.preventDefault();

            self.hidePreviewThumbs();
        } else if (!self.$body.hasClass('comment-active')) {
            if (e.which === PR.keys.left) {
                e.preventDefault();

                var $slider = $(self.settings.versionSlider);
                if ($slider.length) {
                    $slider.slider('goTo', 'prev');
                }
            } else if (e.which === PR.keys.right) {
                e.preventDefault();

                var $slider = $(self.settings.versionSlider);
                if ($slider.length) {
                    $slider.slider('goTo', 'next');
                }
            }
        }
    });

    // Versions select
    self.$document.on('change', self.settings.versionsSelect, function(e) {
        self.invokeAccess($(this).find('option:selected').index() + 1, 1);
    });

    // Control panel visibility toggle
    self.$document.on('click', self.settings.contorlPanelToggle, function(e) {
        e.preventDefault();

        self.toggleControlPanel();
    });

    // Preview mode handle
    self.$document.on('click', self.settings.previewModeHandle, function(e) {
        e.preventDefault();

        self.activatePreviewMode();
    });

    // Comments mode handle
    self.$document.on('click', self.settings.commentsModeHandle, function(e) {
        e.preventDefault();

        self.activateCommentsMode();
    });

    // Hotspots navigation
    self.$document.on('click', self.settings.hotspot, function(e) {
        e.preventDefault();
        e.stopPropagation();

        var link = $(this).data('link');

        if (!isNaN(link)) {
            var $slider = $(self.settings.versionSlider);
            if ($slider.length) {
                $slider.slider('goTo', $slider.find(self.settings.versionSliderItem + '[data-screen-id="' + link + '"]').index());
            }
        } else if (PR.isValidUrl(link)) {
            window.open(PR.htmlDecode(link),'_blank');
        }
    });

    // Preview mode hints
    self.$document.on('click', self.settings.versionSliderItem, function(e) {
        if (self.$body.hasClass('preview-mode')) {
            e.preventDefault();

            self.$body.addClass('preview-mode-hint').stop(true, true).delay(500).queue(function(next) {
                self.$body.removeClass('preview-mode-hint');
                next();
            });
        }
    });

    // Keyboard shortcut to toggle hotspots visibility
    self.$document.on('keydown', function(e) {
        if (e.shiftKey &&
            e.which === PR.keys.h &&
            self.$body.hasClass('preview-mode')
        ) {
            e.preventDefault();

            self.$body.toggleClass('hotspots-force-show');
        }
    });

    // Show preview thumb screens
    self.$document.on('click', self.settings.previewThumbsHandle, function(e) {
        e.preventDefault();

        self.togglePreviewThumbs();
    });

    // Change slide on preview thumb click
    self.$document.on('click', self.settings.previewThumb, function(e) {
        e.preventDefault();

        $(self.settings.versionSlider).slider('goTo', $(this).index());
    });

    // Custom slider nav
    self.$document.on('click', self.settings.nextSlideHandle, function(e) {
        e.preventDefault();

        $(self.settings.versionSlider).slider('goTo', 'next');
    });
    self.$document.on('click', self.settings.prevSlideHandle, function(e) {
        e.preventDefault();

        $(self.settings.versionSlider).slider('goTo', 'prev');
    });
};

/**
 * Gets mapped navigation hash props.
 * @return {Object}
 */
PreviewView.prototype.getHashNav = function() {
    var hash       = window.location.hash;
    var versionPos = null;
    var screenPos  = null;

    if (!hash.length) {
        return {};
    }

    hash = hash.substr(1); // remove leading #

    if (this.combinedHashPattern.test(hash)) {
        var hashSplit = hash.split('-');
        versionPos    = hashSplit[0].substr(1) << 0;
        screenPos     = hashSplit[1].substr(1) << 0;
    } else if (this.versionHashPattern.test(hash)) {
        versionPos = hash.substr(1) << 0;
    } else if (this.screenHashPattern.test(hash)) {
        screenPos = hash.substr(1) << 0;
    }

    return {
        'versionPos': versionPos,
        'screenPos':  screenPos,
    };
};

/**
 * Sets mapped navigation hash props.
 * @param integer versionPos Version position counter
 * @param integer screenPos  Screen position counter
 */
PreviewView.prototype.setHashNav = function(versionPos, screenPos) {
    var hash = '';

    if (versionPos) {
        hash += ('v' + versionPos || 1);
    }

    if (screenPos) {
        if (hash) {
            hash += '-';
        }

        hash += ('s' + screenPos);
    }

    window.location.hash = hash;
};

/**
 * Updates slider caption content according to the current active slide.
 */
PreviewView.prototype.updateSliderCaption = function() {
    var self         = this;
    var $caption     = $(self.settings.versionSliderCaption);
    var $activeSlide = $(self.settings.versionSliderItem + '.active');

    $caption.addClass('active');
    $(self.settings.activeSlideTitleHolder).html($activeSlide.data('title'));
    $(self.settings.activeSlideOrderHolder).html($activeSlide.index() + 1);

    if (self.sliderCaptionTimeout) {
        clearTimeout(self.sliderCaptionTimeout);
    }
    self.sliderCaptionTimeout = setTimeout(function() {
        $caption.removeClass('active');
    }, 2000);
};

/**
 * Sends project invoke access request.
 * @param {Number}   versionPos Position of the version to load (leave empty to auto load the latest active one)
 * @param {Number}   ScreenPos  Position of the screen to select (leave empty to select the first one)
 * @param {Function} callback
 */
PreviewView.prototype.invokeAccess = function(versionPos, screenPos, callback) {
    var self       = this;
    var mappedHash = self.getHashNav();

    versionPos = versionPos || mappedHash.versionPos || 0;
    screenPos  = screenPos  || mappedHash.screenPos  || 1;

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url: (self.settings.ajaxInvokeAccessUrl || self.$accessForm.attr('action')) + '?version_pos=' + (versionPos - 1),
        type: 'POST',
        data: self.$accessForm.serialize()
    }).done(function(response) {
        if (response.success) {
            // ensure that prev child elems and their events are removed
            self.$previewWrapper.children().remove();

            // append the new content
            self.$previewWrapper.html(response.previewHtml);

            var $slider                = $(self.settings.versionSlider);
            var activeVersionPos       = ($(self.settings.versionsSelect).find('option:selected').index() || 0) + 1;
            var $previewThumbContainer = $(self.settings.previewThumbsContainer)
            var isPreviewThumbVisible  = false;
            var previewThumbScroll     = null;
            var $previewThumb;

            $slider.on('sliderChangeBefore', function(e, $activeSlide) {
                // update active preview thumb on slider change
                $previewThumb = $(self.settings.previewThumb).removeClass('active')
                    .filter('[data-screen-id="' + $activeSlide.data('screen-id') + '"]').addClass('active');

                isPreviewThumbVisible = $previewThumb.is(':visible');
                previewThumbScroll    = null; // reset

                if (!$previewThumb.length) {
                    return;
                }

                if (!isPreviewThumbVisible) {
                    $previewThumbContainer.show(); // show the preview container to properly get thumb position
                }

                // calculate scroll position
                if ($previewThumb.position().left + $previewThumb.width() > self.$document.width()) {
                    previewThumbScroll = $previewThumbContainer.scrollLeft() + $previewThumb.position().left + $previewThumb.width() - self.$document.width() + 15;
                } else if ($previewThumb.position().left < 0) {
                    previewThumbScroll = $previewThumbContainer.scrollLeft() + $previewThumb.position().left - 15;
                }

                // perform scroll (if needed)
                if (previewThumbScroll !== null) {
                    if (isPreviewThumbVisible) {
                        $previewThumbContainer.stop(true, true).animate({'scrollLeft': previewThumbScroll}, 300);
                    } else {
                        $previewThumbContainer.scrollLeft(previewThumbScroll);
                    }
                }

                if (!isPreviewThumbVisible) {
                    $previewThumbContainer.hide(); // revert changes
                }
            });

            $slider.on('sliderChange sliderInit', function(e, $activeSlide) {
                PR.horizontalAlign($activeSlide);

                self.commentsView.deselectCommentTarget();

                self.commentsView.updateCommentsCounter();

                self.setHashNav(activeVersionPos, $activeSlide.index() + 1);

                self.updateSliderCaption();
            });

            $slider.slider({nav: false});


            $slider.find(self.settings.versionSliderItem).on('scroll', function(e) {
                if (self.$body.hasClass('comment-active')) {
                    self.commentsView.deselectCommentTarget();
                }
            });

            // updates container width to prevent displaying unnecessary horizontal scrollbar
            if (!$slider.hasClass('desktop')) {
                $slider.find('.hotspot-layer').on('load', function(e) {
                    PR.updateScrollContainerWidth(this, $(this).closest(self.settings.versionSliderItem))
                });
            }

            self.activatePreviewMode();

            if (screenPos > 1) {
                $slider.slider('goTo', screenPos - 1, false);
            }

            self.$accessFormWrapper.removeClass('active').addClass('inactive');
            self.$previewWrapper.removeClass('inactive').addClass('active');
        } else if (response.errors) {
            $.each(response.errors, function(name, errors) {
                self.$accessForm.yiiActiveForm('updateAttribute', 'projectaccessform-' + name, errors);
            });

            self.$accessFormWrapper.addClass('active').removeClass('inactive');
            self.$previewWrapper.addClass('inactive').removeClass('active');
        }

        if (PR.isFunction(callback)) {
            callback(response);
        }
    });
};

// --- Preview container

/**
 * Show preview thumbs container.
 * @param {Boolean} animate
 */
PreviewView.prototype.showPreviewThumbs = function(animate) {
    animate = typeof animate !== 'undefined' ? animate : true;

    $(this.settings.previewThumbsHandle).addClass('active');
    if (animate) {
        $(this.settings.previewThumbsContainer).stop(true, true).slideDown(250);
    } else {
        $(this.settings.previewThumbsContainer).show();
    }
};

/**
 * Hide preview thumbs container.
 * @param {Boolean} animate
 */
PreviewView.prototype.hidePreviewThumbs = function(animate) {
    animate = typeof animate !== 'undefined' ? animate : true;

    $(this.settings.previewThumbsHandle).removeClass('active');
    if (animate) {
        $(this.settings.previewThumbsContainer).stop(true, true).slideUp(250);
    } else {
        $(this.settings.previewThumbsContainer).hide();
    }
};

/**
 * Toggle preview thumbs container visibility.
 * @param {Boolean} animate
 */
PreviewView.prototype.togglePreviewThumbs = function(animate) {
    animate = typeof animate !== 'undefined' ? animate : true;

    if ($(this.settings.previewThumbsContainer).is(':visible')) {
        this.hidePreviewThumbs(animate);
    } else {
        this.showPreviewThumbs(animate);
    }
};

// --- Control panel

/**
 * Show preview version control panel.
 */
PreviewView.prototype.showControlPanel = function() {
    this.$body.removeClass('control-panel-collapsed');
    $(this.settings.controlPanel).stop(true, true).slideDown(200).removeClass('collapsed');
};

/**
 * Hide preview version control panel.
 */
PreviewView.prototype.hideControlPanel = function() {
    this.$body.addClass('control-panel-collapsed');
    $(this.settings.controlPanel).stop(true, true).slideUp(200).addClass('collapsed');
    this.hidePreviewThumbs(false);
};

/**
 * Toggle preview version control panel.
 */
PreviewView.prototype.toggleControlPanel = function() {
    if (this.$body.hasClass('control-panel-collapsed')) {
        this.showControlPanel();
    } else {
        this.hideControlPanel();
    }
};

// --- Modes

/**
 * Activates project preview mode.
 */
PreviewView.prototype.activatePreviewMode = function() {
    this.$body.addClass('preview-mode').removeClass('comments-mode');
    $(this.settings.previewModeHandle).addClass('active');
    $(this.settings.commentsModeHandle).removeClass('active');
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip', '');
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip-class', '');

    this.commentsView.disable();
};

/**
 * Activates project comment mode.
 */
PreviewView.prototype.activateCommentsMode = function() {
    this.$body.removeClass('preview-mode hotspots-force-show').addClass('comments-mode');
    $(this.settings.previewModeHandle).removeClass('active');
    $(this.settings.commentsModeHandle).addClass('active');
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip', this.settings.commentsTooltipText);
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip-class', 'comments-mode-tooltip');

    this.commentsView.enable();
};
