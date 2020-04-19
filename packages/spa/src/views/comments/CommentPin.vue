<template>
    <dragger class="comment-pin"
        :class="{
            'new':       comment.isNew,
            'completed': comment.isResolved,
            'active':    isActive,
            'loader':    isProcessing,
        }"
        :style="{
            left: (comment.left * scaleFactor) + 'px',
            top: (comment.top * scaleFactor) + 'px',
        }"
        :data-id="comment.id"
        :enable="allowPositionChange"
        wrapperSelector=".screen-inner-wrapper"
        @click.native.prevent="setActiveCommentId(comment.id)"
        @dragging="onRepositioning()"
        @dragStopped="updatePinPosition()"
    >
    </dragger>
</template>

<script>
import { mapState, mapActions }  from 'vuex';
import ApiClient     from '@/utils/ApiClient';
import Dragger       from '@/components/Dragger';
import ScreenComment from '@/models/ScreenComment';

export default {
    name: 'comment-pin',
    components: {
        'dragger': Dragger,
    },
    props: {
        allowPositionChange: {
            type:    Boolean,
            default: false,
        },
        comment: {
            type:     ScreenComment,
            required: true,
        },
    },
    data() {
        return {
            isProcessing: false,
        }
    },
    watch: {
        activeCommentId(newVal, oldVal) {
            if (this.comment.id == newVal) {
                this.onActivate();
            } else {
                this.onDeactivate();
            }
        },
    },
    computed: {
        ...mapState({
            scaleFactor:     state => state.screens.scaleFactor,
            activeCommentId: state => state.comments.activeCommentId,
        }),

        isActive() {
            return this.comment.id == this.activeCommentId;
        },
    },
    methods: {
        ...mapActions({
            removeComment:      'comments/removeComment',
            setActiveCommentId: 'comments/setActiveCommentId',
        }),

        onActivate() {
            this.$nextTick(() => {
                this.positionWithinView();
            });
        },
        onDeactivate() {
            if (this.comment.isNew) {
                this.removeComment(this.comment.id);
            }
        },

        // ensures that the comment pin is in visible viewport
        positionWithinView() {
            this.$el.scrollIntoView({
                behavior: 'smooth',
                block:    'nearest',
            });
        },
        updatePinPosition() {
            this.comment.left = this.$el.offsetLeft / this.scaleFactor;
            this.comment.top  = this.$el.offsetTop / this.scaleFactor;

            if (this.comment.isNew) {
                return;
            }

            this.isProcessing = true;

            ApiClient.ScreenComments.update(this.comment.id, {
                left: this.comment.left,
                top:  this.comment.top,
            }).then((response) => {
                this.comment.load(response.data);

                this.$emit('commentUpdated', this.comment, this.$el);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
        onRepositioning() {
            this.$emit('repositioning', this.comment, this.$el);
        },
    },
}
</script>
