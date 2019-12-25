<template>
    <div ref="projectItem" class="project-item" @click.prevent="select()">
        <div class="thumb">
            <img v-if="project.featuredScreen && project.featuredScreen.small"
                :src="project.featuredScreen.small"
                :alt="project.title"
            />
        </div>
        <div class="content">
            <span class="title">{{ project.title }}{{ project.archived ? ' (Archived)' : '' }}</span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'project-item',
    props: {
        project: {
            type:     Object,
            required: true,
        },
    },
    methods: {
        select() {
            if (this.$refs.projectItem) {
                this.$refs.projectItem.scrollIntoView({
                    behavior: 'smooth',
                    block:    'nearest',
                });
            }

            this.$emit('select', this.project.id);
        }
    }
}
</script>
