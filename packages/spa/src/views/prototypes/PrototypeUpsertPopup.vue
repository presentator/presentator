<template>
    <form @submit.prevent="submitForm()">
        <popup ref="popup"
            class="popup-sm"
            :closeOnEsc="!isProcessing"
            :closeOnOverlay="!isProcessing"
            :closeBtn="!isProcessing"
        >
            <template v-slot:header>
                <h4 class="title">{{ isUpdate ? $t('Edit prototype') : $t('New prototype') }}</h4>
            </template>
            <template v-slot:content>
                <form-field class="form-group required" name="title">
                    <label for="prototype_title">{{ $t('Title') }}</label>
                    <input type="text" v-model="title" id="prototype_title" maxlenght="255" required>
                </form-field>

                <form-field class="form-group required options-toggle" name="type">
                    <label>{{ $t('Type') }}</label>
                    <div class="row">
                        <div class="col-6">
                            <div class="toggle-item">
                                <input type="radio" v-model="type" name="options" id="type_desktop" value="desktop" @change="onTypeChange">
                                <label for="type_desktop">
                                    <div class="icon"><i class="fe fe-monitor"></i></div>
                                    <div class="txt">{{ $t('Desktop') }}</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="toggle-item">
                                <input type="radio" v-model="type" name="options" id="type_mobile" value="mobile" @change="onTypeChange">
                                <label for="type_mobile">
                                    <div class="icon"><i class="fe fe-smartphone"></i></div>
                                    <div class="txt">{{ $t('Mobile device') }}</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </form-field>

                <!-- Device -->
                <div v-if="type === 'mobile'" class="row">
                    <div :class="'col-' + (activeSize === 'other' ? '6' : '12')">
                        <div class="form-group">
                            <select v-model="activeSize">
                                <option v-for="(option, key) in sizesList" :key="key" :value="key">{{ $t(option.label) }}</option>
                            </select>
                        </div>
                    </div>
                    <template v-if="activeSize === 'other'">
                        <div class="col-3">
                            <form-field name="width" :showErrorMsg="false">
                                <input type="number" v-model.number="width" :placeholder="$t('Width')" :title="$t('Width')" min="100" step="1" required>
                            </form-field>
                        </div>
                        <div class="col-3">
                            <form-field name="height" :showErrorMsg="false">
                                <input type="number" v-model.number="height" :placeholder="$t('Height')" :title="$t('Height')" min="100" step="1" required>
                            </form-field>
                        </div>
                    </template>
                </div>

                <!-- Scale factor -->
                <div v-if="type === 'desktop'" class="form-group">
                    <input type="checkbox"
                        id="retina_rescale"
                        v-model="scaleFactor"
                        true-value="0.5"
                        false-value="1"
                    >
                    <label for="retina_rescale">
                        <span class="txt">{{ $t('2x retina scale') }}</span>
                        <i class="fe fe-info link-hint m-l-5" v-tooltip.right="$t('For 2x pixel density designs')"></i>
                    </label>
                </div>
                <div v-else class="form-group">
                    <input type="checkbox"
                        id="auto_rescale"
                        v-model="scaleFactor"
                        true-value="0"
                        false-value="1"
                    >
                    <label for="auto_rescale">
                        <span class="txt">{{ $t('Auto scale') }}</span>
                        <i class="fe fe-info link-hint m-l-5" v-tooltip.right="$t('Auto scale/fit each screen to the device width')"></i>
                    </label>
                </div>
            </template>
            <template v-slot:footer>
                <span v-if="isUpdate"
                    class="link-fade link-hint m-r-small"
                    v-tooltip="$t('Delete')"
                    @click.prevent="deletePrototype()"
                >
                    <i class="fe fe-trash"></i>
                </span>

                <span v-if="isUpdate"
                    class="link-fade link-hint m-r-small"
                    v-tooltip="$t('Duplicate')"
                    @click.prevent="duplicatePrototype()"
                >
                    <i class="fe fe-copy"></i>
                </span>

                <div class="flex-fill-block"></div>

                <button type="submit" class="btn btn-primary btn-cons btn-loader" :class="{'btn-loader-active': isProcessing}">
                    <span class="txt">{{ isUpdate ? $t('Update') : $t('Create') }}</span>
                </button>
            </template>
        </popup>
    </form>
</template>

<script>
import { mapActions } from 'vuex';
import ApiClient      from '@/utils/ApiClient';
import CommonHelper   from '@/utils/CommonHelper';
import Popup          from '@/components/Popup';
import Prototype      from '@/models/Prototype';

const sizesList = Prototype.getSizesList();

const defaultFormData = {
    title:       '',
    type:        'desktop',
    width:       100,
    height:      100,
    scaleFactor: 1,
    activeSize:  Object.keys(sizesList)[0],
};

