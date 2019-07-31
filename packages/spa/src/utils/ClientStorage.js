import CommonHelper from '@/utils/CommonHelper'

/**
 * Basic local/session storage wrapper class for storing and retrieving various app settings.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class ClientStorage {
    /**
     * Returns `Storage` instance.
     *
     * @return {Storage}
     */
    static getStorage() {
        return window.localStorage || window.sessionStorage;
    }

    /**
     * Checks whether the storage is available.
     *
     * @return {Boolean}
     */
    static hasStorage() {
        return typeof this.getStorage() !== 'undefined';
    }

    /**
     * @param  {String} key
     * @param  {Mixed} defaultVal The default value that will be returned if key is not set.
     * @return {Mixed}
     */
    static getItem(key, defaultVal) {
        if (!this.hasStorage()) {
            return defaultVal;
        }

        var val = this.getStorage().getItem(key);

        var normalizedVal = val;
        try {
            normalizedVal = JSON.parse(val);
        } catch (e) {
        }

        if (CommonHelper.isEmpty(normalizedVal)) {
            return defaultVal;
        }

        return normalizedVal;
    }

    /**
     * @param {String} key
     * @param {Mixed}  val
     */
    static setItem(key, val) {
        if (!this.hasStorage()) {
            return;
        }

        var normalizedVal = val;
        if (!CommonHelper.isString(val)) {
            normalizedVal = JSON.stringify(val);
        }

        this.getStorage().setItem(key, normalizedVal);
    }

    /**
     * @param {String} key
     */
    static removeItem(key) {
        if (!this.hasStorage()) {
            return;
        }

        this.getStorage().removeItem(key);
    }
}
