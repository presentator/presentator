var ScreenFitView = function (data) {
    data = data || {};

    var defaults = {
        'screenFitToggleHandle': '#panel_toggle_screen_fit_handle',
        'versionSlider':         '.version-slider',
        'versionSliderItem':     '.slider-item',
        'sliderItemImg':         '.hotspot-layer',
    };

    this.settings = $.extend({}, defaults, data);

    this.init();
};

/**
 * Init method
 */
ScreenFitView.prototype.init = function () {
    var self = this;

    var $document = $(document);

    // Screen fit toggle
    $document.off('click.screenfitview', self.settings.screenFitToggleHandle);
    $document.on('click.screenfitview', self.settings.screenFitToggleHandle, function (e) {
        e.preventDefault();

        self.toggle()
    });

    $document.off('sliderChange.screenfitview', self.settings.versionSlider);
    $document.on('sliderChange.screenfitview', self.settings.versionSlider, function (e, $activeSlide) {
        if (self.isActive()) {
            self.enable($activeSlide);
        } else {
            self.disable($activeSlide);
        }
    });
};

/**
 * @return {Boolean}
 */
ScreenFitView.prototype.isActive = function () {
    return $(this.settings.screenFitToggleHandle).first().hasClass('active');
};

/**
 * Enable screen window fit handler.
 * @param {Mixed} sliderItem
 */
ScreenFitView.prototype.enable = function (sliderItem) {
    var self = this;
    var $handle     = $(self.settings.screenFitToggleHandle);
    var $sliderItem = $(sliderItem)

    $handle.addClass('active');

    var scaleFactor = self.scaleScreen(window, $sliderItem);

    $sliderItem.data('scale-factor', scaleFactor || 1);

    $(window).off('resize.screenfit').on('resize.screenfit', function (e) {
        if ($sliderItem.length) {
            $sliderItem.data('scale-factor', self.scaleScreen(window, $sliderItem));
        }
    });
};

/**
 * Disable screen window fit handler.
 * @param {Mixed} sliderItem
 */
ScreenFitView.prototype.disable = function (sliderItem) {
    var $handle     = $(this.settings.screenFitToggleHandle);
    var $sliderItem = $(sliderItem || (this.$activeVersionSlider && this.$activeVersionSlider.slider('getActive')))

    $handle.removeClass('active');

    var originalScaleFactor = $sliderItem.data('original-scale-factor') || 1;

    if (originalScaleFactor == $sliderItem.data('scale-factor')) {
        return; // no change is required
    }

    $sliderItem.data('scale-factor', originalScaleFactor);
    this.scaleScreen(originalScaleFactor, $sliderItem);

    $(window).off('resize.screenfit');

    PR.horizontalAlign($sliderItem);
};

/**
 * Screen window fit toggle handler.
 * @param {Mixed} sliderItem
 */
ScreenFitView.prototype.toggle = function (sliderItem) {
    var $sliderItem = $(sliderItem || (this.settings.versionSliderItem + '.active'));
    if (!$sliderItem.length) {
        return;
    }

    if (this.isActive()) {
        this.disable($sliderItem);
    } else {
        this.enable($sliderItem);
    }
};

/**
 * Scale single screen item by keeping its aspect ratio.
 * @param  {Number|String} scaleTo Scale factor or scale to item selector.
 * @param  {[type]} img            Screen image to scale
 * @param  {[type]} siblings       Optional items siblings to scale with.
 * @param  {Number} The calculated scale factor
 */
ScreenFitView.prototype.scaleScreen = function (scaleTo, sliderItem, siblings) {
    siblings = typeof siblings !== 'undefined' ? siblings : '.hotspot, .comment-target';

    var $sliderItem = $(sliderItem);
    var $img        = $sliderItem.find(this.settings.sliderItemImg);
    var scaleFactor = 1;

    var itemWidth  = $img.data('original-width') || $img.width();
    var itemHeight = $img.data('original-height') || $img.height();

    if (isNaN(scaleTo)) {
        if ($(scaleTo).width() >= itemWidth) {
            scaleFactor = 1; // no need to up scale
        } else {
            scaleFactor = itemWidth / $(scaleTo).width();
        }
    } else {
        scaleFactor = scaleTo;
    }

    // scale screen img
    $img.css({
        width:  itemWidth / scaleFactor,
        height: itemHeight / scaleFactor
    });

    // scale screen img siblings (if any)
    var $sibling = $();
    var settings = {};
    $sliderItem.find(siblings).each(function (i, elem) {
        $sibling = $(elem);
        settings = {};

        if ($sibling.data('original-width') && $sibling.data('original-height')) {
            settings.width  = $sibling.data('original-width') / scaleFactor;
            settings.height = $sibling.data('original-height') / scaleFactor;
        }

        settings.left = ($sibling.data('original-left') || $sibling.position().left) / scaleFactor;
        settings.top  = ($sibling.data('original-top') || $sibling.position().top) / scaleFactor;

        $sibling.css(settings);
    });

    return scaleFactor;
};
