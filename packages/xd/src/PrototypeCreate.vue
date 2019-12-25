<template>
    <div class="field-group">
        <input ref="titleInput" v-model="title" uxp-quiet="true" type="text" placeholder="Type prototype title" title="Prototype Title">

        <reactive-select v-model="type" uxp-quiet="true" title="Prototype Type">
            <option value="mobile">Mobile</option>
            <option value="desktop">Desktop</option>
        </reactive-select>

        <template v-if="type === 'mobile'">
            <input v-model.number="width" type="number" uxp-quiet="true" style="width: 35px;" placeholder="Width" title="Prototype Width">

            <input v-model.number="height" type="number" uxp-quiet="true" style="width: 35px;" placeholder="Height" title="Prototype Height">
        </template>

        <button uxp-variant="primary" @click.prevent="create()" :disabled="!canCreate">Create prototype</button>
    </div>
</template>

<script>
const ApiClient      = require('@/utils/ApiClient.js');
const ReactiveSelect = require('@/ReactiveSelect.vue').default;

const defaultSizes = {
    // width: height
    1024: 1366,
    768:  1024,
    412:  824,
    375:  812,
    360:  740,
    324:  394,
}

module.exports = {
    name: 'prototype-create',
    components: {
        'reactive-select': ReactiveSelect,
    },
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
        reset() {
            this.title = '';

            // set first artboard as default
            const artboards = this.$getArtboards();
            if (artboards.length) {
                this.width  = artboards[0].width << 0;

                // we first try to set the artboard height from a default devices list
                // because sometimes the designs are "longer" than the device view port
                if (defaultSizes[this.width]) {
                    this.height = defaultSizes[this.width];
                } else {
                    this.height = artboards[0].height << 0;
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

            return ApiClient.Prototypes.create({
                'projectId':   this.projectId,
                'title':       this.title,
                'type':        this.type,
                'width':       this.width,
                'height':      this.height,
                'scaleFactor': (this.type === 'mobile' ? 0 : 1),
            }).then((response) => {
                this.$emit('prototypeCreated', response.data);

                this.reset();
            }).catch((err) => {
                this.$baseApiErrorHandler(err);
            }).finally(() => {
                this.isCreating = false;
            });
        },
    },
}
</script>
