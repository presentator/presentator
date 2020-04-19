<template>
    <div class="preview-container-wrapper">
        <div class="preview-container">
            <template v-if="isLoadingAssets">
                <div class="flex-fill-block"></div>

                <div class="block txt-center">
                    <span class="loader loader-lg loader-blend"></span>
                </div>
            </template>

            <template v-if="!isLoadingAssets && !hasAssets">
                <div class="flex-fill-block"></div>

                <div class="block scroll-block txt-center p-base">
                    <figure class="mockup m-b-small">
                        <div class="mockup-bg"></div>
                        <div class="browser secondary"></div>
                        <div class="browser primary"><i class="fe fe-book-open"></i></div>
                    </figure>

                    <h4>{{ $t('No guideline assets to show.') }}</h4>
                </div>
            </template>

            <div v-if="!isLoadingAssets && hasAssets"
                class="guideline-sections-list scroll-block p-base"
            >
                <h3 class="m-t-0 m-b-base">{{ $t('{projectTitle} guideline', {projectTitle: project.title}) }}</h3>

                <guideline-section-preview
                    v-for="section in orderedGuidelineSections"
                    v-if="section.assets.length > 0"
                    :key="section.id"
                    :section="section"
                ></guideline-section-preview>
            </div>

            <div class="flex-fill-block"></div>

            <preview-bar
                :project="project"
                :projectLink="projectLink"
            ></preview-bar>
        </div>
    </div>
</template>

<script>
import { mapState }     from 'vuex';
import ApiClient        from '@/utils/ApiClient';
import Project          from '@/models/Project';
import ProjectLink      from '@/models/ProjectLink';
import GuidelineSection from '@/models/GuidelineSection';
import SectionPreview   from '@/views/guidelines/SectionPreview';
import PreviewBar       from '@/views/preview/PreviewBar';

export default {
    name: 'preview-guideline',
    props: {
        project: {
            type:     Project,
            required: true,
        },
        projectLink: {
            type:     ProjectLink,
            required: true,
        },
    },
    components: {
        'guideline-section-preview': SectionPreview,
        'preview-bar': PreviewBar,
    },
    data() {
        return {
            isLoadingAssets:   false,
            guidelineSections: [],

            isPreviewBarActive: true,
        }
    },
    computed: {
        ...mapState({
            previewToken: state => state.preview.previewToken,
        }),

        orderedGuidelineSections() {
            return this.guidelineSections.slice().sort((a, b) => (a['order'] - b['order']));
        },
        hasAssets() {
            for (let i in this.guidelineSections) {
                if (this.guidelineSections[i].assets && this.guidelineSections[i].assets.length > 0) {
                    return true;
                }
            }

            return false;
        },
    },
    activated() {
        this.$setDocumentTitle(() => this.$t('{projectTitle} guideline', {projectTitle: this.project.title}));
    },
    beforeMount() {
        this.loadAssets();
    },
    methods: {
        loadAssets() {
            if (this.isLoadingAssets) {
                return;
            }

            this.isLoadingAssets = true;

            ApiClient.Previews.getAssets(this.previewToken).then((response) => {
                this.guidelineSections = GuidelineSection.createInstances(response.data);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingAssets = false;
            });
        },
    },
}
</script>
