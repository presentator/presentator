<?php
namespace presentator\api\models\forms;

use presentator\api\data\ActiveDataProvider;
use presentator\api\models\HotspotTemplate;
use presentator\api\models\HotspotTemplateScreenRel;

/**
 * Search class for the HotspotTemplate model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotTemplateSearch extends ApiSearch
{
    /**
     * @var integer
     */
    public $prototypeId;

    /**
     * @var integer
     */
    public $screenId;

    /**
     * @var string
     */
    public $title;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['prototypeId', 'screenId'], 'integer'];
        $rules[] = ['title', 'string', 'max' => 255];

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
            'query' => $query,
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
        $query->andFilterWhere(['like', HotspotTemplate::tableName() . '.title', $this->title]);
        $query->andFilterWhere([HotspotTemplate::tableName() . '.prototypeId' => $this->prototypeId]);
        $this->bindDateRangesToQuery(HotspotTemplate::tableName());

        if ($this->screenId) {
            $query->joinWith('hotspotTemplateScreenRels', false);
            $query->andWhere([HotspotTemplateScreenRel::tableName() . '.screenId' => $this->screenId]);
        }

        return $dataProvider;
    }
}
