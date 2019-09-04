/**
 * Basic tooltip Vue directive as a plugin (register with `Vue.use()`).
 *
 * Example usage:
 * ```html
 * <div v-tooltip.top="'Top tooltip'">...</div>
 * <div v-tooltip.right="'Right tooltip'">...</div>
 * <div v-tooltip.bottom="'Bottom tooltip'">...</div>
 * <div v-tooltip.left="'Left tooltip'">...</div>
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */

let isScrollListenerBinded = false;

export default {
    install(Vue, options = {}) {
        let cachedTooltipElem, delayedHideTooltipTimeoutId;

        const getTooltipElem = function () {
            if (cachedTooltipElem) {
                return cachedTooltipElem;
            }

            cachedTooltipElem = document.getElementById('__tooltip_directive__');

            // create if missing
            if (!cachedTooltipElem) {
                cachedTooltipElem = document.createElement('div');
                cachedTooltipElem.setAttribute('class', 'tooltip-directive');
                cachedTooltipElem.setAttribute('id', '__tooltip_directive__');
                document.body.appendChild(cachedTooltipElem);
            }

            return cachedTooltipElem;
        };

        const showTooltip = function (e) {
            var text       = this.tooltipText || '';
            var position   = this.tooltipPosition || 'bottom';
            var elemBox    = this.getBoundingClientRect();
            var tooltip    = getTooltipElem();
            var hTolerance = 2;
            var vTolerance = 4;
            var left       = 0;
            var top        = 0;

            if (!text) {
                tooltip.classList.remove('active');
                return;
            }

            // set tooltip content
            tooltip.textContent = text;

            // activate tooltip to properly calculate its size
            tooltip.classList.add('active');

            // reset tooltip position
            tooltip.style.left = '0px';
            tooltip.style.top = '0px';

            // calculate new tooltip position
            if (position === 'top') {
                left = elemBox.left + (elemBox.width / 2) - (tooltip.offsetWidth / 2);
                top  = elemBox.top - tooltip.offsetHeight - vTolerance;
            } else if (position === 'left') {
                left = elemBox.left - tooltip.offsetWidth - hTolerance;
                top  = elemBox.top + (elemBox.height / 2) - (tooltip.offsetHeight / 2);
            } else if (position === 'right') {
                left = elemBox.left + elemBox.width + hTolerance;
                top  = elemBox.top + (elemBox.height / 2) - (tooltip.offsetHeight / 2);
            } else if (position === 'bottom') {
                left = elemBox.left + (elemBox.width / 2) - (tooltip.offsetWidth / 2);
                top  = elemBox.top + elemBox.height + vTolerance;
            } else { // follow
                left = e.pageX + 5;
                top  = e.pageY + 5;
            }

            // right edge boundary
            if ((left + tooltip.offsetWidth) > document.documentElement.clientWidth) {
                left = document.documentElement.clientWidth - tooltip.offsetWidth - 5;
            }

            // left edge boundary
            left = left >= 0 ? left : 0;

            // bottom edge boundary
            if ((top + tooltip.offsetHeight) > document.documentElement.clientHeight) {
                top = document.documentElement.clientHeight - tooltip.offsetHeight - 5;
            }

            // top edge boundary
            top = top >= 0 ? top : 0;

            // set new tooltip position
            tooltip.style.left = left + 'px';
            tooltip.style.top  = top + 'px';
        };

        const hideTooltip = function (e) {
            getTooltipElem().classList.remove('active');
        };

        // `hideTooltip` with slight delay
        // (used for touch events order firing workaround)
        const delayedHideTooltip = function (e) {
            if (delayedHideTooltipTimeoutId) {
                clearTimeout(delayedHideTooltipTimeoutId);
            }

            delayedHideTooltipTimeoutId = setTimeout(() => {
                hideTooltip();
            }, 250);
        };

        // bind only once
        if (!isScrollListenerBinded) {
            document.addEventListener('scroll', hideTooltip, {
                capture: true,
                passive: true,
            });

            isScrollListenerBinded = true;
        }

        // register directive
        Vue.directive('tooltip', {
            bind(el, binding, vnode, oldVnode) {
                if (binding.modifiers.follow) {
                    el.tooltipPosition = 'follow';
                } else if (binding.modifiers.top) {
                    el.tooltipPosition = 'top';
                } else if (binding.modifiers.left) {
                    el.tooltipPosition = 'left';
                } else if (binding.modifiers.right) {
                    el.tooltipPosition = 'right';
                } else {
                    el.tooltipPosition = 'bottom';
                }

                el.tooltipText = binding.value;

                el.addEventListener('mousemove', showTooltip);
                el.addEventListener('focusin', showTooltip);
                el.addEventListener('touchmove', showTooltip);
                el.addEventListener('mouseleave', hideTooltip);
                el.addEventListener('focusout', hideTooltip);
                el.addEventListener('mouseup', delayedHideTooltip);
            },
            update(el, binding, vnode, oldVnode) {
                if (binding.value != binding.oldValue) {
                    el.tooltipText = binding.value;
                }
            },
            unbind(el, binding, vnode, oldVnode) {
                el.removeEventListener('mousemove', showTooltip);
                el.removeEventListener('focusin', showTooltip);
                el.removeEventListener('mouseleave', hideTooltip);
                el.removeEventListener('focusout', hideTooltip);
                el.removeEventListener('mouseup', delayedHideTooltip);

                var tooltip = getTooltipElem();

                // active tooltip of the removed element
                if (
                    el &&
                    tooltip &&
                    tooltip.classList.contains('active') &&
                    (tooltip.textContent == el.tooltipText)
                ) {
                    hideTooltip();
                }
            },
        });
    },
}
