<?php
namespace app\tests\models;

use Yii;
use yii\helpers\FileHelper;
use common\components\web\CUploadedFile;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\models\Screen;
use app\models\ScreenReplaceForm;

/**
 * ScreenReplaceForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenReplaceFormTest extends \Codeception\Test\Unit
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
     * Test helper to create CUploadedFile instance from file path.
     * @param  string $filePath
     * @return CUploadedFile
     */
    protected function getUploadedFileInstance($filePath)
    {
        return new CUploadedFile([
            'name'     => basename($filePath),
            'tempName' => $filePath,
            'type'     => FileHelper::getMimeType($filePath),
            'size'     => filesize($filePath),
            'error'    => UPLOAD_ERR_OK,
        ]);
    }

    /**
     * `ScreenReplaceForm::save()` method test.
     */
    public function testSave()
    {
        $this->specify('Error save attempt', function() {
            $screen    = Screen::findOne(1001);
            $imagePath = Yii::getAlias('@common/tests/_data/test_image.gif'); // unsupported extension
            $image     = $this->getUploadedFileInstance($imagePath);

            $model = new ScreenReplaceForm($screen, [
                'image' => $image,
            ]);

            verify('Model should not save', $model->save())->false();
            verify('Image error message should be set', $model->errors)->hasKey('image');
        });

        $this->specify('Success save attempt (without screen title replacement)', function() {
            $screen        = Screen::findOne(1002);
            $originalTitle = $screen->title;
            $imagePath     = Yii::getAlias('@common/tests/_data/test_image.png');
            $image         = $this->getUploadedFileInstance($imagePath);

            $model = new ScreenReplaceForm($screen, [
                'image' => $image,
            ]);

            verify('Model should save', $model->save())->true();
            verify('Model should not have any errors', $model->errors)->isEmpty();

            $screen->refresh();
            verify('Model screen title should not be changed', $screen->title)->equals($originalTitle);
        });

        $this->specify('Success save attempt (with screen title replacement)', function() {
            $screen        = Screen::findOne(1001);
            $originalTitle = $screen->title;
            $imagePath     = Yii::getAlias('@common/tests/_data/test_image.png');
            $image         = $this->getUploadedFileInstance($imagePath);

            $model = new ScreenReplaceForm($screen, [
                'image' => $image,
            ]);

            verify('Model should save', $model->save())->true();
            verify('Model should not have any errors', $model->errors)->isEmpty();

            $screen->refresh();
            verify('Model screen title should be changed', $screen->title)->equals('test_image');
        });
    }
}
