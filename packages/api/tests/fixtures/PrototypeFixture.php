<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PrototypeFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\Prototype';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/Prototype.php';
}
