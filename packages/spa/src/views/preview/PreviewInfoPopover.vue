<template>
    <toggler class="popover popover-sm preview-info-popover" :hideOnChildClick="false">
        <div class="content m-b-small txt-center">
            <p><strong>{{ project.title }}</strong></p>

            <p v-if="$getAppConfig('VUE_APP_PROJECT_URL')" class="txt-hint txt-small">
                <i18n path='Presented with {projectLink}.'>
                    <a place="projectLink" :href="$getAppConfig('VUE_APP_PROJECT_URL')" target="_blank" rel="noopener">Presentator</a>
                </i18n>
            </p>

            <div class="clearfix m-t-small"></div>

            <language-select></language-select>
        </div>
    </toggler>
</template>

<script>
import Project         from '@/models/Project';
import LanguagesSelect from '@/views/base/LanguagesSelect';

export default {
    name: 'preview-info-popover',
    components: {
        'language-select': LanguagesSelect,
    },
    props: {
        project: {
            type:     Project,
            required: true,
        },
    },
}
</script>
