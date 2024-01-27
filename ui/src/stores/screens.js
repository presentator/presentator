import { writable, get, derived } from "svelte/store";
import pb from "@/pb";
import utils from "@/utils";
import { prototypes, activePrototype } from "@/stores/prototypes";
import { confirm } from "@/stores/confirmation";

export const modes = {
    preview: "preview",
    hotspots: "hotspots",
    comments: "comments",
};
Object.freeze(modes);

const activeScreenId = writable("");
const prevActiveScreenId = writable("");
const transitionScreenId = writable("");

export const screens = writable([]);
export const transition = writable("");
export const overlayScreenSettings = writable(null);
export const isLoadingScreens = writable(false);
export const mode = writable(modes.preview);
export const fitToScreen = writable(false);
export const activeScale = writable(1);
export const showPreviewHotspots = writable(false);
export const showPreviewAnnotations = writable(true);

export function resetScreensStore() {
    loadModeFromUrl();

    screens.set([]);
    prevActiveScreenId.set("");
    activeScreenId.set("");
    transitionScreenId.set("");
    transition.set("");
    overlayScreenSettings.set(null);
    isLoadingScreens.set(false);
    activeScale.set(1);
    showPreviewHotspots.set(false);
    showPreviewAnnotations.set(true);
}

export const activeScreen = derived([screens, activeScreenId], ([$screens, $activeScreenId]) => {
    if (!$screens.length) {
        return null;
    }

    // always fallback to the first screen if no explicit active screen is set
    return ($activeScreenId && $screens.find((p) => p.id == $activeScreenId)) || $screens[0];
});
activeScreen.set = (modelOrId) => {
    prevActiveScreenId.set(get(activeScreenId) || null);
    overlayScreenSettings.set(null); // reset overlay

    if (utils.isObject(modelOrId)) {
        addScreen(modelOrId, true);
    } else {
        activeScreenId.set(modelOrId);
    }
};

export const prevActiveScreen = derived([screens, prevActiveScreenId], ([$screens, $prevActiveScreenId]) => {
    if (!$screens.length || !$prevActiveScreenId) {
        return null;
    }

    return $screens.find((p) => p.id == $prevActiveScreenId);
});
prevActiveScreen.set = (modelOrId) => {
    if (utils.isObject(modelOrId)) {
        addScreen(modelOrId);
        prevActiveScreenId.set(modelOrId.id);
    } else {
        prevActiveScreenId.set(modelOrId);
    }
};

export const transitionScreen = derived([screens, transitionScreenId], ([$screens, $transitionScreenId]) => {
    if (!$screens.length || !$transitionScreenId) {
        return null;
    }

    return $screens.find((p) => p.id == $transitionScreenId);
});
transitionScreen.set = (modelOrId) => {
    if (utils.isObject(modelOrId)) {
        addScreen(modelOrId);
        transitionScreenId.set(modelOrId.id);
    } else {
        transitionScreenId.set(modelOrId);
    }
};

// Retrieves the first available screen based on an offset relative to
// the current active one.
export function getScreenByActiveOffset(offset = 1) {
    const list = get(screens) || [];
    if (!list.length) {
        return null;
    }

    const activeId = get(activeScreenId) || null;

    let index = list.findIndex((v) => v.id == activeId) + offset;
    if (index < 0) {
        index = 0;
    } else if (index >= list.length) {
        index = list.length - 1;
    }

    return list[index];
}

export function changeActiveScreenByOffset(offset = 1) {
    const found = getScreenByActiveOffset(offset);
    if (!found?.id) {
        return;
    }

    const active = get(activeScreenId) || null;

    if (active?.id != found.id) {
        activeScreen.set(found.id);
    }
}

export function addScreen(screen, active = false) {
    if (!screen) {
        return;
    }

    screens.update((list) => {
        utils.pushOrReplaceObject(list, screen);
        return list;
    });

    if (active) {
        activeScreen.set(screen.id);
    }
}

// remove single screen
export function removeScreen(screen) {
    if (!screen) {
        return;
    }

    screens.update((list) => {
        utils.removeByKey(list, "id", screen.id);

        // optimistically update the related prototypes screens order
        prototypes.update((prototypesList) => {
            const found = prototypesList.find((p) => p.id == screen["prototype"]);
            if (found?.screensOrder?.length) {
                utils.removeByValue(found.screensOrder, found.id);
            }
            return prototypesList;
        });

        activeScreenId.update((id) => {
            return id == screen.id ? "" : id;
        });

        return list;
    });
}

export let unsubscribeScreensFunc;

export async function initScreensSubscription(prototypeId) {
    unsubscribeScreensFunc?.();

    return pb
        .collection("screens")
        .subscribe(
            "*",
            (e) => {
                if (e.action === "delete") {
                    removeScreen(e.record);
                } else {
                    addScreen(e.record);
                }
            },
            { filter: `prototype="${prototypeId}"` },
        )
        .then((unsubscribe) => {
            unsubscribeScreensFunc = unsubscribe;
            return unsubscribe;
        })
        .catch((err) => {
            console.warn("failed to init screens subscription:", err);
        });
}

export function getScreenOrder(screen) {
    if (!screen?.id) {
        return 0;
    }

    let index = get(activePrototype)?.screensOrder?.indexOf(screen.id);

    // fallback to order in the screens list
    if (index < 0) {
        index = get(screens).findIndex((s) => s.id == screen.id);
    }

    return index + 1;
}

// fit
// -------------------------------------------------------------------

const FIT_TO_SCREEN_KEY = "fitToScreen";

export function toggleFitToScreen() {
    fitToScreen.update((v) => {
        v = !v;

        window.localStorage.setItem(FIT_TO_SCREEN_KEY, v ? 1 : 0);

        utils.replaceHashQueryParams({ fit: v ? 1 : 0 });

        return v;
    });
}

export function loadFitToScreen() {
    const params = utils.getHashQueryParams();

    let fit = typeof params.fit !== "undefined" ? params.fit : window.localStorage.getItem(FIT_TO_SCREEN_KEY);
    fit = !!(fit << 0);

    fitToScreen.set(fit);

    utils.replaceHashQueryParams({ fit: fit ? 1 : 0 });
}

// mode
// -------------------------------------------------------------------

export function loadModeFromUrl() {
    const params = utils.getHashQueryParams();

    changeMode(params.mode);
}

export function changeMode(newMode) {
    // always fallback to preview
    if (!newMode || !modes[newMode]) {
        newMode = modes.preview;
    }

    mode.set(newMode);

    utils.replaceHashQueryParams({ mode: newMode });
}

// generic helpers
// -------------------------------------------------------------------

export async function replaceScreenWithConfirm(screen, file) {
    return confirm(
        "Screen replacement could result in hotspots and comments misplacement if the new screen image has different dimensions from the original. Do you still want to proceed?",
        async () => {
            return replaceScreen(screen, file);
        },
    );
}

export async function replaceScreen(screen, file) {
    if (!file || !screen?.id || screen.isReplacing) {
        return;
    }

    screen.isReplacing = true;

    const data = new FormData();
    data.append("file", file);
    data.append("title", file.name.split(".").slice(0, -1).join(".") || file.name); // trim extension

    try {
        const updatedScreen = await pb.collection("screens").update(screen.id, data, {
            requestKey: "replace" + screen.id,
        });

        screen.isReplacing = false;

        addScreen(updatedScreen);
    } catch (err) {
        if (!err.isAbort) {
            screen.isReplacing = false;
            pb.error(err);
        }
    }
}
