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
     * `VersionsController::actionAjaxCreate()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxCreateFail(FunctionalTester $I)
    {
        $oldCount = Version::find()->count();

        $I->wantTo('Falsely create a new version');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['versions/ajax-create']);

        $I->amGoingTo('try with a missing or invalid project ID');
        $I->sendAjaxPostRequest(['versions/ajax-create'], ['projectId' => 12345]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(Version::className(), $oldCount);

        $I->amGoingTo('try with a project that is not owned by the logged user');
        $I->sendAjaxPostRequest(['versions/ajax-create'], ['projectId' => 1003]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(Version::className(), $oldCount);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxCreateSuccess(FunctionalTester $I)
    {
        $oldCount = Version::find()->count();

        $I->wantTo('Successfully create a new version');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['versions/ajax-create']);
        $I->sendAjaxPostRequest(['versions/ajax-create'], ['projectId' => 1001]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->seeResponseContains('"navItemHtml":');
        $I->seeResponseContains('"contentItemHtml":');
        $I->seeRecordsCountChange(Version::className(), $oldCount, 1);
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
