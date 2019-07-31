import CommonHelper from '@/utils/CommonHelper';
import Prototype    from '@/models/Prototype';

export default CommonHelper.createResettableStore({
    namespaced: true,
    initialState() {
        return {
            activePrototypeId: null,
            prototypes:        [],
        }
    },
    mutations: {
        setActivePrototypeId(state, id) {
            state.activePrototypeId = id;
        },
        setPrototypes(state, prototypesData) {
            state.prototypes = Prototype.createInstances(prototypesData);
        },
        removePrototype(state, id) {
            CommonHelper.removeByKey(state.prototypes, 'id', id);
        },
        addPrototype(state, prototypeData) {
            CommonHelper.pushUnique(state.prototypes, new Prototype(prototypeData));
        },
        updatePrototype(state, prototypeData) {
            prototypeData = prototypeData || {};

            var prototype = CommonHelper.findByKey(state.prototypes, 'id', prototypeData.id);

            if (prototype) {
                let updatedPrototype = prototype.clone(prototypeData);

                CommonHelper.removeByKey(state.prototypes, 'id', prototype.id);

                state.prototypes.push(updatedPrototype);
            }
        },
    },
    actions: {
        setActivePrototypeId(context, id) {
            var prototype = context.getters.getPrototype(id) || context.state.prototypes[0];

            context.commit('setActivePrototypeId', prototype ? prototype.id : null);
        },
        setPrototypes(context, prototypesData) {
            context.commit('setPrototypes', prototypesData);

            if (context.state.prototypes[0]) {
                context.dispatch('setActivePrototypeId', context.state.prototypes[0].id);
            }
        },
        removePrototype(context, id) {
            context.commit('removePrototype', id);

            if (context.state.activePrototypeId == id) {
                // auto set the last prototype as active
                let prototypeId = context.state.prototypes.length ? context.state.prototypes[context.state.prototypes.length - 1].id : null;
                context.dispatch('setActivePrototypeId', prototypeId);
            }
        },
        addPrototype(context, prototypeData) {
            context.commit('addPrototype', prototypeData);
        },
        updatePrototype(context, prototypeData) {
            context.commit('updatePrototype', prototypeData);
        },
    },
    getters: {
        getPrototype: (state) => (id) => {
            return CommonHelper.findByKey(state.prototypes, 'id', id);
        },
        activePrototype: (state, getters) => {
            return getters.getPrototype(state.activePrototypeId);
        }
    },
});
