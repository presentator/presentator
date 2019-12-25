import Vue                from 'vue';
import VueRouter          from 'vue-router';
import VueHelper          from '@/utils/VueHelper';
import apiClient          from '@/utils/ApiClient';
import clientStorage      from '@/utils/ClientStorage';
import types              from '@/utils/types';
import App                from '@/App';
import RouteAuth          from '@/RouteAuth';
import RouteExport        from '@/RouteExport';
import RouteExportSuccess from '@/RouteExportSuccess';

Vue.config.productionTip = false;

// silent noisy uncaught router promise errors
// https://github.com/vuejs/vue-router/issues/2881
const originalReplace = VueRouter.prototype.replace;
VueRouter.prototype.replace = function replace(location, onResolve, onReject) {
    if (onResolve || onReject) return originalReplace.call(this, location, onResolve, onReject);
    return originalReplace.call(this, location).catch(err => err);
}

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
        {
            path:     '*',
            redirect: '/',
        },
    ],
});

// global route guard
router.beforeEach((to, from, next) => {
    apiClient.loadStorageData();

    const hasValidToken = apiClient.hasValidToken();

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

let app;
window.addEventListener('message', async (event) => {
    if (!event || !event.data || !event.data.pluginMessage) {
        return;
    }

    let message = event.data.pluginMessage || {};

    if (message.type === types.MESSAGE_INIT_APP && !app) {
        clientStorage.load(message.data);

        await apiClient.refreshToken();

        app = new Vue({
            el: '#app',
            router: router,
            render: h => h(App),
            destroyed: function() {
                app = null;
            },
        });
    }
});
