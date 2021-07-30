import Vue from 'vue';
import types from '@/utils/types';
import clientStorage from '@/utils/ClientStorage';

/**
 * Commonly used Vue instance helper methods.
 */
export default {
    install(Vue, options) {
        /**
         * Generic api client error response handler.
         *
         * @param {Error} err
         */
        Vue.prototype.$baseApiErrorHandler = function(err) {
            if (!err) {
                return;
            }

            const responseStatus = (err.response && err.response.status) ? err.response.status : 200;
            const errMessage = (err.response && err.response.data && err.response.data.message) ? err.response.data.message : err.message;

            if (responseStatus == 401 || responseStatus == 403) {
                this.$logout();
            }

            if (errMessage) {
                this.$notify(errMessage);
            }
        };

        /**
         * Logouts the current user by taking care of cleaning stored user data.
         */
        Vue.prototype.$logout = function() {
            clientStorage.removeItem('token');

            if (this.$route.name !== 'auth') {
                this.$router.replace({ name: 'auth' });
            }
        };

        /**
         * Registers a new global notification/alert.
         *
         * @param {String} message   Notification message
         * @param {Number} [timeout] Auto close timeout in ms (defaults to 3500 ms)
         */
        Vue.prototype.$notify = function(message, timeout = 3500) {
            parent.postMessage({
                pluginMessage: {
                    type: types.MESSAGE_NOTIFY,
                    data: {
                        message: message,
                        timeout: timeout,
                    },
                },
            }, '*');
        };

        /**
         * Closes the active `figma.ui` modal dialog.
         */
        Vue.prototype.$closePluginDialog = function() {
            parent.postMessage({
                pluginMessage: { type: types.MESSAGE_CLOSE },
            }, '*');
        };

        /**
         * Resizes the active `figma.ui` modal dialog.
         *
         * @param {Number} [width]  New width of the dialog (if not set the default one will be used).
         * @param {Number} [height] New height of the dialog (if not set the default one will be used).
         */
        Vue.prototype.$resizePluginDialog = function(width, height) {
            parent.postMessage({
                pluginMessage: {
                    type: types.MESSAGE_RESIZE_UI,
                    data: {
                        width: width,
                        height: height,
                    },
                },
            }, '*');
        };

        /**
         * Returns design frame nodes.
         *
         * @param  {Boolean} [onlySelected] Whether to return only the selected frames (`false` by default).
         * @return {Promise}
         */
        Vue.prototype.$getFrames = function(onlySelected = false) {
            const state = ('get_frames_' + Date.now());

            return new Promise(function(resolve, reject) {
                let forceTimeoutId = null;

                let handler = function(event) {
                    let message = event.data.pluginMessage || {};

                    if (
                        message.state == state && // is the correct response
                        message.type === types.MESSAGE_GET_FRAMES_RESPONSE
                    ) {
                        clearTimeout(forceTimeoutId);

                        window.removeEventListener('message', handler);

                        resolve(message.data || []);
                    }
                };

                window.addEventListener('message', handler);

                // force resolve after 10 seconds
                forceTimeoutId = setTimeout(() => {
                    window.removeEventListener('message', handler);

                    Promise.resolve([]);
                }, 10000);

                // request frames
                parent.postMessage({
                    pluginMessage: {
                        state: state,
                        type: types.MESSAGE_GET_FRAMES,
                        data: {
                            onlySelected: onlySelected,
                        },
                    },
                }, '*');
            });
        };

        /**
         * Exports a single frame image data.
         *
         * @param  {String} frameId    ID of the frame to export.
         * @param  {Object} [settings] Additional settings to be passed to the export command.
         * @return {Promise}
         */
        Vue.prototype.$exportFrame = function(frameId, settings) {
            const state = ('export_frame_' + frameId + Date.now());

            return new Promise(function(resolve, reject) {
                let forceTimeoutId = null;

                let handler = function(event) {
                    let message = event.data.pluginMessage || {};

                    if (
                        message.state == state && // is the correct response
                        message.type === types.MESSAGE_EXPORT_FRAME_RESPONSE
                    ) {
                        clearTimeout(forceTimeoutId);

                        window.removeEventListener('message', handler);

                        resolve(message.data || []);
                    }
                };

                window.addEventListener('message', handler);

                // force resolve after 10 seconds
                forceTimeoutId = setTimeout(() => {
                    window.removeEventListener('message', handler);
                    Promise.resolve([]);
                }, 10000);

                // request frames
                parent.postMessage({
                    pluginMessage: {
                        state: state,
                        type: types.MESSAGE_EXPORT_FRAME,
                        data: {
                            id: frameId,
                            settings: settings,
                        },
                    },
                }, '*');
            });
        };
    }
}
