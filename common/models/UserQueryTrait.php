<?php
namespace common\models;

use common\components\helpers\CArrayHelper;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
trait UserQueryTrait
{
    /**
     * Counts total user projects.
     * @return integer
     */
    public function countProjects()
    {
        return (int) $this->getProjects()
            ->select('id')
            ->count();
    }

    /**
     * Returns filtered list with user projects.
     * @param  string $search
     * @param  integer $limit  Number of returned results.
     * @param  integer $offset Results page offset
     * @return Project[]
     */
    public function searchProjects($search, $limit = -1, $offset = 0)
    {
        $searchParts = explode(' ', $search); // some sort of fuzzy searching

        return Project::find()
            ->distinct()
            ->with(['featuredScreen', 'screens'])
            ->joinWith('userRels', false)
            ->where([UserProjectRel::tableName() . '.userId' => $this->id])
            ->andWhere(['like', Project::tableName() . '.title', $searchParts])
            ->orderBy([Project::tableName() . '.createdAt' => SORT_ASC])
            ->limit($limit)
            ->offset($offset)
            ->all();
    }

    /**
     * Returns list with user projects.
     * @param  integer $limit  Number of returned results.
     * @param  integer $offset Results page offset
     * @return Project[]
     */
    public function findProjects($limit = -1, $offset = 0)
    {
        return Project::find()
            ->distinct()
            ->with(['featuredScreen', 'screens'])
            ->joinWith('userRels', false)
            ->where([UserProjectRel::tableName() . '.userId' => $this->id])
            ->orderBy([Project::tableName() . '.createdAt' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all();
    }

    /**
     * Returns single user project by its id.
     * @param  integer $id
     * @return Project|null
     */
    public function findProjectById($id)
    {
        return Project::find()
            ->with(['versions.screens'])
            ->joinWith('userRels', false)
            ->where([
                Project::tableName() . '.id'            => $id,
                UserProjectRel::tableName() . '.userId' => $this->id,
            ])
            ->one();
    }

    /**
     * Returns a single Version model belonging to a project owned by the current user.
     * @param  integer $versionId
     * @return Version|null
     */
    public function findVersionById($versionId)
    {
        return Version::find()
            ->joinWith(['project.userRels'], false)
            ->where([
                UserProjectRel::tableName() . '.userId' => $this->id,
                Version::tableName() . '.id'            => $versionId,
            ])
            ->one();
    }

    /**
     * Generates query object for fetching screen model(s)
     * belonging to a project owned by the current user.
     * @param  integer|array $screenId
     * @return \yii\db\ActiveQuery
     */
    public function findScreensQuery($screenId)
    {
        return Screen::find()
            ->distinct()
            ->joinWith(['project.userRels'], false)
            ->where([
                UserProjectRel::tableName() . '.userId' => $this->id,
                Screen::tableName() . '.id'      => $screenId,
            ])
            ->orderBy([Screen::tableName() . '.order' => SORT_ASC]);
    }

    /**
     * Returns single Screen model belonging to a project owned by the current user.
     * @param  integer $screenId
     * @return Screen
     */
    public function findScreenById($screenId)
    {
        return $this->findScreensQuery($screenId)->one();
    }

    /**
     * Returns single ScreenComment belonging to a project owned by the current user.
     * @param  integer $commentId
     * @return ScreenComment
     */
    public function findScreenCommentById($commentId)
    {
        return ScreenComment::find()
            ->joinWith('screen.project.userRels', false)
            ->where([
                UserProjectRel::tableName() . '.userId' => $this->id,
                ScreenComment::tableName() . '.id'      => $commentId,
            ])
            ->one();
    }

    /**
     * Returns latest leaved ScreenComment models belonging to projects owned by the current user.
     * @param  integer $limit  Number of the returned results.
     * @param  integer $offset Results offset.
     * @return ScreenComment[]
     */
    public function findLeavedScreenComments($limit = 20, $offset = 0)
    {
        return ScreenComment::find()
            ->joinWith('screen.project.userRels')
            ->where([UserProjectRel::tableName() . '.userId' => $this->id])
            ->andWhere(['not like', ScreenComment::tableName() . '.from', $this->email])
            ->orderBy([ScreenComment::tableName() . '.createdAt' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->groupBy(ScreenComment::tableName() . '.id')
            ->all();
    }

    /**
     * Counts unread user comments by list of screen ids.
     * @param  array   $screenIds
     * @param  boolean $onlyPrimary Counts only primary comments.
     * @return array
     */
    public function countUnreadCommentsByScreens(array $screenIds, $onlyPrimary = false)
    {
        $query = ScreenComment::find()
            ->select([
                // minimize select fields
                ScreenComment::tableName() . '.id',
                Screen::tableName() . '.id as groupId'
            ])
            ->joinWith(['userRels', 'screen'], false)
            ->where([
                Screen::tableName() . '.id' => $screenIds,
                UserScreenCommentRel::tableName() . '.userId' => $this->id,
                UserScreenCommentRel::tableName() . '.isRead' => UserScreenCommentRel::IS_READ_FALSE,
            ])
            ->asArray();

        if ($onlyPrimary) {
            $query->andWhere([ScreenComment::tableName() . '.replyTo' => null]);
        }

        $grouppedComments = CArrayHelper::index($query->all(), null, 'groupId');

        return CArrayHelper::countGroupByKeys($screenIds, $grouppedComments);
    }

    /**
     * Counts unread user comments by list of project ids.
     * @param  array   $projectIds
     * @param  boolean $onlyPrimary Counts only primary comments.
     * @return array
     */
    public function countUnreadCommentsByProjects(array $projectIds, $onlyPrimary = false)
    {
        $query = ScreenComment::find()
            ->select([
                // minimize select fields
                ScreenComment::tableName() . '.id',
                Project::tableName() . '.id as groupId'
            ])
            ->joinWith(['userRels', 'screen.project'], false)
            ->where([
                Project::tableName() . '.id' => $projectIds,
                UserScreenCommentRel::tableName() . '.userId' => $this->id,
                UserScreenCommentRel::tableName() . '.isRead' => UserScreenCommentRel::IS_READ_FALSE,
            ])
            ->asArray();

        if ($onlyPrimary) {
            $query->andWhere([ScreenComment::tableName() . '.replyTo' => null]);
        }

        $grouppedComments = CArrayHelper::index($query->all(), null, 'groupId');

        return CArrayHelper::countGroupByKeys($projectIds, $grouppedComments);
    }
}
