import { writable, derived } from "svelte/store";
import pb from "@/pb";
import utils from "@/utils";
import { activeScreen } from "@/stores/screens";

export const templates = writable([]);
export const isLoadingTemplates = writable(false);

export function resetTemplatesStore() {
    templates.set([]);
    isLoadingTemplates.set(false);
}

export function addTemplate(template) {
    templates.update((list) => {
        utils.pushOrReplaceObject(list, template);
        return list;
    });
}

export function removeTemplate(template) {
    templates.update((list) => {
        utils.removeByKey(list, "id", template?.id);
        return list;
    });
}

export const activeScreenTemplates = derived([templates, activeScreen], ([$templates, $activeScreen]) => {
    return $templates.filter(
        (h) => h.screens.length && $activeScreen?.id && h.screens.includes($activeScreen.id),
    );
});

export let unsubscribeHotspotTemplatesFunc;

export async function initTemplatesSubscription(prototypeId) {
    unsubscribeHotspotTemplatesFunc?.();

    return pb
        .collection("hotspotTemplates")
        .subscribe(
            "*",
            (e) => {
                if (e.action === "delete") {
                    removeTemplate(e.record);
                } else {
                    addTemplate(e.record);
                }
            },
            {
                filter: `prototype="${prototypeId}"`,
            },
        )
        .then((unsubscribe) => {
            unsubscribeHotspotTemplatesFunc = unsubscribe;
            return unsubscribe;
        })
        .catch((err) => {
            console.warn("failed to init templates subscription:", err);
        });
}

export async function loadTemplates(prototypeId) {
    resetTemplatesStore();

    isLoadingTemplates.set(true);

    try {
        const items = await pb.collection("hotspotTemplates").getFullList({
            filter: `prototype="${prototypeId}"`,
        });
        items.sort((a, b) => (!a.created || a.created > b.created ? 1 : -1));

        templates.set(items);

        isLoadingTemplates.set(false);
    } catch (err) {
        if (!err.isAbort) {
            isLoadingTemplates.set(false);
            pb.error(err);
        }
    }
}
