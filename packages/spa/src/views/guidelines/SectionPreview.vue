<template>
    <div class="guideline-section">
        <header class="section-header">
            <h5 class="title">{{ section.title }}</h5>

            <template v-if="section.description">
                (<div class="description">{{ section.description }}</div>)
            </template>

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
            <div class="boxes-list assets-list">
                <asset-box-preview
                    v-for="asset in orderedAssets"
                    :key="asset.id"
                    :asset="asset"
                ></asset-box-preview>
            </div>
        </div>
    </div>
</template>

<script>
import GuidelineSection from '@/models/GuidelineSection';
import AssetBoxPreview  from '@/views/guidelines/AssetBoxPreview';

export default {
    name: 'guideline-section-preview',
    props: {
        section: {
            type:     GuidelineSection,
            required: true,
        },
    },
    components: {
        'asset-box-preview': AssetBoxPreview,
    },
    data() {
        return {
            collapsed: false,
        }
    },
    computed: {
        orderedAssets() {
            return this.section.assets.slice().sort((a, b) => (a['order'] - b['order']));
        }
    },
    methods: {
        toggleSection() {
            this.collapsed = !this.collapsed;
        },
    },
}
</script>