export default {
    name: 'prototype-upsert-popup',
    components: {
        'popup': Popup,
    },
    props: {
        projectId: {
            required: true,
        }
    },
    data() {
        return {
            model:        new Prototype,
            isProcessing: false,
            sizesList:    sizesList,
            title:        defaultFormData.title,
            type:         defaultFormData.type,
            width:        defaultFormData.width,
            height:       defaultFormData.height,
            scaleFactor:  defaultFormData.scaleFactor,
            activeSize:   defaultFormData.activeSize,
        }
    },
    computed: {
        isUpdate() {
            return this.model.id > 0;
        },
    },
    watch: {
        activeSize(newVal, oldVal) {
            this.onActiveSizeUpdate();
        },
    },
    beforeMount() {
        this.loadForm();
    },
    methods: {
        ...mapActions({
            updatePrototype:      'prototypes/updatePrototype',
            removePrototype:      'prototypes/removePrototype',
            addPrototype:         'prototypes/addPrototype',
            setActivePrototypeId: 'prototypes/setActivePrototypeId',
        }),
        loadForm(model) {
            model = model || this.model || {};

            this.title       = CommonHelper.getNestedVal(model, 'title', defaultFormData.title);
            this.type        = CommonHelper.getNestedVal(model, 'type', defaultFormData.type);
            this.width       = CommonHelper.getNestedVal(model, 'width', defaultFormData.width);
            this.height      = CommonHelper.getNestedVal(model, 'height', defaultFormData.height);
            this.scaleFactor = CommonHelper.getNestedVal(model, 'scaleFactor', defaultFormData.scaleFactor);
            this.activeSize  = CommonHelper.getNestedVal(model, 'sizeKey', defaultFormData.activeSize);
        },
        open(modelData) {
            this.isProcessing = false;

            if (modelData instanceof Prototype) {
                this.model = modelData;
            } else {
                this.model = new Prototype(modelData);
            }

            this.loadForm();

            this.$refs.popup.open();
        },
        close() {
            this.isProcessing = false;

            this.$refs.popup.close();
        },
        onTypeChange() {
            // reset scaleFactor
            this.scaleFactor = 1;

            if (this.type == 'mobile') {
                this.scaleFactor = 0; // set auto by default

                if (!this.width || !this.height) { // from desktop to mobile
                    this.activeSize = defaultFormData.activeSize; // switch to the default device size
                }

                this.onActiveSizeUpdate();
            }
        },
        onActiveSizeUpdate() {
            if (this.activeSize !== 'other') {
                this.width  = sizesList[this.activeSize]['width'];
                this.height = sizesList[this.activeSize]['height'];
            }
        },
        submitForm() {
            if (this.isProcessing) {
                return;
            }

            if (this.model.id) {
                this.update();
            } else {
                this.create();
            }
        },
        update() {
            this.isProcessing = true;

            ApiClient.Prototypes.update(this.model.id, {
                title:       this.title,
                type:        this.type,
                width:       this.width,
                height:      this.height,
                scaleFactor: this.scaleFactor,
            }).then((response) => {
                this.$toast(this.$t('Successfully updated prototype.'));

                this.close();

                this.model.load(response.data);

                this.updatePrototype(response.data);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
        create() {
            this.isProcessing = true;

            ApiClient.Prototypes.create({
                projectId:   this.projectId,
                title:       this.title,
                type:        this.type,
                width:       this.width,
                height:      this.height,
                scaleFactor: this.scaleFactor,
            }).then((response) => {
                this.$toast(this.$t('Successfully created prototype.'));

                this.close();

                this.model.load(response.data);

                this.addPrototype(response.data);

                this.setActivePrototypeId(response.data.id);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
        deletePrototype() {
            if (
                !this.model.id ||
                !window.confirm(this.$t('Do you really want to delete prototype "{title}"?', {title: this.model.title}))
            ) {
                return;
            }

            this.close();

            // actual deletion
            ApiClient.Prototypes.delete(this.model.id);

            // optimistic deletion
            this.$toast(this.$t('Successfully deleteted prototype "{title}".', {title: this.model.title}));
            this.removePrototype(this.model.id);
        },
        duplicatePrototype() {
            if (!this.model.id || this.isProcessing) {
                return;
            }

            var title = window.prompt(
                this.$t('Create a copy of the current prototype with its screens and hotspots.') + '\n\n' + this.$t('Title of your new prototype:'),
                this.model.title + ' (copy)'
            );

            if (title == null || title == "") {
                return; // user closed the prompt modal
            }

            this.isProcessing = true;

            ApiClient.Prototypes.duplicate(this.model.id, {
                title: title,
            }).then((response) => {
                this.$toast(this.$t('Successfully duplicated prototype.'));

                this.close();

                this.addPrototype(response.data);

                this.setActivePrototypeId(response.data.id);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    }
}
</script>
