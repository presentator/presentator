<?php
namespace presentator\api\models;

/**
 * UserProjectRel AR model
 *
 * @property integer $id
 * @property integer $userId
 * @property integer $projectId
 * @property integer $pinned
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserProjectRel extends ActiveRecord
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
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'projectId']);
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();

        $fields['pinned'] = function ($model, $field) {
            return $model->$field ? 1 : 0; // normalize mysql and postgre bool type
        };

        return $fields;
    }
}
