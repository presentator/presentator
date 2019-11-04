<template>
    <div>
        <div class="search-bar">
            <span>Project</span>

            <input type="text"
                uxp-quiet="true"
                placeholder="🔍 Search for project..."
                v-model="searchTerm"
                @input="loadProjects(1, true)"
            />
        </div>

        <p v-if="!projects.length && !isLoading && isSearchApplied">No projects found :(</p>

        <div v-else class="projects-list">
            <project-create v-show="!isLoading && !isSearchApplied" @projectCreated="onProjectCreate" />

            <project-item v-for="project in projects"
                :key="project.id"
                :project="project"
                :class="{'selected': project.id == selectedProject}"
                @overlayClick="selectProject(project.id)"
            />
        </div>

        <div class="row centered">
            <button v-if="isLoading" key="loadingBtn" uxp-variant="primary" uxp-quiet="true">Loading...</button>

            <button v-if="!isLoading && hasMoreProjects" key="loadMoreBtn" class="column" uxp-variant="primary" @click="loadProjects(currentPage + 1)">Load more</button>

            <button v-if="isSearchApplied" key="clearSearchBtn" class="column" uxp-variant="primary" @click="clearSearch()">Clear search</button>
        </div>
    </div>
</template>

<script>
const storageHelper = require('xd-storage-helper');
const ApiClient     = require('@/utils/ApiClient.js');
const ProjectCreate = require('@/ProjectCreate.vue').default;
const ProjectItem   = require('@/ProjectItem.vue').default;

module.exports = {
    name: 'project-picker',
    components: {
        'project-create': ProjectCreate,
        'project-item':   ProjectItem,
    },
    data() {
        return {
            isLoading:         false,
            totalProjects:     0,
            currentPage:       1,
            searchTerm:        '',
            projects:          [],
            selectedProject:   null,
        }
    },
    computed: {
        isSearchApplied() {
            return this.searchTerm.length > 0;
        },
        hasMoreProjects() {
            return this.totalProjects > this.projects.length;
        },
    },
    watch: {
        selectedProject(newVal, oldVal) {
            this.$emit('changed', newVal);
        },
    },
    mounted() {
        this.loadProjects(1, true);
    },
    methods: {
        clearSearch() {
            this.searchTerm = '';

            this.loadProjects(1, true);
        },
        hasProject(id) {
            for (let i = this.projects.length - 1; i >= 0; i--) {
                if (this.projects[i].id == id) {
                    return true;
                }
            }

            return false;
        },
        selectProject(id) {
            this.selectedProject = this.hasProject(id) ? id : null;

            storageHelper.set('lastSelectedProject', this.selectedProject);
        },
        async loadProjects(page = 1, reset = false) {
            this.isLoading = true;

            if (reset) {
                this.projects        = [];
                this.selectedProject = null;
            }

            return ApiClient.Projects.getList(page, 31, {
                'envelope':      true,
                'search[title]': this.searchTerm,
            }).then(async (response) => {
                this.totalProjects = response.data.headers['x-pagination-total-count'] << 0;
                this.currentPage   = response.data.headers['x-pagination-current-page'] << 0;

                if (this.currentPage == 1) {
                    this.projects        = [];
                    this.selectedProject = null;
                }

                this.projects = this.projects.concat(response.data.response);

                const lastSelectedProject = await storageHelper.get('lastSelectedProject', null);
                if (lastSelectedProject) {
                    this.$nextTick(() => {
                        this.selectProject(lastSelectedProject);
                    });
                }
            }).catch((err) => {
                this.$baseApiErrorHandler(err);
            }).finally(() => {
                this.isLoading = false;
            });
        },
        async onProjectCreate(project) {
            await this.loadProjects();

            this.$nextTick(() => {
                this.selectProject(project.id);
            });
        },
    },
}
</script>
