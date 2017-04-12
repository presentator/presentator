<?php
namespace common\widgets;

use yii\bootstrap\ActiveField;

/**
 * Custom ActiveField widget.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CActiveField extends ActiveField
{
    /**
     * inheritdoc
     */
    public $checkboxTemplate = "{input}{label}{error}{hint}";

    /**
     * inheritdoc
     */
    public $radioTemplate = "{input}{label}{error}{hint}";
}
