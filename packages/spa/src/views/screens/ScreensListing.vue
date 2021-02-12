<template>
    <div v-if="activePrototype"
        v-shortcut.27="deselectAllScreens"
        v-shortcut.ctrl.65="selectAllScreens"
    >
        <div v-if="withFilterBar" class="search-bar">
            <div class="search-input-wrapper" :class="{'active': searchTerm.length > 0}">
                <span class="search-clear" v-tooltip.left="$t('Clear')" @click.prevent="resetFilters()"></span>
                <input type="input"
                    class="search-input"
                    :placeholder="$t('Search screens')"
                    v-model.trim="searchTerm"
                    @input="onSearchInputChange"
                >
            </div>
        </div>

        <div class="block txt-center txt-hint">
            <span v-show="!orderedScreens.length && isLoadingScreens" class="loader loader-lg loader-blend m-b-base"></span>

            <h4 v-show="hasActiveFilters && !orderedScreens.length && !isLoadingScreens" class="m-b-25">
                {{ $t('No screens were found.') }}
            </h4>
        </div>

        <h5 v-show="!isLoadingScreens && orderedScreens.length && searchTerm.length > 0"
            class="m-t-0 m-b-small"
        >
            {{ $t('Search results for "{searchTerm}" ({totalFound}):', {
                searchTerm: searchTerm,
                totalFound: totalScreens,
            }) }}
        </h5>

        <draggable class="boxes-list screens-list"
            group="screens"
            filter=".ignore-sort"
            draggable=".box-card"
            :fallbackTolerance="2"
            :fallbackOnBody="true"
            :forceFallback="true"
            :animation="200"
            :touchStartThreshold="0"
            :list="orderedScreens"
            :disabled="hasActiveFilters"
            @change="onSortChange"
            @start="onSortStart"
            @end="onSortEnd"
        >
            <screen-upload
                v-show="isUploadBoxVisible"
                slot="header"
                class="ignore-sort"
                :class="{'expanded': !orderedScreens.length}"
                :prototypeId="activePrototype.id"
                @screenUploaded="onScreenUpload"
                @screensQueueComplete="onScreensQueueComplete"
            ></screen-upload>

            <screen-box v-for="screen in orderedScreens"
                :key="'screen_box' + screen.id"
                :screen="screen"
                @screenDelete="onScreenDelete"
            ></screen-box>
        </draggable>

        <div class="block txt-center">
            <button class="btn btn-warning btn-lg btn-cons-xl m-l-small m-r-small"
                @click.prevent="resetFilters()"
                v-show="hasActiveFilters"
            >
                <span class="txt">{{ $t('Reset filters') }}</span>
            </button>

            <button class="btn btn-transp-primary btn-lg btn-cons-xl btn-loader m-l-small m-r-small"
                :class="{'btn-loader-active': isLoadingScreens}"
                @click.prevent="loadScreens(currentPage + 1, false)"
                v-show="hasMoreScreens"
            >
                <span class="txt">{{ $t('Load more screens') }}</span>
            </button>
        </div>

        <bulk-bar @screenDelete="onScreenDelete"></bulk-bar>
    </div>
</template>

<style lang="scss" scoped>
.box-placeholder.expanded {
    width: 100%;
    min-height: 320px;
}
</style>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import draggable    from 'vuedraggable'
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import BulkBar      from '@/views/screens/BulkBar';
import ScreenUpload from '@/views/screens/ScreenUpload';
import ScreenBox    from '@/views/screens/ScreenBox';
import Screen       from '@/models/Screen';

const SCREENS_PER_PAGE = 200;

const defaultData = {
    isLoadingScreens: false,
    isLoadingComments: false,
    searchTerm:       '',
    currentPage:      1,
    totalScreens:     0,
};

