import CommonHelper from '@/utils/CommonHelper';

/**
 * Simple outside click Vue directive as a plugin (register with `Vue.use()`).
 *
 * Example usage:
 * ```html
 * <div v-outside-click="{
 *     'handler': myClickHandler,
 *     'status':  myStatusVar, // if true, myClickHandler will be called on outside click
 * }">
 *     ...
 * </div>
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default {
    install(Vue, options = {}) {
        const outsideClickCall = function (e, el, callback) {
            if (
                el &&
                el._outsideClickStatus &&
                e.target &&
                el !== e.target &&
                !el.contains(e.target) &&
                typeof callback === 'function'
            ) {
                callback();
            }
        };

        // register directive
        Vue.directive('outside-click', {
            bind(el, binding, vnode, oldVnode) {
                el._outsideClickStatus = CommonHelper.getNestedVal(binding.value, 'status', false);

                // flag to detect whether the click started outside the element
                // used to prevent closing on cursor selection/dragging
                el._outsideMousedownHandler = function (e) {
                    outsideClickCall(e, el, () => {
                        el._outsideMouseDown = true;
                    });
                };

                el._outsideClickHandler = function (e) {
                    if (el._outsideMouseDown) {
                        el._outsideMouseDown = false;

                        outsideClickCall(e, el, CommonHelper.getNestedVal(binding.value, 'handler', false));
                    }
                };

                document.addEventListener('mousedown', el._outsideMousedownHandler);
                document.addEventListener('click', el._outsideClickHandler);
            },
            update(el, binding, vnode, oldVnode) {
                var status    = CommonHelper.getNestedVal(binding.value, 'status', false);
                var oldStatus = CommonHelper.getNestedVal(binding.oldValue, 'status', false);

                if (status != oldStatus) {
                    el._outsideClickStatus = status;
                    el._outsideMouseDown = false;
                }

            },
            unbind(el, binding, vnode, oldVnode) {
                document.removeEventListener('mousedown', el._outsideMousedownHandler);
                document.removeEventListener('click', el._outsideClickHandler);
            },
        });
    }
}
