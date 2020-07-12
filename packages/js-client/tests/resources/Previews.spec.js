import {assert}    from 'chai';
import axios       from 'axios';
import mockAdapter from 'axios-mock-adapter';
import Previews    from '@/resources/Previews';

/**
 * Previews API resource tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
describe('Previews', function () {
    var adapter  = new mockAdapter(axios);
    var resource = new Previews(axios.create());

    // mock all requests
    adapter.onAny().reply(200);

    describe('authorize()', function () {
        it('Should correctly set request data', function (done) {
            var bodyParams  = {'body_test1': 1, 'body_test2': 2};
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.authorize('myslug', 'mypassword', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/previews');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'slug':     'myslug',
                    'password': 'mypassword',
                }, bodyParams));
            }).then(done).catch(done);
        });
    });

    describe('getOne()', function () {
        it('Should correctly set request data', function (done) {
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.getOne('test_token', queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/previews');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, queryParams);
                assert.equal(response.config.headers['X-Preview-Token'], 'test_token');
            }).then(done).catch(done);
        });
    });

    describe('getPrototype()', function () {
        it('Should correctly set request data', function (done) {
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.getPrototype('test_token', 123, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/previews/prototypes/123');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, queryParams);
                assert.equal(response.config.headers['X-Preview-Token'], 'test_token');
            }).then(done).catch(done);
        });
    });

    describe('getAssets()', function () {
        it('Should correctly set request data', function (done) {
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.getAssets('test_token', queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/previews/assets');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, queryParams);
                assert.equal(response.config.headers['X-Preview-Token'], 'test_token');
            }).then(done).catch(done);
        });
    });

    describe('getScreenCommentsList()', function () {
        it('Should correctly set request data', function (done) {
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.getScreenCommentsList('test_token', 2, 15, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/previews/screen-comments');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, Object.assign({
                    'page':     2,
                    'per-page': 15,
                }, queryParams));
                assert.equal(response.config.headers['X-Preview-Token'], 'test_token');
            }).then(done).catch(done);
        });
    });

    describe('createScreenComment()', function () {
        it('Should correctly set request data', function (done) {
            var bodyParams  = {'body_test1': 1, 'body_test2': 2};
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.createScreenComment('test_token', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/previews/screen-comments');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
                assert.equal(response.config.headers['X-Preview-Token'], 'test_token');
            }).then(done).catch(done);
        });
    });

    describe('updateScreenComment()', function () {
        it('Should correctly set request data', function (done) {
            var bodyParams  = {'body_test1': 1, 'body_test2': 2};
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.updateScreenComment('test_token', 123, bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/previews/screen-comments/123');
                assert.equal(response.config.method, 'put');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
                assert.equal(response.config.headers['X-Preview-Token'], 'test_token');
            }).then(done).catch(done);
        });
    });

    describe('report()', function () {
        it('Should correctly set request data', function (done) {
            var bodyParams  = {'body_test1': 1, 'body_test2': 2};
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.report('test_token', 'test', bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/previews/report');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), Object.assign({
                    'details': 'test',
                }, bodyParams));
                assert.equal(response.config.headers['X-Preview-Token'], 'test_token');
            }).then(done).catch(done);
        });
    });
});
