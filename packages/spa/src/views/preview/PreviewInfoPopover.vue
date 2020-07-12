<template>
    <toggler class="popover popover-sm preview-info-popover no-clip" :hideOnChildClick="false">
        <div class="content txt-center">
            <p><strong>{{ project.title }}</strong></p>

            <p v-if="$getAppConfig('VUE_APP_PROJECT_URL')" class="txt-hint txt-small">
                <i18n path='Presented with {projectLink}.'>
                    <a slot="projectLink" :href="$getAppConfig('VUE_APP_PROJECT_URL')" target="_blank" rel="noopener">Presentator</a>
                </i18n>
            </p>

            <div class="clearfix m-t-small"></div>

            <language-select></language-select>

            <template v-if="$getAppConfig('VUE_APP_SHOW_SPAM_REPORT') << 0">
                <div class="clearfix m-t-small"></div>

                <small class="link-danger link-fade" @click.prevent="openReportPopup">
                    <i class="fe fe-flag m-r-5"></i>
                    <span class="txt">{{ $t('Report') }}</span>
                </small>
            </template>
        </div>

        <relocator>
            <report-popup ref="reportPopup"></report-popup>
        </relocator>
    </toggler>
</template>

<script>
import Project         from '@/models/Project';
import Relocator       from '@/components/Relocator';
import ReportPopup     from '@/views/preview/ReportPopup';
import LanguagesSelect from '@/views/base/LanguagesSelect';

export default {
    name: 'preview-info-popover',
    components: {
        'relocator':       Relocator,
        'report-popup':    ReportPopup,
        'language-select': LanguagesSelect,
    },
    props: {
        project: {
            type:     Project,
            required: true,
        },
    },
    methods: {
        openReportPopup() {
            if (this.$refs.reportPopup) {
                this.$refs.reportPopup.open();
            }
        },
    }
}
</script>
