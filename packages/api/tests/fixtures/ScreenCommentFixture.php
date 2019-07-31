<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\ScreenComment';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/ScreenComment.php';
}
