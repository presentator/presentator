<template>
    <div class="project-item">
        <template v-if="isActive">
            <div class="thumb">
                <button uxp-variant="secondary" uxp-quiet="true" @click.prevent="deactivate()" :disabled="isCreating">Cancel</button>
                <button uxp-variant="primary" @click.prevent="create()" :disabled="!canCreate">Create</button>
            </div>

            <div class="content">
                <input ref="titleInput" v-model="title" type="text" uxp-quiet="true" placeholder="Type project title">
            </div>
        </template>

        <div v-else class="new-placeholder">
            <div class="icon">+</div>
            <div class="txt">New project</div>
            <div class="click-overlay" @click.prevent="activate()"></div>
        </div>
    </div>
</template>

<script>
const ApiClient = require('@/utils/ApiClient.js');

module.exports = {
    name: 'project-create',
    data() {
        return {
            isActive:   false,
            isCreating: false,
            title:      '',
        }
    },
    computed: {
        canCreate() {
            return this.isActive && !this.isCreating && this.title.length > 0;
        },
    },
    methods: {
        activate() {
            this.isActive = true;
            this.title    = ''; // reset

            this.$nextTick(() => {
                if (this.$refs.titleInput) {
                    this.$refs.titleInput.focus();
                }
            })
        },
        deactivate() {
            this.isActive = false;
        },
        async create() {
            if (!this.canCreate) {
                return;
            }

            this.isCreating = true;

            return ApiClient.Projects.create({
                'title': this.title,
            }).then((response) => {
                this.$emit('projectCreated', response.data);

                this.deactivate();
            }).catch((err) => {
                this.$baseApiErrorHandler(err);
            }).finally(() => {
                this.isCreating = false;
            });
        },
    },
}
</script>
