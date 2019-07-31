<template>
    <form @submit.prevent="share()">
        <popup ref="popup">
            <template v-slot:header>
                <span class="side-ctrl side-ctrl-left"
                    v-tooltip.right="$t('Back')"
                    @click.prevent="close()"
                >
                    <i class="fe fe-arrow-left"></i>
                </span>

                <h4 class="title">{{ $t('Project links - Share') }}</h4>
            </template>
            <template v-slot:content>
                <div class="alert alert-light-border txt-center m-b-20">
                    <strong>{{ link.fullUrl }}</strong>
                </div>

                <form-field class="required" name="email">
                    <label for="link_share_email">{{ $t('Email(s)') }}</label>
                    <input type="text" v-model="email" id="link_share_email" required>
                    <p class="help-block">{{ $t('Separate multiple email addresses with comma.') }}</p>
                </form-field>

                <form-field name="message">
                    <label for="link_share_message">{{ $t('Message') }}</label>
                    <textarea v-model="message" id="link_share_message" :placeholder="$t('eg.') + ' ' + $t('The password is...')"></textarea>
                </form-field>
            </template>
            <template v-slot:footer>
                <button type="button" class="btn btn-light-border" @click.prevent="close()">
                    <span class="txt">{{ $t('Back') }}</span>
                </button>
                <button type="submit" class="btn btn-primary btn-cons btn-loader" :class="{'btn-loader-active': isProcessing}">
                    <span class="txt">{{ $t('Share') }}</span>
                </button>
            </template>
        </popup>
    </form>
</template>

<script>
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import Popup        from '@/components/Popup';
import ProjectLink  from '@/models/ProjectLink';

export default {
    name: 'share-link-popup',
    components: {
        'popup': Popup,
    },
    data() {
        return {
            link:         new ProjectLink,
            email:        '',
            message:      '',
            isProcessing: false,
        }
    },
    methods: {
        resetForm() {
            this.isProcessing = false;
            this.email        = '';
            this.message      = '';
        },
        open(linkData) {
            this.resetForm();

            if (!CommonHelper.isEmpty(linkData)) {
                this.link.load(linkData);
            }

            this.$refs.popup.open();

            this.$emit('open');
        },
        close() {
            this.$refs.popup.close();

            this.$emit('close');
        },
        share() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.ProjectLinks.share(this.link.id, {
                email:   this.email,
                message: this.message,
            }).then((response) => {
                this.$toast(this.$t('Successfully shared project link.'));

                this.resetForm();

                this.close();
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        }
    },
}
</script>
