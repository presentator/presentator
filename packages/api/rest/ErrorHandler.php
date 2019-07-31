<?php
namespace presentator\api\rest;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Custom error handler to normalize the API error response output.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
    /**
     * {@inheritdoc}
     */
    protected function convertExceptionToArray($exception)
    {
        if ($exception instanceof NotFoundHttpException) {
            $defaultMessage = Yii::t('app', 'The resource you are looking for does not exist or is temporary unavailable.');
        } elseif ($exception instanceof ForbiddenHttpException) {
            $defaultMessage = Yii::t('app', 'You are not allowed to perform this request.');
        } else {
            $defaultMessage = Yii::t('app', 'Oops, an error occurred while processing your request.');
        }

        $message = YII_DEBUG ? $exception->getMessage() : '';
        $message = $message ?: $defaultMessage;

        return [
            'message' => $message,
            'errors'  => (object) [],
        ];
    }
}
