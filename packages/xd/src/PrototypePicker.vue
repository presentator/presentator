<template>
    <div>
        <label class="block-field">
            <span>Prototype</span>

            <reactive-select v-model="selectedPrototype" :disabled="!prototypes.length || isLoading">
                <option v-if="!projectId"  key="missingProjectOption" value="" selected>First select a project to load its prototypes</option>
                <option v-else-if="isLoading" key="loadingOption" value="" selected>Loading...</option>

                <template v-else>
                    <option v-for="(prototype, i) in prototypes"
                        :key="prototype.id"
                        :value="prototype.id"
                    >{{ prototype.title || `Prototype ${i+1}` }}&nbsp;({{
                        prototype.type === 'desktop' ? 'Desktop' : `Mobile ${prototype.width}x${prototype.height}`
                    }})</option>

                    <option key="newPrototypeOption" value="new">+ New prototype</option>
                </template>
            </reactive-select>
        </label>

        <div v-if="selectedPrototype === 'new'">
            <prototype-create :projectId="projectId" @prototypeCreated="onPrototypeCreate" />

            <div class="spacer"></div>
        </div>
    </div>
</template>

<script>
const storageHelper   = require('xd-storage-helper');
const ApiClient       = require('@/utils/ApiClient.js');
const PrototypeCreate = require('@/PrototypeCreate.vue').default;
const ReactiveSelect  = require('@/ReactiveSelect.vue').default;

module.exports = {
    name: 'prototype-picker',
    components: {
        'reactive-select':  ReactiveSelect,
        'prototype-create': PrototypeCreate,
    },
    props: {
        projectId: {
            type: Number,
        },
    },
    data() {
        return {
            isLoading:         false,
            prototypes:        [],
            selectedPrototype: '',
        }
    },
    watch: {
        projectId(newVal, oldVal) {
            this.reset();

            if (newVal) {
                this.loadPrototypes();
            }
        },
        selectedPrototype(newVal, oldVal) {
            if (newVal > 0) {
                storageHelper.set('lastSelectedPrototype', newVal);

                this.$emit('changed', newVal);
            } else {
                this.$emit('changed', null); // always emit null to clear mid-selection state
            }
        },
    },
    mounted() {
        if (this.projectId) {
            this.loadPrototypes();
        }
    },
    methods: {
        reset() {
            this.prototypes = [];

            this.setSelectedPrototype('');
        },
        hasPrototype(id) {
            for (let i = this.prototypes.length - 1; i >= 0; i--) {
                if (this.prototypes[i].id == id) {
                    return true;
                }
            }

            return false;
        },
        async loadPrototypes() {
            if (!this.projectId) {
                console.log('loadPrototypes: projectId is missing.')
                return;
            }

            this.isLoading = true;

            return ApiClient.Prototypes.getList(1, 100, {
                'search[projectId]': this.projectId,
            }).then((response) => {
                this.prototypes = response.data;

                this.$nextTick(async () => {
                    let defaultPrototype = 'new';

                    if (this.prototypes.length) {
                        let lastSelectedPrototype = await storageHelper.get('lastSelectedPrototype', null);
                        defaultPrototype = this.hasPrototype(lastSelectedPrototype) ?
                            lastSelectedPrototype : (this.prototypes[this.prototypes.length - 1].id);
                    }

                    this.setSelectedPrototype(defaultPrototype);
                });
            }).catch((err) => {
                this.$baseApiErrorHandler(err);
            }).finally(() => {
                this.isLoading = false;
            });
        },
        onPrototypeCreate(prototype) {
            this.prototypes.push(prototype);

            this.$nextTick(() => {
                this.setSelectedPrototype(prototype.id);
            });
        },
        setSelectedPrototype(val) {
            if (this.selectedPrototypeTimeoutId) {
                clearTimeout(this.selectedPrototypeTimeoutId);
            }

            this.selectedPrototypeTimeoutId = setTimeout(() => {
                this.selectedPrototype = val;
            }, 250); // slight delay to ensure that all prototype options are rendered
        },
    },
}
</script>
