<template>
    <dragger class="comment-pin"
        :class="{
            'new':       !comment.id,
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
        @click.native.prevent="activate()"
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
            isActive:     false,
            isProcessing: false,
        }
    },
    computed: {
        ...mapState({
            scaleFactor: state => state.screens.scaleFactor,
        }),
    },
    methods: {
        ...mapActions({
            removeComment: 'comments/removeComment',
        }),

        activate() {
            if (this.isActive) {
                return;
            }

            this.$emit('beforeActivate', this.comment, this.$el);

            this.isActive = true;

            this.$nextTick(() => {
                this.positionWithinView();
            });

            this.$emit('activated', this.comment, this.$el);
        },
        deactivate() {
            if (!this.isActive) {
                return;
            }

            this.isActive = false;

            this.$emit('beforeDeactivate', this.comment, this.$el);

            if (!this.comment.id) {
                this.removeComment(this.comment.id);
            }

            this.$emit('deactivated', this.comment, this.$el);
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

            if (!this.comment.id) {
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
