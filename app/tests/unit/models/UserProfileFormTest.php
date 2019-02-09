<?php
namespace app\tests\models;

use Yii;
use yii\helpers\Html;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;
use app\models\UserProfileForm;

/**
 * UserProfileForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserProfileFormTest extends \Codeception\Test\Unit
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
     * `UserProfileForm::loadUser()` method test.
     */
    public function testLoadUser()
    {
        $user1 = User::findOne(1002);
        $model = new UserProfileForm($user1);

        verify('Model firstName should match with user1 one', $model->firstName)->equals($user1->firstName);
        verify('Model lastName should match with user1 one', $model->lastName)->equals($user1->lastName);
        verify('Model email should match with user1 one', $model->email)->equals($user1->email);

        $user2 = User::findOne(1003);
        $model->loadUser($user2);

        verify('Model firstName should match with user2 one', $model->firstName)->equals($user2->firstName);
        verify('Model lastName should match with user2 one', $model->lastName)->equals($user2->lastName);
        verify('Model email should match with user2 one', $model->email)->equals($user2->email);
    }

    /**
     * `UserProfileForm::validatePassword()` method test.
     */
    public function testValidatePassword()
    {
        $user  = User::findOne(1002);

        $this->specify('Failure old password attempt', function () use ($user) {
            $model = new UserProfileForm($user, [
                'password' => '123456789',
            ]);

            $model->validatePassword('password', []);

            verify('Error message should be set', $model->errors)->hasKey('password');
        });

        $this->specify('Success old password attempt', function () use ($user) {
            $model = new UserProfileForm($user, [
                'password' => '123456',
            ]);

            $model->validatePassword('password', []);

            verify('Error message should not be set', $model->errors)->hasntKey('password');
        });
    }

    /**
     * `UserProfileForm::save()` failure method scenarios test.
     */
    public function testSaveFailure()
    {
        $this->specify('Failure save attempt with missing password and invalid email address', function () {
            $user         = User::findOne(1006);
            $oldFirstName = $user->firstName;
            $oldLastName  = $user->lastName;
            $oldEmail     = $user->email;

            $model = new UserProfileForm($user, [
                'firstName' => 'Lorem',
                'lastName'  => 'Ipsum',
                'email'     => 'invalid_email',
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should not save', $result)->false();
            verify('email error message should be set', $model->errors)->hasKey('email');
            verify('password error message should be set', $model->errors)->hasKey('password');
            verify('User firstName should not be changed', $user->firstName)->equals($oldFirstName);
            verify('User lastName should not be changed', $user->lastName)->equals($oldLastName);
            verify('User email address should be changed', $user->email)->equals($oldEmail);

            // verification email should not be sent
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Failure save attempt with invalid password and valid email address', function () {
            $user         = User::findOne(1006);
            $oldFirstName = $user->firstName;
            $oldLastName  = $user->lastName;
            $oldEmail     = $user->email;

            $model = new UserProfileForm($user, [
                'firstName' => 'Lorem',
                'lastName'  => 'Ipsum',
                'email'     => 'test_change@presentator.io',
                'password'  => 1234,
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should not save', $result)->false();
            verify('password error message should be set', $model->errors)->hasKey('password');
            verify('email error message should not be set', $model->errors)->hasntKey('email');
            verify('User firstName should not be changed', $user->firstName)->equals($oldFirstName);
            verify('User lastName should not be changed', $user->lastName)->equals($oldLastName);
            verify('User email address should be changed', $user->email)->equals($oldEmail);

            // verification email should not be sent
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Failure save attempt with valid password and duplicated email address', function () {
            $user         = User::findOne(1006);
            $oldFirstName = $user->firstName;
            $oldLastName  = $user->lastName;
            $oldEmail     = $user->email;

            $model = new UserProfileForm($user, [
                'email'     => 'test5@presentator.io',
                'password'  => '123456',
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should not save', $result)->false();
            verify('firstName error message should not be set', $model->errors)->hasntKey('firstName');
            verify('lastName error message should not be set', $model->errors)->hasntKey('lastName');
            verify('password error message should not be set', $model->errors)->hasntKey('password');
            verify('email error message should be set', $model->errors)->hasKey('email');
            verify('User firstName should not be changed', $user->firstName)->equals($oldFirstName);
            verify('User lastName should not be changed', $user->lastName)->equals($oldLastName);
            verify('User email address should be changed', $user->email)->equals($oldEmail);

            // verification email should not be sent
            $this->tester->dontSeeEmailIsSent();
        });
    }

    /**
     * `UserProfileForm::save()` success method scenarios test.
     */
    public function testSaveSuccess()
    {
        $this->specify('Success save attempt without email address change', function () {
            $user  = User::findOne(1003);
            $model = new UserProfileForm($user, [
                'firstName' => 'Lorem',
                'lastName'  => 'Ipsum',
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should save', $result)->true();
            verify('User firstName should match', $user->firstName)->equals('Lorem');
            verify('User lastName should match', $user->lastName)->equals('Ipsum');
            verify('User email address should not be changed', $user->email)->equals('test3@presentator.io');

            // verification email should not be sent
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Success save attempt with email address change', function () {
            $user     = User::findOne(1002);
            $newEmail = 'test_change@presentator.io';

            $model = new UserProfileForm($user, [
                'firstName' => '',
                'lastName'  => 'Ipsum',
                'email'     => $newEmail,
                'password'  => '123456',
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should save', $result)->true();
            verify('User firstName should match', $user->firstName)->equals('');
            verify('User lastName should match', $user->lastName)->equals('Ipsum');
            verify('User email address should not be changed', $user->email)->equals('test2@presentator.io');
            verify('Email change token should be generated', $user->emailChangeToken)->notEmpty();

            // verification email should be sent
            $this->tester->seeEmailIsSent();
            $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
            verify('Receiver email should match', $message->getTo())->hasKey($newEmail);
            verify('Body should contains an email change url', current($message->getChildren())->getBody())->contains(
                Html::encode(Yii::$app->mainUrlManager->createUrl([
                    'site/change-email',
                    'token' => $user->emailChangeToken,
                    'email' => $newEmail,
                ], true))
            );
        });
    }
}
