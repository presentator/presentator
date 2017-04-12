<?php
namespace api\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\auth\CompositeAuth;
use common\components\HttpJwtAuth;

/**
 * Base API controller that is intended to be inherited by all api controllers.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public $serializer = '\common\components\rest\CSerializer';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpJwtAuth::className(),
            ],
            // NB! Use 'except' or 'only' to control the affected actions!
        ];

        return $behaviors;
    }

    /**
     * Sets and return error response.
     * @param  null|string $message
     * @param  array       $errorsс
     * @param  integer     $status
     * @return array
     */
    protected function setErrorResponse($message = null, $errors = [], $status = 400)
    {
        Yii::$app->response->statusCode = $status;

        $message = $message ? $message : 'Oops, an error occurred while processing your request.';

        return [
            'message' => $message,
            'errors'  => $errors,
        ];
    }

    /**
     * Sets and return "404 Not Found" error response.
     * @param  null|string $message
     * @return array
     */
    protected function setNotFoundResponse($message = null)
    {
        $message = $message ? $message : 'The item you are looking for does not exist or is temporary unavailable.';

        return $this->setErrorResponse($message, [], 404);
    }
}
