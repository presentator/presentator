<?php
namespace presentator\api\data;

/**
 * Custom array data provider that enhance the default yii one by
 * adding options to set manually `fields` and `expand` model fields.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ArrayDataProvider extends \yii\data\ArrayDataProvider
{
    use RestDataProviderTrait;
}
