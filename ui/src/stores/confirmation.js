import { writable } from "svelte/store";

// Example value format:
// {
//   "text":        "Do you really want to delete the selectedItem",
//   "yesCallback": function() {...},
//   "noCallback":  function() {...},
// }
export const confirmation = writable({});

/**
 * @param  {String}   text
 * @param  {Function} [yesCallback]
 * @param  {Function} [noCallback]
 * @return {Promise}
 */
export function confirm(text, yesCallback, noCallback) {
    return new Promise((resolve) => {
        confirmation.set({
            text: text,
            yesCallback: async () => {
                if (yesCallback) {
                    await yesCallback();
                }
                resolve();
            },
            noCallback: async () => {
                if (noCallback) {
                    await noCallback();
                }
                resolve();
            },
        });
    });
}

export function resetConfirmation() {
    confirmation.set({});
}
