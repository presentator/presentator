<template>
    <div ref="panel" class="panel">
        <project-picker @changed="onProjectChange" @loaded="onContentLoaded" />

        <div class="spacer"></div>

        <prototype-picker @changed="onPrototypeChange" @loaded="onContentLoaded" :projectId="selectedProject" />

        <div class="spacer"></div>

        <div class="field-group">
           <label for="export_filter" class="section-title">Screens to export</label>
           <div class="row">
               <select v-model="exportFilter" id="export_filter">
                   <option value="all">All screens</option>
                   <option value="selection">Only the selected screen(s)</option>
               </select>

               <select v-model.number="exportScale" id="export_scale" class="scale-select" title="Scale">
                   <option value="1">1x</option>
                   <option value="2">2x</option>
               </select>
           </div>
        </div>

        <div class="spacer"></div>

        <footer class="row panel-footer">
            <a class="danger-link" @click.prevent="!isExporting ? $logout() : null">Logout</a>

            <div class="fill-block"></div>

            <button class="button button--secondary"
                :disabled="isExporting"
                @click.prevent="$closePluginDialog()"
            >Close</button>

            <button class="button button--primary"
                :disabled="!canExport"
                @click.prevent="exportFrames()"
            >{{ isExporting ? 'Exporting...' : 'Export' }}</button>
        </footer>
    </div>
</template>

<style>
.scale-select {
    width: 50px;
}
</style>

<script>
import clientStorage   from '@/utils/ClientStorage';
import apiClient       from '@/utils/ApiClient';
import ProjectPicker   from '@/ProjectPicker';
import PrototypePicker from '@/PrototypePicker';

export default {
    name: 'route-export',
    components: {
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
            exportScale:       clientStorage.getItem('lasExportScale') || 1,
        };
    },
    computed: {
        canExport() {
            return this.selectedProject && this.selectedPrototype && !this.isExporting;
        },
    },
    watch: {
        exportScale(newVal, oldVal) {
            clientStorage.setItem('lasExportScale', newVal);
        },
    },
    mounted() {
        this.updateHeight();
    },
    methods: {
        updateHeight() {
            if (!this.$refs.panel) {
                return;
            }

            this.$refs.panel.classList.add('resizing'); // reset flex block behavior
            this.$resizePluginDialog(null, this.$refs.panel.offsetHeight + 45);
            this.$refs.panel.classList.remove('resizing'); // revert changes
        },
        onContentLoaded() {
            setTimeout(() => {
                this.updateHeight();
            }, 0); // reorder execution queue
        },
        onProjectChange(projectId) {
            this.selectedProject = projectId;
        },
        onPrototypeChange(prototypeId) {
            this.selectedPrototype = prototypeId;
        },
        async exportFrames() {
            if (this.isExporting) {
                return;
            }

            this.isExporting = true;

            var successfullyExported = 0;

            try {
                if (!this.selectedProject || !this.selectedPrototype) {
                    throw new Error('Please make sure to select a project and prototype first.');
                }

                const frames = await this.$getFrames(this.exportFilter === 'selection');
                if (!frames.length) {
                    throw new Error('No frames to export.');
                }

                // fetch all prototype screens to decide later whether to send create or update (replace) request
                const screens = (await apiClient.Screens.getList(1, 199, {
                    'search[prototypeId]': this.selectedPrototype,
                    'fields': 'id, title, file',
                })).data;

                const exportSettings = {
                    'constraint': {
                        'type': 'SCALE',
                        'value': this.exportScale || 1,
                    },
                };

                for (let i = 0; i < frames.length; i++) {
                    let frame     = frames[i];
                    let frameData = await this.$exportFrame(frame.id, exportSettings);

                    if (!frameData) {
                        continue;
                    }

                    let fileName = (frame.name + frame.id).toLowerCase()
                        .replace(/[^\w ]+/g, '')
                        .replace(/ +/g, '_');

                    let formData = new FormData();
                    formData.append('prototypeId', this.selectedPrototype);
                    formData.append('file', new Blob([ frameData ], {type: 'image/png'}), fileName + '.png');
                    formData.append('title', frame.name);

                    // check if screen exist
                    let existingScreenId = null;
                    for (let j = screens.length - 1; j >= 0; j--) {
                        if (screens[j].file.original.indexOf(fileName) > 0) {
                            existingScreenId = screens[j].id;
                            break;
                        }
                    }

                    let uploadRequest = null;
                    if (existingScreenId) {
                        uploadRequest = apiClient.$http.put('/screens/' + existingScreenId, formData);
                    } else {
                        uploadRequest = apiClient.$http.post('/screens', formData);
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
    }
};
</script>
