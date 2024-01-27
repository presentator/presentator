import { writable } from "svelte/store";
import utils from "@/utils";

export const toasts = writable([]);

export function addInfoToast(message, duration = 3000) {
    return addToast(message, "info", duration);
}

export function addSuccessToast(message, duration = 3000) {
    return addToast(message, "success", duration);
}

export function addErrorToast(message, duration = 4000) {
    return addToast(message, "error", duration);
}

export function addWarningToast(message, duration = 4000) {
    return addToast(message, "warning", duration);
}

export function addToast(message, type, duration) {
    duration = duration || 4000;
    const toast = {
        message: message,
        type: type,
        duration: duration,
        timeout: setTimeout(() => {
            removeToast(toast);
        }, duration),
    };

    toasts.update((t) => {
        removeToastFromArray(t, toast.message);

        utils.pushOrReplaceObject(t, toast, "message");

        return t;
    });
}

export function removeToast(messageOrToast) {
    toasts.update((t) => {
        removeToastFromArray(t, messageOrToast);

        return t;
    });
}

export function removeAllToasts() {
    toasts.update((t) => {
        for (let toast of t) {
            removeToastFromArray(t, toast);
        }

        return [];
    });
}

// Internal toast removal method (usually used to delete previous duplicated toasts).
// NB! This doesn't update the store value! Use `removeToast()` instead.
function removeToastFromArray(arr, messageOrToast) {
    let toast;
    if (typeof messageOrToast == "string") {
        toast = utils.findByKey(arr, "message", messageOrToast);
    } else {
        toast = messageOrToast;
    }

    if (!toast) {
        return;
    }

    clearTimeout(toast.timeout);
    utils.removeByKey(arr, "message", toast.message);
}
