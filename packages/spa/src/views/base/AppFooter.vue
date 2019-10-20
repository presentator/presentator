<template>
    <footer class="app-footer">
        <div class="info">
            <div class="info-item">Presentator v2.2.2</div>

            <a :href="$getAppConfig('VUE_APP_REPO_URL')" class="info-item" target="_blank" rel="noopener">
                <i class="fe fe-github"></i>
                <span class="txt">Github</span>
            </a>

            <div v-if="loggedUser && loggedUser.id"
                class="info-item handle"
                @click.prevent="openFeedbackPopup()"
            >
                <i class="fe fe-life-buoy"></i>
                <span class="txt">{{ $t('Send feedback') }}</span>
            </div>

            <div class="info-item">
                <languages-select></languages-select>
            </div>
        </div>

        <div v-if="$getAppConfig('VUE_APP_SHOW_CREDITS') << 0 ? true : false" class="credits">
            <i18n path="Crafted by {author}">
                <a slot="author" href="https://gani.bg" target="_blank" rel="noopener">Gani</a>
            </i18n>
        </div>

        <relocator>
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
        openFeedbackPopup() {
            if (this.$refs.feedbackPopup) {
                this.$refs.feedbackPopup.open();
            }
        },
    },
}
</script>
