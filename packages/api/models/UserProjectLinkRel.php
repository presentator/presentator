<?php
namespace presentator\api\models;

/**
 * UserProjectLinkRel AR model
 *
 * @property integer $id
 * @property integer $userId
 * @property integer $projectLinkId
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserProjectLinkRel extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectLink()
    {
        return $this->hasOne(ProjectLink::class, ['id' => 'projectLinkId']);
    }
}
