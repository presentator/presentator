<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectLinkPrototypeRelFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\ProjectLinkPrototypeRel';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/ProjectLinkPrototypeRel.php';
}
