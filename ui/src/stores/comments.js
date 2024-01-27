import { writable, derived, get } from "svelte/store";
import pb from "@/pb";
import utils from "@/utils";
import { activeScreen, mode, modes } from "@/stores/screens";

export const comments = writable([]);
export const selectedCommentId = writable(null); // @see selectedComment
export const isLoadingComments = writable(false);
export const isCommentDeleting = writable(false);
export const showResolved = writable(false);

export function resetCommentsStore() {
    comments.set([]);
    selectedCommentId.set(null);
    isLoadingComments.set(false);
    isCommentDeleting.set(false);
    // showResolved.set(false); preserve the latest state between navigations
}

export const selectedComment = derived([comments, selectedCommentId], ([$comments, $selectedCommentId]) => {
    if (!$comments.length || $selectedCommentId === null) {
        return null;
    }

    return $comments.find((c) => c.id === $selectedCommentId);
});
selectedComment.set = (modelOrId) => {
    if (utils.isObject(modelOrId)) {
        addComment(modelOrId, true);
    } else {
        selectedCommentId.set(modelOrId);
    }
};

export function addComment(comment, select = false) {
    if (!comment) {
        return;
    }

    comments.update((list) => {
        utils.pushOrReplaceObject(list, comment);
        return list;
    });

    if (select) {
        selectedCommentId.set(comment.id);
    }
}

export function removeComment(comment) {
    if (!comment) {
        return;
    }

    comments.update((list) => {
        return list.filter((c) => c.id != comment.id && c.replyTo != comment.id);
    });

    selectedCommentId.update((id) => {
        // switch to the primary comment (if available)
        if (id == comment.id) {
            return comment.replyTo || null;
        }

        return id;
    });
}

export function removeUnsavedComments() {
    comments.update((list) => {
        return list.filter((c) => !!c.id);
    });

    selectedCommentId.update((id) => {
        return id === "" ? null : id;
    });
}

export const activeScreenComments = derived([comments, activeScreen], ([$comments, $activeScreen]) => {
    return $comments
        .filter((c) => c.screen == $activeScreen?.id)
        .sort((a, b) => (!a.created || a.created > b.created ? 1 : -1));
});

export const activeScreenPrimaryComments = derived([activeScreenComments], ([$comments]) => {
    return $comments.filter((c) => !c.replyTo);
});

export const activeScreenUnresolvedPrimaryComments = derived([activeScreenComments], ([$comments]) => {
    return $comments.filter((c) => c.id && !c.replyTo && !c.resolved);
});

export let unsubscribeCommentsFunc;

export async function initCommentsSubscription(prototypeId) {
    unsubscribeCommentsFunc?.();

    return pb
        .collection("comments")
        .subscribe(
            "*",
            (e) => {
                if (e.action === "delete") {
                    removeComment(e.record);
                } else {
                    addComment(e.record);
                }
            },
            {
                filter: `screen.prototype="${prototypeId}"`,
                expand: "user",
            },
        )
        .then((unsubscribe) => {
            unsubscribeCommentsFunc = unsubscribe;
            return unsubscribe;
        })
        .catch((err) => {
            console.warn("failed to init comments subscription:", err);
        });
}

export async function loadComments(prototypeId, selectedId = undefined) {
    resetCommentsStore();

    isLoadingComments.set(true);

    try {
        const items = await pb.collection("comments").getFullList({
            filter: `screen.prototype="${prototypeId}"`,
            expand: "user",
        });

        comments.set(items);

        if (get(mode) === modes.comments && selectedId) {
            if (items.find((c) => c.id == selectedId)?.resolved) {
                showResolved.set(true);
            }
            selectedComment.set(selectedId);
        }

        isLoadingComments.set(false);
    } catch (err) {
        if (!err.isAbort) {
            isLoadingComments.set(false);
            pb.error(err);
        }
    }
}
