<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectLinkFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\ProjectLink';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/ProjectLink.php';
}
