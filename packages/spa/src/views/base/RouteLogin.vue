<template>
    <div class="auth-container">
        <div class="flex-fill-block"></div>

        <div class="container-wrapper container-wrapper-sm">
            <app-header></app-header>

            <div class="clearfix m-b-large"></div>

            <div class="panel auth-panel">
                <h3 class="panel-title">{{ $t('Sign in') }}</h3>

                <div v-if="isLoadingAuthMethods" class="panel-content">
                    <div class="block txt-center txt-hint">
                        <span class="loader"></span>
                    </div>
                </div>

                <div v-else class="panel-content">
                    <div v-if="!authMethods.emailPassword && !authMethods.clients.length" class="alert alert-border txt-center">
                        {{$t('No authorization methods found.')}}
                    </div>

                    <email-password-form v-if="authMethods.emailPassword"></email-password-form>

                    <div v-if="authMethods.emailPassword && authMethods.clients.length" class="block txt-center m-t-base m-b-10">
                        {{$t('Or sign in via:')}}
                    </div>

                    <auth-clients-bar :clients="authMethods.clients"></auth-clients-bar>
                </div>
            </div>

            <div class="clearfix m-b-base"></div>

            <div v-if="!isLoadingAuthMethods && authMethods.emailPassword" class="auth-meta">
                <router-link :to="{name: 'register'}">
                    {{ $t("Don't have an account yet?") }}
                    <strong>{{ $t('Sign up.') }}</strong>
                </router-link>
            </div>
        </div>

        <div class="flex-fill-block m-b-base"></div>

        <app-footer></app-footer>
    </div>
</template>

<script>
import ApiClient         from '@/utils/ApiClient';
import AppHeader         from '@/views/base/AppHeader';
import AppFooter         from '@/views/base/AppFooter';
import AuthClientsBar    from '@/views/base/AuthClientsBar';
import EmailPasswordForm from '@/views/base/EmailPasswordForm';

export default {
    name: 'login',
    components: {
        'app-header':          AppHeader,
        'app-footer':          AppFooter,
        'auth-clients-bar':    AuthClientsBar,
        'email-password-form': EmailPasswordForm,
    },
    data() {
        return {
            authMethods:          {},
            isLoadingAuthMethods: false,
        }
    },
    beforeMount() {
        this.$setDocumentTitle(() => this.$t('Sign in'));
        this.loadAuthMethods();
    },
    methods: {
        loadAuthMethods() {
            if (this.isLoadingAuthMethods) {
                return;
            }

            this.isLoadingAuthMethods = true;

            ApiClient.Users.getAuthMethods().then((response) => {
                this.authMethods = response.data || { emailPassword: true, clients: [] };
            }).catch((err) => {
                // silence errors...
            }).finally(() => {
                this.isLoadingAuthMethods = false;
            });
        },
    },
}
</script>
