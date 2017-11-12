var ProfileView = function (data) {
    data = data || {};

    var defaults = {
        'maxUploadSize': 15,

        // avatar form selectors
        'avatarImg':           '.avatar-img',
        'cropHotspot':         '#crop_hotspot',
        'avatarPopup':         '#avatar_popup',
        'uploadContainer':     '#upload_container',
        'previewContainer':    '#preview_container',
        'previewImg':          '#preview_img',
        'previewRemoveHandle': '#preview_remove',
        'saveAvatarHandle':    '#persist_avatar',
        'deleteAvatarHandle':  '.delete-avatar',

        // setting forms
        'userIdentificator':     '.user-identificator',
        'userTabs':              '#user_tabs',
        'notificationsForm':     '#user_notifications_form',
        'passwordForm':          '#user_password_form',
        'profileForm':           '#user_profile_form',
        'emailField':            '#userprofileform-email',
        'emailConfirmFormGroup': '.field-userprofileform-password',

        // ajax urls
        'ajaxNotificationsSaveUrl': '/account/ajax-notifications-save',
        'ajaxPasswordSaveUrl':      '/account/ajax-password-save',
        'ajaxProfielSaveUrl':       '/account/ajax-profile-save',
        'ajaxTempAvatarUploadUrl':  '/account/temp-avatar-upload',
        'ajaxSaveAvatarUrl':        '/account/avatar-save',
        'ajaxDeleteAvatarUrl':      '/account/avatar-delete'
    };

    this.settings = $.extend({}, defaults, data);

    // commonly used selectors
    this.$avatarPopup         = $(this.settings.avatarPopup);
    this.$uploadContainer     = $(this.settings.uploadContainer);
    this.$previewContainer    = $(this.settings.previewContainer);
    this.$previewImg          = $(this.settings.previewImg);
    this.$cropHotspot         = $(this.settings.cropHotspot);

    this.$userTabs              = $(this.settings.userTabs);
    this.$notificationsForm     = $(this.settings.notificationsForm);
    this.$passwordForm          = $(this.settings.passwordForm);
    this.$profileForm           = $(this.settings.profileForm);
    this.$emailField            = $(this.settings.emailField);
    this.$emailConfirmFormGroup = $(this.settings.emailConfirmFormGroup);

    this.saveFormXHR     = null;
    this.saveAvatarXHR   = null;
    this.deleteAvatarXHR = null;

    this.hotspotsInst = null;

    this.init();
};

/**
 * Init method
 */
