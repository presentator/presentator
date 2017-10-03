<?php
namespace common\components\helpers;

use Yii;

/**
 * EmailHeler class that implements commonly used methods to work with email addresses.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class EmailHelper
{
    /**
     * Parses an address string as defined in RFC2822.
     * NB! This method doesn't check whether the address is valid or not.
     * For more complete RFC2822 coverage see `imap_rfc822_parse_adrlist`.
     *
     * @example
     * ```php
     * MailHelper::stringToArray('John Doe <john.doe@presentator.io>, test@presentator.io');
     *
     * // sample output:
     * [
     *     'john.doe@presentator.io' => 'John Doe',
     *     'test@presentator.io' => null
     * ]
     * ```
     *
     * @see `self::arrayToString()`
     * @param  string $str
     * @return array
     */
    public static function stringToArray($str)
    {
        $parsed    = [];
        $addresses = explode(',', $str);

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

    /**
     * Parse an address array into a string as defined in RFC2822.
     *
     * @example
     * ```php
     * MailHelper::arrayToString([
     *     'john.doe@presentator.io' => John Doe,
     *     'test@presentator.io' => null
     * ]);
     *
     * // sample output:
     * 'John Doe <john.doe@presentator.io>, test@presentator.io'
     * ```
     *
     * @see `self::stringToArray()`
     * @param  array $arr
     * @return string
     */
    public static function arrayToString(array $arr)
    {
        $pieces = [];

        foreach ($arr as $email => $name) {
            $pieces[] = $name ? sprintf('%s <%s>', $name, $email) : $email;
        }

        return implode(', ', $pieces);
    }
}
