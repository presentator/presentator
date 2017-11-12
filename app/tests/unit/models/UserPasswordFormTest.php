<?php
namespace app\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;
use app\models\UserPasswordForm;

/**
 * UserPasswordForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserPasswordFormTest extends \Codeception\Test\Unit
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
            'setting' => [
                'class'    => UserSettingFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_setting.php'),
            ],
        ]);
    }

    /**
     * `UserPasswordForm::validateOldPassword()` method test.
     */
    public function testValidateOldPassword()
    {
        $user  = User::findOne(1002);

        $this->specify('Wrong old password attempt', function () use ($user) {
            $model = new UserPasswordForm($user, [
                'oldPassword' => '123456789',
            ]);

            $model->validateOldPassword('oldPassword', []);

            verify('Error message should be set', $model->errors)->hasKey('oldPassword');
        });

        $this->specify('Correct old password attempt', function () use ($user) {
            $model = new UserPasswordForm($user, [
                'oldPassword' => '123456',
            ]);

            $model->validateOldPassword('oldPassword', []);

            verify('Error message should not be set', $model->errors)->hasntKey('oldPassword');
        });
    }

    /**
     * `UserPasswordForm::save()` method test.
     */
    public function testSave()
    {
        $user = User::findOne(1002);

        $this->specify('False save attempt', function () use ($user) {
            $model = new UserPasswordForm($user, [
                'oldPassword'        => '1234',
                'newPassword'        => '123456789',
                'newPasswordConfirm' => '1234567',
            ]);

            $result = $model->save();

            verify('Model should not save', $result)->false();
            verify('oldPassword error message should be set', $model->errors)->hasKey('oldPassword');
            verify('newPasswordConfirm error message should be set', $model->errors)->hasKey('newPasswordConfirm');
        });

        $this->specify('Success save attempt', function () use ($user) {
            $model = new UserPasswordForm($user, [
                'oldPassword'        => '123456',
                'newPassword'        => '123456789',
                'newPasswordConfirm' => '123456789',
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should save', $result)->true();
            verify('User password should match', $user->validatePassword('123456789'))->true();
        });
    }
}
