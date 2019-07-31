<?php
namespace presentator\api\validators;

use Yii;
use yii\validators\Validator;

/**
 * Email domain validator.
 *
 * Example usage:
 * ```php
 * // usage in `\yii\base\Model::rules()`
 * return [
 *     // default domain filters
 *     ['email1', EmailDomainValidator::class],
 *
 *     // custom domain filters
 *     ['email2', EmailDomainValidator::class, 'onlyDomains' => 'test.com', 'exceptDomains' => []],
 * ];
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class EmailDomainValidator extends Validator
{
    /**
     * @var array
     */
    public $onlyDomains;

    /**
     * @var array
     */
    public $exceptDomains;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!$this->message) {
            $this->message = Yii::t('app', 'Email address domain is not allowed.');
        }

        if ($this->onlyDomains === null) {
            $this->onlyDomains = Yii::$app->params['onlyDomainsRegisterFilter'];
        }

        if ($this->exceptDomains === null) {
            $this->exceptDomains = Yii::$app->params['exceptDomainsRegisterFilter'];
        }

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $value  = (string) $value;
        $domain = substr(strrchr(trim($value), '@'), 1);

        if (
            (!empty($this->onlyDomains) && !in_array($domain, $this->onlyDomains)) ||
            (!empty($this->exceptDomains) && in_array($domain, $this->exceptDomains))
        ) {
            return [$this->message, []];
        }

        return null;
    }
}
