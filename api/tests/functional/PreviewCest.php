<?php
namespace api\tests\FunctionalTester;

use Yii;
use api\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\ProjectPreviewFixture;
use common\tests\fixtures\ScreenCommentFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\tests\fixtures\UserScreenCommentRelFixture;

/**
 * PreviewController API functional test.
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
            'user' => [
                'class'    => UserFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user.php'),
            ],
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project.php'),
            ],
            'preview' => [
                'class'    => ProjectPreviewFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project_preview.php'),
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

    /* View action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function previewMissing(FunctionalTester $I)
    {
        $I->wantTo('Check wrong preview slug');
        $I->sendGET('/previews/invalid_slug');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array',
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function previewUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Access password protected project preview as guest');
        $I->sendGET('/previews/aIwFAZg9');
        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array',
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function previewForbidden(FunctionalTester $I)
    {
        $I->wantTo('Access password protected project preview with wrong password');
        $I->sendGET('/previews/aIwFAZg9', ['password' => '123456789']);
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array',
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function previewAuthorized(FunctionalTester $I)
    {
        $I->wantTo('Access password protected project preview with correct password');
        $I->sendGET('/previews/aIwFAZg9', ['password' => '123456']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'        => 'integer',
            'projectId' => 'integer:=1003',
            'slug'      => 'string:=aIwFAZg9',
            'project'   => [
                'id'       => 'integer',
                'title'    => 'string',
                'featured' => 'array|null',
                'versions' => 'array',
            ]
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function previewPublic(FunctionalTester $I)
    {
        $I->wantTo('Access public project preview');
        $I->sendGET('/previews/pLIRe9su');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'        => 'integer',
            'projectId' => 'integer:=1001',
            'slug'      => 'string:=pLIRe9su',
            'project'   => [
                'id'       => 'integer',
                'title'    => 'string',
                'featured' => 'array|null',
                'versions' => 'array',
            ]
        ]);
    }

    /* Add comment action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function addCommentInViewOnlyPreview(FunctionalTester $I)
    {
        $I->wantTo('Check adding a comment to a project preview only for views');
        $I->sendPOST('/previews/ePcGLAg5', []);
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array',
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function addCommentTargetError(FunctionalTester $I)
    {
        $I->wantTo('Wrong attempt to create a new comment target');
        $I->sendPOST('/previews/aIwFAZg9?password=123456', [
            'screenId' => 1001, // invalid value because it is from different project preview
            'message'  => '',
            'from'     => 'invalid_email@',
            'posX'     => 'invalid_value',
            'posY'     => 'invalid_value',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'screenId' => 'string',
                'message'  => 'string',
                'from'     => 'string',
                'posX'     => 'string',
                'posY'     => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function addCommentTargetSuccess(FunctionalTester $I)
    {
        $data = [
            'screenId' => 1004,
            'message'  => 'Lorem ipsum',
            'from'     => 'test1@presentator.io',
            'posX'     => 30,
            'posY'     => 150,
        ];
        $I->wantTo('Correct attempt to create a new comment target');
        $I->sendPOST('/previews/aIwFAZg9?password=123456', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($data);
        $I->seeResponseMatchesJsonType([
            'screenId' => 'integer',
            'replyTo'  => 'null',
            'message'  => 'string',
            'from'     => 'string',
            'posX'     => 'integer',
            'posY'     => 'integer',
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function addCommentReplyError(FunctionalTester $I)
    {
        $I->wantTo('Wrong attempt to create a new comment reply');
        $I->sendPOST('/previews/aIwFAZg9?password=123456', [
            'screenId' => 1001, // invalid value because it is from different project preview
            'replyTo'  => 1001, // invalid value because it is from different screen
            'message'  => '',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'screenId' => 'string',
                'replyTo'  => 'string',
                'message'  => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function addCommentReplySuccess(FunctionalTester $I)
    {
        $data = [
            'screenId' => 1004,
            'replyTo'  => 1005,
            'message'  => 'Lorem ipsum',
            'from'     => 'test1@presentator.io',
        ];
        $I->wantTo('Correct attempt to create a new comment reply');
        $I->sendPOST('/previews/aIwFAZg9?password=123456', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array_merge($data, [
            // should match with the primary comment
            'posX' => 100,
            'posY' => 50,
        ]));
        $I->seeResponseMatchesJsonType([
            'screenId' => 'integer',
            'replyTo'  => 'integer',
            'message'  => 'string',
            'from'     => 'string',
            'posX'     => 'integer',
            'posY'     => 'integer',
        ]);
    }
}
