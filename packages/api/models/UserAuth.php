<?php
namespace presentator\api\models;

/**
 * UserAuth AR model
 *
 * @property integer $id
 * @property integer $userId
 * @property string  $source
 * @property string  $sourceId
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserAuth extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }
}
