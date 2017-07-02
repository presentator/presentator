<?php

use yii\db\Migration;

/**
 * Handles adding title to table `version`.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m170701_113208_add_title_column_to_version_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%version}}', 'title', $this->string(100)->defaultValue(NULL)->after('projectId'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%version}}', 'title');
    }
}
