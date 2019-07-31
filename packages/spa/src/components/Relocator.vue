<template>
    <div>
        <div ref="slotWrapper">
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'relocator',
    props: {
        container: {
            default: 'body' // string or node
        },
    },
    computed: {
        containerElem() {
            if (typeof this.container === 'string') {
                return document.querySelector(this.container);
            }

            return this.container;
        }
    },
    mounted() {
        if (this.containerElem) {
            let content = this.$refs.slotWrapper;

            content.parentNode.removeChild(content);
            this.containerElem.appendChild(content);

            // revert changes
            this.$once('hook:beforeDestroy', () => {
                if (content) {
                    content.parentNode.removeChild(content);
                    this.$el.appendChild(content);
                }
            });
        }
    },
}
</script>
