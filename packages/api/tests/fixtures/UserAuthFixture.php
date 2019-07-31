<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserAuthFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\UserAuth';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/UserAuth.php';
}
