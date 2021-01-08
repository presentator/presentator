<template>
    <footer class="app-footer">
        <div class="info">
            <a :href="$getAppConfig('VUE_APP_RELEASES_URL')" class="info-item" target="_blank" rel="noopener">
                <span class="txt">Presentator v2.8.2</span>
            </a>

            <a :href="$getAppConfig('VUE_APP_REPO_URL')" class="info-item" target="_blank" rel="noopener">
                <i class="fe fe-github"></i>
                <span class="txt">Github</span>
            </a>

            <div v-if="loggedUser && loggedUser.id && $getAppConfig('VUE_APP_SHOW_SEND_FEEDBACK') << 0"
                class="info-item handle"
                @click.prevent="openFeedbackPopup()"
            >
                <i class="fe fe-life-buoy"></i>
                <span class="txt">{{ $t('Send feedback') }}</span>
            </div>

            <a v-for="(url, name) in getFooterLinks()" :href="url" class="info-item" target="_blank" rel="noopener">
                <span class="txt">{{ name }}</span>
            </a>

            <div class="info-item">
                <languages-select></languages-select>
            </div>
        </div>

        <div v-if="$getAppConfig('VUE_APP_SHOW_CREDITS') << 0" class="credits">
            <i18n path="Crafted by {author}">
                <a slot="author" href="https://gani.bg" target="_blank" rel="noopener">Gani</a>
            </i18n>
        </div>

        <relocator v-if="$getAppConfig('VUE_APP_SHOW_SEND_FEEDBACK') << 0">
            <feedback-popup ref="feedbackPopup"></feedback-popup>
        </relocator>
    </footer>
</template>

<script>
import { mapState }    from 'vuex';
import Relocator       from '@/components/Relocator';
import FeedbackPopup   from '@/views/base/FeedbackPopup';
import LanguagesSelect from '@/views/base/LanguagesSelect';

export default {
    name: 'app-footer',
    components: {
        'relocator':        Relocator,
        'feedback-popup':   FeedbackPopup,
        'languages-select': LanguagesSelect,
    },
    computed: {
        ...mapState({
            loggedUser: state => state.user.user,
        }),
    },
    methods: {
        getFooterLinks() {
            const parts = (this.$getAppConfig('VUE_APP_FOOTER_LINKS') || '')
                .split(',');

            const result = {};

            for (let i = 0; i < parts.length; i++) {
                let linkParts = parts[i].split('|', 2);
                let name = (linkParts[0] || '').trim();
                let link = (linkParts[1] || '').trim();

                if (name.length && link.length) {
                    result[name] = link;
                }
            }

            return result;
        },
        openFeedbackPopup() {
            if (this.$refs.feedbackPopup) {
                this.$refs.feedbackPopup.open();
            }
        },
    },
}
</script>
