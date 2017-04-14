<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use app\models\UserForm;
use app\models\AvatarForm;
use common\components\web\CUploadedFile;

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

        $userForm   = new UserForm($user);
        $avatarForm = new AvatarForm($user);

        if ($userForm->load(Yii::$app->request->post()) && $userForm->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully updated profile settings.'));

            // prevent resubmit
            return $this->redirect(['users/settings']);
        }

        return $this->render('settings', [
            'user'       => $user,
            'userForm'   => $userForm,
            'avatarForm' => $avatarForm,
        ]);
    }

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
            'message' => $model->getFirstError('avatar'),
        ];
    }

    /**
     * @todo separate action for thumbs
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
