<template>
    <div class="auth-container">
        <div class="flex-fill-block"></div>

        <div class="container-wrapper container-wrapper-sm">
            <app-header></app-header>

            <div class="clearfix m-b-large"></div>

            <div class="panel auth-panel">
                <h3 class="panel-title">{{ $t('Error {code}', {code: 404}) }}</h3>

                <div class="panel-content txt-center">
                    <p>{{ $t('Oops, the page you were looking for does not exist or is temporary unavailable.') }}</p>

                    <div class="clearfix m-b-small"></div>

                    <router-link :to="{name: 'home'}" class="btn btn-primary btn-lg btn-block">
                        <span class="txt">{{ $t('Go to homepage') }}</span>
                        <i class="fe fe-arrow-right-circle"></i>
                    </router-link>
                </div>
            </div>

            <div class="clearfix m-b-base"></div>
        </div>

        <div class="flex-fill-block m-b-base"></div>

        <app-footer></app-footer>
    </div>
</template>

<script>
import AppHeader from '@/views/base/AppHeader';
import AppFooter from '@/views/base/AppFooter';

export default {
    name: 'not-found',
    components: {
        'app-header': AppHeader,
        'app-footer': AppFooter,
    },
    beforeMount() {
        this.$setDocumentTitle(() => this.$t('Error {code}', {code: 404}));
    },
}
</script>
