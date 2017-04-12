/**
 * Very simple context menu plugin accessible through the global PR object.
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
    var $document                    = $(document);
    var $window                      = $(window);
    var $body                        = $('body');
    var $globalMenuWrapper           = $('#global_context_menu_wrapper');
    var activeGlobalMenuWrapperClass = 'active';
    var activeMenuClass              = 'active';
    var activeBodyClass              = 'context-menu-active';
    var activeRelItemClass           = 'context-menu-item-active';
    var ignoreContextClass           = 'context-menu-ignore';

    PR = (typeof PR === 'object' ? PR : {});

    /**
     * Shows and positions the context menu.
     * @param {Number} cursorX
     * @param {Number} cursorY
     * @param {Mixed}  relItem
     */
    PR.openContextMenu = function(cursorX, cursorY, relItem) {
        var $relItem = $(relItem);
        var $menu    = $($relItem.data('context-menu')).first();

        if (!$menu.length) {
            return;
        }

        $menu.trigger('contextMenuOpenStart', [$relItem, $menu]);

        $globalMenuWrapper.append($menu);

        $globalMenuWrapper.addClass(activeGlobalMenuWrapperClass);
        $menu.addClass(activeMenuClass);
        $body.addClass(activeBodyClass);
        $relItem.addClass(activeRelItemClass);

        var posX       = 0;
        var posY       = 0;
        var menuWidth  = $menu.outerWidth();
        var menuHeight = $menu.outerHeight();

        // calculate horizontal menu position
        if (cursorX + menuWidth < $window.width()) {
            // position to the right
            posX = cursorX;
        } else {
            // position to the left
            posX = cursorX - menuWidth;
        }

        // calculate vertical menu position
        if (cursorY + menuHeight < $window.height()) {
            // position to the bottom
            posY = cursorY;
        } else {
            // position to the top
            posY = cursorY - menuHeight;
        }

        $menu.css({
            'left': posX,
            'top':  posY
        })

        $menu.trigger('contextMenuOpenEnd', [$relItem, $menu]);
    };

    /**
     * Hides active context menu.
     * @param {Mixed} relItem
     */
    PR.closeContextMenu = function(relItem) {
        var $relItem = $(relItem || '.' + activeRelItemClass);
        var $menu    = $($relItem.data('context-menu')).first();

        if (!$menu.length || !$menu.hasClass(activeMenuClass)) {
            return;
        }

        $menu.trigger('contextMenuCloseStart', [$relItem, $menu]);

        $globalMenuWrapper.removeClass(activeGlobalMenuWrapperClass);
        $menu.removeClass(activeMenuClass);
        $relItem.removeClass(activeRelItemClass);
        $body.removeClass(activeBodyClass);

        $menu.trigger('contextMenuCloseEnd', [$relItem, $menu]);
    };

    // Create global context menu wrapper if doesn't exist
    if (!$globalMenuWrapper.length) {
        $body.append('<div id="global_context_menu_wrapper" class="global-context-menu-wrapper"></div>');
        $globalMenuWrapper = $('#global_context_menu_wrapper')
    }

    // Open context menu
    $document.off('contextmenu.prContextMenu', '[data-context-menu]');
    $document.on('contextmenu.prContextMenu', '[data-context-menu]', function(e) {
        if (
            !$(e.target).hasClass(ignoreContextClass) &&
            !$(this).find('.' + ignoreContextClass).find(e.target).length
        ) {
            e.preventDefault();
            PR.openContextMenu(e.pageX, e.pageY, this);
        }
    });

    // Hide context menu
    $document.on('click', '#global_context_menu_wrapper', function(e) {
        e.preventDefault();

        PR.closeContextMenu();
    });

    // Disable default context menu
    $document.on('contextmenu', '#global_context_menu_wrapper', function(e) {
        PR.closeContextMenu();

        return false;
    });
})(jQuery);
