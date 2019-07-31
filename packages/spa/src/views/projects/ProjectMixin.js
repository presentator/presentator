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
            if (
                !this.project.id ||                          // the project to update is not loaded yet
                !event.target ||                             // event input element doesn't exist
                event.target.innerText == this.project.title // no title change
            ) {
                if (event.target) {
                    event.target.blur();
                }

                return;
            }

            // reset if no title is provided
            if (!event.target.innerText) {
                event.target.innerText = this.project.title;

                event.target.blur();
                return;
            }

            var title = event.target.innerText;

            // optimistic update
            this.project.title = title;
            event.target.blur();

            // actual update
            ApiClient.Projects.update(this.project.id, {
                'title': title,
            }).then((response) => {
                this.project.load(response.data);
            }).catch((err) => {
                this.$errResponseHandler(err);
            });
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
