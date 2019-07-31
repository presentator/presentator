<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;

/**
 * Login form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class LoginForm extends ApiForm
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['email']    = Yii::t('app', 'Email');
        $labels['password'] = Yii::t('app', 'Password');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            [['email', 'password'], 'string', 'max' => 255],
            ['email', 'email'],
            ['email', 'exist', 'targetClass' => User::class, 'filter' => ['status' => User::STATUS['ACTIVE']]],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Inline user password validator.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        $user = $this->email ? User::findByEmail($this->email) : null;

        if (!$user || !$user->validatePassword($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid password.'));
        }
    }

    /**
     * Logs in a user using the provided email and password.
     *
     * @return null|User
     */
    public function login(): ?User
    {
        if ($this->validate()) {
            return User::findByEmail($this->email);
        }

        return null;
    }
}
