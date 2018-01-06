<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\components\validators\CEmailValidator;

/**
 * Register form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class RegisterForm extends Model
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
     * @var string
     */
    public $passwordConfirm;

    /**
     * @var boolean
     */
    public $terms = false;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'           => Yii::t('app', 'Email'),
            'password'        => Yii::t('app', 'Password'),
            'passwordConfirm' => Yii::t('app', 'Password confirm'),
            'terms'           => Yii::t('app', 'Terms'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password', 'passwordConfirm'], 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::className()],
            ['email', CEmailValidator::className(), 'allowedDomains' => Yii::$app->params['allowedRegistrationDomains']],
            ['password', 'string', 'min' => 4],
            ['passwordConfirm', 'compare', 'compareAttribute'=>'password', 'message'=> Yii::t('app', "Passwords don't match")],
            ['terms', 'required', 'requiredValue' => 1, 'message' => Yii::t('app', 'This checkbox is required.')],
        ];
    }

    /**
     * Creates an inactive user by its email and password.
     * @return null|User `User` model on success, otherwise - `null`.
     */
    public function register()
    {
        if ($this->validate()) {
            $user = new User;
            $user->status = User::STATUS_INACTIVE;
            $user->email = $this->email;
            $user->setPassword($this->password);

            // set initial user settings
            if ($user->save() && $user->sendActivationEmail()) {
                $user->setSetting(User::NOTIFICATIONS_SETTING_KEY, true);

                return $user;
            }
        }

        return null;
    }
}
