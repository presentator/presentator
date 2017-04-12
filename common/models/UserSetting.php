<?php
namespace common\models;

use common\components\helpers\CStringHelper;

/**
 * UserSetting AR model
 *
 * @property integer $id
 * @property integer $userId
 * @property string  $settingName
 * @property string  $settingValue
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserSetting extends CActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%userSetting}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }

    /**
     * Returns single setting value for a specific user.
     * NB! If the setting doesn't exist, it will return `$defaultValue`.
     * @see `CStringHelper::autoTypecast()`
     * @param  User   $user
     * @param  string $settingName
     * @param  mixed  $defaultValue
     * @return mixed
     */
    public static function getSettingByUser(User $user, $settingName, $defaultValue = null)
    {
        $model = static::find()
            ->where([
                'userId'      => $user->id,
                'settingName' => $settingName,
            ])
            ->one();

        if (!$model) {
            return $defaultValue;
        }

        return CStringHelper::autoTypecast($model->settingValue);
    }

    /**
     * Updates or creates a new UserSetting model.
     * @param  User   $user
     * @param  string $settingName
     * @param  mixed  $settingValue
     * @return boolean
     */
    public static function setSettingByUser(User $user, $settingName, $settingValue)
    {
        $model = static::find()
            ->where([
                'userId'      => $user->id,
                'settingName' => $settingName,
            ])
            ->one();

        if (!$model) {
            // create new record
            $model = new self();
            $model->userId      = $user->id;
            $model->settingName = $settingName;
        }

        if (is_array($settingValue)) {
            $model->settingValue = serialize($settingValue);
        } elseif (is_bool($settingValue) || $settingValue === null) {
            $model->settingValue = var_export($settingValue, true);
        } else {
            $model->settingValue = $settingValue;
        }

        return $model->save();
    }
}
