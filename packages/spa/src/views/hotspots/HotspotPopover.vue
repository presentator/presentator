<template>
    <div class="popover-holder hotspot-popover-holder">
        <div v-show="isActive" class="popover-overlay" @click.stop.prevent="close()"></div>

        <div ref="popover"
            class="popover hotspot-popover"
            :class="{'active': isActive}"
        >
            <form @submit.prevent="submitForm()">
                <form-field name="type">
                    <div class="input-group">
                        <label class="input-addon bg-border txt-default">{{ $t('Link to') }}</label>
                        <select :id="'hotspot_type_' + hotspot.id" v-model="type" required>
                            <option value="screen">{{ $t('Screen') }}</option>
                            <option value="overlay">{{ $t('Screen as overlay') }}</option>
                            <option value="prev">{{ $t('Prev screen in series') }}</option>
                            <option value="next">{{ $t('Next screen in series') }}</option>
                            <option value="back">{{ $t('Back (last visited screen)') }}</option>
                            <option value="scroll">{{ $t('Position (scroll to)') }}</option>
                            <option v-if="$getAppConfig('VUE_APP_ALLOW_HOTSPOTS_URL') << 0" value="url">{{ $t('External URL') }}</option>
                        </select>
                    </div>
                </form-field>

                <form-field v-show="type === 'screen' || type === 'overlay'" name="settingScreenId">
                    <label>{{ $t('Screen') }}</label>
                    <div tabindex="0"
                        class="btn btn-block btn-light-border btn-dropdown"
                        @keydown.space.prevent="$refs.screensDropdown ? $refs.screensDropdown.show() : true"
                        @keydown.enter.prevent="$refs.screensDropdown ? $refs.screensDropdown.toggle() : true"
                    >
                        <span class="txt">{{ selectedScreen ? selectedScreen.title : $t('Select a screen...') }}</span>

                        <toggler ref="screensDropdown"
                            class="dropdown dropdown-thumbs-list"
                            @show="onScreensDropdownShow"
                        >
                            <figure v-for="screen in orderedScreens"
                                :key="'hotspot_screen_' + screen.id"
                                class="thumb thumb-handle"
                                :class="{'active': screen.id == screenId}"
                                v-tooltip.top="screen.title"
                                @click.stop.prevent="selectScreen(screen.id)"
                            >
                                <img v-if="screen.getImage('small')" :src="screen.getImage('small')" :alt="screen.title" class="img">
                                <i v-else class="fe fe-image img"></i>
                            </figure>
                        </toggler>
                    </div>
                </form-field>

                <div class="row">
                    <div v-show="type === 'overlay'" class="col-6">
                        <form-field name="settingOverlayPosition">
                            <label :for="'hotspot_screen_overlay_position_' + hotspot.id">{{ $t('Position') }}</label>
                            <select :id="'hotspot_screen_overlay_position_' + hotspot.id" v-model="overlayPosition">
                                <option value="centered">{{ $t('Centered') }}</option>
                                <option value="top-left">{{ $t('Top Left') }}</option>
                                <option value="top-center">{{ $t('Top Center') }}</option>
                                <option value="top-right">{{ $t('Top Right') }}</option>
                                <option value="bottom-left">{{ $t('Bottom Left') }}</option>
                                <option value="bottom-center">{{ $t('Bottom Center') }}</option>
                                <option value="bottom-right">{{ $t('Bottom Right') }}</option>
                            </select>
                        </form-field>
                    </div>
                    <div v-show="type !== 'url' && type !== 'scroll'"
                        :class="type === 'overlay' ? 'col-6' : 'col-12'"
                    >
                        <form-field name="settingTransition">
                            <label :for="'hotspot_transition_' + hotspot.id">{{ $t('Transition') }}</label>
                            <select :id="'hotspot_transition_' + hotspot.id" v-model="transition">
                                <option value="none">{{ $t('None') }}</option>
                                <option value="fade">{{ $t('Fade') }}</option>
                                <option value="slide-left">{{ $t('Slide left') }}</option>
                                <option value="slide-right">{{ $t('Slide right') }}</option>
                                <option value="slide-top">{{ $t('Slide top') }}</option>
                                <option value="slide-bottom">{{ $t('Slide bottom') }}</option>
                            </select>
                        </form-field>
                    </div>
                </div>

                <div v-show="type === 'overlay'" class="form-group form-group-sm">
                    <label>{{ $t('Offset')}} <small class="txt-hint">(px)</small></label>
                    <div class="input-group">
                        <div class="input-addon bg-light-border">T</div>
                        <input type="number" v-model.number="offsetTop" v-tooltip.bottom="$t('Top offset')">
                        <div class="input-addon bg-light-border">B</div>
                        <input type="number" v-model.number="offsetBottom" v-tooltip.bottom="$t('Bottom offset')">
                        <div class="input-addon bg-light-border">L</div>
                        <input type="number" v-model.number="offsetLeft" v-tooltip.bottom="$t('Left offset')">
                        <div class="input-addon bg-light-border">R</div>
                        <input type="number" v-model.number="offsetRight" v-tooltip.bottom="$t('Right offset')">
                    </div>
                </div>

                <div v-show="type === 'scroll'" class="row">
                    <div class="col-6">
                        <form-field name="settingScrollTop">
                            <label :for="'hotspot_scroll_top_' + hotspot.id">{{ $t('Vertical position') }}</label>
                            <div class="input-group">
                                <input type="number" :id="'hotspot_scroll_top_' + hotspot.id" v-model.number="scrollTop" min="0">
                                <div class="input-addon">px</div>
                            </div>
                        </form-field>
                    </div>
                    <div class="col-6">
                        <form-field name="settingScrollLeft">
                            <label :for="'hotspot_scroll_left_' + hotspot.id">{{ $t('Horizontal position') }}</label>
                            <div class="input-group">
                                <input type="number" :id="'hotspot_scroll_left_' + hotspot.id" v-model.number="scrollLeft" min="0">
                                <div class="input-addon">px</div>
                            </div>
                        </form-field>
                    </div>
                </div>

                <form-field v-show="type === 'overlay'" name="settingFixOverlay">
                    <input type="checkbox" :id="'hotspot_screen_overlay_fix_position' + hotspot.id" v-model="fixOverlay">
                    <label :for="'hotspot_screen_overlay_fix_position' + hotspot.id">{{ $t('Fix position of overlay') }}</label>
                </form-field>

                <form-field v-show="type === 'overlay'" name="settingOutsideClose">
                    <input type="checkbox" :id="'hotspot_screen_overlay_outside_click_' + hotspot.id" v-model="outsideClose">
                    <label :for="'hotspot_screen_overlay_outside_click_' + hotspot.id">{{ $t('Close on outside click') }}</label>
                </form-field>

                <form-field v-if="$getAppConfig('VUE_APP_ALLOW_HOTSPOTS_URL') << 0" v-show="type === 'url'" name="settingUrl">
                    <label :for="'hotspot_external_url_' + hotspot.id">URL</label>
                    <input type="url" :id="'hotspot_external_url_' + hotspot.id" v-model="url" :placeholder="$t('eg.') + ' https://google.com'">
                </form-field>

                <div class="form-group m-b-10">
                    <input type="checkbox" :id="'hotspot_include_in_template_toggle_' + hotspot.id" v-model="includeInTemplate">
                    <label :for="'hotspot_include_in_template_toggle_' + hotspot.id">
                        <span class="txt">{{ $t('Include in template') }}</span>
                        <i class="fe fe-info link-hint m-l-5" v-tooltip.right="$t('Allows reusing the hotspot in other screens')"></i>
                    </label>
                </div>
                <div class="form-group-section m-b-0" v-if="includeInTemplate">
                    <div class="form-group form-group-sm" :class="{'m-b-5' : template === 'new'}">
                        <select v-model="template">
                            <option v-for="hotspotTemplate in hotspotTemplates" :value="hotspotTemplate.id">
                                {{ hotspotTemplate.title }}
                            </option>
                            <option value="new">+ {{ $t('New template') }}</option>
                        </select>
                    </div>
                    <div v-if="template === 'new'" class="form-group form-group-sm">
                        <input type="text" :placeholder="$t('Template name') + ' *'" v-model.trim="newTemplateTitle" required minlength="1" maxlength="255">
                    </div>
                </div>

                <hr class="m-t-20 m-b-20">

                <div class="flex-block">
                    <button type="button" class="btn btn-light-border" @click.prevent="close()">
                        <span class="txt">{{ $t('Cancel') }}</span>
                    </button>
                    <span v-if="hotspot.id"
                        class="link-fade txt-danger m-l-small"
                        @click.prevent="deleteHotspot()"
                    >
                        {{ $t('Delete') }}
                    </span>
                    <div class="flex-fill-block"></div>
                    <button type="submit"
                        class="btn btn-primary btn-cons-sm btn-loader"
                        :class="{'btn-loader-active': isProcessing}"
                    >
                        <span class="txt">{{ hotspot.id ? $t('Update') : $t('Create') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import Hotspot      from '@/models/Hotspot';

export default {
    name: 'hotspot-popover',
    data() {
        return {
            isActive:                  false,
            repositionToElem:          null,
            hotspot:                   new Hotspot,
            isProcessing:              false,
            type:                      'screen',
            screenId:                  null,
            url:                       '',
            transition:                'none',
            overlayPosition:           'top-left',
            fixOverlay:                false,
            offsetTop:                 0,
            offsetBottom:              0,
            offsetLeft:                0,
            offsetRight:               0,
            outsideClose:              true,
            scrollTop:                 0,
            scrollLeft:                0,
            includeInTemplate:         false,
            template:                  1,
            isCreatingHotspotTemplate: false,
            newTemplateTitle:          '',
        }
    },
    computed: {
        ...mapState({
            activePrototypeId:   state => state.prototypes.activePrototypeId,
            activeScreenId:      state => state.screens.activeScreenId,
            hotspots:            state => state.hotspots.hotspots,
            hotspotTemplates:    state => state.hotspots.hotspotTemplates,
        }),
        ...mapGetters({
            'orderedScreens':     'screens/orderedScreens',
            'getScreen':          'screens/getScreen',
            'getHotspot':         'hotspots/getHotspot',
            'getHotspotTemplate': 'hotspots/getHotspotTemplate',
        }),

        selectedScreen() {
            return this.getScreen(this.screenId);
        },
    },
    watch: {
        includeInTemplate(newVal, oldVal) {
            this.$nextTick(() => {
                this.reposition();
            });
        },
        type(newVal, oldVal) {
            this.$nextTick(() => {
                this.reposition();
            });
        },
        screenId(newVal, oldVal) {
            this.removeError('settingScreenId');
        },
    },
    mounted() {
        document.addEventListener('scroll', this.onEventPopoverReposition, {
            capture: true,
            passive: true,
        });

        window.addEventListener('resize', this.onEventPopoverReposition, {
            passive: true,
        });
    },
    beforeDestroy() {
        document.removeEventListener('scroll', this.onEventPopoverReposition, {
            capture: true,
        });

        window.removeEventListener('resize', this.onEventPopoverReposition);
    },
    methods: {
        ...mapActions({
            'setErrors':          'form-field/setErrors',
            'removeError':        'form-field/removeError',
            'removeHotspot':      'hotspots/removeHotspot',
            'addHotspotTemplate': 'hotspots/addHotspotTemplate',
        }),

        open(hotspot, repositionToElem) {
            if (this.isActive) {
                return;
            }

            this.isActive = true;

            if (hotspot instanceof Hotspot) {
                this.hotspot = hotspot;
            } else {
                this.hotspot = new Hotspot(hotspot);
            }

            this.reloadForm()

            if (repositionToElem) {
                this.repositionToElem = repositionToElem;
                this.reposition(repositionToElem);
            }

            this.$emit('opened');
        },
        close() {
            if (!this.isActive) {
                return;
            }

            this.isActive = false;

            if (!this.hotspot.id) {
                this.removeHotspot(this.hotspot.id);
            }

            this.$emit('closed');
        },

        // position
        // ---
        onEventPopoverReposition(e) {
            if (this.isActive) {
                this.reposition();
            }
        },
        reposition(repositionToElem) {
            repositionToElem = repositionToElem || this.repositionToElem;
            if (!this.isActive || !repositionToElem) {
                return;
            }

            var popover   = this.$refs.popover;
            var elPos     = repositionToElem.getBoundingClientRect();
            var tolerance = 5;
            var top       = elPos.top - tolerance;
            var left      = elPos.left + elPos.width + tolerance;

            // reset popover position
            popover.style.left = '0px';
            popover.style.top  = '0px';

            // right screen edge constraint
            if (left + popover.offsetWidth > document.documentElement.clientWidth) {
                left = elPos.left - popover.offsetWidth - tolerance;
            }

            // left screen edge constraint
            left = left >= 0 ? left : 0;

            // bottom screen edge constraint
            if (top + popover.offsetHeight > document.documentElement.clientHeight) {
                top = document.documentElement.clientHeight - popover.offsetHeight;
            }

            // top screen edge constraint
            top = top >= 0 ? top : 0;

            // set new popover position
            popover.style.left = left + 'px';
            popover.style.top  = top + 'px';
        },

        // form
        // ---
        selectScreen(screenId) {
            this.screenId = screenId;
            if (this.$refs.screensDropdown) {
                this.$refs.screensDropdown.hide();
            }
        },
        reloadForm() {
            this.type              = this.hotspot.type;
            this.screenId          = CommonHelper.getNestedVal(this.hotspot, 'settings.screenId', null);
            this.url               = CommonHelper.getNestedVal(this.hotspot, 'settings.url', '');
            this.transition        = CommonHelper.getNestedVal(this.hotspot, 'settings.transition', 'none');
            this.overlayPosition   = CommonHelper.getNestedVal(this.hotspot, 'settings.overlayPosition', 'top-left');
            this.fixOverlay        = CommonHelper.getNestedVal(this.hotspot, 'settings.fixOverlay') ? true : false;
            this.offsetTop         = CommonHelper.getNestedVal(this.hotspot, 'settings.offsetTop', 0) << 0;
            this.offsetBottom      = CommonHelper.getNestedVal(this.hotspot, 'settings.offsetBottom', 0) << 0;
            this.offsetLeft        = CommonHelper.getNestedVal(this.hotspot, 'settings.offsetLeft', 0) << 0;
            this.offsetRight       = CommonHelper.getNestedVal(this.hotspot, 'settings.offsetRight', 0) << 0;
            this.scrollTop         = CommonHelper.getNestedVal(this.hotspot, 'settings.scrollTop', 0) << 0;
            this.scrollLeft        = CommonHelper.getNestedVal(this.hotspot, 'settings.scrollLeft', 0) << 0;
            this.outsideClose      = CommonHelper.getNestedVal(this.hotspot, 'settings.outsideClose', true) ? true : false;
            this.includeInTemplate = this.hotspot.hotspotTemplateId > 0;
            this.template          = this.hotspot.hotspotTemplateId || (this.hotspotTemplates[0] ? this.hotspotTemplates[0].id : 'new');
            this.newTemplateTitle  = '';

            this.setErrors({});
        },
        submitForm() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            this.createHotspotTemplate().finally(() => {
                var isUpdate = this.hotspot.id > 0;

                var selectedTemplate = this.includeInTemplate ? this.getHotspotTemplate(this.template) : null;

                var requestData = {
                    'screenId':               selectedTemplate ? null : this.activeScreenId,
                    'hotspotTemplateId':      selectedTemplate ? selectedTemplate.id : null,
                    'left':                   this.hotspot.left,
                    'top':                    this.hotspot.top,
                    'width':                  this.hotspot.width,
                    'height':                 this.hotspot.height,
                    'type':                   this.type,
                    'settingScreenId':        this.screenId,
                    'settingUrl':             this.url,
                    'settingOverlayPosition': this.overlayPosition,
                    'settingFixOverlay':      this.fixOverlay,
                    'settingTransition':      this.transition,
                    'settingOffsetTop':       this.offsetTop << 0,
                    'settingOffsetBottom':    this.offsetBottom << 0,
                    'settingOffsetLeft':      this.offsetLeft << 0,
                    'settingOffsetRight':     this.offsetRight << 0,
                    'settingScrollTop':       this.scrollTop << 0,
                    'settingScrollLeft':      this.scrollLeft << 0,
                    'settingOutsideClose':    this.outsideClose,
                };

                var requestPromise = null;
                if (isUpdate) {
                    requestPromise = ApiClient.Hotspots.update(this.hotspot.id, requestData);
                } else {
                    requestPromise = ApiClient.Hotspots.create(requestData);
                }

                requestPromise.then((response) => {
                    this.hotspot.load(response.data);

                    if (selectedTemplate) {
                        this.linkToHotspotTemplate(selectedTemplate.id, this.activeScreenId);
                    }

                    this.$toast(this.$t('Successfully updated hotspot.'));

                    this.reloadForm();

                    this.close();

                    this.$emit('hotspotUpdated', this.hotspot);
                }).catch((err) => {
                    this.$errResponseHandler(err);
                }).finally(() => {
                    this.isProcessing = false;
                });
            });
        },
        onScreensDropdownShow() {
            this.$nextTick(() => {
                var activeThumb = this.$el.querySelector('.dropdown-thumbs-list .thumb.active');
                if (activeThumb) {
                    activeThumb.scrollIntoView({block: 'nearest'});
                }
            });
        },
        deleteHotspot() {
            if (
                !this.hotspot.id ||
                !window.confirm(this.$t('Do you really want to delete the selected hotspot?'))
            ) {
                return;
            }

            // optimistic delete
            this.close();
            this.removeHotspot(this.hotspot.id);
            this.$toast(this.$t('Successfully deleted hotspot.'));

            // actual delete
            ApiClient.Hotspots.delete(this.hotspot.id);
        },

        // template
        // ---
        createHotspotTemplate() {
            if (
                this.isCreatingHotspotTemplate ||
                !this.newTemplateTitle ||
                !this.includeInTemplate ||
                this.template !== 'new'
            ) {
                return Promise.resolve();
            }

            this.isCreatingHotspotTemplate = true;

            return ApiClient.HotspotTemplates.create({
                title:       this.newTemplateTitle,
                prototypeId: this.activePrototypeId,
            }, {
                'expand': 'screenIds',
            }).then((response) => {
                this.addHotspotTemplate(response.data);

                this.newTemplateTitle = ''; // reset

                this.template = this.hotspotTemplates[this.hotspotTemplates.length - 1].id;
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isCreatingHotspotTemplate = false;

                return Promise.resolve();
            });
        },
        linkToHotspotTemplate(templateId, screenId) {
            screenId = screenId || this.activeScreenId;

            var template = this.getHotspotTemplate(templateId);
            if (
                !template ||
                CommonHelper.inArray(template.screenIds, screenId)
            ) {
                return;
            }

            // optimistic linking
            template.screenIds.push(screenId);

            // actual linking
            ApiClient.HotspotTemplates.linkScreen(template.id, screenId);
        },
    },
}
</script>