export default {
    name: 'screens-listing',
    components: {
        'bulk-bar':      BulkBar,
        'screen-upload': ScreenUpload,
        'screen-box':    ScreenBox,
        'draggable':     draggable,
    },
    props: {
        withFilterBar: {
            type:    Boolean,
            default: true,
        },
    },
    data() {
        return Object.assign({}, defaultData);
    },
    computed: {
        ...mapState({
            selectedScreens: state => state.screens.selectedScreens,
            screens:         state => state.screens.screens,
        }),
        ...mapGetters({
            getScreen:       'screens/getScreen',
            orderedScreens:  'screens/orderedScreens',
            activePrototype: 'prototypes/activePrototype',
        }),

        hasMoreScreens() {
            return this.totalScreens > this.orderedScreens.length;
        },
        hasActiveFilters() {
            return this.searchTerm.length > 0;
        },
        isUploadBoxVisible() {
            return !this.hasActiveFilters && (!this.isLoadingScreens || this.orderedScreens.length);
        },
    },
    watch: {
        activePrototype(newVal, oldVal) {
            if (
                newVal &&
                (!oldVal || newVal.id != oldVal.id)
            ) {
                this.loadScreens();
                this.loadComments();
            }
        },
    },
    beforeMount() {
        this.loadScreens();
        this.loadComments();
    },
    methods: {
        ...mapActions({
            setScreens:         'screens/setScreens',
            appendScreens:      'screens/appendScreens',
            addScreen:          'screens/addScreen',
            removeScreen:       'screens/removeScreen',
            selectScreen:       'screens/selectScreen',
            selectAllScreens:   'screens/selectAllScreens',
            deselectAllScreens: 'screens/deselectAllScreens',
            setComments:        'comments/setComments',
            appendComments:     'comments/appendComments',
        }),

        resetFilters() {
            this.searchTerm = defaultData.searchTerm;

            this.loadScreens();
        },
        resetList() {
            this.totalScreens = defaultData.totalScreens;
            this.currentPage  = defaultData.currentPage;

            this.setScreens([]);
        },
        onSearchInputChange(e) {
            if (this.searchTimeoutId) {
                clearTimeout(this.searchTimeoutId);
            }

            if (!this.searchTerm.length) {
                this.loadScreens();
            } else {
                this.resetList();
                this.isLoadingScreens = true;

                // throttle
                this.searchTimeoutId = setTimeout(() => {
                    this.loadScreens();
                }, 250);
            }
        },
        loadScreens(page = 1, reset = true) {
            if (!this.activePrototype) {
                return;
            }

            if (reset) {
                this.resetList();
            }

            this.isLoadingScreens = true;

            ApiClient.Screens.getList(page, SCREENS_PER_PAGE, {
                'envelope':            true,
                'search[prototypeId]': this.activePrototype.id,
                'search[title]':       (this.withFilterBar ? this.searchTerm : ''),
            }).then((response) => {
                this.appendScreens(CommonHelper.getNestedVal(response, 'data.response', []))

                this.totalScreens = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-total-count', 0)  || 0;
                this.currentPage  = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-current-page', 1) || 1;
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingScreens = false;
            });
        },
        loadComments(page = 1) {
            if (!this.activePrototype) {
                return;
            }

            this.isLoadingComments = true;

            ApiClient.enableAutoCancellation(false);

            ApiClient.ScreenComments.getList(page, 100, {
                'envelope':            true,
                'search[prototypeId]': this.activePrototype.id,
                'search[status]':      'pending',
                'search[replyTo]':     0, // primary
            }).finally(() => {
                this.isLoadingComments = false;
            }).then((response) => {
                let commentsData = CommonHelper.getNestedVal(response, 'data.response', []);
                let currentPage  = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-current-page', 1);
                let totalPages   = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-page-count', 1);

                if (page == 1) {
                    this.setComments(commentsData);
                } else {
                    this.appendComments(commentsData);
                }

                // load next portion of comments (if there are more)
                if (totalPages > currentPage) {
                    this.loadComments(page + 1);
                }
            }).catch((err) => {
                this.$errResponseHandler(err);
            });
        },


        onScreenDelete(screenId) {
            this.removeScreen(screenId);

            this.totalScreens--;
        },
        onScreenUpload(screenData) {
            if (screenData && screenData.id) {
                this.addScreen(screenData);

                this.totalScreens++;
            }
        },
        onScreensQueueComplete(totalUploaded) {
            // reset bulk selection
            this.deselectAllScreens();

            // scroll to the last added screen
            if (totalUploaded > 0) {
                this.$nextTick(() => {
                    var lastScreen = document.querySelector('.box-screen:last-child');
                    if (lastScreen) {
                        lastScreen.scrollIntoView({block: 'nearest'});
                    }
                });
            }
        },

        // sorting
        onSortStart(e) {
            if (this.sortAnimationTimeoutId) {
                clearTimeout(this.sortAnimationTimeoutId);
            }

            if (e.target) {
                e.target.classList.add('sort-started');
            }
        },
        onSortEnd(e) {
            if (this.sortAnimationTimeoutId) {
                clearTimeout(this.sortAnimationTimeoutId);
            }

            this.sortAnimationTimeoutId = setTimeout(() => {
                if (e.target) {
                    e.target.classList.remove('sort-started');
                }
            }, 350);
        },
        onSortChange(e) {
            if (!e || !e.moved || !e.moved.element) {
                return;
            }

            this.updateScreenOrder(e.moved.element, e.moved.newIndex + 1); // zero based
        },
        updateScreenOrder(screen, newOrder) {
            if (!screen || screen.order == newOrder) {
                return;
            }

            // update remaining screens order
            if (newOrder > screen.order) { // move forwards
                for (let i = this.screens.length - 1; i >= 0; i--) {
                    if (
                        this.screens[i].id != screen.id && this.screens[i].order > screen.order && this.screens[i].order <= newOrder) {
                        this.$set(this.screens[i], 'order', this.screens[i].order - 1);
                    }
                }
            } else { // move backwards
                for (let i = this.screens.length - 1; i >= 0; i--) {
                    if (this.screens[i].id != screen.id && this.screens[i].order < screen.order && this.screens[i].order >= newOrder) {
                        this.$set(this.screens[i], 'order', this.screens[i].order + 1);
                    }
                }
            }

            // optimistic update
            screen.order = newOrder;

            // actual update
            ApiClient.Screens.update(screen.id, {
                order: newOrder,
            });
        },
    }
}
</script>
