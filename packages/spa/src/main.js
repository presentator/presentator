import Vue          from 'vue';
import App          from './App';
import store        from './store';
import router       from './router';
import { i18n }     from './i18n';
import VueHelper    from './utils/VueHelper';
import Tooltip      from './directives/Tooltip';
import OutsideClick from './directives/OutsideClick';
import Scale        from './directives/Scale';
import Shortcut     from './directives/Shortcut';
import FormField    from './components/FormField';
import Alert        from './components/Alert';
import Toggler      from './components/Toggler';

Vue.config.productionTip = false;

Vue.use(VueHelper);

// common custom directives
Vue.use(Tooltip);
Vue.use(OutsideClick);
Vue.use(Scale);
Vue.use(Shortcut);

// common custom components
Vue.component('form-field', FormField);
Vue.component('alert', Alert);
Vue.component('toggler', Toggler);

new Vue({
    store:  store,
    router: router,
    i18n:   i18n,
    render: h => h(App),
}).$mount('#app');
