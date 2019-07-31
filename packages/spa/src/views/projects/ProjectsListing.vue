<template>
    <div>
        <div v-if="withFilterBar" class="search-bar">
            <div class="search-filter-wrapper">
                <select class="search-filter"
                    v-model="archived"
                    @change="loadProjects()"
                >
                    <option value="">{{ $t('All projects') }} </option>
                    <option value="0">{{ $t('Active projects') }} </option>
                    <option value="1">{{ $t('Archived projects') }} </option>
                </select>
            </div>
            <div class="search-input-wrapper" :class="{'active': searchTerm.length > 0}">
                <span class="search-clear" v-tooltip.left="$t('Clear')" @click.prevent="clearSearch()"></span>
                <input type="input"
                    class="search-input"
                    v-model.trim="searchTerm"
                    :placeholder="searchPlaceholder"
                    @input="onSearchInputChange"
                >
            </div>
        </div>

        <div class="block txt-center txt-hint">
            <span v-show="!projects.length && isLoadingProjects" class="loader loader-lg loader-blend m-b-base"></span>

            <h4 v-show="hasActiveFilters && !projects.length && !isLoadingProjects" class="m-b-25">
                {{ $t('No projects were found.') }}
            </h4>

            <div v-show="!hasActiveFilters && !projects.length && !isLoadingProjects" class="m-b-base">
                <figure class="mockup m-t-base m-b-base">
                    <div class="mockup-bg"></div>
                    <div class="browser secondary"></div>
                    <div class="browser primary"><i class="fe fe-grid"></i></div>
                </figure>
                <div class="content m-b-base">
                    <h4 class="txt-default">{{ $t("You don't have any active projects.") }}</h4>
                    <p>{{ $t('Create a project to share your designs, collect feedback and more.') }}</p>
                </div>
                <button class="btn btn-success btn-lg btn-cons-xl btn-loader"
                    @click.prevent="openCreatePopup"
                >
                    <i class="fe fe-plus"></i>
                    <span class="txt">{{ $t('New project') }}</span>
                </button>
            </div>
        </div>

        <h5 v-show="!isLoadingProjects && projects.length && searchTerm.length > 0" class="m-t-0 m-b-small">
            {{ $t('Search results for "{searchTerm}" ({totalFound}):', {
                searchTerm: searchTerm,
                totalFound: totalProjects,
            }) }}
        </h5>

        <div class="boxes-list projects-list">
            <project-box v-for="project in projects"
                :key="project.id"
                :project="project"
                @projectUpdate="onProjectUpdate"
                @projectDelete="onProjectDelete"
            ></project-box>
        </div>

        <div class="block txt-center">
            <button class="btn btn-warning btn-lg btn-cons-xl m-l-small m-r-small"
                @click.prevent="resetFilters()"
                v-show="hasActiveFilters"
            >
                <span class="txt">{{ $t('Reset filters') }}</span>
            </button>

            <button class="btn btn-transp-primary btn-lg btn-cons-xl btn-loader m-l-small m-r-small"
                :class="{'btn-loader-active': isLoadingProjects}"
                @click.prevent="loadProjects(currentPage + 1, false)"
                v-show="hasMoreProjects"
            >
                <span class="txt">{{ $t('Load more projects') }}</span>
            </button>
        </div>

        <relocator>
            <project-create-popup ref="createPopup"></project-create-popup>
        </relocator>
    </div>
</template>

<script>
import ApiClient          from '@/utils/ApiClient';
import CommonHelper       from '@/utils/CommonHelper';
import ProjectBox         from '@/views/projects/ProjectBox';
import Project            from '@/models/Project';
import ProjectCreatePopup from '@/views/projects/ProjectCreatePopup';
import Relocator          from '@/components/Relocator';

const PROJECTS_PER_PAGE = 40;

const defaultData = {
    isLoadingProjects: false,
    searchTerm:        '',
    projects:          [],
    currentPage:       1,
    totalProjects:     0,
    archived:          0,
};

export default {
    name: 'projects-listing',
    components: {
        'project-box':          ProjectBox,
        'project-create-popup': ProjectCreatePopup,
        'relocator':            Relocator,
    },
    props: {
        withFilterBar: {
            type:    Boolean,
            default: true,
        },
    },
    data() {
        return Object.assign({}, defaultData);
    },
    computed: {
        hasMoreProjects() {
            return this.totalProjects > this.projects.length;
        },
        hasActiveFilters() {
            return (
                this.searchTerm.length != defaultData.searchTerm ||
                this.archived != defaultData.archived
            );
        },
        searchPlaceholder() {
            if (this.archived === '') {
                return this.$t('Search all projects');
            }

            if (this.archived == 1) {
                return this.$t('Search for archived projects');
            }

            return this.$t('Search for active projects');
        },
    },
    beforeMount() {
        this.loadProjects();
    },
    methods: {
        openCreatePopup() {
            if (this.$refs.createPopup) {
                this.$refs.createPopup.open();
            }
        },
        clearSearch() {
            this.searchTerm = '';

            this.loadProjects();
        },
        resetFilters() {
            this.archived   = defaultData.archived;
            this.searchTerm = defaultData.searchTerm;

            this.loadProjects();
        },
        resetList() {
            this.projects      = defaultData.projects.slice(0);
            this.totalProjects = defaultData.totalProjects;
            this.currentPage   = defaultData.currentPage;
        },
        onSearchInputChange(e) {
            if (this.searchTimeoutId) {
                clearTimeout(this.searchTimeoutId);
            }

            if (!this.searchTerm.length) {
                this.loadProjects();
            } else {
                this.resetList();
                this.isLoadingProjects = true;

                // throttle
                this.searchTimeoutId = setTimeout(() => {
                    this.loadProjects();
                }, 250);
            }
        },
        loadProjects(page = 1, reset = true, callback = null) {
            if (reset) {
                this.resetList();
            }

            this.isLoadingProjects = true;

            ApiClient.Projects.getList(page, PROJECTS_PER_PAGE, {
                'envelope':         true,
                'search[archived]': (this.withFilterBar ? this.archived : defaultData.archived),
                'search[title]':    (this.withFilterBar ? this.searchTerm : defaultData.searchTerm),
                'sort':             '-createdAt',
            }).then((response) => {
                var projects  = Project.createInstances(CommonHelper.getNestedVal(response, 'data.response', []));
                this.projects = this.projects.concat(projects);

                this.totalProjects = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-total-count', 0) << 0;
                this.currentPage   = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-current-page', 1) << 0;

                if (CommonHelper.isFunction(callback)) {
                    callback(response);
                }
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingProjects = false;
            });
        },
        onProjectUpdate(project) {
            if (this.archived != project.isArchived) {
                this.onProjectDelete(project.id);
            }
        },
        onProjectDelete(projectId) {
            CommonHelper.removeByKey(this.projects, 'id', projectId);

            this.totalProjects--;
        },
    }
}
</script>
