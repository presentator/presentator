<?php
namespace common\models;

/**
 * UserAuth AR model
 *
 * @property integer $id
 * @property integer $userId
 * @property string  $source
 * @property integer $sourceId
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserAuth extends CActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%userAuth}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }
}
