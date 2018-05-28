var ProjectView = function(data) {
    data = data || {};

    var defaults = {
        'projectEditHandle':  '.project-edit-handle',
        'projectUpdatePopup': '#project_edit_popup',
        'titleHolders':       '.project-title',

        // project preview share
        'shareForm':   '#project_preview_share_form',
        'previewLink': '.preview-link',

        // Admins search form
        'adminsSearchForm':         '#admins_search_form',
        'adminsSearchUserInput':    '#admins_search_term_input',
        'adminsSearchProjectInput': '#admins_search_project_id',
        'adminSuggestionsList':     '#admins_search_suggestions',
        'adminSuggestionItem':      '.user-suggestion-item',

        'adminsList':        '#admins_list',
        'adminListItem':     '.user-list-item',
        'adminRemoveHandle': '.remove-handle',

        // Ajax urls
        'ajaxGetUpdateFormUrl':  '',
        'ajaxSaveUpdateFormUrl': '',
        'ajaxShareProjectUrl':   '',
        'ajaxSearchUsersUrl':    '',
        'ajaxAddAdminUrl':       '',
        'ajaxRemoveAdminUrl':    '',
    };

    this.settings = $.extend({}, defaults, data);

    // cached elements
    this.$projectUpdatePopup       = $(this.settings.projectUpdatePopup);
    this.$shareForm                = $(this.settings.shareForm);
    this.$previewLink              = $(this.settings.previewLink);
    this.$adminsSearchForm         = $(this.settings.adminsSearchForm);
    this.$adminsSearchUserInput    = $(this.settings.adminsSearchUserInput);
    this.$adminsSearchProjectInput = $(this.settings.adminsSearchProjectInput);
    this.$adminSuggestionsList     = $(this.settings.adminSuggestionsList);
    this.$adminsList               = $(this.settings.adminsList);

    this.generalXHR    = null;
    this.updateXHR     = null;
    this.shareXHR      = null;
    this.searchXHR     = null;
    this.searchTrottle = null;

    this.init();
};

/**
 * Init method
 */
ProjectView.prototype.init = function() {
    var self = this;
    var $document = $(document);

    // project update
    $document.on('click', self.settings.projectEditHandle, function(e) {
        e.preventDefault();

        self.getUpdateForm();
    });

    self.$previewLink.each(function(i, link) {
        $(link).html(PR.highlightLastStringPart($(link).attr('href')));
    });

    // Project share
    self.$shareForm.on('beforeSubmit', function(e) {
        self.shareProject(this);

        return false;
    });

    // Admins search
    self.$adminsSearchUserInput.on('input paste', function() {
        if (self.searchTrottle) {
            clearTimeout(self.searchTrottle);
        }

        self.searchTrottle = setTimeout(function() {
            self.searchUsers(self.$adminsSearchUserInput.val());
        }, 200);
    });

    // Add project admin from suggestions list
    $document.on('click', self.settings.adminSuggestionItem, function(e) {
        e.preventDefault();

        var $item = $(this);

        self.$adminsSearchUserInput.val($item.data('value'));
        self.addAdmin($item.data('user-id'), self.$adminsSearchProjectInput.val());
    });

    // Remove project admin
    $document.on('click', self.settings.adminListItem + ' ' + self.settings.adminRemoveHandle, function(e) {
        e.preventDefault();

        var $item = $(this).closest(self.settings.adminListItem);

        if ($item.siblings().length > 0 && window.confirm($item.data('confirm-text'))) {
            self.removeAdmin($item.data('user-id'), $item.data('project-id'));
        }
    });

    // Clear admin suggestion term input on outside click
    var $suggestionInputParent = self.$adminsSearchUserInput.closest('.form-group');
    $document.on('click', function(e) {
        if (
            self.$adminsSearchUserInput.val().length > 0 &&
            !$suggestionInputParent.is(e.target) &&
            !$suggestionInputParent.has(e.target).length
        ) {
            e.preventDefault();
            self.$adminsSearchUserInput.val('');
            self.$adminSuggestionsList.hide();
        }
    });
};

/**
 * Handles sending project share email via ajax.
 * @param {String|Object|Null} form
 */
