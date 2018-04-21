<?php
namespace api\tests\FunctionalTester;

use Yii;
use api\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\User;
use common\models\Screen;

/**
 * ScreensController API functional test.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensCest
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
            'version' => [
                'class'    => VersionFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/version.php'),
            ],
            'screen' => [
                'class'    => ScreenFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/screen.php'),
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
        $I->seeUnauthorizedAccess('/screens');
    }

    /**
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('List all user screens');
        $I->sendGET('/screens');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'         => 'integer',
            'versionId'  => 'integer',
            'title'      => 'string',
            'hotspots'   => 'array|null',
            'alignment'  => 'integer',
            'background' => 'string|null',
            'imageUrl'   => 'string',
            'thumbs'     => 'array',
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
        $I->seeUnauthorizedAccess('/screens', 'POST');
    }

    /**
     * @param FunctionalTester $I
     */
    public function createError(FunctionalTester $I)
    {
        $I->wantTo('Wrong screen create attempt');
        $I->sendPOST('/screens', [
            'versionId'  => 1004,
            'title'      => '',
            'alignment'  => -1,
            'background' => '#00p',
            'hotspots'   => ['test' => ['width' => 'invalid']],
        ], ['image' => Yii::getAlias('@common/tests/_data/test_image.gif')]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'versionId'  => 'string',
                'title'      => 'string',
                'alignment'  => 'string',
                'background' => 'string',
                'image'      => 'string',
                'hotspots'   => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $data = [
            'versionId'  => 1002,
            'title'      => 'Test screen',
            'alignment'  => Screen::ALIGNMENT_CENTER,
            'background' => '#000000',
            'hotspots'   => ['test' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 1]],
        ];
        $I->wantTo('Correct screen create attempt');
        $I->sendPOST('/screens', $data, [
            'image' => Yii::getAlias('@common/tests/_data/test_image.png')
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'         => 'integer',
            'versionId'  => 'integer',
            'title'      => 'string',
            'hotspots'   => 'array|null',
            'alignment'  => 'integer',
            'background' => 'string|null',
            'imageUrl'   => 'string',
            'thumbs'     => 'array',
        ]);
        $I->seeResponseContainsJson($data);
    }

    /* Update action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function updateUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to update action');
        $I->seeUnauthorizedAccess('/screens/1001', 'PUT');
    }

    /**
     * @param FunctionalTester $I
     */
    public function updateMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to update unaccessible or other project screen');
        $I->sendPUT('/screens/1003', []);
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
        $data = [
            'versionId'  => 1004,
            'title'      => '',
            'alignment'  => -1,
            'background' => '#test',
            'hotspots'   => ['test' => ['width' => 'invalid']],
        ];

        $I->wantTo('Wrong screen update attempt');
        $I->sendPUT('/screens/1001', $data);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'versionId'  => 'string',
                'title'      => 'string',
                'alignment'  => 'string',
                'background' => 'string',
                'hotspots'   => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $data = [
            'versionId'  => 1002,
            'title'      => 'New title',
            'alignment'  => Screen::ALIGNMENT_RIGHT,
            'background' => '#ffffff',
            'hotspots'   => null,
        ];

        $I->wantTo('Correct screen update attempt');
        $I->sendPUT('/screens/1001', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($data);
        $I->seeResponseMatchesJsonType([
            'id'         => 'integer:=1001',
            'versionId'  => 'integer',
            'title'      => 'string',
            'hotspots'   => 'array|null',
            'alignment'  => 'integer',
            'background' => 'string|null',
            'imageUrl'   => 'string',
            'thumbs'     => 'array',
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
        $I->seeUnauthorizedAccess('/screens/1001');
    }

    /**
     * @param FunctionalTester $I
     */
    public function viewMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to view unaccessible or other project screen');
        $I->sendGET('/screens/1003');
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
        $I->wantTo('Get project screen');
        $I->sendGET('/screens/1001');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'         => 'integer:=1001',
            'versionId'  => 'integer',
            'title'      => 'string',
            'hotspots'   => 'array|null',
            'alignment'  => 'integer',
            'background' => 'string|null',
            'imageUrl'   => 'string',
            'thumbs'     => 'array',
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
        $I->seeUnauthorizedAccess('/screens/1001', 'DELETE');
    }

    /**
     * @param FunctionalTester $I
     */
    public function deleteMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to delete unaccessible or other project screen');
        $I->sendDELETE('/screens/1003');
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
        $I->wantTo('Delete project screen');
        $I->sendDELETE('/screens/1001');
        $I->seeResponseCodeIs(204);
    }
}
