<?php
namespace common\components\validators;

use Yii;
use yii\validators\EmailValidator;

/**
 * CEmailValidator that extends the default yii2 rest EmailValidator class
 * to allow validating multiple comma separated emails and add domains constraint.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CEmailValidator extends EmailValidator
{
    /**
     * Allow multiple comma separated emails (eg. 'test@presentator.io, John Doe <john.doe@presentator.io>').
     * @var boolean
     */
    public $allowMultiple = false;

    /**
     * List of allowed email domains (leave empty for no limitation).
     * @var array
     */
    public $allowedDomains = [];

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if ($this->allowMultiple) {
            $parts = explode(',', $value);

            if (count($parts) > 1) {
                return $this->validateMultiple($parts);
            }
        }

        $domainValidation = $this->validateDomain($value);
        if ($domainValidation !== null) {
            return $domainValidation;
        }

        return parent::validateValue($value);
    }

    /**
     * Validates multiple email addresses.
     * @param  array $addresses
     * @return null|array
     */
    protected function validateMultiple($addresses)
    {
        foreach ($addresses as $email) {
            $formatValidation = parent::validateValue(trim($email));
            if ($formatValidation !== null) {
                return $formatValidation;
            }

            $domainValidation = $this->validateDomain($email);
            if ($domainValidation !== null) {
                return $domainValidation;
            }
        }

        return null;
    }

    /**
     * Checks if the address domain is listed in the allowed ones.
     * @param  string $value
     * @return null|array
     */
    protected function validateDomain($value)
    {
        if (!empty($this->allowedDomains)) {
            $domain = substr(strrchr(trim($value), '@'), 1);

            if (!in_array($domain, $this->allowedDomains)) {
                return [Yii::t('app', 'Registrations from {domain} domain are not allowed.', ['domain' => $domain]), []];
            }
        }

        return null;
    }
}
