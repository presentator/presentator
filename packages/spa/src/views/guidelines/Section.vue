<template>
    <div class="guideline-section">
        <header class="section-header">
            <div class="ctrl-item settings-ctrl">
                <i class="fe fe-more-horizontal"></i>
                <toggler ref="sectionDropdown" class="dropdown dropdown-sm">
                    <div class="dropdown-item"
                        :class="{'disabled': !withMoveUpCtrl}"
                        @click.prevent="reorderSection('up')"
                    >
                        <i class="fe fe-arrow-up"></i>
                        <span class="txt">{{ $t('Move up') }}</span>
                    </div>
                    <div class="dropdown-item"
                        :class="{'disabled': !withMoveDownCtrl}"
                        @click.prevent="reorderSection('down')"
                    >
                        <i class="fe fe-arrow-down"></i>
                        <span class="txt">{{ $t('Move down') }}</span>
                    </div>
                    <hr>
                    <div class="dropdown-item link-danger" @click.prevent="deleteSection()">
                        <i class="fe fe-trash"></i>
                        <span class="txt">{{ $t('Delete') }}</span>
                    </div>
                </toggler>
            </div>

            <h5 ref="titleElem"
                class="title m-l-10"
                contenteditable="true"
                spellcheck="false"
                autocomplete="off"
                :title="$t('Click to edit')"
                :data-placeholder="section.title"
                @blur="updateSection()"
                @keydown.enter.prevent="updateSection()"
            >{{ section.title }}</h5>

            (<div ref="descriptionElem"
                class="description"
                contenteditable="true"
                spellcheck="false"
                autocomplete="off"
                :title="$t('Click to edit')"
                :data-placeholder="$t('Add description here...')"
                @blur="updateSection()"
                @keydown.enter.prevent="updateSection()"
            >{{ section.description }}</div>)

            <div class="flex-fill-block"></div>

            <div class="ctrl-item toggle-ctrl" @click.prevent="toggleSection()">
                <template v-if="collapsed">
                    <span class="txt m-r-5 txt-default">{{ $t('Expand') }}</span>
                    <i class="fe fe-chevron-down"></i>
                </template>
                <template v-else>
                    <span class="txt m-r-5">{{ $t('Collapse') }}</span>
                    <i class="fe fe-chevron-up"></i>
                </template>
            </div>
        </header>

        <div class="section-content" v-show="!collapsed">
            <draggable class="boxes-list assets-list"
                group="asset-section"
                draggable=".box-card"
                filter=".ignore-sort"
                :fallbackTolerance="2"
                :forceFallback="true"
                :animation="200"
                :touchStartThreshold="0"
                :list="orderedAssets"
                @change="onSortChange"
                @start="onSortStart"
                @end="onSortEnd"
            >
                <asset-box v-for="asset in orderedAssets"
                    :key="'asset_' + asset.id"
                    :asset="asset"
                    @assetDelete="onAssetDelete"
                ></asset-box>

                <div slot="header" class="box box-btns box-compact txt-hint ignore-sort">
                    <div ref="addFileContainer" class="box-btn dz-clickable">
                        <div class="content">
                            <template v-if="isAddingFile">
                                <span class="loader loader-blend"></span>
                            </template>
                            <template v-else>
                                <div class="icon"><i class="fe fe-file"></i></div>
                                <h5 class="title">{{ $t('Add file') }}</h5>
                            </template>
                        </div>
                    </div>
                    <div class="box-btn" @click.prevent="createColor()">
                        <div class="content">
                            <template v-if="isAddingColor">
                                <span class="loader loader-blend"></span>
                            </template>
                            <template v-else>
                                <div class="icon"><i class="fe fe-droplet"></i></div>
                                <h5 class="title">{{ $t('Add color') }}</h5>
                            </template>
                        </div>
                    </div>
                </div>
            </draggable>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.box-btns {
    min-height: 250px;
    order: 199; // simulate slot="footer" with slot="header" since vue.draggable doesn't support ignore elements yet
}
</style>

<script>
import Dropzone         from 'dropzone';
import ApiClient        from '@/utils/ApiClient';
import CommonHelper     from '@/utils/CommonHelper';
import GuidelineSection from '@/models/GuidelineSection';
import GuidelineAsset   from '@/models/GuidelineAsset';
import AssetBox         from '@/views/guidelines/AssetBox';
import draggable        from 'vuedraggable';

