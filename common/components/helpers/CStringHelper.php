<?php
namespace common\components\helpers;

use yii\helpers\StringHelper;

/**
 * Extended StringHelper class.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CStringHelper extends StringHelper
{
    /**
     * Return typecasted value from a string based on the following schema:
     * - integer/float - if the value is numeric
     * - null          - if the value is `null` or `'null'` string
     * - boolean       - if the value is boolean or its equal string representation
     * - string        - in all other cases
     * @param  string $value
     * @return mixed
     */
    public static function autoTypecast($value)
    {
        $normalizedStringValue = strtolower($value);

        // auto typecast
        if (is_numeric($value)) {
            return $value + 0;
        } elseif ($value === null || $normalizedStringValue === 'null') {
            return null;
        } elseif ($value === false || $normalizedStringValue === 'false') {
            return false;
        } elseif ($value === true || $normalizedStringValue === 'true') {
            return true;
        }

        return $value;
    }

    /**
     * Parses an address string as defined in RFC2822.
     * NB! This method doesn't check whether the address is valid or not.
     * For more complete RFC2822 coverage see `imap_rfc822_parse_adrlist`.
     *
     * @example
     * ```php
     * CStringHelper::parseAddresses('John Doe <john.doe@presentator.io>, test@presentator.io');
     *
     * // sample result:
     * [
     *     'john.doe@presentator.io' => 'John Doe',
     *     'test@presentator.io' => null
     * ]
     * ```
     * @param  string $addressesString
     * @return array
     */
    public static function parseAddresses($addressesString)
    {
        $parsed    = [];
        $addresses = explode(',', $addressesString);


        foreach ($addresses as $address) {
            $split = explode(' <', trim($address));

            if (trim($split[0]) === '') {
                continue;
            }

            if (!empty($split[1])) { // eg. John Doe <john.doe@presentator.io>
                $name = trim($split[0]);
                $addr = rtrim($split[1], '>');
            } else { // eg. test@presentator.io
                $name = null;
                $addr = $split[0];
            }

            $parsed[$addr] = $name;
        }

        return $parsed;
    }
}
