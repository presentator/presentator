var SuperIndex = function (data) {
    data = data || {};

    var defaults = {
        // users list
        'usersListWrapper': '#users_list_wrapper',
        'usersList':        '#users_list',

        // search
        'searchListWrapper': '#users_search_list_wrapper',
        'searchList':        '#users_search_list',
        'searchBar':         '#users_search_bar',
        'searchInput':       '#users_search_input',
        'searchClearHandle': '.clear-users-search',

        // Ajax urls
        'ajaxSearchUsersUrl': '/admin/users/ajax-search-users',
    };

    this.settings = $.extend({}, defaults, data);

    // cached selectors
    this.$usersListWrapper  = $(this.settings.usersListWrapper);
    this.$usersList         = $(this.settings.usersList);
    this.$searchListWrapper = $(this.settings.searchListWrapper);
    this.$searchList        = $(this.settings.searchList);
    this.$searchBar         = $(this.settings.searchBar);
    this.$searchInput       = $(this.settings.searchInput);
    this.$searchClearHandle = $(this.settings.searchClearHandle);

    this.searchXHR     = null;
    this.searchTrottle = null;

    this.init();
};

/**
 * Init method
 */
SuperIndex.prototype.init = function () {
    var self      = this;
    var $document = $(document);

    // Users search
    var searchValue = '';
    self.$searchInput.on('input paste', function (e) {
        searchValue = self.$searchInput.val();
        if (searchValue.length > 0) {
            self.$searchBar.addClass('has-value');
        } else {
            self.$searchBar.removeClass('has-value');
        }

        if (self.searchTrottle) {
            clearTimeout(self.searchTrottle);
        }
        self.searchTrottle = setTimeout(function () {
            self.searchUsers(searchValue);
        }, 200);
    });

    // Search focus/blur handlers
    self.$searchInput.on('focus', function (e) {
        self.$searchBar.addClass('focus');
    });
    self.$searchInput.on('blur', function (e) {
        self.$searchBar.removeClass('focus');
    });

    // Clear users search
    $document.on('click', self.settings.searchClearHandle, function (e) {
        e.preventDefault();
        self.deactivateSearch();
    });

    // Keyboard shortcuts
    $document.on('keydown', function (e) {
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
 * Deactivates users search bar.
 */
SuperIndex.prototype.deactivateSearch = function () {
    var self = this;

    PR.abortXhr(self.searchXHR);
    clearTimeout(self.searchTrottle);

    self.$usersListWrapper.show();
    self.$searchListWrapper.hide();
    self.$searchBar.removeClass('focus has-value');
    self.$searchInput.blur().val('');
    self.$searchList.html('');
};

/**
 * Performs users search via ajax.
 * @param {String} search
 */
SuperIndex.prototype.searchUsers = function (search) {
    var self = this;

    if (!search || search.length < 2) {
        self.$searchListWrapper.hide();
        self.$usersListWrapper.show();
        return;
    }

    self.$searchListWrapper.show();
    self.$usersListWrapper.hide();

    PR.abortXhr(self.searchXHR);
    self.searchXHR = $.ajax({
        url:  self.settings.ajaxSearchUsersUrl,
        type: 'GET',
        data: {'search': search}
    }).done(function (response) {
        if (response.success) {
            if (response.listHtml) {
                self.$searchList.html(response.listHtml);
            } else {
                self.$searchList.html('');
            }
        }
    });
};
