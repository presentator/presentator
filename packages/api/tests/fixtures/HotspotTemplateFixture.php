<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotTemplateFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\HotspotTemplate';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/HotspotTemplate.php';
}
