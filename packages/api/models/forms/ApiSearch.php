<?php
namespace presentator\api\models\forms;

use yii\base\Model;
use yii\db\ActiveQuery;

/**
 * Base Search class intented to be inherited by all other api search models.
 * Usually used with `\app\components\data\ActiveDataProvider`.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
abstract class ApiSearch extends Model
{
    /**
     * @var string
     */
    public $beforeCreatedAt;

    /**
     * @var string
     */
    public $afterCreatedAt;

    /**
     * @var string
     */
    public $beforeUpdatedAt;

    /**
     * @var string
     */
    public $afterUpdatedAt;

    /**
     * @var ActiveQuery
     */
    protected $query;

    /**
     * @param ActiveQuery $query
     * @param array       [$config]
     */
    public function __construct(ActiveQuery $query, $config = [])
    {
        $this->setQuery($query);

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return 'search';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [
            ['beforeCreatedAt', 'afterCreatedAt', 'beforeUpdatedAt', 'afterUpdatedAt'],
            'date',
            'format' => 'php:Y-m-d H:i:s',
        ];

        return $rules;
    }

    /**
     * @param ActiveQuery $query
     */
    public function setQuery(ActiveQuery $query): void
    {
        $this->query = $query;
    }

    /**
     * @return ActiveQuery
     */
    public function getQuery(): ActiveQuery
    {
        return $this->query;
    }

    /**
     * Binds datetime fields range filters to the model's query.
     *
     * @param string $tableAlias
     */
    protected function bindDateRangesToQuery(string $tableAlias): void
    {
        $query = $this->getQuery();
        if (!$query) {
            return;
        }

        $query->andFilterWhere(['<=', $tableAlias . '.createdAt', $this->beforeCreatedAt]);
        $query->andFilterWhere(['>=', $tableAlias . '.createdAt', $this->afterCreatedAt]);
        $query->andFilterWhere(['<=', $tableAlias . '.updatedAt', $this->beforeUpdatedAt]);
        $query->andFilterWhere(['>=', $tableAlias . '.updatedAt', $this->afterUpdatedAt]);
    }
}
