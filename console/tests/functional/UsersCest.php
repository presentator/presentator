<?php
namespace console\tests\functional;

use Yii;
use console\controllers\UsersController;
use console\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\models\User;

/**
 * UsersController functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UsersCest
{
    /**
     * @inheritdoc
     */
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'user' => [
                'class'    => UserFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user.php'),
            ],
        ]);
    }

    /* ===============================================================
     * `UsersController::actionSuper()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function testSuperFail(FunctionalTester $I)
    {
        $I->wantTo('Fail providing super user access rights to non-registered user.');

        $controller = new UsersController('users', Yii::$app);
        $result     = $controller->runAction('super', ['missing@presentator.io']);

        verify('Action should complete with an error', $result)->equals(UsersController::EXIT_CODE_ERROR);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSuperSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully provide super user access rights to an existing regular user.');

        $user = User::findOne(['type' => User::TYPE_REGULAR]);

        $controller = new UsersController('users', Yii::$app);
        $result     = $controller->runAction('super', [$user->email]);

        $user->refresh();

        verify('Action should complete normally', $result)->equals(UsersController::EXIT_CODE_NORMAL);
        verify('User type should match', $user->type)->equals(User::TYPE_SUPER);
    }

    /* ===============================================================
     * `UsersController::actionRegular()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function testRegularFail(FunctionalTester $I)
    {
        $I->wantTo('Fail providing regular user access rights to non-registered user.');

        $controller = new UsersController('users', Yii::$app);
        $result     = $controller->runAction('regular', ['missing@presentator.io']);

        verify('Action should complete with an error', $result)->equals(UsersController::EXIT_CODE_ERROR);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRegularSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully provide regular user access rights to an existing super user.');

        $user = User::findOne(['type' => User::TYPE_SUPER]);

        $controller = new UsersController('users', Yii::$app);
        $result     = $controller->runAction('regular', [$user->email]);

        $user->refresh();

        verify('Action should complete normally', $result)->equals(UsersController::EXIT_CODE_NORMAL);
        verify('User type should match', $user->type)->equals(User::TYPE_REGULAR);
    }
}
