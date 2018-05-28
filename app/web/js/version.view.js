var VersionView = function(data) {
    data = data || {};

    var defaults = {
        // selectors
        'navList':            '#versions_list',
        'navItem':            '.version-item',
        'deleteHandle':       '.version-delete',
        'editHandle':         '.version-edit',
        'createHandle':       '#version_create',
        'versionEditPopup':   '#version_edit_popup',
        'versionCreatePopup': '#version_create_popup',
        'versionTabs':        '#version_screens_tabs',
        'screensWrapper':     '.version-screens',
        'typeSelect':         '[name="VersionForm[type]"]',
        'subtypeSelect':      '#versionform-subtype',

        // urls
        'ajaxGetFormUrl':  '/versions/ajax-get-form',
        'ajaxSaveFormUrl': '/versions/ajax-save-form',
        'ajaxCreateUrl':   '/versions/ajax-create',
        'ajaxDeleteUrl':   '/versions/ajax-delete',
    };

    this.settings = $.extend({}, defaults, data);

    // commonly used selectors
    this.$navList            = $(this.settings.navList);
    this.$versionEditPopup   = $(this.settings.versionEditPopup);
    this.$versionCreatePopup = $(this.settings.versionCreatePopup);
    this.$versionTabs        = $(this.settings.versionTabs);

    this.generalXHR = null;
    this.createXHR  = null;
    this.deleteXHR  = null;

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

        self.getVersionForm();
    });

    // Edit version handle
    $document.off('click.pr.versionView', self.settings.navItem + ' ' + self.settings.editHandle);
    $document.on('click.pr.versionView', self.settings.navItem + ' ' + self.settings.editHandle, function(e) {
        e.preventDefault();
        e.stopPropagation();

        self.getVersionForm($(this).closest(self.settings.navItem).data('version-id'));
    });

    // Delete version handle
    $document.off('click.pr.versionView', self.settings.deleteHandle);
    $document.on('click.pr.versionView', self.settings.deleteHandle, function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (window.confirm($(this).data('confirm-text') || 'Do you really want to delete the version?')) {
            self.deleteVersion($(this).data('version-id'));
        }
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
 * Switch the current active project version.
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
 * Fetch and populate version form popup.
 * @param {null|Number} versionId
 */
VersionView.prototype.getVersionForm = function(versionId) {
    var self   = this;
    var $popup = versionId ? self.$versionEditPopup : self.$versionCreatePopup;

    // clear previous content
    self.$versionEditPopup.add(self.$versionCreatePopup).find('.popup-content .content').empty();

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url:  self.settings.ajaxGetFormUrl,
        type: 'GET',
        data: {
            'versionId': versionId || '',
        }
    }).done(function(response) {
        if (response.success && response.formHtml) {
            $popup.find('.popup-content .content').first().html(response.formHtml);
            PR.openPopup($popup);

            var $form = $popup.find('form');

            // Custom select
            $form.find(self.settings.subtypeSelect).selectify();

            // Subtypes toggle handler
            PR.bindSubtypesToggle($form.find(self.settings.typeSelect), $form.find(self.settings.subtypeSelect), false);

            // Scales toggle handler
            PR.bindScalesToggle($form.find(self.settings.typeSelect));

            $form.on('beforeSubmit', function(e) {
                self.submitVersionForm(this);

                return false;
            });
        }
    });
};

/**
 * Takes care for submitting the version form via ajax.
 * @param {String|Object} form
 */
VersionView.prototype.submitVersionForm = function(form) {
    var self = this;

    var $form = $(form);
    if (!$form.length) {
        console.warn('Missing version form.');
        return;
    }

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url:  self.settings.ajaxSaveFormUrl,
        type: 'POST',
        data: $form.serialize(),
    }).done(function(response) {
        if (response.success) {
            PR.closePopup();

            var $navItem = $();
            if (response.navItemHtml) {
                if (response.isUpdate && response.version) {
                    $navItem = self.getNavItem(response.version.id);
                    $navItem.replaceWith(response.navItemHtml)
                    $navItem = self.getNavItem(response.version.id); // reload nav item
                } else {
                    self.$navList.append(response.navItemHtml);
                    $navItem = self.getLastNavItem();
                }
            }

            if (response.contentItemHtml) {
                self.$versionTabs.children('.tabs-content').append(response.contentItemHtml);
            }

            self.activateVersion($navItem);

            self.checkIsOnlyOneVersion();

            if (response.isUpdate) {
                $(document).trigger('versionUpdated', [$navItem]);
            } else {
                $(document).trigger('versionCreated', [$navItem]);
            }
        }
    });
};

/**
 * Deletes a version via ajax.
 * @param {Number} versionId
 */
VersionView.prototype.deleteVersion = function(versionId) {
    var self = this;

    var $navItem = self.getNavItem(versionId)
    if (!$navItem) {
        console.warn('Missing version nav item.');
        return;
    }

    PR.abortXhr(self.deleteXHR);
    self.deleteXHR = $.ajax({
        url:  self.settings.ajaxDeleteUrl,
        type: 'POST',
        data: {
            'id': versionId
        },
    }).done(function(response) {
        if (response.success) {
            PR.closePopup();

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
