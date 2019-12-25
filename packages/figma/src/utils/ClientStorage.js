import types from '@/utils/types';

/**
 * Generic app storage class.
 *
 * Example usage:
 * ```js
 * @import clientStorage from '@/utils/ClientStorage';
 * ...
 * var token = clientStorage.getItem('token');
 * ```
 */
class ClientStorage {
    /**
     * Initializes the storage.
     */
    constructor(data) {
        this.load(data);
    }

    /**
     * Loads storage data.
     */
    load(data) {
        this.data = Object.assign({}, (this.data || {}), (data || {}));
    }

    /**
     * Returns a single item from the storage.
     *
     * @param  {String} key
     * @return {Mixed}
     */
    getItem(key) {
        return this.data[key];
    }

    /**
     * Sets a single item in the storage.
     *
     * @param {String} key
     * @param {Mixed}  val
     */
    setItem(key, val) {
        this.data[key] = val;

        parent.postMessage({
            pluginMessage: {
                type: types.MESSAGE_SAVE_STORAGE,
                data: this.data,
            },
        }, '*');
    }

    /**
     * Removes a single item from the storage.
     *
     * @param {String} key
     */
    removeItem(key) {
        delete this.data[key];

        parent.postMessage({
            pluginMessage: {
                type: types.MESSAGE_SAVE_STORAGE,
                data: this.data,
            },
        }, '*');
    }
}

export default new ClientStorage();
