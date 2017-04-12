<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use common\models\User;

/**
 * Password reset form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PasswordResetForm extends Model
{
    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $passwordConfirm;

    /**
     * @var User
     */
    private $user;

    /**
     * Creates a form model from a password reset token.
     * @param  string $token
     * @param  array $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('Password reset token cannot be blank.');
        }

        $this->user = User::findByPasswordResetToken($token);
        if (!$this->user) {
            throw new InvalidParamException('Wrong or expired password reset token.');
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password'        => Yii::t('app', 'Password'),
            'passwordConfirm' => Yii::t('app', 'Password confirm'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'passwordConfirm'], 'required'],
            ['password', 'string', 'min' => 4],
            ['passwordConfirm', 'compare', 'compareAttribute'=>'password', 'message'=> Yii::t('app', "Passwords don't match")],
        ];
    }

    /**
     * Changes user password.
     * @return boolean
     */
    public function reset()
    {
        if ($this->validate()) {
            $this->user->setPassword($this->password);
            $this->user->removePasswordResetToken();

            return $this->user->save();
        }

        return false;
    }
}
