<?php
namespace app\tests\functional;

use Yii;
use app\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\ScreenCommentFixture;
use common\tests\fixtures\ProjectPreviewFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\tests\fixtures\UserScreenCommentRelFixture;
use common\models\User;
use common\models\Version;
use common\models\Project;

/**
 * VersionsController functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionsCest
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
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project.php'),
            ],
            'version' => [
                'class'    => VersionFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/version.php'),
            ],
            'screen' => [
                'class'    => ScreenFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/screen.php'),
            ],
            'comment' => [
                'class'    => ScreenCommentFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/screen_comment.php'),
            ],
            'projectPreviewFixture' => [
                'class'    => ProjectPreviewFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project_preview.php'),
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
            'userScreenCommentRel' => [
                'class'    => UserScreenCommentRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_screen_comment_rel.php'),
            ],
        ]);
    }

    /* ===============================================================
     * `VersionsController::actionAjaxGetForm()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetFormFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely fetch version form');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxGetActionAccess(['versions/ajax-get-form', 'projectId' => 1001]);

        $I->amGoingTo('try with a version belonging to a project that is not owned by the logged user');
        $I->sendAjaxGetRequest(['versions/ajax-get-form', 'projectId' => 1004]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetFormSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully fetch version form');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxGetActionAccess(['versions/ajax-get-form', 'projectId' => 1001]);
        $I->sendAjaxGetRequest(['versions/ajax-get-form', 'projectId' => 1001]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"formHtml":');
    }

    /* ===============================================================
     * `VersionsController::actionAjaxGetForm()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxSaveFormCreateFail(FunctionalTester $I)
    {
        $oldCount = Version::find()->count();

        $I->wantTo('Falsely save create version form');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['versions/ajax-save-form', 'projectId' => 12345]);

        $I->amGoingTo('try with a missing or invalid project ID');
        $I->sendAjaxPostRequest(['versions/ajax-save-form', 'projectId' => 12345], []);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(Version::className(), $oldCount);

        $I->amGoingTo('try with a project that is not owned by the logged user');
        $I->sendAjaxPostRequest(['versions/ajax-save-form', 'projectId' => 1003]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(Version::className(), $oldCount);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxSaveFormCreateSuccess(FunctionalTester $I)
    {
        $oldCount = Version::find()->count();

        $I->wantTo('Successfully create a new version');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['versions/ajax-save-form', 'projectId' => 1001]);
        $I->sendAjaxPostRequest(['versions/ajax-save-form', 'projectId' => 1001], [
            'VersionForm' => [
                'title' => 'TEST_TITLE'
            ],
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->seeResponseContains('"isUpdate":false');
        $I->seeResponseContains('"version":');
        $I->seeResponseContains('"navItemHtml":');
        $I->seeResponseContains('"contentItemHtml":');
        $I->seeRecordsCountChange(Version::className(), $oldCount, 1);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxSaveFormUpdateFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely save update version form');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['versions/ajax-save-form', 'projectId' => 12345]);

        $I->amGoingTo('try with a missing or invalid project ID');
        $I->sendAjaxPostRequest(['versions/ajax-save-form', 'projectId' => 12345], []);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try with a version belonging to a project that is not owned by the logged user');
        $I->sendAjaxPostRequest(['versions/ajax-save-form', 'projectId' => 1003], [
            'versionId' => 1001,
            'VersionForm' => [
                'title'     => 'TEST_TITLE'
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxSaveFormUpdateSuccess(FunctionalTester $I)
    {
        $oldCount = Version::find()->count();

        $I->wantTo('Successfully create a new version');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['versions/ajax-save-form', 'projectId' => 1001]);
        $I->sendAjaxPostRequest(['versions/ajax-save-form', 'projectId' => 1001], [
            'versionId' => 1001,
            'VersionForm' => [
                'title' => 'TEST_TITLE'
            ],
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->seeResponseContains('"isUpdate":true');
        $I->seeResponseContains('"version":');
        $I->seeResponseContains('"navItemHtml":');
        $I->seeResponseContains('"contentItemHtml":');
        $I->dontSeeRecordsCountChange(Version::className(), $oldCount);
    }

    /* ===============================================================
     * `VersionsController::actionAjaxDelete()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxDeleteFail(FunctionalTester $I)
    {
        $oldCount = Version::find()->count();

        $I->wantTo('Falsely delete a version');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['versions/ajax-delete']);

        $I->amGoingTo('try with a version that is not owned by the logged user');
        $I->sendAjaxPostRequest(['versions/ajax-delete'], ['id' => 1004]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(Version::className(), $oldCount);

        $I->amGoingTo('try to delete the only one project version');
        $I->sendAjaxPostRequest(['versions/ajax-delete'], ['id' => 1003]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(Version::className(), $oldCount);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxDeleteSuccess(FunctionalTester $I)
    {
        $oldCount = Version::find()->count();

        $I->wantTo('Successfully delete a version model');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['versions/ajax-delete']);
        $I->sendAjaxPostRequest(['versions/ajax-delete'], ['id' => 1001]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecord(Version::className(), ['id' => 1001]);
    }

    /* ===============================================================
     * `VersionsController::actionAjaxGetScreensSlider()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetScreensSliderFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely fetch version screens slider');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxGetActionAccess(['versions/ajax-get-screens-slider'], ['versionId' => 1004]);

        $I->amGoingTo('try with a missing or invalid version ID');
        $I->sendAjaxGetRequest(['versions/ajax-get-screens-slider'], ['versionId' => 12345]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try with a version that is not owned by the logged user');
        $I->sendAjaxGetRequest(['versions/ajax-get-screens-slider'], ['versionId' => 1004]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetScreensSliderSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully fetch version screens slider');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxGetActionAccess(['versions/ajax-get-screens-slider'], ['versionId' => 1001]);
        $I->sendAjaxGetRequest(['versions/ajax-get-screens-slider'], ['versionId' => 1001]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"screensSliderHtml":');
    }
}
