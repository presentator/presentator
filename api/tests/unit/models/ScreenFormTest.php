<?php
namespace api\tests\models;

use Yii;
use yii\helpers\FileHelper;
use common\components\web\CUploadedFile;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\User;
use common\models\Screen;
use api\models\ScreenForm;

/**
 * ScreenForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenFormTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \api\tests\UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    public function _before()
    {
        $this->tester->haveFixtures([
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
    }

    /**
     * `ScreenForm::validateHex()` inline validator test.
     */
    public function testValidateHex()
    {
        $user = User::findOne(1002);

        $this->specify('INVALID HEX color code', function() use ($user) {
            $model = new ScreenForm($user, [
                'background' => 'invalid_hex_color',
            ]);
            $model->validateHex('background', []);

            verify('Error message should be set', $model->errors)->hasKey('background');
        });

        $this->specify('VALID HEX color code', function() use ($user) {
            $model = new ScreenForm($user, [
                'background' => '#000000',
            ]);
            $model->validateHex('background', []);

            verify('Error message should not be set', $model->errors)->hasntKey('background');
        });
    }

    /**
     * `ScreenForm::validateUserVersionId()` inline validator test.
     */
    public function testValidateUserVersionId()
    {
        $user = User::findOne(1002);

        $this->specify('INVALID version id', function() use ($user) {
            $model = new ScreenForm($user, [
                'versionId' => 1005,
            ]);
            $model->validateUserVersionId('versionId', []);

            verify('Error message should be set', $model->errors)->hasKey('versionId');
        });

        $this->specify('VALID version id', function() use ($user) {
            $model = new ScreenForm($user, [
                'versionId' => 1001,
            ]);
            $model->validateUserVersionId('versionId', []);

            verify('Error message should not be set', $model->errors)->hasntKey('versionId');
        });
    }

    /**
     * Tests whether `ScreenForm::save()` CREATES an existing Screen model.
     */
    public function testSaveCreate()
    {
        $user = User::findOne(1002);

        $this->specify('Error create attempt', function() use ($user) {
            $imagePath = Yii::getAlias('@common/tests/_data/test_image.gif'); // unsupported type
            $model = new ScreenForm($user, [
                'title'      => '',
                'versionId'  => 1004, // not owned by the user
                'alignment'  => 0,
                'background' => '#abc',
                'image'      => $this->tester->getUploadedFileInstance($imagePath),
            ]);
            $model->scenario = ScreenForm::SCENARIO_CREATE;

            $result = $model->save();

            verify('Model should not save', $result)->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('VersionId error message should be set', $model->errors)->hasKey('versionId');
            verify('Alignment error message should be set', $model->errors)->hasKey('alignment');
            verify('Background error message should be set', $model->errors)->hasKey('background');
            verify('Image error message should be set', $model->errors)->hasKey('image');
        });

        $this->specify('Success create attempt 1', function() use ($user) {
            $imagePath = Yii::getAlias('@common/tests/_data/test_image.png');

            $model = new ScreenForm($user, [
                'title'      => 'New screen title',
                'versionId'  => 1001,
                'alignment'  => Screen::ALIGNMENT_LEFT,
                'background' => '',
                'image'      => $this->tester->getUploadedFileInstance($imagePath),
            ]);
            $model->scenario = ScreenForm::SCENARIO_CREATE;

            $result = $model->save();

            verify('Model should save successfully', $result)->isInstanceOf(Screen::className());
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Screen title should match', $result->title)->equals('New screen title');
            verify('Screen version should match', $result->versionId)->equals(1001);
            verify('Screen alignment should match', $result->alignment)->equals(Screen::ALIGNMENT_LEFT);
            verify('Screen background should match', $result->background)->null();
            verify('Screen hotspots should match', $result->hotspots)->null();
        });

        $this->specify('Success create attempt 2 (with hotspots)', function() use ($user) {
            $hotspots  = json_encode(['test' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 1]]);
            $imagePath = Yii::getAlias('@common/tests/_data/test_image.png');

            $model = new ScreenForm($user, [
                'title'      => 'New screen title',
                'versionId'  => 1001,
                'alignment'  => Screen::ALIGNMENT_LEFT,
                'background' => '#fff000',
                'hotspots'   => $hotspots,
                'image'      => $this->tester->getUploadedFileInstance($imagePath),
            ]);
            $model->scenario = ScreenForm::SCENARIO_CREATE;

            $result = $model->save();

            verify('Model should save successfully', $result)->isInstanceOf(Screen::className());
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Screen title should match', $result->title)->equals('New screen title');
            verify('Screen version should match', $result->versionId)->equals(1001);
            verify('Screen alignment should match', $result->alignment)->equals(Screen::ALIGNMENT_LEFT);
            verify('Screen background should match', $result->background)->equals('#fff000');
            verify('Screen hotspots should match', $result->hotspots)->equals($hotspots);
        });
    }

    /**
     * Tests whether `ScreenForm::save()` UPDATES an existing Screen model.
     */
    public function testSaveUpdate()
    {
        $user   = User::findOne(1002);
        $screen = Screen::findOne(1001);

        $this->specify('Error update attempt', function() use ($user, $screen) {
            $model = new ScreenForm($user, [
                'title'      => '',
                'versionId'  => 1004, // not owned by the user
                'alignment'  => 0,
                'background' => '#abc',
                'hotspots'   => ['test' => ['width' => 'invalid']],
            ]);
            $model->scenario = ScreenForm::SCENARIO_UPDATE;

            $result = $model->save($screen);

            verify('Model should not save', $result)->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('VersionId error message should be set', $model->errors)->hasKey('versionId');
            verify('Alignment error message should be set', $model->errors)->hasKey('alignment');
            verify('background error message should be set', $model->errors)->hasKey('background');
            verify('Hotspots error message should be set', $model->errors)->hasKey('hotspots');
            verify('Image error message should not be set', $model->errors)->hasntKey('image');
        });

        $this->specify('Success update attempt 1', function() use ($user, $screen) {
            $model = new ScreenForm($user, [
                'title'     => 'New screen title',
                'versionId' => 1001,
                'alignment' => Screen::ALIGNMENT_LEFT,
            ]);
            $model->scenario = ScreenForm::SCENARIO_UPDATE;

            $result = $model->save($screen);

            $screen->refresh();

            verify('Model should save successfully', $result)->isInstanceOf(Screen::className());
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Screen title should match', $screen->title)->equals('New screen title');
            verify('Screen version should match', $screen->versionId)->equals(1001);
            verify('Screen alignment should match', $screen->alignment)->equals(Screen::ALIGNMENT_LEFT);
            verify('Screen background should match', $screen->background)->null();
            verify('Screen hotspots should match', $screen->hotspots)->null();
        });

        $this->specify('Success update attempt 2 (with hotspots)', function() use ($user, $screen) {
            $hotspots = ['test' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 1]];

            $model = new ScreenForm($user, [
                'title'      => 'New screen title',
                'versionId'  => 1001,
                'alignment'  => Screen::ALIGNMENT_LEFT,
                'background' => '#fff000',
                'hotspots'   => $hotspots,
            ]);
            $model->scenario = ScreenForm::SCENARIO_UPDATE;

            $result = $model->save($screen);

            $screen->refresh();

            verify('Model should save successfully', $result)->isInstanceOf(Screen::className());
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Screen title should match', $screen->title)->equals('New screen title');
            verify('Screen version should match', $screen->versionId)->equals(1001);
            verify('Screen alignment should match', $screen->alignment)->equals(Screen::ALIGNMENT_LEFT);
            verify('Screen background should match', $screen->background)->equals('#fff000');
            verify('Screen hotspots should match', $screen->hotspots)->equals(json_encode($hotspots));
        });
    }
}
