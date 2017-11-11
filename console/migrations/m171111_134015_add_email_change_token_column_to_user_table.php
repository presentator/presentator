<?php

use yii\db\Migration;

/**
 * Handles adding emailChangeToken to table `user`.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m171111_134015_add_email_change_token_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'emailChangeToken', $this->string()->unique()->after('passwordResetToken'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'emailChangeToken');
    }
}
