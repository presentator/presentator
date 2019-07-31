<?php
namespace presentator\api\models;

/**
 * HotspotTemplateScreenRel AR model
 *
 * @property integer $id
 * @property integer $hotspotTemplateId
 * @property integer $screenId
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotTemplateScreenRel extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHotspotTemplate()
    {
        return $this->hasOne(HotspotTemplate::class, ['id' => 'hotspotTemplateId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreen()
    {
        return $this->hasOne(Screen::class, ['id' => 'screenId']);
    }
}
