<?php
namespace presentator\api\models\forms;

use presentator\api\data\ActiveDataProvider;
use presentator\api\models\Hotspot;
use presentator\api\models\Screen;
use presentator\api\models\HotspotTemplate;

/**
 * Search class for the Hotspot model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotSearch extends ApiSearch
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
     * @var integer
     */
    public $hotspotTemplateId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var float
     */
    public $left;

    /**
     * @var float
     */
    public $top;

    /**
     * @var float
     */
    public $width;

    /**
     * @var float
     */
    public $height;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['hotspotTemplateId', 'screenId', 'prototypeId'], 'integer'];
        $rules[] = [['left', 'top', 'width', 'height'], 'number', 'min' => 0];
        $rules[] = ['type', 'in', 'range' => array_values(Hotspot::TYPE)];

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
        $dataProvider->sort->defaultOrder = ['createdAt' => SORT_ASC];

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere([Hotspot::tableName() . '.screenId' => $this->screenId]);
        $query->andFilterWhere([Hotspot::tableName() . '.hotspotTemplateId' => $this->hotspotTemplateId]);
        $query->andFilterWhere([Hotspot::tableName() . '.type' => $this->type]);
        $query->andFilterWhere([Hotspot::tableName() . '.left' => $this->left]);
        $query->andFilterWhere([Hotspot::tableName() . '.top' => $this->top]);
        $query->andFilterWhere([Hotspot::tableName() . '.width' => $this->width]);
        $query->andFilterWhere([Hotspot::tableName() . '.height' => $this->height]);
        $this->bindDateRangesToQuery(Hotspot::tableName());

        if ($this->prototypeId) {
            $query->joinWith(['screen', 'hotspotTemplate'], false)
                ->andWhere([
                    'or',
                    [Screen::tableName() . '.prototypeId' => $this->prototypeId],
                    [HotspotTemplate::tableName() . '.prototypeId' => $this->prototypeId],
                ]);
        }

        return $dataProvider;
    }
}
