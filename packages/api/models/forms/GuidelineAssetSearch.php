<?php
namespace presentator\api\models\forms;

use presentator\api\validators\HexValidator;
use presentator\api\data\ActiveDataProvider;
use presentator\api\models\GuidelineAsset;

/**
 * Search class for the GuidelineAsset model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineAssetSearch extends ApiSearch
{
    /**
     * @var integer
     */
    public $guidelineSectionId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $hex;

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

        $rules[] = ['guidelineSectionId', 'integer'];
        $rules[] = ['type', 'in', 'range' => array_values(GuidelineAsset::TYPE)];
        $rules[] = ['hex', HexValidator::class];
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
        $dataProvider->sort->defaultOrder = ['order' => SORT_ASC, 'createdAt' => SORT_ASC];

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere([GuidelineAsset::tableName() . '.guidelineSectionId' => $this->guidelineSectionId]);
        $query->andFilterWhere([GuidelineAsset::tableName() . '.type' => $this->type]);
        $query->andFilterWhere([GuidelineAsset::tableName() . '.hex' => $this->hex]);
        $query->andFilterWhere(['like', GuidelineAsset::tableName() . '.title', $this->title]);
        $this->bindDateRangesToQuery(GuidelineAsset::tableName());

        return $dataProvider;
    }
}
