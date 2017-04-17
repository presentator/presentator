<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\Inflector;
use common\models\Screen;
use common\models\Version;
use common\components\helpers\CFileHelper;

/**
 * Screens upload form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensUploadForm extends Model
{
    /**
     * @var \yii\web\UploadedFile[]
     */
    public $images;

    /**
     * @var Version
     */
    private $version;

    /**
     * Model constructor.
     * @param Version $version
     * @param array $config
     */
    public function __construct(Version $version, $config = [])
    {
        $this->version = $version;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['images'], 'image',
                'skipOnEmpty' => false,
                'extensions'  => 'png, jpg, jpeg',
                'maxFiles'    => 10,
                'maxSize'     => (1024 * 1024 * Yii::$app->params['maxUploadSize']),
            ],
        ];
    }

    /**
     * Creates and upload new screens.
     * @return false|Screen[] `false` on validation error, otherwise - array with the created Screen models.
     */
    public function save()
    {
        if ($this->validate()) {
            $result        = [];
            $uploadDirPath = $this->version->project->getUploadDir();
            $uploadDirUrl  = CFileHelper::getUrlFromPath($uploadDirPath, false);

            // ensure that the directory exist
            CFileHelper::createDirectory($uploadDirPath);

            $lastScreen = $this->version->lastScreen;

            foreach ($this->images as $image) {
                $filename = Inflector::slug($image->basename) . '_' . time() . '_' . rand(0, 100) . '.' . $image->extension;

                $model            = new Screen;
                $model->versionId = $this->version->id;
                $model->title     = $image->basename;
                $model->imageUrl  = rtrim($uploadDirUrl, '/') . '/' . $filename;

                // copy screen settings from the last uploaded version screen (if it's available)
                if ($lastScreen) {
                    $model->alignment  = $lastScreen->alignment;
                    $model->background = $lastScreen->background;
                } else {
                    $model->alignment  = Screen::ALIGNMENT_CENTER;
                    $model->background = null;
                }

                $model->generateThumbsOnCreate = false;

                if (
                    $image->saveAs(rtrim($uploadDirPath, '/') . '/' . $filename) &&
                    $model->save()
                ) {
                    $result[] = $model;
                }
            }

            return $result;
        }

        return false;
    }
}
