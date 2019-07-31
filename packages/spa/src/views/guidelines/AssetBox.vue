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
                            <span class="txt">{{ $t('Copy HEX') }}</span>
                        </div>

                        <div v-if="asset.isColor"
                            class="dropdown-item"
                            @click.prevent="openColorPicker()"
                        >
                            <i class="fe fe-droplet"></i>
                            <span class="txt">{{ $t('Change color') }}</span>
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
            <template v-if="asset.isFile">
                <div ref="titleLabel"
                    key="title"
                    class="title"
                    contenteditable="true"
                    spellcheck="false"
                    autocomplete="off"
                    :title="$t('Click to edit')"
                    :data-placeholder="asset.title"
                    @blur="saveTitle()"
                    @keydown.enter.prevent="saveTitle()"
                >{{ asset.title }}</div>

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
                <input type="color"
                    class="asset-color-input"
                    :id="'asset_color_' + asset.id"
                    v-model="asset.hex"
                    @change="saveHex()"
                >

                <label ref="hexLabel"
                    key="hex"
                    class="title txt-uppercase"
                    :title="$t('Click to edit')"
                    :for="'asset_color_' + asset.id"
                >{{ asset.hex }}</label>

                <div class="meta">
                    <div class="meta-item">rgb({{ asset.rgb.r }}, {{ asset.rgb.g }}, {{ asset.rgb.b }})</div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import ApiClient      from '@/utils/ApiClient';
import CommonHelper   from '@/utils/CommonHelper';
import GuidelineAsset from '@/models/GuidelineAsset';

export default {
    name: 'guideline-asset-box',
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
            var elem = this.$refs.titleLabel;

            if (
                !elem ||                           // event input element doesn't exist
                elem.innerText == this.asset.title // no title change
            ) {
                if (elem) {
                    elem.blur();
                }

                return;
            }

            // reset if no title is provided
            if (!elem.innerText) {
                elem.innerText = this.asset.title;
                elem.blur();

                return;
            }

            var title = elem.innerText;

            // optimistic update
            this.$set(this.asset, 'title', title);

            // reset caret position of the editable content due to text ellipsis overflow
            elem.innerText = '';
            setTimeout(() => {
                elem.innerText = title;
                elem.blur();
            }, 100); // reorder execution queue

            // actual update
            ApiClient.GuidelineAssets.update(this.asset.id, {
                title: title,
            }).then((response) => {
                this.asset.load(response.data);
                elem.innerText = this.asset.title;
            }).catch((err) => {
                this.$errResponseHandler(err);
            });
        },
    },
}
</script>

<style lang="scss" scoped>
.asset-color-input {
    position: absolute;
    opacity: 0;
    visibility: hidden;
}
</style>
