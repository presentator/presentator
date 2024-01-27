import PocketBase, { LocalAuthStore } from "pocketbase";
// ---
import utils from "@/utils";
import { replace } from "svelte-spa-router";
import { addErrorToast } from "@/stores/toasts";
import { setErrors } from "@/stores/errors";
import { setLoggedUser } from "@/stores/app";

const REDIRECT_PATH_KEY = "pr_redirect";

/**
 * Locally stores the current path for later redirect.
 */
PocketBase.prototype.rememberPath = function () {
    window.localStorage.setItem(REDIRECT_PATH_KEY, window.location.hash.slice(1));
};

/**
 * Locally stores the current path for later redirect.
 *
 * @param {String} [fallback] Fallback path if there is nothing stored (default to "/")
 */
PocketBase.prototype.replaceWithRemembered = function (fallback = "/") {
    const path = window.localStorage.getItem(REDIRECT_PATH_KEY);

    if (path) {
        window.localStorage.removeItem(REDIRECT_PATH_KEY);
    }

    replace(path || fallback);
};

/**
 * Clears the authorized state and redirects to the login page.
 *
 * @param {Boolean} [redirect] Whether to redirect to the login page.
 */
PocketBase.prototype.logout = function (redirect = true) {
    this.authStore.clear();

    if (redirect) {
        replace("/login");
    }
};

/**
 * Generic API error response handler.
 *
 * @param  {Error}   err        The API error itself.
 * @param  {Boolean} notify     Whether to add a toast notification.
 * @param  {String}  defaultMsg Default toast notification message if the error doesn't have one.
 */
PocketBase.prototype.error = function (err, notify = true, defaultMsg = "") {
    if (!err || !(err instanceof Error) || err.isAbort) {
        return;
    }

    const statusCode = err?.status << 0;
    const response = err?.response || {};

    // add toast error notification
    let msg = notify && (response.message || err.message || defaultMsg);
    if (msg) {
        addErrorToast(msg);
    }

    // client-side error
    if (statusCode == 0) {
        console.log(err);
    }

    // populate form field errors
    if (!utils.isEmpty(response.data)) {
        setErrors(response.data);
    }

    // unauthorized
    if (statusCode === 401) {
        this.rememberPath();
        this.cancelAllRequests();
        return this.logout();
    }

    // forbidden
    if (statusCode === 403) {
        this.rememberPath();
        this.cancelAllRequests();
        return replace("/login");
    }
};

// Changes the current auth store to use the storage with the specified suffix key.
PocketBase.prototype.initStore = function (suffix = "") {
    const key = "pr_user_auth" + suffix;
    if (this.authStore.storageKey != key) {
        this.authStore = new AppAuthStore(key);
    }
};

// Custom auth store to sync the svelte user store state with the authorized user instance.
class AppAuthStore extends LocalAuthStore {
    /**
     * @inheritdoc
     */
    constructor(storageKey = "pr_user_auth") {
        super(storageKey);

        this.save(this.token, this.model);
    }

    /**
     * @inheritdoc
     */
    save(token, model) {
        super.save(token, model);

        if (model && model.collectionName == "users") {
            setLoggedUser(model);
        }
    }

    /**
     * @inheritdoc
     */
    clear() {
        if (this.model?.collectionName == "users") {
            setLoggedUser(null);
        }

        super.clear();
    }
}

const pb = new PocketBase(import.meta.env.PR_BACKEND_URL);

pb.initStore();

// we create a separate temp store to ensure that the authRefresh() is
// applied on the default pb.authStore even if it is replaced at later stage (eg. in case of a link auth)
if (pb.authStore.isValid) {
    const temp = new PocketBase(pb.baseUrl, pb.authStore);
    temp.collection(temp.authStore.model.collectionName)
        .authRefresh()
        .catch((err) => {
            console.warn("Failed to refresh the authenticated model", err);

            // clear the store only on invalidated/expired token
            const status = err?.status << 0;
            if (status == 401 || status == 403) {
                temp.authStore.clear();
            }
        });
}

export function createLinkClient(storeSuffix) {
    const client = new PocketBase(pb.baseUrl);

    client.initStore(storeSuffix);

    return client;
}

export default pb;
