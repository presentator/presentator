<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use common\models\User;
use common\components\helpers\CFileHelper;
use Imagine\Image\Box;
use yii\imagine\Image;

/**
 * Avatar upload form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class AvatarForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $avatar;

    /**
     * @var User
     */
    private $user;

    /**
     * Model constructor.
     * @param User $user
     * @param array $config
     */
    public function __construct(User $user, $config = [])
    {
        $this->user = $user;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'avatar' => Yii::t('app', 'Avatar'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['avatar'], 'image',
                'skipOnEmpty' => false,
                'extensions'  => 'png, jpg',
                'maxFiles'    => 1,
                'maxSize'     => (1024 * 1024 * Yii::$app->params['maxUploadSize']),
                'maxHeight'   => 3500,
                'maxWidth'    => 3500,
            ],
        ];
    }

    /**
     * Uploads a temporary avatar file.
     * @param  string $fileName
     * @return boolean
     */
    public function tempUpload()
    {
        if ($this->validate() && $this->avatar instanceof UploadedFile) {
            CFileHelper::createDirectory($this->user->getUploadDir());

            ini_set('memory_limit', '512M');

            Image::getImagine()
                ->open($this->avatar->tempName)
                ->thumbnail(new Box(1000, 1000))
                ->save($this->user->getTempAvatarPath(), ['quality' => 90]);

            return true;
        }

        return false;
    }
}
