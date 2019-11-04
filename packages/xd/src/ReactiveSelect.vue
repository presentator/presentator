<template>
    <select ref="select" @change="onSelectChange">
        <slot></slot>
    </select>
</template>

<script>
// Custom select wrapper component as a workaround for the XD select bug(s) and v-model reactivity
// (the default XD select doesn't update the model property on change)
module.exports = {
    name: 'reactive-select',
    props: ['value'],
    watch: {
        value(newVal, oldVal) {
            this.setSelectValue(newVal);
        }
    },
    mounted() {
        this.setSelectValue(this.value);
    },
    methods: {
        onSelectChange(e) {
            this.$emit('input', e.target.value);
        },
        setSelectValue(val) {
            if (this.setterTimeoutId) {
                clearTimeout(this.setterTimeoutId);
            }

            // slight delay to give enough time to the renderer before marking the related select option
            this.setterTimeoutId = setTimeout(() => {
                if (this.$refs.select) {
                    this.$refs.select.value = val;
                }
            }, 250);
        },
    },
}
</script>
