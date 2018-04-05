var ProfileView = function (data) {
    data = data || {};

    var defaults = {
        'emailChangeTokenExpire': 1800, // in sec

        // setting forms
        'userIdentificator':     '.user-identificator',
        'userTabs':              '#user_tabs',
        'notificationsForm':     '#user_notifications_form',
        'passwordForm':          '#user_password_form',
        'profileForm':           '#user_profile_form',
        'emailField':            '#userprofileform-email',
        'emailConfirmFormGroup': '.field-userprofileform-password',
        'pendingEmailHint':      '.pending-email-hint',

        // ajax urls
        'ajaxNotificationsSaveUrl': '/account/ajax-notifications-save',
        'ajaxPasswordSaveUrl':      '/account/ajax-password-save',
        'ajaxProfielSaveUrl':       '/account/ajax-profile-save',

        // texts
        'pendingEmailHintText': 'Confirmation email was sent to {pendingEmail}.'
    };

    this.settings = $.extend({}, defaults, data);

    // commonly used selectors
    this.$userTabs              = $(this.settings.userTabs);
    this.$notificationsForm     = $(this.settings.notificationsForm);
    this.$passwordForm          = $(this.settings.passwordForm);
    this.$profileForm           = $(this.settings.profileForm);
    this.$emailField            = $(this.settings.emailField);
    this.$emailConfirmFormGroup = $(this.settings.emailConfirmFormGroup);

    this.saveFormXHR = null;

    this.PENDING_EMAIL_COOKIE_KEY = 'pending_email';

    this.init();
};

/**
 * Init method
 */
ProfileView.prototype.init = function () {
    var self      = this;
    var $document = $(document);


    self.$userTabs.tabs();
    self.togglePendingEmailHintBox();

    self.$userTabs.off('tabChange.pr');
    self.$userTabs.on('tabChange.pr', function (e, tabContentId, $tabContent) {
        $tabContent = $tabContent || $();
        var $form   = $tabContent.find('form');

        if ($form.length && $form.data('yiiActiveForm')) {
            $form.yiiActiveForm('resetForm'); // reset form errors
        }
    });

    self.$notificationsForm.off('beforeSubmit.profileview');
    self.$notificationsForm.on('beforeSubmit.profileview', function (e) {
        self.saveNotificationsForm();

        return false;
    });

    self.$passwordForm.off('beforeSubmit.profileview');
    self.$passwordForm.on('beforeSubmit.profileview', function (e) {
        self.savePasswordForm();

        return false;
    });

    self.$profileForm.off('beforeSubmit.profileview');
    self.$profileForm.on('beforeSubmit.profileview', function (e) {
        self.saveProfileForm();

        return false;
    });

    // Toggle email confirm form group
    // ---
    var emailChangeTrottle = null;
    self.$emailField.off('input.profileview change.profileview');
    self.$emailField.on('input.profileview change.profileview', function (e) {
        if (emailChangeTrottle) {
            clearTimeout(emailChangeTrottle);
            emailChangeTrottle = null;
        }

        emailChangeTrottle = setTimeout(function () {
            self.toggleEmailConfirmFormGroup();
        }, 250);
    });

    self.$profileForm.off('reset.profileview');
    self.$profileForm.on('reset.profileview', function (e) {
        setTimeout(function () {
            self.toggleEmailConfirmFormGroup();
        }, 0); // reorder execution queue
    });

    self.toggleEmailConfirmFormGroup();
};

/**
 * Generic method to persist form data via ajax.
 * @param {Mixed}    form
 * @param {String}   action
 * @param {Function} callback
 */
ProfileView.prototype.saveForm = function (form, action, callback) {
    var self  = this;
    var $form = $(form);

    action = action || $form.attr('action');

    PR.abortXhr(self.saveFormXHR);
    self.saveFormXHR = $.ajax({
        url: action,
        type: 'POST',
        data: $form.serialize()
    }).done(function (response) {
        if (PR.isFunction(callback)) {
            callback(response);
        }

        if (response.success) {
            PR.saveFormState($form);

            $form.removeClass('is-dirty');
        } else if (response.errors) {
            $form.yiiActiveForm('updateMessages', response.errors, true);
        }
    });
};

/**
 * Persist user notifications form data.
 */
ProfileView.prototype.saveNotificationsForm = function () {
    this.saveForm(
        this.$notificationsForm,
        this.settings.ajaxNotificationsSaveUrl
    );
};

/**
 * Persist user password form data.
 */
ProfileView.prototype.savePasswordForm = function () {
    var self = this;

    self.saveForm(
        self.$passwordForm,
        self.settings.ajaxPasswordSaveUrl,
        function (response) {
            if (response.success) {
                self.$passwordForm.get(0).reset();
            }
        }
    );
};

/**
 * Persist user profile form data.
 */
ProfileView.prototype.saveProfileForm = function () {
    var self = this;

    self.saveForm(
        self.$profileForm,
        self.settings.ajaxProfielSaveUrl,
        function (response) {
            if (!response.success) {
                return;
            }

            if (response.userIdentificator) {
                $(self.settings.userIdentificator).text(response.userIdentificator);
            }

            var oldEmail = self.$emailField.data('original-email');
            var newEmail = self.$emailField.val();

            // reset email and password fields state
            self.$emailField.val(oldEmail);
            self.$emailConfirmFormGroup.find(':input').val('');
            self.toggleEmailConfirmFormGroup();

            // store the pending email in a cookie
            // @todo depending on the usage, consider to store in session or db
            var expireDate = new Date();
            expireDate.setSeconds(expireDate.getSeconds() + self.settings.emailChangeTokenExpire);
            PR.cookies.setItem(self.PENDING_EMAIL_COOKIE_KEY, newEmail, expireDate);

            self.togglePendingEmailHintBox();
        }
    );
};

/**
 * Takes care for toggling the visibility of email confirm form group container.
 */
ProfileView.prototype.toggleEmailConfirmFormGroup = function () {
    if (this.$emailField.val() !== this.$emailField.data('original-email')) {
        this.$emailConfirmFormGroup.removeClass('hidden');
    } else {
        this.$emailConfirmFormGroup.addClass('hidden');
    }
};

/**
 * Toggles pending email helper hint block text based on stored cookie value.
 */
ProfileView.prototype.togglePendingEmailHintBox = function () {
    var originalEmail = this.$emailField.data('original-email');

    var pendingEmail = PR.cookies.getItem(this.PENDING_EMAIL_COOKIE_KEY, '');

    if (pendingEmail && originalEmail !== pendingEmail) {
        var $hintBox = this.$emailField.parent().find(this.settings.pendingEmailHint).first();

        if (!$hintBox.length) {
            $hintBox = $('<p class="help-block ' + this.settings.pendingEmailHint.substr(1) + '"></p>');
            this.$emailField.after($hintBox);
        }

        $hintBox.text(PR.resolveTemplate(this.settings.pendingEmailHintText, {
            'pendingEmail': pendingEmail
        }));
    }
};
