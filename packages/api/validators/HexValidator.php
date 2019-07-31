<?php
namespace presentator\api\validators;

use Yii;
use yii\validators\Validator;

/**
 * HEX color code validator.
 *
 * Sample valid values:
 * - '#000000'
 * - `#000`    (valid only if `fullLength` is `false`)
 * - '000000'  (valid only if `requireHash` is `false`)
 * - '000'     (valid only if `fullLength` and `requireHash` is `false`)
 *
 * Example usage:
 * ```php
 * // usage in `\yii\base\Model::rules()`
 * return [
 *     ['backgroundColor', HexValidator::class, 'fullLength' => false],
 *     ...
 * ];
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HexValidator extends Validator
{
    /**
     * @var boolean
     */
    public $requireHash = true;

    /**
     * @var boolean
     */
    public $fullLength = true;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!$this->message) {
            $this->message = Yii::t('app', 'Invalid HEX color code.');
        }

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $value = (string) $value;

        if (substr($value, 0, 1) === '#') {
            // trim first # character
            $value = substr($value, 1);
        } elseif ($this->requireHash) {
            // missing # character
            return [$this->message, []];
        }

        if ($this->fullLength) {
            $validLengths = [6];
        } else {
            $validLengths = [3, 6];
        }

        if (
            // invalid hexadecimal characters
            !ctype_xdigit($value) ||
            // invalid string length
            !in_array(strlen($value), $validLengths)
        ) {
            return [$this->message, []];
        }

        return null;
    }
}
