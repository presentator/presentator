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

let firestoreUnsubscribe = null;
let intervalListenerId   = null;

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
                this.startUserNotificationsListener();
            }
        },
        '$route.name': function (newVal, oldVal) {
            if (!this.$route.meta.requiresAuth) {
                this.stopUserNotificationsListener();
            } else {
                this.startUserNotificationsListener();
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
            loadLocalUser:          'user/loadLocal',
            loadUserUnreadComments: 'notifications/loadUserUnreadComments',
        }),

        // Bind user notifications listener
        // -----------------------------------------------------------
        startUserNotificationsListener(forceReinit = false) {
            if (!forceReinit && (firestoreUnsubscribe || intervalListenerId)) {
                return; // already inited
            }

            if (AppConfig.isFirestoreConfigured()) {
                this.startFirestoreListener();
            } else {
                this.startIntervalListener();
            }
        },
        stopUserNotificationsListener() {
            this.stopIntervalListener();
            this.stopFirestoreListener();
        },

        // Cloud Firestore notifications listener
        // ---
        startFirestoreListener() {
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

                    // stop previous listener (if any)
                    this.stopFirestoreListener();

                    // start a new listener
                    firestoreUnsubscribe = db.collection(AppConfig.get('VUE_APP_FIRESTORE_COLLECTION'))
                        .where('u' + this.loggedUser.id, '>', 0)
                        .onSnapshot((querySnapshot) => {
                            this.loadUserUnreadComments().catch((err) => {});
                        });
                });
            });
        },
        stopFirestoreListener() {
            if (CommonHelper.isFunction(firestoreUnsubscribe)) {
                firestoreUnsubscribe();
            }
        },

        // Interval notifications listener (fallback if Firestore is not configured)
        // ---
        startIntervalListener() {
            this.stopIntervalListener();

            this.loadUserUnreadComments().catch((err) => {});

            intervalListenerId = setInterval(() => {
                this.loadUserUnreadComments().catch((err) => {});
            }, AppConfig.get('VUE_APP_NOTIFICATIONS_INTERVAL') || 60000);
        },
        stopIntervalListener() {
            if (intervalListenerId) {
                clearInterval(intervalListenerId);
                intervalListenerId = null;
            }
        },
    },
}
</script>

<style src="@/assets/sass/app.scss" lang="scss"></style>
