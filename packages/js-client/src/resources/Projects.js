import BaseResource from '@/BaseResource';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class Projects extends BaseResource {
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
            'url':    '/projects',
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
            'url':     '/projects/' + encodeURIComponent(id),
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
            'url':    '/projects',
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
            'url':    '/projects/' + encodeURIComponent(id),
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
            'url':    '/projects/' + encodeURIComponent(id),
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} id
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getCollaboratorsList(id, queryParams = {}) {
        return this.$http({
            'method': 'get',
            'url':    '/projects/' + encodeURIComponent(id) + '/collaborators',
            'params': queryParams,
        });
    }

    /**
     * @param  {String} id
     * @param  {String} searchTerm
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    searchUsers(id, searchTerm, queryParams = {}) {
        queryParams = Object.assign({
            'search': searchTerm,
        }, queryParams);

        return this.$http({
            'method': 'get',
            'url':    '/projects/' + encodeURIComponent(id) + '/users/search',
            'params': queryParams,
        });
    }

    /**
     * @param  {String} id
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getUsersList(id, queryParams = {}) {
        return this.$http({
            'method': 'get',
            'url':    '/projects/' + encodeURIComponent(id) + '/users',
            'params': queryParams,
        });
    }

    /**
     * @param  {Number} id
     * @param  {String} userId
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    linkUser(id, userId, bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method': 'post',
            'url':    '/projects/' + encodeURIComponent(id) + '/users/' + encodeURIComponent(userId),
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {Number} id
     * @param  {Number} userId
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    unlinkUser(id, userId, bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method': 'delete',
            'url':    '/projects/' + encodeURIComponent(id) + '/users/' + encodeURIComponent(userId),
            'params': queryParams,
            'data':   bodyParams,
        });
    }
}
