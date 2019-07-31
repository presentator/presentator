<template>
    <div class="auth-container">
        <div class="flex-fill-block"></div>

        <div class="container-wrapper container-wrapper-sm">
            <app-header></app-header>

            <div class="clearfix m-b-large"></div>

            <div class="panel auth-panel">
                <h3 class="panel-title">{{ $t('Forgotten password') }}</h3>

                <div v-if="processSuccess" class="panel-content">
                    <div class="alert alert-transp-primary txt-center">
                        <p>
                            {{ $t('We sent a recovery link to your email address:') }} <br>
                            <strong>{{ email }}</strong>
                        </p>
                    </div>
                </div>

                <div v-else class="panel-content">
                    <p class="txt-center">{{ $t("We'll send a recovery link to your email:") }}</p>

                    <form class="register-form disabled" @submit.prevent="onSubmit">
                        <form-field class="form-group-lg" name="email">
                            <div class="input-group">
                                <label for="forgotten_password_email" class="input-addon p-r-0">
                                    <i class="fe fe-mail"></i>
                                </label>
                                <input type="email" id="forgotten_password_email" v-model="email" :placeholder="$t('Email')" required>
                            </div>
                        </form-field>

                        <button class="btn btn-primary btn-lg btn-loader block" :class="{'btn-loader-active': isProcessing}">
                            <span class="txt">{{ $t('Send recovery link') }}</span>
                            <i class="fe fe-arrow-right-circle"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="clearfix m-b-base"></div>

            <div class="auth-meta">
                <router-link :to="{name: 'login'}">{{ $t('Return to login.') }}</router-link>
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
    name: 'forgotten-password',
    components: {
        'app-header': AppHeader,
        'app-footer': AppFooter,
    },
    data() {
        return {
            email:          '',
            isProcessing:   false,
            processSuccess: false,
        }
    },
    beforeMount() {
        this.$setDocumentTitle(() => this.$t('Forgotten password'));
    },
    methods: {
        onSubmit() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Users.requestPasswordReset(this.email).then((response) => {
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
