import Vue          from 'vue';
import CommonHelper from '@/utils/CommonHelper';

export default CommonHelper.createResettableStore({
    namespaced: true,
    initialState() {
        return {
            errors: [],
        }
    },
    mutations: {
        setErrors(state, errors) {
            Vue.set(state, 'errors', errors || {});
        },
        removeError(state, name) {
            Vue.delete(state.errors, name);
        },
    },
    actions: {
        setErrors(context, errors) {
            context.commit('setErrors', errors);
        },
        removeError(context, name) {
            context.commit('removeError', name);
        },
    },
    getters: {
        getError: (state) => (name) => {
            return state.errors[name] || '';
        },
    },
});
