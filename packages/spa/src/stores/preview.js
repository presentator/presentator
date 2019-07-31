import AppConfig     from '@/utils/AppConfig';
import CommonHelper  from '@/utils/CommonHelper';
import ClientStorage from '@/utils/ClientStorage';

const baseStorageKey = AppConfig.get('VUE_APP_BASE_PREVIEW_TOKEN_STORAGE_KEY');

export default CommonHelper.createResettableStore({
    namespaced: true,
    initialState() {
        return {
            previewToken: '',
        }
    },
    mutations: {
        setPreviewToken(state, previewToken) {
            state.previewToken = previewToken;
        },
    },
    actions: {
        setPreviewToken(context, token) {
            context.commit('setPreviewToken', token);

            if (token) {
                let payload = CommonHelper.getJwtPayload(token);

                ClientStorage.setItem(baseStorageKey + payload.slug, token);
            }
        },
        clearPreviewToken(context, slug) {
            // clear local state
            if (slug) {
                ClientStorage.removeItem(baseStorageKey + slug);
            }

            context.dispatch('setPreviewToken', '');
        },
        loadLocalPreviewToken(context, slug) {
            var token = ClientStorage.getItem(baseStorageKey + slug);

            if (CommonHelper.isJwtExpired(token)) {
                context.dispatch('clearPreviewToken', slug);
            } else {
                context.dispatch('setPreviewToken', token);
            }
        },
    },
});
