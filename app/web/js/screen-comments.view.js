var ScreenCommentsView = function(data) {
    data = data || {};

    var defaults = {
        'unreadCommentsNotification': '.comments-notification',

        'versionSlider':     '.version-slider',
        'versionSliderItem': '.slider-item',

        'drawLayer':        '.hotspot-layer',
        'drawLayerWrapper': '.hotspot-layer-wrapper',

        'commentPopover':          '#comment_popover',
        'commentForm':             '#comment_form',
        'commentFormFromInput':    '#comment_form_from_input',
        'commentFormMessageInput': '#comment_form_message_input',
        'commentsList':            '#comments_list',
        'commentsListItem':        '.comment',
        'commentTarget':           '.comment-target',
        'commentDeleteHandle':     '.comment-delete',
        'commentTargetsList':      '#comment_targets_list',
        'commentsCounter':         '.comments-counter',

        'ajaxCommentCreateUrl': '/screen-comments/ajax-create',
        'ajaxCommentDeleteUrl': '/screen-comments/ajax-delete',
        'ajaxCommentsListUrl':  '/screen-comments/ajax-get-comments',

        'confirmCommentTargetDeleteText': 'Do you really want to delete the selected comment target and all its replies?',
        'confirmCommentReplyDeleteText': 'Do you really want to delete the comment reply?',
    };

    this.settings = $.extend({}, defaults, data);

    this.generalXHR = null;

    this.pinsInst = new Pins({
        pinClass:      this.settings.commentTarget,
        layer:         this.settings.drawLayer,
        layerWrapper:  this.settings.drawLayerWrapper,
        appendWrapper: this.settings.commentTargetsList
    });

    this.init();
};

/**
 * Init method
 */
ScreenCommentsView.prototype.init = function() {
    var self = this;

    var $document = $(document);
    var $body     = $('body');

    // Comment target draw
    $document.on('created.pins', function(e, $target) {
        $target.data('isNew', true).addClass('new');

        self.deselectCommentTarget();

        self.selectCommentTarget($target);
    });

    // Comment target drag/move
    $document.on('dragEnd.pins', function(e, $target) {
        self.repositionPopover($target);
    });

    // Comment target click
    $document.on('clicked.pins', function(e, $target) {
        self.selectCommentTarget($target);
    });

    // Comment target click
    $document.on('removeEnd.pins', function(e, $target) {
        self.deselectCommentTarget();
        self.updateCommentsCounter();
    });

    // Delete comment
    $document.on('click', self.settings.commentDeleteHandle, function(e) {
        e.preventDefault();

        var $item = $(this).closest('[data-comment-id]');
        var isPrimaryComment = $(self.settings.commentTarget + '[data-comment-id="' + $item.data('comment-id') + '"]');

        var allowDelete = isPrimaryComment ?
            window.confirm(self.settings.confirmCommentTargetDeleteText) : window.confirm(self.settings.confirmCommentReplyDeleteText);

        if (allowDelete) {
            self.removeComment($item.data('comment-id'));
        }
    });

    // Comment form submit
    $document.on('submit', self.settings.commentForm, function(e) {
        e.preventDefault();
        var $form          = $(this);
        var $fromInput     = $form.find(self.settings.commentFormFromInput);
        var $messageInput  = $form.find(self.settings.commentFormMessageInput);
        var $activeComment = self.getActiveScreenSliderItem().find(self.settings.commentTarget + '.selected');

        if (!$activeComment.length) {
            console.warn('Missing active comment target!');
            return false;
        }

        if ($messageInput.length && !$messageInput.val().length) {
            $messageInput.addClass('has-error');
            return false;
        }

        if ($fromInput.length && !$fromInput.val().length) {
            $fromInput.addClass('has-error');
            return false;
        }

        $messageInput.removeClass('has-error');
        $fromInput.removeClass('has-error');

        if ($activeComment.data('isNew')) {
            self.createCommentTarget($activeComment, $messageInput.val(), $fromInput.val(), null, function(response) {
                if (response.success) {
                    if ($fromInput.length) {
                        $fromInput.attr('value', $fromInput.val());
                    }

                    $form.get(0).reset();
                }
            });
        } else {
            self.createCommentReply($activeComment.data('comment-id'), $messageInput.val(), $fromInput.val(), function(response) {
                if (response.success) {
                    if ($fromInput.length) {
                        $fromInput.attr('value', $fromInput.val());
                    }

                    $form.get(0).reset();
                }
            });
        }

        return false;
    });

    // Deselect on ouside click
    var $activeComment;
    $document.on('mousedown touchend', function(e) {
        if ($body.hasClass('comment-active')) {
            $activeComment = self.getActiveScreenSliderItem().find(self.settings.commentTarget + '.selected');

            if (
                $activeComment.length &&
                !$(self.settings.commentPopover).is(e.target) &&
                !$(self.settings.commentPopover).has(e.target).length &&
                !$activeComment.is(e.target) &&
                !$activeComment.has(e.target).length
            ) {
                e.preventDefault();

                self.deselectCommentTarget();

                if ($activeComment.data('isNew')) {
                    self.pinsInst.removePin($activeComment);
                }
            }
        }
    });

    // Deselect on esc
    $document.on('keydown', function(e) {
        if (e.which == PR.keys.esc && $body.hasClass('comment-active')) {
            $activeComment = self.getActiveScreenSliderItem().find(self.settings.commentTarget + '.selected');
            self.deselectCommentTarget();

            if ($activeComment.data('isNew')) {
                self.pinsInst.removePin($activeComment);
            }
        };
    });

    $(window).on('resize', function() {
        if ($body.hasClass('comment-active')) {
            self.repositionPopover();
        }
    });
};

