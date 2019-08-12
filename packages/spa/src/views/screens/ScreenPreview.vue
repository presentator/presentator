<template>
    <div v-if="activeScreen"
        class="preview-screen-holder" :class="[
            (activePrototype.isForDesktop ? 'desktop' : 'mobile'),
            ('align-' + activeScreen.alignment),
            (inTransition ? 'transitioning' : ''),
            (overlayScreen ? 'overlay-active' : ''),
            ('transition-' + activeTransition),
        ]"
        v-shortcut.37="goToPrevScreen"
        v-shortcut.39="goToNextScreen"
    >
        <template v-if="screens.length > 1">
            <nav class="preview-nav nav-left"
                :class="{'nav-disabled': activeScreenOrderedIndex == 0}"
                :title="$t('Prev screen')"
                @click.prevent="goToPrevScreen()"
            >
                <i class="fe fe-chevron-left"></i>
            </nav>

            <nav class="preview-nav nav-right"
                :class="{'nav-disabled': orderedScreens.length == activeScreenOrderedIndex+1}"
                :title="$t('Next screen')"
                @click.prevent="goToNextScreen()"
            >
                <i class="fe fe-chevron-right"></i>
            </nav>
        </template>

        <div ref="activeScreenWrapper"
            tabindex="-1"
            class="screen-wrapper"
            :style="{
                'width':  (activePrototype.width ? activePrototype.width+'px' : null),
                'height': (activePrototype.height ? activePrototype.height+'px' : null),
                'background': (activeScreen.background || null),
            }"
            @scroll.capture.passive="fixedOverlayReposition"
        >
            <!-- fixed screen footer -->
            <div v-if="interactions && activeScreen.fixedHeader > 0 && !inTransition"
                class="fixed-screen-header"
                :style="{
                    'height':     (activeScreen.fixedHeader * scaleFactor) + 'px',
                    'margin-top': (-activeScreen.fixedHeader * scaleFactor) + 'px',
                }"
            >
                <div class="fixed-screen-overflow-wrapper">
                    <div class="fixed-screen-content-wrapper">
                        <img class="fixed-screen"
                            :src="activeScreen.getImage()"
                            :alt="activeScreen.title"
                            v-scale="activePrototype.scaleFactor"
                        >
                        <div class="block preview-hotspots-block">
                            <div v-for="hotspot in activeScreenHotspots"
                                v-if="hotspot.top < activeScreen.fixedHeader"
                                :key="'fixed_header_hotspot_' + hotspot.id"
                                class="hotspot"
                                :style="{
                                    'left':   (hotspot.left * scaleFactor) + 'px',
                                    'top':    (hotspot.top * scaleFactor) + 'px',
                                    'width':  (hotspot.width * scaleFactor) + 'px',
                                    'height': (hotspot.height * scaleFactor) + 'px',
                                }"
                                @click.prevent="hotspotNavigate(hotspot.id)"
                            >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- screen container -->
            <div class="screen-inner-wrapper"
                :class="{'position-static': overlayScreen && isOverlayScreenFixed}"
            >
                <img class="screen old-active-screen"
                    :src="oldActiveScreen ? oldActiveScreen.getImage() : ''"
                    :alt="oldActiveScreen ? oldActiveScreen.title : ''"
                    :style="{
                        'background': (oldActiveScreen ? oldActiveScreen.background : null),
                    }"
                    v-show="oldActiveScreen && inTransition"
                    v-scale="activePrototype.scaleFactor"
                >

                <img ref="activeScreen"
                    :key="'active_screen_' + activeScreen.id"
                    crossorigin="anonymous"
                    draggable="false"
                    class="screen"
                    :class="inTransition ? 'new-active-screen' : 'current-active-screen'"
                    :src="activeScreen.getImage()"
                    :alt="activeScreen.title"
                    v-tooltip.follow="activeScreenTooltip"
                    v-scale="activePrototype.scaleFactor"
                    @load="refreshActiveScreenWrapperAlignment()"
                    @mousedown.left.stop.prevent="$emit('activeScreenMousedown', $event)"
                    @click.left.stop.prevent="$emit('activeScreenClick', $event)"
                    @click="onOverlayOutsideClick"
                >

                <!-- overlay screen container -->
                <div v-if="interactions && overlayScreen"
                    ref="overlayContainer"
                    key="overlayContainer"
                    class="overlay-container"
                    :class="isOverlayScreenFixed ? 'fixed' : 'relative'"
                >
                    <div
                        class="overlay-screen-wrapper"
                        :class="[
                            ('transition-' + overlayScreenTransition),
                            ('position-' + overlayScreenPosition),
                            (isOverlayScreenClosing ? 'closing' : ''),
                        ]"
                        :style="{
                            'margin-top': (overlayScreenOffsetTop * scaleFactor) + 'px',
                            'margin-bottom': (overlayScreenOffsetBottom * scaleFactor) + 'px',
                            'margin-left': (overlayScreenOffsetLeft * scaleFactor) + 'px',
                            'margin-right': (overlayScreenOffsetRight * scaleFactor) + 'px',
                        }"
                    >
                        <img class="screen overlay-screen"
                            :src="overlayScreen.getImage()"
                            :alt="overlayScreen.title"
                            v-scale="activePrototype.scaleFactor"
                        >

                        <div class="block preview-hotspots-block" key="overlay-screen-hotspots-block">
                            <div v-for="hotspot in overlayScreenHotspots"
                                :key="'overlay_screen_hotspot_' + hotspot.id"
                                class="hotspot"
                                :style="{
                                    'left':   (hotspot.left * scaleFactor) + 'px',
                                    'top':    (hotspot.top * scaleFactor) + 'px',
                                    'width':  (hotspot.width * scaleFactor) + 'px',
                                    'height': (hotspot.height * scaleFactor) + 'px',
                                }"
                                @click.prevent="hotspotNavigate(hotspot.id)"
                            >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- hotspots -->
                <div v-if="interactions" class="block preview-hotspots-block" key="active-screen-hotspots-block">
                    <div v-for="hotspot in activeScreenHotspots"
                        :key="'active_screen_hotspot_' + hotspot.id"
                        class="hotspot"
                        :style="{
                            'left':   (hotspot.left * scaleFactor) + 'px',
                            'top':    (hotspot.top * scaleFactor) + 'px',
                            'width':  (hotspot.width * scaleFactor) + 'px',
                            'height': (hotspot.height * scaleFactor) + 'px',
                        }"
                        @click.prevent="hotspotNavigate(hotspot.id)"
                    >
                    </div>
                </div>

                <!-- custom slot -->
                <slot></slot>
            </div>

            <!-- fixed screen footer -->
            <div v-if="interactions && activeScreen.fixedFooter > 0 && !inTransition"
                class="fixed-screen-footer"
                :style="{
                    'height':     (activeScreen.fixedFooter * scaleFactor) + 'px',
                    'margin-top': (-activeScreen.fixedFooter * scaleFactor) + 'px',
                }"
            >
                <div class="fixed-screen-overflow-wrapper">
                    <div class="fixed-screen-content-wrapper">
                        <img class="fixed-screen"
                            :src="activeScreen.getImage()"
                            :alt="activeScreen.title"
                            v-scale="activePrototype.scaleFactor"
                        >
                        <div class="block preview-hotspots-block">
                            <div v-for="hotspot in activeScreenHotspots"
                                :key="'fixed_footer_hotspot_' + hotspot.id"
                                class="hotspot"
                                :style="{
                                    'left':   (hotspot.left * scaleFactor) + 'px',
                                    'top':    (hotspot.top * scaleFactor) + 'px',
                                    'width':  (hotspot.width * scaleFactor) + 'px',
                                    'height': (hotspot.height * scaleFactor) + 'px',
                                }"
                                @click.prevent="hotspotNavigate(hotspot.id)"
                            >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import CommonHelper from '@/utils/CommonHelper';

