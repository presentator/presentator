<?php
namespace presentator\api\models\forms;

use presentator\api\data\ActiveDataProvider;
use presentator\api\models\ProjectLink;

/**
 * Search class for the ProjectLink model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectLinkSearch extends ApiSearch
{
    /**
     * @var integer
     */
    public $projectId;

    /**
     * @var string
     */
    public $slug;

    /**
     * @var boolean
     */
    public $allowComments;

    /**
     * @var boolean
     */
    public $allowGuideline;

    /**
     * @var boolean
     */
    public $hasPassword;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['projectId', 'integer'];
        $rules[] = ['slug', 'string', 'max' => 100];
        $rules[] = [['allowComments', 'allowGuideline', 'hasPassword'], 'boolean'];

        return $rules;
    }

    /**
     * @param  array [$params]
     * @return ActiveDataProvider
     */
    public function search(array $params = []): ActiveDataProvider
    {
        $query = $this->getQuery()->with('prototypes');

        $dataProvider = new ActiveDataProvider([
            'query'  => $query,
            'expand' => ['prototypes'],
            'pagination' => [
                'pageSizeLimit' => [1, 100],
            ],
        ]);

        // set up sorting
        $dataProvider->sort->defaultOrder = ['createdAt' => SORT_ASC];

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere([ProjectLink::tableName() . '.projectId' => $this->projectId]);
        $query->andFilterWhere(['like', ProjectLink::tableName() . '.slug', $this->slug]);
        $query->andFilterWhere([ProjectLink::tableName() . '.allowComments' => $this->allowComments]);
        $query->andFilterWhere([ProjectLink::tableName() . '.allowGuideline' => $this->allowGuideline]);
        $this->bindDateRangesToQuery(ProjectLink::tableName());

        if ($this->hasPassword !== null) {
            if ($this->hasPassword) {
                $query->andFilterWhere([
                    'and',
                    ['not', [ProjectLink::tableName() . '.passwordHash' => null]],
                    ['not', [ProjectLink::tableName() . '.passwordHash' => '']],
                ]);
            } else {
                $query->andFilterWhere([
                    'or',
                    [ProjectLink::tableName() . '.passwordHash' => null],
                    [ProjectLink::tableName() . '.passwordHash' => ''],
                ]);
            }
        }

        return $dataProvider;
    }
}
