import BaseResource from '@/BaseResource';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class Hotspots extends BaseResource {
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
            'url':    '/hotspots',
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
            'url':     '/hotspots/' + encodeURIComponent(id),
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
            'url':    '/hotspots',
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
            'url':    '/hotspots/' + encodeURIComponent(id),
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
            'url':    '/hotspots/' + encodeURIComponent(id),
            'params': queryParams,
            'data':   bodyParams,
        });
    }
}
