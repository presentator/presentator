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
}
