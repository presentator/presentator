<?php
namespace presentator\api\tests\functional;

use Yii;
use yii\helpers\ArrayHelper;
use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\PrototypeFixture;
use presentator\api\tests\fixtures\ScreenFixture;
use presentator\api\tests\fixtures\ScreenCommentFixture;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\UserProjectRelFixture;
use presentator\api\tests\fixtures\UserScreenCommentRelFixture;
use presentator\api\models\Project;
use presentator\api\models\User;
use presentator\api\models\UserProjectRel;

/**
 * ProjectsController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectsCest
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
            'ScreenFixture' => [
                'class' => ScreenFixture::class,
            ],
            'ScreenCommentFixture' => [
                'class' => ScreenCommentFixture::class,
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

    /* `ProjectsController::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list projects');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/projects');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `ProjectsController::actionIndex()` success test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list projects');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(1003);

        $I->amGoingTo('authorize as regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/projects', [
            [
                'params'   => [],
                'expected' => [1001, 1002],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1002],
            ],
            [
                'params'   => ['search[archived]' => 1],
                'expected' => [1002],
            ],
            [
                'params'   => ['search[title]' => '1'],
                'expected' => [1001],
            ],
            [
                'params'   => ['search[pinned]' => 1],
                'expected' => [1001],
            ],
            [
                'params'   => ['sort' => '-title'],
                'expected' => [1001, 1002],
            ],
            [
                'params'   => ['sort' => 'createdAt'],
                'expected' => [1001, 1002],
            ],
        ], function ($scenarioIndex, $scenarioData) use ($I) {
            $I->seeResponseMatchesJsonType([
                'pinned'         => 'integer',
                'featuredScreen' => 'array',
            ]);
        });

        $I->amGoingTo('authorize as super user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/projects', [
            [
                'params'   => [],
                'expected' => [1002, 1001, 1005, 1004, 1003],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1001],
            ],
            [
                'params'   => ['search[archived]' => 1],
                'expected' => [1002, 1005],
            ],
            [
                'params'   => ['search[pinned]' => 1],
                'expected' => [1002],
            ],
            [
                'params'   => ['search[title]' => '1'],
                'expected' => [1001],
            ],
            [
                'params'   => ['sort' => '-title'],
                'expected' => [1002, 1005, 1004, 1003, 1001],
            ],
        ], function ($scenarioIndex, $scenarioData) use ($I) {
            $I->seeResponseMatchesJsonType([
                'pinned'         => 'integer',
                'featuredScreen' => 'array',
            ]);
        });
    }

    /* `ProjectsController::actionCreate()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionCreate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create new project');

        $user = User::findOne(1004);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/projects');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize and submit invalid form data');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendPOST('/projects', [
            'title'    => '',
            'archived' => 'invalid',
            'pinned'   => 'invalid',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'title'    => 'string',
                'archived' => 'string',
                'pinned'   => 'string',
            ],
        ]);
    }

    /**
     * `ProjectsController::actionCreate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create new project');

        $user = User::findOne(1004);

        $testScenarios = [
            [
                'comment' => 'create project with defaults',
                'data'    => [
                    'title' => 'create_test',
                ],
                'expected' => [
                    'title'    => 'create_test',
                    'archived' => 0,
                    'pinned'   => 0,
                ],
            ],
            [
                'comment' => 'create pinned project',
                'data'    => [
                    'title'  => 'create_test2',
                    'pinned' => 1,
                ],
                'expected' => [
                    'title'    => 'create_test2',
                    'archived' => 0,
                    'pinned'   => 1,
                ],
            ],
            [
                'comment' => 'create archived project',
                'data'    => [
                    'title'    => 'create_test3',
                    'archived' => 1,
                ],
                'expected' => [
                    'title'    => 'create_test3',
                    'archived' => 1,
                ],
            ],
            [
                'comment' => 'create pinned and archived project',
                'data'    => [
                    'title'    => 'create_test4',
                    'pinned'   => 1,
                    'archived' => 1,
                ],
                'expected' => [
                    'title'    => 'create_test4',
                    'archived' => 1,
                    'pinned'   => 1,
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
            $I->sendPOST('/projects', $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'             => 'integer',
                'title'          => 'string',
                'archived'       => 'integer',
                'pinned'         => 'integer',
                'featuredScreen' => 'array',
            ]);
            $I->seeResponseContainsJson($scenario['expected']);
            $I->seeRecord(UserProjectRel::class, [
                'userId'    => $user->id,
                'projectId' => $I->grabDataFromResponseByJsonPath('$.id'),
            ]);
        }
    }

    /* `ProjectsController::actionUpdate()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionUpdate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update project');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/projects/1001');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to update project owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/projects/1001');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and submit invalid form data to a random project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/projects/1001', [
            'title'    => '',
            'archived' => 'invalid',
            'pinned'   => 'invalid',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'title'    => 'string',
                'archived' => 'string',
                'pinned'   => 'string',
            ],
        ]);
    }

    /**
     * `ProjectsController::actionUpdate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update project');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and update owned project',
                'token'     => $regularUser->generateAccessToken(),
                'projectId' => 1003,
                'data'      => [
                    'title' => 'update_test',
                ],
                'expected' => [
                    'title'    => 'update_test',
                    'archived' => 0,
                    'pinned'   => 0,
                ],
            ],
            [
                'comment'   => 'authorize as super user and update a project',
                'token'     => $superUser->generateAccessToken(),
                'projectId' => 1005,
                'data'      => [
                    'title' => 'update_test2',
                ],
                'expected' => [
                    'title'    => 'update_test2',
                    'archived' => 1,
                    'pinned'   => 0,
                ],
            ],
            [
                'comment'   => 'archive a project',
                'token'     => $superUser->generateAccessToken(),
                'projectId' => 1002,
                'data'      => [
                    'title'    => 'update_test3',
                    'archived' => 1,
                ],
                'expected' => [
                    'title'    => 'update_test3',
                    'archived' => 1,
                    'pinned'   => 1,
                ],
            ],
            [
                'comment'   => 'pin a project',
                'token'     => $superUser->generateAccessToken(),
                'projectId' => 1004,
                'data'      => [
                    'title'    => 'update_test4',
                    'pinned'   => 1,
                ],
                'expected' => [
                    'title'    => 'update_test4',
                    'archived' => 0,
                    'pinned'   => 1,
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/projects/' . $scenario['projectId'], $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'             => ('integer:=' . $scenario['projectId']),
                'title'          => 'string',
                'archived'       => 'integer',
                'pinned'         => 'integer',
                'featuredScreen' => 'array',
            ]);
            $I->seeResponseContainsJson($scenario['expected']);
        }
    }

    /* `ProjectsController::actionView()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionView()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function viewFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully view project');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/projects/1001');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to view project owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/projects/1001');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to view unexisting project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/projects/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `ProjectsController::actionView()` success test.
     *
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully view project');

        $regularUser  = User::findOne(1004);
        $superUser    = User::findOne(1003);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and try to view owned project',
                'token'     => $regularUser->generateAccessToken(),
                'projectId' => $regularUser->projects[0]->id,
            ],
            [
                'comment'   => 'authorize as super user and try to view any project',
                'token'     => $superUser->generateAccessToken(),
                'projectId' => 1001,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET('/projects/' . $scenario['projectId']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id' => 'integer:=' . $scenario['projectId'],
                'featuredScreen' => 'array',
            ]);
        }
    }

    /* `ProjectsController::actionDelete()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionDelete()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function deleteFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully delete project');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/projects/1001');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to delete project owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/projects/1001');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to delete unexisting project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/projects/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `ProjectsController::actionDelete()` success test.
     *
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete project');

        $regularUser  = User::findOne(1004);
        $superUser    = User::findOne(1003);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and try to delete an owned project',
                'token'     => $regularUser->generateAccessToken(),
                'projectId' => $regularUser->projects[0]->id,
            ],
            [
                'comment'   => 'authorize as super user and try to delete any project',
                'token'     => $superUser->generateAccessToken(),
                'projectId' => 1001,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE('/projects/' . $scenario['projectId']);
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(Project::class, ['id' => $scenario['projectId']]);
        }
    }

    /* `ProjectsController::actionListCollaborators()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionListCollaborators()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function listCollaboratorsFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list project collaborators');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/projects/1001/collaborators');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to list collaborators within a project owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/projects/1001/collaborators');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to list collaborators within an unexisting project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/projects/123456/collaborators');
        $I->seeNotFoundResponse();
    }

    /**
     * `ProjectsController::actionListCollaborators()` success test.
     *
     * @param FunctionalTester $I
     */
    public function listCollaboratorsSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list project collaborators');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $testScenarios = [
            [
                'comment'        => 'authorize as regular user and list owned project collaborators',
                'token'          => $regularUser->generateAccessToken(),
                'projectId'      => 1003,
                'expectedEmails' => ['guest@example.com', $regularUser->email],
            ],
            [
                'comment'        => 'authorize as super user and list any project collaborators',
                'token'          => $superUser->generateAccessToken(),
                'projectId'      => 1001,
                'expectedEmails' => ['test@example.com', 'test2@example.com', 'test3@example.com'],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET("/projects/{$scenario['projectId']}/collaborators");
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();

            if (empty($scenario['expectedEmails'])) {
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.email');
            } else {
                $I->seeResponseMatchesJsonType([
                    'id'        => 'integer|null',
                    'email'     => 'string',
                    'firstName' => 'string|null',
                    'lastName'  => 'string|null',
                    'avatar'    => 'array|null',
                ]);
                $I->dontSeeResponseContainsUserHiddenFields('*');

                foreach ($scenario['expectedEmails'] as $email) {
                    $I->seeResponseContainsJson(['email' => $email]);
                }

                // count match
                $I->dontSeeResponseJsonMatchesJsonPath('$.' . count($scenario['expectedEmails']));
            }
        }
    }

    /* `ProjectsController::actionSearchUsers()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionSearchUsers()` actionSearchUsers test.
     *
     * @param FunctionalTester $I
     */
    public function searchUsersFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully search for new project admins');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/projects/1001/users/search', ['search' => 'test...']);
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to search for users within a project owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/projects/1001/users/search', ['search' => 'test...']);
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to search for users within an unexisting project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/projects/123456/users/search', ['search' => 'test...']);
        $I->seeNotFoundResponse();
    }

    /**
     * `ProjectsController::actionSearchUsers()` success test.
     *
     * @param FunctionalTester $I
     */
    public function searchUsersSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully search for new project admins');

        $project     = Project::findOne(1001);
        $regularUser = $project->getUsers()->andWhere(['type' => User::TYPE['REGULAR']])->one();
        $superUser   = User::findOne(1003);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and pass an empty search term (loose)',
                'token'     => $regularUser->generateAccessToken(),
                'projectId' => $project->id,
                'search'    => '',
                'expected'  => [],
                'loose'     => true,
            ],
            [
                'comment'   => 'authorize as regular user and pass a working search term (loose)',
                'token'     => $regularUser->generateAccessToken(),
                'projectId' => $project->id,
                'search'    => '@example.com',
                'expected'  => [1003, 1004, 1005],
                'loose'     => true,
            ],
            [
                'comment'   => 'authorize as super user and pass a working search term (loose)',
                'token'     => $superUser->generateAccessToken(),
                'projectId' => $project->id,
                'search'    => 'jun',
                'expected'  => [1003, 1005],
                'loose'     => true,
            ],
            [
                'comment'   => 'authorize as regular user and pass a working search term (strict)',
                'token'     => $regularUser->generateAccessToken(),
                'projectId' => $project->id,
                'search'    => '@example.com',
                'expected'  => [],
                'loose'     => false,
            ],
            [
                'comment'   => 'authorize as regular user and pass a working search term (strict)',
                'token'     => $regularUser->generateAccessToken(),
                'projectId' => $project->id,
                'search'    => 'test3@example.com',
                'expected'  => [1003],
                'loose'     => false,
            ],
        ];

        $appLooseSearch = Yii::$app->params['looseProjectUsersSearch'];

        foreach ($testScenarios as $scenario) {
            if (!empty($scenario['loose'])) {
                Yii::$app->params['looseProjectUsersSearch'] = true;
            } else {
                Yii::$app->params['looseProjectUsersSearch'] = false;
            }

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET("/projects/{$scenario['projectId']}/users/search", ['search' => $scenario['search']]);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();

            if (empty($scenario['expected'])) {
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.id');
            } else {
                $I->seeResponseMatchesJsonType([
                    'id'        => 'integer',
                    'email'     => 'string',
                    'firstName' => 'string|null',
                    'lastName'  => 'string|null',
                    'avatar'    => 'array',
                ]);
                $I->dontSeeResponseContainsUserHiddenFields('*');

                foreach ($scenario['expected'] as $id) {
                    $I->seeResponseContainsJson(['id' => $id]);
                }

                // count match
                $I->dontSeeResponseJsonMatchesJsonPath('$.' . count($scenario['expected']));
            }
        }

        // revert changes
        Yii::$app->params['looseProjectUsersSearch'] = $appLooseSearch;
    }

    /* `ProjectsController::actionListUsers()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionListUsers` failure test.
     *
     * @param FunctionalTester $I
     */
    public function listUsersFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list project admins');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/projects/1001/users');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to list the users of a project owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/projects/1001/users');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to list the users of an unexisting project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/projects/123456/users');
        $I->seeNotFoundResponse();
    }

    /**
     * `ProjectsController::actionListUsers()` success test.
     *
     * @param FunctionalTester $I
     */
    public function listUsersSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list project admins');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(1003);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and list users of an owned project',
                'token'     => $regularUser->generateAccessToken(),
                'projectId' => 1002,
                'expected'  => [1002, 1003],
            ],
            [
                'comment'   => 'authorize as super user and list users of any project',
                'token'     => $superUser->generateAccessToken(),
                'projectId' => 1003,
                'expected'  => [1004],
            ],
            [
                'comment'   => 'authorize as super user and list users of a project without any users',
                'token'     => $superUser->generateAccessToken(),
                'projectId' => 1005,
                'expected'  => [],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET("/projects/{$scenario['projectId']}/users");
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();

            if (empty($scenario['expected'])) {
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.id');
            } else {
                $I->seeResponseMatchesJsonType([
                    'id'        => 'integer',
                    'email'     => 'string',
                    'firstName' => 'string|null',
                    'lastName'  => 'string|null',
                    'avatar'    => 'array',
                ]);
                $I->dontSeeResponseContainsUserHiddenFields('*');

                foreach ($scenario['expected'] as $id) {
                    $I->seeResponseContainsJson(['id' => $id]);
                }

                // count match
                $I->dontSeeResponseJsonMatchesJsonPath('$.' . count($scenario['expected']));
            }
        }
    }

    /* `ProjectsController::actionLinkUser()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionLinkUser` failure test.
     *
     * @param FunctionalTester $I
     */
    public function linkUserFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully link user to project');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/projects/1001/users/1004');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to link a user to a project owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPOST('/projects/1001/users/1004');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to link a user to an unexisting project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPOST('/projects/123456/users/1004');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to link an unexisting user to an existing project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPOST('/projects/1005/users/123456');
        $I->seeNotFoundResponse();

        $I->dontSeeEmailIsSent();
    }

    /**
     * `ProjectsController::actionLinkUser()` success test.
     *
     * @param FunctionalTester $I
     */
    public function linkUserSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully link user to project');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(1003);

        $testScenarios = [
            [
                'comment'     => 'authorize as regular user and link already linked user to owned project',
                'token'       => $regularUser->generateAccessToken(),
                'projectId'   => 1002,
                'userId'      => 1003,
                'expectEmail' => false,
            ],
            [
                'comment'     => 'authorize as regular user and link another user to owned project',
                'token'       => $regularUser->generateAccessToken(),
                'projectId'   => 1002,
                'userId'      => 1005,
                'expectEmail' => true,
            ],
            [
                'comment'     => 'authorize as super user and link already linked user to a project',
                'token'       => $superUser->generateAccessToken(),
                'projectId'   => 1001,
                'userId'      => 1002,
                'expectEmail' => false,
            ],
            [
                'comment'     => 'authorize as super user and link another user to a project',
                'token'       => $superUser->generateAccessToken(),
                'projectId'   => 1001,
                'userId'      => 1003,
                'expectEmail' => true,
            ],
        ];

        $totalSentEmails = 0;
        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST("/projects/{$scenario['projectId']}/users/{$scenario['userId']}");
            $I->seeResponseCodeIs(204);

            if (!empty($scenario['expectEmail'])) {
                $totalSentEmails++;
            }
        }
        $I->seeEmailIsSent($totalSentEmails);
    }

    /* `ProjectsController::actionUnlinkUser()`
    --------------------------------------------------------------- */
    /**
     * `ProjectsController::actionUnlinkUser()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function unlinkUserFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully unlink a user from project');

        $regularUser = User::findOne(1004);
        $superUser   = User::findOne(1003);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/projects/1005/users/1003');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to unlink a user of a project owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/projects/1005/users/1003');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to unlink a user of an unexisting project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/projects/123456/users/1003');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to unlink an unexisting user of an existing project');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/projects/1005/users/123456');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as regular user and try to unlink the only one existing project user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/projects/1003/users/1004');
        $I->seeBadRequestResponse();
    }

    /**
     * `ProjectsController::actionUnlinkUser()` success test.
     *
     * @param FunctionalTester $I
     */
    public function unlinkUserSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully unlink a user from project');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(1003);

        $testScenarios = [
            [
                'comment'     => 'authorize as regular user and unlink unlinked user from owned project',
                'token'       => $regularUser->generateAccessToken(),
                'projectId'   => 1002,
                'userId'      => 1005,
                'expectEmail' => false,
            ],
            [
                'comment'     => 'authorize as regular user and unlink linked user from owned project',
                'token'       => $regularUser->generateAccessToken(),
                'projectId'   => 1002,
                'userId'      => 1003,
                'expectEmail' => true,
            ],
            [
                'comment'     => 'authorize as super user and unlink unlinked user from a project',
                'token'       => $superUser->generateAccessToken(),
                'projectId'   => 1004,
                'userId'      => 1005,
                'expectEmail' => false,
            ],
            [
                'comment'     => 'authorize as super user and unlink linked user from a project',
                'token'       => $superUser->generateAccessToken(),
                'projectId'   => 1004,
                'userId'      => 1004,
                'expectEmail' => true,
            ],
        ];

        $totalSentEmails = 0;
        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE("/projects/{$scenario['projectId']}/users/{$scenario['userId']}");
            $I->seeResponseCodeIs(204);

            if (!empty($scenario['expectEmail'])) {
                $totalSentEmails++;
            }
        }
        $I->seeEmailIsSent($totalSentEmails);
    }
}
