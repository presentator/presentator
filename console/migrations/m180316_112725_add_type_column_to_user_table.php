<?php

use yii\db\Migration;

/**
 * Handles adding type to table `user`.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m180316_112725_add_type_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'type', $this->smallInteger()->notNull()->defaultValue(0)->after('status'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'type');
    }
}
