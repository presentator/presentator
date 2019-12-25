<template>
    <div>
        <div class="form-field">
            <label for="prototype_select" class="section-title">Prototype</label>

            <select v-model="selectedPrototype" :disabled="isLoading || !projectId" id="prototype_select">
                <option v-if="!projectId"  key="missingProjectOption" value="" selected disabled>First select a project to load its prototypes</option>

                <option v-else-if="isLoading" key="loadingOption" value="" selected disabled>Loading...</option>

                <template v-else>
                    <option v-for="(prototype, i) in prototypes"
                        :key="prototype.id"
                        :value="prototype.id"
                    >{{ prototype.title || `Prototype ${i+1}` }}&nbsp;({{
                        prototype.type === 'desktop' ? 'Desktop' : `Mobile ${prototype.width}x${prototype.height}`
                    }})</option>

                    <option key="newPrototypeOption" value="new">+ New prototype</option>
                </template>
            </select>
        </div>

        <div v-if="selectedPrototype === 'new'">
            <prototype-create :projectId="projectId" @prototypeCreated="onPrototypeCreate" />
        </div>
    </div>
</template>

<script>
import clientStorage   from '@/utils/ClientStorage';
import apiClient       from '@/utils/ApiClient';
import PrototypeCreate from '@/PrototypeCreate';

export default {
    name: 'prototype-picker',
    components: {
        'prototype-create': PrototypeCreate,
    },
    props: ['projectId'],
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
                clientStorage.setItem('lastSelectedPrototype', newVal);

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

            this.setSelectedPrototype(null);
        },
        hasPrototype(id) {
            for (let i = this.prototypes.length - 1; i >= 0; i--) {
                if (this.prototypes[i].id == id) {
                    return true;
                }
            }

            return false;
        },
        loadPrototypes() {
            if (!this.projectId) {
                console.log('loadPrototypes: projectId is missing.')
                return;
            }

            this.isLoading = true;

            return apiClient.Prototypes.getList(1, 100, {
                'search[projectId]': this.projectId,
            }).then((response) => {
                this.isLoading = false;

                this.prototypes = response.data;

                this.$nextTick(() => {
                    let defaultPrototype = 'new';

                    if (this.prototypes.length) {
                        let lastSelectedPrototype = clientStorage.getItem('lastSelectedPrototype');
                        defaultPrototype = this.hasPrototype(lastSelectedPrototype) ?
                            lastSelectedPrototype : (this.prototypes[this.prototypes.length - 1].id);
                    }

                    this.setSelectedPrototype(defaultPrototype);
                });
            }).catch((err) => {
                if (err) { // is not error from aborted request
                    this.isLoading = false;
                }

                this.$baseApiErrorHandler(err);
            });
        },
        onPrototypeCreate(prototype) {
            this.prototypes.push(prototype);

            this.$nextTick(() => {
                this.setSelectedPrototype(prototype.id);
            });
        },
        setSelectedPrototype(val) {
            this.selectedPrototype = val || '';
        },
    },
}
</script>
