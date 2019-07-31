<?php
namespace presentator\api\models\forms;

use presentator\api\data\ActiveDataProvider;
use presentator\api\models\User;

/**
 * Search class for the User model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserSearch extends ApiSearch
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * Loose `OR` condition search within `email`, `firstName` and `lastName` columns.
     *
     * @var string
     */
    public $identifier;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['type', 'in', 'range' => array_values(User::TYPE)];
        $rules[] = ['status', 'in', 'range' => array_values(User::STATUS)];
        $rules[] = ['email', 'email'];
        $rules[] = [['firstName', 'lastName', 'identifier'], 'string', 'max' => 255];

        return $rules;
    }

    /**
     * @param  array [$params]
     * @return ActiveDataProvider
     */
    public function search(array $params = []): ActiveDataProvider
    {
        $query = $this->getQuery()->with(['settings']);

        $dataProvider = new ActiveDataProvider([
            'query'  => $query,
            'expand' => ['settings'],
        ]);

        // set up sorting
        $dataProvider->sort->defaultOrder = ['createdAt' => SORT_ASC];

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere(['like', User::tableName() . '.firstName', $this->firstName]);
        $query->andFilterWhere(['like', User::tableName() . '.lastName', $this->lastName]);
        $query->andFilterWhere([User::tableName() . '.email' => $this->email]);
        $query->andFilterWhere([User::tableName() . '.type' => $this->type]);
        $query->andFilterWhere([User::tableName() . '.status' => $this->status]);
        $query->andFilterWhere([
            'or',
            ['like', User::tableName() . '.email', $this->identifier],
            ['like', User::tableName() . '.firstName',$this->identifier],
            ['like', User::tableName() . '.lastName',$this->identifier],
        ]);
        $this->bindDateRangesToQuery(User::tableName());

        return $dataProvider;
    }
}
