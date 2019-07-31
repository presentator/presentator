<?php
namespace presentator\api\validators;

use Yii;
use yii\validators\Validator;

/**
 * Validator proxy that calls user specified validator on multiple
 * values defined as concatenated string.
 *
 * Example usage:
 * ```php
 * // usage in `\yii\base\Model::rules()`
 * [
 *     'multipleEmails',
 *     MultipleProxyValidator::class,
 *     'validatorClass' => '\yii\validators\EmailValidator',
 *     'validatorOptions' => ['allowName' => true]
 * ]
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class MultipleProxyValidator extends Validator
{
    /**
     * @var string
     */
    public $validatorClass;

    /**
     * @var array
     */
    public $validatorOptions = [];

    /**
     * @var string
     */
    public $delimiter = ',';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!$this->message) {
            $this->message = Yii::t('app', 'Invalid formatted value.');
        }

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $result = $this->validateValue($model->$attribute);

        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        } else {
            $value = $model->{$attribute};

            if ($value) {
                // filter value
                $uniqueValue = array_unique(array_map('trim', explode(',', $value)));

                $model->{$attribute} = implode($this->delimiter . ' ', $uniqueValue);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $value          = (string) $value;
        $validatorClass = $this->validatorClass;
        $validator      = new $validatorClass($this->validatorOptions);

        $parts = explode($this->delimiter, $value);

        foreach ($parts as $part) {
            if (!$validator->validate(trim($part))) {
                return [$this->message, []];
            }
        }

        return null;
    }
}
