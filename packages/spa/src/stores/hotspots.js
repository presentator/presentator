import CommonHelper    from '@/utils/CommonHelper';
import Hotspot         from '@/models/Hotspot';
import HotspotTemplate from '@/models/HotspotTemplate';

export default CommonHelper.createResettableStore({
    namespaced: true,
    initialState() {
        return {
            hotspots:         [],
            hotspotTemplates: [],
        }
    },
    mutations: {
        // hotspots
        setHotspots(state, hotspotsData) {
            state.hotspots = Hotspot.createInstances(hotspotsData);
        },
        addHotspot(state, hotspotData) {
            var hotspot = new Hotspot(hotspotData);

            CommonHelper.pushUnique(state.hotspots, hotspot);
        },
        updateHotspot(state, hotspotData) {
            hotspotData = hotspotData || {};

            var hotspot = CommonHelper.findByKey(state.hotspots, 'id', hotspotData.id);

            if (hotspot) {
                let updatedHotspot = hotspot.clone(hotspotData);

                CommonHelper.removeByKey(state.hotspots, 'id', hotspot.id);

                state.hotspots.push(updatedHotspot);
            }
        },
        removeHotspot(state, id) {
            CommonHelper.removeByKey(state.hotspots, 'id', id);
        },

        // hotspot templates
        setHotspotTemplates(state, hotspotTemplatesData) {
            state.hotspotTemplates = HotspotTemplate.createInstances(hotspotTemplatesData);
        },
        addHotspotTemplate(state, hotspotTemplateData) {
            var hotspotTemplate = new HotspotTemplate(hotspotTemplateData);

            CommonHelper.pushUnique(state.hotspotTemplates, hotspotTemplate);
        },
        updateHotspotTemplate(state, hotspotTemplateData) {
            hotspotTemplateData = hotspotTemplateData || {};

            var hotspotTemplate = CommonHelper.findByKey(state.hotspotTemplates, 'id', hotspotTemplateData.id);

            if (hotspotTemplate) {
                let updatedHotspotTemplate = hotspotTemplate.clone(hotspotTemplateData);

                CommonHelper.removeByKey(state.hotspotTemplates, 'id', hotspotTemplate.id);

                state.hotspotTemplates.push(updatedHotspotTemplate);
            }
        },
        removeHotspotTemplate(state, id) {
            CommonHelper.removeByKey(state.hotspotTemplates, 'id', id);
        },
    },
    actions: {
        // hotspots
        setHotspots(context, hotspotsData) {
            context.commit('setHotspots', hotspotsData);
        },
        appendHotspots(context, hotspotsData) {
            for (let i = hotspotsData.length - 1; i >= 0; i--) {
                context.dispatch('addHotspot', hotspotsData[i]);
            }
        },
        addHotspot(context, hotspotData) {
            context.commit('addHotspot', hotspotData);
        },
        updateHotspot(context, hotspotData) {
            context.commit('updateHotspot', hotspotData);
        },
        removeHotspot(context, id) {
            context.commit('removeHotspot', id);
        },

        // hotspot templates
        setHotspotTemplates(context, hotspotTemplatesData) {
            context.commit('setHotspotTemplates', hotspotTemplatesData);
        },
        appendHotspotTemplates(context, hotspotTemplatesData) {
            for (let i in hotspotTemplatesData) {
                context.dispatch('addHotspotTemplate', hotspotTemplatesData[i]);
            }
        },
        addHotspotTemplate(context, hotspotTemplateData) {
            context.commit('addHotspotTemplate', hotspotTemplateData);
        },
        updateHotspotTemplate(context, hotspotTemplateData) {
            context.commit('updateHotspotTemplate', hotspotTemplateData);
        },
        removeHotspotTemplate(context, id) {
            context.commit('removeHotspotTemplate', id);
        },
    },
    getters: {
        getHotspot: (state) => (id) => {
            return CommonHelper.findByKey(state.hotspots, 'id', id);
        },
        getHotspotTemplate: (state) => (id) => {
            return CommonHelper.findByKey(state.hotspotTemplates, 'id', id);
        },
        getHotspotsForScreen: (state, getters) => (screenId) => {
            var result = [];

            for (let i = state.hotspots.length - 1; i >= 0; i--) {
                // attached directly to the active screen
                if (state.hotspots[i].screenId == screenId) {
                    result.unshift(state.hotspots[i]);
                } else if (state.hotspots[i].hotspotTemplateId) {
                    let template = getters.getHotspotTemplate(state.hotspots[i].hotspotTemplateId);

                    // attached to a linked templates
                    if (template && CommonHelper.inArray(template.screenIds, screenId)) {
                        result.unshift(state.hotspots[i]);
                    }
                }
            }

            return result;
        },
    },
});
