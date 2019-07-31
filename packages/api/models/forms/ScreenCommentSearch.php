<?php
namespace presentator\api\models\forms;

use presentator\api\data\ActiveDataProvider;
use presentator\api\models\Screen;
use presentator\api\models\ScreenComment;

/**
 * Search class for the ScreenComment model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentSearch extends ApiSearch
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
     * Reply comment id.
     * Set an empty string or zero in order to add `replyTo IS NULL` constraint.
     *
     * @var integer
     */
    public $replyTo;

    /**
     * @var string
     */
    public $from;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $status;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['prototypeId', 'screenId', 'replyTo'], 'integer'];
        $rules[] = ['from', 'email'];
        $rules[] = ['message', 'string'];
        $rules[] = ['status', 'in', 'range' => array_values(ScreenComment::STATUS)];

        return $rules;
    }

    /**
     * @param  array [$params]
     * @return ActiveDataProvider
     */
    public function search(array $params = []): ActiveDataProvider
    {
        $query = $this->getQuery()->with('fromUser');

        $dataProvider = new ActiveDataProvider([
            'query'  => $query,
            'expand' => ['fromUser'],
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
        $query->andFilterWhere([ScreenComment::tableName() . '.screenId' => $this->screenId]);
        $query->andFilterWhere([ScreenComment::tableName() . '.from' => $this->from]);
        $query->andFilterWhere([ScreenComment::tableName() . '.status' => $this->status]);
        $query->andFilterWhere(['like', ScreenComment::tableName() . '.message', $this->message]);
        $this->bindDateRangesToQuery(ScreenComment::tableName());

        if ($this->replyTo !== null && !$this->replyTo) {
            $query->andWhere([ScreenComment::tableName() . '.replyTo' => null]);
        } else {
            $query->andFilterWhere([ScreenComment::tableName() . '.replyTo' => $this->replyTo]);
        }

        if ($this->prototypeId) {
            $query->joinWith('screen', false)
                ->andWhere([Screen::tableName() . '.prototypeId' => $this->prototypeId]);
        }

        return $dataProvider;
    }
}
