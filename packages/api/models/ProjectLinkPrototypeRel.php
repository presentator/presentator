<?php
namespace presentator\api\models;

/**
 * ProjectLinkPrototypeRel AR model
 *
 * @property integer $id
 * @property integer $projectLinkId
 * @property integer $prototypeId
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectLinkPrototypeRel extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectLink()
    {
        return $this->hasOne(ProjectLink::class, ['id' => 'projectLinkId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrototype()
    {
        return $this->hasOne(Prototype::class, ['id' => 'prototypeId']);
    }
}
