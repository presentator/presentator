<template>
    <div class="languages-select"
        :class="{'loading': isChanging}"
    >
        <div class="selected-language">
            <span class="txt language-title">{{ activeLanguageTitle }}</span>
            (<span class="txt language-code">{{ activeLanguageCode }}</span>)
        </div>
        <toggler class="dropdown dropdown-sm">
            <div v-for="(title, code) in languages"
                :key="'language_' + code"
                :class="{'active': activeLanguageCode == code}"
                class="dropdown-item"
                @click.prevent="changeLanguage(code)"
            >
                <small class="label language-code m-r-5"
                    :class="activeLanguageCode == code ? 'label-transp-primary' : 'label-light-border'"
                >{{ code }}</small>
                <span class="txt language-title">{{ title }}</span>
            </div>
        </toggler>
    </div>
</template>

<script>
import { changeLanguage, supportedLanguages } from '@/i18n';

export default {
    name: 'languages-select',
    data() {
        return {
            isChanging: false,
            languages:  Object.assign({}, supportedLanguages),
        }
    },
    watch: {
        '$i18n.locale': function (newVal, oldVal) {
            // refresh document title
            if (typeof document._titleFunc === 'function') {
                this.$setDocumentTitle(document._titleFunc);
            }
        },
    },
    computed: {
        activeLanguageCode() {
            return this.$i18n.locale;
        },
        activeLanguageTitle() {
            return this.languages[this.activeLanguageCode];
        },
    },
    methods: {
        changeLanguage(code) {
            this.isChanging = true;

            changeLanguage(code).finally(() => {
                this.isChanging = false;
            });
        },
    },
}
</script>
