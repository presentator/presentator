<?php
namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Base controller that is intended to be inherited by all app controllers.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class AppController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    $this->redirect(['site/entrance']);
                }
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $request = Yii::$app->getRequest();
        if (!Yii::$app->user->isGuest) {
            $lang = substr(Yii::$app->user->identity->getSetting('language', 'en-US'), 0, 2);
        } else {
            $lang = $request->get('lang', 'en');
        }

        if (isset(Yii::$app->params['languages'][$lang])) {
            if ($lang !== $request->get('lang', null) && !$request->isAjax) {
                $this->redirect(Url::current(['lang' => $lang]));
            }

            Yii::$app->language = Yii::$app->params['languages'][$lang];
        }

        return parent::beforeAction($action);
    }
}
