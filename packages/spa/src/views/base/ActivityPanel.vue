<template>
    <transition name="sidebarPanel">
        <aside v-if="isActive"
            class="app-sidebar-panel no-b"
            v-shortcut.27="hide"
            v-outside-click="{
                'handler': hide,
                'status':  isActive,
            }"
        >
            <div class="app-sidebar-section app-sidebar-header">
                <h4 class="title">{{ $t('Recent activity') }}</h4>
                <div class="list-ctrls">
                    <div class="list-ctrl-item"
                        v-tooltip.right="$t('Close panel')"
                        @click.prevent="hide"
                    >
                        <i class="fe fe-x"></i>
                    </div>
                </div>

                <div class="clearfix m-b-small"></div>

                <div class="form-group form-group-sm">
                    <select>
                        <option :value="null">{{ $t('Viewed project links') }}</option>
                    </select>
                </div>
            </div>

            <div class="app-sidebar-section app-sidebar-content">
                <div v-if="isLoading" class="placeholder-block">
                    <span class="loader"></span>
                </div>

                <div v-if="!isLoading && !projectLinks.length" class="placeholder-block">
                    <div class="icon"><i class="fe fe-activity"></i></div>
                    <div class="content">{{ $t('No recent activity to show.') }}</div>
                </div>

                <div v-if="!isLoading && projectLinks.length" class="cards-list">
                    <a v-for="projectLink in projectLinks"
                        class="card"
                        target="_blank"
                        :href="projectLink.fullUrl"
                    >
                        <figure class="icon">
                            <i class="fe fe-link-2"></i>
                        </figure>
                        <div class="content">
                            <div class="title">{{ projectLink.project ? projectLink.project.title : projectLink.slug }}</div>
                            <div class="meta">
                                <div class="meta-item">{{ projectLink.fullUrl }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </aside>
    </transition>
</template>

<script>
import ApiClient   from '@/utils/ApiClient';
import ProjectLink from '@/models/ProjectLink';

export default {
    name: 'activity-panel',
    data() {
        return {
            isActive:     false,
            isLoading:    false,
            projectLinks: [],
        }
    },
    methods: {
        hide() {
            this.isActive = false;
        },
        show() {
            this.isActive = true;

            this.getAccessedProjectLinks();
        },
        toggle() {
            if (this.isActive) {
                this.hide();
            } else {
                this.show();
            }
        },
        getAccessedProjectLinks() {
            this.isLoading = true;

            ApiClient.ProjectLinks.getAccessed(1, 30).then((response) => {
                this.projectLinks = ProjectLink.createInstances(response.data);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoading = false;
            });
        }
    },
}
</script>
