<?php
use yii\db\Migration;

/**
 * Creates the basic application db schema.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m130524_201442_init extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // User
        $this->createTable('{{%user}}', [
            'id'                 => $this->primaryKey(),
            'email'              => $this->string()->notNull()->unique(),
            'firstName'          => $this->string(),
            'lastName'           => $this->string(),
            'authKey'            => $this->string(32)->notNull(),
            'passwordHash'       => $this->string()->notNull(),
            'passwordResetToken' => $this->string()->unique(),
            'status'             => $this->smallInteger()->notNull()->defaultValue(0),
            'createdAt'          => $this->integer()->notNull(),
            'updatedAt'          => $this->integer()->notNull(),
        ], $tableOptions);

        // User settings
        $this->createTable('{{%userSetting}}', [
            'id'           => $this->primaryKey(),
            'userId'       => $this->integer()->notNull(),
            'settingName'  => $this->string()->notNull(),
            'settingValue' => $this->text(),
            'createdAt'    => $this->integer()->notNull(),
            'updatedAt'    => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_setting_to_user', '{{%userSetting}}', 'userId', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        // User authclient for 3rd-party login
        $this->createTable('{{%userAuth}}', [
            'id'        => $this->primaryKey(),
            'userId'    => $this->integer()->notNull(),
            'source'    => $this->string()->notNull(),
            'sourceId'  => $this->text()->notNull(),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_auth_to_user', '{{%userAuth}}', 'userId', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        // Project
        $this->createTable('{{%project}}', [
            'id'           => $this->primaryKey(),
            'title'        => $this->string(),
            'type'         => $this->smallInteger()->notNull()->defaultValue(1),
            'subtype'      => $this->smallInteger(),
            'passwordHash' => $this->string(),
            'createdAt'    => $this->integer()->notNull(),
            'updatedAt'    => $this->integer()->notNull(),
        ], $tableOptions);

        // User-Project relation
        $this->createTable('{{%userProjectRel}}', [
            'id'        => $this->primaryKey(),
            'userId'    => $this->integer()->notNull(),
            'projectId' => $this->integer()->notNull(),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_upr_to_user', '{{%userProjectRel}}', 'userId', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_upr_to_project', '{{%userProjectRel}}', 'projectId', '{{%project}}', 'id', 'CASCADE', 'CASCADE');

        // Project access
        $this->createTable('{{%projectPreview}}', [
            'id'        => $this->primaryKey(),
            'projectId' => $this->integer()->notNull(),
            'slug'      => $this->string(12)->notNull()->unique(),
            'type'      => $this->smallInteger()->notNull()->defaultValue(1),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_project_preview_to_project', '{{%projectPreview}}', 'projectId', '{{%project}}', 'id', 'CASCADE', 'CASCADE');

        // Version
        $this->createTable('{{%version}}', [
            'id'        => $this->primaryKey(),
            'projectId' => $this->integer()->notNull(),
            'order'     => $this->integer()->notNull()->defaultValue(0),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_version_to_project', '{{%version}}', 'projectId', '{{%project}}', 'id', 'CASCADE', 'CASCADE');

        // Screen
        $this->createTable('{{%screen}}', [
            'id'         => $this->primaryKey(),
            'versionId'  => $this->integer()->notNull(),
            'title'      => $this->string(),
            'hotspots'   => $this->text(),
            'order'      => $this->integer()->notNull(),
            'alignment'  => $this->smallInteger()->notNull()->defaultValue(0),
            'background' => $this->char(7),
            'imageUrl'   => $this->string()->notNull(),
            'createdAt'  => $this->integer()->notNull(),
            'updatedAt'  => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_screen_to_version', '{{%screen}}', 'versionId', '{{%version}}', 'id', 'CASCADE', 'CASCADE');

        // Screen comment
        $this->createTable('screenComment', [
            'id'        => $this->primaryKey(),
            'replyTo'   => $this->integer()->defaultValue(NULL),
            'screenId'  => $this->integer()->notNull(),
            'from'      => $this->string()->notNull(),
            'message'   => $this->text()->notNull(),
            'posX'      => $this->integer()->notNull()->defaultValue(0),
            'posY'      => $this->integer()->notNull()->defaultValue(0),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_screen_comment_to_screen', '{{%screenComment}}', 'screenId', '{{%screen}}', 'id', 'CASCADE', 'CASCADE');

        // User-Screen comment relation
        $this->createTable('userScreenCommentRel', [
            'id'              => $this->primaryKey(),
            'userId'          => $this->integer()->notNull(),
            'screenCommentId' => $this->integer()->notNull(),
            'isRead'          => $this->smallInteger()->notNull()->defaultValue(0),
            'createdAt'       => $this->integer()->notNull(),
            'updatedAt'       => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_uscr_to_user', '{{%userScreenCommentRel}}', 'userId', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_uscr_to_screen_comment', '{{%userScreenCommentRel}}', 'screenCommentId', '{{%screenComment}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_uscr_to_user', '{{%userScreenCommentRel}}');
        $this->dropForeignKey('fk_uscr_to_screen_comment', '{{%userScreenCommentRel}}');
        $this->dropForeignKey('fk_screen_comment_to_screen', '{{%screenComment}}');
        $this->dropForeignKey('fk_screen_to_version', '{{%screen}}');
        $this->dropForeignKey('fk_project_preview_to_project', '{{%projectPreview}}');
        $this->dropForeignKey('fk_upr_to_user', '{{%userProjectRel}}');
        $this->dropForeignKey('fk_upr_to_project', '{{%userProjectRel}}');
        $this->dropForeignKey('fk_version_to_project', '{{%version}}');
        $this->dropForeignKey('fk_setting_to_user', '{{%userSetting}}');
        $this->dropForeignKey('fk_auth_to_user', '{{%userAuth}}');
        $this->dropTable('{{%userScreenCommentRel}}');
        $this->dropTable('{{%screenComment}}');
        $this->dropTable('{{%screen}}');
        $this->dropTable('{{%version}}');
        $this->dropTable('{{%projectPreview}}');
        $this->dropTable('{{%userProjectRel}}');
        $this->dropTable('{{%project}}');
        $this->dropTable('{{%user}}');
        $this->dropTable('{{%userSetting}}');
        $this->dropTable('{{%userAuth}}');
    }
}
