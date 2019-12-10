<?php
namespace presentator\api\tests\functional;

use Yii;
use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\GuidelineSectionFixture;
use presentator\api\tests\fixtures\GuidelineAssetFixture;
use presentator\api\tests\fixtures\PrototypeFixture;
use presentator\api\tests\fixtures\ProjectLinkFixture;
use presentator\api\tests\fixtures\ProjectLinkPrototypeRelFixture;
use presentator\api\tests\fixtures\ScreenFixture;
use presentator\api\tests\fixtures\ScreenCommentFixture;
use presentator\api\tests\fixtures\HotspotFixture;
use presentator\api\tests\fixtures\HotspotTemplateFixture;
use presentator\api\tests\fixtures\HotspotTemplateScreenRelFixture;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\UserProjectRelFixture;
use presentator\api\tests\fixtures\UserScreenCommentRelFixture;
use presentator\api\models\ProjectLink;
use presentator\api\models\ScreenComment;
use presentator\api\models\UserScreenCommentRel;

/**
 * PreviewsController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PreviewsCest
{
    /**
     * {@inheritdoc}
     */
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'ProjectFixture' => [
                'class' => ProjectFixture::class,
            ],
            'GuidelineSectionFixture' => [
                'class' => GuidelineSectionFixture::class,
            ],
            'GuidelineAssetFixture' => [
                'class' => GuidelineAssetFixture::class,
            ],
            'PrototypeFixture' => [
                'class' => PrototypeFixture::class,
            ],
            'ProjectLinkFixture' => [
                'class' => ProjectLinkFixture::class,
            ],
            'ProjectLinkPrototypeRelFixture' => [
                'class' => ProjectLinkPrototypeRelFixture::class,
            ],
            'ScreenFixture' => [
                'class' => ScreenFixture::class,
            ],
            'ScreenCommentFixture' => [
                'class' => ScreenCommentFixture::class,
            ],
            'HotspotFixture' => [
                'class' => HotspotFixture::class,
            ],
            'HotspotTemplateFixture' => [
                'class' => HotspotTemplateFixture::class,
            ],
            'HotspotTemplateScreenRelFixture' => [
                'class' => HotspotTemplateScreenRelFixture::class,
            ],
            'UserFixture' => [
                'class' => UserFixture::class,
            ],
            'UserProjectRelFixture' => [
                'class' => UserProjectRelFixture::class,
            ],
            'UserScreenCommentRelFixture' => [
                'class' => UserScreenCommentRelFixture::class,
            ],
        ]);
    }

    /* `PreviewCest::actionAuthorize()`
    --------------------------------------------------------------- */
    /**
     * `PreviewCest::actionAuthorize()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function authorizeFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully authorize project link access');

        $I->amGoingTo('submit no body params');
        $I->sendPOST('/previews', []);
        $I->seeNotFoundResponse();

        $I->amGoingTo('submit unexisting project link slug');
        $I->sendPOST('/previews', ['slug' => 'missing']);
        $I->seeNotFoundResponse();

        $I->amGoingTo('try accessing password protected project link with no password');
        $I->sendPOST('/previews', ['slug' => 'test2']);
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('try accessing password protected project link with wrong password');
        $I->sendPOST('/previews', ['slug' => 'test2', 'password' => 'wrong_password']);
        $I->seeUnauthorizedResponse();
    }

    /**
     * `PreviewCest::actionAuthorize()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function authorizeSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully authorize project link access');

        $testScenarios = [
            [
                'comment' => 'authorize and access password unprotected project link',
                'data' => [
                    'slug' => 'test1',
                ],
                'expected' => [
                    'prototypes' => [
                        ['id' => 1001],
                        ['id' => 1002],
                    ],
                ],
            ],
            [
                'comment' => 'authorize and access password protected project link',
                'data' => [
                    'slug'     => 'test2',
                    'password' => '123456',
                    'expected' => [
                        'prototypes' => [
                            ['id' => 1003],
                        ],
                    ],
                ],
            ],
            [
                'comment' => 'authorize and access project link with restricted prototypes',
                'data' => [
                    'slug' => 'test6',
                ],
                'expected' => [
                    'prototypes' => [
                        ['id' => 1006],
                    ],
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->sendPOST('/previews', $scenario['data']);
            $I->seeResponseIsJson();
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'token'       => 'string',
                'project'     => 'array',
                'projectLink' => [
                    'slug' => ('string:=' . $scenario['data']['slug']),
                ],
                'prototypes'    => 'array',
                'collaborators' => 'array',
            ]);
            $I->dontSeeResponseJsonMatchesJsonPath('$.projectLink.password');
            $I->dontSeeResponseJsonMatchesJsonPath('$.projectLink.passwordHash');

            if (!empty($scenario['expected'])) {
                $I->seeResponseContainsJson($scenario['expected']);
            }
        }
    }

    /* `PreviewCest::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `PreviewCest::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully access index action');

        $projectLink = ProjectLink::findOne(1001);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/previews');
        $I->seeUnauthorizedResponse();

        // simulate expired token
        $previewTokenDuration = Yii::$app->params['previewTokenDuration'];
        Yii::$app->params['previewTokenDuration'] = -1000;
        $I->amGoingTo('try accessing the action with expired preview token');
        $I->haveHttpHeader('X-Preview-Token', $projectLink->generatePreviewToken());
        $I->sendGET('/previews');
        $I->seeUnauthorizedResponse();
        // revert changes
        Yii::$app->params['previewTokenDuration'] = $previewTokenDuration;

        $token = $projectLink->generatePreviewToken();
        $projectLink->delete();
        $I->amGoingTo('try accessing the action with deleted project link token');
        $I->haveHttpHeader('X-Preview-Token', $token);
        $I->sendGET('/previews');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `PreviewCest::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully access index action');

        $projectLink = ProjectLink::findOne(1006);

        $I->haveHttpHeader('X-Preview-Token', $projectLink->generatePreviewToken());
        $I->sendGET('/previews');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'project'     => 'array',
            'projectLink' => [
                'slug' => ('string:=' . $projectLink->slug),
            ],
            'prototypes'    => 'array',
            'collaborators' => 'array',
        ]);
        $I->dontSeeResponseJsonMatchesJsonPath('$.projectLink.password');
        $I->dontSeeResponseJsonMatchesJsonPath('$.projectLink.passwordHash');
        $I->seeResponseContainsJson([
            'prototypes' => [
                ['id' => 1006],
            ],
        ]);
    }

    /* `PreviewCest::actionPrototype()`
    --------------------------------------------------------------- */
    /**
     * `PreviewCest::actionPrototype()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function prototypeFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list proejct link prototype details');

        $projectLink = ProjectLink::findOne(1006);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/previews/prototypes/1006');
        $I->seeUnauthorizedResponse();

        // simulate expired token
        $previewTokenDuration = Yii::$app->params['previewTokenDuration'];
        Yii::$app->params['previewTokenDuration'] = -1000;
        $I->amGoingTo('try accessing the action with expired preview token');
        $I->haveHttpHeader('X-Preview-Token', $projectLink->generatePreviewToken());
        $I->sendGET('/previews/prototypes/1006');
        $I->seeUnauthorizedResponse();
        // revert changes
        Yii::$app->params['previewTokenDuration'] = $previewTokenDuration;

        $I->amGoingTo('try accessing a missing prototype or a prototype that the project link is not allowed to access');
        $I->haveHttpHeader('X-Preview-Token', $projectLink->generatePreviewToken());
        $I->sendGET('/previews/prototypes/1005');
        $I->seeNotFoundResponse();
    }

    /**
     * `PreviewCest::actionPrototype()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function prototypeSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list proejct link prototype details');

        $projectLink = ProjectLink::findOne(1001);

        $I->haveHttpHeader('X-Preview-Token', $projectLink->generatePreviewToken());
        $I->sendGET('/previews/prototypes/1001', [
            'expand' => 'screens.screenComments' // should be ignored
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'               => 'integer:=1001',
            'screens'          => 'array',
            'hotspotTemplates' => 'array',
        ]);
        $I->seeResponseMatchesJsonType([
            'id'       => 'integer',
            'file'     => 'array',
            'hotspots' => 'array',
        ], '$..screens.*');
        $I->seeResponseMatchesJsonType([
            'id'        => 'integer',
            'screenIds' => 'array',
            'hotspots'  => 'array',
        ], '$..hotspotTemplates.*');
        $I->dontSeeResponseJsonMatchesJsonPath('$..screens.*.screenComments');
    }

    /* `PreviewCest::actionAssets()`
    --------------------------------------------------------------- */
    /**
     * `PreviewCest::actionAssets()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function assetsFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list project link guideline assets');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/previews/assets');
        $I->seeUnauthorizedResponse();

        // simulate expired token
        $previewTokenDuration = Yii::$app->params['previewTokenDuration'];
        Yii::$app->params['previewTokenDuration'] = -1000;
        $I->amGoingTo('try accessing the action with expired preview token');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(1006)->generatePreviewToken());
        $I->sendGET('/previews/assets');
        $I->seeUnauthorizedResponse();
        // revert changes
        Yii::$app->params['previewTokenDuration'] = $previewTokenDuration;

        $I->amGoingTo('try to list guideline assets from a project link that has disabled them');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(['allowGuideline' => 0])->generatePreviewToken());
        $I->sendGET('/previews/assets');
        $I->seeNotFoundResponse();
    }

    /**
     * `PreviewCest::actionAssets()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function assetsSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list project link guideline assets');

        $projectLink = ProjectLink::findOne(['allowGuideline' => 1]);

        $I->haveHttpHeader('X-Preview-Token', $projectLink->generatePreviewToken());
        $I->sendGET('/previews/assets', [
            'expand' => 'assets.project.screens.screenComments', // should be ignored
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'     => 'integer',
            'assets' => 'array',
        ]);
        $I->seeResponseMatchesJsonType([
            'id'   => 'integer',
            'hex'  => 'null|string',
            'file' => 'null|array',
        ], '$..assets.*');
        $I->dontSeeResponseJsonMatchesJsonPath('$..assets.*.filePath');
        $I->dontSeeResponseJsonMatchesJsonPath('$..assets.*.project');
    }

    /* `PreviewCest::actionListScreenComments()`
    --------------------------------------------------------------- */
    /**
     * `PreviewCest::actionLoremactionListScreenComments failure test.
     *
     * @param FunctionalTester $I
     */
    public function listScreenCommentsFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list project link screen comments');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/previews/screen-comments');
        $I->seeUnauthorizedResponse();

        // simulate expired token
        $previewTokenDuration = Yii::$app->params['previewTokenDuration'];
        Yii::$app->params['previewTokenDuration'] = -1000;
        $I->amGoingTo('try accessing the action with expired preview token');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(['allowComments' => 1])->generatePreviewToken());
        $I->sendGET('/previews/screen-comments');
        $I->seeUnauthorizedResponse();
        // revert changes
        Yii::$app->params['previewTokenDuration'] = $previewTokenDuration;

        $I->amGoingTo('try to list screen comments from a project link that has disabled them');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(['allowComments' => 0])->generatePreviewToken());
        $I->sendGET('/previews/screen-comments');
        $I->seeNotFoundResponse();
    }

    /**
     * `PreviewCest::actionListScreenComments()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function listScreenCommentsSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list project link screen comments');

        $projectLink = ProjectLink::findOne(1002);

        $I->haveHttpHeader('X-Preview-Token', $projectLink->generatePreviewToken());
        $I->sendAndCheckDataProviderResponses(
            '/previews/screen-comments?expand=screen.prototype.project.prototypes',
            [
                [
                    'params'   => [],
                    'expected' => [1001, 1002, 1003],
                ],
                [
                    'params'   => ['per-page' => 1, 'page' => 2],
                    'expected' => [1002],
                ],
                [
                    'params'   => ['search[screenId]' => 1002],
                    'expected' => [1003],
                ],
                [
                    'params'   => ['search[prototypeId]' => 1005],
                    'expected' => [],
                ],
                [
                    'params'   => ['search[replyTo]' => 0],
                    'expected' => [1001, 1003],
                ],
                [
                    'params'   => ['sort' => '-createdAt'],
                    'expected' => [1003, 1002, 1001],
                ],
            ], function ($scenarioIndex, $scenarioData) use ($I) {
                if (!empty($scenarioData['expected'])) {
                    $I->seeResponseMatchesJsonType([
                        'id'       => 'integer',
                        'fromUser' => 'null|array',
                    ]);
                    $I->dontSeeResponseContainsUserHiddenFields('fromUser');
                    $I->dontSeeResponseJsonMatchesJsonPath('$..screen');
                }
            }
        );
    }

    /* `PreviewCest::actionCreateScreenComment()`
    --------------------------------------------------------------- */
    /**
     * `PreviewCest::actionLoremactionCreateScreenCommentfailure test.
     *
     * @param FunctionalTester $I
     */
    public function createScreenCommentFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create project link screen comment');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/previews/screen-comments');
        $I->seeUnauthorizedResponse();

        // simulate expired token
        $previewTokenDuration = Yii::$app->params['previewTokenDuration'];
        Yii::$app->params['previewTokenDuration'] = -1000;
        $I->amGoingTo('try accessing the action with expired preview token');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(['allowComments' => 1])->generatePreviewToken());
        $I->sendPOST('/previews/screen-comments');
        $I->seeUnauthorizedResponse();
        // revert changes
        Yii::$app->params['previewTokenDuration'] = $previewTokenDuration;

        $I->amGoingTo('try to create a screen comment to a project link that has disabled them');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(['allowComments' => 0])->generatePreviewToken());
        $I->sendPOST('/previews/screen-comments');
        $I->seeNotFoundResponse();

        $I->amGoingTo('try to create a screen comment with invalid form data');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(['allowComments' => 1])->generatePreviewToken());
        $I->sendPOST('/previews/screen-comments', [
            'screenId' => 1007,
            'from'     => 'test2@example.com',
            'replyTo'  => 1006,
            'message'  => '',
            'left'     => -10,
            'top'      => -10,
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'screenId' => 'string',
                'from'     => 'string',
                'replyTo'  => 'string',
                'message'  => 'string',
                'left'     => 'string',
                'top'      => 'string',
            ],
        ]);
    }

    /**
     * `PreviewCest::actionCreateScreenComment()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createScreenCommentSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create project link screen comment');

        $projectLink = ProjectLink::findOne(1002);

        $data = [
            'screenId' => 1002,
            'from'     => 'joe@lorep.ipsum',
            'replyTo'  => null,
            'message'  => 'test_create +test2@example.com +test3@example.com +test@example.com +missing@example.com',
            'left'     => 100,
            'top'      => 0,
        ];

        $I->haveHttpHeader('X-Preview-Token', $projectLink->generatePreviewToken());
        $I->sendPOST('/previews/screen-comments', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'screenId' => 'integer',
            'replyTo'  => 'integer|null',
            'message'  => 'string',
            'left'     => 'integer|float',
            'top'      => 'integer|float',
            'fromUser' => 'null|array',
        ]);
        $I->seeResponseContainsJson($data);
        $I->dontSeeResponseContainsUserHiddenFields('fromUser');
        $I->seeEmailIsSent(2); // to guests test and test3; test2 is the admin of the project and its email notifications are handled by the mails console command

        // verify that the project users are notified
        $commentId = $I->grabDataFromResponseByJsonPath('$.id');
        foreach ($projectLink->project->users as $user) {
            if ($user->email != $data['from']) {
                $I->seeRecord(UserScreenCommentRel::class, [
                    'userId'          => $user->id,
                    'screenCommentId' => $commentId,
                    'isRead'          => 0,
                ]);
            }
        }
    }

    /* `PreviewCest::actionUpdateScreenComment()`
    --------------------------------------------------------------- */
    /**
     * `PreviewCest::actionLoremactionUpdateScreenCommentfailure test.
     *
     * @param FunctionalTester $I
     */
    public function updateScreenCommentFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update project link screen comment');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/previews/screen-comments/1001');
        $I->seeUnauthorizedResponse();

        // simulate expired token
        $previewTokenDuration = Yii::$app->params['previewTokenDuration'];
        Yii::$app->params['previewTokenDuration'] = -1000;
        $I->amGoingTo('try accessing the action with expired preview token');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(['allowComments' => 1])->generatePreviewToken());
        $I->sendPUT('/previews/screen-comments/1001');
        $I->seeUnauthorizedResponse();
        // revert changes
        Yii::$app->params['previewTokenDuration'] = $previewTokenDuration;

        $I->amGoingTo('try to update a screen comment to a project link that has disabled them');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(1001)->generatePreviewToken());
        $I->sendPUT('/previews/screen-comments/1001');
        $I->seeNotFoundResponse();

        $I->amGoingTo('try to update a reply screen comment');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(1002)->generatePreviewToken());
        $I->sendPUT('/previews/screen-comments/1002', ['status' => 'invalid']);
        $I->seeNotFoundResponse();

        $I->amGoingTo('try to update a screen comment with invalid form data');
        $I->haveHttpHeader('X-Preview-Token', ProjectLink::findOne(1002)->generatePreviewToken());
        $I->sendPUT('/previews/screen-comments/1001', ['status' => 'invalid']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'status' => 'string',
            ],
        ]);
    }

    /**
     * `PreviewCest::actionUpdateScreenComment()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateScreenCommentSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update project link screen comment');

        $projectLink = ProjectLink::findOne(1002);

        $data = ['status' => ScreenComment::STATUS['RESOLVED']];

        $I->haveHttpHeader('X-Preview-Token', $projectLink->generatePreviewToken());
        $I->sendPUT('/previews/screen-comments/1001', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'screenId' => 'integer',
            'replyTo'  => 'integer|null',
            'message'  => 'string',
            'left'     => 'integer|float',
            'top'      => 'integer|float',
            'fromUser' => 'null|array',
        ]);
        $I->seeResponseContainsJson($data);
        $I->dontSeeResponseContainsUserHiddenFields('fromUser');
    }
}
