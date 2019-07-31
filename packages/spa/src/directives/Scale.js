import CommonHelper from '@/utils/CommonHelper';

/**
 * Vue directive to scale image tags (register with `Vue.use()`).
 *
 * Example usage:
 * ```html
 * // Set twice its original size
 * <img src="my-image.png" v-scale="2">
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default {
    install(Vue, options = {}) {
        const scale = function (el, scaleFactor) {
            if (!el.src || el.tagName != 'IMG') {
                return;
            }

            // reset
            el.scaledSrc    = el.src;
            el.style.width  = 'auto';
            el.style.height = 'auto';

            if (scaleFactor == 0) {
                el.style['max-width'] = '100%';
            } else {
                el.style['max-width'] = '';
            }

            CommonHelper.loadImage(el.src).then((data) => {
                if (data.success && data.width > 0 && el) {
                    let width = scaleFactor > 0 ? (data.width * scaleFactor) : data.width;

                    el.style.width = width + 'px';
                }
            });
        }

        // register directive
        Vue.directive('scale', {
            bind(el, binding, vnode, oldVnode) {
                scale(el, binding.value);
            },
            update(el, binding, vnode, oldVnode) {
                if (
                    binding.value != binding.oldValue || // scale factor change
                    el.scaledSrc != el.src               // src change
                ) {
                    scale(el, binding.value);
                }
            },
        });
    }
}
