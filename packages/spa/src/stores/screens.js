import Vue          from 'vue';
import CommonHelper from '@/utils/CommonHelper';
import Screen       from '@/models/Screen';

export default CommonHelper.createResettableStore({
    namespaced: true,
    initialState() {
        return {
            scaleFactor:     1,
            screens:         [],
            activeScreenId:  null,
            selectedScreens: [],
        }
    },
    mutations: {
        setScaleFactor(state, scaleFactor) {
            state.scaleFactor = scaleFactor;
        },
        setActiveScreenId(state, id) {
            state.activeScreenId = id;
        },
        setScreens(state, screensData) {
            state.screens = Screen.createInstances(screensData);
        },
        removeScreen(state, id) {
            CommonHelper.removeByKey(state.screens, 'id', id);
        },
        addScreen(state, screenData) {
            var screen = new Screen(screenData);

            CommonHelper.pushUnique(state.screens, screen);
        },
        updateScreen(state, screenData) {
            screenData = screenData || {};

            var screen = CommonHelper.findByKey(state.screens, 'id', screenData.id);

            if (screen) {
                let updatedScreen = screen.clone(screenData);

                CommonHelper.removeByKey(state.screens, 'id', screen.id);

                state.screens.push(updatedScreen);
            }
        },

        // screens selection
        selectScreen(state, id) {
            id = id << 0;

            if (id && state.selectedScreens.indexOf(id) < 0) {
                state.selectedScreens.push(id);
            }
        },
        deselectScreen(state, id) {
            id = id << 0;

            if (id && state.selectedScreens.indexOf(id) >= 0) {
                state.selectedScreens.splice(state.selectedScreens.indexOf(id), 1);
            }
        },
    },
    actions: {
        setScaleFactor(context, scaleFactor) {
            context.commit('setScaleFactor', scaleFactor);
        },
        setActiveScreenId(context, id) {
            var screen = context.getters.getScreen(id) || context.state.screens[0];

            context.commit('setActiveScreenId', screen ? screen.id : null);
        },
        setScreens(context, screensData) {
            context.commit('setScreens', screensData);

            if (context.state.screens[0]) {
                context.dispatch('setActiveScreenId', context.state.screens[0].id);
            }

            // reset selection
            context.dispatch('deselectAllScreens');
        },
        appendScreens(context, screensData) {
            for (let i in screensData) {
                context.dispatch('addScreen', screensData[i]);
            }
        },
        removeScreen(context, id) {
            let screen = context.getters.getScreen(id);

            context.commit('removeScreen', screen.id);

            if (context.state.activeScreenId == screen.id) {
                // auto set the last screen as active
                let screenId = context.state.screens.length ? context.state.screens[context.state.screens.length - 1].id : null;
                context.dispatch('setActiveScreenId', screenId);
            }

            // update remaining screens order
            for (let i = context.state.screens.length - 1; i >= 0; i--) {
                if (context.state.screens[i].order >= screen.order) {
                    Vue.set(context.state.screens[i], 'order', context.state.screens[i].order - 1);
                }
            }

            // deselect
            context.dispatch('deselectScreen', id);
        },
        addScreen(context, screenData) {
            context.commit('addScreen', screenData);
        },
        updateScreen(context, screenData) {
            context.commit('updateScreen', screenData);
        },

        // screens selection
        selectScreen(context, id) {
            context.commit('selectScreen', id);
        },
        deselectScreen(context, id) {
            context.commit('deselectScreen', id);
        },
        toggleScreenSelection(context, id) {
            if (context.getters.isScreenSelected(id)) {
                context.dispatch('deselectScreen', id);
            } else {
                context.dispatch('selectScreen', id);
            }
        },
        selectAllScreens(context) {
            for (let i = context.state.screens.length - 1; i >= 0; i--) {
                context.dispatch('selectScreen', context.state.screens[i].id);
            }
        },
        deselectAllScreens(context) {
            for (let i = context.state.selectedScreens.length - 1; i >= 0; i--) {
                context.dispatch('deselectScreen', context.state.selectedScreens[i]);
            }
        },
    },
    getters: {
        orderedScreens(state) {
            return state.screens.slice().sort((a, b) => (a['order'] - b['order']));
        },
        getScreen: (state) => (id) => {
            return CommonHelper.findByKey(state.screens, 'id', id);
        },
        activeScreen: (state, getters) => {
            return getters.getScreen(state.activeScreenId);
        },
        activeScreenOrderedIndex: (state, getters) => {
            var activeScreen = getters.activeScreen;

            if (activeScreen) {
                let orderedScreens = getters.orderedScreens;

                for (let i = orderedScreens.length - 1; i >= 0; i--) {
                    if (orderedScreens[i].id == activeScreen.id) {
                        return i;
                    }
                }
            }

            return -1;
        },
        isScreenSelected: (state) => (id) => {
            return state.selectedScreens.indexOf(id << 0) >= 0;
        },
    },
});
