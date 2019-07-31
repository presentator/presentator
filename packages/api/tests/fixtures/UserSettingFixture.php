<?php
namespace presentator\api\tests\fixtures;

use yii\test\ActiveFixture;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserSettingFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = 'presentator\api\models\UserSetting';

    /**
     * {@inheritdoc}
     */
    public $dataFile = '@app/tests/_data/UserSetting.php';
}
