<?php
namespace app\tests\functional;

use Yii;
use app\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;

/**
 * @todo add user change email tests
 * @todo add avatar upload/delete tests
 *
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
            'setting' => [
                'class'    => UserSettingFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_setting.php'),
            ],
        ]);
    }

/* Settings action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function settingsPage(FunctionalTester $I)
    {
        $I->wantTo('Check if settings page is rendered correctly');
        $I->cantAccessAsGuest(['users/settings']);
        $I->amLoggedInAs(1002);
        $I->amOnPage(['users/settings']);
        $I->seeResponseCodeIs(200);
        $I->seeCurrentUrlEquals(['users/settings']);
        $I->see('Settings');
        $I->seeElement('#user_profile_form');
        $I->seeElement('#user_password_form');
        $I->seeElement('#user_notifications_form');
    }

/* Avatar related actions:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxTempAvatarUploadFail(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxTempAvatarUploadSuccess(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxTempAvatarSaveFail(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxTempAvatarSaveSuccess(FunctionalTester $I)
    {
    }
}
