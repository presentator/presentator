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
use common\models\Version;
use app\models\ScreensUploadForm;

/**
 * ScreensUploadForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensUploadFormTest extends \Codeception\Test\Unit
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
     * Test helper to create CUploadedFile instance(s) from file(s) path.
     * @param  array|string $file
     * @return array|CUploadedFile
     */
    protected function getUploadedFileInstance($file)
    {
        $result = [];

        $fileArr = is_array($file) ? $file : [$file];
        foreach ($fileArr as $path) {
            $result[] = new CUploadedFile([
                'name'     => basename($path),
                'tempName' => $path,
                'type'     => FileHelper::getMimeType($path),
                'size'     => filesize($path),
                'error'    => UPLOAD_ERR_OK,
            ]);
        }

        if (is_array($file)) {
            return $result;
        }

        return $result[0];
    }

    /**
     * `ScreensUploadForm::save()` method test.
     */
    public function testSave()
    {
        $version = Version::findOne(1001);

        $this->specify('Error save attempt', function() use ($version) {
            $imagePath1 = Yii::getAlias('@common/tests/_data/test_image.png'); // supported type
            $imagePath2 = Yii::getAlias('@common/tests/_data/test_image.gif'); // unsupported type
            $images = $this->getUploadedFileInstance([$imagePath1, $imagePath2]);

            $model = new ScreensUploadForm($version, [
                'images' => $images,
            ]);

            verify('Model should not save', $model->save())->false();
            verify('Images error message should be set', $model->errors)->hasKey('images');
        });

        $this->specify('Success save attempt', function() use ($version) {
            $imagePath1 = Yii::getAlias('@common/tests/_data/test_image.png'); // supported type
            $imagePath2 = Yii::getAlias('@common/tests/_data/test_image.jpg'); // supported type
            $images = $this->getUploadedFileInstance([$imagePath1, $imagePath2]);

            $model = new ScreensUploadForm($version, [
                'images' => $images,
            ]);

            $result = $model->save();

            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('Model save result should not be empty', $result)->notEmpty();
        });
    }
}
