<?php
namespace api\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * API Login form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class LoginForm extends Model
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
     * @var null|User
     * @see `self::getUser()`
     */
    private $user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            [['email', 'password'], 'string', 'max' => 255],
            ['email', 'email'],
            ['email', 'exist', 'targetClass' => User::className(), 'filter' => ['status' => User::STATUS_ACTIVE]],
            ['password', 'validatePassword'],
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
            $this->addError($attribute, Yii::t('app', 'Invalid password.'));
        }
    }

    /**
     * Returns the related user model.
     * @return null|User
     */
    public function getUser()
    {
        if ($this->user === null) {
            $this->user = User::findByEmail($this->email);
        }

        return $this->user;
    }

    /**
     * Logs in a user using the provided email and password.
     * @return boolean
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();

            Yii::$app->response->headers->set('X-Access-Token', $user->generateJwtToken());

            return true;
        }

        return false;
    }
}
