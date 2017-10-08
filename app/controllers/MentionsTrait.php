<?php
namespace app\controllers;

use Yii;
use common\models\User;
use common\models\Project;

/**
 * MentionsTrait class that handles commonly used controller logic related to user mentions.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
trait MentionsTrait
{
    /**
     * Helper to returns formatted mentions list data from project commenters.
     *
     * @param  Project $project
     * @return array
     */
    protected function getMentionsList(Project $project)
    {
        $result     = [];
        $commenters = $project->findAllCommenters();
        $user       = Yii::$app->user->identity;

        foreach ($commenters as $commenter) {
            if ($user instanceof User && $commenter['email'] == $user->email) {
                continue; // skip
            }

            $name = trim($commenter['firstName'] . ' ' . $commenter['lastName']);

            $result[] = [
                'query' => $name ? sprintf('%s (%s)', $name, $commenter['email']) : $commenter['email'],
                'value' => $commenter['email'],
            ];
        }

        return $result;
    }
}
