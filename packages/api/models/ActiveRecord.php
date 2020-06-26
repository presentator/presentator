<?php
namespace presentator\api\models;

use Yii;
use yii\helpers\StringHelper;
use yii\behaviors\TimestampBehavior;
use presentator\api\behaviors\WebhooksBehavior;


/**
 * Custom ActiveRecord class that implements commonly used methods and properties
 * and is intended to be used by all applications AR models.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%' . StringHelper::basename(get_called_class()) . '}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class'              => TimestampBehavior::class,
            'createdAtAttribute' => 'createdAt',
            'updatedAtAttribute' => 'updatedAt',
            'value'              => function () { return date('Y-m-d H:i:s'); },
        ];

        if (Yii::$app->has('webhooks')) {
            $behaviors['webhooks'] = array_merge(
                Yii::$app->webhooks->getDefinition(get_called_class()),
                ['class' => WebhooksBehavior::class]
            );
        }

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function transactions()
    {
        $transactions = parent::transactions();

        // always run `delete()` in a transaction
        $transactions[self::SCENARIO_DEFAULT] = self::OP_DELETE;

        return $transactions;
    }

    /**
     * Similar to `BaseActiveRecord::link()` but checks whether the model is not already linked.
     *
     * @see `BaseActiveRecord::link()`
     * @param string                $name         The case sensitive name of the relationship, e.g. `orders` for a relation defined via `getOrders()` method.
     * @param ActiveRecordInterface $model        The model to be linked with the current one.
     * @param array                 $extraColumns Additional column values to be saved into the junction table.
     *                                            This parameter is only meaningful for a relationship involving a junction table
     *                                            (i.e., a relation set with [[ActiveRelationTrait::via()]] or [[ActiveQuery::viaTable()]].)
     * @throws InvalidCallException If the method is unable to link two models.
     */
    public function linkOnce($name, $model, $extraColumns = []): void
    {
        $exists = $this->{'get' . ucfirst($name)}()
            ->andWhere([$model::tableName() . '.id' => $model->id])
            ->exists();

        if (!$exists) {
            $this->link($name, $model, $extraColumns);
        }
    }

    /**
     * Returns single model by its id.
     *
     * @param  integer $id        ID of the model to return.
     * @param  array   [$filters] Additional conditions to apply to the query.
     * @return null|ActiveRecord
     */
    public static function findById(int $id, array $filters = [])
    {
        return static::find()
            ->where(['id' => $id])
            ->andFilterWhere($filters)
            ->one();
    }

    /* Eager load relations for already loaded models
    --------------------------------------------------------------- */
    /**
     * Eager loads relations to already fetched AR objects.
     * Similar to `\yi\db\ActiveQuery::findWith()`.
     *
     * @param  array   $models Array with AR models.
     * @param  array   $with   List of relations to load.
     * @return void
     */
    public static function eagerLoad(array $models, array $with)
    {
        if (empty($models)) {
            return;
        }

        $primaryModel = reset($models);

        $relations = static::normalizeRelations($primaryModel, $with);

        /* @var $relation ActiveQuery */
        foreach ($relations as $name => $relation) {
            $relation->populateRelation($name, $models);
        }
    }

    /**
     * @see `\yii\db\ActiveQueryTrait::normalizeRelations()`
     * @param  ActiveRecord $model
     * @param  array        $with
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
}
