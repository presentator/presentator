import Vue           from 'vue';
import ApiClient     from '@/utils/ApiClient';
import AppConfig     from '@/utils/AppConfig';
import CommonHelper  from '@/utils/CommonHelper';
import ClientStorage from '@/utils/ClientStorage';

/**
 * Commonly used Vue instance helper methods.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default {
    install(Vue, options) {
        /**
         * Sets app document title.
         *
         * @param {Function|String} title
         * @param {Boolean}         [withSuffix]
         */
        Vue.prototype.$setDocumentTitle = function (title, withSuffix = true) {
            if (CommonHelper.isFunction(title)) {
                document._titleFunc = title; // used on i18n locale change
                title = title();
            } else {
                document._titleFunc = null;
            }

            if (title && withSuffix) {
                title += (" - " + AppConfig.get('VUE_APP_BASE_TITLE'));
            }

            document.title = title || AppConfig.get('VUE_APP_BASE_TITLE');
        };

        /**
         * Adds a new message to the `toast`` store.
         *
         * @param {String}  text
         * @param {String}  type
         * @param {Number}  timeout
         * @param {Boolean} closeBtn
         */
        Vue.prototype.$toast = function (text, type = 'success', timeout = 4500, closeBtn = true) {
            this.$store.dispatch('toast/addMessage', {
                'text':     text,
                'type':     type,
                'timeout':  timeout,
                'closeBtn': closeBtn,
            });
        };

        /**
         * Convenient wrapper to clear form field store errors.
         */
        Vue.prototype.$clearFormFieldErrors = function () {
            this.$store.dispatch('form-field/reset');
        };

        /**
         * Alias for `AppConfig.get()`.
         *
         * @param  {String} key
         * @param  {Mixed}  [defaultValue]
         * @return {Mixed}
         */
        Vue.prototype.$getAppConfig = function (key, defaultValue) {
            return AppConfig.get(key, defaultValue);
        };

        /**
         * Performs a user login operation based on raw response data.
         *
         * @param {Object}  response
         * @param {Boolean} [changeRoute] Whether to redirect to the homepage.
         */
        Vue.prototype.$loginByResponse = function (response, redirect = true) {
            response = response || {};

            ApiClient.storeApiToken(CommonHelper.getNestedVal(response, 'data.token', ''));

            this.$store.dispatch('user/set', CommonHelper.getNestedVal(response, 'data.user', {}));

            if (redirect) {
                let pathBeforeLogin = ClientStorage.getItem(AppConfig.get('VUE_APP_AFTER_LOGIN_ROUTE_STORAGE_KEY'));
                if (pathBeforeLogin) {
                    this.$router.replace(pathBeforeLogin);
                } else {
                    this.$router.replace({ name: 'home' });
                }
            }

            ClientStorage.removeItem(AppConfig.get('VUE_APP_AFTER_LOGIN_ROUTE_STORAGE_KEY'));
        };

        /**
         * Logouts the current user by taking care of cleaning cached token and user data.
         *
         * @param {Boolean} [changeRoute] Whether to redirect to the login page.
         */
        Vue.prototype.$logout = function (redirect = true) {
            ApiClient.clearStoredApiToken();

            this.$store.dispatch('user/clear');

            if (redirect) {
                this.$router.replace({ name: 'login' });
            }
        };

        /**
         * Refreshes user's token and data.
         *
         * @param {Boolean} [force] Whether to refresh even when the stored api token is not going to expire soon.
         */
        Vue.prototype.$refreshUser = function (force = false) {
            var token            = ApiClient.getStoredApiToken();
            var refreshThreshold = (AppConfig.get('VUE_APP_USER_REFRESH_THRESHOLD') << 0) / 1000;

            if (
                token &&
                !CommonHelper.isJwtExpired(token) &&                          // is still valid
                (force || CommonHelper.isJwtExpired(token, refreshThreshold)) // will soon expire
            ) {
                ApiClient.Users.refresh().then((response) => {
                    this.$loginByResponse(response, false);
                });
            }
        };

        /**
         * Generic apiclient error response handler.
         * @param {Error}   err
         * @param {Boolean} [notify]
         * @param {String}  [defaultMsg]
         */
        Vue.prototype.$errResponseHandler = function (err, notify = true, defaultMsg = '') {
            if (!err || !(err instanceof Error)) {
                return;
            }

            var statusCode   = CommonHelper.getNestedVal(err, 'response.status', 400) << 0;
            var responseData = CommonHelper.getNestedVal(err, 'response.data', {});

            // create error notification
            if (
                notify &&          // notifications are enabled
                statusCode !== 404 // is not 404
            ) {
                let msg = responseData.message || err.message || defaultMsg;

                if (msg) {
                    this.$toast(msg, 'danger');
                }
            }

            // populate form field errors
            if (!CommonHelper.isEmpty(responseData.errors)) {
                this.$nextTick(() => {
                    this.$store.dispatch('form-field/setErrors', responseData.errors);
                });
            }

            // unauthorized
            if (statusCode === 401) {
                if (
                    this.$route.name === 'preview-guideline' ||
                    this.$route.name === 'preview-prototype'
                ) {
                    this.$store.dispatch('preview/clearPreviewToken', this.$route.params.slug);

                    return;
                }

                ClientStorage.setItem(AppConfig.get('VUE_APP_AFTER_LOGIN_ROUTE_STORAGE_KEY'), this.$route.fullPath);

                this.$logout();
                return;
            }

            // forbidden
            if (statusCode === 403 && this.$route.name !== 'home') {
                this.$router.replace({ name: 'home' });
                return;
            }

            // not found
            if (statusCode === 404 && this.$route.name !== '404') {
                this.$router.replace({ name: '404' });
                return;
            }
        };
    }
}
