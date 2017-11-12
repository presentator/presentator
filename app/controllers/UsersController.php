<?php
namespace app\controllers;

use Yii;
use yii\helpers\Html;
use yii\base\Model;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use common\components\web\CUploadedFile;
use app\models\UserForm;
use app\models\AvatarForm;
use app\models\UserProfileForm;
use app\models\UserPasswordForm;
use app\models\UserNotificationsForm;

/**
 * Users controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UsersController extends AppController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs']['actions'] = [
            'ajax-notifications-save' => ['post'],
            'ajax-password-save'      => ['post'],
            'ajax-profile-save'       => ['post'],
            'ajax-temp-avatar-upload' => ['post'],
            'ajax-avatar-save'        => ['post'],
            'ajax-avatar-delete'      => ['post'],
        ];

        return $behaviors;
    }

    /**
     * Renders profile settings page.
     * @return string
     */
    public function actionSettings()
    {
        $user = Yii::$app->user->identity;

        $avatarForm = new AvatarForm($user);

        $profileForm       = new UserProfileForm($user);
        $passwordForm      = new UserPasswordForm($user);
        $notificationsForm = new UserNotificationsForm($user);

        return $this->render('settings', [
            'user'              => $user,
            'avatarForm'        => $avatarForm,
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

    /* Setting forms
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

    /* Avatar
    --------------------------------------------------------------- */
    /**
     * Uploads temp avatar via ajax.
     * @return array
     */
    public function actionAjaxTempAvatarUpload()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user          = Yii::$app->user->identity;
        $model         = new AvatarForm($user);
        $model->avatar = CUploadedFile::getInstance($model, 'avatar');

        if ($model->tempUpload()) {
            return [
                'success'       => true,
                'tempAvatarUrl' => $user->getTempAvatarUrl(),
            ];
        }

        return [
            'success' => false,
            'message' => $model->getFirstErrors('avatar'),
        ];
    }

    /**
     * Persists temp avatar image and generates its thumb via ajax.
     * @return array
     */
    public function actionAjaxAvatarSave()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;

        $crop   = Yii::$app->request->post('crop');
        $isTemp = (int) Yii::$app->request->post('isTemp', 0);

        if (!empty($crop)) {
            if ($isTemp && file_exists($user->getTempAvatarPath())) {
                rename($user->getTempAvatarPath(), $user->getAvatarPath());
            }

            // Crop and generate thumb
            $user->cropAvatar($crop);

            return [
                'success'        => true,
                'message'        => Yii::t('app', 'Successfully updated avatar.'),
                'avatarUrl'      => $user->getAvatarUrl(),
                'avatarThumbUrl' => $user->getAvatarUrl(true),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Deletes avatar and its thumb via ajax.
     * @return array
     */
    public function actionAjaxAvatarDelete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;

        $path      = $user->getAvatarPath();
        $thumbPath = $user->getAvatarPath(true);
        $tempPath  = $user->getTempAvatarPath(true);


        if (file_exists($path)) {
            @unlink($path);
        }

        if (file_exists($thumbPath)) {
            @unlink($thumbPath);
        }

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return [
            'success' => true,
            'message' => Yii::t('app', 'Successfully removed avatar image.'),
        ];
    }
}
