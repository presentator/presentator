<?php
namespace presentator\api\models\forms;

use presentator\api\data\ActiveDataProvider;
use presentator\api\models\GuidelineSection;

/**
 * Search class for the GuidelineSection model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineSectionSearch extends ApiSearch
{
    /**
     * @var integer
     */
    public $projectId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['projectId', 'integer'];
        $rules[] = [['title', 'description'], 'string', 'max' => 255];

        return $rules;
    }

    /**
     * @param  array [$params]
     * @return ActiveDataProvider
     */
    public function search(array $params = []): ActiveDataProvider
    {
        $query = $this->getQuery();

        $dataProvider = new ActiveDataProvider([
            'query'  => $query,
            'pagination' => [
                'pageSizeLimit' => [1, 100],
            ],
        ]);

        // set up sorting
        $dataProvider->sort->defaultOrder = ['order' => SORT_ASC, 'createdAt' => SORT_ASC];

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere([GuidelineSection::tableName() . '.projectId' => $this->projectId]);
        $query->andFilterWhere(['like', GuidelineSection::tableName() . '.title', $this->title]);
        $query->andFilterWhere(['like', GuidelineSection::tableName() . '.description', $this->title]);
        $this->bindDateRangesToQuery(GuidelineSection::tableName());

        return $dataProvider;
    }
}
