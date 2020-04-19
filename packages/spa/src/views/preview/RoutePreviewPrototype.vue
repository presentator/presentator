<template>
    <div class="preview-container-wrapper">
        <div class="preview-container"
            :class="{
                'comments-mode':      isInCommentsMode,
                'preview-mode':       isInPreviewMode,
                'preview-mode-hints': isPreviewModeHintsActive,
            }"
            :style="{
                'background': activePrototype && activeScreen && activePrototype.isForDesktop ? activeScreen.background : null
            }"
            tabindex="-1"
            @keydown.esc="onEscPress"
        >
            <div class="flex-fill-block"></div>

            <active-comment-popover
                ref="commentPopover"
                :isForPreview="true"
                :mentionsList="mentionsList"
            ></active-comment-popover>

            <div v-if="isLoadingData" class="block txt-center">
                <span class="loader loader-lg loader-blend"></span>
            </div>

            <div v-if="!isLoadingData && !screens.length" class="block scroll-block txt-center p-base">
                <figure class="mockup m-b-small">
                    <div class="mockup-bg"></div>
                    <div class="browser secondary"></div>
                    <div class="browser primary"><i class="fe fe-image"></i></div>
                </figure>

                <h4>{{ $t('No prototype screens to show.') }}</h4>
            </div>

            <screen-preview
                v-if="!isLoadingData && screens.length"
                ref="screenPreview"
                :interactions="isInPreviewMode"
                :activeScreenTooltip="modeHelpTooltip"
                :fitToScreen="fitToScreen"
                @activeScreenClick="onActiveScreenClick"
            >
                <div v-if="isInCommentsMode" class="block comments-block">
                    <comment-pin v-for="comment in activeScreenComments"
                        ref="screenCommentPins"
                        :key="'comment_' + comment.id"
                        :comment="comment"
                        :allowPositionChange="false"
                        :class="{
                            'soft-hidden': (!showResolvedComments && comment.isResolved),
                            'unread': isCommentUnread(comment.id),
                        }"
                    ></comment-pin>
                </div>
            </screen-preview>

            <div class="flex-fill-block"></div>

            <preview-bar
                :project="project"
                :projectLink="projectLink"
                @hide="$refs.screensPanel ? $refs.screensPanel.hide() : true"
            >
                <template v-slot:left>
                    <div v-if="activeScreen && $refs.screensPanel"
                        class="ctrl-item ctrl-item-screens"
                        :class="{'active': $refs.screensPanel.isActive}"
                        v-tooltip.top="$refs.screensPanel.isActive ? $t('Hide screens panel') : $t('Show screens panel')"
                        @click.prevent="$refs.screensPanel.toggle()"
                    >
                        <span class="txt screen-title">{{ activeScreen.title }}</span>
                        <span class="txt counter m-l-5">({{ activeScreenOrderedIndex + 1 }} of {{ screens.length }})</span>
                        <i class="m-l-5 fe" :class="$refs.screensPanel.isActive ? 'fe-chevron-up' : 'fe-chevron-down'"></i>
                    </div>
                </template>

                <template v-slot:right>
                    <button v-if="isInCommentsMode && $refs.commentsPanel"
                        class="btn btn-sm no-shadow comments-panel-toggle"
                        :class="$refs.commentsPanel.isActive ? 'btn-danger' : 'btn-transp-danger'"
                        @click.prevent="$refs.commentsPanel.toggle()"
                    >
                        <span class="txt">
                            {{ $t('Comments panel ({resolved}/{total})', {
                                resolved: totalActiveScreenResolvedComments,
                                total: totalActiveScreenComments,
                            }) }}
                        </span>
                    </button>

                    <div v-if="prototypes.length > 0 && activePrototype.scaleFactor != 0"
                        class="ctrl-item ctrl-item-circle"
                        :class="fitToScreen ? 'ctrl-item-success active bg-light-border' : ''"
                        v-tooltip.top="$t('Toggle fit to screen')"
                        @click.prevent="toggleFitToScreen"
                    >
                        <i class="fe fe-maximize"></i>
                    </div>

                    <div v-if="prototypes.length > 1"
                        class="btn btn-sm btn-default m-l-small"
                        v-tooltip.top="!$refs.prototypesDropdown || !$refs.prototypesDropdown.isActive ? $t('Change prototype') : ''"
                    >
                        <i class="fe" :class="activePrototype.isForDesktop ? 'fe-monitor' : 'fe-smartphone'"></i>
                        <span class="txt title m-l-5 m-r-5">{{ activePrototype.title }}</span>
                        <i class="fe" :class="$refs.prototypesDropdown && $refs.prototypesDropdown.isActive ? 'fe-chevron-up' : 'fe-chevron-down'"></i>

                        <toggler ref="prototypesDropdown" class="dropdown">
                            <div class="dropdown-item"
                                v-for="prototype in prototypes"
                                :key="prototype.id"
                                :class="{'active': activePrototype.id == prototype.id}"
                                @click.prevent="setActivePrototypeId(prototype.id)"
                            >
                                <i class="fe" :class="prototype.isForDesktop ? 'fe-monitor' : 'fe-smartphone'"></i>
                                <span class="txt">{{ prototype.title }}</span>
                            </div>
                        </toggler>
                    </div>
                </template>
            </preview-bar>

            <screens-panel ref="screensPanel"></screens-panel>
        </div>

        <comments-panel ref="commentsPanel"></comments-panel>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import moment               from 'moment';
