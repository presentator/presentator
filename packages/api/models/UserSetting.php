<?php
namespace presentator\api\models;

use presentator\api\helpers\CastHelper;

/**
 * UserSetting AR model
 *
 * @property integer $id
 * @property integer $userId
 * @property string  $type
 * @property string  $name
 * @property string  $value
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserSetting extends ActiveRecord
{
    const NOTIFY_ON_EACH_COMMENT = 'notifyOnEachComment';
    const NOTIFY_ON_MENTION      = 'notifyOnMention';

    // types
    const TYPE = [
        'STRING'  => 'string',
        'BOOLEAN' => 'boolean',
        'INTEGER' => 'integer',
        'FLOAT'   => 'float',
        'ARRAY'   => 'array',
    ];

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // ensure that `value` is always stored as string
            if (!is_string($this->value)) {
                $this->setValue($this->value);
            }

            return true;
        }

        return false;
    }

    /**
     * Sets single model value.
     *
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = CastHelper::toString($value);
    }

    /**
     * Returns single casted setting model value.
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($this->type == static::TYPE['STRING']) {
            return CastHelper::toString($this->value);
        }

        if ($this->type == static::TYPE['INTEGER']) {
            return CastHelper::toInt($this->value);
        }

        if ($this->type == static::TYPE['FLOAT']) {
            return CastHelper::toFloat($this->value);
        }

        if ($this->type == static::TYPE['BOOLEAN']) {
            return CastHelper::toBool($this->value);
        }

        if ($this->type == static::TYPE['ARRAY']) {
            return CastHelper::toArray($this->value);
        }

        return null;
    }
}
