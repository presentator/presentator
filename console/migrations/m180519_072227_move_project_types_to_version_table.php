<?php

use yii\db\Migration;
use common\models\Project;
use common\models\Version;

/**
 * Moves project types to version table.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m180519_072227_move_project_types_to_version_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // add version type table columns
        $this->addColumn('{{%version}}', 'type', $this->smallInteger()->notNull()->defaultValue(1)->after('title'));
        $this->addColumn('{{%version}}', 'subtype', $this->smallInteger()->after('type'));
        $this->addColumn('{{%version}}', 'scaleFactor', $this->float(1)->notNull()->defaultValue(1)->after('subtype'));

        // migrate project types to all its related version records
        foreach (Project::find()->with('versions')->each(150) as $project) {
            $result = true;

            foreach ($project->versions as $version) {
                $version->type        = $project->type;
                $version->subtype     = $project->subtype;
                $version->scaleFactor = $project->scaleFactor;

                $result = $result && $version->save();
            }

            if (!$result) {
                throw new \Exception('Unable to save project ' . $project->id);
            }
        }

        // delete transferred columns from the project table
        $this->dropColumn('{{%project}}', 'type');
        $this->dropColumn('{{%project}}', 'subtype');
        $this->dropColumn('{{%project}}', 'scaleFactor');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // add project type table columns
        $this->addColumn('{{%project}}', 'type', $this->smallInteger()->notNull()->defaultValue(1)->after('title'));
        $this->addColumn('{{%project}}', 'subtype', $this->smallInteger()->after('type'));
        $this->addColumn('{{%project}}', 'scaleFactor', $this->float(1)->notNull()->defaultValue(1)->after('subtype'));

        // migrate version types to its related project record
        foreach (Project::find()->with('versions')->each(150) as $project) {
            if (empty($project->versions)) {
                continue;
            }

            $firstVersion = $project->versions[0];

            $project->type        = $firstVersion->type;
            $project->subtype     = $firstVersion->subtype;
            $project->scaleFactor = $firstVersion->scaleFactor;

            if (!$project->save()) {
                throw new \Exception('Unable to save project ' . $project->id);
            }
        }

        // delete transferred columns from the version table
        $this->dropColumn('{{%version}}', 'type');
        $this->dropColumn('{{%version}}', 'subtype');
        $this->dropColumn('{{%version}}', 'scaleFactor');
    }
}
