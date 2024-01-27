import { writable } from "svelte/store";
import utils from "@/utils";

export const errors = writable({});

export function setErrors(newErrors) {
    errors.set(newErrors || {});
}

export function addError(key, message) {
    errors.update((e) => {
        utils.setByPath(e, key, utils.sentenize(message));
        return e;
    });
}

export function removeError(key) {
    errors.update((e) => {
        utils.deleteByPath(e, key);
        return e;
    });
}
