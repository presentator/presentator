<?php

use yii\db\Migration;

/**
 * Creates `UserProjectLinkRel` table.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m200221_193516_add_user_project_link_rel_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%UserProjectLinkRel}}', [
            'id'            => $this->primaryKey(),
            'userId'        => $this->integer()->notNull(),
            'projectLinkId' => $this->integer()->notNull(),
            'createdAt'     => $this->datetime(),
            'updatedAt'     => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_UserProjectLinkRel_to_User', '{{%UserProjectLinkRel}}', 'userId', '{{%User}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_UserProjectLinkRel_to_ProjectLink', '{{%UserProjectLinkRel}}', 'projectLinkId', '{{%ProjectLink}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%UserProjectLinkRel}}');
    }
}
