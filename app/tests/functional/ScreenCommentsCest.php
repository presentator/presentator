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
use common\models\ScreenComment;
use common\models\ProjectPreview;
use common\models\UserScreenCommentRel;

/**
 * ScreenCommentsController functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentsCest
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
            'projectPreview' => [
                'class'    => ProjectPreviewFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project_preview.php'),
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
            'userCommentRel' => [
                'class'    => UserScreenCommentRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_screen_comment_rel.php'),
            ],
        ]);
    }

    /* ===============================================================
     * `ScreensController::actionAjaxDelete()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxDeleteFail(FunctionalTester $I)
    {
        $oldCount = ScreenComment::find()->count();

        $I->wantTo('Falsely delete a screen comment');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-delete']);

        $I->amGoingTo('try with a missing or invalid screen comment ID');
        $I->sendAjaxPostRequest(['screen-comments/ajax-delete'], ['id' => 12345]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(ScreenComment::className(), $oldCount);

        $I->amGoingTo('try with a screen comment from project that is not owned by the logged user');
        $I->sendAjaxPostRequest(['screen-comments/ajax-delete'], ['id' => 1006]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(ScreenComment::className(), $oldCount);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxDeleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete a screen comment model');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-delete']);
        $I->sendAjaxPostRequest(['screen-comments/ajax-delete'], ['id' => 1001]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecord(ScreenComment::className(), ['id' => 1001]);
    }

    /* ===============================================================
     * `ScreensController::actionAjaxCreate()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxCreateGuestFail(FunctionalTester $I)
    {
        $oldCount              = ScreenComment::find()->count();
        $viewOnlyPreview       = ProjectPreview::findOne(1001);
        $viewAndCommentPreview = ProjectPreview::findOne(1002);

        $I->wantTo('Falsely create a new screen comment as a guest');

        $I->amGoingTo('try with view and comment project preview slug and invalid comment data');
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-create'], [], true);
        $I->sendAjaxPostRequest(['screen-comments/ajax-create', 'previewSlug' => $viewAndCommentPreview->slug], [
            'screenId' => 123456,
            'replyTo'  => null,
            'message'  => '',
            'from'     => 'invalid_value',
            'posX'     => 'invalid_value',
            'posY'     => 'invalid_value',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(ScreenComment::className(), $oldCount);

        $I->amGoingTo('try with view only project preview slug and valid comment data');
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-create'], [], true);
        $I->sendAjaxPostRequest(['screen-comments/ajax-create', 'previewSlug' => $viewOnlyPreview->slug], [
            'screenId' => 1003,
            'replyTo'  => null,
            'message'  => 'Lorem ipsum dolor sit amet...',
            'from'     => 'test@presentator.io',
            'posX'     => 0,
            'posY'     => 100,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(ScreenComment::className(), $oldCount);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxCreateGuestSuccess(FunctionalTester $I)
    {
        $oldCount              = ScreenComment::find()->count();
        $viewAndCommentPreview = ProjectPreview::findOne(1002);

        $I->wantTo('Successfully create a new screen comment as a guest');
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-create'], [], true);
        $I->sendAjaxPostRequest(['screen-comments/ajax-create', 'previewSlug' => $viewAndCommentPreview->slug], [
            'screenId' => 1001,
            'replyTo'  => null,
            'message'  => 'Lorem ipsum dolor sit amet...',
            'from'     => 'test@presentator.io',
            'posX'     => 0,
            'posY'     => 100,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->seeResponseContains('"commentsListHtml":');
        $I->seeRecordsCountChange(ScreenComment::className(), $oldCount, 1);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxCreateUserFail(FunctionalTester $I)
    {
        $oldCount = ScreenComment::find()->count();

        $I->wantTo('Falsely create a new screen comment via logged user');
        $I->amLoggedInAs(1002);

        $I->amGoingTo('try with a screen owned by the logged user and invalid comment data');
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-create'], [], true);
        $I->sendAjaxPostRequest(['screen-comments/ajax-create'], [
            'screenId' => 1001,
            'replyTo'  => null,
            'message'  => '',
            'from'     => 'invalid_value',
            'posX'     => 'invalid_value',
            'posY'     => 'invalid_value',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(ScreenComment::className(), $oldCount);

        $I->amGoingTo('try with a screen not owned by the logged user and valid comment data');
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-create'], [], true);
        $I->sendAjaxPostRequest(['screen-comments/ajax-create'], [
            'screenId' => 1003,
            'replyTo'  => null,
            'message'  => 'Lorem ipsum dolor sit amet...',
            'from'     => 'test@presentator.io',
            'posX'     => 0,
            'posY'     => 100,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(ScreenComment::className(), $oldCount);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxCreateUserSuccess(FunctionalTester $I)
    {
        $oldCount = ScreenComment::find()->count();

        $I->wantTo('Successfully create a new screen comment via logged user');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-create'], [], true);
        $I->sendAjaxPostRequest(['screen-comments/ajax-create'], [
            'screenId' => 1001,
            'replyTo'  => null,
            'message'  => 'Lorem ipsum dolor sit amet...',
            'from'     => 'test@presentator.io',
            'posX'     => 0,
            'posY'     => 100,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->seeResponseContains('"commentsListHtml":');
        $I->seeRecordsCountChange(ScreenComment::className(), $oldCount, 1);
    }

    /* ===============================================================
     * `ScreensController::actionAjaxGetComments()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetCommentsGuestFail(FunctionalTester $I)
    {
        $viewOnlyPreview       = ProjectPreview::findOne(1001);
        $viewAndCommentPreview = ProjectPreview::findOne(1002);

        $I->wantTo('Falsely fetch a screen comments list as a guest');

        $I->amGoingTo('try with view only preview slug');
        $I->ensureAjaxGetActionAccess(['screen-comments/ajax-get-comments'], [], true);
        $I->sendAjaxGetRequest(['screen-comments/ajax-get-comments'], [
            'previewSlug' => $viewOnlyPreview->slug,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try with view and comment preview slug and invalid/not from the preview/ primary comment id');
        $I->ensureAjaxGetActionAccess(['screen-comments/ajax-get-comments'], [], true);
        $I->sendAjaxGetRequest(['screen-comments/ajax-get-comments'], [
            'previewSlug' => $viewAndCommentPreview->slug,
            'commentId'   => 1006,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetCommentsGuestSuccess(FunctionalTester $I)
    {
        $viewAndCommentPreview = ProjectPreview::findOne(1002);

        $I->wantTo('Successfully fetch a screen comments list as a guest');
        $I->ensureAjaxGetActionAccess(['screen-comments/ajax-get-comments'], [], true);
        $I->sendAjaxGetRequest(['screen-comments/ajax-get-comments'], [
            'previewSlug' => $viewAndCommentPreview->slug,
            'commentId'   => 1001,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"commentsListHtml":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetCommentsUserFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely fetch a screen comments list as a logged user');
        $I->amLoggedInAs(1002);

        $I->amGoingTo('try with a primary comment from a screen not owned by the logged user');
        $I->ensureAjaxGetActionAccess(['screen-comments/ajax-get-comments'], [], true);
        $I->sendAjaxGetRequest(['screen-comments/ajax-get-comments'], [
            'commentId' => 1006,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try with a reply comment from a screen owned by the logged user');
        $I->ensureAjaxGetActionAccess(['screen-comments/ajax-get-comments'], [], true);
        $I->sendAjaxGetRequest(['screen-comments/ajax-get-comments'], [
            'commentId' => 1003,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetCommentsUserSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully fetch a screen comments list as a logged user');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxGetActionAccess(['screen-comments/ajax-get-comments'], [], true);
        $I->sendAjaxGetRequest(['screen-comments/ajax-get-comments'], [
            'commentId' => 1001,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"commentsListHtml":');
        $I->seeRecord(UserScreenCommentRel::className(), ['userId' => 1002, 'screenCommentId' => 1001, 'isRead' => UserScreenCommentRel::IS_READ_TRUE]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxPositionUpdateFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely update a screen comments target position');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-position-update'], ['commentId' => 1001], false);

        $I->amGoingTo('try with a primary comment from a screen not owned by the logged user');
        $I->sendAjaxPostRequest(['screen-comments/ajax-position-update'], [
            'commentId' => 1006,
            'posX'      => 200,
            'posY'      => 0,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try with a secondary/reply comment');
        $I->sendAjaxPostRequest(['screen-comments/ajax-position-update'], [
            'commentId' => 1003,
            'posX'      => 100,
            'posY'      => 60,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try with a invalid position values');
        $I->sendAjaxPostRequest(['screen-comments/ajax-position-update'], [
            'commentId' => 1001,
            'posX'      => 100,
            'posY'      => -100,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxPositionUpdateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update a screen comments target position');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screen-comments/ajax-position-update'], ['commentId' => 1001], false);

        $I->amGoingTo('try with a primary comment');
        $I->sendAjaxPostRequest(['screen-comments/ajax-position-update'], [
            'commentId' => 1001,
            'posX'      => 200,
            'posY'      => 0,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeRecord(ScreenComment::className(), ['id' => 1001, 'posX' => 200, 'posY' => 0]);
    }
}
