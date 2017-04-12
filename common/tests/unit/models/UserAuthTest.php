<?php
namespace common\tests\unit\models;

use yii\db\ActiveQuery;
use common\models\User;
use common\models\UserAuth;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserAuthFixture;

/**
 * UserAuth AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserAuthTest extends \Codeception\Test\Unit
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
            'auth' => [
                'class'    => UserAuthFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_auth.php',
            ],
        ]);
    }

    /**
     * `UserAuth::getUser()` relation query method test.
     */
    public function testGetUser()
    {
        $model = UserAuth::findOne(1001);
        $query = $model->getUser();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid User model', $model->user)->isInstanceOf(User::className());
        verify('Query result user id should match', $model->user->id)->equals($model->userId);
    }
}
