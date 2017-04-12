<?php
namespace app\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\models\Screen;
use app\models\ScreenSettingsForm;

/**
 * ScreenSettingsForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenSettingsFormTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \app\tests\UnitTester
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
        ]);
    }

    /**
     * `ScreenSettingsForm::loadScreen()` method test.
     */
    public function testLoadScreen()
    {
        $screen1 = Screen::findOne(1001);
        $screen2 = Screen::findOne(1002);
        $model = new ScreenSettingsForm($screen1);

        $model->loadScreen($screen2);

        verify('Screen title should match with screen2 one', $model->title)->equals($screen2->title);
        verify('Screen background should match with screen2 one', $model->background)->equals($screen2->background);
        verify('Screen alignment should match with screen2 one', $model->alignment)->equals($screen2->alignment);
    }

    /**
     * `ScreenSettingsForm::validateHex()` method test.
     */
    public function testValidateHex()
    {
        $screen = Screen::findOne(1001);

        $this->specify('INVALID HEX color code', function() use ($screen) {
            $model = new ScreenSettingsForm($screen, [
                'background' => 'invalid_hex_color',
            ]);
            $model->validateHex('background', []);

            verify('Backround error message should be set', $model->errors)->hasKey('background');
        });

        $this->specify('VALID HEX color code', function() use ($screen) {
            $model = new ScreenSettingsForm($screen, [
                'background' => '#000000',
            ]);
            $model->validateHex('background', []);

            verify('Backround error message should not be set', $model->errors)->hasntKey('background');
        });
    }

    /**
     * `ScreenSettingsForm::save()` method test.
     */
    public function testSave()
    {
        $screen = Screen::findOne(1001);

        $this->specify('Wrong save attempt', function() use ($screen) {
            $model = new ScreenSettingsForm($screen, [
                'title'      => '',
                'alignment'  => -1,
                'background' => 'invalid_hex',
            ]);

            verify('Model should not save', $model->save())->false();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Alignment error message should be set', $model->errors)->hasKey('alignment');
            verify('Backround error message should be set', $model->errors)->hasKey('alignment');
        });

        $this->specify('Correct save attempt', function() use ($screen) {
            $title      = 'Test title';
            $alignment  = Screen::ALIGNMENT_LEFT;
            $background = '#000000';

            $model = new ScreenSettingsForm($screen, [
                'title'      => $title,
                'alignment'  => $alignment,
                'background' => $background,
            ]);

            verify('Model should save', $model->save())->true();
            verify('Model should not have any errors', $model->errors)->isEmpty();

            $screen->refresh();
            verify('Screen title should be changed', $screen->title)->equals($title);
            verify('Screen alignment should be changed', $screen->alignment)->equals($alignment);
            verify('Screen background should be changed', $screen->background)->equals($background);
        });
    }
}
