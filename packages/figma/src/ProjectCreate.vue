<template>
    <div class="project-item new" :class="{'active': isActive}">
        <template v-if="isActive">
            <div class="thumb">
                <button class="button button--secondary" @click.prevent="deactivate()" :disabled="isCreating">Cancel</button>
                <button class="button button--primary" @click.prevent="create()" :disabled="!canCreate">Create</button>
            </div>

            <div class="content">
                <input ref="titleInput" v-model="title" type="text" class="input" placeholder="Project title">
            </div>
        </template>

        <div v-else class="thumb" @click.prevent="activate()">
            <div class="sign">+</div>
            <div class="txt">New project</div>
        </div>
    </div>
</template>

<script>
import apiClient from '@/utils/ApiClient';

export default {
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

            try {
                const response = await apiClient.Projects.create({
                    'title': this.title,
                });

                this.deactivate();

                this.$emit('projectCreated', response.data);
            } catch (err) {
                this.$baseApiErrorHandler(err);
            }

            this.isCreating = false;
        },
    },
}
</script>
