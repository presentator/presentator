<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserProjectRelFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\UserProjectRel';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/UserProjectRel.php';
}
