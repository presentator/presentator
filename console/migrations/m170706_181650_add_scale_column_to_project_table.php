<?php

use yii\db\Migration;

/**
 * Handles adding scale to table `project`.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m170706_181650_add_scale_column_to_project_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'scaleFactor', $this->float(1)->notNull()->defaultValue(1)->after('subtype'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%project}}', 'scaleFactor');
    }
}
