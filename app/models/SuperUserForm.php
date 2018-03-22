<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * @todo Rename notifications to comments user setting (update also the UserNotificationForm)
 *
 * User form model intented to be used by system admins for User create/update.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class SuperUserForm extends Model
{
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
    public $email;

    /**
     * @var integer
     */
    public $status;

    /**
     * @var integer
     */
    public $type;

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
    public $notifications = true;

    /**
     * @var boolean
     */
    public $mentions = true;

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
            'firstName'       => Yii::t('app', 'First name'),
            'lastName'        => Yii::t('app', 'Last name'),
            'email'           => Yii::t('app', 'Email'),
            'password'        => Yii::t('app', 'Password'),
            'passwordConfirm' => Yii::t('app', 'Password confirmation'),
            'notifications'   => Yii::t('app', 'Receive an email when a new screen comment is added'),
            'mentions'        => Yii::t('app', 'Receive an email when someone mentions you'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['type', 'default', 'value' => User::TYPE_REGULAR],
            ['type', 'in', 'range' => array_keys(User::getTypeLabels())],
            ['status', 'default', 'value' => static::STATUS_INACTIVE],
            ['status', 'in', 'range' => array_keys(User::getStatusLabels())],
            [['email', 'status', 'type'], 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::className(), 'filter' => function ($query) {
                $query->andWhere(['not', ['email' => $this->user->email]]);
            }],
            [['firstName', 'lastName'], 'string', 'max' => 255],
            [['firstName', 'lastName'], 'filter', 'filter' => function ($value) {
                $value = trim($value);

                // capitalize first letter
                return (mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1));
            }],
            ['password', 'string', 'min' => 4, 'max' => 255],
            ['passwordConfirm', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('app', "Passwords don't match")],
            [['notifications', 'mentions'], 'boolean'],
        ];
    }

    /**
     * Loads User settings into the form model.
     * @param User $user
     */
    public function loadUser(User $user)
    {
        $this->user          = $user;
        $this->firstName     = $this->user->firstName;
        $this->lastName      = $this->user->lastName;
        $this->email         = $this->user->email;
        $this->status        = $this->user->status;
        $this->type          = $this->user->type;
        $this->notifications = $this->user->getSetting(User::NOTIFICATIONS_SETTING_KEY, true);
        $this->mentions      = $this->user->getSetting(User::MENTIONS_SETTING_KEY, true);
    }

    /**
     * Saves model settings to the user model.
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $user            = $this->user;
            $user->firstName = $this->firstName;
            $user->lastName  = $this->lastName;
            $user->email     = $this->email;
            $user->status    = $this->status;
            $user->type      = $this->type;

            if ($this->password) {
                $user->setPassword($this->password);
            }

            $user->setSetting(User::NOTIFICATIONS_SETTING_KEY, $this->notifications ? true : false);
            $user->setSetting(User::MENTIONS_SETTING_KEY, $this->mentions ? true : false);

            return $user->save();
        }

        return false;
    }
}