/**
 * Returns the current active screen slider item element.
 * @return {jQuery}
 */
ScreenCommentsView.prototype.getActiveScreenSliderItem = function() {
    return $(this.settings.versionSlider).find(this.settings.versionSliderItem + '.active');
};

/**
 * Repositions comments list popover.
 * @param {null|Object} item
 */
ScreenCommentsView.prototype.repositionPopover = function(item) {
    var $item    = item ? $(item) : this.getActiveScreenSliderItem().find(this.settings.commentTarget + '.selected');

    PR.repositionPopover($item, this.settings.commentPopover, '.version-slider-content');
};

/**
 * Creates a new commen target via ajax
 * @param {Object}      target
 * @param {String}      message
 * @param {String}      from
 * @param {Function}    callback
 * @param {null|Number} screenId Attach the comment to a specific screen.
 */
ScreenCommentsView.prototype.createCommentTarget = function(target, message, from, screenId, callback) {
    var self = this;

    var $screen;
    if (!screenId) {
        $screen  = self.getActiveScreenSliderItem();
        screenId = $screen.data('screen-id');
    } else {
        $screen = $(self.settings.versionSliderItem + '[data-screen-id="' + screenId + '"]');
    }

    var $target = $(target);

    var scaleFactor = $screen.data('scale-factor') || 1;

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url: self.settings.ajaxCommentCreateUrl,
        type: 'POST',
        data: {
            'posX':     $target.position().left * scaleFactor,
            'posY':     $target.position().top * scaleFactor,
            'screenId': screenId,
            'message':  message,
            'from':     from
        }
    }).done(function(response) {
        if (response.success && response.comment) {
            PR.setData($target, 'comment-id', response.comment.id);

            $target.removeClass('new').data('isNew', false);
            self.deselectCommentTarget();
            self.updateCommentsCounter();
        }

        if (PR.isFunction(callback)) {
            callback(response);
        }
    });
};

/**
 * Creates a comment reply via ajax.
 * @param {Object}   target
 * @param {String}   message
 * @param {String}   from
 * @param {Function} callback
 */
ScreenCommentsView.prototype.createCommentReply = function(replyTo, message, from, callback) {
    var self = this;

    if (!replyTo) {
        console.warn('Missing comment reply id');
        return;
    }

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url: self.settings.ajaxCommentCreateUrl,
        type: 'POST',
        data: {
            'replyTo': replyTo,
            'message': message,
            'from':    from
        }
    }).done(function(response) {
        if (response.success && response.commentsListHtml) {
            var $commentsList = $(self.settings.commentPopover).find(self.settings.commentsList);
            $commentsList.html(response.commentsListHtml);
            $commentsList.stop(true, true).animate({
                'scrollTop': $commentsList.scrollTop() + $commentsList.children(self.settings.commentsListItem).last().position().top
            }, 400);
        }

        if (PR.isFunction(callback)) {
            callback(response);
        }
    });
};

/**
 * Removes a single comment (target or reply) via ajax.
 * @param {Number}   commentId
 * @param {Function} callback
 */
ScreenCommentsView.prototype.removeComment = function(commentId, callback) {
    var self = this;

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url: self.settings.ajaxCommentDeleteUrl,
        type: 'POST',
        data: {
            'id': commentId,
        }
    }).done(function(response) {
        if (response.success) {
            var $target = $(self.settings.commentTarget + '[data-comment-id="' + commentId + '"]');
            if ($target.length) {
                // is primary comment
                self.pinsInst.removePin($target);
            } else {
                // is reply
                $(self.settings.commentsList).find(self.settings.commentsListItem + '[data-comment-id="' + commentId + '"]').remove();
            }

            self.updateUnreadCommentsNotification();
        }

        if (PR.isFunction(callback)) {
            callback(response);
        }
    });
};

/**
 * Marks a comment target/pin as selected.
 * @param {Mixed} target
 * @param {Mixed} commentListItem Id or comment element to auto scroll to.
 */
