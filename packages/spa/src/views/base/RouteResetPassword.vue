<template>
    <div class="auth-container">
        <div class="flex-fill-block"></div>

        <div class="container-wrapper container-wrapper-sm">
            <app-header></app-header>

            <div class="clearfix m-b-large"></div>

            <div class="panel auth-panel">
                <h3 class="panel-title">{{ $t('Reset password') }}</h3>

                <div v-if="processSuccess" class="panel-content">
                    <div class="alert alert-transp-primary txt-center">
                        <p>{{ $t('Your password has been reset successfully.') }}</p>
                    </div>

                    <div class="clearfix m-b-small"></div>

                    <router-link :to="{name: 'login'}" class="btn btn-primary block">
                        <span class="txt">{{ $t('Continue to login') }}</span>
                        <i class="fe fe-arrow-right-circle"></i>
                    </router-link>
                </div>

                <div v-else class="panel-content">
                    <p class="txt-center">{{ $t('Type your new password:') }}</p>

                    <form class="register-form disabled" @submit.prevent="onSubmit">
                        <form-field class="form-group-lg" name="password">
                            <div class="input-group">
                                <label for="reset_password" class="input-addon p-r-0">
                                    <i class="fe fe-mail"></i>
                                </label>
                                <input type="password" id="reset_password" v-model="password" :placeholder="$t('New password')" required>
                            </div>
                        </form-field>

                        <form-field class="form-group-lg" name="passwordConfirm">
                            <div class="input-group">
                                <label for="reset_password_confirm" class="input-addon p-r-0">
                                    <i class="fe fe-mail"></i>
                                </label>
                                <input type="password" id="reset_password_confirm" v-model="passwordConfirm" :placeholder="$t('Confirm new password')" required>
                            </div>
                        </form-field>

                        <button class="btn btn-primary btn-lg btn-loader block" :class="{'btn-loader-active': isProcessing}">
                            <span class="txt">{{ $t('Reset password') }}</span>
                            <i class="fe fe-arrow-right-circle"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="flex-fill-block m-b-base"></div>

        <app-footer></app-footer>
    </div>
</template>

<script>
import ApiClient from '@/utils/ApiClient';
import AppHeader from '@/views/base/AppHeader';
import AppFooter from '@/views/base/AppFooter';

export default {
    name: 'reset-password',
    components: {
        'app-header': AppHeader,
        'app-footer': AppFooter,
    },
    data() {
        return {
            password:        '',
            passwordConfirm: '',
            isProcessing:    false,
            processSuccess:  false,
        }
    },
    beforeMount() {
        this.$setDocumentTitle(() => this.$t('Reset password'));
    },
    methods: {
        onSubmit() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Users.confirmPasswordReset(
                this.$route.params.resetToken,
                this.password,
                this.passwordConfirm
            ).then((response) => {
                this.processSuccess = true;
            }).catch((err) => {
                this.processSuccess = false;

                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
