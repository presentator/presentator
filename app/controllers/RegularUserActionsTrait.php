<?php
namespace app\controllers;

use Yii;
use yii\helpers\Html;
use yii\base\Model;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use app\models\UserProfileForm;
use app\models\UserPasswordForm;
use app\models\UserNotificationsForm;

/**
 * RegularUserActionsTrait class that handles the logged in user acount actions.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
trait RegularUserActionsTrait
{
    /**
     * Renders profile settings page.
     * @return string
     */
    public function actionSettings()
    {
        $user = Yii::$app->user->identity;

        $profileForm       = new UserProfileForm($user);
        $passwordForm      = new UserPasswordForm($user);
        $notificationsForm = new UserNotificationsForm($user);

        return $this->render('settings', [
            'user'              => $user,
            'profileForm'       => $profileForm,
            'passwordForm'      => $passwordForm,
            'notificationsForm' => $notificationsForm,
        ]);
    }

    /**
     * Returns model errors list indexed by input id.
     * @param  Model  $model
     * @return array
     */
    protected function getModelErrosList(Model $model)
    {
        $result = [];

        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }

        return $result;
    }

    /* Ajax form handlers
    --------------------------------------------------------------- */
    /**
     * Persists user notifications form update via ajax.
     * @return array
     */
    public function actionAjaxNotificationsSave()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user  = Yii::$app->user->identity;
        $model = new UserNotificationsForm($user);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'message' => Yii::t('app', 'Successfully updated notification settings.'),
            ];
        }

        return [
            'success' => false,
            'errors'  => $this->getModelErrosList($model),
        ];
    }

    /**
     * Persists user password form update via ajax.
     * @return array
     */
    public function actionAjaxPasswordSave()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user  = Yii::$app->user->identity;
        $model = new UserPasswordForm($user);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'message' => Yii::t('app', 'Successfully changed account password.'),
            ];
        }

        return [
            'success' => false,
            'errors'  => $this->getModelErrosList($model),
        ];
    }

    /**
     * Persists user profile form update via ajax.
     * @return array
     */
    public function actionAjaxProfileSave()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user  = Yii::$app->user->identity;
        $model = new UserProfileForm($user);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'message' => Yii::t('app', 'Successfully updated profile settings.'),
                'userIdentificator' => $user->getIdentificator(),
            ];
        }

        return [
            'success' => false,
            'errors'  => $this->getModelErrosList($model),
        ];
    }
}
