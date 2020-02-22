<?php
namespace presentator\api\tests\functional;

use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\PrototypeFixture;
use presentator\api\tests\fixtures\ProjectLinkFixture;
use presentator\api\tests\fixtures\UserProjectRelFixture;
use presentator\api\tests\fixtures\UserProjectLinkRelFixture;
use presentator\api\tests\fixtures\ProjectLinkPrototypeRelFixture;
use presentator\api\models\User;
use presentator\api\models\ProjectLink;

/**
 * ProjectLinksController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectLinksCest
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
            'PrototypeFixture' => [
                'class' => PrototypeFixture::class,
            ],
            'ProjectLinkFixture' => [
                'class' => ProjectLinkFixture::class,
            ],
            'ProjectLinkPrototypeRelFixture' => [
                'class' => ProjectLinkPrototypeRelFixture::class,
            ],
            'UserFixture' => [
                'class' => UserFixture::class,
            ],
            'UserProjectRelFixture' => [
                'class' => UserProjectRelFixture::class,
            ],
            'UserProjectLinkRelFixture' => [
                'class' => UserProjectLinkRelFixture::class,
            ],
        ]);
    }

    /* `ProjectLinksController::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `ProjectLinksController::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list project links');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/project-links');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `ProjectLinksController::actionIndex()` success test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list project links');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $scenarioCallback = function ($scenarioIndex, $scenarioData) use ($I) {
            if (!empty($scenarioData['expected'])) {
                $I->seeResponseMatchesJsonType([
                    'id'                => 'integer',
                    'slug'              => 'string',
                    'passwordProtected' => 'boolean',
                    'prototypes'        => 'array',
                ]);
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.password');
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.passwordHash');
            }
        };

        $I->amGoingTo('authorize as regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/project-links', [
            [
                'params'   => [],
                'expected' => [1001, 1002, 1003, 1004],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1002],
            ],
            [
                'params'   => ['search[projectId]' => 1001],
                'expected' => [1001, 1002],
            ],
            [
                'params'   => ['search[projectId]' => 1005],
                'expected' => [],
            ],
            [
                'params'   => ['sort' => '-slug'],
                'expected' => [1004, 1003, 1002, 1001],
            ],
        ], $scenarioCallback);

        $I->amGoingTo('authorize as super user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/project-links', [
            [
                'params'   => [],
                'expected' => [1001, 1002, 1003, 1004, 1005, 1006],
            ],
            [
                'params'   => ['per-page' => 2, 'page' => 2],
                'expected' => [1003, 1004],
            ],
            [
                'params'   => ['search[projectId]' => 1003],
                'expected' => [1005],
            ],
            [
                'params'   => ['sort' => '-slug'],
                'expected' => [1006, 1005, 1004, 1003, 1002, 1001],
            ],
        ], $scenarioCallback);
    }

    /* `ProjectLinksController::actionCreate()`
    --------------------------------------------------------------- */
    /**
     * `ProjectLinksController::actionCreate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create new project link');

        $user = User::findOne(1002);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/project-links');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize and submit invalid form data');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendPOST('/project-links', [
            'projectId'      => 1005,
            'allowComments'  => -10,
            'allowGuideline' => 10,
            'password'       => '123',
            'prototypes'     => [1001],
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'projectId'      => 'string',
                'allowComments'  => 'string',
                'allowGuideline' => 'string',
                'password'       => 'string',
                'prototypes'     => 'string',
            ],
        ]);
    }

    /**
     * `ProjectLinksController::actionCreate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create new project link');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and create a new project link for an owned project',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => [
                    'projectId'      => 1001,
                    'allowComments'  => 1,
                    'allowGuideline' => 0,
                    'password'       => '123456',
                    'prototypes'     => [1001, 1002],
                ],
            ],
            [
                'comment' => 'authorize as super user and create a new project link for a project',
                'token'   => $superUser->generateAccessToken(),
                'data'    => [
                    'projectId'      => 1005,
                    'allowGuideline' => 1,
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST('/project-links', $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'projectId'         => 'integer',
                'allowComments'     => 'integer',
                'allowGuideline'    => 'integer',
                'passwordProtected' => 'boolean',
                'prototypes'        => 'array',
            ]);

            $dataToCheck = $scenario['data'];
            unset($dataToCheck['password']);
            unset($dataToCheck['prototypes']);

            $I->seeResponseContainsJson($dataToCheck);
            $I->dontSeeResponseJsonMatchesJsonPath('$.password');
            $I->dontSeeResponseJsonMatchesJsonPath('$.passwordHash');
        }
    }

    /* `ProjectLinksController::actionUpdate()`
    --------------------------------------------------------------- */
    /**
     * `ProjectLinksController::actionUpdate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update project link');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/project-links/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to update project link owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/project-links/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and submit invalid form data to a random project link');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/project-links/1006', [
            'projectId'      => 123456,
            'allowComments'  => -10,
            'allowGuideline' => 10,
            'password'       => '123',
            'prototypes'     => [123456, 1005],
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'projectId'      => 'string',
                'allowComments'  => 'string',
                'allowGuideline' => 'string',
                'password'       => 'string',
                'prototypes'     => 'string',
            ],
        ]);
    }

    /**
     * `ProjectLinksController::actionUpdate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update project link');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and update owned project link',
                'token'   => $regularUser->generateAccessToken(),
                'linkId'  => 1001,
                'data'    => [
                    'projectId'      => 1002,
                    'allowComments'  => 1,
                    'allowGuideline' => 1,
                    'password'       => '123456',
                    'prototypes'     => [1003],
                ],
            ],
            [
                'comment' => 'authorize as super user and update a project link',
                'token'   => $superUser->generateAccessToken(),
                'linkId'  => 1002,
                'data'    => [
                    'projectId'     => 1005,
                    'allowComments' => 1,
                    'password'      => '',
                    'prototypes'    => [],
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/project-links/' . $scenario['linkId'], $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'             => ('integer:=' . $scenario['linkId']),
                'projectId'      => 'integer',
                'allowComments'  => 'integer',
                'allowGuideline' => 'integer',
                'prototypes'     => 'array',
            ]);

            $dataToCheck = $scenario['data'];
            unset($dataToCheck['password']);
            unset($dataToCheck['prototypes']);

            $I->seeResponseContainsJson($dataToCheck);
            $I->dontSeeResponseJsonMatchesJsonPath('$.password');
            $I->dontSeeResponseJsonMatchesJsonPath('$.passwordHash');
        }
    }

    /* `ProjectLinksController::actionView()`
    --------------------------------------------------------------- */
    /**
     * `ProjectLinksController::actionView()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function viewFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully view project link');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/project-links/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to view project link owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/project-links/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to view unexisting project link');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/project-links/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `ProjectLinksController::actionView()` success test.
     *
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully view project link');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and try to view owned project link',
                'token'   => $regularUser->generateAccessToken(),
                'linkId'  => 1001,
            ],
            [
                'comment' => 'authorize as super user and try to view a project link',
                'token'   => $superUser->generateAccessToken(),
                'linkId'  => 1005,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET('/project-links/' . $scenario['linkId']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id'                => 'integer:=' . $scenario['linkId'],
                'passwordProtected' => 'boolean',
                'prototypes'        => 'array',
            ]);
            $I->dontSeeResponseJsonMatchesJsonPath('$.password');
            $I->dontSeeResponseJsonMatchesJsonPath('$.passwordHash');
        }
    }

    /* `ProjectLinksController::actionDelete()`
    --------------------------------------------------------------- */
    /**
     * `ProjectLinksController::actionDelete()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function deleteFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully delete project link');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/project-links/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to delete project link owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/project-links/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to delete unexisting project link');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/project-links/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `ProjectLinksController::actionDelete()` success test.
     *
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete project link');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and try to delete an owned project link',
                'token'   => $regularUser->generateAccessToken(),
                'linkId'  => 1002,
            ],
            [
                'comment' => 'authorize as super user and try to delete a project link',
                'token'   => $superUser->generateAccessToken(),
                'linkId'  => 1005,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE('/project-links/' . $scenario['linkId']);
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(ProjectLink::class, ['id' => $scenario['linkId']]);
        }
    }

    /* `ProjectLinksController::actionShare()`
    --------------------------------------------------------------- */
    /**
     * `ProjectLinksController::actionShare()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function shareFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully share project link');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/project-links/1005/share');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to share project link owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPOST('/project-links/1005/share');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to share unexisting project link');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPOST('/project-links/123456/share');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and submit invalid share form data');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPOST('/project-links/1005/share', [
            'email'   => 'invalid',
            'message' => '',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'email'   => 'string',
                'message' => 'string',
            ],
        ]);
        $I->dontSeeEmailIsSent();
    }

    /**
     * `ProjectLinksController::actionShare()` success test.
     *
     * @param FunctionalTester $I
     */
    public function shareSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully share project link');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and share owned project link',
                'token'   => $regularUser->generateAccessToken(),
                'linkId'  => 1001,
                'data'    => [
                    'email'   => 'test1@example.com, test2@example.com, test3@example.com',
                    'message' => 'Lorem ipsum...',
                ],
                'expectedEmailsCount' => 3,
            ],
            [
                'comment' => 'authorize as super user and share a project link',
                'token'   => $superUser->generateAccessToken(),
                'linkId'  => 1006,
                'data'    => [
                    'email'   => 'test@example.com',
                    'message' => 'Lorem ipsum...',
                ],
                'expectedEmailsCount' => 1,
            ],
        ];

        $totalExpectedEmails = 0;

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST("/project-links/{$scenario['linkId']}/share", $scenario['data']);
            $I->seeResponseCodeIs(204);

            $totalExpectedEmails += $scenario['expectedEmailsCount'];
            $I->seeEmailIsSent($totalExpectedEmails);
        }
    }


    /* `ProjectLinksController::actionAccessed()`
    --------------------------------------------------------------- */
    /**
     * `ProjectLinksController::actionAccessed()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function accessedFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list recently accessed project links');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/project-links/accessed');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `ProjectLinksController::actionAccessed()` success test.
     *
     * @param FunctionalTester $I
     */
    public function accessedSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list recently accessed project links');

        $user = User::findOne(1002);

        $scenarioCallback = function ($scenarioIndex, $scenarioData) use ($I) {
            if (!empty($scenarioData['expected'])) {
                $I->seeResponseMatchesJsonType([
                    'id'                => 'integer',
                    'slug'              => 'string',
                    'passwordProtected' => 'boolean',
                    'projectInfo'       => 'array',
                ]);
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.prototypes');
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.password');
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.passwordHash');
            }
        };

        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/project-links/accessed', [
            [
                'params'   => [],
                'expected' => [1005, 1002, 1003, 1001],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1005, 1003, 1002, 1001], // initially updatedAt desc should be applied and then desc by id
            ],
            [
                'params'   => ['expand' => 'prototypes'], // should be ignored
                'expected' => [1005, 1002, 1003, 1001],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1002],
            ],
            [
                'params'   => ['search[projectId]' => 1002],
                'expected' => [1003],
            ],
            [
                'params'   => ['search[slug]' => 'test5'],
                'expected' => [1005],
            ],
        ], $scenarioCallback);
    }
}
