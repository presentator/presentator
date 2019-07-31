<template>
    <div v-if="isLoadingScreens" class="full-page-flex">
        <div class="flex-fill-block"></div>
        <div class="block txt-center">
            <span class="loader loader-lg loader-blend"></span>
        </div>
        <div class="flex-fill-block"></div>
    </div>
    <div v-else-if="activeScreen"
        class="preview-container"
        :class="{
            'comments-mode':      isInCommentsMode,
            'hotspots-mode':      isInHotspotsMode,
            'preview-mode':       isInPreviewMode,
            'preview-mode-hints': isPreviewModeHintsActive,
        }"
        :style="{
            'background': activePrototype.isForDesktop && activeScreen ? activeScreen.background : null
        }"
        tabindex="-1"
        @keydown.esc="onEscPress"
        @keydown.ctrl.83.exact.prevent=""
        @keyup.ctrl.83.exact.prevent="snapActiveHotspot"
    >
        <div class="flex-fill-block"></div>

        <hotspot-popover
            ref="hotspotPopover"
            @closed="onHotspotPopoverClose"
        ></hotspot-popover>

        <comment-popover
            ref="commentPopover"
            :isForPreview="false"
            :mentionsList="mentionsList"
            @closed="onCommentPopoverClose"
        ></comment-popover>

        <screen-preview
            ref="screenPreview"
            :interactions="isInPreviewMode"
            :activeScreenTooltip="modeHelpTooltip"
            @activeScreenMousedown="onActiveScreenMousedown"
            @activeScreenClick="onActiveScreenClick"
        >
            <div v-if="isInHotspotsMode" class="block hotspots-block">
                <hotspot-box v-for="hotspot in activeScreenHotspots"
                    ref="hotspotBoxes"
                    :key="hotspot.id"
                    :hotspot="hotspot"
                    :snapToImage="$refs.screenPreview ? $refs.screenPreview.$refs.activeScreen : null"
                    @repositioning="onHotspotRepositioning"
                    @repositionStopped="onHotspotRepositioning"
                    @beforeActivate="onHotspotActivate"
                ></hotspot-box>
            </div>

            <div v-if="isInCommentsMode" class="block comments-block">
                <comment-pin v-for="comment in activeScreenComments"
                    ref="screenCommentPins"
                    :key="comment.id"
                    :comment="comment"
                    :allowPositionChange="true"
                    :class="{
                        'soft-hidden': (!showResolvedComments && comment.isResolved),
                        'unread': isCommentUnread(comment.id),
                    }"
                    @repositioning="onCommentRepositioning"
                    @beforeActivate="onCommentActivate"
                ></comment-pin>
            </div>
        </screen-preview>

        <div class="flex-fill-block"></div>

        <nav class="floating-bar preview-bar active">
            <div class="nav nav-left">
                <router-link :to="{name: 'prototype', params: {projectId: activePrototype.projectId, prototypeId: activePrototype.id}}"
                    class="ctrl-item ctrl-item-circle ctrl-item-close"
                    v-tooltip.top="$t('Back to listing')"
                >
                    <i class="fe fe-arrow-left"></i>
                </router-link>

                <div v-if="$refs.screensPanel"
                    class="ctrl-item ctrl-item-screens"
                    :class="{'active': $refs.screensPanel.isActive}"
                    v-tooltip.top="$t($refs.screensPanel.isActive ? 'Hide screens panel' : 'Show screens panel')"
                    @click.prevent="$refs.screensPanel.toggle()"
                >
                    <span class="txt screen-title">{{ activeScreen.title }}</span>
                    <span class="txt counter m-l-5">({{ $t('{current} of {total}', {current: activeScreenOrderedIndex + 1, total: screens.length}) }})</span>
                    <i class="m-l-5 fe" :class="$refs.screensPanel.isActive ? 'fe-chevron-up' : 'fe-chevron-down'"></i>
                </div>
            </div>
            <div class="nav nav-center">
                <div class="ctrl-item ctrl-item-circle ctrl-item-success"
                    :class="{'highlight': isInPreviewMode}"
                    v-tooltip.top="$t('Preview mode (P)')"
                    v-shortcut.80="setPreviewMode"
                    @click.prevent="setPreviewMode()"
                >
                    <i class="fe fe-eye"></i>
                </div>
                <div class="ctrl-item ctrl-item-circle ctrl-item-primary"
                    :class="{'highlight': isInHotspotsMode}"
                    v-tooltip.top="$t('Hotspots mode (H)')"
                    v-shortcut.72="setHotspotsMode"
                    @click.prevent="setHotspotsMode()"
                >
                    <span v-if="isLoadingHotspots || isLoadingHotspotTemplates" class="loader"></span>
                    <i v-else class="fe fe-target"></i>
                </div>
                <div class="ctrl-item ctrl-item-circle ctrl-item-danger"
                    :class="{'highlight': isInCommentsMode}"
                    v-tooltip.top="$t('Comments mode (C)')"
                    v-shortcut.67="setCommentsMode"
                    @click.prevent="setCommentsMode()"
                >
                    <span v-if="activeUnreadComments.length" class="beacon beacon-danger"></span>

                    <span v-if="isLoadingComments" class="loader"></span>
                    <i v-else class="fe fe-message-circle"></i>
                </div>
            </div>
            <div class="nav nav-right">
                <div v-if="isInCommentsMode && totalActiveScreenComments > 0" class="form-group">
                    <input type="checkbox" id="toggle_resolved_comments" v-model="showResolvedComments">
                    <label for="toggle_resolved_comments">
                        {{ $t('Show resolved comments') }}
                        ({{ $t('{current} of {total}', {
                            current: totalActiveScreenResolvedComments,
                            total:   totalActiveScreenComments,
                        }) }})
                    </label>
                </div>

                <div v-if="isInHotspotsMode" class="ctrl-item ctrl-item-templates txt-default">
                    <span class="txt counter m-r-5">{{ totalActiveHotspotTemplates }}</span>
                    <span class="txt title m-r-5">
                        {{ $t(totalActiveHotspotTemplates == 1 ? $t('Active hotspot template') : $t('Active hotspot templates')) }}
                    </span>
                    <i class="fe fe-chevron-up"></i>

                    <hotspot-templates-popover
                        class="transform-bottom-right"
                        :screen="activeScreen"
                    ></hotspot-templates-popover>
                </div>

                <div class="ctrl-item ctrl-item-circle">
                    <div v-tooltip.top="$t('Screen settings')">
                        <i class="fe fe-settings"></i>
                    </div>

                    <screen-edit-popover
                        ref="screenEditPopover"
                        class="transform-bottom-right"
                        :screen="activeScreen"
                    ></screen-edit-popover>
                </div>
            </div>
        </nav>

        <screens-panel ref="screensPanel"></screens-panel>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient               from '@/utils/ApiClient';
