<template>
    <div class="panel">
        <header class="panel-header">
            <div class="logo">Presentator</div>
        </header>

        <div class="spacer"></div>

        <div class="alert success centered">
            The selected screens were successfully exported. <br>
            <a :href="previewLink"><strong>View project in Presentator</strong></a>.
        </div>

        <div class="spacer"></div>

        <footer class="row panel-footer">
            <a class="danger-link" @click.prevent="$logout()">Logout</a>
            <div class="fill-block"></div>
            <router-link :to="{'name': 'export'}" class="button button--secondary" tag="button">Back</router-link>
            <button class="button button--primary" @click.prevent="$closePluginDialog()">Close</button>
        </footer>
    </div>
</template>

<script>
import clientStorage from '@/utils/ClientStorage';

export default {
    name: 'route-export-success',
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
    mounted() {
        this.$resizePluginDialog(/* reset to defaults */);

        this.loadAppUrl();
    },
    methods: {
        loadAppUrl() {
            this.appUrl = clientStorage.getItem('appUrl') || 'https://app.presentator.io';

            // remove spaces and trailing slashes
            this.appUrl = this.appUrl.trim().replace(/\/+$/, '');
        },
    },
}
</script>
