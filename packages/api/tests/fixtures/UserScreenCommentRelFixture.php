<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserScreenCommentRelFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\UserScreenCommentRel';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/UserScreenCommentRel.php';
}
