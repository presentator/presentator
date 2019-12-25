<template>
    <div class="field-group prototype-create-row">
        <input ref="titleInput"
            v-model="title"
            type="text"
            class="input prototype-title-field"
            placeholder="Prototype title"
            title="Prototype title"
        >

        <select v-model="type" title="Prototype Type" class="prototype-type-field">
            <option value="mobile">Mobile</option>
            <option value="desktop">Desktop</option>
        </select>

        <template v-if="type === 'mobile'">
            <input v-model.number="width" type="number" class="input prototype-size-field" placeholder="Width" title="Prototype width">

            <input v-model.number="height" type="number" class="input prototype-size-field" placeholder="Height" title="Prototype height">
        </template>

        <button class="button button--primary" @click.prevent="create()" :disabled="!canCreate">Create prototype</button>
    </div>
</template>

<script>
import apiClient from '@/utils/ApiClient.js';

const defaultSizes = {
    // width: height
    1024: 1366,
    768:  1024,
    412:  824,
    375:  812,
    360:  740,
    324:  394,
}

export default {
    name: 'prototype-create',
    props: ['projectId'],
    data() {
        return {
            isCreating: false,
            title:      '',
            type:       'desktop',
            width:      0,
            height:     0,
        }
    },
    computed: {
        canCreate() {
            return (
                !this.isCreating &&
                this.projectId &&
                this.title.length > 0 &&
                (this.type === 'desktop' || (this.width > 0 && this.height > 0))
            );
        }
    },
    mounted() {
        this.reset();
    },
    methods: {
        async reset() {
            this.title = '';

            // set the first frame dimensions as default
            const frames = await this.$getFrames();
            if (frames.length) {
                this.width  = frames[0].width << 0;

                // we first try to set the frame height from a default devices list
                // because sometimes the designs are "longer" than the frame viewport
                if (defaultSizes[this.width]) {
                    this.height = defaultSizes[this.width];
                } else {
                    this.height = frames[0].height << 0;
                }
            }

            if (this.width > 0 && this.width <= 1024) {
                this.type = 'mobile';
            }

            if (this.$refs.titleInput) {
                this.$refs.titleInput.focus();
            }
        },
        async create() {
            if (!this.canCreate) {
                return;
            }

            this.isCreating = true;

            try {
                const response = await apiClient.Prototypes.create({
                    'projectId':   this.projectId,
                    'title':       this.title,
                    'type':        this.type,
                    'width':       this.width,
                    'height':      this.height,
                    'scaleFactor': (this.type === 'mobile' ? 0 : 1),
                });

                this.$emit('prototypeCreated', response.data);

                this.reset();
            } catch (err) {
                this.$baseApiErrorHandler(err);
            }

            this.isCreating = false;
        },
    },
}
</script>
