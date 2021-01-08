<template>
    <div class="popover-holder comment-popover-holder">
        <div v-show="isActive" class="popover-overlay" @click.stop.prevent="close()"></div>

        <div ref="popover"
            class="popover comment-popover"
            :class="{'active': isActive}"
        >
            <div v-if="!lastActiveComment.isNew" class="form-group resolved-checkbox">
                <input type="checkbox"
                    :id="'mark_as_resoved_' + lastActiveComment.id"
                    v-model="lastActiveComment.status"
                    true-value="resolved"
                    false-value="pending"
                    @change="saveCommentStatus()"
                >
                <label :for="'mark_as_resoved_' + lastActiveComment.id">{{ $t('Mark as resolved') }}</label>
            </div>

            <div v-if="isLoadingReplies" class="block txt-center txt-hint p-small">
                <span class="loader"></span>
            </div>

            <div v-if="!isLoadingReplies && !lastActiveComment.isNew" ref="commentsListContainer" class="comments-list">
                <div v-for="comment in commentsList"
                    :key="'reply_' + comment.id"
                    :class="{'primary': comment.isPrimary}"
                    class="comment-list-item"
                >
                    <figure class="avatar">
                        <img v-if="comment.user && comment.user.getAvatar('small')"
                            :src="comment.user.getAvatar('small')"
                            alt="User avatar"
                        >
                        <i v-else class="fe fe-user"></i>
                    </figure>
                    <div class="content">
                        <small class="content-header">
                            <span class="name">{{ comment.user ? comment.user.identifier : comment.from}}</span>
                            <span class="date txt-hint">{{ comment.createdAtFromNow }}</span>
                        </small>
                        <div class="message">{{ comment.message }}</div>
                    </div>
                    <div class="list-ctrls">
                        <div class="list-ctrl-item ctrl-danger"
                            @click.prevent="deleteComment(comment.id)"
                            v-tooltip.top="$t('Delete')"
                            v-if="!isForPreview"
                        >
                            <i class="fe fe-trash"></i>
                        </div>
                    </div>
                </div>
            </div>

            <form ref="messageForm" class="comment-message-form" @submit.prevent="">
                <form-field v-if="isForPreview" name="from" :showErrorMsg="true" class="email-field">
                    <div class="input-group">
                        <div class="input-addon p-l-small p-r-0 m-r-0">
                            <i class="fe fe-mail" v-tooltip.left="$t('From (email)')"></i>
                        </div>
                        <input type="email"
                            required
                            class="p-l-10"
                            v-model="from"
                            :placeholder="$t('Your email')"
                            @keydown.enter.prevent=""
                        >
                    </div>
                </form-field>

                <form-field name="message" :showErrorMsg="false" class="message-field">
                    <textarea ref="messageField"
                        required
                        v-model="message"
                        :placeholder="$t('Write a comment (@ to mention)')"
                        @keydown.ctrl.enter.exact.prevent="addComment()"
                    ></textarea>

                    <div class="ctrls-bar">
                        <button type="button" class="ctrl-item emoji-ctrl">
                            <i class="fe fe-smile"></i>
                            <toggler ref="emojisBar" class="emojis-bar">
                                <span class="emoji" title="+1" @click.prevent="insertEmoji('👍')">👍</span>
                                <span class="emoji" title="-1" @click.prevent="insertEmoji('👎')">👎</span>
                                <span class="emoji" title="ok" @click.prevent="insertEmoji('👌')">👌</span>
                                <span class="emoji" title="pray" @click.prevent="insertEmoji('🙏')">🙏</span>
                                <span class="emoji" title="smile" @click.prevent="insertEmoji('🙂')">🙂</span>
                                <span class="emoji" title="confused" @click.prevent="insertEmoji('😕')">😕</span>
                                <span class="emoji" title="thinking" @click.prevent="insertEmoji('🤔')">🤔</span>
                                <span class="emoji" title="joy" @click.prevent="insertEmoji('😂')">😂</span>
                                <span class="emoji" title="hooray" @click.prevent="insertEmoji('🎉')">🎉</span>
                                <span class="emoji" title="watching" @click.prevent="insertEmoji('👀')">👀</span>
                                <span class="emoji" title="love" @click.prevent="insertEmoji('❤️')">❤️</span>
                                <span class="emoji" title="poop" @click.prevent="insertEmoji('💩')">💩</span>
                            </toggler>
                        </button>

                        <button ref="messageBtn"
                            type="button"
                            class="ctrl-item submit-ctrl"
                            v-tooltip.bottom="$t('Add comment ({shortcut})', {shortcut: 'Ctrl+Enter'})"
                            @click.prevent="addComment()"
                            @keydown.ctrl.enter.exact.prevent="addComment()"
                        >
                            <span v-if="isProcessing" class="loader"></span>
                            <i v-else class="fe fe-send"></i>
                        </button>
                    </div>

                    <mentions-list ref="mentionsList" :list="mentionsList" class="dropdown-compact input-dropdown"></mentions-list>
                </form-field>
            </form>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters }  from 'vuex';
