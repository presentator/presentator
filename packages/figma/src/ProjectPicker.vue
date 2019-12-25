<template>
    <div>
        <div class="search-bar">
            <span class="section-title">Project</span>

            <div class="search-input input-icon">
               <div class="input-icon__icon">
                 <div class="icon icon--search icon--black-3"></div>
                 </div>
                <input type="text"
                    class="input-icon__input"
                    placeholder="Search for project..."
                    v-model="searchTerm"
                    @input="loadProjects(1, true)"
                />
            </div>
        </div>

        <p v-if="!projects.length && !isLoading && isSearchApplied">No projects found :(</p>

        <div v-else class="projects-list">
            <project-create v-show="!isLoading && !isSearchApplied" @projectCreated="onProjectCreate" />

            <project-item v-for="project in projects"
                :key="project.id"
                :project="project"
                :class="{'selected': project.id == selectedProject}"
                @select="selectProject(project.id)"
            />
        </div>

        <div class="spacer"></div>

        <div class="row centered">
            <button v-if="isLoading" class="button button--secondary" key="loadingBtn" disabled>Loading...</button>

            <button v-if="!isLoading && hasMoreProjects" class="button button--secondary" key="loadMoreBtn" @click="loadProjects(currentPage + 1)">Load more</button>

            <button v-if="isSearchApplied" class="button button--secondary-destructive" key="clearSearchBtn" @click="clearSearch()">Clear search</button>
        </div>
    </div>
</template>

<script>
import clientStorage from '@/utils/ClientStorage';
import apiClient     from '@/utils/ApiClient';
import ProjectCreate from '@/ProjectCreate';
import ProjectItem   from '@/ProjectItem';

export default {
    name: 'project-picker',
    components: {
        'project-create': ProjectCreate,
        'project-item':   ProjectItem,
    },
    data() {
        return {
            isLoading:       false,
            totalProjects:   0,
            currentPage:     1,
            searchTerm:      '',
            projects:        [],
            selectedProject: null,
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

            clientStorage.setItem('lastSelectedProject', this.selectedProject);
        },
        loadProjects(page = 1, reset = false) {
            this.isLoading = true;

            if (reset) {
                this.projects        = [];
                this.selectedProject = null;
            }

            return apiClient.Projects.getList(page, 31, {
                'envelope':      true,
                'search[title]': this.searchTerm,
            }).then((response) => {
                this.isLoading = false;

                this.totalProjects = response.data.headers['x-pagination-total-count'] << 0;
                this.currentPage   = response.data.headers['x-pagination-current-page'] << 0;

                if (this.currentPage == 1) {
                    this.projects        = [];
                    this.selectedProject = null;
                }

                this.projects = this.projects.concat(response.data.response);

                const lastSelectedProject = clientStorage.getItem('lastSelectedProject');
                if (lastSelectedProject) {
                    this.$nextTick(() => {
                        this.selectProject(lastSelectedProject);
                    });
                }
            }).catch((err) => {
                if (err) { // is not error from aborted request
                    this.isLoading = false;
                }

                this.$baseApiErrorHandler(err);
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
