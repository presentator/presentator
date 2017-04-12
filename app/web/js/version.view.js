var VersionView = function(data) {
    data = data || {};

    var defaults = {
        // selectors
        'navList':      '#versions_list',
        'navItem':      '.version-item',
        'deleteHandle': '.version-delete',
        'createHandle': '#version_create',

        'versionTabs':    '#version_screens_tabs',
        'screensWrapper': '.version-screens',

        'versionSlidersWrapper': '#version_sliders',
        'versionSlider':         '.version-slider',

        // texts
        'confirmDeleteText': 'Do you really want to deleted the selected version and all of its screens?',
        'confirmCreateText': 'Do you really want to create a new version?',

        // urls
        'ajaxCreateUrl': '/versions/ajax-create',
        'ajaxDeleteUrl': '/versions/ajax-delete',
    };

    this.settings = $.extend({}, defaults, data);

    // commonly used selectors
    this.$navList               = $(this.settings.navList);
    this.$versionTabs           = $(this.settings.versionTabs);
    this.$versionSlidersWrapper = $(this.settings.versionSlidersWrapper);

    this.createXHR = null;
    this.deleteXHR = null;

    this.init();
};

/**
 * Init method
 */
VersionView.prototype.init = function() {
    var self = this;

    var $document = $(document);

    self.checkIsOnlyOneVersion();

    self.$versionTabs.tabs();

    // Create version handle
    $document.off('click.pr.versionView', self.settings.createHandle);
    $document.on('click.pr.versionView', self.settings.createHandle, function(e) {
        e.preventDefault();

        if (window.confirm(self.settings.confirmCreateText)) {
            self.createVersion($(this).data('project-id'));
        }
    });

    // Delete version handle
    $document.off('click.pr.versionView', self.settings.deleteHandle);
    $document.on('click.pr.versionView', self.settings.deleteHandle, function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $navItem = $(this).closest(self.settings.navItem);
        $navItem.addClass('danger-highlight');

        if (window.confirm(self.settings.confirmDeleteText)) {
            self.deleteVersion($navItem.data('version-id'));
        }

        $navItem.removeClass('danger-highlight');
    });

    // Activate version handle
    $document.off('click.pr.versionView', self.settings.navItem);
    $document.on('click.pr.versionView', self.settings.navItem, function(e) {
        e.preventDefault();

        self.activateVersion(this);
    });
};

/**
 * Checks the number of version items and add a helper class(es) accordingly.
 */
VersionView.prototype.checkIsOnlyOneVersion = function() {
    if (this.$navList.find(this.settings.navItem).length == 1) {
        this.$navList.addClass('only-one-version');
    } else if (this.$navList.hasClass('only-one-version')) {
        this.$navList.removeClass('only-one-version');
    }
};

/**
 * Returns single version nav item by its version id.
 * @param  {Number} versionId
 * @return {jQuery}
 */
VersionView.prototype.getNavItem = function(versionId) {
    if (!versionId) {
        console.warn('Missing version id.');
        return;
    }

    return $(this.settings.navItem + '[data-version-id="' + versionId + '"]').first();
};

/**
 * Returns the first version nav item in versions list.
 * @return {jQuery}
 */
VersionView.prototype.getFirstNavItem = function() {
    return this.$navList.children(this.settings.navItem).first();
};

/**
 * Returns the last version item in versions list.
 * @return {jQuery}
 */
VersionView.prototype.getLastNavItem = function() {
    return this.$navList.children(this.settings.navItem).last();
};

/**
 * Takes care for switching to the active project version.
 * @param {Mixed} version version item selector or version id
 */
VersionView.prototype.activateVersion = function(version) {
    var self = this;

    var $navItem = null;
    if (version instanceof jQuery) {
        $navItem = version;
    } else if (!isNaN(version)) {
        $navItem = self.getNavItem(version);
    } else {
        $navItem = $(version);
    }

    if (!$navItem || !$navItem.length) {
        console.warn('Version item was not found.');
        return;
    }

    if ($navItem.hasClass('active')) {
        return; // no need for further actions
    }

    $(self.settings.navItem).removeClass('active');
    $navItem.addClass('active')

    self.$versionTabs.tabs('goTo', $(self.settings.screensWrapper + '[data-version-id="' + $navItem.data('version-id') + '"]').attr('id'));
};

/**
 * Creates new version via ajax.
 * @param {Number} projectId
 */
VersionView.prototype.createVersion = function(projectId) {
    var self = this;

    PR.abortXhr(self.createXHR);
    self.createXHR = $.ajax({
        url:  self.settings.ajaxCreateUrl,
        type: 'POST',
        data: {
            'projectId': projectId
        },
    }).done(function(response) {
        if (response.success) {
            if (response.navItemHtml) {
                self.$navList.append(response.navItemHtml);
            }

            if (response.contentItemHtml) {
                self.$versionTabs.children('.tabs-content').append(response.contentItemHtml);
            }

            if (response.sliderHtml) {
                self.$versionSlidersWrapper.append(response.sliderHtml)
            }

            self.activateVersion(self.getLastNavItem());

            self.checkIsOnlyOneVersion();

            $(document).trigger('versionCreated', [self.getLastNavItem()]);
        }
    });
};

/**
 * Deletes a version via ajax.
 * @param {Number} versionId
 */
VersionView.prototype.deleteVersion = function(versionId) {
    var self = this;

    PR.abortXhr(self.deleteXHR);
    self.deleteXHR = $.ajax({
        url:  self.settings.ajaxDeleteUrl,
        type: 'POST',
        data: {
            'id': versionId
        },
    }).done(function(response) {
        if (response.success) {
            var $navItem = self.getNavItem(versionId)

            $navItem.addClass('anim-start').delay(400).queue(function(next) {
                if ($navItem.hasClass('active')) {
                    if ($navItem.prev().length) {
                        self.activateVersion($navItem.prev());
                    } else {
                        self.activateVersion($navItem.next());
                    }
                }

                $(self.settings.screensWrapper + '[data-version-id="' + versionId + '"]').remove();
                $navItem.remove();

                self.checkIsOnlyOneVersion();

                $(document).trigger('versionDeleted');


                next();
            });
        }
    });
};
