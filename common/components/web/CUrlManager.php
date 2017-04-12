<?php
namespace common\components\web;

use Yii;
use yii\web\UrlManager;

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
            $params['lang'] = Yii::$app->request->get('lang', 'en');
        }

        return parent::createUrl($params);
    }
}
