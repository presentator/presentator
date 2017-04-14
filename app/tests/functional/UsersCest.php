<?php
namespace app\tests\functional;

use Yii;
use app\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
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
            'setting' => [
                'class'    => UserSettingFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_setting.php'),
            ],
        ]);
    }

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
    }

    /**
     * @param FunctionalTester $I
     */
    public function settingsSubmitFail(FunctionalTester $I)
    {
        $I->wantTo('Fail submitting new user settings');
        $I->amLoggedInAs(1002);
        $I->amOnPage(['users/settings']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('form[action="' . Yii::$app->urlManager->createUrl(['users/settings']) . '"]', [
            'UserForm' => [
                'firstName'          => 'John',
                'lastName'           => '',
                'changePassword'     => 1,
                'oldPassword'        => 'incorrect_password',
                'newPassword'        => '123456',
                'newPasswordConfirm' => '987654',
            ],
        ]);
        $I->seeElement('.field-userform-oldpassword.has-error');
        $I->seeElement('.field-userform-newpasswordconfirm.has-error');
        $I->dontSeeElement('.field-userform-firstname.has-error');
        $I->dontSeeElement('.field-userform-lastname.has-error');
        $I->dontSeeElement('.field-userform-newpassword.has-error');
        $I->dontSeeFlash('success');
    }

    /**
     * @param FunctionalTester $I
     */
    public function settingsSubmitSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully submit new user settings');
        $I->amLoggedInAs(1002);
        $I->amOnPage(['users/settings']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('form[action="' . Yii::$app->urlManager->createUrl(['users/settings']) . '"]', [
            'UserForm' => [
                'email'              => 'invalid_email', // should be ignored
                'firstName'          => 'John',
                'lastName'           => '',
                'changePassword'     => 1,
                'oldPassword'        => '123456',
                'newPassword'        => '111222',
                'newPasswordConfirm' => '111222',
            ],
        ]);
        $I->dontSeeElement('form#w0 .has-error');
        $I->seeFlash('success');
    }

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
