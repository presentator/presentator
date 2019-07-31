<template>
    <form @submit.prevent="submitForm()">
        <popup class="popup-sm" ref="popup">
            <template v-slot:header>
                <h4 class="title">{{ $t('Help us improve Presentator') }}</h4>
            </template>
            <template v-slot:content>
                <div class="alert alert-light-border txt-center m-b-small">
                    <p>
                        {{ $t('Found a bug or have a feature request?') }} <br>

                        <i18n path="Fill the form below OR {issuesLink}.">
                            <a place="issuesLink" :href="$getAppConfig('VUE_APP_ISSUES_URL')" target="_blank" rel="noopener">{{ $t('create a GitHub issue') }}</a>
                        </i18n>
                    </p>
                </div>

                <form-field class="required" name="message">
                    <label for="feedback_message">{{ $t('Message') }}</label>
                    <textarea v-model.trim="message" id="feedback_message" required></textarea>
                </form-field>
            </template>
            <template v-slot:footer>
                <button type="button" class="btn btn-light-border" @click.prevent="close()">
                    <span class="txt">{{ $t('Cancel') }}</span>
                </button>
                <button type="submit" class="btn btn-primary btn-cons btn-loader" :class="{'btn-loader-active': isProcessing}">
                    <span class="txt">{{ $t('Send feedback') }}</span>
                </button>
            </template>
        </popup>
    </form>
</template>

<script>
import ApiClient from '@/utils/ApiClient';
import Popup     from '@/components/Popup';

export default {
    name: 'feedback-popup',
    components: {
        'popup': Popup,
    },
    data() {
        return {
            isProcessing: false,
            message:      '',
        }
    },
    methods: {
        open() {
            this.resetForm();

            this.$refs.popup.open();

            this.$emit('open');
        },
        close() {
            this.$refs.popup.close();

            this.$emit('close');
        },
        resetForm() {
            this.message = '';
        },
        submitForm() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Users.sendFeedback(this.message).then((response) => {
                this.$toast(this.$t('Thank you for the feedback!'));

                this.close();

                this.resetForm();
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
