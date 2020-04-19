<template>
    <transition name="sidebarPanel">
        <aside v-if="isActive" class="app-sidebar-panel no-b"
            v-shortcut.27="hide"
            v-outside-click="{
                'handler': hide,
                'status':  isActive,
            }"
        >
            <div class="app-sidebar-section app-sidebar-header">
                <h4 class="title">{{ $t('Notifications') }}</h4>
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
                    <select v-if="Object.keys(projectsList).length" v-model="activeProjectId">
                        <option :value="null">{{ $t('All projects') }}</option>
                        <option v-for="(projectTitle, projectId) in projectsList"
                            :key="'filter_' + projectId"
                            :value="projectId"
                        >
                            {{ projectTitle }}
                        </option>
                    </select>
                </div>
                <div v-if="activeUnreadComments.length" class="block txt-right m-t-5">
                    <small class="link-primary" @click.prevent="markAllActiveAsRead">{{ $t('Mark all as read') }}</small>
                </div>
            </div>

            <div class="app-sidebar-section app-sidebar-content">
                <div v-if="isLoading" class="placeholder-block">
                    <span class="loader"></span>
                </div>

                <div v-if="!isLoading && !activeUnreadComments.length" class="placeholder-block">
                    <div class="icon"><i class="fe fe-bell"></i></div>
                    <div class="content">{{ $t('No notifications to show.') }}</div>
                </div>

                <div v-if="!isLoading && activeUnreadComments.length" class="comments-list">
                    <div v-for="comment in activeUnreadComments"
                        :key="'notification_' + comment.id"
                        class="comment-list-item"
                    >
                        <figure class="avatar">
                            <img v-if="comment.user && comment.user.getAvatar('small')"
                                :src="comment.user.getAvatar('small')"
                                :alt="$t('User avatar')"
                            >
                            <i v-else class="fe fe-user"></i>
                        </figure>
                        <div class="content">
                            <small class="content-header">
                                <span class="name">{{ comment.user ? comment.user.identifier : comment.from}}</span>
                                <span class="date txt-hint">{{ comment.createdAtFromNow }}</span>
                            </small>
                            <div class="message">{{ comment.message }}</div>
                            <div v-if="comment.metaData" class="meta">
                                <div v-if="comment.metaData.screenTitle"
                                    class="meta-item"
                                    :title="comment.metaData.screenTitle"
                                >
                                    <span class="txt screen-title">{{ comment.metaData.screenTitle }}</span>
                                </div>
                                <router-link :to="{
                                        name: 'screen',
                                        params: {
                                            projectId:   comment.metaData.projectId,
                                            prototypeId: comment.metaData.prototypeId,
                                            screenId:    comment.metaData.screenId,
                                        },
                                        query: {
                                            mode:      'comments',
                                            commentId: (comment.replyTo || comment.id),
                                        },
                                    }"
                                    class="meta-item link-primary"
                                >
                                    <span class="txt">Details</span>
                                </router-link>
                            </div>
                        </div>
                        <div class="list-ctrls">
                            <div class="list-ctrl-item ctrl-success"
                                v-tooltip.top="$t('Mark as read')"
                                @click.prevent="markAsRead(comment.id)"
                            >
                                <i class="fe fe-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </transition>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient     from '@/utils/ApiClient';
import CommonHelper  from '@/utils/CommonHelper';
import ScreenComment from '@/models/ScreenComment';

export default {
    name: 'notifications-panel',
    data() {
        return {
            isActive:        false,
            isLoading:       false,
            activeProjectId: null,
        }
    },
    computed: {
        ...mapState({
            unreadComments: state => state.notifications.unreadComments,
        }),
        ...mapGetters({
            getUnreadCommentsForProject: 'notifications/getUnreadCommentsForProject',
        }),

        projectsList() {
            var result = {};

            for (let i in this.unreadComments) {
                if (this.unreadComments[i].metaData) {
                    result[this.unreadComments[i].metaData.projectId] = this.unreadComments[i].metaData.projectTitle || 'N/A';
                }
            }

            // reset selected project id if missing from the list
            if (this.activeProjectId && !result[this.activeProjectId]) {
                this.activeProjectId = null;
            }

            return result;
        },
        activeUnreadComments() {
            if (!this.activeProjectId) {
                return this.unreadComments;
            }

            return this.getUnreadCommentsForProject(this.activeProjectId);
        },
    },
    methods: {
        ...mapActions({
            loadUserUnreadComments: 'notifications/loadUserUnreadComments',
            markAsRead:             'notifications/markAsRead',
        }),

        hide() {
            this.isActive = false;
        },
        show() {
            this.isActive = true;

            this.activeProjectId = null;

            this.isLoading = true;

            this.loadUserUnreadComments().finally(() => {
                this.isLoading = false;
            }).catch((err) => {});
        },
        toggle() {
            if (this.isActive) {
                this.hide();
            } else {
                this.show();
            }
        },
        markAllActiveAsRead() {
            var comments = this.activeUnreadComments.slice(); // clone to prevent computed property update side effects

            for (let i = comments.length - 1; i >= 0; i--) {
                this.markAsRead(comments[i].id);
            }
        },
    },
}
</script>

<style lang="scss" scoped>
.meta-item .screen-title {
    display: inline-block;
    vertical-align: top;
    max-width: 100px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
</style>