import CommonHelper            from '@/utils/CommonHelper';
import ScreenPreview           from '@/views/screens/ScreenPreview';
import ScreensPanel            from '@/views/screens/ScreensPanel';
import ScreenEditPopover       from '@/views/screens/ScreenEditPopover';
import HotspotBox              from '@/views/hotspots/HotspotBox';
import HotspotPopover          from '@/views/hotspots/HotspotPopover';
import HotspotTemplatesPopover from '@/views/hotspots/HotspotTemplatesPopover';
import CommentPin              from '@/views/comments/CommentPin';
import CommentPopover          from '@/views/comments/CommentPopover';
import PreviewModeMixin        from '@/views/screens/PreviewModeMixin';
import CommentsModeMixin       from '@/views/screens/CommentsModeMixin';
import HotspotsModeMixin       from '@/views/screens/HotspotsModeMixin';

const MODE_PREVIEW  = 'preview';
const MODE_HOTSPOTS = 'hotspots';
const MODE_COMMENTS = 'comments';

export default {
    name: 'screens-view',
    mixins: [
        PreviewModeMixin,
        CommentsModeMixin,
        HotspotsModeMixin,
    ],
    components: {
        'screen-preview':            ScreenPreview,
        'screens-panel':             ScreensPanel,
        'screen-edit-popover':       ScreenEditPopover,
        'comment-pin':               CommentPin,
        'comment-popover':           CommentPopover,
        'hotspot-box':               HotspotBox,
        'hotspot-popover':           HotspotPopover,
        'hotspot-templates-popover': HotspotTemplatesPopover,
    },
    data() {
        return {
            isLoadingScreens: false,
            mode:             MODE_PREVIEW,
        }
    },
    computed: {
        ...mapState({
            loggedUser:        state => state.user.user,
            activePrototypeId: state => state.prototypes.activePrototypeId,
            screens:           state => state.screens.screens,
            activeScreenId:    state => state.screens.activeScreenId,
        }),
        ...mapGetters({
            activePrototype:            'prototypes/activePrototype',
            getScreen:                  'screens/getScreen',
            activeScreen:               'screens/activeScreen',
            activeScreenOrderedIndex:   'screens/activeScreenOrderedIndex',
        }),

        isInCommentsMode() {
            return this.mode === MODE_COMMENTS;
        },
        isInHotspotsMode() {
            return this.mode === MODE_HOTSPOTS;
        },
        isInPreviewMode() {
            return this.mode === MODE_PREVIEW;
        },
        modeHelpTooltip() {
            if (this.isInCommentsMode) {
                return this.$t('Click to leave a comment');
            }

            if (this.isInHotspotsMode) {
                return this.$t('Click and drag to create a hotspot\n(hold "Ctrl" to snap)');
            }

            return '';
        },
    },
    watch: {
        mode(newVal, oldVal) {
            this.updateRouteMode();
        },
        activeScreenId(newVal, oldVal) {
            this.deactivateHotspots();
            this.deactivateComments();
            this.updateRouteScreenId();
        },
        '$route.params.screenId': function (newVal, oldVal) {
            if (
                !newVal ||
                !this.getScreen(newVal)
            ) {
                this.updateRouteScreenId();
            } else if (newVal != this.activeScreenId) {
                this.setActiveScreenId(newVal);
            }
        },
        '$route.query.mode': function (newVal, oldVal) {
            if (newVal === MODE_COMMENTS) {
                this.setCommentsMode();
            } else if (newVal === MODE_PREVIEW) {
                this.setPreviewMode();
            } else if (newVal === MODE_HOTSPOTS) {
                this.setHotspotsMode();
            } else {
                this.updateRouteMode();
            }
        },
    },
    beforeMount() {
        if (
            this.$route.query.mode === MODE_COMMENTS ||
            this.$route.query.commentId
        ) {
            this.setCommentsMode();
        } else if (this.$route.query.mode === MODE_HOTSPOTS) {
            this.setHotspotsMode();
        } else {
            this.setPreviewMode();
        }

        this.init();
    },
    methods: {
        ...mapActions({
            setScreens:           'screens/setScreens',
            setActiveScreenId:    'screens/setActiveScreenId',
            addPrototype:         'prototypes/addPrototype',
            setActivePrototypeId: 'prototypes/setActivePrototypeId',
        }),

        init() {
            this.$setDocumentTitle(() => this.$t('Screen'));

            this.loadScreens(this.$route.params.prototypeId, this.$route.params.screenId);
            this.loadHotspots(this.$route.params.prototypeId);
            this.loadHotspotTemplates(this.$route.params.prototypeId);
            this.loadComments(this.$route.params.prototypeId);
            this.loadMentionsList(this.$route.params.projectId);
        },
        loadMentionsList(projectId) {
            projectId = projectId || (this.activePrototype ? this.activePrototype.projectId : null);
            if (!projectId) {
                return;
            }

            ApiClient.Projects.getCollaboratorsList(projectId).then((response) => {
                this.mentionsList = this.convertCollaboratorsListToMentionsList(response.data, [this.loggedUser.email]);
            });
        },
        loadScreens(prototypeId, screenId) {
            prototypeId = prototypeId || this.activePrototypeId;

            if (!prototypeId || this.isLoadingScreens) {
                return;
            }

            this.isLoadingScreens = true;

            ApiClient.Prototypes.getOne(prototypeId, {
                'expand': 'screens',
            }).then((response) => {
                this.addPrototype(response.data);
                this.setActivePrototypeId(CommonHelper.getNestedVal(response, 'data.id'));

                this.setScreens(CommonHelper.getNestedVal(response, 'data.screens', []));

                if (this.getScreen(screenId)) {
                    this.setActiveScreenId(screenId);
                }

                this.updateRouteProjectId();
                this.updateRouteScreenId();
                this.updateRouteMode();
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingScreens = false;
            });
        },
        updateRouteProjectId() {
            if (this.activePrototype && this.$route.params.projectId != this.activePrototype.projectId) {
                this.$router['replace']({
                    to: this.$route.name,
                    params: Object.assign({}, this.$route.params, {
                        projectId: this.activePrototype.projectId,
                    }),
                    query:  Object.assign({}, this.$route.query),
                });
            }
        },
        updateRouteScreenId() {
            let routeScreenId = this.$route.params.screenId;

            if (routeScreenId != this.activeScreenId) {
                this.$router[!routeScreenId ? 'replace' : 'push']({
                    to: this.$route.name,
                    params: Object.assign({}, this.$route.params, {
                        screenId: this.activeScreenId
                    }),
                    query:  Object.assign({}, this.$route.query),
                });
            }
        },
        updateRouteMode() {
            if (this.$route.query.mode != this.mode) {
                this.$router.replace({
                    name:   this.$route.name,
                    params: Object.assign({}, this.$route.params),
                    query:  Object.assign({}, this.$route.query, {
                        mode: this.mode,
                    }),
                });
            }
        },
        setCommentsMode() {
            this.mode = MODE_COMMENTS;
        },
        setHotspotsMode() {
            this.mode = MODE_HOTSPOTS;
        },
        setPreviewMode() {
            this.mode = MODE_PREVIEW;
        },
        onEscPress(e) {
            if (this.isInHotspotsMode) {
                e.preventDefault();

                this.deactivateHotspots();
            } else if (this.isInCommentsMode) {
                e.preventDefault();

                this.deactivateComments();
            }
        },
        onActiveScreenMousedown(e) {
            if (this.isInHotspotsMode) {
                this.initHotspotCreation(e, this.activeScreenId);
            }
        },
        onActiveScreenClick(e) {
            if (this.isInPreviewMode) {
                this.blinkPreviewModeHints();
            } else if (this.isInCommentsMode) {
                this.initCommentCreation(e, this.activeScreenId);
            }
        },
    },
}
</script>
