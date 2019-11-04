const styles             = require('@/styles.css');
const Vue                = require('vue').default;
const VueRouter          = require('vue-router').default;
const ApiClient          = require('@/utils/ApiClient');
const VueHelper          = require('@/utils/VueHelper.js');
const App                = require('@/App.vue').default;
const RouteAuth          = require('@/RouteAuth.vue').default;
const RouteExport        = require('@/RouteExport.vue').default;
const RouteExportSuccess = require('@/RouteExportSuccess.vue').default;

Vue.use(VueRouter);

Vue.use(VueHelper);

const router = new VueRouter({
    routes: [
        {
            path:      '/',
            name:      'auth',
            component: RouteAuth,
        },
        {
            path:      '/export',
            name:      'export',
            component: RouteExport,
        },
        {
            path:      '/export/:projectId/:prototypeId',
            name:      'export-success',
            component: RouteExportSuccess,
            props:     true,
        },
    ],
});

// global route guard
router.beforeEach(async (to, from, next) => {
    await ApiClient.loadStorageData();

    const hasValidToken = ApiClient.hasValidToken();

    // trying to access auth page while logged in
    if (hasValidToken && to.name === 'auth') {
        return next({name: 'export'});
    }

    // missing/expired token
    if (!hasValidToken && to.name !== 'auth') {
        return next({name: 'auth'});
    }

    return next();
});

let dialog;
function getDialog() {
    if (dialog == null) {
        document.body.innerHTML = '<dialog><div id="container"></div></dialog>';
        dialog = document.querySelector('dialog');

        Vue.prototype.$xdDialog = dialog;

        new Vue({
            el: '#container',
            router,
            render: h => h(App)
        });
    }

    return dialog;
}

module.exports = {
    commands: {
        menuCommand: function () {
            getDialog().showModal();

            // trigger stored user token refresh each time when the dialog is shown
            ApiClient.refreshToken();
        },
    },
};
