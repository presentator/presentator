<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * User password settings form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserPasswordForm extends Model
{
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
            'oldPassword'        => Yii::t('app', 'Old password'),
            'newPassword'        => Yii::t('app', 'New password'),
            'newPasswordConfirm' => Yii::t('app', 'New password confirmation'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oldPassword', 'newPassword', 'newPasswordConfirm'], 'required'],
            ['oldPassword', 'validateOldPassword'],
            ['newPassword', 'string', 'min' => 4, 'max' => 255],
            ['newPasswordConfirm', 'compare', 'compareAttribute' => 'newPassword', 'message' => Yii::t('app', "Passwords don't match")],
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
     * Helper to load some of the form model parameters from a User model.
     * @param User $user
     */
    public function loadUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Saves model settings to the user model.
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $user = $this->user;
            $user->setPassword($this->newPassword);

            $result = $user->save();

            if ($result) {
                // reset password fields
                $this->oldPassword        = '';
                $this->newPassword        = '';
                $this->newPasswordConfirm = '';
            }

            return $result;
        }

        return false;
    }
}
