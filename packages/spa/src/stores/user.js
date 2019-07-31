import User          from '@/models/User';
import AppConfig     from '@/utils/AppConfig';
import ClientStorage from '@/utils/ClientStorage';
import CommonHelper  from '@/utils/CommonHelper';

export default CommonHelper.createResettableStore({
    namespaced: true,
    initialState() {
        return {
            user: new User,
        }
    },
    mutations: {
        set(state, userData) {
            state.user = new User(userData);
        },
    },
    actions: {
        set(context, userData) {
            context.commit('set', userData);

            ClientStorage.setItem(AppConfig.get('VUE_APP_USER_DATA_STORAGE_KEY'), context.state.user.export());
        },
        clear(context) {
            context.dispatch('set', {});

            ClientStorage.removeItem(AppConfig.get('VUE_APP_USER_DATA_STORAGE_KEY'));
        },
        loadLocal(context) {
            context.dispatch('set', ClientStorage.getItem(AppConfig.get('VUE_APP_USER_DATA_STORAGE_KEY'), {}));
        },
    },
});
