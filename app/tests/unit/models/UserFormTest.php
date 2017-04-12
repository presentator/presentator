<?php
namespace app\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;
use app\models\UserForm;

/**
 * UserForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserFormTest extends \Codeception\Test\Unit
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
     * `UserForm::loadUser()` method test.
     */
    public function testLoadUser()
    {
        $user1 = User::findOne(1002);
        $user2 = User::findOne(1003);
        $model = new UserForm($user1);

        $model->loadUser($user2);

        verify('Model firstName should match with user2 one', $model->firstName)->equals($user2->firstName);
        verify('Model lastName should match with user2 one', $model->lastName)->equals($user2->lastName);
        verify('Model email should match with user2 one', $model->email)->equals($user2->email);
        verify('Model notifications should match with user2 one', $model->notifications)->equals($user2->getSetting(User::NOTIFICATIONS_SETTING_KEY));
        verify('Model language should match with user2 one', $model->language)->equals($user2->getSetting(User::LANGUAGE_SETTING_KEY));
    }

    /**
     * `UserForm::validateOldPassword()` method test.
     */
    public function testValidateOldPassword()
    {
        $user  = User::findOne(1002);

        $this->specify('Wrong old password attempt', function() use ($user) {
            $model = new UserForm($user, [
                'oldPassword'    => '123456789',
                'changePassword' => true,
            ]);

            $model->validateOldPassword('oldPassword', []);

            verify('Error message should be set', $model->errors)->hasKey('oldPassword');
        });

        $this->specify('Correct old password attempt', function() use ($user) {
            $model = new UserForm($user, [
                'oldPassword'    => '123456',
                'changePassword' => true,
            ]);

            $model->validateOldPassword('oldPassword', []);

            verify('Error message should not be set', $model->errors)->hasntKey('oldPassword');
        });

        $this->specify('Correct no password change attempt', function() use ($user) {
            $model = new UserForm($user, [
                'oldPassword'    => '123456789', // doesn't matter
                'changePassword' => false,
            ]);

            $model->validateOldPassword('oldPassword', []);

            verify('Error message should not be set', $model->errors)->hasntKey('oldPassword');
        });
    }

    /**
     * `UserForm::save()` method test.
     */
    public function testSave()
    {
        $user = User::findOne(1002);

        $this->specify('False save attempt', function() use ($user) {
            $model = new UserForm($user, [
                'oldPassword'        => '1234',
                'newPassword'        => '123456789',
                'newPasswordConfirm' => '1234567',
                'changePassword'     => true,
                'language'           => 'invalid',
                'notifications'      => false,
            ]);

            verify('Model should not save', $model->save())->false();
            verify('oldPassword error message should be set', $model->errors)->hasKey('oldPassword');
            verify('newPasswordConfirm error message should be set', $model->errors)->hasKey('newPasswordConfirm');
            verify('language error message should be set', $model->errors)->hasKey('language');
        });

        $this->specify('Correct save attempt', function() use ($user) {
            $model = new UserForm($user, [
                'firstName'          => 'Lorem',
                'lastName'           => 'Ipsum',
                'oldPassword'        => '123456',
                'newPassword'        => '123456789',
                'newPasswordConfirm' => '123456789',
                'changePassword'     => true,
                'language'           => 'bg-BG',
                'notifications'      => false,
            ]);

            verify('Model should save', $model->save())->true();

            $user->refresh();
            verify('User firstName should match', $user->firstName)->equals('Lorem');
            verify('User lastName should match', $user->lastName)->equals('Ipsum');
            verify('User password should match', $user->validatePassword('123456789'))->true();
            verify('User language lastName should match', $user->getSetting(User::LANGUAGE_SETTING_KEY))->equals('bg-BG');
            verify('User notifications setting should match', $user->getSetting(User::NOTIFICATIONS_SETTING_KEY))->equals(false);
        });
    }
}
