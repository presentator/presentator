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
use common\models\User;

/**
 * ScreenCommentsController API functional test.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentsCest
{
    /**
     * @var User
     */
    protected $user;

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

        // Authenticate user
        $this->user = User::findOne(1002);
        $I->haveHttpHeader('X-Access-Token', $this->user->generateJwtToken());
    }

    /* Index action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function indexUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to index action');
        $I->seeUnauthorizedAccess('/comments');
    }

    /**
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('List all user comments');
        $I->sendGET('/comments');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'        => 'integer',
            'replyTo'   => 'integer|null',
            'screenId'  => 'integer',
            'from'      => 'string',
            'message'   => 'string',
            'posX'      => 'integer',
            'posY'      => 'integer',
        ]);
    }

    /* Create action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function createUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to create action');
        $I->seeUnauthorizedAccess('/comments', 'POST');
    }

    /**
     * @param FunctionalTester $I
     */
    public function createCommentTargetError(FunctionalTester $I)
    {
        $I->wantTo('Wrong comment target create attempt');
        $I->sendPOST('/comments', [
            'screenId' => 1003, // invalid value because it is from different user project
            'message'  => '',
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
                'posX'     => 'string',
                'posY'     => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function createCommentTargetSuccess(FunctionalTester $I)
    {
        $data = [
            'screenId' => 1001,
            'message'  => 'Lorem ipsum',
            'posX'     => 0,
            'posY'     => 50,
        ];
        $I->wantTo('Correct comment target create attempt');
        $I->sendPOST('/comments', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array_merge($data, [
            'from' => $this->user->email,
        ]));
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
    public function createCommentReplyError(FunctionalTester $I)
    {
        $I->wantTo('Wrong comment reply create attempt');
        $I->sendPOST('/comments', [
            'screenId' => 1003, // invalid value because it is from different project preview
            'replyTo'  => 1005, // invalid value because it is from different screen
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
    public function createCommentReplySuccess(FunctionalTester $I)
    {
        $data = [
            'screenId' => 1001,
            'replyTo'  => 1001,
            'message'  => 'Lorem ipsum dolor sit amet...',
            // should be ignored
            'posX' => 5,
            'posY' => 10,
        ];
        $I->wantTo('Correct comment reply create attempt');
        $I->sendPOST('/comments', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array_merge($data, [
            'from' => $this->user->email,
            // should match with the primary comment
            'posX' => 200,
            'posY' => 100,
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

    /* View action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function viewUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to view action');
        $I->seeUnauthorizedAccess('/comments/1001');
    }

    /**
     * @param FunctionalTester $I
     */
    public function viewMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to view unaccessible or comment from other user projects');
        $I->sendGET('/comments/1005');
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
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Get screen comment');
        $I->sendGET('/comments/1001');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'       => 'integer:=1001',
            'screenId' => 'integer:=1001',
            'replyTo'  => 'null',
            'message'  => 'string',
            'from'     => 'string',
            'posX'     => 'integer',
            'posY'     => 'integer',
        ]);
    }

    /* Delete action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function deleteUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to view action');
        $I->seeUnauthorizedAccess('/comments/1004', 'DELETE');
    }

    /**
     * @param FunctionalTester $I
     */
    public function deleteMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to delete unaccessible or other screen comment');
        $I->sendDELETE('/comments/1005');
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
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Delete screen comment');
        $I->sendDELETE('/comments/1001');
        $I->seeResponseCodeIs(204);
    }
}
