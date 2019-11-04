<template>
    <div class="alerts-list">
        <div v-for="item in items"
            :key="item.message"
            class="alert color-white"
            :class="{
                'background-blue':   (item.type === 'info'),
                'background-orange': (item.type === 'warning'),
                'background-red':    (item.type === 'error'),
                'background-green':  (item.type === 'success'),
            }"
        >
            {{ item.message }}

            <div class="close" @click.prevent="remove(item.message)">&#11198;</div>
        </div>
    </div>
</template>

<script>
const events = require('@/utils/EventsBus');

module.exports = {
    name: 'notifications',
    data() {
        return {
            items: {},
        }
    },
    mounted() {
        events.$on('add', this.add);
    },
    methods: {
        add(message, type = 'info', duration = 5000) {
            var item = this.items[message] || {};

            item.message  = message;
            item.type     = type;
            item.duration = duration;

            if (item.closeTimeoutId) {
                clearTimeout(item.closeTimeoutId);
            }
            item.closeTimeoutId = null;

            this.$set(this.items, item.message, item);

            if (item.duration > 0) {
                item.closeTimeoutId = setTimeout(() => {
                    this.remove(item.message);
                }, item.duration);
            }
        },
        remove(message) {
            if (!this.items[message]) {
                return;
            }

            if (this.items[message].closeTimeoutId) {
                clearTimeout(this.items[message].closeTimeoutId);
            }

            this.$delete(this.items, message);
        },
    },
}
</script>
