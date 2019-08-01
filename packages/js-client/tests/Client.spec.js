import {assert}     from 'chai';
import axios        from 'axios';
import mockAdapter  from 'axios-mock-adapter';
import Client       from '@/Client';
import BaseResource from '@/BaseResource';

/**
 * Client API tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
describe('Client', function () {
    var adapter = new mockAdapter(axios);

    // mock all requests
    adapter.onAny().reply(200);

    describe('constructor()', function () {
        it('Should create a properly configured http client instance', function () {
            var client = new Client('test_base_url', 'test_token', 'test_language', { 'timeout': 1000 });

            assert.equal(client.$baseUrl, 'test_base_url');
            assert.equal(client.$token, 'test_token');
            assert.equal(client.$language, 'test_language');
            assert.equal(client.$http.defaults.timeout, 1000);
            assert.equal(client.$http.defaults.baseURL, 'test_base_url');
            assert.equal(client.$http.defaults.headers.common['Authorization'], 'Bearer test_token');
            assert.equal(client.$http.defaults.headers.common['Accept-Language'], 'test_language');
        });

        it('Should load all api resources', function () {
            var client = new Client('test_base_url');

            var resourcesContext = require.context('@/resources/', true, /\.(js)$/);
            resourcesContext.keys().forEach((file) => {
                let resourceClass = resourcesContext(file);
                if (resourceClass && resourceClass.prototype instanceof BaseResource) {
                    assert.instanceOf(client[resourceClass.prototype.constructor.name], resourceClass);
                }
            });
        });
    });

    describe('cancelRequest()', function () {
        it('Should successfully clear a stored cancellation token', function () {
            var client = new Client('test_base_url');

            // set cancellation tokens
            client.$cancelSource['test1'] = axios.CancelToken.source();
            client.$cancelSource['test2'] = axios.CancelToken.source();

            var result = client.cancelRequest('test1');

            assert.instanceOf(result, Client);
            assert.isUndefined(client.$cancelSource['test1']);
            assert.isDefined(client.$cancelSource['test2']);
        });
    });

    describe('enableAutoCancellation()', function () {
        it('Should successfully enable duplicated request cancellation', function () {
            var client = new Client('test_base_url');
            var result = client.enableAutoCancellation();

            assert.instanceOf(result, Client);
            assert.isTrue(client.$enableAutoCancelation);
        });

        it('Should successfully disable duplicated request cancellation', function () {
            var client = new Client('test_base_url');

            // verify that initially auto cancellation is enabled
            assert.isTrue(client.$enableAutoCancelation);

            // disable auto cancellation
            var result = client.enableAutoCancellation(false);

            assert.instanceOf(result, Client);
            assert.isFalse(client.$enableAutoCancelation);
        });
    });

    describe('setToken()', function () {
        it('Should successfully set an authorization token and header', function () {
            var client = new Client('test_base_url');
            var result = client.setToken('test_token');

            assert.instanceOf(result, Client);
            assert.equal(client.$token, 'test_token');
            assert.equal(client.$http.defaults.headers.common['Authorization'], 'Bearer test_token');
        });

        it('Should successfully unset an authorization token and header', function () {
            var client = new Client('test_base_url', 'test_token');

            // verify that the client has defined auth token and header
            assert.equal(client.$token, 'test_token');
            assert.equal(client.$http.defaults.headers.common['Authorization'], 'Bearer test_token');

            // unset the token and header
            var result = client.setToken('');

            // verify that the client doesn't have defined auth token and header
            assert.instanceOf(result, Client);
            assert.equal(client.$token, '');
            assert.isUndefined(client.$http.defaults.headers.common['Authorization']);
        });
    });

    describe('setLanguage()', function () {
        it('Should successfully set language header', function () {
            var client = new Client('test_base_url');
            var result = client.setLanguage('test_lang');

            assert.instanceOf(result, Client);
            assert.equal(client.$language, 'test_lang');
            assert.equal(client.$http.defaults.headers.common['Accept-Language'], 'test_lang');
        });

        it('Should successfully unset language header', function () {
            var client = new Client('test_base_url');

            // verify that the client has set default language
            assert.equal(client.$language, 'en-US');
            assert.equal(client.$http.defaults.headers.common['Accept-Language'], 'en-US');

            // unset the language property and its related header
            var result = client.setLanguage('');

            // verify that the client doesn't have defined language property and related header value
            assert.instanceOf(result, Client);
            assert.equal(client.$language, '');
            assert.isUndefined(client.$http.defaults.headers.common['Accept-Language']);
        });
    });
});
