import CommonHelper from '@/utils/CommonHelper';

/**
 * Global keyboard shortcut directive as a plugin (register with `Vue.use()`).
 * Supports basic key modifiers - ctrl, shift, alt and meta.
 *
 * NB! The shortcut is binded in a "undisrupted" manner and it is not
 * triggered on input typing or while popups, popovers and dropdowns are opened.
 *
 * Example usage for "CTRL + Enter" shortcut:
 * ```html
 * <div v-shortcut.ctrl.13="someMethod">...</div>
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default {
    install(Vue, options = {}) {
        const shortcutHandler = function (e, el, binding) {
            binding = binding || {};

            if (
                binding.modifiers[e.which || e.keyCode] &&
                (!binding.modifiers.ctrl || e.ctrlKey) &&
                (!binding.modifiers.alt || e.altKey) &&
                (!binding.modifiers.shift || e.shiftKey) &&
                (!binding.modifiers.meta || e.metaKey) &&
                CommonHelper.isFunction(binding.value) &&
                (!e.target || !CommonHelper.isFormField(e.target)) &&
                !document.querySelector('.popover.active, .popup.active, .dropdown.active') &&
                (!document.activeElement || !document.activeElement.isContentEditable)
            ) {
                e.preventDefault();

                binding.value(e, el);
            }
        };

        const normalizeFunctionName = function (name) {
            return name.replace(/\W/g, '_');
        }

        // register directive
        Vue.directive('shortcut', {
            bind(el, binding, vnode, oldVnode) {
                let handlerName = normalizeFunctionName(binding.rawName);

                el[handlerName] = function (e) {
                    shortcutHandler(e, el, binding);
                };

                document.addEventListener('keydown', el[handlerName]);
            },
            unbind(el, binding, vnode, oldVnode) {
                document.removeEventListener('keydown', el[normalizeFunctionName(binding.rawName)]);
            },
        });
    }
}
