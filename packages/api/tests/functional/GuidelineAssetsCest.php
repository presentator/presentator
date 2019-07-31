<?php
namespace presentator\api\tests\functional;

use Yii;
use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\GuidelineSectionFixture;
use presentator\api\tests\fixtures\GuidelineAssetFixture;
use presentator\api\tests\fixtures\UserProjectRelFixture;
use presentator\api\models\User;
use presentator\api\models\GuidelineAsset;

/**
 * GuidelineAssetsController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineAssetsCest
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
            'UserFixture' => [
                'class' => UserFixture::class,
            ],
            'UserProjectRelFixture' => [
                'class' => UserProjectRelFixture::class,
            ],
        ]);
    }

    /* `GuidelineAssetsController::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineAssetsController::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list guideline assets');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/guideline-assets');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `GuidelineAssetsController::actionIndex()` success test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list guideline assets');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('authorize as regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/guideline-assets', [
            [
                'params'   => [],
                'expected' => [1002, 1003, 1004, 1001],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1003],
            ],
            [
                'params'   => ['search[guidelineSectionId]' => 1001],
                'expected' => [1002, 1001],
            ],
            [
                'params'   => ['search[guidelineSectionId]' => 1004],
                'expected' => [],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1004, 1003, 1002, 1001],
            ],
        ]);

        $I->amGoingTo('authorize as super user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/guideline-assets', [
            [
                'params'   => [],
                'expected' => [1002, 1003, 1004, 1005, 1001, 1006],
            ],
            [
                'params'   => ['per-page' => 2, 'page' => 2],
                'expected' => [1004, 1005],
            ],
            [
                'params'   => ['search[guidelineSectionId]' => 1001],
                'expected' => [1002, 1001],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1006, 1005, 1004, 1003, 1002, 1001],
            ],
        ]);
    }

    /* `GuidelineAssetsController::actionCreate()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineAssetsController::actionCreate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create new guideline asset');

        $user = User::findOne(1002);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/guideline-assets');
        $I->seeUnauthorizedResponse();

        $testScenarios = [
            [
                'comment' => 'authorize and submit invalid color asset form data',
                'token'   => $user->generateAccessToken(),
                'data'    => [
                    'guidelineSectionId' => 1005,
                    'type'               => 'invalid', // should fallback to color asset scenario
                    'order'              => -10,
                    'hex'                => '#111',
                ],
                'errors' => ['guidelineSectionId', 'type', 'order', 'hex'],
            ],
            [
                'comment' => 'authorize and submit invalid file asset form data',
                'token'   => $user->generateAccessToken(),
                'data'    => [
                    'type'               => 'file', // switch to file asset scenario
                    'guidelineSectionId' => 1005,
                    'order'              => -10,
                    'title'              => str_repeat('.', 256),
                    'file'               => null,
                ],
                'errors' => ['guidelineSectionId', 'order', 'title', 'file'],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $fileData = [];
            if (isset($scenario['data']['file'])) {
                $fileData = ['file' => $scenario['data']['file']];
            }

            $postData = $scenario['data'];
            unset($postData['file']);

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST('/guideline-assets', $postData, $fileData);
            $I->seeResponseCodeIs(400);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'message' => 'string',
                'errors'  => array_fill_keys($scenario['errors'], 'string'),
            ]);

            $nonErrorFields = array_diff(array_keys($scenario['data']), $scenario['errors']);
            foreach ($nonErrorFields as $field) {
                $I->dontSeeResponseJsonMatchesJsonPath('$.errors.' . $field);
            }
        }
    }

    /**
     * `GuidelineAssetsController::actionCreate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create new guideline asset');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $maxUploadSize = Yii::$app->params['maxGuidelineAssetUploadSize'];
        Yii::$app->params['maxGuidelineAssetUploadSize'] = 10;

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and create a new file asset for an owned guideline section',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => [
                    'guidelineSectionId' => 1001,
                    'type'               => 'file',
                    'title'              => 'create_test',
                    'file'               => Yii::getAlias('@app/tests/_data/test_image.png'),
                ],
            ],
            [
                'comment' => 'authorize as regular user and create a new color asset for an owned guideline section',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => [
                    'guidelineSectionId' => 1001,
                    'type'               => 'color',
                    'order'              => 1,
                    'hex'                => '#111111',
                ],
            ],
            [
                'comment' => 'authorize as super user and create a new file asset for a guideline section',
                'token'   => $superUser->generateAccessToken(),
                'data'    => [
                    'guidelineSectionId' => 1005,
                    'type'               => 'file',
                    'file'               => Yii::getAlias('@app/tests/_data/test_file.pdf'),
                ],
            ],
            [
                'comment' => 'authorize as super user and create a new color asset for a guideline section',
                'token'   => $superUser->generateAccessToken(),
                'data'    => [
                    'guidelineSectionId' => 1005,
                    'type'               => 'color',
                    'hex'                => '#111111',
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $fileData = [];
            if (isset($scenario['data']['file'])) {
                $fileData = ['file' => $scenario['data']['file']];
            }

            $postData = $scenario['data'];
            unset($postData['file']);

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST('/guideline-assets', $postData, $fileData);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'                 => 'integer',
                'guidelineSectionId' => 'integer',
                'type'               => 'string',
                'hex'                => 'string',
                'file'               => empty($fileData) ? 'null' : 'array',
            ]);
            $I->seeResponseContainsJson($postData);
        }

        // revert changes
        Yii::$app->params['maxGuidelineAssetUploadSize'] = $maxUploadSize;
    }

    /* `GuidelineAssetsController::actionUpdate()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineAssetsController::actionUpdate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update guideline asset');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/guideline-assets/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to update guideline asset owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/guideline-assets/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to update unexisting guideline asset');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/guideline-assets/123456');
        $I->seeNotFoundResponse();

        $testScenarios = [
            [
                'comment' => 'authorize and submit invalid color asset form data',
                'token'   => $regularUser->generateAccessToken(),
                'assetId' => 1001,
                'data'    => [
                    'type'               => 'invalid', // should be ignored
                    'guidelineSectionId' => 1005,
                    'order'              => -10,
                    'hex'                => '#111',
                ],
                'errors' => ['guidelineSectionId', 'order', 'hex'],
            ],
            [
                'comment' => 'authorize and submit invalid file asset form data',
                'token'   => $regularUser->generateAccessToken(),
                'assetId' => 1002,
                'data'    => [
                    'type'               => 'invalid', // should be ignored
                    'guidelineSectionId' => 1005,
                    'order'              => -10,
                    'title'              => '',
                    'file'               => null, // should be ignored
                ],
                'errors' => ['guidelineSectionId', 'order', 'title'],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $fileData = [];
            if (isset($scenario['data']['file'])) {
                $fileData = ['file' => $scenario['data']['file']];
            }

            $putData = $scenario['data'];
            unset($putData['file']);

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/guideline-assets/' . $scenario['assetId'], $putData, $fileData);
            $I->seeResponseCodeIs(400);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'message' => 'string',
                'errors'  => array_fill_keys($scenario['errors'], 'string'),
            ]);

            $nonErrorFields = array_diff(array_keys($scenario['data']), $scenario['errors']);
            foreach ($nonErrorFields as $field) {
                $I->dontSeeResponseJsonMatchesJsonPath('$.errors.' . $field);
            }
        }
    }

    /**
     * `GuidelineAssetsController::actionUpdate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update guideline asset');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and update file asset from an owned guideline section',
                'assetId' => 1002,
                'token'   => $regularUser->generateAccessToken(),
                'data'    => [
                    'guidelineSectionId' => 1002,
                    'order'              => 2,
                    'title'              => 'update_test',
                ],
            ],
            [
                'comment' => 'authorize as regular user and update color asset from an owned guideline section',
                'assetId' => 1001,
                'token'   => $regularUser->generateAccessToken(),
                'data'    => [
                    'guidelineSectionId' => 1001,
                    'order'              => 1,
                    'hex'                => '#111111',
                ],
            ],
            [
                'comment' => 'authorize as super user and update file asset from a guideline section',
                'assetId' => 1005,
                'token'   => $superUser->generateAccessToken(),
                'data'    => [
                    'title' => 'update_test2',
                ],
            ],
            [
                'comment' => 'authorize as super user and update color asset from a guideline section',
                'assetId' => 1004,
                'token'   => $superUser->generateAccessToken(),
                'data'    => [
                    'hex' => '#AAAAAA',
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $fileData = [];
            if (isset($scenario['data']['file'])) {
                $fileData = ['file' => $scenario['data']['file']];
            }

            $putData = $scenario['data'];
            unset($putData['file']);

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/guideline-assets/' . $scenario['assetId'], $putData, $fileData);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'                 => ('integer:=' . $scenario['assetId']),
                'guidelineSectionId' => 'integer',
                'type'               => 'string',
                'hex'                => 'string',
                'file'               => 'null|array',
            ]);
            $I->seeResponseContainsJson($putData);
        }
    }

    /* `GuidelineAssetsController::actionView()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineAssetsController::actionView()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function viewFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully view guideline asset');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/guideline-assets/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to view guideline asset owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/guideline-assets/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to view unexisting guideline asset');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/guideline-assets/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `GuidelineAssetsController::actionView()` success test.
     *
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully view guideline asset');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and try to view owned guideline asset',
                'token'   => $regularUser->generateAccessToken(),
                'assetId' => 1001,
            ],
            [
                'comment' => 'authorize as super user and try to view a guideline asset',
                'token'   => $superUser->generateAccessToken(),
                'assetId' => 1004,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET('/guideline-assets/' . $scenario['assetId'], ['expand' => 'assets']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id'   => 'integer:=' . $scenario['assetId'],
                'hex'  => 'string',
                'file' => 'null|array',
            ]);
        }
    }

    /* `GuidelineAssetsController::actionDelete()`
    --------------------------------------------------------------- */
    /**
     * `GuidelineAssetsController::actionDelete()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function deleteFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully delete guideline asset');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/guideline-assets/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to delete guideline asset owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/guideline-assets/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to delete unexisting guideline asset');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/guideline-assets/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `GuidelineAssetsController::actionDelete()` success test.
     *
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete guideline asset');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and try to delete an owned guideline asset',
                'token'   => $regularUser->generateAccessToken(),
                'assetId' => 1002,
            ],
            [
                'comment' => 'authorize as super user and try to delete a guideline asset',
                'token'   => $superUser->generateAccessToken(),
                'assetId' => 1005,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE('/guideline-assets/' . $scenario['assetId']);
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(GuidelineAsset::class, ['id' => $scenario['assetId']]);
        }
    }
}
