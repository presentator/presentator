<?php

use yii\db\Migration;

/**
 * Adds `pinned` column to the `Project` table.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m210220_154329_add_project_pinned_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%Project}}',
            'pinned',
            $this->boolean()->defaultValue(0)->after("archived")
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%Project}}', 'pinned');
    }
}