import AppConfig     from '@/utils/AppConfig';
import ClientStorage from '@/utils/ClientStorage';
import CommonHelper  from '@/utils/CommonHelper';
import ApiClient     from '@/utils/ApiClient';
import ScreenComment from '@/models/ScreenComment';
import MentionsList  from '@/components/MentionsList';

export default {
    name: 'active-comment-popover',
    components: {
        'mentions-list': MentionsList,
    },
    props: {
        mentionsList: {
            type: Array,
            default() {
                return [];
            }
        },
        isForPreview: {
            type:    Boolean,
            default: true,
        },
    },
    data() {
        return {
            isProcessing:      false,
            isLoadingReplies:  false,
            lastActiveComment: new ScreenComment,
            replies:           [],
            // form fields
            message: '',
            from:    '',
        }
    },
    computed: {
        ...mapState({
            previewToken:    state => state.preview.previewToken,
            activeCommentId: state => state.comments.activeCommentId,
        }),
        ...mapGetters({
            activeComment:               'comments/activeComment',
            getUnreadComment:            'notifications/getUnreadComment',
            getUnreadCommentsForComment: 'notifications/getUnreadCommentsForComment',
        }),

        isActive() {
            return this.activeComment != null;
        },
        commentsList() {
            var result = this.replies.slice();

            if (this.lastActiveComment && !this.lastActiveComment.isNew) {
                result.unshift(this.lastActiveComment);
            }

            result.sort((a, b) => (Date.parse(a['createdAt']) - Date.parse(b['createdAt'])));

            return result;
        },
        unreadCommentReplies() {
            return this.getUnreadCommentsForComment(this.activeCommentId);
        },
    },
    watch: {
        commentsList(newVal, oldVal) {
            this.$nextTick(() => {
                // slightly delay to ensure that the popover position is
                // calculated correctly on initial rendering and/or animations
                setTimeout(() => {
                    this.reposition();
                }, 100);
            });
        },
        unreadCommentReplies(newVal, oldVal) {
            if (newVal) {
                for (let i in newVal) {
                    CommonHelper.pushUnique(this.replies, newVal[i]);
                }

                if (newVal.length && this.isActive) {
                    this.$nextTick(() => {
                        this.scrollToLastComment();
                        this.readComments(this.commentsList.slice(), 500);
                    });
                }
            }
        },
        activeCommentId(newVal, oldVal) {
            if (this.activeComment) {
                this.open();
            } else {
                this.close();
            }
        },
    },
    mounted() {
        document.addEventListener('scroll', this.onEventPopoverReposition, {
            capture: true,
            passive: true,
        });

        window.addEventListener('resize', this.onEventPopoverReposition, {
            passive: true,
        });

        this.from = ClientStorage.getItem(AppConfig.get('VUE_APP_PREVIEW_COMMENT_FROM_STORAGE_KEY')) || CommonHelper.getNestedVal(this.$store, 'state.user.user.email', '');
    },
    beforeDestroy() {
        document.removeEventListener('scroll', this.onEventPopoverReposition, {
            capture: true,
        });

        window.removeEventListener('resize', this.onEventPopoverReposition);
    },
    methods: {
        ...mapActions({
            setActiveCommentId:  'comments/setActiveCommentId',
            removeComment:       'comments/removeComment',
            removeUnreadComment: 'notifications/removeUnreadComment',
            markAsRead:          'notifications/markAsRead',
        }),

        initForm(clearMessage = false) {
            if (clearMessage) {
                this.message = '';
            }

            if (this.$refs.messageBtn) {
                this.$refs.messageBtn.blur();
            }
        },
        open() {
            this.lastActiveComment = this.activeComment;

            this.replies = [];

            this.initForm();

            this.loadReplies();

            this.$nextTick(() => this.reposition());

            if (this.lastActiveComment.isNew && this.$refs.messageField) {
                setTimeout(() => {
                    if (this.$refs.messageField) {
                        this.$refs.messageField.focus();
                    }
                }, 100); // popover animation delay
            }
        },
        close() {
            this.setActiveCommentId(null);

            if (this.lastActiveComment.isNew) {
                this.removeComment(this.lastActiveComment.id);
            }

            if (this.$refs.mentionsList) {
                this.$refs.mentionsList.hide();
            }

            if (this.$refs.emojisBar) {
                this.$refs.emojisBar.hide();
            }
        },
        onEventPopoverReposition(e) {
            if (this.isActive) {
                this.reposition();
            }
        },
        reposition(repositionToElem) {
            repositionToElem = repositionToElem || document.querySelector('.comment-pin[data-id="' + this.lastActiveComment.id + '"]');
            if (!this.isActive || !repositionToElem) {
                return;
            }

            var container = document.querySelector('.preview-container') || document.documentElement;
            var popover   = this.$refs.popover;
            var elPos     = repositionToElem.getBoundingClientRect();
            var tolerance = 5;
            var top       = elPos.top - tolerance;
            var left      = elPos.left + elPos.width + tolerance;

            // reset popover position
            popover.style.left = '0px';
            popover.style.top  = '0px';

            // right screen edge constraint
            if (left + popover.offsetWidth > container.clientWidth) {
                left = elPos.left - popover.offsetWidth - tolerance;
            }

            // left screen edge constraint
            left = left >= 0 ? left : 0;

            // bottom screen edge constraint
            if (top + popover.offsetHeight > container.clientHeight) {
                top = container.clientHeight - popover.offsetHeight;
            }

            // top screen edge constraint
            top = top >= 0 ? top : 0;

            // set new popover position
            popover.style.left = left + 'px';
            popover.style.top  = top + 'px';
        },

        // ---

        loadReplies() {
            if (this.isLoadingReplies || this.lastActiveComment.isNew) {
                return;
            }

            this.isLoadingReplies = true;

            var request;

            ApiClient.enableAutoCancellation(false);

            if (this.isForPreview) {
                request = ApiClient.Previews.getScreenCommentsList(this.previewToken, 1, 199, {
                    'search[screenId]': this.lastActiveComment.screenId,
                    'search[replyTo]':  this.lastActiveComment.id,
                });
            } else {
                request = ApiClient.ScreenComments.getList(1, 199, {
                    'search[screenId]': this.lastActiveComment.screenId,
                    'search[replyTo]':  this.lastActiveComment.id,
                });
            }

            request.then((response) => {
                this.replies = ScreenComment.createInstances(response.data);

                this.readComments(this.commentsList.slice());
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingReplies = false;

                this.$nextTick(() => {
                    this.scrollToLastComment();
                });
            });
        },
        deleteComment(commentId) {
            if (this.isForPreview) {
                return;
            }

            var comment = CommonHelper.findByKey(this.commentsList, 'id', commentId);
            if (!comment) {
                return;
            }

            var deleteMsg = '';
            if (comment.isPrimary) {
                deleteMsg = this.$t('Do you really want to deleted the selected comment and all its replies?');
            } else {
                deleteMsg = this.$t('Do you really want to deleted the selected comment?');
            }

            if (!window.confirm(deleteMsg)) {
                return;
            }

            // optimistic delete
            this.$toast(this.$t('Successfully deleted comment.'));
            CommonHelper.removeByKey(this.replies, 'id', commentId); // remove from the replies list (if reply)
            this.removeComment(commentId);

            // close popover on primary comment deletion
            if (this.lastActiveComment.id == commentId) {
                this.close();
            }

            // actual delete
            ApiClient.ScreenComments.delete(commentId);
        },
        addComment() {
            if (this.isProcessing || !this.message) {
                // trigger browser's form validations
                if (
                    this.$refs.messageForm &&
                    CommonHelper.isFunction(this.$refs.messageForm.reportValidity)
                ) {
                    this.$refs.messageForm.reportValidity();
                }

                return;
            }

            this.isProcessing = true;

            var request;
            var replyTo = !this.lastActiveComment.isNew ? this.lastActiveComment.id : null;

            if (this.isForPreview) {
                request = ApiClient.Previews.createScreenComment(this.previewToken, {
                    replyTo:  replyTo,
                    screenId: this.lastActiveComment.screenId,
                    left:     this.lastActiveComment.left,
                    top:      this.lastActiveComment.top,
                    message:  this.message,
                    from:     this.from,
                });
            } else {
                request = ApiClient.ScreenComments.create({
                    replyTo:  replyTo,
                    screenId: this.lastActiveComment.screenId,
                    left:     this.lastActiveComment.left,
                    top:      this.lastActiveComment.top,
                    message:  this.message,
                });
            }

            request.then((response) => {
                ClientStorage.setItem(AppConfig.get('VUE_APP_PREVIEW_COMMENT_FROM_STORAGE_KEY'), this.from);

                this.initForm(true);

                this.$toast(this.$t('Successfully added new comment.'));

                var comment = replyTo ? new ScreenComment() : this.lastActiveComment;

                comment.load(response.data);

                if (comment.isReply) {
                    this.replies.push(comment);

                    this.$nextTick(() => {
                        this.scrollToLastComment();
                    });
                } else {
                    this.close();
                }

                this.$emit('commentCreated', comment);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
        saveCommentStatus() {
            if (this.isProcessing || this.lastActiveComment.isNew) {
                return;
            }

            this.isProcessing = true;

            var request;

            if (this.isForPreview) {
                request = ApiClient.Previews.updateScreenComment(this.previewToken, this.lastActiveComment.id, {
                    'status': this.lastActiveComment.status,
                });
            } else {
                request = ApiClient.ScreenComments.update(this.lastActiveComment.id, {
                    'status': this.lastActiveComment.status,
                });
            }

            request.then((response) => {
                this.lastActiveComment.load(response.data);

                this.$toast(this.$t('Successfully updated comment state.'));

                if (this.lastActiveComment.isResolved) {
                    this.close();
                }

                this.$emit('commentUpdated', this.lastActiveComment);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
        scrollToLastComment() {
            if (!this.$refs.popover) {
                return;
            }

            this.$refs.popover.scrollTo({
                'behavior': 'smooth',
                'top':      this.$refs.popover.scrollHeight,
            });
        },
        readComments(comments, delay = 0) {
            setTimeout(() => {
                for (let i = comments.length - 1; i >= 0; i--) {
                    if (this.getUnreadComment(comments[i].id)) {
                        if (this.previewToken) {
                            this.removeUnreadComment(comments[i].id); // remove from store
                        } else {
                            this.markAsRead(comments[i].id); // remove from store + send user rel update request
                        }
                    }
                }
            }, delay);
        },
        insertEmoji(emoji) {
            if (!CommonHelper.isEmpty(this.message)) {
                this.message = (this.message.trim() + ' ');
            }

            this.message += (emoji + ' ');

            this.$nextTick(() => {
                if (this.$refs.messageField) {
                    this.$refs.messageField.focus();
                }
            });
        },
    },
}
</script>
