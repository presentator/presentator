<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * @todo Rename notifications user setting.
 *
 * User notifications settings form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserNotificationsForm extends Model
{
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
            'notifications' => Yii::t('app', 'Receive an email when a new screen comment is added'),
            'mentions'      => Yii::t('app', 'Receive an email when someone mentions you'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['notifications', 'mentions'], 'boolean'],
        ];
    }

    /**
     * Helper to load some of the form model parameters from a User model.
     * @param User $user
     */
    public function loadUser(User $user)
    {
        $this->user          = $user;
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
            $user = $this->user;

            $user->setSetting(User::NOTIFICATIONS_SETTING_KEY, $this->notifications ? true : false);
            $user->setSetting(User::MENTIONS_SETTING_KEY, $this->mentions ? true : false);

            return $user->save();
        }

        return false;
    }
}
