<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineAssetFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\GuidelineAsset';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/GuidelineAsset.php';
}
