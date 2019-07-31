<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\Hotspot';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/Hotspot.php';
}
