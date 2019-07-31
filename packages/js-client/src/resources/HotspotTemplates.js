const BaseResource = require('@/BaseResource');

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
module.exports = class HotspotTemplates extends BaseResource {
    /**
     * @param  {Number} [page]
     * @param  {Number} [perPage]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getList(page = 1, perPage = 20, queryParams = {}) {
        queryParams = Object.assign({
            'page':     page,
            'per-page': perPage,
        }, queryParams);

        return this.$http({
            'method': 'get',
            'url':    '/hotspot-templates',
            'params': queryParams,
        });
    }

    /**
     * @param  {String} id
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getOne(id, queryParams = {}) {
        return this.$http({
            'method':  'get',
            'url':     '/hotspot-templates/' + encodeURIComponent(id),
            'params':  queryParams,
        });
    }

    /**
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    create(bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method': 'post',
            'url':    '/hotspot-templates',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} id
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    update(id, bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method': 'put',
            'url':    '/hotspot-templates/' + encodeURIComponent(id),
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} id
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    delete(id, bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method': 'delete',
            'url':    '/hotspot-templates/' + encodeURIComponent(id),
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} id
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getScreensList(id, queryParams = {}) {
        return this.$http({
            'method': 'get',
            'url':    '/hotspot-templates/' + encodeURIComponent(id) + '/screens',
            'params': queryParams,
        });
    }

    /**
     * @param  {Number} id
     * @param  {String} screenId
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    linkScreen(id, screenId, bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method': 'post',
            'url':    '/hotspot-templates/' + encodeURIComponent(id) + '/screens/' + encodeURIComponent(screenId),
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {Number} id
     * @param  {Number} screenId
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    unlinkScreen(id, screenId, bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method': 'delete',
            'url':    '/hotspot-templates/' + encodeURIComponent(id) + '/screens/' + encodeURIComponent(screenId),
            'params': queryParams,
            'data':   bodyParams,
        });
    }
}
