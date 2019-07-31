import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import Hotspot      from '@/models/Hotspot';

export default {
    data() {
        return {
            isPreviewModeHintsActive:  false,
            isLoadingHotspots:         false,
            isLoadingHotspotTemplates: false,
        }
    },
    computed: {
        ...mapState({
            activePrototypeId: state => state.prototypes.activePrototypeId,
            hotspots:          state => state.hotspots.hotspots,
            hotspotTemplates:  state => state.hotspots.hotspotTemplates,
        }),
        ...mapGetters({
            getHotspot:           'hotspots/getHotspot',
            getHotspotTemplate:   'hotspots/getHotspotTemplate',
            getHotspotsForScreen: 'hotspots/getHotspotsForScreen',
        }),
    },
    methods: {
        ...mapActions({
            setHotspots:         'hotspots/setHotspots',
            appendHotspots:      'hotspots/appendHotspots',
            setHotspotTemplates: 'hotspots/setHotspotTemplates',
        }),

        loadHotspots(prototypeId, page = 1) {
            prototypeId = prototypeId || this.activePrototypeId;

            if (!prototypeId || this.isLoadingHotspots) {
                return;
            }

            this.isLoadingHotspots = true;

            ApiClient.Hotspots.getList(page, 200, {
                'envelope': true,
                'search[prototypeId]': prototypeId,
            }).then((response) => {
                var hotspotsData = CommonHelper.getNestedVal(response, 'data.response', []);
                var currentPage  = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-current-page', 1);
                var totalPages   = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-page-count', 1);

                if (page == 1) {
                    this.setHotspots(hotspotsData);
                } else {
                    this.appendHotspots(hotspotsData);
                }

                // load next portion of hotspots (if there are more)
                if (totalPages > currentPage) {
                    this.loadHotspots(prototypeId, page + 1);
                }
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingHotspots = false;
            });
        },
        loadHotspotTemplates(prototypeId) {
            prototypeId = prototypeId || this.activePrototypeId;

            if (!prototypeId || this.isLoadingHotspotTemplates) {
                return;
            }

            this.isLoadingHotspotTemplates = true;

            ApiClient.HotspotTemplates.getList(1, 100, {
                'expand': 'screenIds',
                'search[prototypeId]': prototypeId,
            }).then((response) => {
                this.setHotspotTemplates(response.data || []);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingHotspotTemplates = false;
            });
        },
        blinkPreviewModeHints() {
            this.isPreviewModeHintsActive = true;

            if (this.previewModeHintsTimeoutId) {
                clearTimeout(this.previewModeHintsTimeoutId);
            }

            this.previewModeHintsTimeoutId = setTimeout(() => {
                this.isPreviewModeHintsActive = false;
            }, 500);
        },
    },
}
