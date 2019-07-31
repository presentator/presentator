<?php
namespace presentator\api\models\forms;

use yii\base\Model;

/**
 * Base Form class intented to be inherited by all other api form models.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
abstract class ApiForm extends Model
{
    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return '';
    }
}
