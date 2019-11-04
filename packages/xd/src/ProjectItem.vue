<template>
    <div class="project-item">
        <div class="thumb">
            <img v-if="project.featuredScreen && project.featuredScreen.small"
                :src="project.featuredScreen.small"
                :alt="project.title"
            />
        </div>
        <div class="content">
            <span class="title">{{ project.title }}{{ project.archived ? ' (Archived)' : '' }}</span>
        </div>

        <!-- workaround for Adobe XD events propagation -->
        <div class="click-overlay" @click.prevent="$emit('overlayClick', project.id)"></div>
    </div>
</template>

<script>
module.exports = {
    name: 'project-item',
    props: {
        project: {
            type:     Object,
            required: true,
        },
    },
}
</script>
