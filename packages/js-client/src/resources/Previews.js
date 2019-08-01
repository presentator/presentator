import BaseResource from '@/BaseResource';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class Previews extends BaseResource {
    /**
     * @param  {String} slug
     * @param  {String} [password]
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    authorize(slug, password = '', bodyParams = {}, queryParams = {}) {
        bodyParams = Object.assign({
            'slug':     slug,
            'password': password,
        }, bodyParams);

        return this.$http({
            'method': 'post',
            'url':    '/previews',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} slug
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getOne(previewToken, queryParams = {}) {
        return this.$http({
            'method':  'get',
            'url':     '/previews',
            'params':  queryParams,
            'headers': { 'X-Preview-Token': previewToken },
        });
    }

    /**
     * @param  {String} previewToken
     * @param  {Number} id
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getPrototype(previewToken, id, queryParams = {}) {
        return this.$http({
            'method':  'get',
            'url':     '/previews/prototypes/' + encodeURIComponent(id),
            'params':  queryParams,
            'headers': { 'X-Preview-Token': previewToken },
        });
    }

    /**
     * @param  {String} previewToken
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getAssets(previewToken, queryParams = {}) {
        return this.$http({
            'method':  'get',
            'url':     '/previews/assets',
            'params':  queryParams,
            'headers': { 'X-Preview-Token': previewToken },
        });
    }

    /**
     * @param  {String} previewToken
     * @param  {Number} [page]
     * @param  {Number} [perPage]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getScreenCommentsList(previewToken, page = 1, perPage = 20, queryParams = {}) {
        queryParams = Object.assign({
            'page':     page,
            'per-page': perPage
        }, queryParams);

        return this.$http({
            'method':  'get',
            'url':     '/previews/screen-comments',
            'params':  queryParams,
            'headers': { 'X-Preview-Token': previewToken },
        });
    }

    /**
     * @param  {String} previewToken
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    createScreenComment(previewToken, bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method':  'post',
            'url':     '/previews/screen-comments',
            'params':  queryParams,
            'data':    bodyParams,
            'headers': { 'X-Preview-Token': previewToken },
        });
    }

    /**
     * @param  {String} previewToken
     * @param  {Number} id
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    updateScreenComment(previewToken, id, bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method':  'put',
            'url':     '/previews/screen-comments/' + encodeURIComponent(id),
            'params':  queryParams,
            'data':    bodyParams,
            'headers': { 'X-Preview-Token': previewToken },
        });
    }
}
