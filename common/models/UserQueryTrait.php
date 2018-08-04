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
     * @param  boolean $mustBeOwner Flag to filter and return only super user owned items (for regular users this flag is ignored)
     * @return integer
     */
    public function countProjects($mustBeOwner = false)
    {
        $query = Project::find()->select(Project::tableName() . '.id');

        if ($this->type != self::TYPE_SUPER || $mustBeOwner) {
            $query->joinWith('userRels', false)
                ->andWhere([UserProjectRel::tableName() . '.userId' => $this->id]);
        }

        return (int) $query->count();
    }

    /**
     * Returns filtered list with user projects.
     * @param  string $search
     * @param  integer $limit       Number of returned results.
     * @param  integer $offset      Results page offset
     * @param  boolean $mustBeOwner Flag to filter and return only super user owned items (for regular users this flag is ignored)
     * @return Project[]
     */
    public function searchProjects($search, $limit = -1, $offset = 0, $mustBeOwner = false)
    {
        $searchParts = explode(' ', $search); // some sort of fuzzy searching

        $query = Project::find()
            ->distinct()
            ->with(['featuredScreen', 'screens']);

        if ($this->type != self::TYPE_SUPER || $mustBeOwner) {
            $query->joinWith('userRels', false)
                ->andWhere([UserProjectRel::tableName() . '.userId' => $this->id]);
        }

        return $query->andWhere(['or like', Project::tableName() . '.title', $searchParts])
            ->orderBy([Project::tableName() . '.createdAt' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all();
    }

    /**
     * Returns list with user projects.
     * @param  integer $limit       Number of returned results.
     * @param  integer $offset      Results page offset
     * @param  boolean $mustBeOwner Flag to filter and return only super user owned items (for regular users this flag is ignored)
     * @return Project[]
     */
    public function findProjects($limit = -1, $offset = 0, $mustBeOwner = false)
    {
        $query = Project::find()
            ->distinct()
            ->with(['featuredScreen', 'screens']);

        if ($this->type != self::TYPE_SUPER || $mustBeOwner) {
            $query->joinWith('userRels', false)
                ->andWhere([UserProjectRel::tableName() . '.userId' => $this->id]);
        }

        return $query->orderBy([Project::tableName() . '.createdAt' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all();
    }

    /**
     * Returns single user project by its id.
     * @param  integer $id
     * @param  boolean $mustBeOwner Flag to filter and return only super user owned items (for regular users this flag is ignored)
     * @return Project|null
     */
    public function findProjectById($id, $mustBeOwner = false)
    {
        $query = Project::find()
            ->with(['versions.screens']);

        if ($this->type != self::TYPE_SUPER || $mustBeOwner) {
            $query->joinWith('userRels', false)
                ->andWhere([UserProjectRel::tableName() . '.userId' => $this->id]);
        }

        return $query->andWhere([
                Project::tableName() . '.id' => $id,
            ])
            ->one();
    }

    /**
     * Returns a single Version model belonging to a project owned by the current user.
     * @param  integer $versionId
     * @param  boolean $mustBeOwner Flag to filter and return only super user owned items (for regular users this flag is ignored)
     * @return Version|null
     */
    public function findVersionById($versionId, $mustBeOwner = false)
    {
        $query = Version::find();

        if ($this->type != self::TYPE_SUPER || $mustBeOwner) {
            $query->joinWith(['project.userRels'], false)
                ->andWhere([UserProjectRel::tableName() . '.userId' => $this->id]);
        }

        return $query->andWhere([
                Version::tableName() . '.id' => $versionId,
            ])
            ->one();
    }

    /**
     * Generates query object for fetching screen model(s)
     * belonging to a project owned by the current user.
     * @param  integer|array $screenId
     * @param  boolean       $mustBeOwner Flag to filter and return only super user owned items (for regular users this flag is ignored)
     * @return \yii\db\ActiveQuery
     */
    public function findScreensQuery($screenId, $mustBeOwner = false)
    {
        $query = Screen::find()->distinct();

        if ($this->type != self::TYPE_SUPER || $mustBeOwner) {
            $query->joinWith(['project.userRels'], false)
                ->andWhere([UserProjectRel::tableName() . '.userId' => $this->id]);
        }

        return $query->andWhere([
                Screen::tableName() . '.id' => $screenId,
            ])
            ->orderBy([Screen::tableName() . '.order' => SORT_ASC]);
    }

    /**
     * Returns single Screen model belonging to a project owned by the current user.
     * @param  integer $screenId
     * @param  boolean $mustBeOwner Flag to filter and return only super user owned items (for regular users this flag is ignored)
     * @return Screen
     */
    public function findScreenById($screenId, $mustBeOwner = false)
    {
        return $this->findScreensQuery($screenId, $mustBeOwner)->one();
    }

    /**
     * Returns single ScreenComment belonging to a project owned by the current user.
     * @param  integer $commentId
     * @param  boolean $mustBeOwner Flag to filter and return only super user owned items (for regular users this flag is ignored)
     * @return ScreenComment
     */
    public function findScreenCommentById($commentId, $mustBeOwner = false)
    {
        $query = ScreenComment::find();

        if ($this->type != self::TYPE_SUPER || $mustBeOwner) {
            $query->joinWith('screen.project.userRels', false)
                ->andWhere([UserProjectRel::tableName() . '.userId' => $this->id]);
        }

        return $query->andWhere([
                ScreenComment::tableName() . '.id' => $commentId,
            ])
            ->one();
    }

    /**
     * Returns latest leaved ScreenComment models belonging to projects owned by the current user.
     * @param  integer $limit       Number of the returned results.
     * @param  integer $offset      Results offset.
     * @param  boolean $mustBeOwner Flag to filter and return only super user owned items (for regular users this flag is ignored)
     * @return ScreenComment[]
     */
    public function findLeavedScreenComments($limit = 20, $offset = 0, $mustBeOwner = false)
    {
        $query = ScreenComment::find();

        if ($this->type != self::TYPE_SUPER || $mustBeOwner) {
            $query->joinWith('screen.project.userRels')
                ->andWhere([UserProjectRel::tableName() . '.userId' => $this->id]);
        }

        return $query
            ->andWhere(['not like', ScreenComment::tableName() . '.from', $this->email])
            ->orderBy([ScreenComment::tableName() . '.createdAt' => SORT_DESC])
            ->groupBy(ScreenComment::tableName() . '.id')
            ->limit($limit)
            ->offset($offset)
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
