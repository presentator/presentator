<?php
namespace common\components\helpers;

use yii\base\Arrayable;
use yii\helpers\ArrayHelper;

/**
 * A bit modified version of the default Yii ArrayHelper class.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CArrayHelper extends ArrayHelper
{
    /**
     * Count array group elemetns based on keys list.
     *
     * @example
     * ```php
     * $group = [
     *    '1' => [
     *        ['myValue1', 'myValue2'],
     *        ['myValue1', 'myValue2']
     *    ],
     *    '2' => [
     *        ['myKey' => 'myValue', ...]
     *    ]
     * ];
     *
     * $result = CArrayHelper::countGroupByKeys(['1', '2', '3'], $group);
     * ```
     *
     * The $result will be:
     * ```php
     * [
     *     '1' => 2,
     *     '2' => 1,
     *     '3' => 0,
     * ]
     * ```
     *
     * @param  array $keys
     * @param  array $group
     * @return array
     */
    public static function countGroupByKeys(array $keys, array $group = [])
    {
        $result = [];

        foreach ($keys as $key) {
            if (isset($group[$key])) {
                $result[$key] = count($group[$key]);
            } else {
                $result[$key] = 0;
            }
        }

        return $result;
    }

    /**
     * Converts an object or an array of objects into an array.
     * @see `yii\helpers\BaseArrayHelper::toArray()`
     * @param mixed $object      The object to be converted into an array
     * @param array $properties  A mapping from object class names to the properties that need to put into the resulting arrays.
     * @param boolean $recursive Whether to recursively converts properties which are objects into arrays.
     * @param array $expands     Model expand rel params.
     * @return array the array representation of the object
     */
    public static function toArray($object, $properties = [], $recursive = true, $expands = [])
    {
        if (is_array($object)) {
            if ($recursive) {
                foreach ($object as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        if(is_int($key)){
                            $expand = $expands;
                        }elseif (isset ($expands[$key])) {
                            $expand = $expands[$key];
                        }  else {
                            $expand = [];
                        }
                        $object[$key] = static::toArray($value, $properties, true, $expand);
                    }
                }
            }

            return $object;
        } elseif (is_object($object)) {
            if (!empty($properties)) {
                $className = get_class($object);
                if (!empty($properties[$className])) {
                    $result = [];
                    foreach ($properties[$className] as $key => $name) {
                        if (is_int($key)) {
                            $result[$name] = $object->$name;
                        } else {
                            $result[$key] = static::getValue($object, $name);
                        }
                    }

                    return $recursive ? static::toArray($result, $properties) : $result;
                }
            }
            if ($object instanceof Arrayable) {
                $result = $object->toArray([], $expands, $recursive);
            } else {
                $result = [];
                foreach ($object as $key => $value) {
                    $result[$key] = $value;
                }
            }

            return $recursive ? static::toArray($result, [], true, $expands) : $result;
        } else {
            return [$object];
        }
    }
}
