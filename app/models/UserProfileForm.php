<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * User profile settings form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserProfileForm extends Model
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
     * @var string
     */
    public $password;

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
            'firstName' => Yii::t('app', 'First name'),
            'lastName'  => Yii::t('app', 'Last name'),
            'email'     => Yii::t('app', 'Email'),
            'password'  => Yii::t('app', 'Password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'email'],
            ['email', 'required'],
            ['email', 'unique', 'targetClass' => User::className(), 'filter' => function ($query) {
                $query->andWhere(['not', ['email' => $this->user->email]]);
            }],
            [['firstName', 'lastName', 'password'], 'string', 'max' => 255],
            [['firstName', 'lastName'], 'filter', 'filter' => function ($value) {
                $value = trim($value);

                // capitalize first letter
                return (mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1));
            }],
            ['password', 'required', 'when' => function ($model) {
                if ($model->email !== $this->user->email) {
                    return true;
                }

                return false;
            }, 'whenClient' => 'function (attribute, value) {
                if ($(attribute.input).is(":visible")) {
                    return true;
                }

                return false;
            }'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * User password inline validator.
     * @param string $attribute
     * @param mixed  $params
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->user->validatePassword((string) $this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid password.'));
        }
    }

    /**
     * Helper to load some of the form model parameters from a User model.
     * @param User $user
     */
    public function loadUser(User $user)
    {
        $this->user      = $user;
        $this->firstName = $this->user->firstName;
        $this->lastName  = $this->user->lastName;
        $this->email     = $this->user->email;
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

            if ($user->email !== $this->email) {
                $user->generateEmailChangeToken($this->email);

                $user->sendEmailChangeEmail($this->email);
            }

            return $user->save();
        }

        return false;
    }
}
