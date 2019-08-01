import BaseResource from '@/BaseResource';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class Users extends BaseResource {
    /**
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    getAuthClients(queryParams = {}) {
        return this.$http({
            'method': 'get',
            'url':    '/users/auth-clients',
            'params': queryParams,
        });
    }

    /**
     * @param  {String} client
     * @param  {String} code
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    authorizeAuthClient(client, code, bodyParams = {}, queryParams = {}) {
        bodyParams = Object.assign({
            'client': client,
            'code':   code,
        }, bodyParams);

        return this.$http({
            'method': 'post',
            'url':    '/users/auth-clients',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    register(bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method': 'post',
            'url':    '/users/register',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} activationToken
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    activate(activationToken, bodyParams = {}, queryParams = {}) {
        bodyParams = Object.assign({
            'token': activationToken,
        }, bodyParams);

        return this.$http({
            'method': 'post',
            'url':    '/users/activate',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} email
     * @param  {String} password
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    login(email, password, bodyParams = {}, queryParams = {}) {
        bodyParams = Object.assign({
            'email':    email,
            'password': password,
        }, bodyParams);

        return this.$http({
            'method': 'post',
            'url':    '/users/login',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} email
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    requestPasswordReset(email, bodyParams = {}, queryParams = {}) {
        bodyParams = Object.assign({
            'email': email,
        }, bodyParams);

        return this.$http({
            'method': 'post',
            'url':    '/users/request-password-reset',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} passwordResetToken
     * @param  {String} password
     * @param  {String} passwordConfirm
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    confirmPasswordReset(
        passwordResetToken,
        password,
        passwordConfirm,
        bodyParams = {},
        queryParams = {}
    ) {
        bodyParams = Object.assign({
            'token':           passwordResetToken,
            'password':        password,
            'passwordConfirm': passwordConfirm,
        }, bodyParams);

        return this.$http({
            'method': 'post',
            'url':    '/users/confirm-password-reset',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} email
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    requestEmailChange(email, bodyParams = {}, queryParams = {}) {
        bodyParams = Object.assign({
            'newEmail': email,
        }, bodyParams);

        return this.$http({
            'method': 'post',
            'url':    '/users/request-email-change',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} emailChangeToken
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    confirmEmailChange(emailChangeToken, bodyParams = {}, queryParams = {}) {
        bodyParams = Object.assign({
            'token': emailChangeToken,
        }, bodyParams);

        return this.$http({
            'method': 'post',
            'url':    '/users/confirm-email-change',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {String} message
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    sendFeedback(message, bodyParams = {}, queryParams = {}) {
        bodyParams = Object.assign({
            'message': message,
        }, bodyParams);

        return this.$http({
            'method': 'post',
            'url':    '/users/feedback',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

    /**
     * @param  {Object} [bodyParams]
     * @param  {Object} [queryParams]
     * @return {Promise}
     */
    refresh(bodyParams = {}, queryParams = {}) {
        return this.$http({
            'method': 'post',
            'url':    '/users/refresh',
            'params': queryParams,
            'data':   bodyParams,
        });
    }

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
            'url':    '/users',
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
            'url':     '/users/' + encodeURIComponent(id),
            'params':  queryParams
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
            'url':    '/users',
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
            'url':    '/users/' + encodeURIComponent(id),
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
            'url':    '/users/' + encodeURIComponent(id),
            'params': queryParams,
            'data':   bodyParams,
        });
    }
}
