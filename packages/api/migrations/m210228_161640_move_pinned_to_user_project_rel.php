<?php

use yii\db\Migration;
use presentator\api\models\Project;

/**
 * Move the project pinned column to the `UserProjectRel`table.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m210228_161640_move_pinned_to_user_project_rel extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%UserProjectRel}}',
            'pinned',
            $this->boolean()->defaultValue(false)->after("projectId")
        );

        foreach (Project::find()->with('userProjectRels')->each() as $project) {
            if (!$project->pinned || count($project->userProjectRels) > 1) {
                // skip unpinned or projects with more than one admin
                continue;
            }

            foreach ($project->userProjectRels as $rel) {
                $rel->pinned = $project->pinned;
                $rel->save();
            }
        }

        $this->dropColumn('{{%Project}}', 'pinned');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%UserProjectRel}}', 'pinned');

        $this->addColumn(
            '{{%Project}}',
            'pinned',
            $this->boolean()->defaultValue(false)->after("archived")
        );
    }
}
