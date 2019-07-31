<?php
namespace presentator\api\models;

use yii\helpers\ArrayHelper;

/**
 * HotspotTemplate AR model
 *
 * @property integer $id
 * @property integer $prototypeId
 * @property string  $title
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotTemplate extends ActiveRecord
{
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
    public function getHotspotTemplateScreenRels()
    {
        return $this->hasMany(HotspotTemplateScreenRel::class, ['hotspotTemplateId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::class, ['id' => 'screenId'])
            ->via('hotspotTemplateScreenRels');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHotspots()
    {
        return $this->hasMany(Hotspot::class, ['hotspotTemplateId' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['hotspots']  = 'hotspots';
        $extraFields['screenIds'] = function ($model, $field) {
            return ArrayHelper::getColumn($model->hotspotTemplateScreenRels, 'screenId');
        };

        return $extraFields;
    }
}