ProfileView.prototype.init = function () {
    var self      = this;
    var $document = $(document);

    /* Avatar
    --------------------------------------------------------------- */
    self.tempAvatarUpload();

    $document.off('click.profileview', self.settings.saveAvatarHandle);
    $document.on('click.profileview', self.settings.saveAvatarHandle, function (e) {
        e.preventDefault();

        self.saveAvatar();
    });

    $document.off('click.profileview', self.settings.deleteAvatarHandle);
    $document.on('click.profileview', self.settings.deleteAvatarHandle, function (e) {
        e.preventDefault();

        self.deleteAvatar();
    });

    self.initAvatarCrop();

    self.$previewImg.off('load.profileview');
    self.$previewImg.on('load.profileview', function () {
        self.repositionCrop();
    });

    self.$avatarPopup.off('popupOpen.profileview');
    self.$avatarPopup.on('popupOpen.profileview', function (e) {
        if (self.$previewImg.data('preview-url')) {
            // reset preview image
            self.$previewImg.attr('src', PR.nocacheUrl(self.$previewImg.data('preview-url'))).show();
            self.$uploadContainer.hide();
            self.$previewContainer.show();
        }

        self.repositionCrop();
    });

    /* User settings
    --------------------------------------------------------------- */
    self.$userTabs.tabs();
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
 * Init avatar crop container.
 */
ProfileView.prototype.initAvatarCrop = function () {
    this.hotspotsInst = new Hotspots({
        drawContainer: '.preview-image-wrapper',
        drawLayer:     '.preview-image',
        maxHotspots:   1,
        squareScale:   true,
        minWidth:      100,
        minHeight:     100
    });

    this.hotspotsInst.init();
}

/**
 * Sets default dimensions to the crop hotspot elem.
 */
ProfileView.prototype.repositionCrop = function () {
    var self = this;

    if (!self.$cropHotspot.length || !self.$previewImg.length) {
        return;
    }

    var ratio     = self.$previewImg[0].naturalWidth / self.$previewImg.width();
    var imgWidth  = self.$previewImg.width();
    var imgHeight = self.$previewImg[0].naturalHeight / ratio;

    var size, left, top;
    if (imgWidth < imgHeight) {
        size = imgWidth;
        left = 0;
        top = (imgHeight / 2) - (size / 2);
    } else {
        size = imgHeight;
        left = (imgWidth / 2) - (size / 2);
        top = 0;
    }

    self.$cropHotspot.css({
        'left':   left,
        'top':    top,
        'width':  size,
        'height': size
    });
};

/**
 * Handles temp avatar upload.
 */
ProfileView.prototype.tempAvatarUpload = function () {
    var self = this;

    Dropzone.autoDiscover = false;
    var myDropzone = new Dropzone(self.settings.uploadContainer, {
        url:                   self.settings.ajaxTempAvatarUploadUrl,
        paramName:             'AvatarForm[avatar]',
        uploadMultiple:        false,
        thumbnailWidth:        null,
        thumbnailHeight:       null,
        addRemoveLinkss:       false,
        createImageThumbnails: false,
        previewTemplate:       '<div style="display: none"></div>',
        acceptedFiles:         'image/*',
        maxFiles:              1,
        maxFilesize:           self.settings.maxUploadSize
    });

    myDropzone.on('sending', function (file, xhr, formData) {
        formData.append(yii.getCsrfParam(), yii.getCsrfToken());
        self.$uploadContainer.show().addClass('loading');
    });

    myDropzone.on('complete', function (file, xhr, formData) {
        self.$uploadContainer.removeClass('loading');
        myDropzone.removeAllFiles(true);
    });

    myDropzone.on('success', function (file, response) {
        if (response.success) {
            self.$uploadContainer.hide();
            self.$previewContainer.show();

            if (response.tempAvatarUrl) {
                self.$previewImg.attr('src', PR.nocacheUrl(response.tempAvatarUrl)).show();
            }
        }

        PR.addNotification(response.message, response.success ? 'success' : 'danger');
    });

    $(document).on('deleteAvatar', function (e) {
        myDropzone.removeAllFiles(true);
        self.$previewImg.attr('src', '').data('preview-url', '');
        self.$uploadContainer.show();
        self.$previewContainer.hide();
    });

    $(document).on('click.profileView', self.settings.previewRemoveHandle, function (e) {
        e.preventDefault();

        myDropzone.removeAllFiles(true);
        self.$previewImg.attr('src', '');
        self.$uploadContainer.show();
        self.$previewContainer.hide();
    });
};

/**
 * Saves avatar and generate thumb according to the crop dimensions.
 */
ProfileView.prototype.saveAvatar = function () {
    var self = this;

    var ratio = self.$previewImg.get(0).naturalWidth / self.$previewImg.width();
    var crop = {
        x: (self.$cropHotspot.position().left * ratio) || 0,
        y: (self.$cropHotspot.position().top * ratio)  || 0,
        w: self.$cropHotspot.width() * ratio,
        h: self.$cropHotspot.height() * ratio
    };

    var isTemp = self.$previewImg.attr('src').indexOf('avatar_temp.jpg') >= 0;

    PR.abortXhr(self.saveAvatarXHR);
    self.saveAvatarXHR = $.ajax({
        url: self.settings.ajaxSaveAvatarUrl,
        type: 'POST',
        data: {
            'crop':   crop,
            'isTemp': isTemp ? 1 : 0,
        },
    }).done(function (response) {
        if (response.success) {
            PR.closePopup(self.$avatarPopup);

            if (response.avatarUrl && response.avatarThumbUrl) {
                setTimeout(function() {
                    self.$previewImg.data('preview-url', response.avatarUrl);
                    $(self.settings.deleteAvatarHandle).show();
                    $(self.settings.avatarImg).show().attr('src', PR.nocacheUrl(response.avatarThumbUrl));
                }, 100); // animations delay
            }
        }
    });
};

/**
 * Delete avatar and its thumb via ajax.
 */
ProfileView.prototype.deleteAvatar = function () {
    var self = this;

    PR.abortXhr(self.deleteAvatarXHR);
    self.deleteAvatarXHR = $.ajax({
        url: self.settings.ajaxDeleteAvatarUrl,
        type: 'POST',
    }).done(function (response) {
        if (response.success) {
            PR.closePopup(self.$avatarPopup);

            $(self.settings.deleteAvatarHandle).hide();
            $(self.settings.avatarImg).attr('src', '');

            $(document).trigger('deleteAvatar');
        }
    });
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
        if (response.success) {
            PR.saveFormState($form);

            $form.removeClass('is-dirty');
        } else if (response.errors) {
            $form.yiiActiveForm('updateMessages', response.errors, true);
        }

        if (PR.isFunction(callback)) {
            callback(response);
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
            if (response.userIdentificator) {
                $(self.settings.userIdentificator).text(response.userIdentificator);
            }
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
