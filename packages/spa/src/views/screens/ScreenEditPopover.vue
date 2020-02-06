<template>
    <toggler ref="popover"
        class="popover screen-edit-popover"
        tag="form"
        :hideOnChildClick="false"
        @submit.native.prevent="submitForm()"
    >
        <form-field name="title">
            <label for="screen_title">{{ $t('Title') }}</label>
            <input type="text" id="screen_title" v-model.trim="title" maxlength="255" required>
        </form-field>

        <div class="row">
            <div class="col-6">
                <div class="form-group form-group">
                    <label for="screen_alignment">{{ $t('Alignment') }}</label>
                    <div class="switch-group">
                        <div class="switch">
                            <input type="radio" v-model="alignment" value="left" id="screen_alignment_radio_left">
                            <label for="screen_alignment_radio_left">{{ $t('Left') }}</label>
                        </div>
                        <div class="switch">
                            <input type="radio" v-model="alignment" value="center" id="screen_alignment_radio_center">
                            <label for="screen_alignment_radio_center">{{ $t('Center') }}</label>
                        </div>
                        <div class="switch">
                            <input type="radio" v-model="alignment" value="right" id="screen_alignment_radio_right">
                            <label for="screen_alignment_radio_right">{{ $t('Right') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <form-field name="background">
                    <label for="screen_background">{{ $t('Background') }}</label>
                    <div class="input-group">
                        <input type="color" v-model="background" id="screen_background">
                        <label for="screen_background" class="input-addon txt-monospace p-l-0">{{ background }}</label>
                    </div>
                </form-field>
            </div>
        </div>
        <div class="row align-items-center">
            <div class="col-6">
                <div class="form-group">
                    <input type="checkbox" id="screen_fixed_header_check" v-model="hasFixedHeader">
                    <label for="screen_fixed_header_check">{{ $t('Has fixed header') }}</label>
                </div>
            </div>
            <div class="col-6">
                <form-field class="form-group-sm" name="fixedHeader" v-show="hasFixedHeader">
                    <div class="input-group">
                        <input type="number" v-model.number="fixedHeader" min="0">
                        <div class="input-addon">px</div>
                    </div>
                </form-field>
            </div>
        </div>
        <div class="row align-items-center">
            <div class="col-6">
                <div class="form-group m-b-0">
                    <input type="checkbox" id="screen_fixed_footer_check" v-model="hasFixedFooter">
                    <label for="screen_fixed_footer_check">{{ $t('Has fixed footer') }}</label>
                </div>
            </div>
            <div class="col-6">
                <form-field class="m-b-0 form-group-sm" name="fixedFooter" v-show="hasFixedFooter">
                    <div class="input-group">
                        <input type="number" v-model.number="fixedFooter" min="0">
                        <div class="input-addon">px</div>
                    </div>
                </form-field>
            </div>
        </div>

        <hr class="m-t-20 m-b-20">

        <div class="flex-block">
            <div class="flex-fill-block txt-left">
                <span v-if="isReplacing" class="loader txt-hint"></span>
                <span v-else ref="replaceHandle" class="link-hint">{{ $t('Replace screen') }}</span>
            </div>
            <button type="submit" class="btn btn-primary btn-cons btn-loader" :class="{'btn-loader-active': isProcessing}">
                <span class="txt">{{ $t('Save changes') }}</span>
            </button>
        </div>
    </toggler>
</template>

<script>
import { mapActions } from 'vuex';
import Dropzone       from 'dropzone';
import ApiClient      from '@/utils/ApiClient';
import CommonHelper   from '@/utils/CommonHelper';
import Screen         from '@/models/Screen';

const defaultFormData = {
    title:          '',
    background:     '#ffffff',
    alignment:      'center',
    fixedHeader:    0,
    fixedFooter:    0,
    hasFixedHeader: false,
    hasFixedFooter: false,
};

export default {
    name: 'screen-edit-popover',
    props: {
        screen: {
            type:     Screen,
            required: true,
        },
    },
    data() {
        return {
            dropzone:       null,
            isProcessing:   false,
            isReplacing:    false,
            title:          defaultFormData.title,
            background:     defaultFormData.background,
            alignment:      defaultFormData.alignment,
            fixedHeader:    defaultFormData.fixedHeader,
            fixedFooter:    defaultFormData.fixedFooter,
            hasFixedHeader: defaultFormData.hasFixedHeader,
            hasFixedFooter: defaultFormData.hasFixedFooter,
        }
    },
    watch: {
        screen(newVal, oldVal) {
            this.loadForm(newVal);

            this.initReplaceHandle();
        },
    },
    mounted() {
        this.loadForm();

        this.initReplaceHandle();
    },
    destroyed() {
        if (this.dropzone) {
            this.dropzone.destroy();
        }
    },
    methods: {
        ...mapActions({
            'setErrors': 'form-field/setErrors',
        }),

        loadForm(data) {
            data = data || this.screen || {};

            this.title          = data.title       || defaultFormData.title;
            this.background     = data.background  || defaultFormData.background;
            this.alignment      = data.alignment   || defaultFormData.alignment;
            this.fixedHeader    = data.fixedHeader || defaultFormData.fixedHeader;
            this.fixedFooter    = data.fixedFooter || defaultFormData.fixedFooter;
            this.hasFixedHeader = data.fixedHeader ? true : false;
            this.hasFixedFooter = data.fixedFooter ? true : false;

            this.setErrors({});
        },
        submitForm() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Screens.update(this.screen.id, {
                'title':       this.title,
                'background':  this.background,
                'alignment':   this.alignment,
                'fixedHeader': this.hasFixedHeader ? this.fixedHeader : 0,
                'fixedFooter': this.hasFixedFooter ? this.fixedFooter : 0,
            }).then((response) => {
                this.screen.load(response.data);

                this.loadForm();

                this.$toast(this.$t('Successfully updated screen "{title}".', {title: this.screen.title}));

                if (this.$refs.popover) {
                    this.$refs.popover.hide();
                }

                this.$emit('updated', this.screen.id);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
        initReplaceHandle() {
            Dropzone.autoDiscover = false;

            // reset
            if (this.dropzone) {
                this.dropzone.destroy();

                this.isReplacing = false;
            }

            this.dropzone = new Dropzone(this.$refs.replaceHandle, {
                url: (ApiClient.$baseUrl + '/screens/' + encodeURIComponent(this.screen.id)),
                method: 'put',
                paramName: 'file',
                parallelUploads: 1,
                uploadMultiple: false,
                thumbnailWidth: null,
                thumbnailHeight: null,
                addRemoveLinks: false,
                createImageThumbnails: false,
                previewTemplate: '<div style="display: none"></div>',
            });

            this.dropzone.on('addedfile', (file) => {
                if (!window.confirm(
                    this.$t('Replacing could result in hotspots and comments misplacement if the new screen image has different dimensions from the original.') +
                    '\n' +
                    this.$t('Do you still want to proceed?')
                )) {
                    this.dropzone.removeAllFiles(true);
                    return;
                }

                this.dropzone.options.headers = Object.assign(this.dropzone.options.headers || {}, {
                    'Authorization': ('Bearer ' + ApiClient.$token),
                });
            });

            this.dropzone.on('sending', (file, xhr, formData) => {
                this.isReplacing = true;
            });

            this.dropzone.on('queuecomplete', () => {
                this.isReplacing = false;
            });

            this.dropzone.on('success', (file, response) => {
                this.screen.load(response);

                if (this.$refs.popover) {
                    this.$refs.popover.hide();
                }

                this.$emit('updated', this.screen.id);
            });

            this.dropzone.on('error', (file, response, xhr) => {
                var message = CommonHelper.getNestedVal(response, 'errors.file', this.$t('An error occurred while uploading the screen.'));

                this.$toast(message, 'danger');
            });
        },
    },
}
</script>

<style lang="scss">
.screen-edit-popover {
    width: 450px;
}
</style>
