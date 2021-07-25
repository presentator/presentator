import { assert } from 'chai';
import axios from 'axios';
import mockAdapter from 'axios-mock-adapter';
import Users from '@/resources/Users';

/**
 * Users API resource tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
describe('Users', function() {
    var adapter = new mockAdapter(axios);
    var resource = new Users(axios.create());

    // mock all requests
    adapter.onAny().reply(200);

    describe('getAuthMethods()', function() {
        it('Should correctly set request data', function(done) {
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.getAuthMethods(queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/auth-methods');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, queryParams);
            }).then(done).catch(done);
        });
    });

    describe('getAuthClients()', function() {
        it('Should correctly set request data', function(done) {
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.getAuthClients(queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/auth-clients');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, queryParams);
            }).then(done).catch(done);
        });
    });

    describe('authorizeAuthClient()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.authorizeAuthClient('test_client', 'test_code', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/auth-clients');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'client': 'test_client',
                    'code': 'test_code',
                }, bodyParams));
            }).then(done).catch(done);
        });
    });

    describe('register()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.register(bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/register');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
            }).then(done).catch(done);
        });
    });

    describe('activate()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.activate('test_token', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/activate');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'token': 'test_token',
                }, bodyParams));
            }).then(done).catch(done);
        });
    });

    describe('login()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.login('test@example.com', '123456', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/login');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'email': 'test@example.com',
                    'password': '123456',
                }, bodyParams));
            }).then(done).catch(done);
        });
    });

    describe('requestPasswordReset()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.requestPasswordReset('test@example.com', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/request-password-reset');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'email': 'test@example.com',
                }, bodyParams));
            }).then(done).catch(done);
        });
    });

    describe('confirmPasswordReset()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.confirmPasswordReset('test_token', '123456', '654321', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/confirm-password-reset');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'token': 'test_token',
                    'password': '123456',
                    'passwordConfirm': '654321',
                }, bodyParams));
            }).then(done).catch(done);
        });
    });

    describe('requestEmailChange()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.requestEmailChange('test@example.com', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/request-email-change');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'newEmail': 'test@example.com',
                }, bodyParams));
            }).then(done).catch(done);
        });
    });

    describe('confirmEmailChange()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.confirmEmailChange('test_token', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/confirm-email-change');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'token': 'test_token',
                }, bodyParams));
            }).then(done).catch(done);
        });
    });

    describe('sendFeedback()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.sendFeedback('test', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/feedback');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'message': 'test',
                }, bodyParams));
            }).then(done).catch(done);
        });
    });

    describe('refresh()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.refresh(bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/refresh');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
            }).then(done).catch(done);
        });
    });

    describe('getList()', function() {
        it('Should correctly set request data', function(done) {
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.getList(2, 15, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, Object.assign({
                    'page': 2,
                    'per-page': 15,
                }, queryParams));
            }).then(done).catch(done);
        });
    });

    describe('getOne()', function() {
        it('Should correctly set request data', function(done) {
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.getOne(123, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/123');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, queryParams);
            }).then(done).catch(done);
        });
    });

    describe('create()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.create(bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
            }).then(done).catch(done);
        });
    });

    describe('update()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.update(123, bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/123');
                assert.equal(response.config.method, 'put');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
            }).then(done).catch(done);
        });
    });

    describe('delete()', function() {
        it('Should correctly set request data', function(done) {
            var bodyParams = { 'body_test1': 1, 'body_test2': 2 };
            var queryParams = { 'query_test1': 1, 'query_test2': 2 };
            var result = resource.delete(123, bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function(response) {
                assert.equal(response.config.url, '/users/123');
                assert.equal(response.config.method, 'delete');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
            }).then(done).catch(done);
        });
    });
});
