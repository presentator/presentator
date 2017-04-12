<?php
namespace common\models;

use yii\web\Linkable;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use common\components\helpers\CArrayHelper;

/**
 * Custom ActiveRecord class that implements commonly used methods and properties
 * and is intended to be used by all applications AR models.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
abstract class CActiveRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => 'updatedAt',
            ],
        ];
    }

    /* Workoround to eager load relations for already fetched models
    --------------------------------------------------------------- */
    /**
     * Eager loads relations to already fetched AR objects.
     * Very similar to `\yi\db\ActiveQuery::findWith()`.
     * @param  array $models Array with AR models.
     * @param  array $with   List of relations to load.
     * @return void
     */
    public static function eagerLoad(array $models, array $with)
    {
        if (empty($models)) {
            return;
        }

        $primaryModel = reset($models);

        $relations = self::normalizeRelations($primaryModel, $with);
        /* @var $relation ActiveQuery */
        foreach ($relations as $name => $relation) {
            $relation->populateRelation($name, $models);
        }
    }

    /**
     * @see `\yii\db\ActiveQueryTrait::normalizeRelations()`
     * @param  ActiveRecord $model
     * @param  array $with
     * @return ActiveQueryInterface[]
     */
    protected static function normalizeRelations($model, array $with)
    {
        $relations = [];
        foreach ($with as $name => $callback) {
            if (is_int($name)) {
                $name = $callback;
                $callback = null;
            }
            if (($pos = strpos($name, '.')) !== false) {
                // with sub-relations
                $childName = substr($name, $pos + 1);
                $name = substr($name, 0, $pos);
            } else {
                $childName = null;
            }

            if (!isset($relations[$name])) {
                $relation = $model->getRelation($name);
                $relation->primaryModel = null;
                $relations[$name] = $relation;
            } else {
                $relation = $relations[$name];
            }

            if (isset($childName)) {
                $relation->with[$childName] = $callback;
            } elseif ($callback !== null) {
                call_user_func($callback, $relation);
            }
        }

        return $relations;
    }

    /* ArrayableTrait overrides
    --------------------------------------------------------------- */
    /**
     * @inheritdoc
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = [];
        foreach ($this->resolveFields($fields, $expand) as $field => $definition) {
            $data[$field] = is_string($definition) ? $this->$definition : call_user_func($definition, $this, $field);
        }

        if ($this instanceof Linkable) {
            $data['_links'] = Link::serialize($this->getLinks());
        }

        $expands = $this->normalizeExpand($expand);
        return $recursive ? CArrayHelper::toArray($data, [], true, $expands) : $data;
    }

    /**
     * @inheritdoc
     */
    protected function resolveFields(array $fields, array $expand)
    {
        $result = [];

        foreach ($this->fields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }
            if (empty($fields) || in_array($field, $fields, true)) {
                $result[$field] = $definition;
            }
        }

        if (empty($expand)) {
            return $result;
        }

        $expands = $this->normalizeExpand($expand);
        foreach ($this->extraFields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }

            if (isset($expands[$field])) {
                $result[$field] = $definition;
            }
        }

        return $result;
    }

    /**
     * Normalize and filter expand values.
     * @param  array $expand
     * @return array
     */
    protected function normalizeExpand(array $expand)
    {
        $expands = [];

        foreach ($expand as $field) {
            $fields = explode('.', $field,2);
            $expands[$fields[0]][] = isset($fields[1]) ? $fields[1] : false;
        }

        foreach ($expands as $key => $value) {
            $expands[$key] = array_filter($value);
        }

        return $expands;
    }
}
