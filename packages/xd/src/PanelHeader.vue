<template>
    <div>
        <div class="panel-header">
            <h1><slot>Presentator Export</slot></h1>

            <a v-if="isLogged && email"
                class="color-red"
                :title="'You are currently login as ' + email"
                @click.prevent="$logout()"
            >Logout</a>
        </div>

        <hr/>

        <notifications />
    </div>
</template>

<script>
const storageHelper = require('xd-storage-helper');
const ApiClient     = require('@/utils/ApiClient.js');
const Notifications = require('@/Notifications.vue').default;

module.exports = {
    name: 'panel-header',
    components: {
        'notifications': Notifications,
    },
    data() {
        return {
            isLogged: false,
            email:    '',
        }
    },
    mounted() {
        this.loadStoredState();
    },
    methods: {
        async loadStoredState() {
            await ApiClient.loadStorageData();

            this.email    = await storageHelper.get('email', '');
            this.isLogged = await ApiClient.hasValidToken();
        },
    },
}
</script>
