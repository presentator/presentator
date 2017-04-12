<?php
namespace api\components;

use yii\web\ErrorHandler;
use yii\web\UnauthorizedHttpException;

/**
 * Custom error handler to normalize the API error response output.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ApiErrorHandler extends ErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function convertExceptionToArray($exception)
    {
        return [
            'message' => $exception->getMessage(),
            'errors'  => [],
        ];
    }
}
