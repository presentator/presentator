<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\data\Pagination;
use common\models\User;
use common\components\web\CUploadedFile;
use app\models\AvatarForm;
use app\models\SuperUserForm;

/**
 * Users controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UsersController extends AppController
{
    use SuperUserActionsTrait;
    use RegularUserActionsTrait;

    const ITEMS_PER_PAGE     = 20;
    const MAX_SEARCH_RESULTS = 50;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'actions' => [
                    'ajax-temp-avatar-upload',
                    'ajax-avatar-save',
                    'ajax-avatar-delete',
                ],
                'allow' => true,
                'roles' => ['@'],
            ],
            [
                'actions' => [
                    'settings',
                    'ajax-notifications-save',
                    'ajax-password-save',
                    'ajax-profile-save',
                    'ajax-temp-avatar-upload',
                    'ajax-avatar-save',
                    'ajax-avatar-delete',
                ],
                'allow' => true,
                'roles' => ['@'],
                'matchCallback' => function ($rule, $action) {
                    return Yii::$app->user->identity->type != User::TYPE_SUPER;
                },
            ],
            [
                'actions' => [
                    'index',
                    'ajax-search-users',
                    'create',
                    'update',
                    'delete',
                ],
                'allow' => true,
                'roles' => ['@'],
                'matchCallback' => function ($rule, $action) {
                    return Yii::$app->user->identity->type == User::TYPE_SUPER;
                },
            ],
        ];

        $behaviors['verbs']['actions'] = [
            'delete'                  => ['post'],
            'ajax-notifications-save' => ['post'],
            'ajax-password-save'      => ['post'],
            'ajax-profile-save'       => ['post'],
            'ajax-temp-avatar-upload' => ['post'],
            'ajax-avatar-save'        => ['post'],
            'ajax-avatar-delete'      => ['post'],
        ];

        return $behaviors;
    }

    /* Avatar
    --------------------------------------------------------------- */
    /**
     * Uploads temp avatar via ajax.
     * @param  null|integer $id
     * @return array
     */
    public function actionAjaxTempAvatarUpload($id = null)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = $this->resolveAvatarUser($id);

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
     * @param  null|integer $id
     * @return array
     */
    public function actionAjaxAvatarSave($id = null)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = $this->resolveAvatarUser($id);

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
     * @param  null|integer $id
     * @return array
     */
    public function actionAjaxAvatarDelete($id = null)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = $this->resolveAvatarUser($id);

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

    /**
     * @param  null|integer $id
     * @return \common\models\User
     */
    protected function resolveAvatarUser($id = null) {
        $currentUser = Yii::$app->user->identity;

        if ($id && $currentUser->type == User::TYPE_SUPER) {
            $user = User::findOne($id);

            if ($user) {
                return $user;
            }
        }

        return $currentUser;
    }
}
