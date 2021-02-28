<?php
namespace presentator\api\models;

use \yii\db\ActiveQuery;

trait UserQueryTrait
{
    /**
     * Generates query to fetch user projects.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findProjectsQuery(): ActiveQuery
    {
        $query = Project::find();

        $query->joinWith(['userProjectRels' => function (ActiveQuery $q) {
            if ($this->isSuperUser()) { // soft bind (for the pinned state)
                $q->andOnCondition([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ]);
            } else { // access check
                $q->andWhere([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ]);
            }
        }], false);

        return $query;
    }

    /**
     * Returns single project by its id.
     * Only owned projects will be returned, if the current user is not "Super user"
     *
     * @param  integer $projectId ID of the project to fetch.
     * @param  array   [$filters] Additional filters to apply to the query.
     * @return null|Project
     */
    public function findProjectById(int $projectId, array $filters = []): ?Project
    {
        $query = $this->findProjectsQuery();

        return $query->andWhere([Project::tableName() . '.id' => $projectId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Generates query to fetch project prototypes owned by the user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findPrototypesQuery(): ActiveQuery
    {
        $query = Prototype::find();

        if (!$this->isSuperUser()) {
            $query->joinWith('project.userProjectRels', false)
                ->andWhere([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ]);
        }

        return $query;
    }

    /**
     * Returns single user owned project prototype by its id.
     *
     * @param  integer $prototypeId ID of the prototype to fetch.
     * @param  array   [$filters]   Additional filters to apply to the query.
     * @return null|Prototype
     */
    public function findPrototypeById(int $prototypeId, array $filters = []): ?Prototype
    {
        $query = $this->findPrototypesQuery();

        return $query->andWhere([Prototype::tableName() . '.id' => $prototypeId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Generates query to fetch project links owned by the user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findProjectLinksQuery(): ActiveQuery
    {
        $query = ProjectLink::find();

        if (!$this->isSuperUser()) {
            $query->joinWith('project.userProjectRels', false)
                ->andWhere([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ]);
        }

        return $query;
    }

    /**
     * Returns single user owned project link by its id.
     *
     * @param  integer $projectLinkId ID of the project link to fetch.
     * @param  array   [$filters]     Additional filters to apply to the query.
     * @return null|ProjectLink
     */
    public function findProjectLinkById(int $projectLinkId, array $filters = []): ?ProjectLink
    {
        $query = $this->findProjectLinksQuery();

        return $query->andWhere([ProjectLink::tableName() . '.id' => $projectLinkId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Generates query to fetch guideline sections owned by the user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findGuidelineSectionsQuery(): ActiveQuery
    {
        $query = GuidelineSection::find();

        if (!$this->isSuperUser()) {
            $query->joinWith('project.userProjectRels', false)
                ->andWhere([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ]);
        }

        return $query;
    }

    /**
     * Returns single user owned guideline section by its id.
     *
     * @param  integer $sectionId ID of the guideline section to fetch.
     * @param  array   [$filters] Additional filters to apply to the query.
     * @return null|GuidelineSection
     */
    public function findGuidelineSectionById(int $sectionId, array $filters = []): ?GuidelineSection
    {
        $query = $this->findGuidelineSectionsQuery();

        return $query->andWhere([GuidelineSection::tableName() . '.id' => $sectionId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Generates query to fetch guideline assets owned by the user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findGuidelineAssetsQuery(): ActiveQuery
    {
        $query = GuidelineAsset::find();

        if (!$this->isSuperUser()) {
            $query->joinWith('guidelineSection.project.userProjectRels', false)
                ->andWhere([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ]);
        }

        return $query;
    }

    /**
     * Returns single user owned guideline asset by its id.
     *
     * @param  integer $assetId   ID of the guideline asset to fetch.
     * @param  array   [$filters] Additional filters to apply to the query.
     * @return null|GuidelineAsset
     */
    public function findGuidelineAssetById(int $assetId, array $filters = []): ?GuidelineAsset
    {
        $query = $this->findGuidelineAssetsQuery();

        return $query->andWhere([GuidelineAsset::tableName() . '.id' => $assetId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Generates query to fetch project screens owned by the user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findScreensQuery(): ActiveQuery
    {
        $query = Screen::find();

        if (!$this->isSuperUser()) {
            $query->joinWith('prototype.project.userProjectRels', false)
                ->andWhere([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ]);
        }

        return $query;
    }

    /**
     * Returns user owned project screen by its id.
     *
     * @param  integer $screenId  ID of the screen to fetch.
     * @param  array   [$filters] Additional filters to apply to the query.
     * @return null|Screen
     */
    public function findScreenById(int $screenId, array $filters = []): ?Screen
    {
        $query = $this->findScreensQuery();

        return $query->andWhere([Screen::tableName() . '.id' => $screenId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Generates query to fetch screen comments owned by the user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findScreenCommentsQuery(): ActiveQuery
    {
        $query = ScreenComment::find();

        if (!$this->isSuperUser()) {
            $query->joinWith('screen.prototype.project.userProjectRels', false)
                ->andWhere([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ]);
        }

        return $query;
    }

    /**
     * Returns user owned screen comment by its id.
     *
     * @param  integer $commentId ID of the comment to fetch.
     * @param  array   [$filters] Additional filters to apply to the query.
     * @return null|ScreenComment
     */
    public function findScreenCommentById(int $commentId, array $filters = []): ?ScreenComment
    {
        $query = $this->findScreenCommentsQuery();

        return $query->andWhere([ScreenComment::tableName() . '.id' => $commentId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Returns all available unread screen comments for the current user.
     *
     * @return array
     */
    public function findUnreadScreenComments(): array
    {
        return ScreenComment::find()
            ->joinWith('userScreenCommentRels', false, 'INNER JOIN')
            ->andWhere([
                UserScreenCommentRel::tableName() . '.isRead' => false,
                UserScreenCommentRel::tableName() . '.userId' => $this->id,
            ])
            ->orderBy([ScreenComment::tableName() . '.createdAt' => SORT_DESC])
            ->all();
    }

    /**
     * Generates query to fetch hotspot template owned by the user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findHotspotTemplatesQuery(): ActiveQuery
    {
        $query = HotspotTemplate::find();

        if (!$this->isSuperUser()) {
            $query->joinWith('prototype.project.userProjectRels', false)
                ->andWhere([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ]);
        }

        return $query;
    }

    /**
     * Returns user owned hotspot template by its id.
     *
     * @param  integer $templateId ID of the hotspot template to fetch.
     * @param  array   [$filters]  Additional filters to apply to the query.
     * @return null|HotspotTemplate
     */
    public function findHotspotTemplateById(int $templateId, array $filters = []): ?HotspotTemplate
    {
        $query = $this->findHotspotTemplatesQuery();

        return $query->andWhere([HotspotTemplate::tableName() . '.id' => $templateId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Generates query to fetch screen hotspots owned by the user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findHotspotsQuery(): ActiveQuery
    {
        $query = Hotspot::find();

        if (!$this->isSuperUser()) {
            $connection = Hotspot::getDb();

            $query->joinWith(['screen', 'hotspotTemplate'], false)
                ->leftJoin(
                    Prototype::tableName(),
                    sprintf(
                        '%s.%s=%s.%s OR %s.%s=%s.%s',
                        $connection->quoteTableName(Screen::tableName()),
                        $connection->quoteColumnName('prototypeId'),
                        $connection->quoteTableName(Prototype::tableName()),
                        $connection->quoteColumnName('id'),
                        // or
                        $connection->quoteTableName(HotspotTemplate::tableName()),
                        $connection->quoteColumnName('prototypeId'),
                        $connection->quoteTableName(Prototype::tableName()),
                        $connection->quoteColumnName('id')
                    )
                )
                ->leftJoin(
                    UserProjectRel::tableName(),
                    sprintf(
                        '%s.%s=%s.%s',
                        $connection->quoteTableName(Prototype::tableName()),
                        $connection->quoteColumnName('projectId'),
                        $connection->quoteTableName(UserProjectRel::tableName()),
                        $connection->quoteColumnName('projectId')
                    )
                )
                ->andWhere([
                    UserProjectRel::tableName() . '.userId' => $this->id,
                ])
            ;
        }

        return $query;
    }

    /**
     * Returns user owned screen hotspot by its id.
     *
     * @param  integer $hotspotId ID of the hotspot to fetch.
     * @param  array   [$filters] Additional filters to apply to the query.
     * @return null|Hotspot
     */
    public function findHotspotById(int $hotspotId, array $filters = []): ?Hotspot
    {
        $query = $this->findHotspotsQuery();

        return $query->andWhere([Hotspot::tableName() . '.id' => $hotspotId])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Generates query to fetch recent project links accessed by the user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findAccessedProjectLinksQuery(): ActiveQuery
    {
        return ProjectLink::find()
            ->joinWith('userProjectLinkRels', false)
            ->andWhere([
                UserProjectLinkRel::tableName() . '.userId' => $this->id,
            ])
            ->orderBy([UserProjectLinkRel::tableName() . '.updatedAt' => SORT_DESC]);
    }

    /**
     * Returns single project link accessed by the user.
     *
     * @param  integer $projectLinkId ID of the project link to fetch.
     * @param  array   [$filters]     Additional filters to apply to the query.
     * @return null|ProjectLink
     */
    public function findAccessedProjectLinkById(int $projectLinkId, array $filters = []): ?ProjectLink
    {
        $query = $this->findProjectLinksQuery();

        return $query->andWhere([ProjectLink::tableName() . '.id' => $projectLinkId])
            ->andFilterWhere($filters)
            ->one();
    }
}
