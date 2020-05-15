<template>
    <div class="floating-bar with-sidebar bulk-screen-bar"
        :class="{'active': totalSelectedScreens > 0}"
    >
        <div class="nav nav-left">
            <span class="txt">{{ $tc('1 screen selected | {count} screens selected', totalSelectedScreens) }}</span>

            <button v-if="movablePrototypes.length"
                type="button"
                class="btn btn-border btn-sm btn-loader m-l-small"
            >
                <span class="txt">{{ $t('Move selected') }}</span>
                <i class="fe fe-chevron-up m-l-5"></i>

                <toggler class="dropdown dropdown-wrapped dropdown-sm prototypes-dropdown">
                    <div v-for="prototype in movablePrototypes"
                        :key="prototype.id"
                        class="dropdown-item"
                        @click.prevent="moveScreensToPrototype(prototype.id)"
                    >
                        <i class="fe" :class="prototype.isForDesktop ? 'fe-monitor' : 'fe-smartphone'"></i>
                        <span class="txt">{{ prototype.title }}</span>
                    </div>
                </toggler>
            </button>

            <button v-if="totalSelectedScreens > 0"
                type="button"
                class="btn btn-transp-danger btn-sm m-l-small"
                @click.prevent="deleteSelected()"
            >
                <i class="fe fe-trash m-r-5"></i>
                <span class="txt">{{ $t('Delete selected') }}</span>
            </button>
        </div>
        <div class="nav nav-right">
            <div class="ctrl-item" @click.prevent="deselectAllScreens()">
                <span class="txt">{{ $t('Reset selection') }}</span>
                <i class="fe fe-x m-l-5"></i>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.prototypes-dropdown {
    right: auto;
    left: 0;
    min-width: 230px;
}
</style>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient from '@/utils/ApiClient';

export default {
    name: 'bulk-bar',
    data() {
        return {
            isMovingScreens: false,
        }
    },
    computed: {
        ...mapState({
            selectedScreens: state => state.screens.selectedScreens,
            prototypes:      state => state.prototypes.prototypes,
        }),
        ...mapGetters({
            activePrototype: 'prototypes/activePrototype',
        }),

        // prototypes list without the active prototype
        movablePrototypes() {
            if (!this.activePrototype) {
                return this.prototypes;
            }

            var result = [];

            for (let i in this.prototypes) {
                if (this.prototypes[i].id != this.activePrototype.id) {
                    result.push(this.prototypes[i]);
                }
            }

            return result;
        },
        totalSelectedScreens () {
            return this.selectedScreens.length;
        },
    },
    methods: {
        ...mapActions({
            deselectAllScreens:   'screens/deselectAllScreens',
            setActivePrototypeId: 'prototypes/setActivePrototypeId',
        }),

        deleteSelected() {
            var confirmMsg = this.totalSelectedScreens == 1 ?
                this.$t('Do you really want to delete the selected screen?') :
                this.$t('Do you really want to delete the selected screens?');

            var successMsg = this.$tc('Successfully deleted 1 screen. | Successfully deleted {count} screens.', this.totalSelectedScreens);

            if (
                this.totalSelectedScreens <= 0 ||
                !window.confirm(confirmMsg)
            ) {
                return;
            }

            for (let i = this.selectedScreens.length - 1; i >= 0; i--) {
                ApiClient.Screens.delete(this.selectedScreens[i]);

                this.$emit('screenDelete', this.selectedScreens[i]);
            }

            this.$toast(successMsg);

            this.deselectAllScreens();
        },

        moveScreensToPrototype(prototypeId) {
            var confirmMsg = this.totalSelectedScreens == 1 ?
                this.$t('Do you really want to move the selected screen?') :
                this.$t('Do you really want to move the selected screens?');

            if (
                this.totalSelectedScreens <= 0 ||
                !window.confirm(confirmMsg)
            ) {
                return;
            }

            this.isMovingScreens = true;

            var movePromises = [];
            for (let i in this.selectedScreens) {
                let promise = ApiClient.Screens.update(this.selectedScreens[i], {
                    prototypeId: prototypeId,
                    order: 0, // push to the end of the list
                });

                movePromises.push(promise);
            }

            Promise.all(movePromises).then((values) => {
                this.$toast(this.$tc('Successfully moved 1 screen. | Successfully moved {count} screens.', this.totalSelectedScreens));
            }).catch((values) => {
                if (this.totalSelectedScreens == 1) {
                    this.$toast(this.$t('Unable to move the selected screen.'), 'danger');
                } else {
                    this.$toast(this.$t('Unable to move all of the selected screens.'), 'danger');
                }
            }).finally(() => {
                this.isMovingScreens = false;

                this.deselectAllScreens();

                this.setActivePrototypeId(prototypeId);
            });
        }
    }
}
</script>
