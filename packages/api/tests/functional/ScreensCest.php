<?php
namespace presentator\api\tests\functional;

use Yii;
use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\UserProjectRelFixtureture;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\PrototypeFixture;
use presentator\api\tests\fixtures\ScreenFixture;
use presentator\api\tests\fixtures\UserProjectRelFixture;
use presentator\api\models\User;
use presentator\api\models\Screen;

/**
 * ScreensController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensCest
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
            'UserFixture' => [
                'class' => UserFixture::class,
            ],
            'UserProjectRelFixture' => [
                'class' => UserProjectRelFixture::class,
            ],
        ]);
    }

    /* `ScreensController::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `ScreensController::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list screens');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/screens');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `ScreensController::actionIndex()` success test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list screens');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('authorize as regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/screens', [
            [
                'params'   => [],
                'expected' => [1003, 1004, 1005, 1001, 1006, 1002],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1004],
            ],
            [
                'params'   => ['search[prototypeId]' => 1001],
                'expected' => [1003, 1001, 1002],
            ],
            [
                'params'   => ['search[prototypeId]' => 1004],
                'expected' => [],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1006, 1005, 1004, 1003, 1002, 1001],
            ],
        ]);

        $I->amGoingTo('authorize as super user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/screens', [
            [
                'params'   => [],
                'expected' => [1003, 1004, 1005, 1007, 1008, 1001, 1006, 1002],
            ],
            [
                'params'   => ['per-page' => 2, 'page' => 2],
                'expected' => [1005, 1007],
            ],
            [
                'params'   => ['search[prototypeId]' => 1001],
                'expected' => [1003, 1001, 1002],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1008, 1007, 1006, 1005, 1004, 1003, 1002, 1001],
            ],
        ]);
    }

    /* `ScreensController::actionCreate()`
    --------------------------------------------------------------- */
    /**
     * `ScreensController::actionCreate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create new screen');

        $user = User::findOne(1002);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/screens');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize and submit invalid create form data');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendPOST('/screens', [
            'prototypeId' => 1006,
            'order'       => -10,
            'title'       => str_repeat('.', 256),
            'alignment'   => 'invalid',
            'background'  => '#000',
            'fixedHeader' => -10,
            'fixedFooter' => -10,
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'prototypeId' => 'string',
                'order'       => 'string',
                'title'       => 'string',
                'alignment'   => 'string',
                'background'  => 'string',
                'fixedHeader' => 'string',
                'fixedFooter' => 'string',
                'file'        => 'string',
            ],
        ]);
    }

    /**
     * `ScreensController::actionCreate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create new screen');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $maxUploadSize = Yii::$app->params['maxScreenUploadSize'];
        Yii::$app->params['maxScreenUploadSize'] = 10;

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and create a new screen to owned prototype',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => [
                    'prototypeId' => 1001,
                    'order'       => 1,
                    'title'       => 'test',
                    'alignment'   => 'right',
                    'background'  => '#ff0000',
                    'fixedHeader' => 100,
                    'fixedFooter' => 200,
                    'file'        => Yii::getAlias('@app/tests/_data/test_image.png'),
                ],
            ],
            [
                'comment' => 'authorize as super user and create a new screen with minimum settings to a prototype',
                'token'   => $superUser->generateAccessToken(),
                'data'    => [
                    'prototypeId' => 1006,
                    'file'        => Yii::getAlias('@app/tests/_data/test_image.jpg'),
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $files  = isset($scenario['data']['file']) ? ['file' => $scenario['data']['file']] : [];
            $params = $scenario['data'];
            unset($params['file']);

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST('/screens', $params, $files);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'          => 'integer',
                'prototypeId' => 'integer',
                'alignment'   => 'string',
                'background'  => 'string',
                'title'       => 'string',
                'file'        => 'array',
            ]);
            $I->seeResponseContainsJson($params);
        }

        // revert changes
        Yii::$app->params['maxScreenUploadSize'] = $maxUploadSize;
    }

    /* `ScreensController::actionUpdate()`
    --------------------------------------------------------------- */
    /**
     * `ScreensController::actionUpdate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update screen');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/screens/1008');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to update screen owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/screens/1008');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to update unexisting screen');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/screens/123456');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize and submit invalid update form data');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/screens/1001', [
            'prototypeId' => 1004,
            'order'       => -10,
            'title'       => '',
            'alignment'   => 'invalid',
            'background'  => '#000',
            'fixedHeader' => -10,
            'fixedFooter' => -10,
        ], [
            'file' => Yii::getAlias('@app/tests/_data/test_file.pdf'),
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'prototypeId' => 'string',
                'order'       => 'string',
                'title'       => 'string',
                'alignment'   => 'string',
                'background'  => 'string',
                'fixedHeader' => 'string',
                'fixedFooter' => 'string',
                'file'        => 'string',
            ],
        ]);
    }

    /**
     * `ScreensController::actionUpdate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update screen');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'  => 'authorize as regular user and update a screen from owned prototype',
                'token'    => $regularUser->generateAccessToken(),
                'screenId' => 1001,
                'data'     => [
                    'prototypeId' => 1001,
                    'order'       => 2,
                    'title'       => 'update_test',
                    'alignment'   => 'left',
                    'background'  => '#000fff',
                    'fixedHeader' => 50,
                    'fixedFooter' => 200,
                    'file'        => Yii::getAlias('@app/tests/_data/test_image.png'),
                ],
            ],
            [
                'comment' => 'authorize as super user and update a screen from a prototype',
                'token'   => $superUser->generateAccessToken(),
                'screenId' => 1008,
                'data'    => [
                    'prototypeId' => 1006,
                    'alignment'   => 'right',
                    'fixedHeader' => 500,
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $files  = isset($scenario['data']['file']) ? ['file' => $scenario['data']['file']] : [];
            $params = $scenario['data'];
            unset($params['file']);

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/screens/' . $scenario['screenId'], $params, $files);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'          => ('integer:=' . $scenario['screenId']),
                'prototypeId' => 'integer',
                'alignment'   => 'string',
                'background'  => 'string',
                'title'       => 'string',
                'file'        => 'array',
            ]);
            $I->seeResponseContainsJson($params);
        }
    }

    /* `ScreensController::actionView()`
    --------------------------------------------------------------- */
    /**
     * `ScreensController::actionView()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function viewFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully view screen');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/screens/1008');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to view screen owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/screens/1008');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to view unexisting screen');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/screens/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `ScreensController::actionView()` success test.
     *
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully view screen');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'  => 'authorize as regular user and try to view owned screen',
                'token'    => $regularUser->generateAccessToken(),
                'screenId' => 1001,
            ],
            [
                'comment'  => 'authorize as super user and try to view a screen',
                'token'    => $superUser->generateAccessToken(),
                'screenId' => 1008,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET('/screens/' . $scenario['screenId'], ['expand' => 'hotspots']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id'          => ('integer:=' . $scenario['screenId']),
                'prototypeId' => 'integer',
                'alignment'   => 'string',
                'background'  => 'string',
                'title'       => 'string',
                'file'        => 'array',
                'hotspots'    => 'array',
            ]);
        }
    }

    /* `ScreensController::actionDelete()`
    --------------------------------------------------------------- */
    /**
     * `ScreensController::actionDelete()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function deleteFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully delete screen');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/screens/1008');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to delete screen owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/screens/1008');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to delete unexisting screen');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/screens/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `ScreensController::actionDelete()` success test.
     *
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete screen');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'  => 'authorize as regular user and try to delete an owned screen',
                'token'    => $regularUser->generateAccessToken(),
                'screenId' => 1002,
            ],
            [
                'comment'  => 'authorize as super user and try to delete a screen',
                'token'    => $superUser->generateAccessToken(),
                'screenId' => 1005,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE('/screens/' . $scenario['screenId']);
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(Screen::class, ['id' => $scenario['screenId']]);
        }
    }
}
