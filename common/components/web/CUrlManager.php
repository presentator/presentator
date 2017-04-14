<?php
namespace common\components\web;

use Yii;
use yii\web\UrlManager;
use common\components\helpers\GeoIPHelper;

/**
 * Extends the default Yii2 UrlManager class.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CUrlManager extends UrlManager
{
    /**
     * @inheritdoc
     */
    public function createUrl($params)
    {
        // manually add lang param to the url
        if (!isset($params['lang'])) {
            $lang = Yii::$app->request->get('lang', null);

            if (!$lang) {
                $lang = GeoIPHelper::detectLanguageCode();
            }

            $params['lang'] = $lang;
        }

        return parent::createUrl($params);
    }
}
