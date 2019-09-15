import CommonHelper from '@/utils/CommonHelper';

/**
 * Helper class with static methods for easier access to various app configurations.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class AppConfig {
    /**
     * Returns single app config setting by its key.
     * It looks first into the global `window.APP_CONFIG` objects
     * and if the setting is not found there - in the `process.env` object (.env[.*] file(s)).
     *
     * Example usage:
     * ```js
     * AppConfig.get('my_key.my_subkey');
     * AppConfig.get('my_key', 'lorem ipsum');
     * ```
     *
     * @param  {string} key            Name of the setting to return (nested keys with dot-notation are supported).
     * @param  {Mixed}  [defaultValue] The value that will be returned if `key` setting is not defined.
     * @return {Mixed}
     */
    static get(key, defaultValue) {
        return CommonHelper.getNestedVal(
            window.APP_CONFIG,
            key,
            CommonHelper.getNestedVal(process.env, key, defaultValue)
        );
    }

    /**
     * Checks whether Firebase Cloud Firestore configuration settings are provided.
     *
     * @return {Boolean}
     */
    static isFirestoreConfigured() {
        return (
            AppConfig.get('VUE_APP_FIRESTORE_PROJECT_ID') &&
            AppConfig.get('VUE_APP_FIRESTORE_COLLECTION')
        );
    }
}
