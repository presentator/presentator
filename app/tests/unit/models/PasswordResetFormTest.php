<?php
namespace app\tests\models;

use Yii;
use yii\base\InvalidParamException;
use common\tests\fixtures\UserFixture;
use common\models\User;
use app\models\PasswordResetForm;

/**
 * PasswordResetForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PasswordResetFormTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \app\tests\UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class'    => UserFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user.php'),
            ],
        ]);
    }

    /**
     * `PasswordResetForm::reset()` method test.
     */
    public function testReset()
    {
        $this->specify('Reset attempt with expired token', function() {
            $user = User::findOne(1003);

            $model = new PasswordResetForm($user->passwordResetToken, [
                'password'        => '123456',
                'passwordConfirm' => '123456',
            ]);

            verify('Model should not reset', $model->reset())->false();
        },  ['throws' => new InvalidParamException]);

        $this->specify('Reset attempt with mismatched passwords', function() {
            $user = User::findOne(1004);
            $model = new PasswordResetForm($user->passwordResetToken, [
                'password'        => '123456789',
                'passwordConfirm' => '123456',
            ]);

            verify('Model should not reset', $model->reset())->false();
            verify('Error message should be set', $model->errors)->hasKey('passwordConfirm');
        });

        $this->specify('Correct reset attempt', function() {
            $beforeResetUser = User::findOne(1004);
            $model = new PasswordResetForm($beforeResetUser->passwordResetToken, [
                'password'        => '123456789',
                'passwordConfirm' => '123456789',
            ]);

            verify('Model should reset', $model->reset())->true();

            // check the changes on the User model after reset
            $updatedUser = User::findOne(1004);
            verify('User should not have passwordResetToken', $updatedUser->passwordResetToken)->isEmpty();
            verify('User should have new password', $updatedUser->validatePassword('123456789'))->true();
        });
    }
}
