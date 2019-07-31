<template>
    <form @submit.stop.prevent="submitForm()">
        <popup class="popup-sm" ref="popup">
            <template v-slot:header>
                <h4 class="title">{{ $t('Change email') }}</h4>
            </template>
            <template v-slot:content>
                <form-field name="email">
                    <label for="profile_new_email">{{ $t('New email') }}</label>
                    <input type="email" v-model="newEmail" id="profile_new_email" required>
                </form-field>
            </template>
            <template v-slot:footer>
                <button v-show="!isProcessing" type="button" class="btn btn-light-border" @click.prevent="close()">
                    <span class="txt">{{ $t('Cancel') }}</span>
                </button>
                <div class="flex-fill-block"></div>
                <button class="btn btn-primary btn-cons btn-loader" :class="{'btn-loader-active': isProcessing}">
                    <span class="txt">{{ $t('Send verification link') }}</span>
                </button>
            </template>
        </popup>
    </form>
</template>

<script>
import ApiClient from '@/utils/ApiClient';
import Popup     from '@/components/Popup';
import User      from '@/models/User';

export default {
    name: 'user-email-change-popup',
    components: {
        'popup': Popup,
    },
    props: {
        user: {
            type:     User,
            required: true,
        },
    },
    data() {
        return {
            isProcessing: false,
            newEmail: '',
        }
    },
    methods: {
        open() {
            this.isProcessing = false;

            this.resetForm();

            this.$refs.popup.open();
        },
        close() {
            this.isProcessing = false;

            this.$refs.popup.close();
        },
        resetForm() {
            this.newEmail = '';
        },
        submitForm() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Users.requestEmailChange(this.user.email, {
                newEmail: this.newEmail,
            }).then((response) => {
                this.close();

                this.resetForm();

                this.$toast(this.$t('A verification link to your new email address was successfully sent.'));
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
