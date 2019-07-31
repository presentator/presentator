<template>
    <div class="full-page-flex">
        <div class="flex-fill-block"></div>
        <div class="block txt-center txt-hint p-base">
            <span class="loader loader-blend v-align-middle m-r-5"></span>
            <span class="txt v-align-middle">{{ $t('Please wait.') }} {{ $t('Redirecting...') }}</span>
        </div>
        <div class="flex-fill-block"></div>
    </div>
</template>

<script>
import AppConfig     from '@/utils/AppConfig';
import ClientStorage from '@/utils/ClientStorage';
import CommonHelper  from '@/utils/CommonHelper';
import ApiClient     from '@/utils/ApiClient';

export default {
    name: 'auth-callback',
    data() {
        return {
            isAuthorizing: false,
        }
    },
    beforeMount() {
        // manually parse query params because of https://github.com/vuejs/vue-router/issues/2125
        var queryParams = CommonHelper.getQueryParams(window.location.href);

        this.authorize(queryParams.code, queryParams.state);
    },
    methods: {
        authorize(code, state) {
            if (this.isAuthorizing) {
                return;
            }

            var storedClient = ClientStorage.getItem(AppConfig.get('VUE_APP_AUTH_CLIENT_NAME_STORAGE_KEY'));
            var storedState  = ClientStorage.getItem(AppConfig.get('VUE_APP_AUTH_CLIENT_STATE_STORAGE_KEY'));

            if (!storedClient || !storedState || !state || state !== storedState) {
                this.$toast(this.$t('Invalid callback session.'), 'danger');
                this.cancelAuthorization();
                return;
            }

            this.isAuthorizing = true;

            ApiClient.Users.authorizeAuthClient(storedClient, code).then((response) => {
                this.$loginByResponse(response, false);

                if (window.opener && !window.opener.closed) {
                    window.opener.location.reload();
                    window.opener.focus();
                    window.close();
                } else {
                    this.$router.go({name: 'home'});
                }
            }).catch((err) => {
                this.$toast(this.$t('Invalid or expired authorization code.'), 'danger');
                this.cancelAuthorization();
            }).finally(() => {
                this.isAuthorizing = false;
            });
        },
        cancelAuthorization(redirectDelay = 2000) {
            this.isAuthorizing = false;

            setTimeout(() => {
                if (window.opener && !window.opener.closed) {
                    window.opener.location.reload();
                    window.opener.focus();
                    window.close();
                } else {
                    this.$router.replace({name: 'login'});
                }
            }, redirectDelay);
        },
    }
}
</script>
