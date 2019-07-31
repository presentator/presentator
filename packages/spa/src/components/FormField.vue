<template>
    <div class="form-group" :class="{'has-error': hasVisibleError}">
        <slot></slot>

        <div class="help-block help-block-error" v-show="showErrorMsg && hasVisibleError">{{ error }}</div>
    </div>
</template>

<script>
export default {
    name: 'form-field',
    props: {
        // used as error key
        name: {
            type:     String,
            required: true,
        },
        showErrorMsg: {
            type:    Boolean,
            default: true,
        },
    },
    data() {
        return {
            hideError: false,
        }
    },
    computed: {
        errors() {
            return this.$store.state['form-field'].errors;
        },
        error() {
            return this.$store.getters['form-field/getError'](this.name) || '';
        },
        hasVisibleError() {
            return !this.hideError && this.error.length;
        },
    },
    watch: {
        errors(newVal, oldVal) {
            this.hideError = false; // reset
        }
    },
    mounted() {
        const onChange = () => {
            this.hideError = true;
        };

        this.$el.addEventListener('change', onChange);

        this.$once('hook:beforeDestroy', () => {
            this.$el.removeEventListener('change', onChange);
        });
    },
}
</script>
