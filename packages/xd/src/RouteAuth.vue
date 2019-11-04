<template>
    <form class="panel" method="dialog" @submit.prevent="authorize()">
        <panel-header></panel-header>

        <p v-if="isLoadingDefaults">Loading....</p>

        <template v-else>
            <div class="row">
                <label class="block-field">
                    <span>App URL</span>
                    <input v-model="appUrl" type="text" placeholder="eg. https://app.presentator.io" />
                </label>

                <label class="block-field">
                    <span>Api URL</span>
                    <input v-model="apiUrl" type="text" placeholder="eg. https://app.presentator.io/api" />
                </label>
            </div>

            <hr class="small">

            <label class="block-field">
                <span>Email</span>
                <input v-model="email" type="text" autofocus />
            </label>

            <label class="block-field">
                <span>Password</span>
                <input ref="passwordInput" v-model="password" type="password" />
            </label>
        </template>

        <footer>
            <button uxp-variant="primary" @click.prevent="$closePluginDialog()">Close</button>
            <button type="submit" uxp-variant="cta" :disabled="isAuthorizing">Authorize</button>
        </footer>
    </form>
</template>

<script>
const storageHelper = require('xd-storage-helper');
const ApiClient     = require('@/utils/ApiClient');
const PanelHeader   = require('@/PanelHeader.vue').default;

module.exports = {
    name: 'route-auth',
    components: {
        'panel-header': PanelHeader,
    },
    data() {
        return {
            isAuthorizing:     false,
            isLoadingDefaults: false,
            appUrl:            '',
            apiUrl:            '',
            email:             '',
            password:          '',
        }
    },
    mounted() {
        this.loadDefaults();
    },
    methods: {
        async loadDefaults() {
            this.isLoadingDefaults = true;

            this.appUrl   = await storageHelper.get('appUrl', 'https://app.presentator.io');
            this.apiUrl   = await storageHelper.get('apiUrl', 'https://app.presentator.io/api');
            this.email    = await storageHelper.get('email', '');
            this.password = '';

            this.isLoadingDefaults = false;

            // focus the password input if an email is already typed
            this.$nextTick(() => {
                if (this.email && this.$refs.passwordInput) {
                    this.$refs.passwordInput.focus();
                }
            });
        },
        authorize() {
            this.isAuthorizing = true;

            ApiClient.setBaseUrl(this.apiUrl);

            ApiClient.Users.login(this.email, this.password).then(async (response) => {
                await storageHelper.set('appUrl', this.appUrl);
                await storageHelper.set('apiUrl', this.apiUrl);
                await storageHelper.set('email', this.email);
                await storageHelper.set('token', response.data.token);

                this.$router.replace({ name: 'export' });
            }).catch((err) => {
                this.$baseApiErrorHandler(err);
            }).finally(() => {
                this.isAuthorizing = false;
            });
        },
    },
}
</script>
