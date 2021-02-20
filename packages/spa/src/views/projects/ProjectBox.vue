<template>
    <div class="box box-card"
        :data-id="project.id"
        @mouseleave="$refs.projectDropdown ? $refs.projectDropdown.hide() : true"
    >
        <figure class="box-thumb">
            <div class="crop-wrapper">
                <img v-if="project.getFeaturedScreen('medium')"
                    :src="project.getFeaturedScreen('medium')"
                    alt="Featured screen"
                    class="img"
                >
                <i v-else class="fe fe-image img"></i>
            </div>

            <div class="thumb-overlay">
                <router-link :to="{name: 'prototype', params: {projectId: project.id}}" class="overlay-ctrl"></router-link>

                <router-link :to="{name: 'prototype', params: {projectId: project.id}}" class="box-ctrl handle center">
                    <i class="fe fe-eye"></i>
                </router-link>

                <div class="box-ctrl handle top-right">
                    <i class="fe fe-more-horizontal"></i>
                    <toggler ref="projectDropdown" class="dropdown dropdown-sm">
                        <div class="dropdown-item" @click.prevent="updateArchivedState(!project.isArchived)">
                            <i class="fe fe-archive"></i>
                            <span class="txt">{{ project.isArchived ? $t('Unarchive') : $t('Archive') }}</span>
                        </div>
                        <hr>
                        <div class="dropdown-item link-danger" @click.prevent="deleteProject()">
                            <i class="fe fe-trash"></i>
                            <span class="txt">{{ $t('Delete') }}</span>
                        </div>
                    </toggler>
                </div>

                <div
                    v-if="!project.isArchived"
                    class="box-ctrl handle top-left"
                    :class="{'txt-warning': project.isPinned }"
                    v-tooltip="project.isPinned ? $t('Unstar project') : $t('Star project')"
                    @click.prevent="updatePinnedState(!project.isPinned)"
                >
                    <i class="fe fe-star"></i>
                </div>
            </div>
        </figure>

        <div class="box-content">
            <div ref="titleLabel"
                key="title"
                class="title"
                contenteditable="true"
                spellcheck="false"
                autocomplete="off"
                :title="$t('Click to edit')"
                :data-placeholder="project.title || $t('Title')"
                @blur="saveTitle()"
                @keydown.enter.prevent="saveTitle()"
            >{{ project.title }}</div>

            <div class="meta">
                <div class="meta-item">{{ $t('Created {date}', {date: project.createdAtFromNow}) }}</div>
                <div v-if="project.isArchived" class="meta-item">
                    <span class="label label-transp-warning">{{ $t('Archived') }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ApiClient from '@/utils/ApiClient';
import Project   from '@/models/Project';

export default {
    name: 'project-box',
    props: {
        project: {
            type:     Project,
            required: true,
        },
    },
    methods: {
        updateArchivedState(archive) {
            var confirmMsg = '';
            if (archive) {
                confirmMsg = this.$t('Do you really want to archive "{title}"?', {title: this.project.title});
            } else {
                confirmMsg = this.$t('Do you really want to unarchive "{title}"?', {title: this.project.title});
            }

            if (!window.confirm(confirmMsg)) {
                return;
            }

            ApiClient.Projects.update(this.project.id, {
                archived: archive ? 1 : 0,
            }).then((response) => {
                this.project.load(response.data)

                this.$toast(this.$t('Successfully updated project archived state.'));

                this.$emit('projectUpdate', this.project);
            }).catch((err) => {
                this.$errResponseHandler(err);
            });
        },
        updatePinnedState(pin) {
            let isPinned = pin ? 1 : 0;

            // actual update
            ApiClient.Projects.update(this.project.id, {
                pinned: isPinned,
            });

            // optimistic update
            this.project.pinned = isPinned;
            this.$emit('projectUpdate', this.project);
        },
        deleteProject() {
            if (!window.confirm(this.$t('Do you really want to delete project "{title}"?', {title: this.project.title}))) {
                return;
            }

            // actual delete
            ApiClient.Projects.delete(this.project.id);

            // optimistic delete
            this.$toast(this.$t('Successfully deleted project "{title}".', {title: this.project.title}));
            this.$emit('projectDelete', this.project.id);
        },
        saveTitle() {
            this.$inlineTitleUpdate(
                this.$refs.titleLabel,
                this.project,
                ApiClient.Projects.update
            );
        }
    },
}
</script>
