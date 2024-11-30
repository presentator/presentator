import { writable, derived } from "svelte/store";
import utils from "@/utils";
import pb from "@/pb";

export const prototypes = writable([]);
export const activePrototypeId = writable(""); // @see activePrototype
export const isLoadingPrototypes = writable(false);

export function resetPrototypesStore() {
    prototypes.set([]);
    activePrototypeId.set("");
    isLoadingPrototypes.set(false);
}

export const activePrototype = derived(
    [prototypes, activePrototypeId],
    ([$prototypes, $activePrototypeId]) => {
        if (!$prototypes.length) {
            return null;
        }

        // always fallback to the last prototype if no explicit active prototype is found
        return (
            ($activePrototypeId && $prototypes.find((p) => p.id == $activePrototypeId)) ||
            $prototypes[$prototypes.length - 1]
        );
    },
);
activePrototype.set = (modelOrId) => {
    if (utils.isObject(modelOrId)) {
        addPrototype(modelOrId, true);
    } else {
        activePrototypeId.set(modelOrId);
    }
};

export function addPrototype(prototype, active = false) {
    if (!prototype) {
        return;
    }

    prototypes.update((list) => {
        utils.pushOrReplaceObject(list, prototype);
        return list;
    });

    if (active) {
        activePrototypeId.set(prototype.id);
    }
}

export function removePrototype(prototype) {
    if (!prototype) {
        return;
    }

    prototypes.update((list) => {
        utils.removeByKey(list, "id", prototype.id);
        return list;
    });

    activePrototypeId.update((id) => {
        return id == prototype.id ? "" : id;
    });
}

export let unsubscribePrototypesFunc;

export async function initPrototypesSubscription(projectId) {
    unsubscribePrototypesFunc?.();

    return pb
        .collection("prototypes")
        .subscribe(
            "*",
            (e) => {
                if (e.action === "delete") {
                    removePrototype(e.record);
                } else {
                    addPrototype(e.record);
                }
            },
            { filter: `project="${projectId}"` },
        )
        .then((unsubscribe) => {
            unsubscribePrototypesFunc = unsubscribe;
            return unsubscribe;
        })
        .catch((err) => {
            console.warn("failed to init prototypes subscription:", err);
        });
}
