<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineSectionFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\GuidelineSection';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/GuidelineSection.php';
}
