<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotTemplateScreenRelFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\HotspotTemplateScreenRel';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/HotspotTemplateScreenRel.php';
}
