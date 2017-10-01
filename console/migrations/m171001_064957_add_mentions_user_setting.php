<?php

use yii\db\Migration;
use common\models\User;

/**
 * Adds "mentions" users setting.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m171001_064957_add_mentions_user_setting extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $createdAt = time();
        $updatedAt = time();
        $settings  = [];

        foreach (User::find()->each(100) as $user) {
            $settings[] = [
                'userId'       => $user->id,
                'settingName'  => 'mentions',
                'settingValue' => 'true',
                'createdAt'    => $createdAt,
                'updatedAt'    => $updatedAt,
            ];
        }

        $this->batchInsert(
            '{{%userSetting}}',
            ['userId', 'settingName', 'settingValue', 'createdAt', 'updatedAt'],
            $settings
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%userSetting}}', ['settingName' => 'mentions']);
    }
}
