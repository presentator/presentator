<?php
namespace presentator\api\models;

use yii2tech\ar\position\PositionBehavior;

/**
 * GuidelineSection AR model
 *
 * @property integer $id
 * @property integer $projectId
 * @property integer $order
 * @property string  $title
 * @property string  $description
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineSection extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'projectId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssets()
    {
        return $this->hasMany(GuidelineAsset::class, ['guidelineSectionId' => 'id'])
            ->addOrderBy([GuidelineAsset::tableName() . '.order' => SORT_ASC]);
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
            'groupAttributes'   => ['projectId'],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['assets'] = 'assets';

        return $extraFields;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // trigger guideline assets delete procedures
        foreach ($this->assets as $asset) {
            if (!$asset->delete()) {
                return false;
            }
        }

        return true;
    }
}
