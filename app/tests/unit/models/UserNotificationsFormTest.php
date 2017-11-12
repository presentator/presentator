<?php
namespace app\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;
use app\models\UserNotificationsForm;

/**
 * UserNotificationsForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserNotificationsFormTest extends \Codeception\Test\Unit
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
     * `UserNotificationsForm::loadUser()` method test.
     */
    public function testLoadUser()
    {
        $user1 = User::findOne(1002);
        $model = new UserNotificationsForm($user1);

        verify('Model notifications setting should match with user1 one', $model->notifications)
            ->equals($user1->getSetting(User::NOTIFICATIONS_SETTING_KEY));
        verify('Model mentions setting should match with user1 one', $model->mentions)
            ->equals($user1->getSetting(User::MENTIONS_SETTING_KEY));

        $user2 = User::findOne(1003);
        $model->loadUser($user2);

        verify('Model notifications setting should match with user2 one', $model->notifications)
            ->equals($user2->getSetting(User::NOTIFICATIONS_SETTING_KEY));
        verify('Model mentions setting should match with user2 one', $model->mentions)
            ->equals($user2->getSetting(User::MENTIONS_SETTING_KEY));
    }

    /**
     * `UserNotificationsForm::save()` method test.
     */
    public function testSave()
    {
        $user = User::findOne(1002);

        $this->specify('False save attempt', function () use ($user) {
            $model = new UserNotificationsForm($user, [
                'notifications' => 'invalid_value',
                'mentions'      => 'invalid_value',
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should not save', $result)->false();
            verify('notifications error message should be set', $model->errors)->hasKey('notifications');
            verify('mentions error message should be set', $model->errors)->hasKey('mentions');
            verify('User notifications setting should not be changed', $user->getSetting(User::NOTIFICATIONS_SETTING_KEY))->equals(true);
            verify('User mentions setting should not be changed', $user->getSetting(User::MENTIONS_SETTING_KEY))->equals(true);
        });

        $this->specify('Success save attempt', function () use ($user) {
            $model = new UserNotificationsForm($user, [
                'notifications' => false,
                'mentions'      => false,
            ]);

            $result = $model->save();
            $user->refresh();

            verify('Model should save', $result)->true();
            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('User notifications setting should be changed', $user->getSetting(User::NOTIFICATIONS_SETTING_KEY))->equals(false);
            verify('User mentions setting should be changed', $user->getSetting(User::MENTIONS_SETTING_KEY))->equals(false);
        });
    }
}
