import moment        from 'moment';
import Vue           from 'vue';
import VueI18n       from 'vue-i18n';
import AppConfig     from '@/utils/AppConfig';
import ApiClient     from '@/utils/ApiClient';
import ClientStorage from '@/utils/ClientStorage';

Vue.use(VueI18n);

export const supportedLanguages = {
    "en-US": "English",
    "bg-BG": "Български",
    "de-DE": "Deutsch",
    "nl-NL": "Nederlands",
    "pt-BR": "Português",
};

// Extend the internal `VueI18n._translate` method in order to
// populate the source language messages on runtime
const sourceMessages = {};
const i18nTranslate = VueI18n.prototype._translate;
VueI18n.prototype._translate = function (messages, locale, fallback, key, host, interpolateMode, args) {
    if (!sourceMessages[key]) {
        sourceMessages[key] = key;
    }

    return i18nTranslate.apply(this, arguments);
};

const defaultLanguage = Object.keys(supportedLanguages)[0];

const i18nMessages = {};
i18nMessages[defaultLanguage] = sourceMessages;

// Create VueI18n instance
export const i18n = new VueI18n({
    locale:         defaultLanguage,
    fallbackLocale: defaultLanguage,
    messages:       i18nMessages,
});

// Holds the languages that are loaded
const loadedLanguages = [defaultLanguage]

// Async loads and sets i18n language
export const changeLanguage = function (code) {
    if (i18n.locale !== code) {
        if (loadedLanguages.indexOf(code) == -1) {
            return import(/* webpackChunkName: "messages-[request]" */ `@/messages/${code}`).then((messages) => {
                i18n.setLocaleMessage(code, messages.default);

                loadedLanguages.push(code);

                return setI18nLanguage(code);
            });
        }

        return Promise.resolve(setI18nLanguage(code));
    }

    return Promise.resolve(code);
}

// Sets loaded i18n language
const setI18nLanguage = function (code) {
    i18n.locale = code;

    ClientStorage.setItem(AppConfig.get('VUE_APP_PREFERRED_LANGUAGE_STORAGE_KEY'), code);

    ApiClient.setLanguage(code);

    moment.locale(code);

    document.querySelector('html').setAttribute('lang', code)

    return code;
}

// Loosely search for a supported language code (eg. 'us' will match 'en-US', 'BG' will match 'bg-BG', etc.).
const looseLanguageSearch = function (language) {
    // direct match
    if (supportedLanguages[language]) {
        return language;
    }

    language = (language || '').toLowerCase();

    // loose search
    for (let code in supportedLanguages) {
        let codeParts = code.toLowerCase().split('-');
        for (let i in codeParts) {
            if (language === codeParts[i]) {
                return code;
            }
        }
    }

    // fallback to the first available language
    return Object.keys(supportedLanguages)[0];
}

// Load user defined language (if missing - try to load the browser's default)
changeLanguage(ClientStorage.getItem(
    AppConfig.get('VUE_APP_PREFERRED_LANGUAGE_STORAGE_KEY'),
    looseLanguageSearch(window.navigator.userLanguage || window.navigator.language)
));
