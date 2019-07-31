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

                        <div class="breadcrumb-item">{{ $t('Prototypes') }}</div>
                    </nav>

                    <div class="flex-fill-block"></div>

                    <prototypes-switch ref="prototypesSwitch" :projectId="project.id" class="m-l-10"></prototypes-switch>
                </header>

                <div v-if="!isLoadingPrototypes && !prototypes.length" class="block txt-center">
                    <figure class="mockup m-t-base m-b-base">
                        <div class="mockup-bg"></div>
                        <div class="browser secondary"></div>
                        <div class="browser primary"><i class="fe fe-layers"></i></div>
                    </figure>

                    <div class="content m-b-base">
                        <h4>{{ $t('The project does not have any prototypes yet.') }}</h4>
                    </div>

                    <button type="button" class="btn btn-lg btn-success btn-cons-xl btn-loader"
                        @click.prevent="$refs.prototypesSwitch ? $refs.prototypesSwitch.openUpsertPopup() : true"
                    >
                        <i class="fe fe-plus"></i>
                        <span class="txt">{{ $t('New prototype') }}</span>
                    </button>
                </div>

                <screens-listing></screens-listing>
            </template>

            <div class="flex-fill-block"></div>

            <app-footer class="m-t-base"></app-footer>
        </main>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient        from '@/utils/ApiClient';
import CommonHelper     from '@/utils/CommonHelper';
import AppFooter        from '@/views/base/AppFooter';
import ProjectSidebar   from '@/views/projects/ProjectSidebar';
import ScreensListing   from '@/views/screens/ScreensListing';
import PrototypesSwitch from '@/views/prototypes/PrototypesSwitch';
import ProjectMixin     from '@/views/projects/ProjectMixin';

export default {
    name: 'prototype-view',
    mixins: [ProjectMixin],
    components: {
        'app-footer':        AppFooter,
        'project-sidebar':   ProjectSidebar,
        'screens-listing':   ScreensListing,
        'prototypes-switch': PrototypesSwitch,
    },
    data() {
        return {
            pageTitle: (() => this.$t('Prototypes')),
            isLoadingPrototypes: false,
        }
    },
    computed: {
        ...mapState({
            prototypes: state => state.prototypes.prototypes,
        }),
        ...mapGetters({
            getPrototype:    'prototypes/getPrototype',
            activePrototype: 'prototypes/activePrototype',
        }),

        isPageLoaded() {
            return !this.isLoadingProject && !this.isLoadingPrototypes;
        },
    },
    watch: {
        '$route.params.projectId': function (newVal, oldVal) {
            if (newVal != oldVal) {
                this.init();
            }
        },
        '$route.params.prototypeId': function (newVal, oldVal) {
            if (!this.activePrototype || newVal != this.activePrototype.id) {
                this.setActivePrototypeId(newVal);
            }
        },
        activePrototype(newVal, oldVal) {
            this.$router.replace({ // use replace to prevent duplicated history records on prototype delete
                name: 'prototype',
                params: Object.assign({}, this.$route.params, {
                    prototypeId: (newVal ? newVal.id : null),
                }),
            });
        },
    },
    beforeMount() {
        this.init()
    },
    methods: {
        ...mapActions({
            setPrototypes:        'prototypes/setPrototypes',
            setActivePrototypeId: 'prototypes/setActivePrototypeId',
        }),

        init() {
            this.loadProject(this.$route.params.projectId);

            this.loadPrototypes(
                this.$route.params.projectId,
                this.$route.params.prototypeId
            );
        },
        loadPrototypes(projectId, activePrototypeId) {
            if (this.isLoadingPrototypes) {
                return;
            }

            this.isLoadingPrototypes = true;

            ApiClient.Prototypes.getList(1, 100, {
                'search[projectId]': projectId,
            }).then((response) => {
                this.setPrototypes(response.data);

                // implicit set active prototype
                let activePrototype = this.getPrototype(activePrototypeId);
                if (activePrototype) {
                    this.setActivePrototypeId(activePrototype.id);
                } else if (this.prototypes.length) {
                    this.setActivePrototypeId(this.prototypes[this.prototypes.length - 1].id);
                }

                // normalize optional prototypeId param
                this.$router.replace({
                    to: 'prototype',
                    params: Object.assign({}, this.$route.params, {
                        prototypeId: (this.activePrototype ? this.activePrototype.id : null),
                    }),
                });
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingPrototypes = false;
            });
        }
    },
}
</script>
