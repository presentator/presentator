<?php
namespace common\components\helpers;

use Yii;

/**
 * GeoIP helper application wrapper class for dpodium/yii2-geoip component.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GeoIPHelper
{
    /**
     * Returns valid application language key code based on GeoIP country code.
     * @param  string $default The default return value if client IP doesn't exist.
     * @return string
     */
    public static function detectLanguageCode($default = 'en')
    {
        try {
            $countryCode = strtolower(Yii::$app->geoip->lookupCountryCode());
        } catch (\Exception $e) {
            $countryCode = strtolower($default);
        }

        if ($countryCode === 'bg') {
            $lang = 'bg';
        } elseif ($countryCode === 'pl') {
            $lang = 'pl';
        } elseif ($countryCode === 'fr') {
            $lang = 'fr';
        } elseif ($countryCode === 'de') {
            $lang = 'de';
        } elseif ($countryCode === 'es') {
            $lang = 'es';
        } elseif ($countryCode === 'br' || $countryCode === 'pt' || $countryCode === 'pt-br') {
            $lang = 'pt-br';
        } elseif ($countryCode === 'sq' || $countryCode === 'al' || $countryCode === 'sq-al') {
            $lang = 'sq-al';
        } else {
            $lang = $default;
        }

        return $lang;
    }
}
