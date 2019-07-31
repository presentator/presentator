<template>
    <div class="popup"
        tabindex="-1"
        ref="popupContainer"
        :class="{ 'active': isActive, 'is-closing': isClosing }"
        @keyup.esc.self="closeOnEsc ? close() : true"
    >
        <div class="popup-overlay" @click.prevent="closeOnOverlay ? close() : true"></div>

        <div class="popup-panel">
            <span v-if="closeBtn" class="popup-close-handle popup-close" @click.prevent="close()"></span>

            <div class="popup-header">
                <slot name="header"></slot>
            </div>

            <div class="popup-content">
                <slot name="content"></slot>
            </div>

            <div class="popup-footer">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'popup',
    props: {
        closeOnEsc: {
            type:    Boolean,
            default: true,
        },
        closeOnOverlay: {
            type:    Boolean,
            default: true,
        },
        closeBtn: {
            type:    Boolean,
            default: true,
        },
    },
    data() {
        return {
            isActive:  false,
            isClosing: false,
        }
    },
    methods: {
        open() {
            this.isActive  = true;
            this.isClosing = false;

            this.$nextTick(() => {
                this.$refs.popupContainer.focus();
            });

            document.body.classList.add('popup-active');

            this.$nextTick(() => {
                // focus first found input
                let input = this.$el.querySelector('form input');
                if (input) {
                    input.focus();
                }
            });
        },
        close() {
            if (!this.isActive) {
                return; // already closed
            }

            this.isClosing = true;

            document.body.classList.remove('popup-active');

            if (this.closingTimeoutId) {
                clearTimeout(this.closingTimeoutId);
            }

            this.closingTimeoutId = setTimeout(() => {
                this.isActive  = false;
                this.isClosing = false;
            }, 300);
        },
    }
}
</script>
