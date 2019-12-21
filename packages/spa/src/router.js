import Vue           from 'vue';
import Router        from 'vue-router';
import AppConfig     from '@/utils/AppConfig';
import ApiClient     from '@/utils/ApiClient';
import CommonHelper  from '@/utils/CommonHelper';
import ClientStorage from '@/utils/ClientStorage';

// silent noisy uncaught nav promise errors
// https://github.com/vuejs/vue-router/issues/2881
const originalReplace = Router.prototype.replace;
Router.prototype.replace = function replace(location, onResolve, onReject) {
    if (onResolve || onReject) return originalReplace.call(this, location, onResolve, onReject);
    return originalReplace.call(this, location).catch(err => err);
}

Vue.use(Router);

const router = new Router({
    mode: 'hash',
    routes: [
        {
            path:     '/',
            name:     'home',
            redirect: { name: 'projects' },
            meta:     { requiresAuth: true },
        },
        // Base routes
        {
            path:      '/sign-in',
            name:      'login',
            component: () => import('./views/base/RouteLogin.vue'),
            meta:      { requiresAuth: false, isAuthRoute: true },
        },
        {
            path:      '/sign-up',
            name:      'register',
            component: () => import('./views/base/RouteRegister.vue'),
            meta:      { requiresAuth: false, isAuthRoute: true },
        },
        {
            path:      '/auth-callback',
            name:      'auth-callback',
            component: () => import('./views/base/RouteAuthCallback.vue'),
            meta:      { requiresAuth: false, isAuthRoute: true },
        },
        {
            path:      '/activate/:activateToken',
            name:      'activate',
            component: () => import('./views/base/RouteActivate.vue'),
            meta:      { requiresAuth: false, isAuthRoute: true },
        },
        {
            path:      '/change-email/:emailChangeToken',
            name:      'change-email',
            component: () => import('./views/base/RouteChangeEmail.vue'),
            meta:      { requiresAuth: false, isAuthRoute: false },
        },
        {
            path:      '/forgotten-password',
            name:      'forgotten-password',
            component: () => import('./views/base/RouteForgottenPassword.vue'),
            meta:      { requiresAuth: false, isAuthRoute: true },
        },
        {
            path:      '/reset-password/:resetToken',
            name:      'reset-password',
            component: () => import('./views/base/RouteResetPassword.vue'),
            meta:      { requiresAuth: false, isAuthRoute: true },
        },
        // Projects
        {
            path:      '/projects',
            name:      'projects',
            component: () => import('./views/projects/RouteProjects.vue'),
            meta:      { requiresAuth: true },
        },
        {
            path:      '/projects/:projectId/guideline',
            name:      'guideline',
            component: () => import('./views/guidelines/RouteGuideline.vue'),
            meta:      { requiresAuth: true },
        },
        {
            path:      '/projects/:projectId/prototypes/:prototypeId?',
            name:      'prototype',
            component: () => import('./views/prototypes/RoutePrototype.vue'),
            meta:      { requiresAuth: true },
        },
        // Screen edit
        {
            path:      '/projects/:projectId/prototypes/:prototypeId/screens/:screenId?',
            name:      'screen',
            component: () => import('./views/screens/RouteScreen.vue'),
            meta:      { requiresAuth: true },
        },
        // User
        {
            path:      '/users',
            name:      'users',
            component: () => import('./views/users/RouteUsers.vue'),
            meta:      { requiresAuth: true, isSuperUserRoute: true },
        },
        {
            path:      '/users/:userId',
            name:      'user',
            component: () => import('./views/users/RouteUser.vue'),
            meta:      { requiresAuth: true },
        },
        // Project preview
        {
            path:      '/:slug',
            name:      'preview',
            component: () => import('./views/preview/RoutePreview.vue'),
            meta:      { requiresAuth: false },
            props:     true, // pass route params to component's props
            children: [
                {
                    path:      'prototypes/:prototypeId?/screens/:screenId?',
                    alias:     '',
                    name:      'preview-prototype',
                    component: () => import('./views/preview/RoutePreviewPrototype.vue'),
                    meta:      { requiresAuth: false },
                },
                {
                    path:      'guideline',
                    name:      'preview-guideline',
                    component: () => import('./views/preview/RoutePreviewGuideline.vue'),
                    meta:      { requiresAuth: false },
                },
            ],
        },

        // "catch all" 404
        {
            path:      '/not-found',
            alias:     '*',
            name:      '404',
            component: () => import('./views/base/RouteNotFound.vue'),
            meta:      { requiresAuth: false },
        },
    ],
});

// handles auth routes redirection
router.beforeEach((to, from, next) => {
    var hasValidApiToken = !CommonHelper.isJwtExpired(ApiClient.getStoredApiToken());

    // missing/expired token
    if (to.meta.requiresAuth && !hasValidApiToken) {
        ClientStorage.setItem(AppConfig.get('VUE_APP_AFTER_LOGIN_ROUTE_STORAGE_KEY'), to.fullPath);

        return next({name: 'login'});
    }

    // trying to access authorization route with valid token
    if (to.meta.isAuthRoute && hasValidApiToken) {
        return next({name: 'home'});
    }

    // regular user is trying to access a super user route
    if (
        to.meta.isSuperUserRoute &&
        router.app.$store &&
        (!router.app.$store.state.user.user || !router.app.$store.state.user.user.isSuperUser)
    ) {
        return next({name: 'home'});
    }

    // switching between public and private routes
    if (
        from.name &&
        router.app.$store &&
        (to.meta.requiresAuth != from.meta.requiresAuth)
    ) {
        router.app.$store.dispatch('notifications/reset');
        router.app.$store.dispatch('comments/reset');
        router.app.$store.dispatch('hotspots/reset');
        router.app.$store.dispatch('prototypes/reset');
        router.app.$store.dispatch('screens/reset');
        router.app.$store.dispatch('preview/reset');
    }

    return next();
});

router.afterEach((to, from) => {
    router.app.$clearFormFieldErrors();

    router.app.$refreshUser();
});

export default router;
