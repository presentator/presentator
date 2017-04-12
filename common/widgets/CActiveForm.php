<?php
namespace common\widgets;

use yii\bootstrap\ActiveForm;

/**
 * Custom ActiveForm widget.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CActiveForm extends ActiveForm
{
    /**
     * inheritdoc
     */
    public $fieldClass = 'common\widgets\CActiveField';
}
