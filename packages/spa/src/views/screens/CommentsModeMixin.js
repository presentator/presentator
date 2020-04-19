import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient     from '@/utils/ApiClient';
import CommonHelper  from '@/utils/CommonHelper';
import ScreenComment from '@/models/ScreenComment';

export default {
    data() {
        return {
            isLoadingComments: false,
            showCommentsPanel: false,
            mentionsList:      [],
        }
    },
    computed: {
        ...mapState({
            activePrototypeId:    state => state.prototypes.activePrototypeId,
            activeScreenId:       state => state.screens.activeScreenId,
            scaleFactor:          state => state.screens.scaleFactor,
            previewToken:         state => state.preview.previewToken,
            showResolvedComments: state => state.comments.showResolvedComments,
            activeCommentId:      state => state.comments.activeCommentId,
            unreadComments:       state => state.notifications.unreadComments,
        }),
        ...mapGetters({
            getCommentsForScreen:       'comments/getCommentsForScreen',
            activeComment:              'comments/activeComment',
            getUnreadCommentsForScreen: 'notifications/getUnreadCommentsForScreen',
            isCommentUnread:            'notifications/isCommentUnread',
        }),

        activeScreenComments() {
            return this.getCommentsForScreen(this.activeScreenId);
        },
        totalActiveScreenComments() {
            return this.activeScreenComments.length;
        },
        activeScreenResolvedComments() {
            return this.getCommentsForScreen(this.activeScreenId, 'resolved');
        },
        totalActiveScreenResolvedComments() {
            return this.activeScreenResolvedComments.length;
        },
        activeUnreadComments() {
            return this.getUnreadCommentsForScreen(this.activeScreenId);
        },
    },
    watch: {
        activeUnreadComments(newVal, oldVal) {
            // adds unread primary comments to the comment pins list
            for (let i in newVal) {
                if (!newVal[i].replyTo) {
                    this.addComment(newVal[i]);
                }
            }
        },
        activeCommentId(newVal, oldVal) {
            this.updateQueryCommentIdParam();

            if (this.activeComment && this.activeComment.isResolved) {
                this.setShowResolvedComments(true);
            }
        },
    },
    methods: {
        ...mapActions({
            setComments:             'comments/setComments',
            addComment:              'comments/addComment',
            appendComments:          'comments/appendComments',
            setShowResolvedComments: 'comments/setShowResolvedComments',
            setActiveCommentId:      'comments/setActiveCommentId',
        }),

        convertCollaboratorsListToMentionsList(collaborators, excludeEmails = []) {
            collaborators = collaborators || [];

            var result = [];

            for (let i = collaborators.length - 1; i >= 0; i--) {
                if (excludeEmails.indexOf(collaborators[i].email) >= 0) {
                    continue;
                }

                let name = ((collaborators[i].firstName || '') + (collaborators[i].lastName || '')).trim();

                result.push({
                    'value': collaborators[i].email,
                    'label': name ? (name + ' (' + collaborators[i].email +')') : collaborators[i].email,
                });
            }

            return result;
        },
        loadComments(prototypeId, page = 1, forPreview = false) {
            prototypeId = prototypeId || this.activePrototypeId;

            if (!prototypeId || this.isLoadingComments) {
                return;
            }

            this.isLoadingComments = true;

            var request = null;

            ApiClient.enableAutoCancellation(false);

            if (forPreview) {
                request = ApiClient.Previews.getScreenCommentsList(this.previewToken, page, 100, {
                    'envelope':            true,
                    'search[prototypeId]': prototypeId,
                    'search[replyTo]':     0, // only primary
                })
            } else {
                request = ApiClient.ScreenComments.getList(page, 100, {
                    'envelope':            true,
                    'search[prototypeId]': prototypeId,
                    'search[replyTo]':     0, // only primary
                });
            }

            request.finally(() => {
                this.isLoadingComments = false;
            }).then((response) => {
                var commentsData = CommonHelper.getNestedVal(response, 'data.response', []);
                var currentPage  = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-current-page', 1);
                var totalPages   = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-page-count', 1);

                if (page == 1) {
                    this.setComments(commentsData);
                } else {
                    this.appendComments(commentsData);
                }

                // load next portion of hotspots (if there are more)
                if (totalPages > currentPage) {
                    this.loadComments(prototypeId, page + 1, forPreview);
                }

                if (this.$route.query.commentId) {
                    setTimeout(() => {
                        this.setActiveCommentId(this.$route.query.commentId);
                    }, 300); // animation delay
                }
            }).catch((err) => {
                this.$errResponseHandler(err);
            });
        },
        initCommentCreation(e, screenId) {
            if (!this.isInCommentsMode) {
                return;
            }

            this.deactivateComments();

            var comment = new ScreenComment({
                screenId: (screenId || this.activeScreenId),
                left:     this.scaleFactor > 0 ? (e.offsetX / this.scaleFactor) : e.offsetX,
                top:      this.scaleFactor > 0 ? (e.offsetY / this.scaleFactor) : e.offsetY,
            });

            this.addComment(comment);

            this.setActiveCommentId(comment.id);
        },
        deactivateComments() {
            this.setActiveCommentId(null);
        },
        updateQueryCommentIdParam() {
            var query = Object.assign({}, this.$route.query);

            if (this.activeComment && !this.activeComment.isNew) {
                query.commentId = this.activeComment.id;
            } else {
                delete(query.commentId);
            }

            this.$router.replace({
                name: this.$route.name,
                params: Object.assign({}, this.$route.params),
                query: query,
            });
        },
        onCommentRepositioning(comment, elem) {
            if (this.$refs.commentPopover) {
                this.$refs.commentPopover.reposition(elem);
            }
        },
    },
}
