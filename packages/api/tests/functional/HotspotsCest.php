<?php
namespace presentator\api\tests\functional;

use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\PrototypeFixture;
use presentator\api\tests\fixtures\ScreenFixture;
use presentator\api\tests\fixtures\HotspotTemplateFixture;
use presentator\api\tests\fixtures\HotspotTemplateScreenRelFixture;
use presentator\api\tests\fixtures\HotspotFixture;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\UserProjectRelFixture;
use presentator\api\models\User;
use presentator\api\models\Hotspot;

/**
 * HotspotsController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotsCest
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
            'HotspotFixture' => [
                'class' => HotspotFixture::class,
            ],
            'UserFixture' => [
                'class' => UserFixture::class,
            ],
            'UserProjectRelFixture' => [
                'class' => UserProjectRelFixture::class,
            ],
        ]);
    }

    /* `HotspotsController::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `HotspotsController::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list hotspots');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/hotspots');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `HotspotsController::actionIndex()` success test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list hotspots');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('authorize as regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/hotspots', [
            [
                'params'   => [],
                'expected' => [1001, 1002, 1003],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1002],
            ],
            [
                'params'   => ['search[prototypeId]' => 1001],
                'expected' => [1001, 1002],
            ],
            [
                'params'   => ['search[prototypeId]' => 1005],
                'expected' => [],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1003, 1002, 1001],
            ],
        ]);

        $I->amGoingTo('authorize as super user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/hotspots', [
            [
                'params'   => [],
                'expected' => [1001, 1002, 1003, 1004, 1005],
            ],
            [
                'params'   => ['per-page' => 2, 'page' => 2],
                'expected' => [1003, 1004],
            ],
            [
                'params'   => ['search[hotspotTemplateId]' => 1006],
                'expected' => [1004, 1005],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1005, 1004, 1003, 1002, 1001],
            ],
        ]);
    }

    /* `HotspotsController::actionCreate()`
    --------------------------------------------------------------- */
    /**
     * `HotspotsController::actionCreate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create new hotspot');

        $user = User::findOne(1002);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/hotspots');
        $I->seeUnauthorizedResponse();

        $baseData = [
            'type'              => 'invalid',
            'hotspotTemplateId' => null,
            'screenId'          => null,
            'left'              => -1,
            'top'               => -1,
            'width'             => 4,
            'height'            => 4,
        ];

        $testScenarios = [
            [
                'comment' => 'authorize and submit invalid type hotspot form data',
                'token'   => $user->generateAccessToken(),
                'data'    => $baseData,
            ],
            [
                'comment' => 'authorize and submit invalid URL type hotspot form data',
                'token'   => $user->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'       => Hotspot::TYPE['URL'],
                    'settingUrl' => 'invalid',
                ]),
            ],
            [
                'comment' => 'authorize and submit invalid SCREEN type hotspot form data',
                'token'   => $user->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCREEN'],
                    'settingScreenId'   => 1007,
                    'settingTransition' => 'invalid',
                ]),
            ],
            [
                'comment' => 'authorize and submit invalid OVERLAY type hotspot form data',
                'token'   => $user->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'                   => Hotspot::TYPE['OVERLAY'],
                    'settingScreenId'        => 123456,
                    'settingTransition'      => 'invalid',
                    'settingOverlayPosition' => 'invalid',
                    'settingOffsetTop'       => 'invalid',
                    'settingOffsetBottom'    => 'invalid',
                    'settingOffsetLeft'      => 'invalid',
                    'settingOffsetRight'     => 'invalid',
                    'settingOutsideClose'    => -10,
                ]),
            ],
            [
                'comment' => 'authorize and submit invalid PREV type hotspot form data',
                'token'   => $user->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['PREV'],
                    'settingTransition' => 'invalid',
                ]),
            ],
            [
                'comment' => 'authorize and submit invalid NEXT type hotspot form data',
                'token'   => $user->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['NEXT'],
                    'settingTransition' => 'invalid',
                ]),
            ],
            [
                'comment' => 'authorize and submit invalid BACK type hotspot form data',
                'token'   => $user->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['BACK'],
                    'settingTransition' => 'invalid',
                ]),
            ],
            [
                'comment' => 'authorize and submit invalid SCROLL type hotspot form data',
                'token'   => $user->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCROLL'],
                    'settingScrollTop'  => -10,
                    'settingScrollLeft' => -10,
                ]),
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $errors = array_fill_keys(array_keys($scenario['data']), 'string');

            if (
                isset($scenario['data']['type']) &&
                in_array($scenario['data']['type'], Hotspot::TYPE)
            ) {
                unset($errors['type']);
            }

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST('/hotspots', $scenario['data']);
            $I->seeResponseCodeIs(400);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'message' => 'string',
                'errors'  => $errors,
            ]);
        }
    }

    /**
     * `HotspotsController::actionCreate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create new hotspot');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $baseData = [
            'hotspotTemplateId' => null,
            'screenId'          => 1001,
            'left'              => 0,
            'top'               => 0,
            'width'             => 5,
            'height'            => 5,
        ];

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and create URL type hotspot',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'       => Hotspot::TYPE['URL'],
                    'settingUrl' => 'https://presentator.io',
                ]),
            ],
            [
                'comment' => 'authorize as regular user and create SCREEN type hotspot',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCREEN'],
                    'settingScreenId'   => 1002,
                    'settingTransition' => Hotspot::TRANSITION['FADE'],
                ]),
            ],
            [
                'comment' => 'authorize as regular user and create OVERLAY type hotspot',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'                   => Hotspot::TYPE['OVERLAY'],
                    'settingScreenId'        => 1001,
                    'settingTransition'      => Hotspot::TRANSITION['FADE'],
                    'settingOverlayPosition' => Hotspot::OVERLAY_POSITION['CENTERED'],
                    'settingOffsetTop'       => 0,
                    'settingOffsetBottom'    => -100,
                    'settingOffsetLeft'      => 100,
                    'settingOffsetRight'     => -100,
                    'settingOutsideClose'    => true,
                ]),
            ],
            [
                'comment' => 'authorize as regular user and create PREV type hotspot',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['PREV'],
                    'settingTransition' => Hotspot::TRANSITION['NONE'],
                ]),
            ],
            [
                'comment' => 'authorize as regular user and create NEXT type hotspot',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['NEXT'],
                    'settingTransition' => Hotspot::TRANSITION['NONE'],
                ]),
            ],
            [
                'comment' => 'authorize as regular user and create BACK type hotspot',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['BACK'],
                    'settingTransition' => Hotspot::TRANSITION['NONE'],
                ]),
            ],
            [
                'comment' => 'authorize as regular user and create SCROLL type hotspot',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCROLL'],
                    'settingScrollTop'  => 0,
                    'settingScrollLeft' => 100,
                ]),
            ],
            [
                'comment' => 'authorize as super user and create SCROLL type hotspot to a hotspot template',
                'token'   => $superUser->generateAccessToken(),
                'data'    => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCROLL'],
                    'screenId'          => null,
                    'hotspotTemplateId' => 1006,
                    'settingScrollTop'  => 0,
                    'settingScrollLeft' => 0,
                ]),
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST('/hotspots', $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'                => 'integer',
                'screenId'          => 'integer|null',
                'hotspotTemplateId' => 'integer|null',
                'type'              => 'string',
                'left'              => 'integer|float',
                'top'               => 'integer|float',
                'width'             => 'integer|float',
                'height'            => 'integer|float',
                'settings'          => 'array',
            ]);
        }
    }

    /* `HotspotsController::actionUpdate()`
    --------------------------------------------------------------- */
    /**
     * `HotspotsController::actionUpdate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update hotspot');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/hotspots/1005');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to update hotspot owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/hotspots/1005');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to update unexisting hotspot');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/hotspots/123456');
        $I->seeNotFoundResponse();

        $baseData = [
            'type'              => 'invalid',
            'hotspotTemplateId' => -1,
            'screenId'          => -1,
            'left'              => -1,
            'top'               => -1,
            'width'             => 4,
            'height'            => 4,
        ];

        $testScenarios = [
            [
                'comment'   => 'authorize and submit invalid type hotspot form data',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => $baseData,
            ],
            [
                'comment'   => 'authorize and submit invalid URL type hotspot form data',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'       => Hotspot::TYPE['URL'],
                    'settingUrl' => 'invalid',
                ]),
            ],
            [
                'comment'   => 'authorize and submit invalid SCREEN type hotspot form data',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCREEN'],
                    'settingScreenId'   => 1007,
                    'settingTransition' => 'invalid',
                ]),
            ],
            [
                'comment'   => 'authorize and submit invalid OVERLAY type hotspot form data',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'                   => Hotspot::TYPE['OVERLAY'],
                    'settingScreenId'        => 123456,
                    'settingTransition'      => 'invalid',
                    'settingOverlayPosition' => 'invalid',
                    'settingOffsetTop'       => 'invalid',
                    'settingOffsetBottom'    => 'invalid',
                    'settingOffsetLeft'      => 'invalid',
                    'settingOffsetRight'     => 'invalid',
                    'settingOutsideClose'    => -10,
                ]),
            ],
            [
                'comment'   => 'authorize and submit invalid PREV type hotspot form data',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['PREV'],
                    'settingTransition' => 'invalid',
                ]),
            ],
            [
                'comment'   => 'authorize and submit invalid NEXT type hotspot form data',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['NEXT'],
                    'settingTransition' => 'invalid',
                ]),
            ],
            [
                'comment'   => 'authorize and submit invalid BACK type hotspot form data',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['BACK'],
                    'settingTransition' => 'invalid',
                ]),
            ],
            [
                'comment'   => 'authorize and submit invalid SCROLL type hotspot form data',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCROLL'],
                    'settingScrollTop'  => -10,
                    'settingScrollLeft' => -10,
                ]),
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $errors = array_fill_keys(array_keys($scenario['data']), 'string');

            if (
                isset($scenario['data']['type']) &&
                in_array($scenario['data']['type'], Hotspot::TYPE)
            ) {
                unset($errors['type']);
            }

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/hotspots/' . $scenario['hotspotId'], $scenario['data']);
            $I->seeResponseCodeIs(400);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'message' => 'string',
                'errors'  => $errors,
            ]);
        }
    }

    /**
     * `HotspotsController::actionUpdate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update hotspot');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $baseData = [
            'hotspotTemplateId' => null,
            'screenId'          => 1002,
            'left'              => 0,
            'top'               => 0,
            'width'             => 5,
            'height'            => 5,
        ];

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and update a hotspot to URL type',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'       => Hotspot::TYPE['URL'],
                    'settingUrl' => 'https://presentator.io',
                ]),
            ],
            [
                'comment'   => 'authorize as regular user and update a hotspot to SCREEN type',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCREEN'],
                    'settingScreenId'   => 1002,
                    'settingTransition' => Hotspot::TRANSITION['FADE'],
                ]),
            ],
            [
                'comment'   => 'authorize as regular user and update a hotspot to OVERLAY type',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'                   => Hotspot::TYPE['OVERLAY'],
                    'settingScreenId'        => 1001,
                    'settingTransition'      => Hotspot::TRANSITION['FADE'],
                    'settingOverlayPosition' => Hotspot::OVERLAY_POSITION['CENTERED'],
                    'settingOffsetTop'       => 0,
                    'settingOffsetBottom'    => -100,
                    'settingOffsetLeft'      => 100,
                    'settingOffsetRight'     => -100,
                    'settingOutsideClose'    => true,
                ]),
            ],
            [
                'comment'   => 'authorize as regular user and update a hotspot to PREV type',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['PREV'],
                    'settingTransition' => Hotspot::TRANSITION['NONE'],
                ]),
            ],
            [
                'comment'   => 'authorize as regular user and update a hotspot to NEXT type',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['NEXT'],
                    'settingTransition' => Hotspot::TRANSITION['NONE'],
                ]),
            ],
            [
                'comment'   => 'authorize as regular user and update a hotspot to BACK type',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['BACK'],
                    'settingTransition' => Hotspot::TRANSITION['NONE'],
                ]),
            ],
            [
                'comment'   => 'authorize as regular user and update a hotspot to SCROLL type',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCROLL'],
                    'settingScrollTop'  => 0,
                    'settingScrollLeft' => 100,
                ]),
            ],
            [
                'comment'   => 'authorize as super user and update a hotspot to SCROLL type',
                'token'     => $superUser->generateAccessToken(),
                'hotspotId' => 1003,
                'data'      => array_merge($baseData, [
                    'type'              => Hotspot::TYPE['SCROLL'],
                    'screenId'          => null,
                    'hotspotTemplateId' => 1006,
                    'settingScrollTop'  => 0,
                    'settingScrollLeft' => 0,
                ]),
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/hotspots/' . $scenario['hotspotId'], $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'                => ('integer:=' . $scenario['hotspotId']),
                'screenId'          => 'integer|null',
                'hotspotTemplateId' => 'integer|null',
                'type'              => 'string',
                'left'              => 'integer|float',
                'top'               => 'integer|float',
                'width'             => 'integer|float',
                'height'            => 'integer|float',
                'settings'          => 'array',
            ]);
        }
    }

    /* `HotspotsController::actionView()`
    --------------------------------------------------------------- */
    /**
     * `HotspotsController::actionView()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function viewFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully view hotspot');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/hotspots/1005');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to view hotspot owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/hotspots/1005');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to view unexisting hotspot');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/hotspots/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `HotspotsController::actionView()` success test.
     *
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully view hotspot');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and try to view owned hotspot',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
            ],
            [
                'comment'   => 'authorize as super user and try to view a hotspot',
                'token'     => $superUser->generateAccessToken(),
                'hotspotId' => 1005,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET('/hotspots/' . $scenario['hotspotId']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id'       => 'integer:=' . $scenario['hotspotId'],
                'settings' => 'array',
            ]);
        }
    }

    /* `HotspotsController::actionDelete()`
    --------------------------------------------------------------- */
    /**
     * `HotspotsController::actionDelete()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function deleteFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully delete hotspot');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/hotspots/1005');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to delete hotspot owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/hotspots/1005');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to delete unexisting hotspot');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/hotspots/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `HotspotsController::actionDelete()` success test.
     *
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete hotspot');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and try to delete an owned hotspot',
                'token'     => $regularUser->generateAccessToken(),
                'hotspotId' => 1001,
            ],
            [
                'comment'   => 'authorize as super user and try to delete a hotspot',
                'token'     => $superUser->generateAccessToken(),
                'hotspotId' => 1005,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE('/hotspots/' . $scenario['hotspotId']);
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(Hotspot::class, ['id' => $scenario['hotspotId']]);
        }
    }
}
