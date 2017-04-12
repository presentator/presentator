<?php
namespace api\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * API Register form model.
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
     * @var string
     */
    public $firstName = '';

    /**
     * @var string
     */
    public $lastName = '';

    /**
     * @var boolean
     */
    public $notifications = true;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password', 'passwordConfirm'], 'required'],
            ['email', 'email'],
            [['email', 'firstName', 'lastName'], 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::className()],
            ['password', 'string', 'min' => 4],
            ['passwordConfirm', 'compare', 'compareAttribute'=>'password', 'message'=> Yii::t('app', "Passwords don't match")],
            ['notifications', 'boolean'],
        ];
    }

    /**
     * Creates and return a new inactive user.
     * @return null|User
     */
    public function register()
    {
        if ($this->validate()) {
            $user            = new User;
            $user->status    = User::STATUS_INACTIVE;
            $user->email     = $this->email;
            $user->firstName = $this->firstName;
            $user->lastName  = $this->lastName;
            $user->setPassword($this->password);

            // set initial user settings
            if ($user->save() && $user->sendActivationEmail()) {
                $user->setSetting(User::LANGUAGE_SETTING_KEY, Yii::$app->language);
                $user->setSetting(User::NOTIFICATIONS_SETTING_KEY, $this->notifications);

                return $user;
            }
        }

        return null;
    }
}
