<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;

/**
 * User password reset form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserPasswordResetForm extends ApiForm
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $passwordConfirm;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['token']           = Yii::t('app', 'Token');
        $labels['password']        = Yii::t('app', 'Password');
        $labels['passwordConfirm'] = Yii::t('app', 'Password confirm');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token', 'password', 'passwordConfirm'], 'required'],
            ['token', 'validateToken'],
            [['password', 'passwordConfirm'], 'string', 'min' => 6, 'max' => 71],
            ['passwordConfirm', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('app', "Passwords don't match.")],
        ];
    }

    /**
     * Inline token validator.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateToken($attribute, $params): void
    {
        $user = $this->{$attribute} ? User::findByPasswordResetToken($this->{$attribute}) : null;

        if (!$user) {
            $this->addError($attribute, Yii::t('app', 'Invalid or expired token.'));
        }
    }

    /**
     * Resets the password for the provided user model.
     *
     * @return null|User
     */
    public function save(): ?User
    {
        if (
            $this->validate() &&
            ($user = User::findByPasswordResetToken($this->token))
        ) {
            $user->setPassword($this->password);

            $user->clearPasswordResetToken();

            $user->generateAuthKey();

            if ($user->save()) {
                return $user;
            }
        }

        return null;
    }
}
