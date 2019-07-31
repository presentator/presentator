<?php
namespace presentator\api\models\forms;

use presentator\api\data\ActiveDataProvider;
use presentator\api\models\Screen;

/**
 * Search class for the Screen model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenSearch extends ApiSearch
{
    /**
     * @var integer
     */
    public $prototypeId;

    /**
     * @var integer
     */
    public $order;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $alignment;

    /**
     * @var string
     */
    public $background;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['prototypeId', 'order'], 'integer', 'min' => 0];
        $rules[] = ['title', 'string', 'max' => 255];
        $rules[] = ['background', 'string', 'min' => 7, 'max' => 7];
        $rules[] = ['alignment', 'in', 'range' => array_values(Screen::ALIGNMENT)];

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
                'pageSizeLimit' => [1, 200],
            ],
        ]);

        // set up sorting
        $dataProvider->sort->defaultOrder = ['order' => SORT_ASC, 'createdAt' => SORT_ASC];

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere([Screen::tableName() . '.prototypeId' => $this->prototypeId]);
        $query->andFilterWhere(['like', Screen::tableName() . '.title', $this->title]);
        $query->andFilterWhere([Screen::tableName() . '.order' => $this->order]);
        $query->andFilterWhere([Screen::tableName() . '.alignment' => $this->alignment]);
        $query->andFilterWhere([Screen::tableName() . '.background' => $this->background]);
        $this->bindDateRangesToQuery(Screen::tableName());

        return $dataProvider;
    }
}
