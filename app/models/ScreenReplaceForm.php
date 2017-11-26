<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use common\models\Screen;
use common\components\helpers\CFileHelper;

/**
 * Screen image replace form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenReplaceForm extends Model
{
    /**
     * @var \yii\web\UploadedFile
     */
    public $image;

    /**
     * @var Screen
     */
    private $screen;

    /**
     * Model constructor.
     * @param Screen $screen
     * @param array  $config
     */
    public function __construct(Screen $screen, $config = [])
    {
        $this->screen = $screen;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image'], 'image',
                'skipOnEmpty' => false,
                'extensions'  => 'png, jpg, jpeg',
                'maxFiles'    => 1,
                'maxSize'     => (1024 * 1024 * Yii::$app->params['maxUploadSize']),
            ],
        ];
    }

    /**
     * Replace single screen image.
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $uploadDirPath = $this->screen->project->getUploadDir();
            $uploadDirUrl  = CFileHelper::getUrlFromPath($uploadDirPath, false);
            $filename      = Inflector::slug($this->image->basename) . '_' . time() . '_' . rand(0, 100) . '.' . $this->image->extension;

            // store old image and thumb file paths
            $oldFiles = [CFileHelper::getPathFromUrl($this->screen->imageUrl)];
            foreach (Screen::THUMB_SIZES as $name => $option) {
                $oldFiles[] = $this->screen->getThumbPath($name);
            }

            // set the new image file name as a title (if the current one is not user specified)
            if (StringHelper::startsWith(basename($this->screen->imageUrl), Inflector::slug($this->screen->title))) {
                $this->screen->title = $this->image->basename;
            }

            // set the new image url
            $this->screen->imageUrl = rtrim($uploadDirUrl, '/') . '/' . $filename;

            if (
                $this->image->saveAs(rtrim($uploadDirPath, '/') . '/' . $filename) &&
                $this->screen->save()
            ) {
                // delete old image and thumb files
                foreach ($oldFiles as $olfFile) {
                    @unlink($oldFile);
                }

                return true;
            }
        }

        return false;
    }
}
