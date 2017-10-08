<?php

use yii\db\Migration;

/**
 * Handles the creation of table `emailQueue`.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m170926_163027_create_mail_queue_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%mailQueue}}', [
            'id'        => $this->bigPrimaryKey(),
            'from'      => $this->string(),
            'to'        => $this->string()->notNull(),
            'cc'        => $this->string(),
            'bcc'       => $this->string(),
            'subject'   => $this->string()->notNull(),
            'body'      => $this->text()->notNull(),
            'status'    => $this->smallInteger()->notNull()->defaultValue(0),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%mailQueue}}');
    }
}
