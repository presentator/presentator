import PresentatorClient from 'presentator-client';
import AppConfig         from '@/utils/AppConfig';
import ClientStorage     from '@/utils/ClientStorage';

PresentatorClient.prototype.storeApiToken = function (token) {
    ClientStorage.setItem('auth_token', token);

    this.setToken(token);
};

PresentatorClient.prototype.getStoredApiToken = function () {
    return ClientStorage.getItem('auth_token', '');
};

PresentatorClient.prototype.loadStoredApiToken = function () {
    this.setToken(this.getStoredApiToken());
};

PresentatorClient.prototype.clearStoredApiToken = function () {
    ClientStorage.removeItem('auth_token');
};

const client = new PresentatorClient(AppConfig.get('VUE_APP_API_URL'));

client.loadStoredApiToken();

export default client;
