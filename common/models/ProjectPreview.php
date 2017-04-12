<?php
namespace common\models;

use Yii;

/**
 * ProjectPreview AR model
 *
 * @property integer $id
 * @property integer $projectId
 * @property string  $slug
 * @property integer $type
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectPreview extends CActiveRecord
{
    const TYPE_VIEW             = 1;
    const TYPE_VIEW_AND_COMMENT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%projectPreview}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['projectId', 'slug', 'type'], 'required'],
            ['type', 'in', 'range' => array_keys(self::getTypeLabels())],
            ['slug', 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['project'] = 'project';

        return $extraFields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'projectId']);
    }

    /**
     * Returns project preview types with labels.
     * @return array
     */
    public static function getTypeLabels()
    {
        return [
            self::TYPE_VIEW             => Yii::t('app', 'View only'),
            self::TYPE_VIEW_AND_COMMENT => Yii::t('app', 'View and comment'),
        ];
    }

    /**
     * Sets random unique string as a preview model slug.
     * @param integer $length
     */
    public function generateSlug($length = 8)
    {
        $alphabet = [
            ['abcdefghijklmnopqrstuvwxyz', 3], // min 3 chars
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', 3], // min 3 chars
            ['0123456789', 1],                 // min 1 char
        ];

        // generate the random slug
        $slug = Yii::$app->security->generateRandomString($length, $alphabet);

        // check if slug exist
        $slugExist = self::checkSlugExist($slug);

        if ($slugExist) {
            // try to generate another string
            $maxAttempt = 3;
            while ($slugExist && $maxAttempt > 0) {
                $slug = Yii::$app->security->generateRandomString($length, $alphabet);

                $slugExist = self::checkSlugExist($slug);
                $maxAttempt--;
            }
        }

        $this->slug = $slug;
    }

    /**
     * Checks if preview model with the passed slug exists.
     * @param  string $slug
     * @return boolean
     */
    public static function checkSlugExist($slug)
    {
        return static::find()
            ->where(['slug' => $slug])
            ->exists();
    }

    /**
     * Returns single preview model by its slug.
     * @param  string       $slug
     * @param  integer|null $type
     * @return ProjectPreview|null
     */
    public static function findOneBySlug($slug, $type = null)
    {
        return static::find()
            ->where(['slug' => $slug])
            ->andFilterWhere(['type' => $type])
            ->one();
    }

    /* Emails
    --------------------------------------------------------------- */
    /**
     * Sends a project preview invitation email.
     * @param  string|array $to
     * @return boolean
     */
    public function sendPreviewEmail($to)
    {
        return Yii::$app->mailer->compose('project_preview', [
                'preview' => $this,
            ])
            ->setFrom(Yii::$app->params['noreplyEmail'])
            ->setTo($to)
            ->setSubject('Presentator.io - ' . Yii::t('app', '{projectTitle} preview', ['projectTitle' => $this->project->title]))
            ->send();
    }
}
