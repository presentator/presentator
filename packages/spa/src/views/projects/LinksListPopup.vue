<template>
    <popup ref="popup">
        <template v-slot:header>
            <h4 class="title">{{ $t('Project links') }}</h4>
        </template>
        <template v-slot:content>
            <div v-if="isLoading" class="block txt-center txt-hint">
                <span class="loader"></span>
            </div>

            <div v-if="!isLoading && !links.length" class="block txt-center txt-hint">
                {{ $t('No preview links found.') }}
            </div>

            <div v-if="!isLoading && links.length" class="project-links-list">
                <div v-for="link in links"
                    :key="link.id"
                    class="project-link"
                >
                    <div class="content">
                        <div class="link">
                            <a :href="link.baseUrl + '/' + link.slug"
                                target="_blank"
                                rel="noopener"
                                v-tooltip.top="$t('Open in new tab')"
                            >
                                {{ link.baseUrl }}/<strong>{{ link.slug }}</strong>
                            </a>
                        </div>

                        <div class="clearfix m-b-10"></div>

                        <small class="label"
                            :class="link.allowComments ? 'label-transp-success': 'label-light-border txt-dark-border'"
                        >{{ $t('Comments') }}</small>

                        <small class="label"
                            :class="link.allowGuideline ? 'label-transp-success': 'label-light-border txt-dark-border'"
                        >{{ $t('Guideline') }}</small>

                        <small class="label"
                            :class="link.passwordProtected ? 'label-transp-success': 'label-light-border txt-dark-border'"
                        >{{ $t('Password') }}</small>

                        <small class="label"
                            :class="link.prototypes.length ? 'label-transp-success': 'label-light-border txt-dark-border'"
                        >{{ $t('Restricted') }}</small>
                    </div>
                    <div class="list-ctrls">
                        <span class="list-ctrl-item" v-tooltip.top="$t('Share')" @click.prevent="shareLink(link)">
                            <i class="fe fe-share-2"></i>
                        </span>
                        <span class="list-ctrl-item" v-tooltip.top="$t('Copy URL')" @click.prevent="copyLink(link)">
                            <i class="fe fe-copy"></i>
                        </span>
                        <span class="list-ctrl-item" v-tooltip.top="$t('Edit')" @click.prevent="editLink(link)">
                            <i class="fe fe-settings"></i>
                        </span>
                        <span class="list-ctrl-item ctrl-danger" v-tooltip.top="$t('Delete')" @click.prevent="deleteLink(link)">
                            <i class="fe fe-trash"></i>
                        </span>
                    </div>
                </div>
            </div>
        </template>
        <template v-slot:footer>
            <button type="button" class="btn btn-light-border" @click.prevent="close()">
                <span class="txt">{{ $t('Cancel') }}</span>
            </button>
            <button type="button" class="btn btn-success btn-cons" @click.prevent="createLink()">
                <i class="fe fe-plus"></i>
                <span class="txt">{{ $t('New project link') }}</span>
            </button>
        </template>
    </popup>
</template>

<script>
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import ProjectLink  from '@/models/ProjectLink';
import Popup        from '@/components/Popup';

export default {
    name: 'links-list-popup',
    components: {
        'popup': Popup,
    },
    props: {
        projectId: {
            required: true,
        }
    },
    data() {
        return {
            links:     [],
            isLoading: false,
        }
    },
    methods: {
        open() {
            this.loadLinks();

            this.$refs.popup.open();

            this.$emit('open');
        },
        close() {
            this.$refs.popup.close();

            this.$emit('close');
        },
        loadLinks(callback) {
            this.isLoading = true;

            ApiClient.ProjectLinks.getList(1, 100, {
                'search[projectId]': this.projectId,
            }).then((response) => {
                this.links = ProjectLink.createInstances(response.data);

                if (CommonHelper.isFunction(callback)) {
                    this.$nextTick(() => { callback(response); });
                }
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoading = false;
            });
        },
        shareLink(link) {
            this.$emit('shareProjectLink', link);
        },
        editLink(link) {
            this.$emit('editProjectLink', link);
        },
        createLink() {
            this.$emit('createProjectLink');
        },
        copyLink(link) {
            if (CommonHelper.copyToClipboard(link.fullUrl)) {
                this.$toast(this.$t('Successfully copied {text} to clipboard.', {text: link.fullUrl}));
            } else {
                this.$toast(this.$t('Failed copying {text} to clipboard.', {text: link.fullUrl}), 'danger');
            }
        },
        deleteLink(link) {
            if (!window.confirm(this.$t('Do you really want to delete the selected project link?'))) {
                return;
            }

            // optimistic delete
            this.$toast(this.$t('Successfully deleted project link.'));
            CommonHelper.removeByKey(this.links, 'id', link.id);

            // actual delete
            ApiClient.ProjectLinks.delete(link.id);
        },
    },
}
</script>