export default {
    name: 'guideline-section',
    components: {
        'asset-box': AssetBox,
        'draggable': draggable,
    },
    props: {
        section: {
            type:     GuidelineSection,
            required: true
        },
        withMoveUpCtrl: {
            default: false,
        },
        withMoveDownCtrl: {
            default: false,
        },
    },
    data() {
        return {
            collapsed:      false,
            dropdownActive: false,
            isAddingFile:   false,
            isAddingColor:  false,
            dropzone:       null,
        }
    },
    computed: {
        orderedAssets() {
            return this.section.assets.slice().sort((a, b) => (a['order'] - b['order']));
        }
    },
    mounted() {
        this.initFileUpload();
    },
    destroyed() {
        if (this.dropzone) {
            this.dropzone.destroy();
        }
    },
    methods: {
        onSortStart(e) {
            if (this.sortAnimationTimeoutId) {
                clearTimeout(this.sortAnimationTimeoutId);
            }

            if (e.target) {
                e.target.classList.add('sort-started');
            }
        },
        onSortEnd(e) {
            if (this.sortAnimationTimeoutId) {
                clearTimeout(this.sortAnimationTimeoutId);
            }

            this.sortAnimationTimeoutId = setTimeout(() => {
                if (e.target) {
                    e.target.classList.remove('sort-started');
                }
            }, 400);
        },
        onSortChange(e) {
            if (e.removed) {
                this.onAssetDelete(e.removed.element.id);
            } else if (e.added) {
                this.addAsset(e.added.element, e.added.newIndex + 1);
            } else if (e.moved) {
                this.updateAssetOrder(e.moved.element, e.moved.newIndex + 1);
            }
        },
        updateAssetOrder(asset, newOrder) {
            if (
                !asset ||
                (asset.guidelineSectionId == this.section.id && asset.order == newOrder)
            ) {
                return;
            }

            // update remaining assets order
            if (newOrder > asset.order) { // move forwards
                for (let i in this.section.assets) {
                    if (this.section.assets[i].id != asset.id && this.section.assets[i].order > asset.order && this.section.assets[i].order <= newOrder) {
                        this.$set(this.section.assets[i], 'order', this.section.assets[i].order - 1);
                    }
                }
            } else { // move backwards
                for (let i in this.section.assets) {
                    if (this.section.assets[i].id != asset.id && this.section.assets[i].order < asset.order && this.section.assets[i].order >= newOrder) {
                        this.$set(this.section.assets[i], 'order', this.section.assets[i].order + 1);
                    }
                }
            }

            // optimistic update
            asset.order = newOrder;

            // actual update
            ApiClient.GuidelineAssets.update(asset.id, {
                order: newOrder,
            });
        },
        addAsset(asset, insertOrder) {
            if (!asset || asset.guidelineSectionId === this.section.id) {
                return;
            }

            // normalize because vue.draggable counts the slot when draggin between sections
            insertOrder = Math.min(insertOrder, this.section.assets.length + 1);

            // update remaining assets order
            for (let i = this.section.assets.length - 1; i >= 0; i--) {
                if (this.section.assets[i].order >= insertOrder) {
                    this.$set(this.section.assets[i], 'order', this.section.assets[i].order + 1);
                }
            }

            // optimistic update
            asset = asset.clone({
                order: insertOrder,
                guidelineSectionId: this.section.id,
            });
            this.section.assets.push(asset);

            // actual update
            ApiClient.GuidelineAssets.update(asset.id, {
                order: insertOrder,
                guidelineSectionId: this.section.id,
            });
        },
        onAssetDelete(id) {
            var assetOrder = 0;
            for (let i in this.section.assets) {
                if (this.section.assets[i].id == id) {
                    assetOrder = this.section.assets[i].order;
                    this.$delete(this.section.assets, i);
                    break;
                }
            }

            // update remaining assets order
            for (let i in this.section.assets) {
                if (this.section.assets[i].order > assetOrder) {
                    this.$set(this.section.assets[i], 'order', this.section.assets[i].order - 1);
                }
            }
        },

        // section
        toggleSection() {
            if (this.collapsed) {
                this.collapsed = false;
            } else {
                this.collapsed = true;
            }
        },
        updateSection(event) {
            if (
                // the section to update is not loaded yet
                !this.section.id ||
                // no title and description change
                (
                    this.$refs.titleElem.innerText == this.section.title &&
                    this.$refs.descriptionElem.innerText == this.section.description
                )
            ) {
                return;
            }

            // reset title if none is provided
            if (!this.$refs.titleElem.innerText.trim()) {
                this.$refs.titleElem.innerText = this.section.title;
                return;
            }

            // optimistic update
            this.$refs.titleElem.blur();
            this.$refs.descriptionElem.blur();

            // actual update
            ApiClient.GuidelineSections.update(this.section.id, {
                title:       this.$refs.titleElem.innerText,
                description: this.$refs.descriptionElem.innerText,
            }).then((response) => {
                if (!response) {
                    return;
                }

                this.section.load(response.data);

                this.$refs.titleElem.innerText       = this.section.title;
                this.$refs.descriptionElem.innerText = this.section.description;
            }).catch((err) => {
                this.$errResponseHandler(err);
            });
        },
        reorderSection(direction='up') {
            // the section to update is not loaded yet
            if (!this.section.id) {
                return;
            }

            var oldOrder = this.section.order;
            var newOrder = direction === 'up' ? (this.section.order - 1) : (this.section.order + 1);

            // optimistic update
            this.$emit('beforeSectionOrderUpdate', this.section.id, newOrder, oldOrder);
            this.section.order = newOrder;

            if (this.$refs.sectionDropdown) {
                this.$refs.sectionDropdown.hide();
            }


            // actual update
            ApiClient.GuidelineSections.update(this.section.id, {
                order: newOrder
            }).then((response) => {
                this.section.load(response.data);

                this.$emit('afterSectionOrderUpdate', this.section.id, this.section.order, oldOrder);
            }).catch((err) => {
                this.$errResponseHandler(err);
            });
        },
        deleteSection() {
            if (
                !this.section.id ||
                !window.confirm(this.$t('Do you really want to delete section "{title}"?', {title: this.section.title}))
            ) {
                return;
            }

            // optimistic deletion
            this.$toast(this.$t('Successfully deleted section "{title}".', {title: this.section.title}));
            ApiClient.GuidelineSections.delete(this.section.id);
            this.$emit('sectionDelete', this.section.id);
        },

        // hex
        createColor(hex, title) {
            if (this.isAddingColor) {
                return;
            }

            this.isAddingColor = true;

            hex = hex || '#000000';

            // set default title
            if (!title) {
                let totalSectionColors = 1;
                for (var i = this.section.assets.length - 1; i >= 0; i--) {
                    if (this.section.assets[i].isColor) {
                        totalSectionColors++;
                    }
                }

                title = (this.$t('Color') + ' ' + totalSectionColors);
            }

            ApiClient.GuidelineAssets.create({
                guidelineSectionId: this.section.id,
                type:               'color',
                order:              0,
                hex:                hex,
                title:              title,
            }).then((response) => {
                this.section.assets.push(new GuidelineAsset(response.data));

                this.$toast(this.$t('Successfully added new asset color.'));
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isAddingColor = false;
            });
        },

        // file
        initFileUpload() {
            Dropzone.autoDiscover = false;

            this.dropzone = new Dropzone(this.$refs.addFileContainer, {
                url: ApiClient.$baseUrl + '/guideline-assets',
                method: 'post',
                paramName: 'file',
                timeout: 0,
                parallelUploads: 1, // limit parallel uploads to keep seletection files order
                uploadMultiple: false,
                thumbnailWidth: null,
                thumbnailHeight: null,
                addRemoveLinks: false,
                createImageThumbnails: false,
                previewTemplate: '<div style="display: none"></div>',
            });

            this.dropzone.on('addedfile', (file) => {
                // update the authorization header each time when a new file is selected
                this.dropzone.options.headers = Object.assign(this.dropzone.options.headers || {}, {
                    'Authorization': ('Bearer ' + ApiClient.$token),
                });
            });

            this.dropzone.on('sending', (file, xhr, formData) => {
                this.isAddingFile = true;
                formData.append('guidelineSectionId', this.section.id)
                formData.append('type', 'file')
            });

            this.dropzone.on('error', (file, response, xhr) => {
                var message = CommonHelper.getNestedVal(response, 'errors.file') || this.$t('An error occurred while uploading the asset file.');

                this.$toast(message, 'danger');
            });

            this.dropzone.on('success', (file, response) => {
                this.section.assets.push(new GuidelineAsset(response));

                this.$toast(this.$t('Successfully added new asset file.'));
            });

            this.dropzone.on('complete', (file) => {
                this.isAddingFile = false;
            });
        },
    },
}
</script>
