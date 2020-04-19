import { mapState, mapActions, mapGetters } from 'vuex';
import CommonHelper from '@/utils/CommonHelper';
import Hotspot      from '@/models/Hotspot';

export default {
    computed: {
        ...mapState({
            activeScreenId:   state => state.screens.activeScreenId,
            hotspots:         state => state.hotspots.hotspots,
            hotspotTemplates: state => state.hotspots.hotspotTemplates,
        }),
        ...mapGetters({
            getHotspotsForScreen: 'hotspots/getHotspotsForScreen',
        }),

        activeScreenHotspots() {
            return this.getHotspotsForScreen(this.activeScreenId);
        },
        totalActiveHotspotTemplates() {
            var result = 0;

            for (let i in this.hotspotTemplates) {
                if (CommonHelper.inArray(this.hotspotTemplates[i].screenIds, this.activeScreenId)) {
                    result++;
                }
            }

            return result;
        },
    },
    methods: {
        ...mapActions({
            addHotspot: 'hotspots/addHotspot',
        }),

        initHotspotCreation(e, screenId) {
            if (!this.isInHotspotsMode) {
                return;
            }

            screenId = screenId || this.activeScreenId;

            var hotspot = new Hotspot({
                screenId: screenId,
                left:     e.offsetX / this.scaleFactor,
                top:      e.offsetY / this.scaleFactor,
            });

            this.addHotspot(hotspot);

            this.$nextTick(() => {
                if (this.$refs.hotspotBoxes) {
                    var hotspotBox = this.$refs.hotspotBoxes[this.$refs.hotspotBoxes.length - 1];

                    if (hotspotBox) {
                        hotspotBox.activate();

                        hotspotBox.initResizing(e);
                    }
                }
            });
        },
        viewHotspot(hotspotId) {
            if (!this.$refs.hotspotBoxes) {
                return;
            }

            for (let i in this.$refs.hotspotBoxes) {
                let hotspotBox = this.$refs.hotspotBoxes[i];
                if (hotspotBox.hotspot && hotspotBox.hotspot.id == hotspotId) {
                    hotspotBox.activate();

                    break;
                }
            }
        },
        deactivateHotspots() {
            if (this.$refs.hotspotPopover) {
                this.$refs.hotspotPopover.close();
            }

            if (this.$refs.hotspotBoxes) {
                for (let i in this.$refs.hotspotBoxes) {
                    this.$refs.hotspotBoxes[i].deactivate();
                }
            }
        },
        onHotspotPopoverClose() {
            this.deactivateHotspots();
        },
        onHotspotActivate(hotspot, elem) {
            this.deactivateHotspots();

            if (this.$refs.hotspotPopover) {
                this.$refs.hotspotPopover.open(hotspot, elem);
            }
        },
        onHotspotRepositioning(hotspot, elem) {
            this.$nextTick(() => {
                if (this.$refs.hotspotPopover) {
                    this.$refs.hotspotPopover.reposition(elem);
                }
            });
        },
        getActiveHotspotBox() {
            if (this.$refs.hotspotBoxes) {
                for (let i in this.$refs.hotspotBoxes) {
                    if (this.$refs.hotspotBoxes[i].isActive) {
                        return this.$refs.hotspotBoxes[i];
                    }
                }
            }

            return null;
        },
        snapActiveHotspot(e) {
            if (!this.isInHotspotsMode) {
                return;
            }

            var activeHotspotBox = this.getActiveHotspotBox();

            if (activeHotspotBox) {
                activeHotspotBox.snap();
            }
        },
    },
}
