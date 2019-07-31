/**
 * BaseResource class that should be inherited from all API resources.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
module.exports = class BaseResource {
    /**
     * @param {Object} http Axios instance
     */
    constructor(http) {
        // use '$' prefix to differentiate from the other class props
        this.$http = http;
    }
}
