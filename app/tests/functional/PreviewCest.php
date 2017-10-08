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
use common\models\ProjectPreview;

/**
 * PreviewController functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PreviewCest
{
    /**
     * @inheritdoc
     */
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
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
            'preview' => [
                'class'    => ProjectPreviewFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project_preview.php'),
            ],
        ]);
    }

    /* ===============================================================
     * `PreviewController::actionView()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function viewFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely fetch a project preview');
        $I->amOnPage(['preview/view', 'slug' => 'missing_slug']);
        $I->seeResponseCodeIs(404);
    }

    /**
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully fetch a project preview');
        $I->amOnPage(['preview/view', 'slug' => 'BAgePG5c']);
        $I->seeResponseCodeIs(200);
        $I->seeElement('#preview_wrapper');
    }

    /* ===============================================================
     * `PreviewController::actionAjaxInvokeAccess()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxInvokeAccessFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely invoke access to a project preview');
        $I->ensureAjaxPostActionAccess(['preview/ajax-invoke-access', 'slug' => 'BAgePG5c'], [], true);

        $I->amGoingTo('try with a missing or invalid project preview slug');
        $I->sendAjaxPostRequest(['preview/ajax-invoke-access', 'slug' => 'missing_slug'], []);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try with a incorrect project preview password');
        $I->sendAjaxPostRequest(['preview/ajax-invoke-access', 'slug' => 'BAgePG5c'], ['ProjectAccessForm[password]' => 654321]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"errors":{"password":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxInvokeAccessSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully invoke access to a project preview');
        $I->ensureAjaxPostActionAccess(['preview/ajax-invoke-access', 'slug' => 'BAgePG5c'], [], true);

        $I->amGoingTo('try accessing an unprotected preview (project without password)');
        $I->sendAjaxPostRequest(['preview/ajax-invoke-access', 'slug' => 'pLIRe9su'], []);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"previewHtml":');
        $I->seeResponseContains('"mentionsList":');

        $I->amGoingTo('try accessing a protected preview (project with password)');
        $I->sendAjaxPostRequest(['preview/ajax-invoke-access', 'slug' => 'BAgePG5c'], ['ProjectAccessForm[password]' => 123456]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"previewHtml":');
        $I->seeResponseContains('"mentionsList":');
    }
}
