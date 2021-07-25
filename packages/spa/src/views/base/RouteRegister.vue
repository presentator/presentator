<template>
    <div class="auth-container">
        <div class="flex-fill-block"></div>

        <div class="container-wrapper container-wrapper-sm">
            <app-header></app-header>

            <div class="clearfix m-b-large"></div>

            <div class="panel auth-panel">
                <h3 class="panel-title">{{ $t('Sign up') }}</h3>

                <div v-if="isLoadingAuthMethods" class="panel-content">
                    <div class="block txt-center txt-hint">
                        <span class="loader"></span>
                    </div>
                </div>

                <div v-else-if="registerSuccess" class="panel-content">
                    <div class="alert alert-transp-primary txt-center">
                        <p>
                            {{ $t('Check your email to finish signing up.') }}<br>
                            {{ $t('We sent a signup link to you at:') }}<br>
                            <strong>{{ email }}</strong>
                        </p>
                    </div>
                </div>

                <div v-else class="panel-content">
                    <form v-if="authMethods.emailPassword" class="register-form" @submit.prevent="onSubmit">
                        <form-field class="form-group-lg" name="email">
                            <div class="input-group">
                                <label for="sign_up_email" class="input-addon p-r-0">
                                    <i class="fe fe-mail"></i>
                                </label>
                                <input type="email" id="sign_up_email" v-model="email" :placeholder="$t('Email')" required>
                            </div>
                        </form-field>

                        <form-field class="form-group-lg" name="password">
                            <div class="input-group">
                                <label for="sign_up_password" class="input-addon p-r-0">
                                    <i class="fe fe-unlock"></i>
                                </label>
                                <input type="password" id="sign_up_password" v-model="password" :placeholder="$t('Password')" required>
                            </div>
                        </form-field>

                        <form-field class="form-group-lg" name="passwordConfirm">
                            <div class="input-group">
                                <label for="sign_up_password_confirm" class="input-addon no-b-r no-bg p-r-0">
                                    <i class="fe fe-lock"></i>
                                </label>
                                <input type="password" id="sign_up_password_confirm" v-model="passwordConfirm" :placeholder="$t('Password confirm')" required>
                            </div>
                        </form-field>

                        <button class="btn btn-primary btn-lg btn-loader block" :class="{'btn-loader-active': isProcessing}">
                            <span class="txt">{{ $t('Sign up') }}</span>
                            <i class="fe fe-arrow-right-circle"></i>
                        </button>

                        <div v-if="$getAppConfig('VUE_APP_TERMS_URL')" class="block txt-center m-t-small txt-hint">
                            <i18n path='By clicking "Sign up" you agree to our {termsLink}.'>
                                <a slot="termsLink" :href="$getAppConfig('VUE_APP_TERMS_URL')" target="_blank" rel="noopener">{{ $t('Terms and Privacy policy') }}</a>
                            </i18n>
                        </div>
                    </form>

                    <div v-if="authMethods.emailPassword && authMethods.clients.length" class="block txt-center m-t-base m-b-10">
                        {{$t('Or sign up via:')}}
                    </div>

                    <auth-clients-bar :clients="authMethods.clients"></auth-clients-bar>
                </div>
            </div>

            <div class="clearfix m-b-base"></div>

            <div v-if="!isLoadingAuthMethods && authMethods.emailPassword" class="auth-meta">
                <router-link :to="{name: 'login'}">
                    {{ $t('Already have an account?') }}
                    <strong>{{ $t('Sign in.') }}</strong>
                </router-link>
            </div>
        </div>

        <div class="flex-fill-block m-b-base"></div>

        <app-footer></app-footer>
    </div>
</template>

<script>
import ApiClient      from '@/utils/ApiClient';
import AppHeader      from '@/views/base/AppHeader';
import AppFooter      from '@/views/base/AppFooter';
import AuthClientsBar from '@/views/base/AuthClientsBar';

export default {
    name: 'register',
    components: {
        'app-header':       AppHeader,
        'app-footer':       AppFooter,
        'auth-clients-bar': AuthClientsBar,
    },
    data() {
        return {
            email:                '',
            password:             '',
            passwordConfirm:      '',
            isProcessing:         false,
            registerSuccess:      false,
            authMethods:          {},
            isLoadingAuthMethods: false,
        }
    },
    beforeMount() {
        this.$setDocumentTitle(() => this.$t('Sign up'));
        this.loadAuthMethods();
    },
    methods: {
        onSubmit() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Users.register({
                email:           this.email,
                password:        this.password,
                passwordConfirm: this.passwordConfirm,
            }).then((response) => {
                this.registerSuccess = true;
            }).catch((err) => {
                this.registerSuccess = false;

                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
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
