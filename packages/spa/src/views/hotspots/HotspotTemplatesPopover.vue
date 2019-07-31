<template>
    <toggler class="popover popover-sm hotspot-templates-popover"
        :hideOnChildClick="false"
    >
        <div class="hotspot-templates-list" v-show="hotspotTemplates.length">
            <template v-for="(template, i) in hotspotTemplates">
                <div class="form-group m-0 flex-block flex-nowrap">
                    <input type="checkbox"
                        :id="'link_hotspot_template_' + template.id"
                        :value="template.id"
                        :checked="isHotspotTemplateLinked(template.id)"
                        @change.prevent="toggleLinking(template.id)"
                    >
                    <label :for="'link_hotspot_template_' + template.id">&nbsp;</label>

                    <div class="m-r-5"
                        contenteditable="true"
                        spellcheck="false"
                        autocomplete="off"
                        :title="$t('Click to edit')"
                        :data-placeholder="template.title"
                        :ref="'titleElem' + template.id"
                        @blur="updateHotspotTemplateTitle(template.id)"
                        @keydown.enter.prevent="updateHotspotTemplateTitle(template.id)"
                    >{{ template.title }}</div>

                    <small class="label label-light-border align-self-start"
                        v-tooltip.top="$t('Total hotspots')"
                    >
                        <span class="txt txt-hint">{{ countTemplateHotspots(template.id) }}</span>
                    </small>

                    <div class="flex-fill-block"></div>

                    <div class="list-ctrls align-self-start">
                        <div class="list-ctrl-item ctrl-danger"
                            v-tooltip.top="$t('Delete')"
                            @click.prevent="deleteHotspotTemplate(template.id)"
                        >
                            <i class="fe fe-trash"></i>
                        </div>
                    </div>
                </div>
                <hr class="m-t-10" :class="{'m-b-10': (i+1 != hotspotTemplates.length)}">
            </template>
        </div>

        <button type="button" class="btn btn-sm btn-transp-success btn-block btn-loader"
            :class="{'btn-loader-active': isCreating}"
            @click.prevent="createHotspotTemplate()"
        >
            <i class="fe fe-plus"></i>
            <span class="txt">{{ $t('New hotspot template') }}</span>
        </button>
    </toggler>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import Screen       from '@/models/Screen';

export default {
    name: 'hotspot-templates-popover',
    props: {
        screen: {
            type: Screen,
            default() {
                return this.activeScreen;
            }
        },
    },
    data() {
        return {
            isCreating: false,
        };
    },
    computed: {
        ...mapState({
            hotspots:          state => state.hotspots.hotspots,
            hotspotTemplates:  state => state.hotspots.hotspotTemplates,
        }),

        ...mapGetters({
            'activeScreen':       'screens/activeScreen',
            'getHotspotTemplate': 'hotspots/getHotspotTemplate',
        }),

        linkedHotspotTemplateIds() {
            var result = [];

            for (let i in this.hotspotTemplates) {
                if (CommonHelper.inArray(this.hotspotTemplates[i].screenIds, this.screen.id)) {
                    result.push(this.hotspotTemplates[i].id);
                }
            }

            return result;
        },
    },
    methods: {
        ...mapActions({
            'removeHotspotTemplate': 'hotspots/removeHotspotTemplate',
            'updateHotspotTemplate': 'hotspots/updateHotspotTemplate',
            'addHotspotTemplate':    'hotspots/addHotspotTemplate',
        }),

        countTemplateHotspots(templateId) {
            var result = 0;

            for (let i = this.hotspots.length - 1; i >= 0; i--) {
                if (this.hotspots[i].hotspotTemplateId == templateId) {
                    result++;
                }
            }

            return result;
        },
        deleteHotspotTemplate(id) {
            var template = this.getHotspotTemplate(id);
            if (
                !template ||
                !window.confirm(this.$t('Do you really want to delete template "{title}" and all its hotspots?', {title: template.title}))
            ) {
                return;
            }

            // optimistic delete
            this.$toast(this.$t('Successfully deleted template "{title}".', {title: template.title}));
            this.removeHotspotTemplate(template.id);

            // actual delete
            ApiClient.HotspotTemplates.delete(template.id);
        },
        updateHotspotTemplateTitle(id) {
            var template  = this.getHotspotTemplate(id);
            var titleElem = this.$refs['titleElem' + id] ? this.$refs['titleElem' + id][0] : null;

            if (
                !template ||                           // missing template
                !titleElem ||                          // missing title node element
                titleElem.innerText === template.title // no changes
            ) {
                return;
            }

            // reset title if none is provided
            if (!titleElem.innerText.trim()) {
                titleElem.innerText = template.title;
                return;
            }

            // optimistic update
            titleElem.blur();

            // actual update
            ApiClient.HotspotTemplates.update(template.id, {
                title: titleElem.innerText,
            }, {
                'expand': 'screenIds',
            }).then((response) => {
                this.updateHotspotTemplate(response.data);
                template = this.getHotspotTemplate(template.id); // refresh

                titleElem.innerText = template.title;
            }).catch((err) => {
                this.$errResponseHandler(err);
            });
        },
        createHotspotTemplate(title) {
            if (this.isCreating) {
                return;
            }

            title = title || (this.$t('Template') + ' ' + (this.hotspotTemplates.length + 1))

            this.isCreating = true;

            ApiClient.HotspotTemplates.create({
                title:       title,
                prototypeId: this.screen.prototypeId,
            }, {
                'expand': 'screenIds',
            }).then((response) => {
                this.addHotspotTemplate(response.data);

                this.$toast(this.$t('Successfully created new hotspot template.'));
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isCreating = false;
            });
        },

        // linking
        // ---
        isHotspotTemplateLinked(templateId) {
            return CommonHelper.inArray(this.linkedHotspotTemplateIds, templateId);
        },
        toggleLinking(templateId) {
            if (this.isHotspotTemplateLinked(templateId)) {
                this.unlinkFromHotspotTemplate(templateId);
            } else {
                this.linkToHotspotTemplate(templateId);
            }
        },
        linkToHotspotTemplate(templateId) {
            var screenId = this.screen.id;
            var template = this.getHotspotTemplate(templateId);

            if (
                !template ||
                CommonHelper.inArray(template.screenIds, screenId)
            ) {
                return;
            }

            // optimistic linking
            this.$toast(this.$t('Successfully linked hotspot template.'));
            template.screenIds.push(screenId);

            // actual linking
            ApiClient.HotspotTemplates.linkScreen(template.id, screenId);
        },
        unlinkFromHotspotTemplate(templateId) {
            var screenId = this.screen.id;
            var template = this.getHotspotTemplate(templateId);

            if (
                !template ||
                !CommonHelper.inArray(template.screenIds, screenId)
            ) {
                return;
            }

            // optimistic unlinking
            this.$toast(this.$t('Successfully unlinked hotspot template.'));
            template.screenIds.splice(template.screenIds.indexOf(screenId << 0), 1);

            // actual unlinking
            ApiClient.HotspotTemplates.unlinkScreen(template.id, screenId);
        },
    },
}
</script>

<style lang="scss">
.hotspot-templates-list {
    overflow: auto;
    width: auto;
    padding: 2px 15px;
    margin: -2px -15px 8px;
    max-height: 400px;
}
</style>
