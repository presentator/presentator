<template>
    <form @submit.prevent="saveChanges()">
        <div v-if="!loggedUser.isSuperUser" class="row">
            <div class="col-lg-6">
                <form-field class="required" name="oldPassword">
                    <label for="security_old_password">{{ $t('Current password') }}</label>
                    <input type="password" v-model="oldPassword" id="security_old_password" required>
                </form-field>
            </div>
            <div class="col-lg-6"></div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <form-field class="required" name="newPassword">
                    <label for="security_new_password">{{ $t('New password') }}</label>
                    <input type="password" v-model="newPassword" id="security_new_password" required>
                </form-field>
            </div>
            <div class="col-lg-6">
                <form-field class="required" name="newPasswordConfirm">
                    <label for="security_new_password_confirm">{{ $t('Confirm new password') }}</label>
                    <input type="password" v-model="newPasswordConfirm" id="security_new_password_confirm" required>
                </form-field>
            </div>
        </div>

        <div class="flex-block">
            <button class="btn btn-primary btn-cons-lg btn-loader" :class="{'btn-loader-active': isProcessing}">
                <span class="txt">{{ $t('Save changes') }}</span>
            </button>
            <router-link :to="{name: 'home'}" class="link-hint m-l-small" v-show="!isProcessing">
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
    name: 'user-security-form',
    props: {
        user: {
            type:     User,
            required: true,
        },
    },
    data() {
        return {
            oldPassword:        '',
            newPassword:        '',
            newPasswordConfirm: '',
            isProcessing:       false,
        }
    },
    computed: {
        ...mapState({
            loggedUser: state => state.user.user,
        }),
    },
    mounted() {
        this.reset();
    },
    methods: {
        reset() {
            this.oldPassword        = '';
            this.newPassword        = '';
            this.newPasswordConfirm = '';
        },
        saveChanges() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Users.update(this.user.id, {
                oldPassword:        (!this.user.isSuperUser ? this.oldPassword : ''),
                newPassword:        this.newPassword,
                newPasswordConfirm: this.newPasswordConfirm,
            }).then((response) => {
                this.$toast(this.$t('Successfully updated user password.'));

                var userData = CommonHelper.getNestedVal(response, 'data', {});

                if (!CommonHelper.isEmpty(userData)) {
                    this.user.load(userData);

                    // relogin if the modified user is the current logged in one
                    if (this.user.id == this.loggedUser.id) {
                        this.relogin();
                    }
                }

                this.reset();
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
        relogin() {
            this.isProcessing = true;

            ApiClient.Users.login(
                this.user.email,
                this.newPassword
            ).then((response) => {
                this.$loginByResponse(response, false);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
