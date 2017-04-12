<?php
namespace app\tests\functional;

use Yii;
use app\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\ScreenCommentFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\Screen;

/**
 * ScreensController functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensCest
{
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
            'comment' => [
                'class'    => ScreenCommentFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/screen_comment.php'),
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
        ]);
    }

    /* ===============================================================
     * `ScreensController::actionAjaxUpload()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxUploadFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely upload new screen(s)');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-upload']);

        $I->amGoingTo('try to upload screens to a version not owned by the logged user');
        $I->sendPOST(
            ['screens/ajax-upload'],
            ['versionId' => 1004],
            [
                // mockup $_FILES
                'ScreensUploadForm' => [
                    'images' => [
                        'name'     => 'test_image.jpg',
                        'type'     => 'image/jpeg',
                        'error'    => UPLOAD_ERR_OK,
                        'size'     => filesize(Yii::getAlias('@common/tests/_data/test_image.jpg')),
                        'tmp_name' => Yii::getAlias('@common/tests/_data/test_image.jpg'),
                    ],
                ]
            ],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try to upload image(s) with extension that is not alowed');
        $I->sendPOST(
            ['screens/ajax-upload'],
            ['versionId' => 1002],
            [
                // mockup $_FILES
                'ScreensUploadForm' => [
                    'images' => [
                        'name'     => 'test_image.gif',
                        'type'     => 'image/jpeg', // try to set incorrect mimetype
                        'error'    => UPLOAD_ERR_OK,
                        'size'     => filesize(Yii::getAlias('@common/tests/_data/test_image.gif')),
                        'tmp_name' => Yii::getAlias('@common/tests/_data/test_image.gif'),
                    ],
                ]
            ],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxUploadSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully upload new screen(s)');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-upload']);
        $I->sendPOST(
            ['screens/ajax-upload'],
            ['versionId' => 1002],
            [
                // mockup $_FILES
                'ScreensUploadForm' => [
                    'images' => [
                        [
                            'name'     => 'test_image.jpg',
                            'type'     => 'image/jpeg',
                            'error'    => UPLOAD_ERR_OK,
                            'size'     => filesize(Yii::getAlias('@common/tests/_data/test_image.jpg')),
                            'tmp_name' => Yii::getAlias('@common/tests/_data/test_image.jpg'),

                        ],
                        [
                            'name'     => 'test_image.png',
                            'type'     => 'image/jpeg',
                            'error'    => UPLOAD_ERR_OK,
                            'size'     => filesize(Yii::getAlias('@common/tests/_data/test_image.png')),
                            'tmp_name' => Yii::getAlias('@common/tests/_data/test_image.png'),
                        ]
                    ],
                ]
            ],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"listItemsHtml":');
    }

    /* ===============================================================
     * `ScreensController::actionAjaxReorder()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxReorderFail(FunctionalTester $I)
    {
        $screen = Screen::findOne(1004);
        $oldOrder = $screen->order;

        $I->wantTo('Falsely reorder screen model');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-reorder']);
        $I->amGoingTo('try to reorder screen not owned by the logged user');
        $I->sendAjaxPostRequest(['screens/ajax-reorder'], [
            'id'       => $screen->id,
            'position' => 3,
        ]);
        $screen->refresh();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->assertEquals($screen->order, $oldOrder);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxReorderSuccess(FunctionalTester $I)
    {

        $I->wantTo('Successfully reorder screen model');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-reorder']);
        $I->amGoingTo('try to reorder screen not owned by the logged user');
        $I->sendAjaxPostRequest(['screens/ajax-reorder'], [
            'id'       => 1001,
            'position' => 2,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $screen1 = Screen::findOne(1001);
        $screen2 = Screen::findOne(1002);
        $I->assertEquals($screen1->order, 2);
        $I->assertEquals($screen2->order, 1);
    }

    /* ===============================================================
     * `ScreensController::actionAjaxDelete()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxDeleteFail(FunctionalTester $I)
    {
        $oldCount = Screen::find()->count();

        $I->wantTo('Falsely delete a screen model');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-delete']);

        $I->amGoingTo('try to delete a nonexisting screen model');
        $I->sendAjaxPostRequest(['screens/ajax-delete'], ['id' => 12345]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(Screen::className(), $oldCount);

        $I->amGoingTo('try to delete a screen that is not owned by the logged user');
        $I->sendAjaxPostRequest(['screens/ajax-delete'], ['id' => 1004]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecordsCountChange(Screen::className(), $oldCount);
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxDeleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete a screen model');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-delete']);
        $I->sendAjaxPostRequest(['screens/ajax-delete'], ['id' => [1001, 1002]]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->dontSeeRecord(Screen::className(), ['id' => 1001]);
        $I->dontSeeRecord(Screen::className(), ['id' => 1002]);
    }

    /* ===============================================================
     * `ProjectsController::actionAjaxGetSettingsForm()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetSettingsFormFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely renders a screen settings form');
        $I->amLoggedInAs(1002);
        $I->cantAccessAsGuest(['screens/ajax-get-settings-form', 'id' => 1004]);
        $I->sendAjaxGetRequest(['screens/ajax-get-settings-form'], ['id' => 1004]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxGetSettingsFormSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully renders a screen settings form');
        $I->amLoggedInAs(1002);
        $I->cantAccessAsGuest(['screens/ajax-get-settings-form', 'id' => 1001]);
        $I->sendAjaxGetRequest(['screens/ajax-get-settings-form'], ['id' => 1001]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"formHtml":');
    }

    /* ===============================================================
     * `ScreensController::actionAjaxSaveSettingsForm()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxSaveSettingsFormFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely submit screen settings form');
        $I->amLoggedInAs(1002);
        $I->cantAccessAsGuest(['screens/ajax-save-settings-form', 'id' => 1001]);

        $I->amGoingTo('try to update a screen that is not owned by the logged user');
        $I->sendAjaxPostRequest(['screens/ajax-save-settings-form', 'id' => 1004], [
            'ScreenSettingsForm' => [
                'title'      => 'Test title',
                'alignment'  => Screen::ALIGNMENT_LEFT,
                'background' => '#000000',
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try to update a screen with wrong settings form data');
        $I->sendAjaxPostRequest(['screens/ajax-save-settings-form', 'id' => 1001], [
            'ScreenSettingsForm' => [
                'title'      => '',
                'alignment'  => '',
                'background' => '#qweasd',
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxSaveSettingsFormSuccess(FunctionalTester $I)
    {
        $screen = Screen::findOne(1001);

        $I->wantTo('Successfully submit screen settings form');
        $I->amLoggedInAs(1002);
        $I->cantAccessAsGuest(['screens/ajax-save-settings-form', 'id' => $screen->id]);
        $I->sendAjaxPostRequest(['screens/ajax-save-settings-form', 'id' => $screen->id], [
            'ScreenSettingsForm' => [
                'title'      => 'New title',
                'alignment'  => Screen::ALIGNMENT_LEFT,
                'background' => '#000000',
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"settings":');
        $I->seeResponseContains('"message":');
        $screen->refresh();
        $I->assertEquals($screen->title, 'New title');
        $I->assertEquals($screen->alignment, Screen::ALIGNMENT_LEFT);
        $I->assertEquals($screen->background, '#000000');
    }

    /* ===============================================================
     * `ScreensController::actionAjaxSaveHotspots()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxSaveHotspotsFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely save hotspots data');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-save-hotspots']);
        $I->amGoingTo('try to update screen that is not owned by the logged user');
        $I->sendAjaxPostRequest(['screens/ajax-save-hotspots'], [
            'id' => 1004,
            'hotspots' => [],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxSaveHotspotsSuccess(FunctionalTester $I)
    {
        $screen = Screen::findOne(1001);

        $I->wantTo('Successfully save hotspots data');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-save-hotspots']);

        $I->amGoingTo('set new screen hotspots');
        $I->sendAjaxPostRequest(['screens/ajax-save-hotspots'], [
            'id'       => $screen->id,
            'hotspots' => [
                'hotspot_1' => ['left' => 0, 'top' => 0, 'width' => 10, 'height' => 10, 'link' => '#'],
                'hotspot_2' => ['left' => 0, 'top' => 0, 'width' => 10, 'height' => 10, 'link' => '#'],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $screen->refresh();
        $I->assertNotEmpty($screen->hotspots);

        $I->amGoingTo('clear all screen hotspots');
        $I->sendAjaxPostRequest(['screens/ajax-save-hotspots'], [
            'id'       => $screen->id,
            'hotspots' => null,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $screen->refresh();
        $I->assertEmpty($screen->hotspots);
    }

    /* ===============================================================
     * `ScreensController::actionAjaxMoveScreens()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxMoveScreensFail(FunctionalTester $I)
    {
        $I->wantTo('Falsely move screens from one version to another');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-move-screens']);

        $I->amGoingTo('try to move screens to a version that is not from the same screens project or is not owned by the logged user');
        $I->sendAjaxPostRequest(['screens/ajax-move-screens'], [
            'screenIds' => [1001, 1002],
            'versionId' => 1004,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');

        $I->amGoingTo('try to move screens not owned by the logged user');
        $I->sendAjaxPostRequest(['screens/ajax-move-screens'], [
            'screenIds' => 1003,
            'versionId' => 1001,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"message":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxMoveScreensSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully move screens from one version to another');
        $I->amLoggedInAs(1002);
        $I->ensureAjaxPostActionAccess(['screens/ajax-move-screens']);

        $I->amGoingTo('try to move screens to a version that is not from the same screens project or is not owned by the logged user');
        $I->sendAjaxPostRequest(['screens/ajax-move-screens'], [
            'screenIds' => [1001, 1002],
            'versionId' => 1002,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');

        $screens = Screen::findAll(['id' => [1001, 1002]]);
        foreach ($screens as $screen) {
            $I->assertEquals($screen->versionId, 1002);
        }
    }
}
