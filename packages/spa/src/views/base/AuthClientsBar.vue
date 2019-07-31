<template>
    <div v-if="!isLoadingClients && clientsList.length"
        class="auth-clients-bar"
    >
        <div v-if="heading" class="heading">{{ heading }}</div>

        <div class="auth-clients-list">
            <div v-for="client in clientsList"
                tabindex="0"
                class="auth-client"
                :class="client.name"
                v-tooltip.bottom="client.title"
                @keydown.prevent.enter="openClientPopup(client)"
                @click.prevent="openClientPopup(client)"
            ></div>
        </div>
    </div>
</template>

<script>
import AppConfig     from '@/utils/AppConfig';
import ClientStorage from '@/utils/ClientStorage';
import CommonHelper  from '@/utils/CommonHelper';
import ApiClient     from '@/utils/ApiClient';
import AuthClient    from '@/models/AuthClient';

export default {
    name: 'auth-clients-bar',
    props: {
        heading: {
            type: String,
        },
    },
    data() {
        return {
            isLoadingClients: false,
            clientsList:      [],
        }
    },
    mounted() {
        this.loadClients();
    },
    methods: {
        loadClients() {
            if (this.isLoadingClients) {
                return;
            }

            this.isLoadingClients = true;

            ApiClient.Users.getAuthClients().then((response) => {
                this.clientsList = AuthClient.createInstances(response.data);
            }).catch((err) => {
                // silence errors...
            }).finally(() => {
                this.isLoadingClients = false;
            });
        },
        openClientPopup(client) {
            // store base client settings so that it can be read later by the redirect callback
            ClientStorage.setItem(AppConfig.get('VUE_APP_AUTH_CLIENT_NAME_STORAGE_KEY'), client.name);
            ClientStorage.setItem(AppConfig.get('VUE_APP_AUTH_CLIENT_STATE_STORAGE_KEY'), client.state);

            CommonHelper.openInWindow(client.authUrl);
        },
    }
}
</script>
