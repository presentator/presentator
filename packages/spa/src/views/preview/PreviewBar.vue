<template>
    <nav class="floating-bar preview-bar" :class="{'active': isActive}">
        <div class="visibility-toggle" @click.prevent="toggle()">
            <span class="txt">{{ isActive ? $t('Hide') : $t('Show') }}</span>
        </div>

        <div class="nav nav-left">
            <slot name="left"></slot>
        </div>

        <div class="nav nav-center">
            <router-link
                v-if="projectLink.allowGuideline"
                :to="{name: 'preview-guideline', params: {slug: projectLink.slug}, query: {}}"
                class="ctrl-item ctrl-item-circle ctrl-item-warning"
                active-class="highlight"
                v-tooltip.top="$t('Guideline (G)')"
                v-shortcut.71="selfClick"
            >
                <i class="fe fe-book-open"></i>
            </router-link>
            <router-link
                :to="{
                    name:   'preview-prototype',
                    params: Object.assign({}, $route.params, {slug: projectLink.slug, prototypeId: activePrototypeId}),
                    query:  Object.assign({}, $route.query, {mode: 'preview'}),
                }"
                class="ctrl-item ctrl-item-circle ctrl-item-success"
                exact-active-class="highlight"
                v-tooltip.top="$t('Preview mode (P)')"
                v-shortcut.80="selfClick"
            >
                <i class="fe fe-eye"></i>
            </router-link>
            <router-link
                v-if="projectLink.allowComments"
                :to="{
                    name:   'preview-prototype',
                    params: Object.assign({}, $route.params, {slug: projectLink.slug, prototypeId: activePrototypeId}),
                    query:  Object.assign({}, $route.query, {mode: 'comments'}),
                }"
                class="ctrl-item ctrl-item-circle ctrl-item-danger"
                exact-active-class="highlight"
                v-tooltip.top="$t('Comments mode (C)')"
                v-shortcut.67="selfClick"
            >
                <span v-if="activeUnreadComments.length" class="beacon beacon-danger"></span>

                <i class="fe fe-message-circle"></i>
            </router-link>
        </div>

        <div class="nav nav-right">
            <slot name="right"></slot>

            <div class="ctrl-item ctrl-item-circle ctrl-item-settings">
                <div v-tooltip.top="$t('Preview info')">
                    <i class="fe fe-info"></i>
                </div>

                <preview-info-popover
                    ref="projectInfoPopover"
                    class="transform-bottom-right"
                    :project="project"
                ></preview-info-popover>
            </div>
        </div>
    </nav>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import AppConfig          from '@/utils/AppConfig';
import ClientStorage      from '@/utils/ClientStorage';
import Project            from '@/models/Project';
import ProjectLink        from '@/models/ProjectLink';
import PreviewInfoPopover from '@/views/preview/PreviewInfoPopover';

export default {
    name: 'preview-bar',
    components: {
        'preview-info-popover': PreviewInfoPopover,
    },
    props: {
        project: {
            type:     Project,
            required: true,
        },
        projectLink: {
            type:     ProjectLink,
            required: true,
        },
    },
    data() {
        return {
            isActive: true,
        }
    },
    computed: {
        ...mapState({
            activePrototypeId: state => state.prototypes.activePrototypeId,
            activeScreenId:    state => state.screens.activeScreenId,
        }),
        ...mapGetters({
            getUnreadCommentsForScreen: 'notifications/getUnreadCommentsForScreen',
        }),

        activeUnreadComments() {
            return this.getUnreadCommentsForScreen(this.activeScreenId);
        },
    },
    mounted() {
        var storedState = ClientStorage.getItem(AppConfig.get('VUE_APP_PREVIEW_BAR_VISIBLITY_STORAGE_KEY'), true);

        if (storedState) {
            this.show();
        } else {
            this.hide();
        }
    },
    methods: {
        show() {
            this.isActive = true;

            ClientStorage.setItem(AppConfig.get('VUE_APP_PREVIEW_BAR_VISIBLITY_STORAGE_KEY'), true);

            this.$emit('show');
        },
        hide() {
            this.isActive = false;

            ClientStorage.setItem(AppConfig.get('VUE_APP_PREVIEW_BAR_VISIBLITY_STORAGE_KEY'), false);

            this.$emit('hide');
        },
        toggle() {
            if (this.isActive) {
                this.hide();
            } else {
                this.show();
            }
        },
        goToGuideline() {
            this.$router.push({
                name: 'preview-guideline',
                params: {
                    slug: this.projectLink.slug,
                },
            });
        },
        selfClick(e, el) {
            if (el) {
                el.click();
            }
        },
    },
}
</script>
