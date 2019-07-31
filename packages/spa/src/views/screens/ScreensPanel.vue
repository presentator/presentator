<template>
    <transition name="previewPanel">
        <div v-if="isActive && screens.length"
            class="preview-screens-panel"
            @wheel.passive.capture="verticalToHorizontalScroll"
        >
            <div class="thumbs-list">
                <figure v-for="screen in orderedScreens"
                    class="thumb thumb-handle screen-thumb"
                    :class="{'active': activeScreenId == screen.id}"
                    :key="'thumb_' + screen.id"
                    v-tooltip.top="screen.title"
                    @click.prevent="setActiveScreenId(screen.id)"
                >
                    <img v-if="screen.getImage('small')"
                        :src="screen.getImage('small')"
                        :alt="screen.title"
                        class="img"
                    >
                    <i v-else class="fe fe-image img"></i>
                </figure>
            </div>
        </div>
    </transition>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';

export default {
    name: 'screens-panel',
    data() {
        return {
            isActive: false,
        }
    },
    computed: {
        ...mapState({
            screens:        state => state.screens.screens,
            activeScreenId: state => state.screens.activeScreenId,
        }),
        ...mapGetters({
            orderedScreens: 'screens/orderedScreens',
        }),
    },
    watch: {
        activeScreenId(newVal, oldVal) {
            if (newVal !== oldVal && this.isActive) {
                this.scrollToActiveScreenThumb();
            }
        },
    },
    methods: {
        ...mapActions({
            setActiveScreenId: 'screens/setActiveScreenId',
        }),

        show() {
            this.isActive = true;

            this.scrollToActiveScreenThumb();
        },
        hide() {
            this.isActive = false;
        },
        toggle() {
            if (this.isActive) {
                this.hide();
            } else {
                this.show();
            }
        },
        scrollToActiveScreenThumb() {
            this.$nextTick(() => {
                if (!this.isActive || !this.$el) {
                    return;
                }

                var tolerance   = 15;
                var container   = this.$el;
                var activeThumb = container ? container.querySelector('.screen-thumb.active') : null;

                if (!activeThumb) {
                    return;
                }

                var thumbLeftEdge  = activeThumb.offsetLeft;
                var thumbRightEdge = thumbLeftEdge + activeThumb.offsetWidth;
                var panelLeftEdge  = container.scrollLeft;
                var panelRightEdge = panelLeftEdge + container.offsetWidth;

                if ((thumbLeftEdge - tolerance) < panelLeftEdge) {
                    container.scrollLeft = thumbLeftEdge - tolerance;
                } else if ((thumbRightEdge + tolerance) > panelRightEdge) {
                    container.scrollLeft = panelLeftEdge - (panelRightEdge - (thumbRightEdge + tolerance));
                }
            });
        },
        verticalToHorizontalScroll(e) {
            if (e.type != 'wheel' || !this.$el) {
                return;
            }

            var delta = ((e.deltaY || -e.wheelDelta || e.detail) >> 10) || 1;
            delta = delta * (-300);

            this.$el.scrollLeft -= delta;
        },
    },
}
</script>
