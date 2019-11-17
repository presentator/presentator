<template>
    <div class="panel panel-lg">
        <panel-header></panel-header>

        <div class="spacer"></div>

        <p>Successfully exported renditions.</p>
        <p><a :href="previewLink">View project in Presentator.</a></p>

        <footer>
            <button uxp-variant="primary" @click="$closePluginDialog()">Close</button>
            <router-link :to="{'name': 'export'}" uxp-variant="primary" tag="button">Back</router-link>
        </footer>
    </div>
</template>

<script>
const storageHelper = require('xd-storage-helper');
const PanelHeader   = require('@/PanelHeader.vue').default;

module.exports = {
    name: 'route-export-success',
    components: {
        'panel-header': PanelHeader,
    },
    props: {
        projectId: {
            required: true,
        },
        prototypeId: {
            required: true,
        },
    },
    data() {
        return {
            appUrl: '',
        }
    },
    computed: {
        previewLink() {
            return `${this.appUrl}/#/projects/${this.projectId}/prototypes/${this.prototypeId}`;
        },
    },
    beforeMount() {
        this.loadAppUrl();
    },
    methods: {
        async loadAppUrl() {
            this.appUrl = await storageHelper.get('appUrl', 'https://app.presentator.io');

            // remove spaces and trailing slashes
            this.appUrl = this.appUrl.trim().replace(/\/+$/, '');
        },
    },
}
</script>
