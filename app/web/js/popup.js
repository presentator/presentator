/**
 * Very simple popup plugin accessible through the global PR object.
 *
 * @example
 * 1) Markup
 * ```
 * <div class="popup" data-overlay-close="true">
 *     <div class="popup-content">
 *         <h3 class="popup-title">My Popup title</h3>
 *         <span class="popup-close close-icon"></span>
 *         <div class="content">
 *             <p>Lorem ipsum...</p>
 *         </div>
 *     </div>
 * </div>
 * ```
 *
 * 2) Open/Close
 * - Via a HTML pointer
 * ```
 * <span data-popup="#my_popup">My popup</span>
 * ```
 *
 * - Programmatically
 * ```
 * // open
 * PR.openPopup('#my_popup');
 * // close
 * PR.closePopup('#my_popup');
 * ```
 *
 * @version 0.1
 * @author: Gani Georgiev <gani.georgiev@gmail.com>
 */
;(function($) {
    var $document = $(document);
    var $body     = $('body');

    PR = (typeof PR === 'object' ? PR : {});

    var isActive = false;

    function resetForms($popup) {
        var $popupForm = $popup.find('form');
        if (!$popupForm.length) {
            return;
        }

        $popupForm.get(0).reset();
        if ($popupForm.data('yiiActiveForm')) {
            $popupForm.yiiActiveForm('resetForm');
        }
    }

    PR.openPopup = function (popupSelector) {
        var $popup = $(popupSelector);
        if (!$popup.length) {
            console.warn(popupSelector + ' does not exist!');
            return;
        }

        $body.addClass('popup-active');
        $popup.addClass('active');

        $popup.trigger('popupOpen', [$popup]);
    }

    PR.closePopup = function (popupSelector, withDelay) {
        var $popup = $(popupSelector || '.popup.active');
        if (!$popup.length || $popup.hasClass('close-start') || !$popup.hasClass('active')) {
            // closing started or is already closed
            return;
        }

        withDelay = typeof withDelay !== 'undefined' ? withDelay : true;

        if (withDelay) {
            $popup.addClass('close-start').delay(750).queue(function(next) {
                $popup.removeClass('close-start active');
                $body.removeClass('popup-active');

                resetForms($popup);
                next();

                $popup.trigger('popupClose', [$popup]);
            });
        } else {
            $popup.removeClass('active');
            $body.removeClass('popup-active');

            resetForms($popup);
            $popup.trigger('popupClose', [$popup]);
        }
    }

    // Open handle
    $document.on('click', '[data-popup]', function(e) {
        e.preventDefault();

        PR.openPopup($(this).data('popup'));
    });

    // Close handle
    $document.on('click', '.popup-close', function(e) {
        e.preventDefault();
        e.stopPropagation();

        PR.closePopup($(this).closest('.popup'));
    });

    // Close on overlay click (only if `data-overlay-close` is not set or is not false)
    var $popupContent = null;
    $document.on('click', '.popup:not([data-overlay-close="false"])', function(e) {
        $popupContent = $(this).find('.popup-content');
        if (
            !$popupContent.is(e.target) &&
            !$popupContent.has(e.target).length
        ) {
            e.preventDefault();
            PR.closePopup();
        }
    });

    $document.on('click', '.popup:not([data-overlay-close="false"])', function(e) {
        $popupContent = $(this).find('.popup-content');
        if (
            !$popupContent.is(e.target) &&
            !$popupContent.has(e.target).length
        ) {
            e.preventDefault();
            PR.closePopup();
        }
    });

    // Keyboard shortcut to close an active popup with `esc` key
    $document.on('keydown', function(e) {
        if (PR.keys &&                                             // keys are defined
            e.which === PR.keys.esc &&                             // is `esc` key pressed
            $body.hasClass('popup-active') &&                      // has active popup
            !$(e.target).is(':input:not(:button)') &&              // is not form input
            $('.popup.active').find('.popup-close:visible').length // popup has visible close handle
        ) {
            e.preventDefault();

            PR.closePopup();
        }
    });

    // Auto open popup based on url hash value
    if (window.location.hash && $('.popup' + window.location.hash).length) {
        PR.openPopup(window.location.hash);
        window.location.hash = ''; // reset
    }
})(jQuery);
