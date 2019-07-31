<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;

/**
 * User password reset request form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserPasswordResetRequestForm extends ApiForm
{
    /**
     * @var string
     */
    public $email;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['email'] = Yii::t('app', 'Email');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
        ];
    }

    /**
     * Sends a forgotten password email to the provided form email address.
     *
     * NB! If the form data is valid this method will always return `true`,
     * no mather whether a user with such email exist or not,
     * in order to prevent unnecessary information disclosure and users enumeration.
     *
     * @return boolean
     */
    public function send(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if (
            ($user = User::findByEmail($this->email)) &&
            !User::isPasswordResetTokenValid((string) $user->passwordResetToken)
        ) {
            $user->generatePasswordResetToken();

            $user->save();

            $user->sendForgottenPasswordEmail();
        }

        return true;
    }
}
