<template>
    <div id="app">
        <router-view></router-view>

        <toast></toast>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AppConfig    from '@/utils/AppConfig';
import CommonHelper from '@/utils/CommonHelper';
import Toast        from '@/components/Toast';

let userNotificationsUnsibscribe = null;

export default {
    name: 'app',
    components: {
        'toast': Toast,
    },
    computed: {
        ...mapState({
            loggedUser: state => state.user.user,
        }),
    },
    watch: {
        'loggedUser.id': function (newVal, oldVal) {
            if (!newVal || !newVal.id) {
                this.stopUserNotificationsListener();
            } else {
                this.initUserNotificationsListener();
            }
        },
        '$route.name': function (newVal, oldVal) {
            if (!this.$route.meta.requiresAuth) {
                this.stopUserNotificationsListener();
            } else {
                this.initUserNotificationsListener();
            }
        }
    },
    beforeMount() {
        this.$setDocumentTitle(''); // reset

        if (!this.loggedUser || !this.loggedUser.id) {
            this.loadLocalUser();
        }
    },
    beforeDestroy() {
        this.stopUserNotificationsListener();
    },
    methods: {
        ...mapActions({
            loadLocalUser:      'user/loadLocal',
            loadUnreadComments: 'notifications/loadUnreadComments',
        }),

        stopUserNotificationsListener() {
            if (CommonHelper.isFunction(userNotificationsUnsibscribe)) {
                userNotificationsUnsibscribe();
            }
        },
        initUserNotificationsListener(reinit = false) {
            if (
                // already subscribed
                (!reinit && userNotificationsUnsibscribe) ||
                // firestore is not configured
                (
                    !AppConfig.get('VUE_APP_FIRESTORE_PROJECT_ID') ||
                    !AppConfig.get('VUE_APP_FIRESTORE_COLLECTION')
                )
            ) {
                return;
            }

            import('firebase/app').then((firebase) => {
                import('firebase/firestore').then(() => {
                    if (!this.loggedUser.id) {
                        return; // guest views and previews have their own notification listeners
                    }

                    if (!firebase.apps.length) {
                        // initialize Cloud Firestore through Firebase
                        firebase.initializeApp({ projectId: AppConfig.get('VUE_APP_FIRESTORE_PROJECT_ID') });
                    }

                    var db = firebase.firestore();

                    this.stopUserNotificationsListener();

                    // start a new listener
                    userNotificationsUnsibscribe = db.collection(AppConfig.get('VUE_APP_FIRESTORE_COLLECTION'))
                        .where('u' + this.loggedUser.id, '>', 0)
                        .onSnapshot((querySnapshot) => {
                            if (this.loggedUser.id) {
                                this.loadUnreadComments();
                            }
                        });
                });
            });
        },
    },
}
</script>

<style src="@/assets/sass/app.scss" lang="scss"></style>
