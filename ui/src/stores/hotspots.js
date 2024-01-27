import { writable, derived } from "svelte/store";
import pb from "@/pb";
import utils from "@/utils";
import { activeScreen } from "@/stores/screens";
import { templates } from "@/stores/templates";

export const hotspotTypes = {
    note: "note",
    screen: "screen",
    overlay: "overlay",
    back: "back",
    prev: "prev",
    next: "next",
    url: "url",
    scroll: "scroll",
};
Object.freeze(hotspotTypes);

export const hotspots = writable([]);
export const selectedHotspotId = writable(null); // @see selectedHotspot
export const isLoadingHotspots = writable(false);
export const isHotspotDeleting = writable(false);
export const isHotspotDragging = writable(false);

export function resetHotspotsStore() {
    hotspots.set([]);
    selectedHotspotId.set(null);
    isLoadingHotspots.set(false);
    isHotspotDeleting.set(false);
    isHotspotDragging.set(false);
}

export const selectedHotspot = derived([hotspots, selectedHotspotId], ([$hotspots, $selectedHotspotId]) => {
    if (!$hotspots.length || $selectedHotspotId === null) {
        return null;
    }

    return $hotspots.find((h) => h.id === $selectedHotspotId);
});
selectedHotspot.set = (modelOrId) => {
    if (utils.isObject(modelOrId)) {
        addHotspot(modelOrId, true);
    } else {
        selectedHotspotId.set(modelOrId);
    }
};

export function addHotspot(hotspot, select = false) {
    if (!hotspot) {
        return;
    }

    hotspots.update((list) => {
        utils.pushOrReplaceObject(list, hotspot);
        return list;
    });

    if (select) {
        selectedHotspotId.set(hotspot.id);
    }
}

export function removeHotspot(hotspot) {
    if (!hotspot) {
        return;
    }

    hotspots.update((list) => {
        utils.removeByKey(list, "id", hotspot.id);
        return list;
    });

    selectedHotspotId.update((id) => {
        return id == hotspot.id ? null : id;
    });
}

export function removeUnsavedHotspots() {
    hotspots.update((list) => {
        return list.filter((c) => !!c.id);
    });

    selectedHotspotId.update((id) => {
        return id === "" ? null : id;
    });
}

export const activeScreenHotspots = derived(
    [activeScreen, hotspots, templates],
    ([$activeScreen, $hotspots, $templates]) => {
        return filterHotspots($activeScreen, $hotspots, $templates);
    },
);

export function filterHotspots(screen, hotspots, templates) {
    return hotspots.filter((h) => {
        if (!screen?.id) {
            return false;
        }

        if (h.screen == screen.id) {
            return true;
        }

        for (let template of templates) {
            if (h.hotspotTemplate == template.id && template.screens?.includes(screen.id)) {
                return true;
            }
        }

        return false;
    });
}

export let unsubscribeHotspotsFunc;

export async function initHotspotsSubscription(prototypeId) {
    unsubscribeHotspotsFunc?.();

    return pb
        .collection("hotspots")
        .subscribe(
            "*",
            (e) => {
                if (e.action == "delete") {
                    removeHotspot(e.record);
                } else {
                    addHotspot(e.record);
                }
            },
            {
                filter: `screen.prototype="${prototypeId}" || hotspotTemplate.prototype="${prototypeId}"`,
            },
        )
        .then((unsubscribe) => {
            unsubscribeHotspotsFunc = unsubscribe;
            return unsubscribe;
        })
        .catch((err) => {
            console.warn("failed to init hotspots subscription:", err);
        });
}

export async function loadHotspots(prototypeId) {
    resetHotspotsStore();

    isLoadingHotspots.set(true);

    try {
        const items = await pb.collection("hotspots").getFullList({
            filter: `screen.prototype="${prototypeId}" || hotspotTemplate.prototype="${prototypeId}"`,
        });

        hotspots.set(items);

        isLoadingHotspots.set(false);
    } catch (err) {
        if (!err.isAbort) {
            isLoadingHotspots.set(false);
            pb.error(err);
        }
    }
}
