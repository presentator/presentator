<template>
    <dragger class="hotspot"
        :style="{
            'left':   (hotspot.left * scaleFactor) + 'px',
            'top':    (hotspot.top * scaleFactor) + 'px',
            'width':  (hotspot.width * scaleFactor) + 'px',
            'height': (hotspot.height * scaleFactor) + 'px',
        }"
        :class="{
            'new':       !hotspot.id,
            'highlight': hotspot.hotspotTemplateId > 0,
            'active':    isActive,
        }"
        wrapperSelector=".screen-inner-wrapper"
        @click.native.prevent="activate()"
        @dragging="onRepositioning"
        @dragStarted="onRepositionStart"
        @dragStopped="onRepositionStop"
    >
        <resizer class="resize-handle"
            ref="resizeHandle"
            wrapperSelector=".screen-inner-wrapper"
            containerSelector=".hotspot"
            :tolerance="0"
            @resizing="onRepositioning"
            @resizeStarted="onRepositionStart"
            @resizeStopped="onRepositionStop"
        ></resizer>
    </dragger>
</template>

<script>
import { mapState } from 'vuex';
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import Dragger      from '@/components/Dragger';
import Resizer      from '@/components/Resizer';
import Hotspot      from '@/models/Hotspot';

export default {
    name: 'hotspot-box',
    components: {
        'dragger': Dragger,
        'resizer': Resizer,
    },
    props: {
        hotspot: {
            type:     Hotspot,
            required: true,
        },
        snapToImage: {}
    },
    data() {
        return {
            isActive: false,
        }
    },
    computed: {
        ...mapState({
            scaleFactor: state => state.screens.scaleFactor,
        }),
    },
    methods: {
        activate() {
            if (this.isActive) {
                return;
            }

            this.$emit('beforeActivate', this.hotspot, this.$el);

            this.isActive = true;

            this.positionWithinView();

            this.$emit('activated', this.hotspot, this.$el);
        },
        deactivate() {
            if (!this.isActive) {
                return;
            }

            this.$emit('beforeDeactivate', this.hotspot, this.$el);

            this.isActive = false;

            if (!this.hotspot.id) {
                this.$emit('hotspotDeleted', this.hotspot, this.$el);
            }

            this.$emit('deactivated', this.hotspot, this.$el);
        },
        positionWithinView() {
            // ensures that the hotspot is visible
            this.$el.scrollIntoView({
                behavior: 'smooth',
                block:    'nearest',
            });
        },
        initResizing(e) {
            this.$refs.resizeHandle.dragInit(e);
            this.$refs.resizeHandle.onMove(e);
        },
        onRepositionStart(e) {
            this.$emit('repositionStarted', this.hotspot, this.$el);
        },
        onRepositioning(e) {
            this.$emit('repositioning', this.hotspot, this.$el);
        },
        onRepositionStop(e) {
            this.$set(this.hotspot, 'left', Math.max(this.$el.offsetLeft, 0) / this.scaleFactor);
            this.$set(this.hotspot, 'top', Math.max(this.$el.offsetTop, 0) / this.scaleFactor);
            this.$set(this.hotspot, 'width', this.$el.offsetWidth / this.scaleFactor);
            this.$set(this.hotspot, 'height', this.$el.offsetHeight / this.scaleFactor);

            // magnet snapping
            if (e.ctrlKey) {
                this.$nextTick(() => {
                    this.snap();
                });
            } else {
                this.savePosition();
            }

            this.$emit('repositionStopped', this.hotspot, this.$el);
        },
        snap(save = true) {
            if (this.snapToImage) {
                let closestEdge = CommonHelper.closestFeatureEdge(
                    this.snapToImage,
                    {
                        x: this.hotspot.left,
                        y: this.hotspot.top,
                        w: this.hotspot.width,
                        h: this.hotspot.height,
                    }
                );

                this.$set(this.hotspot, 'left', (closestEdge.x + this.hotspot.left));
                this.$set(this.hotspot, 'top', (closestEdge.y + this.hotspot.top));
                this.$set(this.hotspot, 'width', closestEdge.w);
                this.$set(this.hotspot, 'height', closestEdge.h);

                this.$emit('repositionStopped', this.hotspot, this.$el);
            }

            if (save) {
                this.savePosition();
            }
        },
        savePosition() {
            if (!this.hotspot.id) {
                return;
            }

            // optimistic update
            this.$emit('hotspotUpdated', this.hotspot, this.$el);

            // actual update
            ApiClient.Hotspots.update(this.hotspot.id, {
                'width':  this.hotspot.width,
                'height': this.hotspot.height,
                'left':   this.hotspot.left,
                'top':    this.hotspot.top,
            }).then((response) => {
                this.hotspot.load(response.data);
            });
        },
    },
}
</script>
