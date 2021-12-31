import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient     from '@/utils/ApiClient';
import CommonHelper  from '@/utils/CommonHelper';
import AppConfig     from '@/utils/AppConfig';
import ClientStorage from '@/utils/ClientStorage';
import Hotspot       from '@/models/Hotspot';

export default {
    data() {
        return {
            isPreviewModeHintsActive:  false,
            isLoadingHotspots:         false,
            isLoadingHotspotTemplates: false,
            fitToScreen:               false,
            keepHotspotsVisible:       false,
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
    watch: {
        fitToScreen: function (newVal) {
            ClientStorage.setItem(
                AppConfig.get('VUE_APP_TOGGLE_FIT_TO_SCREEN_STORAGE_KEY'),
                newVal
            );
        }
    },
    mounted() {
        this.keepHotspotsVisible = this.$route.query.hotspots == 1;

        if (typeof this.$route.query.fit !== 'undefined') {
            this.fitToScreen = this.$route.query.fit == 1;
        } else {
            this.fitToScreen = ClientStorage.getItem(
                AppConfig.get('VUE_APP_TOGGLE_FIT_TO_SCREEN_STORAGE_KEY'),
                false
            );
        }
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
            }).finally(() => {
                this.isLoadingHotspots = false;
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
        toggleFitToScreen() {
            this.fitToScreen = !this.fitToScreen;

            this.$router.replace({
                name:   this.$route.name,
                params: Object.assign({}, this.$route.params),
                query:  Object.assign({}, this.$route.query, {
                    fit: this.fitToScreen ? '1' : '0',
                }),
            });
        },
        toggleKeepHotspotsVisible() {
            this.keepHotspotsVisible = !this.keepHotspotsVisible;

            this.$router.replace({
                name:   this.$route.name,
                params: Object.assign({}, this.$route.params),
                query:  Object.assign({}, this.$route.query, {
                    hotspots: this.keepHotspotsVisible ? '1' : '0',
                }),
            });
        },
    },
}
