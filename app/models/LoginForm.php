<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\User;
use \himiklab\yii2\recaptcha\ReCaptchaValidator;

/**
 * Login form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class LoginForm extends Model
{
    const SCENARIO_RECAPTCHA = 'scenarioReCaptcha';

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $reCaptcha;

    /**
     * @var User
     */
    private $user;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'    => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['email', 'exist', 'targetClass' => User::className(), 'filter' => ['status' => User::STATUS_ACTIVE]],
            ['password', 'validatePassword'],
            [['reCaptcha'], ReCaptchaValidator::className(), 'on' => self::SCENARIO_RECAPTCHA],
        ];
    }

    /**
     * Inline validator for the password.
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        $user = $this->getUser();

        if (!$user || !$user->validatePassword($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid login credentials.'));
        }
    }

    /**
     * Logs in a user using the provided email and password.
     * @return bool
     */
    public function login()
    {
        if ($this->validate() && $this->getUser()) {
            return Yii::$app->user->login($this->getUser(), Yii::$app->params['rememberMeDuration']);
        }

        return false;
    }

    /**
     * Finds user by `email`.
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->user === null) {
            $this->user = User::findByEmail($this->email);
        }

        return $this->user;
    }
}
