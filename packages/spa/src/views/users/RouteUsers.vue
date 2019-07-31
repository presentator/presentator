<template>
    <div class="app-body">
        <home-sidebar></home-sidebar>

        <main class="app-main">
            <header class="app-header">
                <nav class="breadcrumbs">
                    <div class="breadcrumb-item active">{{ $t('Users') }}</div>
                </nav>

                <div class="flex-fill-block"></div>

                <button class="btn btn-cons-lg btn-success m-l-10" @click.prevent="openCreatePopup()">
                    <i class="fe fe-plus"></i>
                    <span class="txt">{{ $t('New user') }}</span>
                </button>
            </header>

            <users-listing></users-listing>

            <div class="flex-fill-block"></div>

            <app-footer class="m-t-base"></app-footer>
        </main>

        <user-create-popup ref="createPopup"></user-create-popup>
    </div>
</template>

<script>
import { mapState }    from 'vuex';
import AppFooter       from '@/views/base/AppFooter';
import HomeSidebar     from '@/views/base/HomeSidebar';
import UsersListing    from '@/views/users/UsersListing';
import UserCreatePopup from '@/views/users/UserCreatePopup';

export default {
    name: 'users-index',
    components: {
        'app-footer':        AppFooter,
        'home-sidebar':      HomeSidebar,
        'users-listing':     UsersListing,
        'user-create-popup': UserCreatePopup,
    },
    beforeMount() {
        this.$setDocumentTitle(() => this.$t('Users'));
    },
    methods: {
        openCreatePopup() {
            if (this.$refs.createPopup) {
                this.$refs.createPopup.open();
            }
        },
    },
}
</script>
