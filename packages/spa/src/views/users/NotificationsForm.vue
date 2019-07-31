<template>
    <form @submit.prevent="saveChanges()">
        <form-field name="notifyOnEachComment" class="form-group form-group-switch">
            <input type="checkbox" id="notify_on_comment" v-model="notifyOnEachComment">
            <label for="notify_on_comment">{{ $t('Receive an email when a new screen comment is added.') }}</label>
        </form-field>
        <form-field name="notifyOnMention"
            class="form-group form-group-switch"
            :class="{'disabled-block': notifyOnEachComment}"
        >
            <input type="checkbox" id="notify_on_mention" v-model="notifyOnMention">
            <label for="notify_on_mention">{{ $t('Receive an email when someone mentions you.') }}</label>
        </form-field>
        <div class="flex-block">
            <button class="btn btn-primary btn-cons-lg btn-loader" :class="{'btn-loader-active': isProcessing}">
                <span class="txt">{{ $t('Save changes') }}</span>
            </button>
            <router-link v-show="!isProcessing" :to="{name: 'home'}" class="link-hint m-l-small">
                <span class="txt">{{ $t('Cancel') }}</span>
            </router-link>
        </div>
    </form>
</template>

<script>
import { mapState } from 'vuex';
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import User         from '@/models/User';

export default {
    name: 'user-notifications-form',
    props: {
        user: {
            type:     User,
            required: true,
        },
    },
    data() {
        return {
            notifyOnEachComment: true,
            notifyOnMention:     true,
            isProcessing:        false,
        }
    },
    computed: {
        ...mapState({
            loggedUser: state => state.user.user,
        }),
    },
    watch: {
        notifyOnEachComment(newVal, oldVal) {
            if (newVal) {
                this.notifyOnMention = true;
            }
        },
        'user.id': function (newVal, oldVal) {
            this.loadForm();
        }
    },
    mounted() {
        this.loadForm();
    },
    methods: {
        refreshUser(userData) {
            if (CommonHelper.isEmpty(userData)) {
                return;
            }

            this.user.load(userData);

            this.loadForm();

            // update the global logged in user data model if
            // the modified user is the current logged in
            if (this.user.id == this.loggedUser.id) {
                this.$store.dispatch('user/set', userData);
            }
        },
        loadForm() {
            this.notifyOnEachComment = this.user.getSetting('notifyOnEachComment', false);
            this.notifyOnMention     = this.user.getSetting('notifyOnMention', false);
        },
        saveChanges() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Users.update(this.user.id, {
                notifyOnEachComment: this.notifyOnEachComment,
                notifyOnMention:     this.notifyOnMention,
            }).then((response) => {
                this.$toast(this.$t('Successfully updated notification settings.'));

                this.refreshUser(CommonHelper.getNestedVal(response, 'data', {}));
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
