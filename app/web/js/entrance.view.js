var EntranceView = function(data) {
    data = data || {};

    var defaults = {
        'authTabs':           '#auth_tabs',
        'authPanel':          '#auth_panel',
        'diagonalBg':         '#diagonal_bg',
        'scrollContainer':    '#global_wrapper',
        'authLinksContainer': '.auth-group',
        'authLinks':          '.auth-group-link',
    };

    this.settings = $.extend({}, defaults, data);

    // commonly used selectors
    this.$authTabs           = $(this.settings.authTabs);
    this.$authPanel          = $(this.settings.authPanel);
    this.$diagonalBg         = $(this.settings.diagonalBg);
    this.$scrollContainer    = $(this.settings.scrollContainer);
    this.$authLinksContainer = $(this.settings.authLinksContainer);
    this.$authLinks          = $(this.settings.authLinks);

    // diagonal bg animation helpers
    this.prevDeg = null;
    this.currDeg = null;

    this.init();
};

/**
 * Init method
 */
EntranceView.prototype.init = function() {
    var self = this;

    self.$authTabs.tabs({
        changeHash: true,
        resetFormOnChange: true,
        animProgress: function(animation, progress, ramainingMs) {
            self.recalcDiagonalAngle(true);
        }
    });

    self.alignAuthLinks();

    self.recalcDiagonalAngle();

    $(window).on('load resize recalcDiagonalAngle', function() {
        self.alignAuthLinks();

        self.recalcDiagonalAngle();
    });

    $(document).on('remove', function() {
        self.recalcDiagonalAngle();
    });

    // refresh diagonal angle on form error
    self.$authPanel.find('form').on('afterValidateAttribute', function (event, messages, errorAttributes) {
        setTimeout(function() {
            self.recalcDiagonalAngle(true);
        }, 0);
    });
};

/**
 * Sets the diagonal background angle based on the auth-panel dimensions.
 */
EntranceView.prototype.recalcDiagonalAngle = function() {
    var self = this;

    if (PR.hasVerticalScrollbar(self.$scrollContainer)) {
        self.currDeg = 0;
    } else {
        self.currDeg = Math.atan(self.$authPanel.outerHeight() / self.$authPanel.outerWidth()) * 180 / Math.PI;
    }

    if (self.currDeg === self.prevDeg) {
        return; // no change
    }

    if (
        (self.prevDeg === 0 && self.currDeg !== 0) ||
        (self.prevDeg !== 0 && self.currDeg === 0)
    ) {
        self.$diagonalBg.addClass('animate');
    }

    self.$diagonalBg.one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function() {
        self.$diagonalBg.removeClass('animate');
    });

    self.$diagonalBg.css({
        'transform': 'translate3d(-50%, -50%, 0) rotate(-' + self.currDeg + 'deg)'
    });

    self.prevDeg = self.currDeg;
};

/**
 * Ensures that each auth link is aligned properly.
 */
EntranceView.prototype.alignAuthLinks = function() {
    var self = this;

    // reset
    self.$authLinksContainer.removeClass('full-icon-row');

    var firstLinkHeight = self.$authLinks.first().height();
    self.$authLinks.each(function (i, link) {
        if ($(link).height() != firstLinkHeight) {
            self.$authLinksContainer.addClass('full-icon-row');

            return false;
        }
    });
};
