<template>
    <component :is="tag" :class="{'active': isActive}"
        v-outside-click="{
            'status':  hideOnOutsideClick && isActive,
            'handler': triggerHide,
        }"
    >
        <slot></slot>
    </component>
</template>

<script>
import CommonHelper from '@/utils/CommonHelper';

export default {
    name: 'toggler',
    props: {
        trigger: {}, // selector or dom element
        tag: {
            type:    String,
            default: 'div',
        },
        hideOnOutsideClick: {
            type: Boolean,
            default: true,
        },
        hideOnChildClick: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            isActive: false,
        }
    },
    computed: {
        triggerElem() {
            if (!this.trigger) {
                return this.$el.parentNode;
            }

            if (CommonHelper.isString(this.trigger)) {
                return document.querySelector(this.trigger) || this.$el.parentNode;
            }

            return this.trigger;
        },
    },
    mounted() {
        if (this.triggerElem) {
            this.triggerElem.addEventListener('click', this.triggerToggle);
        }
    },
    beforeDestroy() {
        if (this.triggerElem) {
            this.triggerElem.removeEventListener('click', this.triggerToggle);
        }
    },
    methods: {
        hide() {
            this.isActive = false;

            if (this.triggerElem) {
                this.triggerElem.classList.remove('active');
            }

            this.$emit('hide');
        },
        show() {
            this.isActive = true;

            if (this.triggerElem) {
                this.triggerElem.classList.add('active');
            }

            this.$emit('show');
        },
        toggle() {
            if (this.isActive) {
                this.hide();
            } else {
                this.show();
            }
        },
        triggerHide(e) {
            if (this.isActive) {
                this.hide();
            }
        },
        triggerToggle(e) {
            if (
                e.target &&
                this.$el &&
                (this.$el === e.target || this.$el.contains(e.target))
            ) {
                // hide dropdown on child click
                if (this.hideOnChildClick) {
                    setTimeout(() => {
                       this.hide();
                    }, 0);
                }
            } else {
                this.toggle();
            }
        },
    }
}
</script>
