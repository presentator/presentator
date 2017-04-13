<?php
namespace api\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\models\User;
use api\models\UserForm;

/**
 * UserForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserFormTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \api\tests\UnitTester
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
     * `UserForm::validateOldPassword()` method test.
     */
    public function testValidateOldPassword()
    {
        $user = User::findOne(1002);

        $this->specify('Wrong value attempt', function() use ($user) {
            $model = new UserForm($user, [
                'oldPassword' => 'someInvalidPassword',
            ]);
            $model->validateOldPassword('oldPassword', []);

            verify('Error message should be set', $model->errors)->hasKey('oldPassword');
        });

        $this->specify('Correct value attempt', function() use ($user) {
            $model = new UserForm($user, [
                'oldPassword' => '123456',
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

        $this->specify('Error update attemp', function() use ($user) {
            $model = new UserForm($user, [
                'firstName'          => 'Test',
                'oldPassword'        => '1234',
                'newPassword'        => '654',
                'newPasswordConfirm' => '654321',
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should not save', $result)->false();
            verify('oldPassword error message should be set', $model->errors)->hasKey('oldPassword');
            verify('newPassword error message should be set', $model->errors)->hasKey('newPassword');
            verify('newPasswordConfirm error message should be set', $model->errors)->hasKey('newPasswordConfirm');
            verify('User firstName should not be changed', $user->firstName)->notEquals('Test');
        });

        $this->specify('Success update attempt', function() use ($user) {
            $model = new UserForm($user, [
                'firstName'          => 'Test',
                'lastName'           => '',
                'oldPassword'        => '123456',
                'newPassword'        => '654321',
                'newPasswordConfirm' => '654321',
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should save successfully', $result)->true();
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('firstName should match', $user->firstName)->equals('Test');
            verify('lastName should match', $user->lastName)->equals('');
            verify('password should match', $user->validatePassword('654321'))->true();
        });
    }
}