ProjectView.prototype.shareProject = function(form) {
    var self  = this;
    var $form = $(form || self.$shareForm);

    if (!$form.length) {
        console.warn('Share form is missing.');
        return;
    }

    var ajaxUrl = self.settings.ajaxShareProjectUrl || $form.attr('action');

    PR.AUTO_LOADER = false;

    PR.abortXhr(self.shareXHR);
    self.shareXHR = $.ajax({
        url:  ajaxUrl,
        type: 'POST',
        data: $form.serialize()
    }).done(function(response) {
        if (response.success) {
            setTimeout(function() {
                PR.closePopup();
            }, 580); // animations delay
        } else if (response.errors) {
            $.each(response.errors, function(name, errors) {
                $form.yiiActiveForm('updateAttribute', 'projectshareform-' + name, errors);
            });
        }
    });
};

/**
 * Renders project update form popup.
 */
ProjectView.prototype.getUpdateForm = function() {
    var self = this;

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url:  self.settings.ajaxGetUpdateFormUrl,
        type: 'GET'
    }).done(function(response) {
        if (response.success && response.updateForm) {
            self.$projectUpdatePopup.find('.popup-content .content').first().html(response.updateForm);
            PR.openPopup(self.$projectUpdatePopup);

            var $form = self.$projectUpdatePopup.find('form');

            // Project update
            $form.on('beforeSubmit', function(e) {
                self.saveUpdateForm(this);

                return false;
            });
        }
    });
};

/**
 * Handles project update form submit via ajax.
 * @param {String|Object|Null} form
 */
ProjectView.prototype.saveUpdateForm = function(form) {
    var self  = this;
    var $form = $(form);

    if (!$form.length) {
        console.warn('Update form is missing.');
        return;
    }

    var ajaxUrl = self.settings.ajaxSaveUpdateFormUrl || $form.attr('action');

    PR.AUTO_LOADER = false;

    PR.abortXhr(self.updateXHR);
    self.updateXHR = $.ajax({
        url:  ajaxUrl,
        type: 'POST',
        data: $form.serialize()
    }).done(function(response) {
        if (response.success) {
            if (response.project) {
                $(self.settings.titleHolders).text(response.project.title);
            }

            setTimeout(function() {
                PR.closePopup();
            }, 580); // animations delay
        }
    });
};

/**
 * Performs users search via ajax.
 * @param {String} search
 */
ProjectView.prototype.searchUsers = function(search) {
    var self  = this;

    if (!search || search.length < 3) {
        self.$adminSuggestionsList.hide();
        return;
    }

    PR.abortXhr(self.searchXHR);
    self.searchXHR = $.ajax({
        url:  self.settings.ajaxSearchUsersUrl,
        type: 'GET',
        data: {'search': search}
    }).done(function(response) {
        if (response.success && response.suggestionsHtml) {
            self.$adminSuggestionsList.html(response.suggestionsHtml).show();
        }
    });
};

/**
 * Links user to a project model via ajax.
 * @param {Number} userId
 * @param {Number} projectId
 */
ProjectView.prototype.addAdmin = function(userId, projectId) {
    var self  = this;

    if (!userId || !projectId) {
        return;
    }

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url:  self.settings.ajaxAddAdminUrl,
        type: 'POST',
        data: {
            'userId':    userId,
            'projectId': projectId,
        }
    }).done(function(response) {
        self.$adminsSearchUserInput.val('');
        self.$adminSuggestionsList.hide();

        if (response.success && response.listItemHtml) {
            self.$adminsList.append(response.listItemHtml);
        }
    });
};

/**
 * Unlinks user from a project model via ajax.
 * @param {Number} userId
 * @param {Number} projectId
 */
ProjectView.prototype.removeAdmin = function(userId, projectId) {
    var self  = this;

    if (!userId || !projectId) {
        return;
    }

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url:  self.settings.ajaxRemoveAdminUrl,
        type: 'POST',
        data: {
            'userId':    userId,
            'projectId': projectId,
        }
    }).done(function(response) {
        if (response.success) {
            $(self.settings.adminListItem + '[data-user-id="' + userId + '"]').remove();

            if (response.redirectUrl && response.redirectUrl.length > 0) {
                window.location.href = response.redirectUrl;
            }
        }
    });
};
