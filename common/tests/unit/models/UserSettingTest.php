<?php
namespace common\tests\unit\models;

use yii\db\ActiveQuery;
use common\models\User;
use common\models\UserSetting;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;

/**
 * UserSetting AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserSettingTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
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
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
            'setting' => [
                'class'    => UserSettingFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_setting.php',
            ],
        ]);
    }

    /**
     * `UserSetting::getUser()` relation query method test.
     */
    public function testGetUser()
    {
        $model = UserSetting::findOne(1001);
        $query = $model->getUser();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid User model', $model->user)->isInstanceOf(User::className());
        verify('Query result user id should match', $model->user->id)->equals($model->userId);
    }

    /**
     * `UserSetting::setSettingByUser()` method test.
     */
    public function testSetSettingByUser()
    {
        $user = User::findOne(1002);

        $this->specify('Creates new setting model', function() use ($user) {
            $result  = UserSetting::setSettingByUser($user, 'myNewSetting', 'test');
            $setting = UserSetting::findOne(['settingName' => 'myNewSetting']);

            verify('Method should complete successfully', $result)->true();
            verify('UserSetting model instance', $setting)->isInstanceof(UserSetting::className());
            verify('UserSetting model to have the specified value', $setting->settingValue)->equals('test');
            verify('UserSetting model to be attached to the user', $setting->userId)->equals($user->id);
        });

        $this->specify('Update existing setting model', function() use ($user) {
            // before
            $setting     = UserSetting::findOne(['settingName' => User::NOTIFICATIONS_SETTING_KEY, 'userId' => $user->id]);
            $beforeValue = $setting->settingValue;

            $result = UserSetting::setSettingByUser($user, User::NOTIFICATIONS_SETTING_KEY, 'someNewValue');

            // after
            $setting->refresh();
            $afterValue = $setting->settingValue;

            verify('Method should complete successfully', $result)->true();
            verify('Before and after value should be different', $beforeValue)->notEquals($afterValue);
            verify('After value should match with the specified one', $afterValue)->equals('someNewValue');
        });
    }

    /**
     * `UserSetting::getSettingByUser()` method test.
     */
    public function testGetSettingByUser()
    {
        $user = User::findOne(1002);

        $this->specify('Nonexisting user setting with default value', function() use ($user) {
            $value = UserSetting::getSettingByUser($user, 'someNoneExistingSetting', 'defaultMissingValue');
            verify($value)->equals('defaultMissingValue');
        });

        $this->specify('Existing user setting', function() use ($user) {
            $value = UserSetting::getSettingByUser($user, User::NOTIFICATIONS_SETTING_KEY, false);
            verify($value)->equals(true);
        });
    }
}
