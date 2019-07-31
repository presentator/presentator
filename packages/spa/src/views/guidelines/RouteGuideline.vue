<template>
    <div class="app-body">
        <project-sidebar :projectId="$route.params.projectId"></project-sidebar>

        <main class="app-main">
            <span v-if="!isPageLoaded" class="loader loader-blend"></span>

            <template v-if="isPageLoaded && project.id > 0">
                <header class="app-header">
                    <nav class="breadcrumbs">
                        <router-link :to="{name: 'projects'}" class="breadcrumb-item">{{ $t('Projects') }}</router-link>

                        <div class="breadcrumb-item editable active"
                            contenteditable="true"
                            spellcheck="false"
                            autocomplete="off"
                            :title="$t('Click to edit')"
                            :data-placeholder="project.title"
                            @blur="updateProjectTitle"
                            @keydown.enter.prevent="updateProjectTitle"
                        >{{ project.title }}</div>

                        <div class="breadcrumb-item active">{{ $t('Guideline') }}</div>
                    </nav>

                    <div class="flex-fill-block"></div>

                    <button class="btn btn-cons-lg btn-success btn-loader m-l-10"
                        :class="{'btn-loader-active': isCreatingSection}"
                        @click.prevent="createSection()"
                    >
                        <i class="fe fe-plus"></i>
                        <span class="txt">{{ $t('New section') }}</span>
                    </button>
                </header>

                <div v-if="isLoadingSections" class="block txt-center">
                    <span class="loader loader-blend"></span>
                </div>

                <div v-if="!isLoadingSections && !orderedSections.length" class="block txt-center">
                    <figure class="mockup m-t-base m-b-base">
                        <div class="mockup-bg"></div>
                        <div class="browser secondary"></div>
                        <div class="browser primary"><i class="fe fe-book-open"></i></div>
                    </figure>

                    <div class="content m-b-base">
                        <h4>{{ $t('Set up your project brand colors, logos and other assets.') }}</h4>
                    </div>

                    <button type="button" class="btn btn-lg btn-success btn-cons-xl btn-loader"
                        :class="{'btn-loader-active': isCreatingSection}"
                        @click.prevent="createSection()"
                    >
                        <i class="fe fe-plus"></i>
                        <span class="txt">{{ $t('New section') }}</span>
                    </button>
                </div>

                <div v-if="!isLoadingSections && orderedSections.length" class="guideline-sections-list">
                    <guideline-section
                        v-for="(section, i) in orderedSections"
                        :key="section.id"
                        :section="section"
                        :withMoveUpCtrl="i >= 1"
                        :withMoveDownCtrl="i != (orderedSections.length-1)"
                        @sectionDelete="onSectionDelete"
                        @beforeSectionOrderUpdate="onSectionOrderUpdate"
                    ></guideline-section>
                </div>
            </template>

            <div class="flex-fill-block"></div>

            <app-footer class="m-t-base"></app-footer>
        </main>
    </div>
</template>

<script>
import ApiClient        from '@/utils/ApiClient';
import CommonHelper     from '@/utils/CommonHelper';
import AppFooter        from '@/views/base/AppFooter';
import GuidelineSection from '@/models/GuidelineSection';
import ProjectSidebar   from '@/views/projects/ProjectSidebar';
import Section          from '@/views/guidelines/Section';
import ProjectMixin     from '@/views/projects/ProjectMixin';

export default {
    name: 'projects-assets',
    mixins: [ProjectMixin],
    components: {
        'app-footer':        AppFooter,
        'project-sidebar':   ProjectSidebar,
        'guideline-section': Section,
    },
    data() {
        return {
            pageTitle: (() => this.$t('Guideline')),
            sections: [],
            isLoadingSections: false,
            isCreatingSection: false,
        }
    },
    computed: {
        isPageLoaded() {
            return !this.isLoadingProject && !this.isLoadingSections;
        },
        orderedSections() {
            return this.sections.slice().sort((a, b) => (a['order'] - b['order']));
        },
    },
    beforeMount() {
        this.loadProject(this.$route.params.projectId);
        this.loadSections(this.$route.params.projectId);
    },
    methods: {
        loadSections(projectId) {
            if (this.isLoadingSections) {
                return;
            }

            projectId = projectId || this.$route.params.projectId;

            this.isLoadingSections = true;

            ApiClient.GuidelineSections.getList(1, 100, {
                'search[projectId]': projectId,
                'expand': 'assets',
            }).then((response) => {
                this.sections = GuidelineSection.createInstances(response.data);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingSections = false;
            });
        },
        createSection(projectId) {
            projectId = projectId || this.$route.params.projectId;

            if (this.isCreatingSection) {
                return;
            }

            this.isCreatingSection = true;

            ApiClient.GuidelineSections.create({
                projectId: projectId,
                title: this.$t('Section') + ' ' + (this.orderedSections.length + 1),
            }).then((response) => {
                var section = new GuidelineSection(response.data);

                this.sections.push(section);

                this.$nextTick(() => {
                    var lastSection = document.querySelector('.guideline-section:last-child');
                    if (lastSection) {
                        lastSection.scrollIntoView({
                            behavior: 'smooth',
                            block:    'nearest',
                        });
                    }
                });

                this.$toast(this.$t('Successfully created new section.'));
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isCreatingSection = false;
            });
        },
        onSectionOrderUpdate(id, newOrder, oldOrder) {
            var section = CommonHelper.findByKey(this.sections, 'id', id);

            if (!section || newOrder == oldOrder) {
                return;
            }

            // update remaining sections order
            if (newOrder > oldOrder) { // move forwards
                for (let i in this.sections) {
                    if (
                        this.sections[i].id != id &&
                        this.sections[i].order > oldOrder &&
                        this.sections[i].order <= newOrder
                    ) {
                        this.sections[i].order = this.sections[i].order - 1;
                    }
                }
            } else { // move backwards
                for (let i in this.sections) {
                    if (
                        this.sections[i].id != id &&
                        this.sections[i].order < oldOrder &&
                        this.sections[i].order >= newOrder
                    ) {
                        this.sections[i].order = this.sections[i].order + 1;
                    }
                }
            }

            section.order = newOrder;
        },
        onSectionDelete(id) {
            var section = CommonHelper.findByKey(this.sections, 'id', id);

            if (!section) {
                return;
            }

            // update remaining sections order
            for (let i in this.sections) {
                if (this.sections[i].order > section.order) {
                    this.$set(this.sections[i], 'order', this.sections[i].order - 1);
                }
            }

            CommonHelper.removeByKey(this.sections, 'id', section.id);
        },
    },
}
</script>
