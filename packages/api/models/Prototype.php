<?php
namespace presentator\api\models;

use Yii;

/**
 * Prototype AR model
 *
 * @property integer $id
 * @property integer $projectId
 * @property string  $title
 * @property string  $type
 * @property float   $width
 * @property float   $height
 * @property float   $scaleFactor
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Prototype extends ActiveRecord
{
    const TYPE = [
        'DESKTOP' => 'desktop',
        'MOBILE'  => 'mobile',
    ];

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
    public function getScreens()
    {
        return $this->hasMany(Screen::class, ['prototypeId' => 'id'])
            ->addOrderBy([Screen::tableName() . '.order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHotspotTemplates()
    {
        return $this->hasMany(HotspotTemplate::class, ['prototypeId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectLinkPrototypeRels()
    {
        return $this->hasMany(ProjectLinkPrototypeRel::class, ['prototypeId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectLinks()
    {
        return $this->hasMany(ProjectLink::class, ['id' => 'projectLinkId'])
            ->via('projectLinkPrototypeRels');
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // delete project links that were restricted only for the deleted prototype
        foreach ($this->projectLinks as $projectLink) {
            if (
                count($projectLink->prototypes) == 1 &&
                $projectLink->prototypes[0]->id == $this->id &&
                !$projectLink->delete()
            ) {
                return false;
            }
        }

        // trigger related screens delete procedure
        foreach ($this->screens as $screen) {
            if (!$screen->delete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['screens']          = 'screens';
        $extraFields['hotspotTemplates'] = 'hotspotTemplates';

        return $extraFields;
    }
}
