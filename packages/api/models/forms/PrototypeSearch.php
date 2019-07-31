<?php
namespace presentator\api\models\forms;

use presentator\api\data\ActiveDataProvider;
use presentator\api\models\Prototype;

/**
 * Search class for the Prototype model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PrototypeSearch extends ApiSearch
{
    /**
     * @var integer
     */
    public $projectId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $title;

    /**
     * @var float
     */
    public $width;

    /**
     * @var float
     */
    public $height;

    /**
     * @var float
     */
    public $scaleFactor;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['projectId', 'integer'];
        $rules[] = ['type', 'in', 'range' => array_values(Prototype::TYPE)];
        $rules[] = ['title', 'string', 'max' => 255];
        $rules[] = [['width', 'height', 'scaleFactor'], 'number'];

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
        $query->andFilterWhere(['like', Prototype::tableName() . '.title', $this->title]);
        $query->andFilterWhere([Prototype::tableName() . '.projectId' => $this->projectId]);
        $query->andFilterWhere([Prototype::tableName() . '.type' => $this->type]);
        $query->andFilterWhere([Prototype::tableName() . '.width' => $this->width]);
        $query->andFilterWhere([Prototype::tableName() . '.height' => $this->height]);
        $query->andFilterWhere([Prototype::tableName() . '.scaleFactor' => $this->scaleFactor]);
        $this->bindDateRangesToQuery(Prototype::tableName());

        return $dataProvider;
    }
}
