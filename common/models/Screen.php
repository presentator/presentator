<?php
namespace common\models;

use Yii;
use Imagine\Image\Box;
use yii\imagine\Image;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;
use common\components\helpers\CFileHelper;
use yii2tech\ar\position\PositionBehavior;

/**
 * Screen AR model.
 *
 * @property integer $id
 * @property integer $versionId
 * @property string  $title
 * @property string  $hotspots
 * @property integer $order
 * @property integer $alignment
 * @property string  $background
 * @property string  $imageUrl
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Screen extends CActiveRecord
{
    // @see `self::getAlignmentLabels()`
    const ALIGNMENT_LEFT   = 1;
    const ALIGNMENT_CENTER = 2;
    const ALIGNMENT_RIGHT  = 3;

    const THUMB_SIZES = [
        // name => [width, height, quality]
        'medium' => [500, 500, 70],
        'small'  => [100, 100, 70],
    ];

    public $generateThumbsOnCreate = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%screen}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['positionBehavior'] = [
            'class' => PositionBehavior::className(),
            'positionAttribute' => 'order',
            'groupAttributes' => [
                'versionId',
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        $fields['hotspots'] = function ($model) {
            if ($model->hotspots) {
                return json_decode($model->hotspots);
            }

            return null;
        };

        $fields['thumbs'] = function ($model) {
            $result = [];

            foreach (self::THUMB_SIZES as $name => $sizes) {
                $result[$name] = $model->getThumbUrl($name);
            }

            return $result;
        };

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['project']        = 'project';
        $extraFields['version']        = 'version';
        $extraFields['commentTargets'] = 'primaryScreenComments';

        return $extraFields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenComments()
    {
        return $this->hasMany(ScreenComment::className(), ['screenId' => 'id'])
            ->orderBy([ScreenComment::tableName() . '.createdAt' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrimaryScreenComments()
    {
        return $this->hasMany(ScreenComment::className(), ['screenId' => 'id'])
            ->andWhere([ScreenComment::tableName() . '.replyTo' => null])
            ->orderBy([ScreenComment::tableName() . '.createdAt' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVersion()
    {
        return $this->hasOne(Version::className(), ['id' => 'versionId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'projectId'])
            ->via('version');
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && $this->generateThumbsOnCreate) {
            foreach (static::THUMB_SIZES as $name => $option) {
                $this->createThumb($name);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        @unlink(CFileHelper::getPathFromUrl($this->imageUrl));
        foreach (static::THUMB_SIZES as $name => $option) {
            @unlink($this->getThumbPath($name));
        }

        return parent::delete();
    }

    /**
     * Returns translated screen alignment labels list.
     * @return array
     */
    public static function getAlignmentLabels()
    {
        return [
            self::ALIGNMENT_LEFT   => Yii::t('app', 'Left'),
            self::ALIGNMENT_CENTER => Yii::t('app', 'Center'),
            self::ALIGNMENT_RIGHT  => Yii::t('app', 'Right'),
        ];
    }

    /**
     * Generates screen thumb path.
     * @param  string $sizeName
     * @return string
     */
    public function getThumbPath($sizeName)
    {
        $pathInfo = pathinfo(CFileHelper::getPathFromUrl($this->imageUrl));

        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb_' . $sizeName . '.' . $pathInfo['extension'];
    }

    /**
     * Generates screen thumb url with option to create the thumb size on the fly if missing.
     * @param  string  $sizeName
     * @param  boolean $createIfMissing
     * @return string
     */
    public function getThumbUrl($sizeName, $createIfMissing = true)
    {
        if ($createIfMissing && !file_exists($this->getThumbPath($sizeName))) {
            try {
                $this->createThumb($sizeName);
            } catch (\Exception $e) {
            }
        }

        return CFileHelper::getUrlFromPath($this->getThumbPath($sizeName));
    }

    /**
     * Creates single image thumb (based on `self::THUMB_SIZES`).
     * @param  string $sizeName
     * @return string The generate thumb url.
     * @throws InvalidValueException The original image doesn't exist.
     * @throws InvalidParamException Unknown thumb size name.
     */
    public function createThumb($sizeName)
    {
        ini_set('memory_limit', '1024M');

        $originalImgPath = CFileHelper::getPathFromUrl($this->imageUrl);
        $pathInfo = pathinfo($originalImgPath);
        $thumbSizes = static::THUMB_SIZES;

        if (!file_exists($originalImgPath)) {
            throw new InvalidValueException($originalImgPath . ' does not exist!');
        }

        if (!isset($thumbSizes[$sizeName])) {
            throw new InvalidParamException($sizeName . ' is not defined in Screen::THUMB_SIZES!');
        }

        $thumbOptions = $thumbSizes[$sizeName];

        Image::thumbnail($originalImgPath, $thumbOptions[0], $thumbOptions[1])
            ->save($this->getThumbPath($sizeName), ['quality' => $thumbOptions[2]]);

        return $this->getThumbUrl($sizeName, false);
    }
}
