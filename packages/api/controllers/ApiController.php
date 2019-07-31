<?php
namespace presentator\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\auth\CompositeAuth;
use presentator\api\filters\HttpJwtAuth;

/**
 * Base API controller that is intended to be inherited by all other api controllers.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ApiController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $serializer = 'presentator\api\rest\Serializer';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class'  => HttpJwtAuth::class,
            'except' => ['options'],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        $actions['options'] = [
            'class' => 'yii\rest\OptionsAction',
        ];

        return $actions;
    }

    /**
     * Sets and return error response.
     *
     * @param  string  [$message]
     * @param  array   [$errors]
     * @param  integer $status
     * @return array
     */
    protected function sendErrorResponse(array $errors = [], string $message = '', int $status = 400): array
    {
        Yii::$app->response->statusCode = $status;

        $message = $message ? $message : Yii::t('app', 'Oops, an error occurred while processing your request.');

        return [
            'message' => $message,
            'errors'  => (object) $errors, // cast to object to ensure that the errors value will be always seriazed to json object
        ];
    }
}
