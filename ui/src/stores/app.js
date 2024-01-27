import { writable } from "svelte/store";

export const pageTitle = writable("");

export const loggedUser = writable({});

export function setLoggedUser(model) {
    loggedUser.set(model || {});
}

export const options = createOptions();

function createOptions(storageKey = "appOptions") {
    let options = {};

    // load previously stored options (if any)
    const rawOptions = window.localStorage.getItem(storageKey);
    if (rawOptions) {
        try {
            options = JSON.parse(rawOptions) || {};
        } catch {}
    }

    const subs = [];

    const subscribe = (callback) => {
        subs.push(callback);

        callback(options);

        return () => {
            const idx = subs.findIndex((fn) => fn === callback);
            if (idx >= 0) {
                subs.splice(idx, 1);
            }
        };
    };

    const set = (v) => {
        options = v || {};

        window.localStorage.setItem(storageKey, JSON.stringify(options));

        subs.forEach((fn) => fn(options));
    };

    const update = (fn) => set(fn(options));

    return { subscribe, set, update };
}
