<template>
    <form @submit.prevent="submitForm()">
        <popup class="popup-sm" ref="popup"
            :closeOnEsc="!isProcessing"
            :closeOnOverlay="!isProcessing"
            :closeBtn="!isProcessing"
        >
            <template v-slot:header>
                <h4 class="title">{{ $t('New project') }}</h4>
            </template>
            <template v-slot:content>
                <form-field class="required" name="title">
                    <label for="project_title">{{ $t('Project title') }}</label>
                    <input type="text" v-model.trim="title" id="project_title" class="required">
                </form-field>

                <div class="outside-popup-content">
                    <div class="block txt-center txt-hint m-b-small">
                        <span class="txt">{{ $t('Project prototype') }}</span>
                    </div>

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
                </div>
            </template>
            <template v-slot:footer>
                <button type="button" class="btn btn-light-border" @click.prevent="close()" v-show="!isProcessing">
                    <span class="txt">{{ $t('Cancel') }}</span>
                </button>
                <div class="flex-fill-block"></div>
                <button type="submit" class="btn btn-primary btn-cons btn-loader" :class="{'btn-loader-active': isProcessing}">
                    <span class="txt">{{ $t('Create project') }}</span>
                </button>
            </template>
        </popup>
    </form>
</template>

<script>
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import Popup        from '@/components/Popup';
import Prototype    from '@/models/Prototype';

const sizesList = Prototype.getSizesList();

const defaultFormData = {
    title:          '',
    type:           'desktop',
    width:          100,
    height:         100,
    scaleFactor:    1,
    activeSize:     Object.keys(sizesList)[0],
};

export default {
    name: 'project-create-popup',
    components: {
        'popup': Popup,
    },
    data() {
        return {
            isProcessing:   false,
            sizesList:      sizesList,
            title:          defaultFormData.title,
            type:           defaultFormData.type,
            width:          defaultFormData.width,
            height:         defaultFormData.height,
            scaleFactor:    defaultFormData.scaleFactor,
            activeSize:     defaultFormData.activeSize,
        }
    },
    watch: {
        activeSize(newVal, oldVal) {
            if (newVal !== 'other') {
                this.width  = sizesList[this.activeSize]['width'];
                this.height = sizesList[this.activeSize]['height'];
            }
        },
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
        onTypeChange() {
            // reset scaleFactor
            this.scaleFactor = 1;

            if (this.type == 'mobile' && (!this.width || !this.height)) {
                this.activeSize = defaultFormData.activeSize;
            }
        },
        resetForm() {
            for (let key in defaultFormData) {
                this[key] = defaultFormData[key];
            }
        },
        submitForm() {
            if (this.isProcessing) {
                return;
            }

            this.createProject().then((projectResponse) => {
                var projectId = CommonHelper.getNestedVal(projectResponse, 'data.id');

                this.createPrototype(projectId).then((prototypeResponse) => {
                    var prototypeId = CommonHelper.getNestedVal(prototypeResponse, 'data.id');

                    this.$router.push({
                        name: 'prototype',
                        params: {
                            projectId:   projectId,
                            prototypeId: prototypeId,
                        },
                    });
                }).finally(() => {
                    this.isProcessing = false;
                });
            }).catch((err) => {
                this.isProcessing = false;
            });
        },

        createProject() {
            return ApiClient.Projects.create({
                title:    this.title,
                archived: 0,
            }).then((response) => {
                return Promise.resolve(response);
            }).catch((err) => {
                this.$errResponseHandler(err);

                return Promise.reject(err);
            });
        },
        createPrototype(projectId) {
            this.isProcessing = true;

            return ApiClient.Prototypes.create({
                projectId:   projectId,
                title:       this.$t('Prototype') + ' 1', // default title
                type:        this.type,
                width:       this.width,
                height:      this.height,
                scaleFactor: this.scaleFactor,
            }).then((response) => {
                return Promise.resolve(response);
            }).catch((err) => {
                this.$errResponseHandler(err);

                return Promise.reject(err);
            });
        },
    }
}
</script>
