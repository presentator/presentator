<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserProjectLinkRelFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\UserProjectLinkRel';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/UserProjectLinkRel.php';
}
