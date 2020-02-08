<template>
    <toggler ref="popover"
        class="popover screen-edit-popover"
        tag="form"
        :hideOnChildClick="false"
        @submit.native.prevent="saveChanges()"
    >
        <form-field name="title">
            <label for="screen_title">{{ $t('Title') }}</label>
            <input type="text" id="screen_title" v-model.trim="screen.title" maxlength="255" required @change="saveChanges()">
        </form-field>

        <div class="row">
            <div class="col-6">
                <div class="form-group form-group">
                    <label for="screen_alignment">
                        <span class="txt">{{ $t('Alignment') }}</span>
                        <screen-bulk-setting-handle :screen="screen" setting="alignment"></screen-bulk-setting-handle>
                    </label>
                    <div class="switch-group">
                        <div class="switch">
                            <input type="radio" v-model="screen.alignment" value="left" id="screen_alignment_radio_left" @change="saveChanges()">
                            <label for="screen_alignment_radio_left">{{ $t('Left') }}</label>
                        </div>
                        <div class="switch">
                            <input type="radio" v-model="screen.alignment" value="center" id="screen_alignment_radio_center" @change="saveChanges()">
                            <label for="screen_alignment_radio_center">{{ $t('Center') }}</label>
                        </div>
                        <div class="switch">
                            <input type="radio" v-model="screen.alignment" value="right" id="screen_alignment_radio_right" @change="saveChanges()">
                            <label for="screen_alignment_radio_right">{{ $t('Right') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <form-field name="background">
                    <label for="screen_background">
                        <span class="txt">{{ $t('Background') }}</span>
                        <screen-bulk-setting-handle :screen="screen" setting="background"></screen-bulk-setting-handle>
                    </label>
                    <div class="input-group">
                        <input type="color" v-model="screen.background" id="screen_background" @change="saveChanges()">
                        <label for="screen_background" class="input-addon txt-monospace p-l-0">{{ screen.background }}</label>
                    </div>
                </form-field>
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col-6">
                <div class="form-group">
                    <input type="checkbox" id="screen_fixed_header_check" v-model="hasFixedHeader">
                    <label for="screen_fixed_header_check">
                        <span class="txt">{{ $t('Has fixed header') }}</span>
                        <screen-bulk-setting-handle :screen="screen" setting="fixedHeader"></screen-bulk-setting-handle>
                    </label>
                </div>
            </div>
            <div class="col-6">
                <form-field class="form-group-sm" name="fixedHeader" v-show="hasFixedHeader">
                    <div class="input-group">
                        <input type="number" v-model.number="screen.fixedHeader" min="0" @change="saveChanges()">
                        <div class="input-addon">px</div>
                    </div>
                </form-field>
            </div>
        </div>
        <div class="row align-items-center">
            <div class="col-6">
                <div class="form-group m-b-0">
                    <input type="checkbox" id="screen_fixed_footer_check" v-model="hasFixedFooter">
                    <label for="screen_fixed_footer_check">
                        <span class="txt">{{ $t('Has fixed footer') }}</span>
                        <screen-bulk-setting-handle :screen="screen" setting="fixedFooter"></screen-bulk-setting-handle>
                    </label>
                </div>
            </div>
            <div class="col-6">
                <form-field class="form-group-sm m-b-0" name="fixedFooter" v-show="hasFixedFooter">
                    <div class="input-group">
                        <input type="number" v-model.number="screen.fixedFooter" min="0" @change="saveChanges()">
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
        </div>
    </toggler>
</template>

<script>
import { mapActions } from 'vuex';
import ApiClient               from '@/utils/ApiClient';
import CommonHelper            from '@/utils/CommonHelper';
import Screen                  from '@/models/Screen';
import ScreenReplaceMixin      from '@/views/screens/ScreenReplaceMixin';
import ScreenBulkSettingHandle from '@/views/screens/ScreenBulkSettingHandle';

export default {
    name: 'screen-edit-popover',
    mixins: [ScreenReplaceMixin],
    components: {
        'screen-bulk-setting-handle': ScreenBulkSettingHandle,
    },
    props: {
        screen: {
            type:     Screen,
            required: true,
        },
    },
    data() {
        return {
            isProcessing:   false,
            hasFixedHeader: false,
            hasFixedFooter: false,
        }
    },
    watch: {
        screen(newVal, oldVal) {
            this.initForm();

            this.initReplace(this.screen);
        },
        hasFixedHeader(newVal, oldVal) {
            if (!newVal) {
                this.screen.fixedHeader = 0;
                this.saveChanges();
            }
        },
        hasFixedFooter(newVal, oldVal) {
            if (!newVal) {
                this.screen.fixedFooter = 0;
                this.saveChanges();
            }
        }
    },
    mounted() {
        this.initForm();

        this.initReplace(this.screen);
    },
    methods: {
        ...mapActions({
            'setErrors': 'form-field/setErrors',
        }),

        replaceSucceeded() {
            if (this.$refs.popover) {
                this.$refs.popover.hide();
            }

            this.$emit('updated', this.screen.id);
        },
        initForm() {
            this.setErrors({});

            this.hasFixedHeader = this.screen.fixedHeader > 0 ? true : false;
            this.hasFixedFooter = this.screen.fixedFooter > 0 ? true : false;
        },
        saveChanges() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Screens.update(this.screen.id, {
                'title':       this.screen.title,
                'background':  this.screen.background,
                'alignment':   this.screen.alignment,
                'fixedHeader': this.hasFixedHeader ? this.screen.fixedHeader : 0,
                'fixedFooter': this.hasFixedFooter ? this.screen.fixedFooter : 0,
            }).then((response) => {
                this.screen.load(response.data);

                this.setErrors({});

                this.$emit('updated', this.screen.id);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
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
