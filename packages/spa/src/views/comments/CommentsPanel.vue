<template>
    <transition name="sidebarPanel" @after-enter="triggerResize" @after-leave="triggerResize">
        <aside v-if="isActive" class="app-sidebar-panel screen-comments-panel">
            <header class="app-sidebar-section app-sidebar-header">
                <h4 class="title">{{ $t('Screen comments') }}</h4>
                <div class="list-ctrls">
                    <div class="list-ctrl-item"
                        v-tooltip.left="$t('Close panel')"
                        @click.prevent="hide"
                    >
                        <i class="fe fe-x"></i>
                    </div>
                </div>

                <div v-if="totalActiveScreenComments > 0" class="form-group form-group-switch m-t-20 m-b-5">
                    <input type="checkbox" id="show_resolved" v-model="resolvedCommentsToggle">
                    <label for="show_resolved">{{ $t('Show resolved comments ({count})', {count: totalActiveScreenResolvedComments}) }}</label>
                </div>
            </header>

            <div class="app-sidebar-section app-sidebar-content">
                <div v-if="totalActiveScreenComments <= 0" class="placeholder-block">
                    <div class="icon"><i class="fe fe-message-circle"></i></div>
                    <div class="content">{{ $t("The screen doesn't have any comments yet.") }}</div>
                </div>

                <div v-else-if="!showResolvedComments && totalActiveScreenComments > 0 && !hasUnresolvedComments" class="placeholder-block">
                    <div class="icon"><i class="fe fe-message-circle"></i></div>
                    <div class="content">{{ $t('All screen comments have been resolved.') }}</div>
                </div>

                <div v-else class="cards-list">
                    <template v-for="(comment, i) in activeScreenComments">
                        <div
                            :key="'cp_' + comment.id + i"
                            v-if="!comment.isNew && (!comment.isResolved || showResolvedComments)"
                            class="card comment-card"
                            :class="{
                                'active': activeCommentId == comment.id,
                                'card-danger': isCommentUnread(comment.id)
                            }"
                            @click.prevent="setActiveCommentId(comment.id)"
                        >
                            <figure class="icon">
                                <span class="txt txt-hint">{{ totalActiveScreenComments-i }}</span>
                                <i v-if="comment.isResolved" class="fe fe-check txt-success"></i>
                            </figure>
                            <div class="content">
                                <div class="meta">
                                    <div class="meta-item">{{ comment.user ? comment.user.identifier : comment.from }}</div>
                                    <div class="meta-item" :title="comment.createdAtLocal">
                                        {{ comment.createdAtFromNow }}
                                    </div>
                                </div>
                                <div class="title txt-default">{{ comment.message }}</div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </aside>
    </transition>
</template>

<style lang="scss" scoped>
.screen-comments-panel {
    position: relative;
    left: auto;
    right: auto;
    flex-shrink: 0;
    .comment-card .meta .meta-item {
        max-width: 152px;
    }
}
</style>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';

export default {
    name: 'comments-panel',
    data() {
        return {
            isActive: false,
        }
    },
    computed: {
        ...mapState({
            activePrototypeId:    state => state.prototypes.activePrototypeId,
            activeScreenId:       state => state.screens.activeScreenId,
            showResolvedComments: state => state.comments.showResolvedComments,
            activeCommentId:      state => state.comments.activeCommentId,
            unreadComments:       state => state.notifications.unreadComments,
        }),
        ...mapGetters({
            getCommentsForScreen: 'comments/getCommentsForScreen',
            isCommentUnread:      'notifications/isCommentUnread',
        }),

        activeScreenComments() {
            return this.getCommentsForScreen(this.activeScreenId).reverse();
        },
        activeScreenResolvedComments() {
            return this.getCommentsForScreen(this.activeScreenId, 'resolved').reverse();
        },
        totalActiveScreenComments() {
            return this.activeScreenComments.length;
        },
        totalActiveScreenResolvedComments() {
            return this.activeScreenResolvedComments.length;
        },
        hasUnresolvedComments() {
            return this.totalActiveScreenResolvedComments < this.totalActiveScreenComments;
        },
        resolvedCommentsToggle: {
            get() {
              return this.showResolvedComments;
            },
            set(value) {
              this.setShowResolvedComments(value);
            },
        },
    },
    methods: {
        ...mapActions({
            setShowResolvedComments: 'comments/setShowResolvedComments',
            setActiveCommentId:      'comments/setActiveCommentId',
        }),

        hide() {
            this.isActive = false;
        },
        show() {
            this.isActive = true;
        },
        toggle() {
            if (this.isActive) {
                this.hide();
            } else {
                this.show();
            }
        },
        // trigger resize event for popovers, tooltips, etc.
        triggerResize() {
            window.dispatchEvent(new Event('resize'));
        },
    },
}
</script>
