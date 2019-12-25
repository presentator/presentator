const Vue           = require('vue').default;
const scenegraph    = require('scenegraph');
const storageHelper = require('xd-storage-helper');
const events        = require('@/utils/EventsBus');

/**
 * Commonly used Vue instance helper methods.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
module.exports = {
    install(Vue, options) {
        /**
         * Generic api client error response handler.
         *
         * @param {Error} err
         */
        Vue.prototype.$baseApiErrorHandler = function (err) {
            if (!err) {
                return;
            }

            const responseStatus = (err.response && err.response.status) ? err.response.status : 200;
            const errMessage     = (err.response && err.response.data && err.response.data.message) ? err.response.data.message : err.message;

            if (responseStatus == 401 || responseStatus == 403) {
                this.$logout();
            }

            if (errMessage) {
                this.$notify(errMessage, 'error');
            }
        };

        /**
         * Logouts the current user by taking care of cleaning stored user data.
         */
        Vue.prototype.$logout = async function () {
            await storageHelper.delete('token');

            if (this.$route.name !== 'auth') {
                this.$router.replace({ name: 'auth' });
            }
        };

        /**
         * Closes the active Adobe XD modal dialog.
         */
        Vue.prototype.$closePluginDialog = function () {
            if (this.$xdDialog && typeof this.$xdDialog.close === 'function') {
                // load the export view in order to show it first next time the dialog is opened
                this.$router.replace({ name: 'export' });

                this.$xdDialog.close();
            }
        };

        /**
         * Registers a new global notification/alert.
         *
         * @param {String} message    Notification message
         * @param {String} [type]     Notification type (info, warning, error, success)
         * @param {Number} [duration] Auto close duration in ms (defaults to 5000 ms)
         */
        Vue.prototype.$notify = function (message, type = 'info', duration = 5000) {
            events.$emit('add', message, type, duration);
        };

        /**
         * Filters through Adobe XD scenegraph nodes and returns only the artboards.
         *
         * @param  {Boolean} selectionOnly Whether to return only the selected artboards (`false` by default, aka all artboards).
         * @return {Array}
         */
        Vue.prototype.$getArtboards = function (selectionOnly = false) {
            const nodes     = selectionOnly ? scenegraph.selection.items : scenegraph.root.children;
            const artboards = [];

            // extract artboards
            nodes.forEach((node, i) => {
                if (node && node.constructor.name.toLowerCase() === 'artboard') {
                    artboards.push(node);
                }
            });

            return artboards;
        };
    }
}
