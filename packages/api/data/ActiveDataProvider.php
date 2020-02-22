<?php
namespace presentator\api\data;

/**
 * Custom active data provider that enhance the default yii one by
 * adding options to set manually `fields` and `expand` model fields.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
    use RestDataProviderTrait;
}
