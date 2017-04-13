<?php
namespace api\models;

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
     * @var string
     */
    public $language;

    /**
     * @var boolean
     */
    public $notifications = true;

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
        $this->user = $user;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['language', 'in', 'range' => Yii::$app->params['languages']],
            [['notifications'], 'boolean'],
            ['newPassword', 'string', 'min' => 4, 'max' => 255],
            ['newPasswordConfirm', 'compare', 'compareAttribute'=>'newPassword', 'message'=> Yii::t('app', "Passwords don't match")],
            [['firstName', 'lastName'], 'filter', 'filter' => function ($value) {
                $value = trim($value);

                // capitalize first letter
                return (mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1));
            }],
            [['oldPassword'], 'required', 'when' => function ($model) {
                if (!empty($model->newPassword) || !empty($model->newPasswordConfirm)) {
                    return true;
                }

                return false;
            }],
            [['newPassword', 'newPasswordConfirm'], 'required', 'when' => function ($model) {
                if (!empty($model->oldPassword)) {
                    return true;
                }

                return false;
            }],
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
        if (!$this->user->validatePassword($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid old password.'));
        }
    }

    /**
     * Saves form settings to the user model.
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

            if ($this->newPassword) {
                $user->setPassword($this->newPassword);
            }

            return $user->save();
        }

        return false;
    }
}
