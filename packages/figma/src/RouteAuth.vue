<template>
    <form class="panel" @submit.prevent="authorize()">
        <header class="panel-header">
            <div class="logo">Presentator</div>
        </header>

        <div class="spacer"></div>

        <div class="row">
            <div class="form-field">
                <label for="app_url_input" class="section-title">App URL</label>
                <input v-model="appUrl" id="app_url_input" type="text" class="input" placeholder="App URL" value="https://app.presentator.io" required>
            </div>
            <div class="form-field">
                <label for="api_url_input" class="section-title">Api URL</label>
                <input v-model="apiUrl" id="api_url_input" type="text" class="input" placeholder="Api URL" value="https://app.presentator.io/api" required>
            </div>
        </div>

        <div class="spacer"></div>

        <div class="form-field">
            <label for="email_input" class="section-title">Email</label>
            <input v-model="email" id="email_input" type="email" class="input" placeholder="test@example.com" required>
        </div>

        <div class="spacer"></div>

        <div class="form-field">
            <label for="password_input" class="section-title">Password</label>
            <input ref="passwordInput"  v-model="password" id="password_input" type="password" class="input" placeholder="***" required>
        </div>

        <div class="spacer"></div>

        <footer class="row panel-footer">
            <a :href="registerUrl" target="_blank">Create an account</a>
            <div class="fill-block"></div>
            <button type="button" class="button button--secondary" @click.prevent="$closePluginDialog()" :disabled="isAuthorizing">Cancel</button>
            <button type="submit" class="button button--primary" :disabled="isAuthorizing">Authorize</button>
        </footer>
    </form>
</template>

<script>
import clientStorage from '@/utils/ClientStorage';
import apiClient     from '@/utils/ApiClient';

export default {
    name: 'route-auth',
    data() {
        return {
            isAuthorizing: false,
            appUrl:        '',
            apiUrl:        '',
            email:         '',
            password:      '',
        };
    },
    computed: {
        registerUrl() {
            let appUrl = (this.appUrl.trim().replace(/\/+$/, '')) || 'https://app.presentator.io';

            return appUrl + '/#/sign-up';
        },
    },
    mounted() {
        this.$resizePluginDialog(/* reset to defaults */);

        this.loadDefaults();
    },
    methods: {
        loadDefaults() {
            this.appUrl   = clientStorage.getItem('appUrl') || 'https://app.presentator.io';
            this.apiUrl   = clientStorage.getItem('apiUrl') || 'https://app.presentator.io/api';
            this.email    = clientStorage.getItem('email')  || '';
            this.password = '';

            // focus the password input if an email is already typed
            this.$nextTick(() => {
                if (this.email && this.$refs.passwordInput) {
                    this.$refs.passwordInput.focus();
                }
            });
        },
        authorize() {
            if (this.isAuthorizing) {
                return;
            }

            this.isAuthorizing = true;

            apiClient.setBaseUrl(this.apiUrl);

            apiClient.Users.login(this.email, this.password).then((response) => {
                clientStorage.setItem('appUrl', this.appUrl);
                clientStorage.setItem('apiUrl', this.apiUrl);
                clientStorage.setItem('email', this.email);
                clientStorage.setItem('token', response.data.token);

                this.$router.replace({ name: 'export' });
            }).catch((err) => {
                this.isAuthorizing = false;

                this.$baseApiErrorHandler(err);
            });
        },
    }
};
</script>
