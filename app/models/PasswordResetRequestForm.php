<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Password reset request form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PasswordResetRequestForm extends Model
{
    /**
     * @var string
     */
    public $email;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('app', 'Email'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist', 'targetClass' => User::className(), 'filter' => ['status' => User::STATUS_ACTIVE]],
        ];
    }

    /**
     * Sends password reset email to the specific user.
     * @return boolean
     */
    public function enquirePasswordReset()
    {
        if (
            $this->validate() &&
            ($user = User::findByEmail($this->email))
        ) {
            if (!User::isPasswordResetTokenValid($user->passwordResetToken)) {
                $user->generatePasswordResetToken();

                if (!$user->save()) {
                    return false;
                }

                return $user->sendPasswordResetEmail();
            }

            Yii::$app->session->setFlash('info', Yii::t('app', 'A password reset request has been already sent. Please check your email inbox or wait until the token expire and try again.'));
        }

        return false;
    }
}
