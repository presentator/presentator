<?php
namespace app\tests\models;

use Yii;
use yii\base\InvalidConfigException;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;
use app\models\SuperUserForm;

/**
 * SuperUserForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class SuperUserFormTest extends \Codeception\Test\Unit
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
     * `SuperUserForm::__constructor()` method test.
     */
    public function testConstruct()
    {
        $this->specify('Create default SuperUserForm instance', function() {
            $model = new SuperUserForm();

            verify('Model scenario should be auto set to create', $model->scenario)->equals(SuperUserForm::SCENARIO_CREATE);
        });

        $this->specify('Misconfigured SuperUserForm instance', function() {
            $model = new SuperUserForm(new User, ['scenario' => SuperUserForm::SCENARIO_UPDATE]);
        }, ['throws' => new InvalidConfigException]);

        $this->specify('Create SuperUserForm instance with specified user model', function() {
            $user  = User::findOne(1001);
            $model = new SuperUserForm($user);

            verify('Model scenario should be auto set to update', $model->scenario)->equals(SuperUserForm::SCENARIO_UPDATE);
            verify('Model firstName should match with the user one', $model->firstName)->equals($user->firstName);
            verify('Model lastName should match with the user one', $model->lastName)->equals($user->lastName);
            verify('Model email should match with the user one', $model->email)->equals($user->email);
            verify('Model status should match with the user one', $model->status)->equals($user->status);
            verify('Model type should match with the user one', $model->type)->equals($user->type);
            verify('Model notifications setting should match with the user one', $model->notifications)->equals($user->getSetting(User::NOTIFICATIONS_SETTING_KEY));
            verify('Model mentions setting should match with the user one', $model->mentions)->equals($user->getSetting(User::MENTIONS_SETTING_KEY, true));
        });
    }

    /**
     * `SuperUserForm::loadUser()` method test.
     */
    public function testLoadUser()
    {
        $user = User::findOne(1002);
        $model = new SuperUserForm($user);

        verify('Model firstName should match with the user one', $model->firstName)->equals($user->firstName);
        verify('Model lastName should match with the user one', $model->lastName)->equals($user->lastName);
        verify('Model email should match with the user one', $model->email)->equals($user->email);
        verify('Model status should match with the user one', $model->status)->equals($user->status);
        verify('Model type should match with the user one', $model->type)->equals($user->type);
        verify('Model notifications setting should match with the user one', $model->notifications)->equals($user->getSetting(User::NOTIFICATIONS_SETTING_KEY));
        verify('Model mentions setting should match with the user one', $model->mentions)->equals($user->getSetting(User::MENTIONS_SETTING_KEY, true));
    }

    /**
     * `SuperUserForm::save()` failure method scenarios test.
     */
    public function testSaveFailure()
    {
        $this->specify('Fail creating new user with empty props', function () {
            $model = new SuperUserForm();

            $result = $model->save();

            verify('Model should not save', $result)->false();

            $expectedErrorKeys = ['email', 'password', 'type', 'status'];
            foreach ($expectedErrorKeys as $key) {
                verify($key . ' error message should be set', $model->errors)->hasKey($key);
            }
        });

        $this->specify('Fail creating new user with invalid props', function () {
            $model = new SuperUserForm(null, [
                'email'           => 'invalid',
                'type'            => -1,
                'status'          => -1,
                'password'        => '123',
                'passwordConfirm' => '123456',
                'notifications'   => 'invalid',
                'mentions'        => 'invalid',
            ]);

            $result = $model->save();

            verify('Model should not save', $result)->false();

            $expectedErrorKeys = ['email', 'password', 'passwordConfirm', 'type', 'status', 'notifications', 'mentions'];
            foreach ($expectedErrorKeys as $key) {
                verify($key . ' error message should be set', $model->errors)->hasKey($key);
            }
        });

        $this->specify('Fail updating existing user with invalid props', function () {
            $originalUser = User::findOne(1001);
            $user = User::findOne(1001);
            $model = new SuperUserForm($user, [
                'email'           => 'invalid',
                'type'            => -1,
                'status'          => -1,
                'password'        => '123',
                'passwordConfirm' => '123456',
                'notifications'   => 'invalid',
                'mentions'        => 'invalid',
            ]);

            $result = $model->save();

            $user->refresh();

            verify('Model should not save', $result)->false();
            verify('User model should not be changed', $user)->equals($originalUser);

            $expectedErrorKeys = ['email', 'password', 'passwordConfirm', 'type', 'status', 'notifications', 'mentions'];
            foreach ($expectedErrorKeys as $key) {
                verify($key . ' error message should be set', $model->errors)->hasKey($key);
            }
        });
    }

    /**
     * `SuperUserForm::save()` success method scenarios test.
     */
    public function testSaveSuccess()
    {
        $this->specify('Successfully create new user', function () {
            $model = new SuperUserForm(null, [
                'email'           => 'create_test@presentator.io',
                'type'            => User::TYPE_REGULAR,
                'status'          => User::STATUS_ACTIVE,
                'password'        => '1234',
                'passwordConfirm' => '1234',
                'notifications'   => false,
                'mentions'        => true,
            ]);

            $result = $model->save();

            verify('Model should save', $result)->true();
            verify('Model should not have any errors', $model->errors)->isEmpty();

            $user = User::findOne(['email' => 'create_test@presentator.io']);
            verify('User email should match', $user->email)->equals('create_test@presentator.io');
            verify('User type should match', $user->type)->equals(User::TYPE_REGULAR);
            verify('User status should match', $user->status)->equals(User::STATUS_ACTIVE);
            verify('User password should match', $user->validatePassword('1234'))->true();
            verify('User notifications setting should match', $user->getSetting(User::NOTIFICATIONS_SETTING_KEY))->equals(false);
            verify('User mentions setting should match', $user->getSetting(User::MENTIONS_SETTING_KEY))->equals(true);
        });

        $this->specify('Successfully update an existing user (without password change)', function () {
            $user = User::findOne(1001);
            $model = new SuperUserForm($user, [
                'email'           => 'update_test@presentator.io',
                'type'            => User::TYPE_SUPER,
                'status'          => User::STATUS_ACTIVE,
                'password'        => '1234',
                'passwordConfirm' => '1234',
                'notifications'   => true,
                'mentions'        => false,
            ]);

            $result = $model->save();

            $user->refresh();

            verify('Model should save', $result)->true();
            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('User email should match', $user->email)->equals('update_test@presentator.io');
            verify('User type should match', $user->type)->equals(User::TYPE_SUPER);
            verify('User status should match', $user->status)->equals(User::STATUS_ACTIVE);
            verify('User password should not be changed', $user->validatePassword('123456'))->true();
            verify('User notifications setting should match', $user->getSetting(User::NOTIFICATIONS_SETTING_KEY))->equals(true);
            verify('User mentions setting should match', $user->getSetting(User::MENTIONS_SETTING_KEY))->equals(false);
        });

        $this->specify('Successfully update an existing user (with password change)', function () {
            $user = User::findOne(1001);
            $model = new SuperUserForm($user, [
                'email'           => 'update_test@presentator.io',
                'type'            => User::TYPE_SUPER,
                'status'          => User::STATUS_ACTIVE,
                'changePassword'  => true,
                'password'        => '1234',
                'passwordConfirm' => '1234',
                'notifications'   => true,
                'mentions'        => false,
            ]);

            $result = $model->save();

            $user->refresh();

            verify('Model should save', $result)->true();
            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('User password should be changed', $user->validatePassword('1234'))->true();
        });
    }
}
