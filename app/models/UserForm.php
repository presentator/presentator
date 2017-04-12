<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * User settings form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserForm extends Model
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $oldPassword;

    /**
     * @var string
     */
    public $newPassword;

    /**
     * @var string
     */
    public $newPasswordConfirm;

    /**
     * @var boolean
     */
    public $changePassword = false;

    /**
     * @var string
     */
    public $language;

    /**
     * @var boolean
     */
    public $notifications;

    /**
     * @var User
     */
    private $user;

    /**
     * Model constructor.
     * @param User  $user
     * @param array $config
     */
    public function __construct(User $user, $config = [])
    {
        $this->loadUser($user);

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'              => Yii::t('app', 'Email'),
            'firstName'          => Yii::t('app', 'First name'),
            'lastName'           => Yii::t('app', 'Last name'),
            'changePassword'     => Yii::t('app', 'Change password'),
            'oldPassword'        => Yii::t('app', 'Old password'),
            'newPassword'        => Yii::t('app', 'New password'),
            'newPasswordConfirm' => Yii::t('app', 'New password confirmation'),
            'notifications'      => Yii::t('app', 'Receive an email when a new screen comment is added'),
            'language'           => Yii::t('app', 'Preferred language'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['language', 'in', 'range' => Yii::$app->params['languages']],
            [['changePassword', 'notifications'], 'boolean'],
            ['newPassword', 'string', 'min' => 4, 'max' => 255],
            ['newPasswordConfirm', 'compare', 'compareAttribute'=>'newPassword', 'message'=> Yii::t('app', "Passwords don't match")],
            [['firstName', 'lastName'], 'filter', 'filter' => function ($value) {
                $value = trim($value);

                // capitalize first letter
                return (mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1));
            }],
            [['oldPassword', 'newPassword', 'newPasswordConfirm'], 'required', 'when' => function ($model) {
                if ($model->changePassword) {
                    return true;
                }

                return false;
            }, 'whenClient' => 'function (attribute, value) {
                if ($("#settingsform-changepassword").is(":checked")) {
                    return true;
                }

                return false;
            }'],
            ['oldPassword', 'validateOldPassword'],
        ];
    }

    /**
     * User old password inline validator.
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateOldPassword($attribute, $params)
    {
        if ($this->changePassword && !$this->user->validatePassword($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid old password.'));
        }
    }

    /**
     * Helper to load some of the form model parameters from a User model.
     * @param User $user
     */
    public function loadUser(User $user)
    {
        $this->user = $user;
        $this->firstName     = $this->user->firstName;
        $this->lastName      = $this->user->lastName;
        $this->email         = $this->user->email;
        $this->notifications = $this->user->getSetting(User::NOTIFICATIONS_SETTING_KEY, true);
        $this->language      = $this->user->getSetting(User::LANGUAGE_SETTING_KEY, Yii::$app->language);
    }

    /**
     * Saves model settings to the user model.
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $user = $this->user;
            $user->firstName = $this->firstName;
            $user->lastName  = $this->lastName;
            $user->setSetting(User::NOTIFICATIONS_SETTING_KEY, $this->notifications ? true : false);
            $user->setSetting(User::LANGUAGE_SETTING_KEY, $this->language ? $this->language : Yii::$app->language);

            if ($this->changePassword) {
                $user->setPassword($this->newPassword);
                // reset password fields
                $this->changePassword     = false;
                $this->oldPassword        = '';
                $this->newPassword        = '';
                $this->newPasswordConfirm = '';
            }

            return $user->save();
        }

        return false;
    }
}
