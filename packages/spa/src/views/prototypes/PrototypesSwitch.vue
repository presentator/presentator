<template>
    <div>
        <button v-if="!prototypes.length || !activePrototype"
            type="button"
            class="btn btn-cons-lg btn-success"
            @click.prevent="openUpsertPopup()"
        >
            <i class="fe fe-plus"></i>
            <span class="txt">{{ $t('New prototype') }}</span>
        </button>

        <template v-else>
            <span class="icon-btn m-r-10 m-t-5"
                v-tooltip.left="$t('Prototype settings')"
                @click.prevent="openUpsertPopup(activePrototype.id)"
            >
                <i class="fe fe-settings"></i>
            </span>

            <div class="btn btn-primary btn-dropdown prototypes-ctrl">
                <i class="fe" :class="activePrototype.isForDesktop ? 'fe-monitor' : 'fe-smartphone'"></i>
                <span class="txt m-l-5">{{ activePrototype.title }}</span>

                <toggler class="dropdown">
                    <div class="dropdown-item"
                        v-for="prototype in prototypes"
                        :key="prototype.id"
                        :class="{'active': activePrototype.id == prototype.id}"
                        @click.prevent="setActivePrototypeId(prototype.id)"
                    >
                        <i class="fe" :class="prototype.isForDesktop ? 'fe-monitor' : 'fe-smartphone'"></i>
                        <span class="txt">{{ prototype.title }}</span>
                    </div>
                    <hr class="m-t-10">
                    <div class="dropdown-item" @click.prevent="openUpsertPopup()">
                        <i class="fe fe-plus"></i>
                        <span class="txt">{{ $t('New prototype') }}</span>
                    </div>
                </toggler>
            </div>
        </template>

        <relocator>
            <prototype-upsert-popup ref="upsertPopup" :projectId="projectId"></prototype-upsert-popup>
        </relocator>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import PrototypeUpsertPopup from '@/views/prototypes/PrototypeUpsertPopup';
import Prototype            from '@/models/Prototype';
import Relocator            from '@/components/Relocator';

export default {
    name: 'prototypes-switch',
    components: {
        'relocator':              Relocator,
        'prototype-upsert-popup': PrototypeUpsertPopup,
    },
    props: {
        projectId: {
            required: true,
        },
    },
    computed: {
        ...mapState({
            prototypes: state => state.prototypes.prototypes,
        }),
        ...mapGetters({
            getPrototype:    'prototypes/getPrototype',
            activePrototype: 'prototypes/activePrototype',
        }),
    },
    methods: {
        ...mapActions({
            setActivePrototypeId: 'prototypes/setActivePrototypeId',
        }),

        openUpsertPopup(id) {
            if (this.$refs.upsertPopup) {
                this.$refs.upsertPopup.open(id ? this.getPrototype(id) : null);
            }
        },
    }
}
</script>

<style lang="scss" scoped>
.prototypes-ctrl {
    min-width: 210px;
    & > .txt {
        max-width: 180px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
}
</style>
