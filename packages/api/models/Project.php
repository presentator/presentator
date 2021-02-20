<?php
namespace presentator\api\models;

use Yii;
use yii\db\Expression;

/**
 * Project AR model
 *
 * @property integer $id
 * @property string  $title
 * @property integer $archived
 * @property integer $pinned
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Project extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProjectRels()
    {
        return $this->hasMany(UserProjectRel::class, ['projectId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'userId'])
            ->via('userProjectRels');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrototypes()
    {
        return $this->hasMany(Prototype::class, ['projectId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGuidelineSections()
    {
        return $this->hasMany(GuidelineSection::class, ['projectId' => 'id'])
            ->addOrderBy([GuidelineSection::tableName() . '.order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectLinks()
    {
        return $this->hasMany(ProjectLink::class, ['projectId' => 'id']);
    }

    /**
     * Generates relation query to fetch the first screen of last active prototype.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFeaturedScreen()
    {
        return $this->hasOne(Screen::className(), ['prototypeId' => 'id'])
            ->via('prototypes')
            ->orderBy([
                Screen::tableName() . '.prototypeId' => SORT_DESC,
                Screen::tableName() . '.order'       => SORT_ASC,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // trigger prototypes delete procedures
        foreach ($this->prototypes as $prototype) {
            if (!$prototype->delete()) {
                return false;
            }
        }

        // trigger guideline sections delete procedures
        foreach ($this->guidelineSections as $section) {
            if (!$section->delete()) {
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

        $extraFields['prototypes']        = 'prototypes';
        $extraFields['projectLinks']      = 'projectLinks';
        $extraFields['guidelineSections'] = 'guidelineSections';
        $extraFields['featuredScreen']    = function ($model, $field) {
            if ($model->featuredScreen) {
                return [
                    'original' => $model->featuredScreen->getUrl(),
                    'small'    => $model->featuredScreen->getThumbUrl('small'),
                    'medium'   => $model->featuredScreen->getThumbUrl('medium'),
                ];
            }

            return (object) [];
        };

        return $extraFields;
    }

    /**
     * Returns the storage directory path for the project prototype files.
     *
     * @return string
     */
    public function getPrototypesStoragePath(): string
    {
        $projectKey = md5(Yii::$app->params['storageKeysSalt'] . $this->id);

        return '/projects/' . $projectKey . '/prototypes';
    }

    /**
     * Returns the storage directory path for the project guideline files.
     *
     * @return string
     */
    public function getGuidelinesStoragePath(): string
    {
        $projectKey = md5(Yii::$app->params['storageKeysSalt'] . $this->id);

        return '/projects/' . $projectKey . '/guideline';
    }

    /**
     * Returns list with all project's collaborators - linked users and screen commentators (including guests).
     *
     * Each list item has the following fields type:
     * ``php
     * [
     *     'email'     => string,
     *     'firstName' => null|string,
     *     'lastName'  => null|string,
     *     'userId'    => null|id,
     * ]
     * ```
     *
     * @return array
     */
    public function findAllCollaborators(): array
    {
        // fetch screen commentators
        $result = ScreenComment::find()
            ->select([
                'email' => ScreenComment::tableName() . '.from',
            ])
            // add dummy user fields
            // they are populate later if a user with the selected email exist
            ->addSelect(new Expression('NULL as id'))
            ->addSelect(new Expression('NULL as firstName'))
            ->addSelect(new Expression('NULL as lastName'))
            ->addSelect(new Expression('NULL as avatar'))
            ->innerJoinWith('screen.prototype', false)
            ->andWhere([
                Prototype::tableName() . '.projectId' => $this->id,
            ])
            ->orderBy([
                ScreenComment::tableName() . '.createdAt' => SORT_DESC,
            ])
            ->indexBy('email')
            ->asArray()
            ->all();


        $users = array_merge(
            User::findAll(['email' => array_keys($result), 'status' => User::STATUS['ACTIVE']]),
            $this->users
        );

        foreach ($users as $user) {
            // normalize existing record or add user to the end result
            $result[$user->email] = [
                'id'        => (int) $user->id,
                'email'     => $user->email,
                'firstName' => $user->firstName,
                'lastName'  => $user->lastName,
                'avatar'    => $user->getAvatar(),
            ];
        }

        return array_values($result);
    }
}
