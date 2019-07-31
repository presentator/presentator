<template>
    <aside class="app-sidebar">
        <div class="app-sidebar-section app-sidebar-header">
            <router-link :to="{name: 'home'}" class="logo" title="Presentator">
                <img src="@/assets/images/logogram.svg" alt="Presentator logo" class="img" width="41" height="53">
            </router-link>
        </div>

        <div class="app-sidebar-section app-sidebar-content">
            <nav class="main-menu">
                <slot></slot>
            </nav>
        </div>

        <div class="app-sidebar-section app-sidebar-footer">
            <nav class="meta-menu m-b-small">
                <div class="menu-item"
                    :class="{'active': ($refs.notificationsPanel && $refs.notificationsPanel.isActive)}"
                    v-tooltip.right="$t('Notifications')"
                    @click.prevent="$refs.notificationsPanel && $refs.notificationsPanel.toggle()"
                >
                    <span v-if="unreadComments.length" class="beacon beacon-danger"></span>

                    <i class="fe fe-bell"></i>
                </div>
            </nav>

            <div v-if="loggedUser && loggedUser.id" class="user-profile">
                <figure class="avatar" v-tooltip.right="loggedUser.identifier">
                    <img v-if="loggedUser.getAvatar('small')"
                        :src="loggedUser.getAvatar('small')"
                        alt="User avatar"
                    >
                    <i class="fe fe-user" v-else></i>
                </figure>

                <toggler class="dropdown dropdown-sm">
                    <router-link :to="{name: 'user', params: {userId: loggedUser.id}}" class="dropdown-item">
                        <i class="fe fe-settings"></i>
                        <span class="txt">{{ $t('Settings') }}</span>
                    </router-link>
                    <hr>
                    <div class="dropdown-item link-danger" @click.prevent="logout()">
                        <i class="fe fe-log-out"></i>
                        <span class="txt">{{ $t('Sign out') }}</span>
                    </div>
                </toggler>
            </div>
        </div>

        <notifications-panel ref="notificationsPanel"></notifications-panel>
    </aside>
</template>

<script>
import { mapState }       from 'vuex';
import NotificationsPanel from '@/views/base/NotificationsPanel';

export default {
    name: 'app-sidebar',
    components: {
        'notifications-panel': NotificationsPanel,
    },
    computed: {
        ...mapState({
            loggedUser:     state => state.user.user,
            unreadComments: state => state.notifications.unreadComments,
        }),
    },
    methods: {
        logout() {
            this.$logout();

            this.$toast(this.$t('You have been signed out successfully.'));
        },
    },
}
</script>
