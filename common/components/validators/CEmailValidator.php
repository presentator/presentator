<?php
namespace common\components\validators;

use yii\validators\EmailValidator;

/**
 * CEmailValidator that extends the default yii2 rest EmailValidator class
 * to allow validating multiple comma separated emails.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CEmailValidator extends EmailValidator
{
    /**
     * @inheritdoc
     */
    public $allowName = true;

    /**
     * Allow multiple comma separated emails (eg. 'test@presentator.io, John Doe <john.doe@presentator.io>').
     * @var boolean
     */
    public $allowMultiple = true;

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $parts = explode(',', $value);

        if ($this->allowMultiple && count($parts) > 1) {
            $result = null;

            foreach ($parts as $email) {
                $result = parent::validateValue(trim($email));

                if ($result !== null) {
                    break;
                }
            }

            return $result;
        }

        return parent::validateValue($value);
    }
}