// @todo consider getting it from css
const SCREENS_ANIMATION_SPEED = 400; // in ms

export default {
    name: 'screen-preview',
    props: {
        interactions: {
            type:    Boolean,
            default: true,
        },
        activeScreenTooltip: {
            type:    String,
            default: '',
        },
    },
    data() {
        return {
            oldActiveScreenId:    null,
            activeTransition:     'none',
            inTransition:         false,

            // overlay props
            overlayScreenId:           null,
            overlayScreenPosition:     'centered',
            overlayScreenTransition:   'none',
            overlayScreenOffsetTop:    0,
            overlayScreenOffsetBottom: 0,
            overlayScreenOffsetLeft:   0,
            overlayScreenOffsetRight:  0,
            overlayScreenOutsideClose: true,
            isOverlayScreenFixed:      false,
            isOverlayScreenClosing:    false,
        }
    },
    computed: {
        ...mapState({
            activeScreenId:   state => state.screens.activeScreenId,
            screens:          state => state.screens.screens,
            scaleFactor:      state => state.screens.scaleFactor,
            hotspots:         state => state.hotspots.hotspots,
            hotspotTemplates: state => state.hotspots.hotspotTemplates,
        }),
        ...mapGetters({
            activePrototype:          'prototypes/activePrototype',
            getScreen:                'screens/getScreen',
            activeScreen:             'screens/activeScreen',
            orderedScreens:           'screens/orderedScreens',
            activeScreenOrderedIndex: 'screens/activeScreenOrderedIndex',
            getHotspot:               'hotspots/getHotspot',
            getHotspotTemplate:       'hotspots/getHotspotTemplate',
            getHotspotsForScreen:     'hotspots/getHotspotsForScreen',
        }),

        oldActiveScreen() {
            return this.getScreen(this.oldActiveScreenId);
        },
        overlayScreen() {
            return this.getScreen(this.overlayScreenId);
        },

        // hotspots
        activeScreenHotspots() {
            return this.getHotspotsForScreen(this.activeScreenId);
        },
        overlayScreenHotspots() {
            return this.getHotspotsForScreen(this.overlayScreenId);
        },
    },
    watch: {
        interactions(newVal, oldVal) {
            this.closeOverlayScreen(); // reset
        },
        activeScreenId(newVal, oldVal) {
            // ensure that old screen id is set when `setActiveScreenId()` is used instead of `changeActiveScreen()`
            this.oldActiveScreenId = oldVal;

            this.onActiveScreenChange();
        },
        'activeScreen.alignment': function (newVal, oldVal) {
            if (newVal !== oldVal) {
                this.refreshActiveScreenWrapperAlignment();
            }
        },
    },
    mounted() {
        this.onActiveScreenChange();
    },
    methods: {
        ...mapActions({
            setActiveScreenId: 'screens/setActiveScreenId',
            setScreens:        'screens/setScreens',
            setScaleFactor:    'screens/setScaleFactor',
        }),

        changeActiveScreen(id, transition, forceChange) {
            var screen = this.getScreen(id);

            if (
                !screen ||
                (!forceChange && id === this.activeScreenId)
            ) {
                return;
            }

            this.$emit('beforeActiveScreenChange', this.activeScreenId, id);

            this.inTransition      = false;
            this.activeTransition  = transition || 'none';
            this.oldActiveScreenId = this.activeScreenId;

            // delay execution queue to prevent transition flickering
            // due to oldActiveScreen dom rendering
            if (this.screenChangeTimeoutId) {
                clearTimeout(this.screenChangeTimeoutId);
            }
            this.screenChangeTimeoutId = setTimeout(() => {
                this.setActiveScreenId(id);

                if (this.activeTransition !== 'none') {
                    this.inTransition = true;

                    if (this.screenChangeAnimationTimeoutId) {
                        clearTimeout(this.screenChangeAnimationTimeoutId);
                    }

                    this.screenChangeAnimationTimeoutId = setTimeout(() => {
                        this.activeTransition = 'none';
                        this.inTransition     = false;
                    }, SCREENS_ANIMATION_SPEED);
                }

                this.$emit('afterActiveScreenChange', this.oldActiveScreenId, this.activeScreenId);
            }, this.activeTransition !== 'none' ? 100 : 0);
        },
        onActiveScreenChange() {
            if (!this.activeScreen) {
                return;
            }

            // set scale factor
            if (this.activePrototype.scaleFactor != 0) { // fixed scale
                this.setScaleFactor(this.activePrototype.scaleFactor);
            } else { // auto scale
                CommonHelper.loadImage(this.activeScreen.getImage()).then((data) => {
                    if (data.success && data.width > 0) {
                        setTimeout(() => {
                            if (this.$refs.activeScreen) {
                                this.setScaleFactor(this.$refs.activeScreen.clientWidth / data.width);
                            }
                        }, 0);
                    }
                });
            }

            this.closeOverlayScreen();
            this.refreshActiveScreenWrapperAlignment();

            this.$setDocumentTitle(() => this.$t('Screen') + ' ' + this.activeScreen.title);

            // refocus screen wrapper
            if (this.$refs.activeScreenWrapper) {
                this.$refs.activeScreenWrapper.focus();
            }
        },
        goToPrevScreen() {
            if (this.orderedScreens[this.activeScreenOrderedIndex - 1]) {
                this.setActiveScreenId(this.orderedScreens[this.activeScreenOrderedIndex - 1].id);
            }
        },
        goToNextScreen() {
            if (this.orderedScreens[this.activeScreenOrderedIndex + 1]) {
                this.setActiveScreenId(this.orderedScreens[this.activeScreenOrderedIndex + 1].id);
            }
        },
        refreshActiveScreenWrapperAlignment() {
            this.$nextTick(() => {
                if (!this.$refs.activeScreenWrapper) {
                    return;
                }

                if (this.activeScreen.isLeftAligned) {
                    this.$refs.activeScreenWrapper.scrollLeft = 0;
                } else if (this.activeScreen.isRightAligned) {
                    this.$refs.activeScreenWrapper.scrollLeft = this.$refs.activeScreenWrapper.scrollWidth;
                } else {
                    this.$refs.activeScreenWrapper.scrollLeft = (this.$refs.activeScreenWrapper.scrollWidth - this.$refs.activeScreenWrapper.offsetWidth) / 2;
                }
            });
        },

        // hotspot navigation
        hotspotNavigate(hotspotId) {
            var hotspot = this.getHotspot(hotspotId);
            if (!hotspot) {
                return;
            }

            var transition = hotspot.settings.transition || 'none';

            if (hotspot.type === 'url') {
                window.open(hotspot.settings.url || '#', '_blank');
            } else if (hotspot.type === 'prev') {
                if (this.orderedScreens[this.activeScreenOrderedIndex - 1]) {
                    this.changeActiveScreen(
                        this.orderedScreens[this.activeScreenOrderedIndex - 1].id,
                        transition
                    );
                }
            } else if (hotspot.type === 'next') {
                if (this.orderedScreens[this.activeScreenOrderedIndex + 1]) {
                    this.changeActiveScreen(
                        this.orderedScreens[this.activeScreenOrderedIndex + 1].id,
                        transition
                    );
                }
            } else if (hotspot.type === 'back') {
                if (this.overlayScreenId) {
                    this.closeOverlayScreen();
                } else if (this.oldActiveScreen) {
                    this.changeActiveScreen(
                        this.oldActiveScreen.id,
                        transition
                    );
                }
            } else if (hotspot.type === 'screen') {
                this.changeActiveScreen(
                    hotspot.settings.screenId,
                    transition
                );
            } else if (hotspot.type === 'overlay') {
                this.openOverlayScreen(
                    hotspot.settings.screenId,
                    transition,
                    hotspot.settings.outsideClose,
                    hotspot.settings.overlayPosition,
                    hotspot.settings.offsetTop,
                    hotspot.settings.offsetBottom,
                    hotspot.settings.offsetLeft,
                    hotspot.settings.offsetRight,
                    hotspot.settings.fixOverlay
                )
            } else if (hotspot.type === 'scroll') {
                if (this.$refs.activeScreenWrapper) {
                    this.$refs.activeScreenWrapper.scrollTo({
                        'behavior': 'smooth',
                        'top':      (hotspot.settings.scrollTop << 0),
                        'left':     (hotspot.settings.scrollLeft << 0),
                    });
                }
            }
        },
        fixedOverlayReposition() {
            if (
                this.isOverlayScreenFixed &&
                this.overlayScreenId &&
                this.$refs.activeScreenWrapper &&
                this.$refs.overlayContainer
            ) {
                this.$refs.overlayContainer.style.marginTop  = this.$refs.activeScreenWrapper.scrollTop + 'px';
                this.$refs.overlayContainer.style.marginLeft = this.$refs.activeScreenWrapper.scrollLeft + 'px';
            }
        },
        openOverlayScreen(
            screenId,
            transition,
            outsideClose,
            position,
            offsetTop,
            offsetBottom,
            offsetLeft,
            offsetRight,
            fixed
        ) {
            if (screenId == this.overlayScreenId) {
                return;
            }

            this.closeOverlayScreen(() => { // close any previous opened overlay screens
                this.isOverlayScreenClosing    = false;
                this.overlayScreenId           = screenId;
                this.overlayScreenTransition   = transition || 'none';
                this.overlayScreenPosition     = position || 'centered';
                this.overlayScreenOffsetTop    = offsetTop << 0;
                this.overlayScreenOffsetBottom = offsetBottom << 0;
                this.overlayScreenOffsetLeft   = offsetLeft << 0;
                this.overlayScreenOffsetRight  = offsetRight << 0;
                this.overlayScreenOutsideClose = !CommonHelper.isEmpty(outsideClose) ? outsideClose : true;
                this.isOverlayScreenFixed      = !CommonHelper.isEmpty(fixed) ? fixed : false;

                this.$nextTick(() => {
                    this.fixedOverlayReposition();
                });
            });
        },
        closeOverlayScreen(callback) {
            if (!this.overlayScreenId) {
                if (CommonHelper.isFunction(callback)) {
                    callback();
                }

                return; // already closed
            }

            this.isOverlayScreenClosing = true;

            if (this.overlayCloseAnimationTimeoutId) {
                clearTimeout(this.overlayCloseAnimationTimeoutId);
            }

            this.overlayCloseAnimationTimeoutId = setTimeout(() => {
                this.isOverlayScreenClosing    = true;
                this.overlayScreenId           = null;
                this.overlayScreenPosition     = 'centered';
                this.overlayScreenTransition   = 'none';
                this.overlayScreenOffsetTop    = 0;
                this.overlayScreenOffsetBottom = 0;
                this.overlayScreenOffsetLeft   = 0;
                this.overlayScreenOffsetRight  = 0;
                this.overlayScreenOutsideClose = true;

                if (CommonHelper.isFunction(callback)) {
                    callback();
                }
            }, this.overlayScreenTransition != 'none' ? SCREENS_ANIMATION_SPEED : 0);
        },
        onOverlayOutsideClick(e) {
            if (this.overlayScreenId && this.overlayScreenOutsideClose) {
                e.preventDefault();
                e.stopPropagation();

                this.closeOverlayScreen();
            }
        }
    },
}
</script>
