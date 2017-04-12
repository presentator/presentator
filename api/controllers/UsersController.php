<?php
namespace api\controllers;

use Yii;
use yii\filters\AccessControl;
use common\models\User;
use common\components\data\CActiveDataProvider;
use api\models\LoginForm;
use api\models\RegisterForm;
use api\models\UserForm;

/**
 * Users API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UsersController extends ApiController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = ['login', 'register'];

        return $behaviors;
    }

    /**
     * @api {POST} /users/login
     * 01. Login
     * @apiName login
     * @apiGroup Users
     * @apiDescription
     * Login a specific User model and generate new authentication token (set via `X-Access-Token` response header).
     *
     * @apiParam {String} email    User email address
     * @apiParam {String} password User password
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 1,
     *   "email": "test@presentator.io",
     *   "firstName": "Lorem",
     *   "lastName": "Ipsum",
     *   "status": 1,
     *   "createdAt": 1489244154,
     *   "updatedAt": 1489244169,
     *   "avatar": "http://app.presentator.op/uploads/users/c8f636f067f89cc148621e728d9d4c2c/avatar.jpg",
     *   "settings": {
     *     "language": "bg-BG",
     *     "notifications": true
     *   }
     * }
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Invalid login credentials.",
     *   "errors": {
     *     "password": "Invalid password."
     *   }
     * }
     */
    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            $user = $model->getUser();

            return $user->toArray([], ['settings']);
        }

        return $this->setErrorResponse(
            Yii::t('app', 'Invalid login credentials.'),
            $model->getFirstErrors()
        );
    }

    /**
     * Performs user model registration.
     * @return array
     */
    public function actionRegister()
    {
        $model = new RegisterForm();

        if ($model->load(Yii::$app->request->post(), '') &&
            ($user = $model->register())
        ) {
            return $user->toArray([], ['settings']);
        }

        return $this->setErrorResponse(
            Yii::t('app', 'Oops, an error occurred while processing your request.'),
            $model->getFirstErrors()
        );
    }

    /**
     * @api {PUT} /users/update
     * 02. Update authenticated user
     * @apiName update
     * @apiGroup Users
     * @apiDescription
     * Updates an authenticated `User` model.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {String}  [firstName]          User first name
     * @apiParam {String}  [lastName]           User last name
     * @apiParam {String}  [language]           User prefered language setting (current app language by default)
     * @apiParam {Boolean} [notifications]      User notifications setting for receiving emails on new leaved comment (`true` by default)
     * @apiParam {String}  [oldPassword]        User old password (**required** on user password change)
     * @apiParam {String}  [newPassword]        User new password (**required** on user password change)
     * @apiParam {String}  [newPasswordConfirm] User new password confirmation (**required** on user password change)
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 1,
     *   "email": "test@presentator.io",
     *   "firstName": "Lorem",
     *   "lastName": "Ipsum",
     *   "status": 1,
     *   "createdAt": 1489244154,
     *   "updatedAt": 1489244169,
     *   "avatar": "http://app.presentator.op/uploads/users/c8f636f067f89cc148621e728d9d4c2c/avatar.jpg",
     *   "settings": {
     *     "language": "bg-BG",
     *     "notifications": true
     *   }
     * }
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": {
     *     "newPassword": "New Password cannot be blank.",
     *     "newPasswordConfirm": "New Password Confirm cannot be blank."
     *   }
     * }
     */
    public function actionUpdate()
    {
        $user = Yii::$app->user->identity;
        $model = new UserForm($user);

        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            $user->refresh();

            return $user->toArray([], ['settings']);
        }

        return $this->setErrorResponse(
            Yii::t('app', 'Oops, an error occurred while processing your request.'),
            $model->getFirstErrors()
        );
    }
}
