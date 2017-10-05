<?php
namespace common\models;

/**
 * Project model query trait.
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
trait ProjectQueryTrait
{
    /**
     * Returns a single Version model belonging to the current project model.
     * @param  integer $versionId
     * @return null|Version
     */
    public function findVersionById($versionId)
    {
        return Version::find()
            ->where([
                'id'        => $versionId,
                'projectId' => $this->id,
            ])
            ->one();
    }

    /**
     * Generates query object for fetching screen model(s)
     * belonging to the current project model.
     * @param  integer|array $screenId
     * @return \yii\db\ActiveQuery
     */
    public function findScreensQuery($screenId)
    {
        return Screen::find()
            ->distinct()
            ->joinWith('project', false)
            ->where([
                Project::tableName() . '.id' => $this->id,
                Screen::tableName() . '.id'  => $screenId,
            ])
            ->orderBy([Screen::tableName() . '.order' => SORT_ASC]);
    }

    /**
     * Returns single Screen model belonging to a project owned by the current user.
     * @param  integer $screenId
     * @return null|Screen
     */
    public function findScreenById($screenId)
    {
        return $this->findScreensQuery($screenId)->one();
    }

    /**
     * Returns single ScreenComment belonging to a project owned by the current user.
     * @param  integer $commentId
     * @return null|ScreenComment
     */
    public function findScreenCommentById($commentId)
    {
        return ScreenComment::find()
            ->joinWith('screen.project', false)
            ->where([
                Project::tableName() . '.id'       => $this->id,
                ScreenComment::tableName() . '.id' => $commentId,
            ])
            ->one();
    }

    /**
     * Returns single ProjectPreview model by its type.
     * @param  integer $type
     * @return null|ProjectPreview
     */
    public function findPreviewByType($type)
    {
        return ProjectPreview::find()
            ->where([
                'projectId' => $this->id,
                'type'      => $type,
            ])
            ->one();
    }

    /**
     * Returns array list with info for all project commenters (email, first name, etc.).
     * @param  boolean $checkMentionSetting Whether to exclude registered users with uncheck mention setting.
     * @return array
     */
    public function findAllCommenters($checkMentionSetting = true)
    {
        $query = ScreenComment::find()
            ->distinct()
            ->select([
                'email'     => ScreenComment::tableName() . '.from',
                'firstName' => User::tableName() . '.firstName',
                'lastName'  => User::tableName() . '.lastName',
                'userId'    => User::tableName() . '.id',
            ])
            ->innerJoinWith('screen.project', false)
            ->leftJoin(
                User::tableName(),
                sprintf('%s.email = %s.from', User::tableName(), ScreenComment::tableName())
            )
            ->where([
                Project::tableName() . '.id'   => $this->id,
            ])
            ->indexBy('email')
        ;

        if ($checkMentionSetting) {
            $query->leftJoin(
                UserSetting::tableName(),
                sprintf('%s.userId = %s.id', UserSetting::tableName(), User::tableName())
            )
            ->andWhere([
                'or',
                [
                    // registered users
                    UserSetting::tableName() . '.settingName'  => User::MENTIONS_SETTING_KEY,
                    UserSetting::tableName() . '.settingValue' => 'true',
                ],
                [
                    // guests
                    UserSetting::tableName() . '.settingName'  => null,
                    UserSetting::tableName() . '.settingValue' => null,
                ],
            ]);
        }

        return $query->asArray()
            ->all();
    }
}
