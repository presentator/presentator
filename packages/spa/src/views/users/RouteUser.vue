<template>
    <div class="app-body">
        <home-sidebar></home-sidebar>

        <main class="app-main">
            <header class="app-header">
                <nav class="breadcrumbs">
                    <template v-if="loggedUser.isSuperUser">
                        <router-link :to="{name: 'users'}" class="breadcrumb-item">{{ $t('Users') }}</router-link>

                        <div class="breadcrumb-item active">
                            <span class="txt">{{ $t('Edit user') }}</span>
                            <span v-if="loggedUser.id == user.id" class="label label-transp-primary m-l-10 v-align-middle">{{ $t('You') }}</span>
                        </div>
                    </template>
                    <template v-else>
                        <div class="breadcrumb-item active">{{ $t('Account settings') }}</div>
                    </template>
                </nav>

                <div class="flex-fill-block"></div>

                <small v-if="!isLoading" class="link-fade" @click.prevent="deleteUser()">
                    {{ $t('Delete account') }}
                </small>
            </header>

            <div v-if="isLoading || !user.id" class="block txt-center txt-hint">
                <span class="loader loader-lg loader-blend"></span>
            </div>

            <div v-else class="tabs">
                <div class="tabs-header">
                    <a v-for="(label, tab) in tabsList"
                        :key="tab"
                        :href="'?tab=' + tab" class="tab-item"
                        :class="{'active': activeTab === tab}"
                        tabindex="0"
                        @click.prevent="changeTab(tab)"
                    >
                        {{ label }}
                    </a>
                </div>
                <div class="tabs-content">
                    <!-- Profile tab -->
                    <div class="tab-item" :class="{'active': activeTab === 'profile'}">
                        <profile-form class="container-wrapper m-l-0" :user="user" :changeEmail="authMethods.emailPassword"></profile-form>
                    </div>

                    <!-- Email notifications tab -->
                    <div class="tab-item" :class="{'active': activeTab === 'notifications'}">
                        <notifications-form class="container-wrapper m-l-0" :user="user"></notifications-form>
                    </div>

                    <!-- Security tab -->
                    <div v-if="authMethods.emailPassword" class="tab-item" :class="{'active': activeTab === 'security'}">
                        <security-form class="container-wrapper m-l-0" :user="user"></security-form>
                    </div>
                </div>
            </div>

            <div class="flex-fill-block"></div>

            <app-footer class="m-t-base"></app-footer>
        </main>
    </div>
</template>

<script>
import { mapState }      from 'vuex';
import ApiClient         from '@/utils/ApiClient';
import CommonHelper      from '@/utils/CommonHelper';
import AppFooter         from '@/views/base/AppFooter';
import HomeSidebar       from '@/views/base/HomeSidebar';
import ProfileForm       from '@/views/users/ProfileForm';
import NotificationsForm from '@/views/users/NotificationsForm';
import SecurityForm      from '@/views/users/SecurityForm';
import User              from '@/models/User';

export default {
    name: 'users-edit',
    components: {
        'app-footer':         AppFooter,
        'home-sidebar':       HomeSidebar,
        'profile-form':       ProfileForm,
        'notifications-form': NotificationsForm,
        'security-form':      SecurityForm,
    },
    data() {
        return {
            activeTab: 'profile',
            tabsList: {
                'profile':       this.$t('Profile'),
                'notifications': this.$t('Email notifications'),
                'security':      this.$t('Security'),
            },
            isLoadingUser: false,
            user: new User,
            authMethods: {},
            isLoadingAuthMethods: false,
        }
    },
    computed: {
        ...mapState({
            loggedUser: state => state.user.user,
        }),

        isLoading: function () {
            return this.isLoadingUser || this.isLoadingAuthMethods;
        },
    },
    watch: {
        '$route.params.userId': function (newVal, oldVal) {
            this.loadUser(newVal);
        },
    },
    beforeMount() {
        if (this.loggedUser.isSuperUser) {
            this.$setDocumentTitle(() => this.$t('Edit users'));
        } else {
            this.$setDocumentTitle(() => this.$t('Account settings'));
        }

        let queryTab = CommonHelper.getNestedVal(this.$route, 'query.tab');
        if (this.tabsList[queryTab]) {
            this.changeTab(queryTab);
        }

        this.loadAuthMethods();

        this.loadUser(this.$route.params.userId);
    },
    methods: {
        changeTab(tab) {
            this.activeTab = tab;

            this.$router.replace({
                name:   this.$route.name,
                params: Object.assign({}, this.$route.params),
                query:  Object.assign({}, this.$route.query, {tab: this.activeTab}),
            });
        },
        loadAuthMethods() {
            if (this.isLoadingAuthMethods) {
                return;
            }

            this.isLoadingAuthMethods = true;

            ApiClient.Users.getAuthMethods().then((response) => {
                this.authMethods = response.data || { emailPassword: true, clients: [] };

                if (!this.authMethods.emailPassword) {
                    delete this.tabsList.security;

                    if (this.activeTab === 'security') {
                        this.changeTab(Object.keys(this.tabsList)[0]);
                    }
                }
            }).catch((err) => {
                // silence errors...
            }).finally(() => {
                this.isLoadingAuthMethods = false;
            });
        },
        loadUser(id) {
            id = id || this.$route.params.userId;

            if (id == this.loggedUser.id) {
                this.setUser(this.loggedUser);
                return;
            }

            if (this.isLoadingUser) {
                return;
            }

            this.isLoadingUser = true;

            ApiClient.Users.getOne(id).then((response) => {
                this.setUser(response.data);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingUser = false;
            });
        },
        setUser(userData) {
            this.user.load(userData);
        },
        deleteUser() {
            var promptEmail = window.prompt(
                this.$t('WARNING this action is irreversible!') + '\n' +
                this.$t('Please type the email address of the user you want to delete.')
            );

            if (promptEmail !== this.user.email) {
                if (promptEmail) {
                    this.$toast(this.$t('Deleting canceled - the provided email address does not match.'), 'warning');
                }

                return;
            }

            ApiClient.Users.delete(this.user.id).finally(() => {
                this.$toast(this.$t('Successfully deleted user "{user}".', {user: this.user.identifier}));

                if (this.user.id == this.loggedUser.id) {
                    this.$logout();
                } else if (this.loggedUser.isSuperUser) {
                    this.$router.replace({ name: 'users' });
                } else {
                    this.$router.replace({ name: 'home' });
                }
            });
        }
    },
}
</script>
