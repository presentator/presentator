<template>
    <div class="auth-container">
        <div class="flex-fill-block"></div>

        <div class="container-wrapper container-wrapper-sm">
            <app-header></app-header>

            <div class="clearfix m-b-large"></div>

            <div class="panel auth-panel">
                <h3 class="panel-title">{{ $t('Sign in') }}</h3>

                <div class="panel-content">
                    <form class="login-form" @submit.prevent="onSubmit">
                        <alert class="alert-transp-danger m-b-base" :closeTimeout="0" :visibility="showFormError">
                            {{ $t('Invalid login credentials.') }}
                        </alert>

                        <form-field class="form-group-lg" name="email">
                            <div class="input-group">
                                <label for="sign_in_email" class="input-addon p-r-0">
                                    <i class="fe fe-mail"></i>
                                </label>
                                <input type="email" v-model="email" id="sign_in_email" :placeholder="$t('Email')" required>
                            </div>
                        </form-field>

                        <form-field class="form-group-lg" name="password">
                            <div class="input-group">
                                <label for="sign_in_password" class="input-addon p-r-0">
                                    <i class="fe fe-lock"></i>
                                </label>
                                <input type="password" v-model="password" id="sign_in_password" :placeholder="$t('Password')" required>
                            </div>
                            <router-link :to="{name: 'forgotten-password'}" class="forgotten-password-link link-primary">
                                {{ $t('Forgotten password?') }}
                            </router-link>
                        </form-field>

                        <button class="btn btn-primary btn-lg btn-loader block" :class="{'btn-loader-active': isProcessing}">
                            <span class="txt">{{ $t('Sign in') }}</span>
                            <i class="fe fe-arrow-right-circle"></i>
                        </button>
                    </form>

                    <div class="clearfix m-b-base"></div>

                    <auth-clients-bar :heading="$t('Or sign in via:')"></auth-clients-bar>
                </div>
            </div>

            <div class="clearfix m-b-base"></div>

            <div class="auth-meta">
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
import ApiClient      from '@/utils/ApiClient';
import CommonHelper   from '@/utils/CommonHelper';
import AppHeader      from '@/views/base/AppHeader';
import AppFooter      from '@/views/base/AppFooter';
import AuthClientsBar from '@/views/base/AuthClientsBar';

export default {
    name: 'login',
    components: {
        'app-header':       AppHeader,
        'app-footer':       AppFooter,
        'auth-clients-bar': AuthClientsBar,
    },
    data() {
        return {
            email:         '',
            password:      '',
            showFormError: false,
            isProcessing:  false,
        }
    },
    beforeMount() {
        this.$setDocumentTitle(() => this.$t('Sign in'));
    },
    methods: {
        onSubmit() {
            if (this.isProcessing) {
                return;
            }

            this.showFormError = false;
            this.isProcessing  = true;

            ApiClient.Users.login(
                this.email,
                this.password
            ).then((response) => {
                this.$loginByResponse(response);
            }).catch((err) => {
                this.showFormError = true;
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
