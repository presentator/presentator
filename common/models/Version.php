<?php
namespace common\models;

use Yii;
use yii2tech\ar\position\PositionBehavior;

/**
 * Version AR model.
 *
 * @property integer      $id
 * @property string       $projectId
 * @property null|string  $title
 * @property integer      $type
 * @property null|integer $subtype
 * @property float        $scaleFactor
 * @property integer      $order
 * @property integer      $createdAt
 * @property integer      $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Version extends CActiveRecord
{
    // Main types (@see `self::getTypeLabels()`)
    const TYPE_DESKTOP = 1;
    const TYPE_TABLET  = 2;
    const TYPE_MOBILE  = 3;

    const SUBTYPES = [
        // Tablet (@see `self::getTabletSubtypeLabels()`)
        21 => [768, 1024],
        22 => [1024, 768],
        23 => [800, 1200],
        24 => [1200, 800],
        // Mobile (@see `self::getMobileSubtypeLabels()`)
        31 => [320, 480],
        32 => [480, 320],
        33 => [375, 667],
        34 => [667, 375],
        35 => [412, 732],
        36 => [732, 712],
    ];

    // Predefined scale factors
    const AUTO_SCALE_FACTOR    = 0;
    const DEFAULT_SCALE_FACTOR = 1;
    const RETINA_SCALE_FACTOR  = 2;

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

    /**
     * Returns version types with labels.
     * @return array
     */
    public static function getTypeLabels()
    {
        return [
            self::TYPE_MOBILE  => Yii::t('app', 'Mobile'),
            self::TYPE_TABLET  => Yii::t('app', 'Tablet'),
            self::TYPE_DESKTOP => Yii::t('app', 'Desktop'),
        ];
    }

    /**
     * Returns version tablet type's subtypes with label.
     * @return array
     */
    public static function getTabletSubtypeLabels()
    {
        return [
            21 => Yii::t('app', '768x1024 <em>(Portrait)</em>'),
            22 => Yii::t('app', '768x1024 <em>(Landscape)</em>'),
            23 => Yii::t('app', '800x1200 <em>(Portrait)</em>'),
            24 => Yii::t('app', '800x1200 <em>(Landscape)</em>'),
        ];
    }

    /**
     * Returns version mobile type's subtypes with labels.
     * @return array
     */
    public static function getMobileSubtypeLabels()
    {
        return [
            31 => Yii::t('app', '320x480 <em>(Portrait)</em>'),
            32 => Yii::t('app', '320x480 <em>(Landscape)</em>'),
            33 => Yii::t('app', '375x667 <em>(Portrait)</em>'),
            34 => Yii::t('app', '375x667 <em>(Landscape)</em>'),
            35 => Yii::t('app', '412x732 <em>(Portrait)</em>'),
            36 => Yii::t('app', '412x732 <em>(Landscape)</em>'),
        ];
    }

    /**
     * Returns resolved model scale factor based on screen width.
     * @param  integer|float [$width]
     * @return integer|float
     */
    public function getScaleFactor($width = 0)
    {
        $scaleFactor = self::DEFAULT_SCALE_FACTOR;

        if ($this->scaleFactor != self::AUTO_SCALE_FACTOR) {
            // Custom scale factor
            $scaleFactor = $this->scaleFactor;
        } elseif (
            $this->subtype &&                            // has defined subtype
            !empty(self::SUBTYPES[$this->subtype][0]) && // has subtype width
            $width > self::SUBTYPES[$this->subtype][0]   // the base size is larger than the subtype one
        )  {
            // Auto scale factor
            $scaleFactor = $width / self::SUBTYPES[$this->subtype][0];
        }

        return $scaleFactor;
    }
}