import AppConfig            from '@/utils/AppConfig';
import ApiClient            from '@/utils/ApiClient';
import CommonHelper         from '@/utils/CommonHelper';
import ClientStorage        from '@/utils/ClientStorage';
import Project              from '@/models/Project';
import ProjectLink          from '@/models/ProjectLink';
import GuidelineSection     from '@/models/GuidelineSection';
import ScreenComment        from '@/models/ScreenComment';
import ScreenPreview        from '@/views/screens/ScreenPreview';
import ScreensPanel         from '@/views/screens/ScreensPanel';
import CommentPin           from '@/views/comments/CommentPin';
import CommentsPanel        from '@/views/comments/CommentsPanel';
import ActiveCommentPopover from '@/views/comments/ActiveCommentPopover';
import PreviewModeMixin     from '@/views/screens/PreviewModeMixin';
import CommentsModeMixin    from '@/views/screens/CommentsModeMixin';
import SectionPreview       from '@/views/guidelines/SectionPreview';
import PreviewBar           from '@/views/preview/PreviewBar';

const MODE_PREVIEW  = 'preview';
const MODE_COMMENTS = 'comments';

let firestoreUnsubscribe = null;

export default {
    name: 'screens-view',
    mixins: [
        PreviewModeMixin,
        CommentsModeMixin,
    ],
    props: {
        project: {
            type:     Project,
            required: true,
        },
        collaborators: {
            type:     Array,
            required: true,
        },
        projectLink: {
            type:     ProjectLink,
            required: true,
        },
    },
    components: {
        'screen-preview':            ScreenPreview,
        'screens-panel':             ScreensPanel,
        'comment-pin':               CommentPin,
        'comments-panel':            CommentsPanel,
        'active-comment-popover':    ActiveCommentPopover,
        'guideline-section-preview': SectionPreview,
        'preview-bar':               PreviewBar,
    },
    data() {
        return {
            isPreviewBarActive: true,
            isLoadingData:      false,
            mode:               MODE_PREVIEW,
        }
    },
    computed: {
        ...mapState({
            activePrototypeId: state => state.prototypes.activePrototypeId,
            prototypes:        state => state.prototypes.prototypes,
            screens:           state => state.screens.screens,
            activeScreenId:    state => state.screens.activeScreenId,
            previewToken:      state => state.preview.previewToken,
        }),
        ...mapGetters({
            activePrototype:          'prototypes/activePrototype',
            getPrototype:             'prototypes/getPrototype',
            activeScreen:             'screens/activeScreen',
            getScreen:                'screens/getScreen',
            activeScreenOrderedIndex: 'screens/activeScreenOrderedIndex',
            getComment:               'comments/getComment',
        }),

        isInCommentsMode() {
            return this.mode === MODE_COMMENTS;
        },
        isInPreviewMode() {
            return this.mode === MODE_PREVIEW;
        },
        modeHelpTooltip() {
            if (this.isInCommentsMode) {
                return this.$t('Click to leave a comment');
            }

            return '';
        },
    },
    watch: {
        '$route.params.prototypeId': function (newVal, oldVal) {
            if (
                !newVal ||
                !this.getPrototype(newVal)
            ) {
                this.updateRoutePrototypeId();
            } else if (newVal != this.activePrototypeId) {
                this.setActivePrototypeId(newVal);
            }
        },
        '$route.params.screenId': function (newVal, oldVal) {
            if (newVal != this.activeScreenId) {
                this.setActiveScreenId(newVal);
            }
        },
        '$route.query.mode': function (newVal, oldVal) {
            if (newVal === MODE_COMMENTS) {
                this.setCommentsMode();
            } else if (newVal === MODE_PREVIEW) {
                this.setPreviewMode();
            } else if (this.$route.name === 'preview-prototype') { // keep-alive constraint
                this.updateRouteMode();
            }
        },
        activePrototypeId(newVal, oldVal) {
            this.init();
        },
        activeScreenId(newVal, oldVal) {
            this.updateRouteScreenId();
            this.deactivateComments();
        },
    },
    activated() {
        if (!this.isLoadingData && !this.screens.length) {
            this.$setDocumentTitle(() => this.$t('{projectTitle} prototypes', {projectTitle: this.project.title}));
        }
    },
    beforeMount() {
        if (this.$route.query.mode === MODE_COMMENTS) {
            this.setCommentsMode();
        } else {
            this.setPreviewMode();
        }

        this.init();
    },
    methods: {
        ...mapActions({
            setActivePrototypeId: 'prototypes/setActivePrototypeId',
            setScreens:           'screens/setScreens',
            setActiveScreenId:    'screens/setActiveScreenId',
            setHotspotTemplates:  'hotspots/setHotspotTemplates',
            appendHotspots:       'hotspots/appendHotspots',
            setHotspots:          'hotspots/setHotspots',
            addUnreadComment:     'notifications/addUnreadComment',
        }),

        init() {
            if (!this.activePrototypeId) {
                return;
            }

            this.loadPrototypeData(
                this.activePrototypeId,
                this.$route.params.screenId,
            );

            if (this.projectLink.allowComments) {
                this.loadComments(this.activePrototypeId, 1, true);
                this.startNewCommentsListener();
            }

            this.mentionsList = this.convertCollaboratorsListToMentionsList(this.collaborators);
        },
        loadPrototypeData(prototypeId, screenId) {
            prototypeId = prototypeId || this.activePrototypeId;
            screenId    = screenId    || this.activeScreenId;

            this.isLoadingData = true;

            ApiClient.Previews.getPrototype(
                this.previewToken,
                prototypeId
            ).then((response) => {
                let screensData          = CommonHelper.getNestedVal(response, 'data.screens', []);
                let hotspotTemplatesData = CommonHelper.getNestedVal(response, 'data.hotspotTemplates', []);

                // load screens
                this.setScreens(screensData);

                // preload screen images
                if (this.screens.length > 0) {
                    this.isLoadingData = true; // force showing the loader

                    let loadingPromises = [];
                    for (let i in this.screens) {
                        loadingPromises.push(CommonHelper.loadImage(this.screens[i].getImage()));
                    }

                    Promise.all(loadingPromises).finally(() => {
                        this.isLoadingData = false;
                    });
                }

                // load screen hotspots
                this.setHotspots([]);
                for (let i in screensData) {
                    if (screensData[i].hotspots) {
                        this.appendHotspots(screensData[i].hotspots);
                    }
                }

                // load hotspot templates
                this.setHotspotTemplates(hotspotTemplatesData);

                // load hotspot template hotspots
                for (let i in hotspotTemplatesData) {
                    if (hotspotTemplatesData[i].hotspots) {
                        this.appendHotspots(hotspotTemplatesData[i].hotspots);
                    }
                }

                if (screenId && this.getScreen(screenId)) {
                    this.setActiveScreenId(screenId); // manual set to prevent flickering
                }

                if (this.$refs.screensPanel) {
                    this.$refs.screensPanel.hide();
                }

                this.updateRoutePrototypeId();
                this.updateRouteScreenId();
                this.updateRouteMode();
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingData = false;
            });
        },
        updateRoutePrototypeId() {
            // keep-alive additional watcher constraint
            if (this.$route.name != 'preview-prototype') {
                return;
            }

            let routePrototypeId = this.$route.params.prototypeId;

            if (routePrototypeId != this.activePrototypeId) {
                this.$router[!routePrototypeId ? 'replace' : 'push']({
                    name:   this.$route.name,
                    params: Object.assign({}, this.$route.params, {
                        prototypeId: this.activePrototypeId,
                        screenId:    this.activeScreenId,
                    }),
                    query: Object.assign({}, this.$route.query),
                });
            }
        },
        updateRouteScreenId() {
            // keep-alive additional watcher constraint
            if (this.$route.name != 'preview-prototype') {
                return;
            }

            let routeScreenId = this.$route.params.screenId;

            if (routeScreenId != this.activeScreenId) {
                this.$router[!routeScreenId ? 'replace' : 'push']({
                    name:   this.$route.name,
                    params: Object.assign({}, this.$route.params, {
                        screenId: this.activeScreenId,
                    }),
                    query: Object.assign({}, this.$route.query),
                });
            }
        },
        updateRouteMode() {
            let routeMode = this.$route.query.mode;

            if (routeMode != this.mode) {
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
        setPreviewMode() {
            this.mode = MODE_PREVIEW;
        },

        onEscPress(e) {
            if (this.isInCommentsMode) {
                e.preventDefault();

                this.deactivateComments();
            }
        },
        onActiveScreenClick(e) {
            if (this.isInPreviewMode) {
                this.blinkPreviewModeHints();
            } else if (this.isInCommentsMode) {
                this.initCommentCreation(e, this.activeScreenId);
            }
        },

        // preview comments
        loadLatestPreviewComments(prototypeId, afterDateTime) {
            if (this.isLoadingComments) {
                return;
            }

            // set `afterDateTime` by default to the last load call
            if (typeof afterDateTime === 'undefined') {
                let lastLoad = ClientStorage.getItem(
                    (AppConfig.get('VUE_APP_PREVIEW_LAST_COMMENTS_LOAD_STORAGE_KEY') + this.projectLink.slug),
                    moment().format('X')
                ) << 0;

                afterDateTime = moment.utc(lastLoad - 1, 'X').format('YYYY-MM-DD HH:mm:ss');
            }

            ApiClient.enableAutoCancellation(false);
            ApiClient.Previews.getScreenCommentsList(this.previewToken, 1, 100, {
                'search[prototypeId]':    prototypeId,
                'search[afterCreatedAt]': afterDateTime,
            }).then((response) => {
                var comments           = ScreenComment.createInstances(response.data);
                var lastGuestFromEmail = ClientStorage.getItem(AppConfig.get('VUE_APP_PREVIEW_COMMENT_FROM_STORAGE_KEY'));

                for (let i in comments) {
                    // is not from the preview guest
                    if (comments[i].from != lastGuestFromEmail) {
                        this.addUnreadComment(comments[i]);
                    }
                }
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                ClientStorage.setItem(
                    (AppConfig.get('VUE_APP_PREVIEW_LAST_COMMENTS_LOAD_STORAGE_KEY') + this.projectLink.slug),
                    moment().format('X')
                );
            });
        },
        startNewCommentsListener() {
            if (
                // the current project link doesn't allow leaving comments
                !this.projectLink.allowComments ||
                // firestore is not configured
                !AppConfig.isFirestoreConfigured()
            ) {
                return;
            }

            import('firebase/app').then((firebase) => {
                import('firebase/firestore').then(() => {
                    if (!firebase.apps.length) {
                        // initialize Cloud Firestore through Firebase
                        firebase.initializeApp({ projectId: AppConfig.get('VUE_APP_FIRESTORE_PROJECT_ID') });
                    }

                    // unsubscribe from previous firestore subscription
                    this.stopNewCommentsListener();

                    var db = firebase.firestore();

                    firestoreUnsubscribe = db.collection(AppConfig.get('VUE_APP_FIRESTORE_COLLECTION'))
                        .doc('p' + this.project.id)
                        .onSnapshot((doc) => {
                            if (this.projectLink.allowComments) {
                                this.loadLatestPreviewComments(this.activePrototypeId);
                            }
                        });

                    this.$once('hook:deactivated', () => {
                        this.stopNewCommentsListener();
                    });
                });
            });
        },
        stopNewCommentsListener() {
            if (CommonHelper.isFunction(firestoreUnsubscribe)) {
                firestoreUnsubscribe();
                firestoreUnsubscribe = null;
            }
        },
    },
}
</script>
