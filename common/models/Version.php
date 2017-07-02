<?php
namespace common\models;

use yii2tech\ar\position\PositionBehavior;

/**
 * Version AR model.
 *
 * @property integer     $id
 * @property string      $projectId
 * @property null|string $title
 * @property integer     $order
 * @property integer     $createdAt
 * @property integer     $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Version extends CActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%version}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['positionBehavior'] = [
            'class' => PositionBehavior::className(),
            'positionAttribute' => 'order',
            'groupAttributes' => [
                'projectId',
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['screens']  = 'screens';

        return $extraFields;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        foreach ($this->screens as $screen) {
            $screen->delete();
        }

        return parent::delete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'projectId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::className(), ['versionId' => 'id'])
            ->orderBy([Screen::tableName() . '.order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastScreen()
    {
        return $this->hasOne(Screen::className(), ['versionId' => 'id'])
            ->orderBy([Screen::tableName() . '.order' => SORT_DESC]);
    }

    /**
     * Checks whether the current version is the only one of its project.
     * @return boolean
     */
    public function isTheOnlyOne()
    {
        return $this->project->getVersions()->count() == 1;
    }
}
