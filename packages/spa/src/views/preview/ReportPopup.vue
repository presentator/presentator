<template>
    <form @submit.prevent="submitForm()">
        <popup class="popup-sm" ref="popup">
            <template v-slot:header>
                <h4 class="title">{{ $t('Report this project for spam or abusive content') }}</h4>
            </template>
            <template v-slot:content>
                <form-field name="details">
                    <label for="report_details">{{ $t('Additional details') }}</label>
                    <textarea v-model.trim="details" id="report_details"></textarea>
                </form-field>
            </template>
            <template v-slot:footer>
                <button type="button" class="btn btn-light-border" @click.prevent="close()">
                    <span class="txt">{{ $t('Cancel') }}</span>
                </button>
                <button type="submit" class="btn btn-primary btn-cons btn-loader" :class="{'btn-loader-active': isProcessing}">
                    <span class="txt">{{ $t('Report') }}</span>
                </button>
            </template>
        </popup>
    </form>
</template>

<script>
import { mapState } from 'vuex';
import ApiClient    from '@/utils/ApiClient';
import Popup        from '@/components/Popup';

export default {
    name: 'report-popup',
    components: {
        'popup': Popup,
    },
    data() {
        return {
            isProcessing: false,
            details:      '',
        }
    },
    computed: {
        ...mapState({
            previewToken: state => state.preview.previewToken,
        }),
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
            this.details = '';
        },
        submitForm() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Previews.report(this.previewToken, this.details).then((response) => {
                this.$toast(this.$t('Thank you, we will investigate and remove the project if found inappropriate.'), 'success', 6000);

                this.$router.replace({ name: 'home' });
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
