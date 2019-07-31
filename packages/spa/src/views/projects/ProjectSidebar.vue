<template>
    <app-sidebar>
        <div class="menu-item-group">
            <router-link :to="{name: 'prototype', params: {projectId: projectId}}"
                class="menu-item"
                active-class="active"
                v-tooltip.right="$t('Prototypes')"
            >
                <i class="fe fe-layers"></i>
            </router-link>
            <router-link :to="{name: 'guideline', params: {projectId: projectId}}"
                class="menu-item"
                active-class="active"
                v-tooltip.right="$t('Guideline')"
            >
                <i class="fe fe-book-open"></i>
            </router-link>
        </div>

        <div class="menu-item" v-tooltip.right="$t('Project links')" @click.prevent="openLinksListPopup()">
            <i class="fe fe-link-2"></i>
        </div>

        <div class="menu-item" v-tooltip.right="$t('Project admins')" @click.prevent="openAdminsPopup()">
            <i class="fe fe-user-plus"></i>
        </div>

        <router-link :to="{name: 'projects'}" class="menu-item menu-item-secondary" v-tooltip.right="$t('Projects list')">
            <i class="fe fe-arrow-left"></i>
        </router-link>

        <relocator>
            <links-list-popup ref="linksListPopup"
                :projectId="projectId << 0"
                @shareProjectLink="openLinkSharePopup"
                @editProjectLink="openLinkUpsertPopup"
                @createProjectLink="openLinkUpsertPopup"
            ></links-list-popup>

            <link-upsert-popup ref="upsertLinkPopup"
                :projectId="projectId << 0"
                @close="onLinkUpsertPopupClose()"
            ></link-upsert-popup>

            <link-share-popup ref="shareLinkPopup"
                @close="onLinkSharePopupClose()"
            ></link-share-popup>

            <admins-popup ref="adminsPopup"
                :projectId="projectId << 0"
            ></admins-popup>
        </relocator>
    </app-sidebar>
</template>

<script>
import Relocator       from '@/components/Relocator';
import AppSidebar      from '@/views/base/AppSidebar';
import LinksListPopup  from '@/views/projects/LinksListPopup';
import LinkSharePopup  from '@/views/projects/LinkSharePopup';
import LinkUpsertPopup from '@/views/projects/LinkUpsertPopup';
import AdminsPopup     from '@/views/projects/AdminsPopup';

export default {
    name: 'project-sidebar',
    components: {
        'relocator':         Relocator,
        'app-sidebar':       AppSidebar,
        'links-list-popup':  LinksListPopup,
        'link-share-popup':  LinkSharePopup,
        'link-upsert-popup': LinkUpsertPopup,
        'admins-popup':      AdminsPopup,
    },
    props: {
        projectId: {
            required: true,
        },
    },
    methods: {
        openLinksListPopup() {
            this.$refs.linksListPopup.open();
        },
        openLinkSharePopup(link) {
            this.$refs.linksListPopup.close();
            this.$refs.shareLinkPopup.open(link);
        },
        openLinkUpsertPopup(link) {
            this.$refs.linksListPopup.close();
            this.$refs.upsertLinkPopup.open(link);
        },
        openAdminsPopup() {
            this.$refs.adminsPopup.open();
        },
        onLinkSharePopupClose() {
            this.$refs.linksListPopup.open();
        },
        onLinkUpsertPopupClose() {
            this.$refs.linksListPopup.open();
        },
    }
}
</script>
