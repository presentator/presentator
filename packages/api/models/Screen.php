<?php
namespace presentator\api\models;

use Yii;
use yii\web\UploadedFile;
use yii2tech\ar\position\PositionBehavior;
use presentator\api\behaviors\FileStorageBehavior;

/**
 * Screen AR model
 *
 * @property integer $id
 * @property integer $prototypeId
 * @property integer $order
 * @property string  $title
 * @property string  $alignment
 * @property string  $background
 * @property float   $fixedHeader
 * @property float   $fixedFooter
 * @property string  $filePath
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Screen extends ActiveRecord
{
    const ALIGNMENT = [
        'LEFT'   => 'left',
        'CENTER' => 'center',
        'RIGHT'  => 'right',
    ];

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrototype()
    {
        return $this->hasOne(Prototype::class, ['id' => 'prototypeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenComments()
    {
        return $this->hasMany(ScreenComment::class, ['screenId' => 'id'])
            ->addOrderBy([ScreenComment::tableName() . '.createdAt' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrimaryScreenComments()
    {
        return $this->getScreenComments()
            ->andWhere([ScreenComment::tableName() . '.replyTo' => null]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHotspotTemplateScreenRels()
    {
        return $this->hasMany(HotspotTemplateScreenRel::class, ['screenId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHotspotTemplates()
    {
        return $this->hasMany(HotspotTemplate::class, ['id' => 'hotspotTemplateId'])
            ->via('hotspotTemplateScreenRels');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHotspots()
    {
        return $this->hasMany(Hotspot::class, ['screenId' => 'id']);
    }

    /**
     * @return Screen
     */
    public function findLastSibling(): ?Screen
    {
        return static::find()
            ->where(['not', ['id' => $this->id]])
            ->andWhere(['prototypeId' => $this->prototypeId])
            ->addOrderBy(['order' => SORT_DESC])
            ->one();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['positionBehavior'] = [
            'class'             => PositionBehavior::class,
            'positionAttribute' => 'order',
            'groupAttributes'   => ['prototypeId'],
        ];

        $behaviors['fileBehavior'] = [
            'class' => FileStorageBehavior::class,
            'filePathPrefix' => function ($model) {
                if ($model->prototype && $model->prototype->project) {
                    return $model->prototype->project->getPrototypesStoragePath();
                }

                return '';
            },
            'thumbs' => [
                'small'  => ['width' => 130, 'height' => 130],
                'medium' => ['width' => 400, 'smartResize' => false],
            ],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();

        unset($fields['filePath']);

        $fields['file'] = function ($model, $field) {
            return (object) $model->getFile();
        };

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['hotspots']       = 'hotspots';
        $extraFields['screenComments'] = 'screenComments';

        return $extraFields;
    }

    /**
     * Returns list with all model's file urls.
     *
     * @return array
     */
    public function getFile(): array
    {
        if ($this->filePath) {
            return [
                'original' => $this->getUrl(),
                'small'    => $this->getThumbUrl('small'),
                'medium'   => $this->getThumbUrl('medium'),
            ];
        }

        return [];
    }
}
