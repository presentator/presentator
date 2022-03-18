<?php

use yii\db\Migration;

/**
 * Creates the basic application db schema.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m190417_133209_init extends Migration
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

        // User
        $this->createTable('{{%User}}', [
            'id'                 => $this->primaryKey(),
            'type'               => $this->string(15)->notNull()->defaultValue('regular'),
            'email'              => $this->string()->notNull(),
            'passwordHash'       => $this->string()->notNull(),
            'passwordResetToken' => $this->string(),
            'authKey'            => $this->string(32)->notNull(),
            'firstName'          => $this->string()->defaultValue(''),
            'lastName'           => $this->string()->defaultValue(''),
            'avatarFilePath'     => $this->string()->defaultValue(''),
            'status'             => $this->string(15)->notNull()->defaultValue('inactive'),
            'createdAt'          => $this->datetime(),
            'updatedAt'          => $this->datetime(),
        ], $tableOptions);
        $this->createIndex('idx_User_email', '{{%User}}', 'email', true);
        $this->createIndex('idx_User_authKey', '{{%User}}', 'authKey', true);
        $this->createIndex('idx_User_passwordResetToken', '{{%User}}', 'passwordResetToken', true);

        // UserSetting
        $this->createTable('{{%UserSetting}}', [
            'id'        => $this->primaryKey(),
            'userId'    => $this->integer()->notNull(),
            'type'      => $this->string(15)->notNull()->defaultValue('string'),
            'name'      => $this->string()->notNull()->defaultValue(''),
            'value'     => $this->text(),
            'createdAt' => $this->datetime(),
            'updatedAt' => $this->datetime(),
        ], $tableOptions);
        $this->createIndex('idx_UserSetting_userId_name', '{{%UserSetting}}', ['userId', 'name'], true);
        $this->addForeignKey('fk_UserSetting_to_User', '{{%UserSetting}}', 'userId', '{{%User}}', 'id', 'CASCADE', 'CASCADE');

        // UserAuth
        $this->createTable('{{%UserAuth}}', [
            'id'        => $this->primaryKey(),
            'userId'    => $this->integer()->notNull(),
            'source'    => $this->string()->notNull()->defaultValue(''),
            'sourceId'  => $this->text()->notNull(),
            'createdAt' => $this->datetime(),
            'updatedAt' => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_UserAuth_to_User', '{{%UserAuth}}', 'userId', '{{%User}}', 'id', 'CASCADE', 'CASCADE');

        // Project
        $this->createTable('{{%Project}}', [
            'id'        => $this->primaryKey(),
            'title'     => $this->string()->notNull()->defaultValue(''),
            'archived'  => $this->boolean()->defaultValue(false),
            'createdAt' => $this->datetime(),
            'updatedAt' => $this->datetime(),
        ], $tableOptions);

        // UserProjectRel
        $this->createTable('{{%UserProjectRel}}', [
            'id'        => $this->primaryKey(),
            'userId'    => $this->integer()->notNull(),
            'projectId' => $this->integer()->notNull(),
            'createdAt' => $this->datetime(),
            'updatedAt' => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_UserProjectRel_to_User', '{{%UserProjectRel}}', 'userId', '{{%User}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_UserProjectRel_to_Project', '{{%UserProjectRel}}', 'projectId', '{{%Project}}', 'id', 'CASCADE', 'CASCADE');

        // Prototype
        $this->createTable('{{%Prototype}}', [
            'id'          => $this->primaryKey(),
            'projectId'   => $this->integer()->notNull(),
            'title'       => $this->string()->notNull()->defaultValue(''),
            'type'        => $this->string(15)->notNull()->defaultValue('desktop'),
            'width'       => $this->float(1)->notNull()->defaultValue(0),
            'height'      => $this->float(1)->notNull()->defaultValue(0),
            'scaleFactor' => $this->float(1)->notNull()->defaultValue(1),
            'createdAt'   => $this->datetime(),
            'updatedAt'   => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_Prototype_to_Project', '{{%Prototype}}', 'projectId', '{{%Project}}', 'id', 'CASCADE', 'CASCADE');

        // ProjectLink
        $this->createTable('{{%ProjectLink}}', [
            'id'             => $this->primaryKey(),
            'projectId'      => $this->integer()->notNull(),
            'slug'           => $this->string(50)->notNull(),
            'passwordHash'   => $this->string(),
            'allowComments'  => $this->boolean()->defaultValue(true),
            'allowGuideline' => $this->boolean()->defaultValue(true),
            'createdAt'      => $this->datetime(),
            'updatedAt'      => $this->datetime(),
        ], $tableOptions);
        $this->createIndex('idx_ProjectLink_slug', '{{%ProjectLink}}', 'slug', true);
        $this->addForeignKey('fk_ProjectLink_to_Project', '{{%ProjectLink}}', 'projectId', '{{%Project}}', 'id', 'CASCADE', 'CASCADE');

        // ProjectLinkPrototypeRel
        $this->createTable('{{%ProjectLinkPrototypeRel}}', [
            'id'            => $this->primaryKey(),
            'projectLinkId' => $this->integer()->notNull(),
            'prototypeId'   => $this->integer()->notNull(),
            'createdAt'     => $this->datetime(),
            'updatedAt'     => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_ProjectLinkPrototypeRel_to_ProjectLink', '{{%ProjectLinkPrototypeRel}}', 'projectLinkId', '{{%ProjectLink}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_ProjectLinkPrototypeRel_to_Prototype', '{{%ProjectLinkPrototypeRel}}', 'prototypeId', '{{%Prototype}}', 'id', 'CASCADE', 'CASCADE');

        // Screen
        $this->createTable('{{%Screen}}', [
            'id'          => $this->primaryKey(),
            'prototypeId' => $this->integer()->notNull(),
            'order'       => $this->integer()->notNull()->defaultValue(0),
            'title'       => $this->string()->notNull()->defaultValue(''),
            'alignment'   => $this->string(15)->notNull()->defaultValue('center'),
            'background'  => $this->char(7)->notNull()->defaultValue('#ffffff'),
            'fixedHeader' => $this->float(1)->notNull()->defaultValue(0),
            'fixedFooter' => $this->float(1)->notNull()->defaultValue(0),
            'filePath'    => $this->string()->notNull()->defaultValue(''),
            'createdAt'   => $this->datetime(),
            'updatedAt'   => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_Screen_to_Prototype', '{{%Screen}}', 'prototypeId', '{{%Prototype}}', 'id', 'CASCADE', 'CASCADE');

        // ScreenComment
        $this->createTable('{{%ScreenComment}}', [
            'id'        => $this->primaryKey(),
            'replyTo'   => $this->integer(),
            'screenId'  => $this->integer()->notNull(),
            'from'      => $this->string()->notNull(),
            'message'   => $this->text()->notNull(),
            'left'      => $this->float(1)->notNull()->defaultValue(0),
            'top'       => $this->float(1)->notNull()->defaultValue(0),
            'status'    => $this->string(15)->notNull()->defaultValue('pending'),
            'createdAt' => $this->datetime(),
            'updatedAt' => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_ScreenComment_to_ScreenComment', '{{%ScreenComment}}', 'replyTo', '{{%ScreenComment}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_ScreenComment_to_Screen', '{{%ScreenComment}}', 'screenId', '{{%Screen}}', 'id', 'CASCADE', 'CASCADE');

        // UserScreenCommentRel
        $this->createTable('{{%UserScreenCommentRel}}', [
            'id'              => $this->primaryKey(),
            'userId'          => $this->integer()->notNull(),
            'screenCommentId' => $this->integer()->notNull(),
            'isRead'          => $this->boolean()->defaultValue(false)->comment('Indicates whether the user has read the related screen comment.'),
            'isProcessed'     => $this->boolean()->defaultValue(false)->comment('Indicates whether a notification email was sent to the user.'),
            'createdAt'       => $this->datetime(),
            'updatedAt'       => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_UserScreenCommentRel_to_User', '{{%UserScreenCommentRel}}', 'userId', '{{%User}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_UserScreenCommentRel_to_ScreenComment', '{{%UserScreenCommentRel}}', 'screenCommentId', '{{%ScreenComment}}', 'id', 'CASCADE', 'CASCADE');

        // HotspotTemplate
        $this->createTable('{{%HotspotTemplate}}', [
            'id'          => $this->primaryKey(),
            'prototypeId' => $this->integer()->notNull(),
            'title'       => $this->string()->notNull()->defaultValue(''),
            'createdAt'   => $this->datetime(),
            'updatedAt'   => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_HotspotTemplate_to_Prototype', '{{%HotspotTemplate}}', 'prototypeId', '{{%Prototype}}', 'id', 'CASCADE', 'CASCADE');

        // HotspotTemplateScreenRel
        $this->createTable('{{%HotspotTemplateScreenRel}}', [
            'id'                => $this->primaryKey(),
            'hotspotTemplateId' => $this->integer()->notNull(),
            'screenId'          => $this->integer()->notNull(),
            'createdAt'         => $this->datetime(),
            'updatedAt'         => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_HotspotTemplateScreenRel_to_HotspotTemplate', '{{%HotspotTemplateScreenRel}}', 'hotspotTemplateId', '{{%HotspotTemplate}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_HotspotTemplateScreenRel_to_Screen', '{{%HotspotTemplateScreenRel}}', 'screenId', '{{%Screen}}', 'id', 'CASCADE', 'CASCADE');

        // Hotspot
        $this->createTable('{{%Hotspot}}', [
            'id'                => $this->primaryKey(),
            'screenId'          => $this->integer(),
            'hotspotTemplateId' => $this->integer(),
            'type'              => $this->string(15)->notNull()->defaultValue('url'),
            'left'              => $this->float(1)->notNull()->defaultValue(0),
            'top'               => $this->float(1)->notNull()->defaultValue(0),
            'width'             => $this->float(1)->notNull()->defaultValue(0),
            'height'            => $this->float(1)->notNull()->defaultValue(0),
            'settings'          => $this->text(),
            'createdAt'         => $this->datetime(),
            'updatedAt'         => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_Hotspot_to_Screen', '{{%Hotspot}}', 'screenId', '{{%Screen}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_Hotspot_to_HotspotTemplate', '{{%Hotspot}}', 'hotspotTemplateId', '{{%HotspotTemplate}}', 'id', 'CASCADE', 'CASCADE');

        // GuidelineSection
        $this->createTable('{{%GuidelineSection}}', [
            'id'          => $this->primaryKey(),
            'projectId'   => $this->integer()->notNull(),
            'order'       => $this->integer()->notNull()->defaultValue(0),
            'title'       => $this->string()->notNull()->defaultValue(''),
            'description' => $this->string()->notNull()->defaultValue(''),
            'createdAt'   => $this->datetime(),
            'updatedAt'   => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_GuidelineSection_to_Project', '{{%GuidelineSection}}', 'projectId', '{{%Project}}', 'id', 'CASCADE', 'CASCADE');

        // GuidelineAsset
        $this->createTable('{{%GuidelineAsset}}', [
            'id'                 => $this->primaryKey(),
            'guidelineSectionId' => $this->integer()->notNull(),
            'type'               => $this->string(15)->notNull()->defaultValue('file'),
            'order'              => $this->integer()->notNull()->defaultValue(0),
            'hex'                => $this->char(7)->notNull()->defaultValue(''),
            'title'              => $this->string()->notNull()->defaultValue(''),
            'filePath'           => $this->string()->notNull()->defaultValue(''),
            'createdAt'          => $this->datetime(),
            'updatedAt'          => $this->datetime(),
        ], $tableOptions);
        $this->addForeignKey('fk_GuidelineAsset_to_GuidelineSection', '{{%GuidelineAsset}}', 'guidelineSectionId', '{{%GuidelineSection}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%GuidelineAsset}}');
        $this->dropTable('{{%GuidelineSection}}');
        $this->dropTable('{{%Hotspot}}');
        $this->dropTable('{{%HotspotTemplateScreenRel}}');
        $this->dropTable('{{%HotspotTemplate}}');
        $this->dropTable('{{%UserScreenCommentRel}}');
        $this->dropTable('{{%ScreenComment}}');
        $this->dropTable('{{%Screen}}');
        $this->dropTable('{{%ProjectLinkPrototypeRel}}');
        $this->dropTable('{{%ProjectLink}}');
        $this->dropTable('{{%Prototype}}');
        $this->dropTable('{{%UserProjectRel}}');
        $this->dropTable('{{%Project}}');
        $this->dropTable('{{%UserAuth}}');
        $this->dropTable('{{%UserSetting}}');
        $this->dropTable('{{%User}}');
    }
}
