<?php

use yii\db\Migration;

/**
 * Handles adding status to table `screenComment`.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m170902_131953_add_status_column_to_screen_comment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%screenComment}}', 'status', $this->smallInteger()->notNull()->defaultValue(0)->after('posY'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%screenComment}}', 'status');
    }
}
