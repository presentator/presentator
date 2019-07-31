<template>
    <app-sidebar>
        <router-link :to="{name: 'projects'}"
            class="menu-item"
            active-class="active"
            v-tooltip.right="$t('Projects')"
        >
            <i class="fe fe-grid"></i>
        </router-link>

        <router-link v-if="loggedUser.isSuperUser"
            :to="{name: 'users'}"
            class="menu-item"
            active-class="active"
            v-tooltip.right="$t('Users')"
        >
            <i class="fe fe-users"></i>
        </router-link>

        <router-link v-else-if="loggedUser.id"
            :to="{name: 'user', params: {userId: loggedUser.id}}"
            class="menu-item"
            active-class="active"
            v-tooltip.right="$t('Profile settings')"
        >
            <i class="fe fe-user"></i>
        </router-link>
    </app-sidebar>
</template>

<script>
import { mapState } from 'vuex';
import AppSidebar   from '@/views/base/AppSidebar';

export default {
    name: 'home-sidebar',
    components: {
        'app-sidebar': AppSidebar,
    },
    computed: {
        ...mapState({
            loggedUser: state => state.user.user,
        }),
    }
}
</script>
