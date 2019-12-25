<template>
    <div class="panel panel-lg">
        <panel-header></panel-header>

        <project-picker @changed="onProjectChange" />

        <div class="spacer"></div>

        <prototype-picker @changed="onPrototypeChange" :projectId="selectedProject" />

        <label class="block-field">
            <span>Artboards to export</span>
            <reactive-select v-model="exportFilter">
                <option value="all">All artboards</option>
                <option value="selection">Only the selected artboard(s)</option>
            </reactive-select>
        </label>

        <footer>
            <button uxp-variant="primary" @click.prevent="$closePluginDialog()" :disabled="isExporting">Close</button>
            <button uxp-variant="cta" @click.prevent="exportRenditions()" :disabled="!canExport">{{ isExporting ? 'Exporting...' : 'Export' }}</button>
        </footer>
    </div>
</template>

<script>
const application     = require('application');
const fs              = require('uxp').storage.localFileSystem;
const ApiClient       = require('@/utils/ApiClient.js');
const PanelHeader     = require('@/PanelHeader.vue').default;
const ReactiveSelect  = require('@/ReactiveSelect.vue').default;
const ProjectPicker   = require('@/ProjectPicker.vue').default;
const PrototypePicker = require('@/PrototypePicker.vue').default;

module.exports = {
    name: 'route-export',
    components: {
        'reactive-select':  ReactiveSelect,
        'panel-header':     PanelHeader,
        'project-picker':   ProjectPicker,
        'prototype-picker': PrototypePicker,
    },
    data() {
        return {
            isExporting:       false,
            prototypes:        [],
            selectedProject:   null,
            selectedPrototype: null,
            exportFilter:      'all',
        }
    },
    computed: {
        canExport() {
            return this.selectedProject && this.selectedPrototype && !this.isExporting;
        },
    },
    methods: {
        onProjectChange(projectId) {
            this.selectedProject = projectId;
        },
        onPrototypeChange(prototypeId) {
            this.selectedPrototype = prototypeId;
        },
        async buildRenditionOptions(artboards) {
            const renditionOptions = [];

            const folder = await fs.getTemporaryFolder();
            if (!folder) {
                throw new Error('Unable to access the temporary system folder.');
            }

            for (let i = 0; i < artboards.length; i++) {
                let normalizedName = artboards[i].name.toLowerCase()
                    .replace(/[^\w ]+/g, '')
                    .replace(/ +/g, '_');

                // create a file that will store the rendition
                let file = await folder.createFile(normalizedName + i + '.png', { overwrite: true });

                // set options for rendering a PNG
                renditionOptions.push({
                    node:       artboards[i],
                    outputFile: file,
                    type:       application.RenditionType.PNG,
                    scale:      1,
                });
            }

            return renditionOptions;
        },
        async exportRenditions() {
            this.isExporting = true;

            var successfullyExported = 0;

            try {
                if (!this.selectedProject || !this.selectedPrototype) {
                    throw new Error('Please make sure to select a project and prototype first.');
                }

                const artboards = this.$getArtboards(this.exportFilter === 'selection');
                if (!artboards.length) {
                    throw new Error('No artboards to export.');
                }

                // create the rendition(s)
                const renditionOptions = await this.buildRenditionOptions(artboards);
                const results          = await application.createRenditions(renditionOptions);

                // fetch all prototype screens to decide later whether to send create or update (replace) request
                const screens = (await ApiClient.Screens.getList(1, 199, {
                    'search[prototypeId]': this.selectedPrototype,
                    'fields': 'id, title, file',
                })).data;

                for (let i = 0; i < results.length; i++) {
                    let formData = new FormData();

                    formData.append('prototypeId', this.selectedPrototype);
                    formData.append('file', results[i].outputFile);
                    formData.append('title', renditionOptions[i].node.name);

                    // check if screen exist
                    let existingScreenId = null;
                    for (let j = screens.length - 1; j >= 0; j--) {
                        if (screens[j].file.original.indexOf(results[i].outputFile.name) >= 0) {
                            existingScreenId = screens[j].id;
                            break;
                        }
                    }

                    let uploadRequest = null;
                    if (existingScreenId) {
                        uploadRequest = ApiClient.$http.put('/screens/' + existingScreenId, formData);
                    } else {
                        uploadRequest = ApiClient.$http.post('/screens', formData);
                    }

                    let response = await uploadRequest;
                    if (response.data && response.data.id) {
                        successfullyExported++;
                    }
                }
            } catch (err) {
                this.$baseApiErrorHandler(err);
            }

            this.isExporting = false;

            if (successfullyExported > 0) {
                this.$router.replace({
                    name: 'export-success',
                    params: {
                        projectId:   this.selectedProject,
                        prototypeId: this.selectedPrototype,
                    },
                });
            }
        },
    },
}
</script>