ScreenCommentsView.prototype.selectCommentTarget = function (target, scrollToComment) {
    var self = this;

    var $target = $(target);
    if (!$target.length || $target.hasClass('selected')) {
        return;
    }

    var select = function() {
        $('body').addClass('comment-active');
        $target.addClass('selected').removeClass('unread');

        self.ensureTargetIsVisible($target);

        self.updateUnreadCommentsNotification();

        self.repositionPopover($target);

        // scroll to specific comment list item
        if (scrollToComment) {
            var $listItem;
            if (!isNaN(scrollToComment)) {
                $listItem = $(self.settings.commentsListItem + '[data-comment-id="' + scrollToComment + '"]');
            } else {
                $listItem = $(scrollToComment);
            }

            if ($listItem.length) {
                setTimeout(function() {
                    $(self.settings.commentPopover).find(self.settings.commentsList)
                        .scrollTop($listItem.position().top);

                    $listItem.addClass('focus').delay(1500).queue(function(next) {
                        $listItem.removeClass('focus');

                        next();
                    });
                }, 300);
            }
        }
    };

    if ($target.data('isNew')) {
        select();
    } else {
        self.loadCommentsList($target.data('comment-id'), function (response) {
            if (response.success) {
                select();
            }
        });
    }
};

/**
 * Deselects active comment target.
 * @param {Mixed} commentTarget
 */
ScreenCommentsView.prototype.deselectCommentTarget = function (commentTarget) {
    var self = this;

    var $popover = $(self.settings.commentPopover);
    var $target  = $(commentTarget || self.settings.commentTarget + '.selected')
        .removeClass('selected');

    if ($popover.is(':visible')) {
        $popover.addClass('close-start').stop(true, true).delay(300).queue(function(next) {
            $popover.removeClass('close-start');
            $popover.find(self.settings.commentsList).empty();
            $popover.find(self.settings.commentForm).get(0).reset();

            next();
        });
    }

    $('body').removeClass('comment-active');

    if ($target.data('isNew') == true) {
        self.pinsInst.removePin($target);
    }
};

/**
 * Loads primary comment and its replies via ajax.
 * @param {Number}   primaryCommentId
 * @param {Function} callback
 */
ScreenCommentsView.prototype.loadCommentsList = function(primaryCommentId, callback) {
    var self = this;

    PR.abortXhr(self.generalXHR);
    self.generalXHR = $.ajax({
        url: self.settings.ajaxCommentsListUrl,
        type: 'GET',
        data: {
            'commentId': primaryCommentId,
        }
    }).done(function(response) {
        if (response.success && response.commentsListHtml) {
            var $commentsList = $(self.settings.commentPopover).find(self.settings.commentsList);

            $commentsList.html(response.commentsListHtml);

            $commentsList.stop(true, true).animate({
                'scrollTop': $commentsList.get(0).scrollHeight
            }, 300);
        }

        if (PR.isFunction(callback)) {
            callback(response);
        }
    });
};

/**
 * Makes sure that a specific comment target is in the visible viewport.
 * @param {Mixed} target
 */
ScreenCommentsView.prototype.ensureTargetIsVisible = function (target) {
    var self = this;

    // normalize target parameter
    var $target    = $(target);
    target         = $target.get(0);

    if (!$target.length) {
        return;
    }

    var targetTop     = 0;
    var targetLeft    = 0;
    var $scrollParent = $target.closest(self.settings.versionSliderItem);

    if ($scrollParent.length) {
        targetTop  = $target.position().top;
        targetLeft = $target.position().left;

        // scroll overflow should be enable to be able to use `.scrollTop()` and `.scrollLeft()`
        $scrollParent.css('overflow', 'auto');

        // horizontal
        if (
            ($scrollParent.scrollLeft() > targetLeft) ||
            ($scrollParent.width() + $scrollParent.scrollLeft() < targetLeft)
        ) {
            $scrollParent.scrollLeft(targetLeft - ($scrollParent.width() / 2));
        }

        // vertical
        if (
            ($scrollParent.scrollTop() > targetTop) ||
            ($scrollParent.height() + $scrollParent.scrollTop() < targetTop)
        ) {
            $scrollParent.scrollTop(targetTop - ($scrollParent.height() / 2));
        }

        // reset overflow
        $scrollParent.css('overflow', '');
    }
};


/**
 * Updates comment targets counters for the current active screen slider.
 */
ScreenCommentsView.prototype.updateCommentsCounter = function() {
    var total = this.getActiveScreenSliderItem().find(this.settings.commentTarget).length;

    $(this.settings.commentsCounter).text(total);
};

/**
 * Updats all screen unread comments notification containers.
 * @param {Number} screenId Updates a specific screen element (active screen slider item on default)
 */
ScreenCommentsView.prototype.updateUnreadCommentsNotification = function(screenId) {
    var self = this;
    var $screen;
    if (screenId) {
        $screen = $(self.settings.versionSliderItem + '[data-screen-id="' + screenId + '"]');
    } else {
        $screen = self.getActiveScreenSliderItem();
    }

    var totalUnread = $screen.find(self.settings.commentTarget + '.unread').length;

    if (totalUnread > 0) {
        $('[data-screen-id="' + $screen.data('screen-id') + '"]').find(self.settings.unreadCommentsNotification).show();
    } else {
        $('[data-screen-id="' + $screen.data('screen-id') + '"]').find(self.settings.unreadCommentsNotification).hide();
    }
};

/**
 * Enables comment targets/pins actions.
 */
ScreenCommentsView.prototype.enable = function() {
    this.pinsInst.enable('create');
};

/**
 * Disables comment targets/pins actions.
 */
ScreenCommentsView.prototype.disable = function() {
    this.pinsInst.disable();
};

