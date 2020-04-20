<template>
    <div class="box box-card box-compact"
        :class="{'box-color': asset.isColor}"
        @mouseleave="$refs.assetDropdown ? $refs.assetDropdown.hide() : true"
    >
        <figure class="box-thumb" :style="{'background': asset.hex}">
            <template v-if="asset.isFile">
                <div class="crop-wrapper">
                    <img v-if="asset.isImage" :src="asset.getFileUrl('medium')" :alt="asset.title" class="img">
                    <span v-else class="img img-alt">{{ asset.fileExtension }}</span>
                </div>
            </template>

            <div class="thumb-overlay">
                <template v-if="asset.isImage">
                    <div class="overlay-ctrl" @click.prevent="openPreviewPopup()"></div>

                    <div class="box-ctrl handle center" @click.prevent="openPreviewPopup()">
                        <i class="fe fe-eye"></i>
                    </div>
                </template>

                <template v-if="asset.isColor">
                    <div class="overlay-ctrl" @click.prevent="openColorPicker()"></div>

                    <div class="box-ctrl handle center"
                        :title="$t('Change color')"
                        @click.prevent="openColorPicker()"
                    >
                        <i class="fe fe-droplet" :style="{'color': asset.contrastHex}"></i>
                    </div>
                </template>

                <div class="box-ctrl handle top-right">
                    <i class="fe fe-more-horizontal" :style="{'color': asset.contrastHex}"></i>

                    <toggler class="dropdown dropdown-sm" ref="assetDropdown">
                        <a v-if="asset.isFile"
                            :href="asset.getFileUrl()"
                            class="dropdown-item"
                            tabindex="-1"
                            download
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="fe fe-download"></i>
                            <span class="txt">{{ $t('Download') }}</span>
                        </a>
                        <div v-if="asset.isColor"
                            class="dropdown-item"
                            @click.prevent="copyToClipboard(asset.hex.toUpperCase())"
                        >
                            <i class="fe fe-copy"></i>
                            <span class="txt">{{ $t('Copy') }} HEX</span>
                        </div>
                        <div v-if="asset.isColor"
                            class="dropdown-item"
                            @click.prevent="copyToClipboard(asset.rgb.toUpperCase())"
                        >
                            <i class="fe fe-copy"></i>
                            <span class="txt">{{ $t('Copy') }} RGB</span>
                        </div>
                        <hr>
                        <div class="dropdown-item link-danger" @click.prevent="deleteAsset()">
                            <i class="fe fe-trash"></i>
                            <span class="txt">{{ $t('Delete') }}</span>
                        </div>
                    </toggler>
                </div>
            </div>
        </figure>

        <div class="box-content">
            <div ref="titleLabel"
                key="title"
                class="title"
                contenteditable="true"
                spellcheck="false"
                autocomplete="off"
                :title="$t('Click to edit')"
                :data-placeholder="asset.title || $t('Title')"
                @blur="saveTitle()"
                @keydown.enter.prevent="saveTitle()"
            >{{ asset.title }}</div>

            <template v-if="asset.isFile">
                <div class="meta">
                    <div class="meta-item txt-uppercase">{{ asset.fileExtension }}</div>

                    <div v-if="asset.isImage && assetWidth > 0 && assetHeight > 0"
                        class="meta-item"
                    >
                        {{ assetWidth }}x{{ assetHeight }}
                    </div>
                </div>
            </template>

            <template v-else>
                <label ref="hexLabel">
                    <input type="color"
                        class="asset-color-input"
                        :id="'asset_color_' + asset.id"
                        v-model="asset.hex"
                        @change="saveHex()"
                    >
                </label>

                <div class="meta">
                    <div class="meta-item"
                        v-tooltip.bottom="$t('Copy')"
                        @click.prevent="copyToClipboard(asset.hex.toUpperCase())"
                    >
                        {{ asset.hex.toUpperCase() }}
                    </div>
                    <div class="meta-item"
                        v-tooltip.bottom="$t('Copy')"
                        @click.prevent="copyToClipboard(asset.rgb.toUpperCase())"
                    >
                        {{ asset.rgb.toUpperCase() }}
                    </div>
                </div>
            </template>
        </div>

        <relocator v-if="asset.isImage">
            <popup class="popup-image" ref="previewPopup" :key="'asset_popup_' + asset.id">
                <template v-slot:content>
                    <img v-if="$refs.previewPopup && $refs.previewPopup.isActive"
                        :src="asset.getFileUrl('original')"
                        :alt="asset.title"
                    >
                </template>
            </popup>
        </relocator>
    </div>
</template>

<style lang="scss" scoped>
.asset-color-input {
    position: absolute;
    opacity: 0;
    visibility: hidden;
}
</style>

<script>
import ApiClient      from '@/utils/ApiClient';
import CommonHelper   from '@/utils/CommonHelper';
import GuidelineAsset from '@/models/GuidelineAsset';
import Relocator      from '@/components/Relocator';
import Popup          from '@/components/Popup';

export default {
    name: 'guideline-asset-box',
    components: {
        'relocator': Relocator,
        'popup':     Popup,
    },
    props: {
        asset: {
            type:     GuidelineAsset,
            required: true,
        }
    },
    data() {
        return {
            assetWidth:  0,
            assetHeight: 0,
        }
    },
    mounted() {
        if (this.asset.isImage) {
            CommonHelper.loadImage(this.asset.getFileUrl()).then((data) => {
                this.assetWidth  = data.width;
                this.assetHeight = data.height;
            });
        }
    },
    methods: {
        deleteAsset() {
            if (
                !this.asset.id ||
                !window.confirm(this.$t('Do you really want to delete the selected asset?'))
            ) {
                return;
            }

            // optimistic deletion
            this.$toast(this.$t('Successfully deleted asset.'));

            // actual deletion
            ApiClient.GuidelineAssets.delete(this.asset.id);

            this.$emit('assetDelete', this.asset.id);
        },
        copyToClipboard(text) {
            if (CommonHelper.copyToClipboard(text)) {
                this.$toast(this.$t('Successfully copied {text} to clipboard.', {text: text}));
            } else {
                this.$toast(this.$t('Failed copying {text} to clipboard.', {text: text}), 'danger');
            }
        },
        openColorPicker() {
            if (!this.asset.isColor || !this.$refs.hexLabel) {
                return;
            }

            this.$refs.hexLabel.click();
        },

        // hex
        saveHex(hex) {
            hex = hex || this.asset.hex;

            // optimistic update
            this.$set(this.asset, 'hex', hex);

            // actual update
            ApiClient.GuidelineAssets.update(this.asset.id, {
                hex: hex,
            }).then((response) => {
                this.asset.load(response.data);
            }).catch((err) => {
                this.$errResponseHandler(err);
            });
        },

        // file
        saveTitle() {
            this.$inlineTitleUpdate(
                this.$refs.titleLabel,
                this.asset,
                ApiClient.GuidelineAssets.update
            );
        },
        openPreviewPopup() {
            if (this.$refs.previewPopup) {
                this.$refs.previewPopup.open();
            }
        },
        closePreviewPopup() {
            if (this.$refs.previewPopup) {
                this.$refs.previewPopup.close();
            }
        },
    },
}
</script>
