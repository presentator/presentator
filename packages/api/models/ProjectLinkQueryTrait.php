<?php
namespace presentator\api\models;

use \yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

trait ProjectLinkQueryTrait
{
    /**
     * Returns array with all `Prototype` models that are allowed
     * to be accessed by the current model.
     *
     * @return ProjectLink[]
     */
    public function findAllowedPrototypes(): array
    {
        $allowedPrototypeIds = ArrayHelper::getColumn($this->projectLinkPrototypeRels, 'prototypeId');

        return Prototype::find()
            ->where(['projectId' => $this->projectId])
            ->andFilterWhere(['id' => $allowedPrototypeIds])
            ->orderBy(['createdAt' => SORT_ASC])
            ->all();
    }

    /**
     * Returns single `Prototype` model that is allowed to be
     * accessed by the current model.
     *
     * @param  integer $id
     * @return null|Prototype
     */
    public function findAllowedPrototypeById(int $id): ?Prototype
    {
        $allowedPrototypeIds = ArrayHelper::getColumn($this->projectLinkPrototypeRels, 'prototypeId');

        if (
            !empty($allowedPrototypeIds) &&
            !in_array($id, $allowedPrototypeIds)
        ) {
            return null;
        }

        return Prototype::find()
            ->where([
                'projectId' => $this->projectId,
                'id'        => $id,
            ])
            ->one();
    }

    /**
     * Generates query to fetch preview allowed screens.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findAllowedScreensQuery(): ActiveQuery
    {
        $allowedPrototypeIds = ArrayHelper::getColumn($this->projectLinkPrototypeRels, 'prototypeId');

        return Screen::find()
            ->joinWith('prototype', false)
            ->andWhere([
                Prototype::tableName() . '.projectId' => $this->projectId,
            ])
            ->andFilterWhere([
                Prototype::tableName() . '.id' => $allowedPrototypeIds,
            ]);
    }

    /**
     * Returns single preview allowed project screen by its id.
     *
     * @param  integer $screenId  ID of the screen to fetch.
     * @param  array   [$filters] Additional filters to apply to the query.
     * @return null|Screen
     */
    public function findAllowedScreenById(int $screenId, array $filters = []): ?Screen
    {
        $query = $this->findAllowedScreensQuery();

        return $query->andWhere([Screen::tableName() . '.id' => $screenId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Generates query to fetch preview allowed screen comments.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findAllowedScreenCommentsQuery(): ActiveQuery
    {
        $allowedPrototypeIds = ArrayHelper::getColumn($this->projectLinkPrototypeRels, 'prototypeId');

        return ScreenComment::find()
            ->joinWith('screen.prototype', false)
            ->andWhere([
                Prototype::tableName() . '.projectId' => $this->projectId,
            ])
            ->andFilterWhere([
                Prototype::tableName() . '.id' => $allowedPrototypeIds,
            ]);
    }

    /**
     * Returns single screen comment by its id if the current
     * ProjectLink model is allowed to view the comment's prototype.
     *
     * @param  integer $commentId ID of the comment to fetch.
     * @param  array   [$filters] Additional filters to apply to the query.
     * @return null|ScreenComment
     */
    public function findAllowedScreenCommentById(int $commentId, array $filters = []): ?ScreenComment
    {
        if (!$this->allowComments) {
            return null;
        }

        $query = $this->findAllowedScreenCommentsQuery();

        return $query->andWhere([ScreenComment::tableName() . '.id' => $commentId])
            ->andFilterWhere($filters)
            ->one();
    }
}
