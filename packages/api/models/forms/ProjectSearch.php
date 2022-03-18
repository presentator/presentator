<?php
namespace presentator\api\models\forms;

use presentator\api\data\ActiveDataProvider;
use presentator\api\models\Project;
use presentator\api\models\UserProjectRel;
use yii\db\Expression;

/**
 * Search class for the Project model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectSearch extends ApiSearch
{
    /**
     * @var integer
     */
    public $title;

    /**
     * @var boolean
     */
    public $archived;

    /**
     * @var boolean
     */
    public $pinned;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['title', 'string', 'max' => 255];
        $rules[] = [['archived', 'pinned'], 'boolean'];

        return $rules;
    }

    /**
     * @param  array [$params]
     * @return ActiveDataProvider
     */
    public function search(array $params = []): ActiveDataProvider
    {
        $query = $this->getQuery()
            ->with(['featuredScreen', 'userProjectRels'])
            ->orderBy([
                new Expression('COALESCE(' . UserProjectRel::tableName() . '.pinned, FALSE) DESC'),
            ]);

        $dataProvider = new ActiveDataProvider([
            'query'  => $query,
            'expand' => ['featuredScreen'],
        ]);

        // set up sorting
        $dataProvider->sort->defaultOrder = ['createdAt' => SORT_DESC];

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere(['like', Project::tableName() . '.title', $this->title]);
        $query->andFilterWhere([Project::tableName() . '.archived' => $this->archived]);
        $query->andFilterWhere([UserProjectRel::tableName() . '.pinned' => $this->pinned]);
        $this->bindDateRangesToQuery(Project::tableName());

        return $dataProvider;
    }
}
