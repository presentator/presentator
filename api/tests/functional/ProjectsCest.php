<?php
namespace api\tests\FunctionalTester;

use Yii;
use api\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\User;
use common\models\Project;

/**
 * ProjectsController API functional test.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectsCest
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
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
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
        $I->seeUnauthorizedAccess('/projects');
    }

    /**
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('List all user projects');
        $I->sendGET('/projects');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'       => 'integer',
            'title'    => 'string',
            'type'     => 'integer',
            'subtype'  => 'integer|null',
            'featured' => 'string|null',
            'previews' => 'array',
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
        $I->seeUnauthorizedAccess('/projects', 'POST');
    }

    /**
     * @param FunctionalTester $I
     */
    public function createError(FunctionalTester $I)
    {
        $I->wantTo('Wrong project create attempt');
        $I->sendPOST('/projects', [
            'title'   => '',
            'type'    => -1,
            'subtype' => -1,
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'title'   => 'string',
                'type'    => 'string',
                'subtype' => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $data = [
            'title'   => 'Test title',
            'type'    => Project::TYPE_TABLET,
            'subtype' => 21,
        ];
        $I->wantTo('Correct project create attempt');
        $I->sendPOST('/projects', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'       => 'integer',
            'title'    => 'string',
            'type'     => 'integer',
            'subtype'  => 'integer|null',
            'featured' => 'string|null',
            'versions' => 'array',
            'previews' => 'array',
        ]);
    }

    /* Update action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function updateUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to update action');
        $I->seeUnauthorizedAccess('/projects/1001', 'PUT');
    }

    /**
     * @param FunctionalTester $I
     */
    public function updateMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to update unaccessible or other user project');
        $I->sendPUT('/projects/1002', [
            'title' => 'Test title',
            'type'  => Project::TYPE_DESKTOP,
        ]);
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
    public function updateError(FunctionalTester $I)
    {
        $I->wantTo('Wrong project update attempt');
        $I->sendPUT('/projects/1001', [
            'title'   => '',
            'type'    => -1,
            'subtype' => -1,
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'title'   => 'string',
                'type'    => 'string',
                'subtype' => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $data = [
            'title'   => 'Test title',
            'type'    => Project::TYPE_TABLET,
            'subtype' => 21,
        ];
        $I->wantTo('Correct project update attempt');
        $I->sendPUT('/projects/1001', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($data);
        $I->seeResponseMatchesJsonType([
            'id'       => 'integer',
            'title'    => 'string',
            'type'     => 'integer',
            'subtype'  => 'integer|null',
            'featured' => 'string|null',
            'versions' => 'array',
            'previews' => 'array',
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
        $I->seeUnauthorizedAccess('/projects/1001');
    }

    /**
     * @param FunctionalTester $I
     */
    public function viewMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to view unaccessible or other user project');
        $I->sendGET('/projects/1002');
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
        $I->wantTo('Get user project');
        $I->sendGET('/projects/1001');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'       => 'integer',
            'title'    => 'string',
            'type'     => 'integer',
            'subtype'  => 'integer|null',
            'featured' => 'string|null',
            'versions' => 'array',
            'previews' => 'array',
        ]);
        $I->seeResponseContainsJson([
            'id' => 1001,
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
        $I->seeUnauthorizedAccess('/projects/1001', 'DELETE');
    }

    /**
     * @param FunctionalTester $I
     */
    public function deleteMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to delete unaccessible or other user project');
        $I->sendDELETE('/projects/1002');
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
        $I->wantTo('Delete user project');
        $I->sendDELETE('/projects/1001');
        $I->seeResponseCodeIs(204);
    }
}
