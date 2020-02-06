import ApiClient from '@/utils/ApiClient';
import Project   from '@/models/Project';

export default {
    data() {
        return {
            pageTitle:        '',
            project:          new Project,
            isLoadingProject: false,
        }
    },
    watch: {
        'project.title': function (newVal, oldVal) {
            this.setCompositeDocumentTitle(this.pageTitle);
        },
    },
    methods: {
        loadProject(projectId) {
            if (this.isLoadingProject) {
                return;
            }

            projectId = projectId || this.$route.params.projectId;

            this.isLoadingProject = true;

            ApiClient.Projects.getOne(projectId).then((response) => {
                this.project.load(response.data);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingProject = false;
            });
        },
        updateProjectTitle(event) {
            this.$inlineTitleUpdate(
                event.target,
                this.project,
                ApiClient.Projects.update
            );
        },
        setCompositeDocumentTitle(title) {
            if (!title) {
                this.$setDocumentTitle(this.project.title)
            } else if (typeof title === 'function') {
                this.$setDocumentTitle(() => title() + ' (' + this.project.title + ')')
            } else {
                this.$setDocumentTitle(() => title + ' (' + this.project.title + ')')
            }
        },
    },
}
