<?php
namespace common\models;

/**
 * UserScreenCommentRel AR model.
 *
 * @property integer $id
 * @property integer $userId
 * @property integer $screenCommentId
 * @property integer $isRead
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserScreenCommentRel extends CActiveRecord
{
    const IS_READ_FALSE = 0;
    const IS_READ_TRUE  = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%userScreenCommentRel}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComment()
    {
        return $this->hasOne(ScreenComment::className(), ['id' => 'screenCommentId']);
    }
}
