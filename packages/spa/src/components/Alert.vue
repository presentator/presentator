<template>
    <div class="alert" v-show="isActive">
        <span class="close-handle" v-if="closeBtn" @click.prevent="hide()"></span>

        <div class="content">
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'alert',
    props: {
        closeTimeout: {
            type:    Number,
            default: 5000,
        },
        closeBtn: {
            type:    Boolean,
            default: true,
        },
        visibility: {
            type:    Boolean,
            default: true,
        },
    },
    data() {
        return {
            isActive: false,
        }
    },
    watch: {
        visibility: function (val, oldVal) {
            if (val) {
                this.show();
            } else {
                this.hide();
            }
        }
    },
    mounted() {
        if (this.visibility) {
            this.show();
        } else {
            this.hide();
        }
    },
    methods: {
        hide() {
            this.isActive = false;

            if (this.closeTimeoutId) {
                clearTimeout(this.closeTimeoutId);
            }
        },
        show() {
            this.isActive = true;

            if (this.closeTimeout > 0) {
                if (this.closeTimeoutId) {
                    clearTimeout(this.closeTimeoutId);
                }

                this.closeTimeoutId = setTimeout(() => {
                    this.hide();
                }, this.closeTimeout);
            }
        },
    },
}
</script>
