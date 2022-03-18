<?php
namespace presentator\api\models;

use Yii;
use yii2tech\ar\position\PositionBehavior;
use presentator\api\behaviors\FileStorageBehavior;

/**
 * GuidelineAsset AR model
 *
 * @property integer $id
 * @property integer $guidelineSectionId
 * @property string  $type
 * @property integer $order
 * @property string  $hex
 * @property string  $title
 * @property string  $filePath
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineAsset extends ActiveRecord
{
    const TYPE = [
        'FILE'  => 'file',
        'COLOR' => 'color',
    ];

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGuidelineSection()
    {
        return $this->hasOne(GuidelineSection::class, ['id' => 'guidelineSectionId']);
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
            'groupAttributes'   => ['guidelineSectionId'],
        ];

        $behaviors['fileBehavior'] = [
            'class' => FileStorageBehavior::class,
            'filePathPrefix' => function ($model) {
                if ($model->guidelineSection && $model->guidelineSection->project) {
                    return $model->guidelineSection->project->getGuidelinesStoragePath();
                }

                return '';
            },
            'thumbs' => [
                'small'  => ['width' => 100, 'height' => 100],
                'medium' => ['width' => 250, 'smartResize' => false],
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
            if ($model->type == static::TYPE['FILE'] && $model->filePath) {
                $result = [
                    'original' => $model->getUrl(),
                ];

                if ($model->supportThumbs()) {
                    $result['small']  = $model->getThumbUrl('small');
                    $result['medium'] = $model->getThumbUrl('medium');
                }

                return $result;
            }

            return null;
        };

        return $fields;
    }
}
