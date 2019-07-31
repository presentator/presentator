<?php
namespace presentator\api\tests\functional;

use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\PrototypeFixture;
use presentator\api\tests\fixtures\ScreenFixture;
use presentator\api\tests\fixtures\HotspotTemplateFixture;
use presentator\api\tests\fixtures\HotspotTemplateScreenRelFixture;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\UserProjectRelFixture;
use presentator\api\models\User;
use presentator\api\models\HotspotTemplate;
use presentator\api\models\HotspotTemplateScreenRel;

/**
 * HotspotTemplatesController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotTemplatesCest
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
        ]);
    }

    /* `HotspotTemplatesController::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `HotspotTemplatesController::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list hotspot templates');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/hotspot-templates');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `HotspotTemplatesController::actionIndex()` success test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list hotspot templates');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('authorize as regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/hotspot-templates', [
            [
                'params'   => [],
                'expected' => [1001, 1002, 1003, 1004],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1002],
            ],
            [
                'params'   => ['search[screenId]' => 1001],
                'expected' => [1001],
            ],
            [
                'params'   => ['search[prototypeId]' => 1006],
                'expected' => [],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1004, 1003, 1002, 1001],
            ],
        ]);

        $I->amGoingTo('authorize as super user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/hotspot-templates', [
            [
                'params'   => [],
                'expected' => [1001, 1002, 1003, 1004, 1005, 1006],
            ],
            [
                'params'   => ['per-page' => 2, 'page' => 2],
                'expected' => [1003, 1004],
            ],
            [
                'params'   => ['search[prototypeId]' => 1004],
                'expected' => [1005, 1006],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1006, 1005, 1004, 1003, 1002, 1001],
            ],
        ]);
    }

    /* `HotspotTemplatesController::actionCreate()`
    --------------------------------------------------------------- */
    /**
     * `HotspotTemplatesController::actionCreate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create new hotspot template');

        $user = User::findOne(1002);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/hotspot-templates');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize and submit invalid form data');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendPOST('/hotspot-templates', [
            'prototypeId' => 1005,
            'title'       => '',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'prototypeId' => 'string',
                'title'       => 'string',
            ],
        ]);
    }

    /**
     * `HotspotTemplatesController::actionCreate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create new hotspot template');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and create a new hotspot template for an owned prototype',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => [
                    'prototypeId' => 1001,
                    'title'       => 'test_title',
                ],
            ],
            [
                'comment' => 'authorize as super user and create a new hotspot template for a ptototype',
                'token'   => $superUser->generateAccessToken(),
                'data'    => [
                    'prototypeId' => 1005,
                    'title'       => 'test_title_2',
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST('/hotspot-templates', $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'prototypeId' => 'integer',
                'title'       => 'string',
            ]);
            $I->seeResponseContainsJson($scenario['data']);
        }
    }

    /* `HotspotTemplatesController::actionUpdate()`
    --------------------------------------------------------------- */
    /**
     * `HotspotTemplatesController::actionUpdate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update hotspot template');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/hotspot-templates/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to update hotspot template owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/hotspot-templates/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and submit invalid form data to a random hotspot template');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/hotspot-templates/1006', [
            'prototypeId' => 123456, // should be ignored
            'title'       => '',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'title' => 'string',
            ],
        ]);
        $I->dontSeeResponseJsonMatchesJsonPath('$.errors.prototypeId');
    }

    /**
     * `HotspotTemplatesController::actionUpdate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update hotspot template');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'    => 'authorize as regular user and update owned hotspot template',
                'token'      => $regularUser->generateAccessToken(),
                'templateId' => 1001,
                'data'       => [
                    'prototypeId' => 1002, // should be ignored
                    'title'       => 'update_test_title',
                ],
            ],
            [
                'comment'    => 'authorize as super user and update a hotspot template',
                'token'      => $superUser->generateAccessToken(),
                'templateId' => 1006,
                'data'       => [
                    'prototypeId' => 1002, // should be ignored
                    'title'       => 'update_test_title2',
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $template = HotspotTemplate::findOne($scenario['templateId']);

            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/hotspot-templates/' . $template->id, $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'          => ('integer:=' . $template->id),
                'prototypeId' => ('integer:=' . $template->prototypeId),
                'title'       => 'string',
            ]);
            $I->seeResponseContainsJson(['title' => $scenario['data']['title']]);
        }
    }

    /* `HotspotTemplatesController::actionView()`
    --------------------------------------------------------------- */
    /**
     * `HotspotTemplatesController::actionView()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function viewFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully view hotspot template');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/hotspot-templates/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to view hotspot template owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/hotspot-templates/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to view unexisting hotspot template');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/hotspot-templates/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `HotspotTemplatesController::actionView()` success test.
     *
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully view hotspot template');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'    => 'authorize as regular user and try to view owned hotspot template',
                'token'      => $regularUser->generateAccessToken(),
                'templateId' => 1001,
            ],
            [
                'comment'    => 'authorize as super user and try to view a hotspot template',
                'token'      => $superUser->generateAccessToken(),
                'templateId' => 1005,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET('/hotspot-templates/' . $scenario['templateId'], ['expand' => 'screenIds']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id'        => ('integer:=' . $scenario['templateId']),
                'screenIds' => 'array',
            ]);
        }
    }

    /* `HotspotTemplatesController::actionDelete()`
    --------------------------------------------------------------- */
    /**
     * `HotspotTemplatesController::actionDelete()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function deleteFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully delete hotspot template');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/hotspot-templates/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to delete hotspot template owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/hotspot-templates/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to delete unexisting hotspot template');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/hotspot-templates/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `HotspotTemplatesController::actionDelete()` success test.
     *
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete hotspot template');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'    => 'authorize as regular user and try to delete an owned hotspot template',
                'token'      => $regularUser->generateAccessToken(),
                'templateId' => 1002,
            ],
            [
                'comment'    => 'authorize as super user and try to delete a hotspot template',
                'token'      => $superUser->generateAccessToken(),
                'templateId' => 1005,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE('/hotspot-templates/' . $scenario['templateId']);
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(HotspotTemplate::class, ['id' => $scenario['templateId']]);
        }
    }

    /* `HotspotTemplatesController::actionListScreens()`
    --------------------------------------------------------------- */
    /**
     * `HotspotTemplatesController::actionListScreens` test.
     *
     * @param FunctionalTester $I
     */
    public function listScreensFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list template screens');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/hotspot-templates/1006/screens');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to list the screens of a template owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/hotspot-templates/1006/screens');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to list the screens of an unexisting template');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/hotspot-templates/123456/screens');
        $I->seeNotFoundResponse();
    }

    /**
     * `HotspotTemplatesController::actionListScreens()` success test.
     *
     * @param FunctionalTester $I
     */
    public function listScreensSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list template screens');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'    => 'authorize as regular user and list screens of an owned template',
                'token'      => $regularUser->generateAccessToken(),
                'templateId' => 1002,
                'expected'   => [1003],
            ],
            [
                'comment'    => 'authorize as super user and list screens of a random template',
                'token'      => $superUser->generateAccessToken(),
                'templateId' => 1004,
                'expected'   => [1005, 1006],
            ],
            [
                'comment'    => 'authorize as super user and list screens of a template without any screens',
                'token'      => $superUser->generateAccessToken(),
                'templateId' => 1005,
                'expected'   => [],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET("/hotspot-templates/{$scenario['templateId']}/screens");
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();

            if (empty($scenario['expected'])) {
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.id');
            } else {
                $I->seeResponseMatchesJsonType([
                    'id'   => 'integer',
                    'file' => 'array',
                ]);
                $I->dontSeeResponseJsonMatchesJsonPath('$.*.filePath');

                foreach ($scenario['expected'] as $id) {
                    $I->seeResponseContainsJson(['id' => $id]);
                }

                // count match
                $I->dontSeeResponseJsonMatchesJsonPath('$.' . count($scenario['expected']));
            }
        }
    }

    /* `HotspotTemplatesController::actionLinkScreen()`
    --------------------------------------------------------------- */
    /**
     * `HotspotTemplatesController::actionLinkScreen` failure test.
     *
     * @param FunctionalTester $I
     */
    public function linkScreenFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully link screen to template');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/hotspot-templates/1006/screens/1008');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to link a screen to a template owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPOST('/hotspot-templates/1006/screens/1008');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to link a screen to an unexisting template');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPOST('/hotspot-templates/123456/screens/1008');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to link an unexisting screen to an existing template');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPOST('/hotspot-templates/1005/screens/123456');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to link mismatched screen and template prototype');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPOST('/hotspot-templates/1005/screens/1008');
        $I->seeNotFoundResponse();
    }

    /**
     * `HotspotTemplatesController::actionLinkScreen()` success test.
     *
     * @param FunctionalTester $I
     */
    public function linkScreenSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully link screen to template');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'    => 'authorize as regular user and link already linked screen to owned template',
                'token'      => $regularUser->generateAccessToken(),
                'templateId' => 1001,
                'screenId'   => 1001,
            ],
            [
                'comment'    => 'authorize as regular user and link a screen to owned template',
                'token'      => $regularUser->generateAccessToken(),
                'templateId' => 1001,
                'screenId'   => 1003,
            ],
            [
                'comment'    => 'authorize as regular user and link already linked screen to a template',
                'token'      => $superUser->generateAccessToken(),
                'templateId' => 1001,
                'screenId'   => 1002,
            ],
            [
                'comment'    => 'authorize as regular user and link a screen to a template',
                'token'      => $superUser->generateAccessToken(),
                'templateId' => 1005,
                'screenId'   => 1007,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST("/hotspot-templates/{$scenario['templateId']}/screens/{$scenario['screenId']}");
            $I->seeResponseCodeIs(204);
            $I->seeRecord(HotspotTemplateScreenRel::class, [
                'hotspotTemplateId' => $scenario['templateId'],
                'screenId'          => $scenario['screenId'],
            ]);
        }
    }

    /* `HotspotTemplatesController::actionUnlinkScreen()`
    --------------------------------------------------------------- */
    /**
     * `HotspotTemplatesController::actionUnlinkScreen` failure test.
     *
     * @param FunctionalTester $I
     */
    public function unlinkScreenFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully unlink screen from template');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/hotspot-templates/1006/screens/1008');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to unlink a screen from a template owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/hotspot-templates/1006/screens/1008');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to unlink a screen from an unexisting template');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/hotspot-templates/123456/screens/1008');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to unlink an unexisting screen from an existing template');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/hotspot-templates/1005/screens/123456');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to unlink mismatched screen and template prototype');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/hotspot-templates/1005/screens/1008');
        $I->seeNotFoundResponse();
    }

    /**
     * `HotspotTemplatesController::actionUnlinkScreen()` success test.
     *
     * @param FunctionalTester $I
     */
    public function unlinkScreenSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully unlink screen from template');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'    => 'authorize as regular user and unlink already unlinked screen from owned template',
                'token'      => $regularUser->generateAccessToken(),
                'templateId' => 1001,
                'screenId'   => 1003,
            ],
            [
                'comment'    => 'authorize as regular user and unlink a screen from owned template',
                'token'      => $regularUser->generateAccessToken(),
                'templateId' => 1001,
                'screenId'   => 1001,
            ],
            [
                'comment'    => 'authorize as regular user and unlink already unlinked screen from a template',
                'token'      => $superUser->generateAccessToken(),
                'templateId' => 1001,
                'screenId'   => 1003,
            ],
            [
                'comment'    => 'authorize as regular user and unlink a screen from a template',
                'token'      => $superUser->generateAccessToken(),
                'templateId' => 1002,
                'screenId'   => 1003,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE("/hotspot-templates/{$scenario['templateId']}/screens/{$scenario['screenId']}");
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(HotspotTemplateScreenRel::class, [
                'hotspotTemplateId' => $scenario['templateId'],
                'screenId'          => $scenario['screenId'],
            ]);
        }
    }
}
