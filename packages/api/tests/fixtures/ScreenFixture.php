<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\Screen';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/Screen.php';
}
