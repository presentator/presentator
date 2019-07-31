<?php
namespace presentator\api\tests\functional;

use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\GuidelineSectionFixture;
use presentator\api\tests\fixtures\UserProjectRelFixture;
use presentator\api\models\User;
use presentator\api\models\GuidelineSection;

/**
 * GuidelineSectionsController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineSectionsCest
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
            'UserFixture' => [
                'class' => UserFixture::class,
            ],
            'UserProjectRelFixture' => [
                'class' => UserProjectRelFixture::class,
            ],
        ]);
    }

    /* `GuidelineSectionsController::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineSectionsController::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list guideline sections');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/guideline-sections');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `GuidelineSectionsController::actionIndex()` success test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list guideline sections');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('authorize as regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/guideline-sections', [
            [
                'params'   => [],
                'expected' => [1002, 1003, 1001],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1003],
            ],
            [
                'params'   => ['search[projectId]' => 1001],
                'expected' => [1002, 1001],
            ],
            [
                'params'   => ['sort' => '-title'],
                'expected' => [1003, 1002, 1001],
            ],
        ]);

        $I->amGoingTo('authorize as super user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/guideline-sections', [
            [
                'params'   => [],
                'expected' => [1002, 1003, 1004, 1006, 1001, 1005],
            ],
            [
                'params'   => ['per-page' => 2, 'page' => 2],
                'expected' => [1004, 1006],
            ],
            [
                'params'   => ['search[projectId]' => 1003],
                'expected' => [1004, 1005],
            ],
            [
                'params'   => ['sort' => '-title'],
                'expected' => [1006, 1005, 1004, 1003, 1002, 1001],
            ],
        ]);
    }

    /* `GuidelineSectionsController::actionCreate()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineSectionsController::actionCreate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create new guideline section');

        $user = User::findOne(1002);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/guideline-sections');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize and submit invalid form data');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendPOST('/guideline-sections', [
            'projectId'   => 1005,
            'order'       => -1,
            'title'       => '',
            'description' => str_repeat('.', 256),
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'projectId'   => 'string',
                'order'       => 'string',
                'title'       => 'string',
                'description' => 'string',
            ],
        ]);
    }

    /**
     * `GuidelineSectionsController::actionCreate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create new guideline section');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and create a new guideline section for an owned project',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => [
                    'projectId'   => 1001,
                    'order'       => 1,
                    'title'       => 'test_title',
                    'description' => 'test_description',
                ],
            ],
            [
                'comment' => 'authorize as super user and create a new guideline section for a project',
                'token'   => $superUser->generateAccessToken(),
                'data'    => [
                    'projectId' => 1005,
                    'title'     => 'test_title_2',
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST('/guideline-sections', $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'projectId'   => 'integer',
                'order'       => 'integer',
                'title'       => 'string',
                'description' => 'string',
            ]);
            $I->seeResponseContainsJson($scenario['data']);
        }
    }

    /* `GuidelineSectionsController::actionUpdate()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineSectionsController::actionUpdate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update guideline section');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/guideline-sections/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to update guideline section owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/guideline-sections/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and submit invalid form data to a random guideline section');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/guideline-sections/1006', [
            'projectId'   => 123456,
            'order'       => -1,
            'title'       => '',
            'description' => str_repeat('.', 256),
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'projectId'   => 'string',
                'order'       => 'string',
                'title'       => 'string',
                'description' => 'string',
            ],
        ]);
    }

    /**
     * `GuidelineSectionsController::actionUpdate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update guideline section');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and update owned guideline section',
                'token'     => $regularUser->generateAccessToken(),
                'sectionId' => 1001,
                'data'      => [
                    'projectId'   => 1002,
                    'order'       => 1,
                    'title'       => 'update_test_title',
                    'description' => 'update_test_description',
                ],
            ],
            [
                'comment'   => 'authorize as super user and update a guideline section',
                'token'     => $superUser->generateAccessToken(),
                'sectionId' => 1006,
                'data'      => [
                    'projectId' => 1002,
                    'title'     => 'update_test_title2',
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/guideline-sections/' . $scenario['sectionId'], $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'          => ('integer:=' . $scenario['sectionId']),
                'projectId'   => 'integer',
                'title'       => 'string',
                'description' => 'string',
                'order'       => 'integer',
            ]);
            $I->seeResponseContainsJson($scenario['data']);
        }
    }

    /* `GuidelineSectionsController::actionView()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineSectionsController::actionView()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function viewFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully view guideline section');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/guideline-sections/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to view guideline section owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/guideline-sections/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to view unexisting guideline section');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/guideline-sections/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `GuidelineSectionsController::actionView()` success test.
     *
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully view guideline section');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and try to view owned guideline section',
                'token'     => $regularUser->generateAccessToken(),
                'sectionId' => 1001,
            ],
            [
                'comment'   => 'authorize as super user and try to view a guideline section',
                'token'     => $superUser->generateAccessToken(),
                'sectionId' => 1004,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET('/guideline-sections/' . $scenario['sectionId'], ['expand' => 'assets']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id'     => 'integer:=' . $scenario['sectionId'],
                'assets' => 'array',
            ]);
        }
    }

    /* `GuidelineSectionsController::actionDelete()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineSectionsController::actionDelete()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function deleteFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully delete guideline section');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/guideline-sections/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to delete guideline section owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/guideline-sections/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to delete unexisting guideline section');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/guideline-sections/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `GuidelineSectionsController::actionDelete()` success test.
     *
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete guideline section');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and try to delete an owned guideline section',
                'token'     => $regularUser->generateAccessToken(),
                'sectionId' => 1002,
            ],
            [
                'comment'   => 'authorize as super user and try to delete a guideline section',
                'token'     => $superUser->generateAccessToken(),
                'sectionId' => 1005,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE('/guideline-sections/' . $scenario['sectionId']);
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(GuidelineSection::class, ['id' => $scenario['sectionId']]);
        }
    }
}
