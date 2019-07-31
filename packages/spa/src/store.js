import Vue                from 'vue';
import Vuex               from 'vuex';
import formFieldStore     from '@/stores/form-field';
import toastStore         from '@/stores/toast';
import userStore          from '@/stores/user';
import notificationsStore from '@/stores/notifications';
import prototypesStore    from '@/stores/prototypes';
import screensStore       from '@/stores/screens';
import commentsStore      from '@/stores/comments';
import hotspotsStore      from '@/stores/hotspots';
import previewStore       from '@/stores/preview';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        'form-field':    formFieldStore,
        'toast':         toastStore,
        'user':          userStore,
        'notifications': notificationsStore,
        'prototypes':    prototypesStore,
        'screens':       screensStore,
        'comments':      commentsStore,
        'hotspots':      hotspotsStore,
        'preview':       previewStore,
    },
});
