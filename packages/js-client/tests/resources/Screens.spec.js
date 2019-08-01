import {assert}    from 'chai';
import axios       from 'axios';
import mockAdapter from 'axios-mock-adapter';
import Screens     from '@/resources/Screens';

/**
 * Screens API resource tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
describe('Screens', function () {
    var adapter  = new mockAdapter(axios);
    var resource = new Screens(axios.create());

    // mock all requests
    adapter.onAny().reply(200);

    describe('getList()', function () {
        it('Should correctly set request data', function (done) {
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.getList(2, 15, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/screens');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, Object.assign({
                    'page':     2,
                    'per-page': 15,
                }, queryParams));
            }).then(done).catch(done);
        });
    });

    describe('getOne()', function () {
        it('Should correctly set request data', function (done) {
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.getOne(123, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/screens/123');
                assert.equal(response.config.method, 'get');
                assert.deepEqual(response.config.params, queryParams);
            }).then(done).catch(done);
        });
    });

    describe('create()', function () {
        it('Should correctly set request data', function (done) {
            var bodyParams  = {'body_test1': 1, 'body_test2': 2};
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.create(bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/screens');
                assert.equal(response.config.method, 'post');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
            }).then(done).catch(done);
        });
    });

    describe('update()', function () {
        it('Should correctly set request data', function (done) {
            var bodyParams  = {'body_test1': 1, 'body_test2': 2};
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.update(123, bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/screens/123');
                assert.equal(response.config.method, 'put');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
            }).then(done).catch(done);
        });
    });

    describe('delete()', function () {
        it('Should correctly set request data', function (done) {
            var bodyParams  = {'body_test1': 1, 'body_test2': 2};
            var queryParams = {'query_test1': 1, 'query_test2': 2};
            var result      = resource.delete(123, bodyParams, queryParams);

            assert.instanceOf(result, Promise);
            result.then(function (response) {
                assert.equal(response.config.url, '/screens/123');
                assert.equal(response.config.method, 'delete');
                assert.deepEqual(response.config.params, queryParams);
                assert.deepEqual(JSON.parse(response.config.data), bodyParams);
            }).then(done).catch(done);
        });
    });
});
