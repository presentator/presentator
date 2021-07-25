<template>
    <div v-if="clients.length" class="auth-clients-list">
        <div v-for="client in clients"
            tabindex="0"
            class="auth-client"
            :class="client.name"
            v-tooltip.bottom="client.title"
            @keydown.prevent.enter="openClientPopup(client)"
            @click.prevent="openClientPopup(client)"
        ></div>
    </div>
</template>

<script>
import AppConfig     from '@/utils/AppConfig';
import ClientStorage from '@/utils/ClientStorage';
import CommonHelper  from '@/utils/CommonHelper';

export default {
    name: 'auth-clients-bar',
    props: {
        clients: {
            type: Array,
            default: () => [],
        },
    },
    methods: {
        openClientPopup(client) {
            // store base client settings so that it can be read later by the redirect callback
            ClientStorage.setItem(AppConfig.get('VUE_APP_AUTH_CLIENT_NAME_STORAGE_KEY'), client.name);
            ClientStorage.setItem(AppConfig.get('VUE_APP_AUTH_CLIENT_STATE_STORAGE_KEY'), client.state);

            CommonHelper.openInWindow(client.authUrl);
        },
    }
}
</script>
