var ProjectIndex = function(data) {
    data = data || {};

    var defaults = {
        'typeSelect':     '[name="ProjectForm[type]"]',
        'subtypeSelect':  '#projectform-subtype',

        // projects list
        'projectsListWrapper':    '#projects_list_wrapper',
        'projectsList':           '#projects_list',
        'noMoreProjectsLabel':    '#no_more_projects',
        'loadMoreProjectsHandle': '#load_more_projects',

        // search
        'searchListWrapper': '#projects_search_list_wrapper',
        'searchList':        '#projects_search_list',
        'noSearchResults':   '#no_search_results',
        'searchWrapper':     '#projects_search_bar',
        'searchInput':       '#projects_search_input',
        'searchClearHandle': '.clear-projects-search',

        // Ajax urls
        'ajaxLoadProjectsUrl':   '/admin/projects/ajax-load-more',
        'ajaxSearchProjectsUrl': '/admin/projects/ajax-search-projects',
    };

    this.settings = $.extend({}, defaults, data);

    // cached selectors
    this.$typeSelect             = $(this.settings.typeSelect);
    this.$subtypeSelect          = $(this.settings.subtypeSelect);
    this.$projectsListWrapper    = $(this.settings.projectsListWrapper);
    this.$projectsList           = $(this.settings.projectsList);
    this.$noMoreProjectsLabel    = $(this.settings.noMoreProjectsLabel);
    this.$loadMoreProjectsHandle = $(this.settings.loadMoreProjectsHandle);
    this.$searchListWrapper      = $(this.settings.searchListWrapper);
    this.$searchList             = $(this.settings.searchList);
    this.$noSearchResults        = $(this.settings.noSearchResults);
    this.$searchWrapper          = $(this.settings.searchWrapper);
    this.$searchInput            = $(this.settings.searchInput);
    this.$searchClearHandle      = $(this.settings.searchClearHandle);

    this.searchXHR       = null;
    this.loadProjectsXHR = null;
    this.searchTrottle   = null;

    this.init();
};

/**
 * Init method
 */
ProjectIndex.prototype.init = function() {
    var self = this;
    var $document = $(document);

    // Custom select
    self.$subtypeSelect.selectify();

    // Subtypes toggle handler
    PR.bindSubtypesToggle(self.$typeSelect, self.$subtypeSelect);

    // Load more projects
    $document.on('click', self.settings.loadMoreProjectsHandle, function(e) {
        e.preventDefault();

        self.loadProjects();
    });

    // Projects search
    var searchValue   = 0;
    self.$searchInput.on('input paste', function(e) {
        searchValue = self.$searchInput.val();
        if (searchValue.length > 0) {
            self.$searchWrapper.addClass('has-value');
        } else {
            self.$searchWrapper.removeClass('has-value');
        }

        if (self.searchTrottle) {
            clearTimeout(self.searchTrottle);
        }
        self.searchTrottle = setTimeout(function() {
            self.searchProjects(searchValue);
        }, 200);
    });

    // Search focus/blur handlers
    self.$searchInput.on('focus', function(e) {
        self.$searchWrapper.addClass('focus');
    });
    self.$searchInput.on('blur', function(e) {
        self.$searchWrapper.removeClass('focus');
    });

    // Clear projects search
    $document.on('click', self.settings.searchClearHandle, function(e) {
        e.preventDefault();
        self.deactivateSearch();
    });

    // Keyboard shortcuts
    $document.on('keydown', function(e) {
        if (e.which == PR.keys.esc) {
            // Deactivate search on esc key press
            e.preventDefault();
            self.deactivateSearch();
        } else if (e.ctrlKey && e.which == PR.keys.f) {
            // activate on 'ctrl + f'
            e.preventDefault();
            self.$searchInput.focus();
        }
    });
};

/**
 * Bind subtype field toggles.
 */
ProjectIndex.prototype.subtypeToggle = function() {
    var self = this;

    var defaultSubtypes      = self.$subtypeSelect.data('default');
    var $customSubtypeSelect = self.$subtypeSelect.closest('.custom-select');
    var $activeSubtypes;

    var toggle = function (typeVal) {
        $activeSubtypes = $customSubtypeSelect.find('.option').hide()
            .filter('[data-group="' + typeVal + '"]').show();

        if (!$activeSubtypes.length) {
            self.$subtypeSelect.closest('.form-group').stop(true, true).slideUp(300);
        } else {
            if ($activeSubtypes.filter('.active').length) {
                self.$subtypeSelect.selectify('select', $activeSubtypes.filter('.active').data('value'));
            } else if (defaultSubtypes[typeVal]) {
                self.$subtypeSelect.selectify('select', defaultSubtypes[typeVal]);
            } else {
                self.$subtypeSelect.selectify('select', $activeSubtypes.first().data('value'));
            }

            self.$subtypeSelect.closest('.form-group').stop(true, true).slideDown(300);
        }
    };

    self.$typeSelect.on('change', function() {
        toggle($(this).val());
    });

    self.$typeSelect.closest('form').on('reset', function() {
        setTimeout(function() {
            toggle(self.$typeSelect.filter(':checked').val());
        }, 50); // @see yiiactiveform.js:431
    });

    toggle(self.$typeSelect.filter(':checked').val());
};

/**
 * Load more projects via ajax.
 */
ProjectIndex.prototype.loadProjects = function() {
    var self     = this;
    var nextPage = (self.$projectsList.data('page') || 1) + 1;

    self.$loadMoreProjectsHandle.addClass('btn-disabled');

    PR.abortXhr(self.loadProjectsXHR);
    self.loadProjectsXHR = $.ajax({
        url:  self.settings.ajaxLoadProjectsUrl,
        type: 'GET',
        data: {
            'page': nextPage
        }
    }).done(function(response) {
        self.$loadMoreProjectsHandle.removeClass('btn-disabled');

        if (response.success) {
            self.$projectsList.data('page', nextPage);

            if (response.projectsHtml) {
                self.$projectsList.append(response.projectsHtml);
            }

            if (response.hasMoreProjects) {
                self.$noMoreProjectsLabel.hide();
                self.$loadMoreProjectsHandle.show();
            } else {
                self.$noMoreProjectsLabel.show();
                self.$loadMoreProjectsHandle.hide();
            }
        }
    });
};

/**
 * Deactivates projects search bar.
 */
ProjectIndex.prototype.deactivateSearch = function() {
    var self = this;

    PR.abortXhr(self.searchXHR);
    clearTimeout(self.searchTrottle);

    self.$projectsListWrapper.show();
    self.$searchListWrapper.hide();
    self.$searchWrapper.removeClass('focus has-value');
    self.$searchInput.blur().val('');
    self.$searchList.html('');
};

/**
 * Performs projects search via ajax.
 * @param {String} search
 */
ProjectIndex.prototype.searchProjects = function(search) {
    var self = this;

    if (!search || search.length < 2) {
        self.$searchListWrapper.hide();
        self.$projectsListWrapper.show();
        return;
    }

    self.$searchListWrapper.show();
    self.$projectsListWrapper.hide();

    PR.abortXhr(self.searchXHR);
    self.searchXHR = $.ajax({
        url:  self.settings.ajaxSearchProjectsUrl,
        type: 'GET',
        data: {'search': search}
    }).done(function(response) {
        if (response.success) {
            if (response.projectsHtml) {
                self.$noSearchResults.hide();
                self.$searchList.html(response.projectsHtml);
            } else {
                self.$searchList.html('');
                self.$noSearchResults.show();
            }
        }
    });
};
