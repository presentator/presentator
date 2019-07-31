<?php
namespace presentator\api\data;

/**
 * Custom array data provider that enhance the default yii one by
 * adding options to set manually fields/expand model options and other stuffs.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ArrayDataProvider extends \yii\data\ArrayDataProvider
{
    /**
     * @var array
     */
    public $fields = [];

    /**
     * @var array
     */
    public $expand = [];
}
