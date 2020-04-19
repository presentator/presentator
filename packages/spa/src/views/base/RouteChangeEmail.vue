<template>
    <div class="auth-container">
        <div class="flex-fill-block"></div>

        <div class="container-wrapper container-wrapper-sm">
            <app-header></app-header>

            <div class="clearfix m-b-large"></div>

            <div class="panel auth-panel">
                <h3 class="panel-title">{{ $t('Email change confirmation') }}</h3>

                <div class="panel-content">
                    <div v-if="isProcessing" class="alert alert-transp-primary txt-center">
                        <p>{{ $t('Changing your email address') }} <span class="loader m-l-5"></span></p>
                    </div>

                    <template v-else>
                        <div v-if="!processSuccess" class="alert alert-transp-danger txt-center">
                            <p>
                                {{ $t('The provided email change token is invalid or expired.') }} <br>

                                <i18n path="Please contact us at {supportEmail} if you need further assistance.">
                                    <a slot="supportEmail" :href="'mailto:' + $getAppConfig('VUE_APP_SUPPORT_EMAIL')">{{ $getAppConfig('VUE_APP_SUPPORT_EMAIL') }}</a>
                                </i18n>
                            </p>
                        </div>

                        <div class="clearfix m-b-small"></div>

                        <router-link :to="{name: 'login'}" class="btn btn-primary block">
                            <span class="txt">{{ $t('Continue to login') }}</span>
                            <i class="fe fe-arrow-right-circle"></i>
                        </router-link>
                    </template>
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
    name: 'activate',
    components: {
        'app-header': AppHeader,
        'app-footer': AppFooter,
    },
    data() {
        return {
            isProcessing:   false,
            processSuccess: false,
        }
    },
    beforeMount() {
        this.$setDocumentTitle(() => this.$t('Email change confirmation'));

        this.activate();
    },
    methods: {
        activate() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Users.confirmEmailChange(this.$route.params.emailChangeToken).then((response) => {
                this.processSuccess = true;

                this.$loginByResponse(response);
            }).catch((err) => {

                this.processSuccess = false;
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
