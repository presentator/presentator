<?php
namespace presentator\api\helpers;

use yii\helpers\StringHelper;

/**
 * Safely cast any value to string, integer, float, boolean or array.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CastHelper
{
    /**
     * @param  mixed $value
     * @return string
     */
    public static function toString($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return StringHelper::floatToString($value);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value) || is_object($value)) {
            return @json_encode($value);
        }

        return '';
    }

    /**
     * @param  mixed $value
     * @return int
     */
    public static function toInt($value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return 0;
    }

    /**
     * @param  mixed $value
     * @return int
     */
    public static function toFloat($value): float
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }

    /**
     * @param  mixed $value
     * @return boolean
     */
    public static function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return (bool) $value;
    }

    /**
     * @param  mixed $value
     * @return array
     */
    public static function toArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $trimmedValue = trim($value);
            $firstChar    = substr($trimmedValue, 0, 1);
            $lastChar     = substr($trimmedValue, -1, 1);

            // loose json array/object string check
            if (
                ($firstChar === '{' || $firstChar === '[') &&
                ($lastChar === '}' || $lastChar === ']')
            ) {
                return (array) @json_decode($value, true);
            }
        }

        if ($value === '' || $value === null) {
            return [];
        }

        return (array) $value;
    }
}
