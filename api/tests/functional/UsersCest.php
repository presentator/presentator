<?php
namespace api\tests\FunctionalTester;

use Yii;
use api\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;

/**
 * UsersController API functional test.
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
            'setting' => [
                'class'    => UserSettingFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_setting.php'),
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginWithInvalidFields(FunctionalTester $I)
    {
        $I->wantTo('Wrong login attempt with invalid fields');
        $I->sendPost('/users/login', ['email' => 'invalid_email@', 'password' => '']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'email'    => 'string',
                'password' => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginWithInactiveUser(FunctionalTester $I)
    {
        $I->wantTo('Wrong login attempt with inactive user');
        $I->sendPost('/users/login', ['email' => 'test1@presentator.io', 'password' => '123456']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'email'    => 'string',
                'password' => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginWithActiveUser(FunctionalTester $I)
    {
        $I->wantTo('Correct login attempt with active user');
        $I->sendPost('/users/login', ['email' => 'test2@presentator.io', 'password' => '123456']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->canSeeResponseContains('{"id":1002,"email":"test2@presentator.io"');
        $I->cantSeeResponseContains('"passwordHash":');
        $I->cantSeeResponseContains('"password":');
    }
}
