<template>
    <div class="app-body">
        <home-sidebar></home-sidebar>

        <main class="app-main">
            <header class="app-header">
                <nav class="breadcrumbs">
                    <div class="breadcrumb-item active">{{ $t('Projects') }}</div>
                </nav>

                <div class="flex-fill-block"></div>

                <button class="btn btn-cons-lg btn-success m-l-10"
                    @click.prevent="$refs.projectsListing ? $refs.projectsListing.openCreatePopup() : true"
                >
                    <i class="fe fe-plus"></i>
                    <span class="txt">{{ $t('New project') }}</span>
                </button>
            </header>

            <projects-listing ref="projectsListing"></projects-listing>

            <div class="flex-fill-block"></div>

            <app-footer class="m-t-base"></app-footer>
        </main>
    </div>
</template>

<script>
import AppFooter       from '@/views/base/AppFooter';
import HomeSidebar     from '@/views/base/HomeSidebar';
import ProjectsListing from '@/views/projects/ProjectsListing';

export default {
    name: 'projects',
    components: {
        'app-footer':       AppFooter,
        'home-sidebar':     HomeSidebar,
        'projects-listing': ProjectsListing,
    },
    beforeMount() {
        this.$setDocumentTitle(() => this.$t('Projects'));
    },
}
</script>
