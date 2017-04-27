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
        'previewThumbsWrapper':   '.preview-thumbs-wrapper',
        'previewThumb':           '.preview-thumb',
        'hotspot':                '.hotspot',
        'activeSlideTitleHolder': '.active-slide-title',
        'activeSlideOrderHolder': '.active-slide-order',

        // menu handles
        'floatingMenu':       '.floating-menu',
        'floatingMenuHandle': '#fm_visibility_handle',
        'screensMenuHandle':  '#fm_screens_handle',
        'previewModeHandle':  '#fm_preview_handle',
        'commentsModeHandle': '#fm_comments_handle',

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

    this.FLOATING_MENU_COLLAPSE_COOKIE = 'floating-menu-collapse';

    // commonly used selectors
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

    var $document = $(document);
    var $body     = $('body');

    if (self.settings.grantedAccess) {
        self.invokeAccess();
    }

    self.$accessForm.on('beforeSubmit.yii', function(e) {
        self.invokeAccess();

        return false;
    });

    // keyboard shortcuts
    $document.on('keydown', function(e) {
        if (e.which === PR.keys.esc) {
            e.preventDefault();

            self.hidePreviewThumbs();
        } else if (!$body.hasClass('comment-active')) {
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
    $document.on('change', self.settings.versionsSelect, function(e) {
        self.invokeAccess($(this).find('option:selected').index() + 1, 1);
    });

    // Floating menu visibility toggle
    $document.on('click', self.settings.floatingMenuHandle, function(e) {
        e.preventDefault();

        var $menu = $(this).closest(self.settings.floatingMenu);
        if ($menu.hasClass('collapsed')) {
            $menu.removeClass('collapsed');
            PR.cookies.setItem(self.FLOATING_MENU_COLLAPSE_COOKIE, 0);
        } else {
            $menu.addClass('collapsed');
            PR.cookies.setItem(self.FLOATING_MENU_COLLAPSE_COOKIE, 1);
        }
    });

    // Preview mode handle
    $document.on('click', self.settings.previewModeHandle, function(e) {
        e.preventDefault();

        self.activatePreviewMode();
    });

    // Comments mode handle
    $document.on('click', self.settings.commentsModeHandle, function(e) {
        e.preventDefault();

        self.activateCommentsMode();
    });

    // Hotspots navigation
    $document.on('click', self.settings.hotspot, function(e) {
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
        if (e.shiftKey &&
            e.which === PR.keys.h &&
            $body.hasClass('preview-mode')
        ) {
            e.preventDefault();

            $body.toggleClass('hotspots-force-show');
        }
    });

    // Show preview thumb screens
    $document.on('click', self.settings.screensMenuHandle, function(e) {
        e.preventDefault();

        self.showPreviewThumbs();
    });

    // Change slider on preview thumb click
    $document.on('click', self.settings.previewThumb, function(e) {
        e.preventDefault();

        $slider = $(self.settings.versionSlider);
        if ($slider.length) {
            $slider.slider('goTo', $(this).index());
        }

        self.hidePreviewThumbs();
    });

    // Hide preview thumb screens on outside click
    $previewThumbs = $();
    $document.on('click', self.settings.previewThumbsWrapper, function(e) {
        $previewThumbs = $(self.settings.previewThumb);
        if (
            !$previewThumbs.is(e.target) ||
            !$previewThumbs.find(e.target).length
        ) {
            e.preventDefault();

            self.hidePreviewThumbs();
        }
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

            var $body            = $('body');
            var $slider          = $(self.settings.versionSlider);
            var activeVersionPos = ($(self.settings.versionsSelect).find('option:selected').index() || 0) + 1;

            $slider.on('sliderChange sliderInit', function(e, $activeSlide) {
                // update active preview thumb on slider change
                var screenId = $activeSlide.data('screen-id');

                $(self.settings.previewThumb).removeClass('active')
                    .filter('[data-screen-id="' + screenId + '"]').addClass('active');

                PR.horizontalAlign($activeSlide);

                self.hidePreviewThumbs();

                self.commentsView.deselectCommentTarget();

                self.commentsView.updateCommentsCounter();

                self.setHashNav(activeVersionPos, $activeSlide.index() + 1);

                self.updateSliderCaption();
            });

            $slider.slider({
                nav: false
            });

            $slider.find(self.settings.versionSliderItem).on('scroll', function(e) {
                if ($body.hasClass('comment-active')) {
                    self.commentsView.deselectCommentTarget();
                }
            });

            self.activatePreviewMode();

            if (screenPos > 1) {
                $slider.slider('goTo', screenPos - 1, false);
            }

            if (Number(PR.cookies.getItem(self.FLOATING_MENU_COLLAPSE_COOKIE, 0))) {
                $(self.settings.floatingMenu).addClass('collapsed');
            } else {
                $(self.settings.floatingMenu).removeClass('collapsed');
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

/**
 * Show preview thumbs container.
 */
PreviewView.prototype.showPreviewThumbs = function() {
    $(this.settings.previewThumbsWrapper).addClass('active');
};

/**
 * Hide preview thumbs container.
 */
PreviewView.prototype.hidePreviewThumbs = function() {
    var $previewThumbsWrapper = $(this.settings.previewThumbsWrapper);

    if ($previewThumbsWrapper.hasClass('active')) {
        $previewThumbsWrapper.addClass('close-start').stop(true, true).delay(400).queue(function(next) {
            $previewThumbsWrapper.removeClass('active close-start');

            next();
        });
    }
};

/**
 * Activates project preview mode.
 */
PreviewView.prototype.activatePreviewMode = function() {
    $('body').addClass('preview-mode').removeClass('comments-mode');
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
    $('body').removeClass('preview-mode hotspots-force-show').addClass('comments-mode');
    $(this.settings.previewModeHandle).removeClass('active');
    $(this.settings.commentsModeHandle).addClass('active');
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip', this.settings.commentsTooltipText);
    PR.setData(this.settings.versionSliderItem + ' .hotspot-layer', 'cursor-tooltip-class', 'comments-mode-tooltip');

    this.commentsView.enable();
};
