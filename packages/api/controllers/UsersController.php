<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\helpers\Inflector;
use presentator\api\helpers\CastHelper;
use presentator\api\models\User;
use presentator\api\models\forms\AuthClientAuthorizationForm;
use presentator\api\models\forms\LoginForm;
use presentator\api\models\forms\UserSearch;
use presentator\api\models\forms\UserCreateForm;
use presentator\api\models\forms\UserUpdateForm;
use presentator\api\models\forms\UserPasswordResetRequestForm;
use presentator\api\models\forms\UserPasswordResetForm;
use presentator\api\models\forms\UserEmailChangeRequestForm;
use presentator\api\models\forms\FeedbackForm;

/**
 * Users rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UsersController extends ApiController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            'options',
            'list-auth-methods',
            'list-auth-clients',
            'authorize-auth-client',
            'login',
            'activate',
            'register',
            'request-password-reset',
            'confirm-password-reset',
            'confirm-email-change',
        ];

        return $behaviors;
    }

    /**
     * Returns array list with the application configured auth methods and clients.
     *
     * @return array
     */
    public function actionListAuthMethods()
    {
        $result = [
            'emailPassword' => Yii::$app->params['emailPasswordAuth'],
            'clients' => [],
        ];

        $clients = AuthClientAuthorizationForm::getConfiguredAuthClients();

        // provide a state key that the consumer could use for manual request verification
        $state = md5(Yii::$app->security->generateRandomString(10));

        // additional clients auth url query parameters
        $authParams = array_filter([
            'state'        => $state,
            'redirect_uri' => Yii::$app->params['authClientRedirectUri'],
        ]);

        foreach ($clients as $key => $client) {
            // disable state param validations since we are not using sessions
            $client->validateAuthState = false;

            $result['clients'][] = [
                'name'    => $key,
                'title'   => Inflector::humanize($key),
                'state'   => $state,
                'authUrl' => $client->buildAuthUrl($authParams),
            ];
        }

        return $result;
    }

    /**
     * Returns array list with the application configured auth clients.
     *
     * @deprecated This action will be removed in 2.12.0!
     *             Please use `actionListAuthMethods` instead.
     *
     * @return array
     */
    public function actionListAuthClients()
    {
        $result = [];

        $clients = AuthClientAuthorizationForm::getConfiguredAuthClients();

        // provide a state key that the consumer could use for manual request verification
        $state = md5(Yii::$app->security->generateRandomString(10));

        // additional clients auth url query parameters
        $authParams = array_filter([
            'state'        => $state,
            'redirect_uri' => Yii::$app->params['authClientRedirectUri'],
        ]);

        foreach ($clients as $key => $client) {
            // disable state param validations since we are not using sessions
            $client->validateAuthState = false;

            $result[] = [
                'name'    => $key,
                'title'   => Inflector::humanize($key),
                'state'   => $state,
                'authUrl' => $client->buildAuthUrl($authParams),
            ];
        }

        return $result;
    }

    /**
     * Performs auth client user authorization.
     *
     * @return mixed
     */
    public function actionAuthorizeAuthClient()
    {
        $model = new AuthClientAuthorizationForm();

        $model->load(Yii::$app->request->post());

        if ($user = $model->authorize()) {
            return $this->authResponse($user);
        }

        return $this->sendErrorResponse(
            $model->getFirstErrors(),
            Yii::t('app', 'Failed to authorize.')
        );
    }

    /**
     * Performs active user login and generates new user authorization token.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (empty(Yii::$app->params['emailPasswordAuth'])) {
            return $this->sendErrorResponse([], Yii::t('app', 'Email/Password authorization is disabled.'));
        }

        $model = new LoginForm();

        $model->load(Yii::$app->request->post());

        if ($user = $model->login()) {
            return $this->authResponse($user);
        }

        return $this->sendErrorResponse([], Yii::t('app', 'Invalid login credentials.'));
    }

    /**
     * Register and create a new inactive User model.
     * The new created user still need to verify its email.
     *
     * @return mixed
     */
    public function actionRegister()
    {
        if (empty(Yii::$app->params['emailPasswordAuth'])) {
            return $this->sendErrorResponse([], Yii::t('app', 'Email/Password authorization is disabled.'));
        }

        $model = new UserCreateForm(['scenario' => UserCreateForm::SCENARIO_REGULAR]);

        $model->load(Yii::$app->request->post());

        if ($user = $model->save()) {
            Yii::$app->response->statusCode = 204;

            // the created user is inactive so there is no need to return anything
            return null;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Activates User model associated with the provided activation token.
     *
     * @return mixed
     */
    public function actionActivate()
    {
        try {
            $token = CastHelper::toString(Yii::$app->request->post('token'));
            $user  = User::activateByActivationToken($token);

            if ($user) {
                return $this->authResponse($user);
            }
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return $this->sendErrorResponse(
            ['token' => Yii::t('app', 'Invalid or expired token.')],
            Yii::t('app', 'Invalid or expired token.')
        );
    }

    /**
     * Sends a forgotten password email.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        if (empty(Yii::$app->params['emailPasswordAuth'])) {
            return $this->sendErrorResponse([], Yii::t('app', 'Email/Password authorization is disabled.'));
        }

        $model = new UserPasswordResetRequestForm();

        $model->load(Yii::$app->request->post());

        if ($model->send()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Resets the password for a user by a password reset token.
     *
     * @return mixed
     */
    public function actionConfirmPasswordReset()
    {
        if (empty(Yii::$app->params['emailPasswordAuth'])) {
            return $this->sendErrorResponse([], Yii::t('app', 'Email/Password authorization is disabled.'));
        }

        $model = new UserPasswordResetForm();

        $model->load(Yii::$app->request->post());

        if ($user = $model->save()) {
            return $this->authResponse($user);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Sends a request to change the authorized user email.
     *
     * @return mixed
     */
    public function actionRequestEmailChange()
    {
        if (empty(Yii::$app->params['emailPasswordAuth'])) {
            return $this->sendErrorResponse([], Yii::t('app', 'Email/Password authorization is disabled.'));
        }

        $model = new UserEmailChangeRequestForm(Yii::$app->user->identity);

        $model->load(Yii::$app->request->post());

        if ($model->send()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Changes the authorized user's email.
     *
     * @return mixed
     */
    public function actionConfirmEmailChange()
    {
        if (empty(Yii::$app->params['emailPasswordAuth'])) {
            return $this->sendErrorResponse([], Yii::t('app', 'Email/Password authorization is disabled.'));
        }

        try {
            $token = CastHelper::toString(Yii::$app->request->post('token'));
            $user  = User::changeEmailByEmailChangeToken($token);

            if ($user) {
                return $this->authResponse($user);
            }
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return $this->sendErrorResponse(
            ['token' => Yii::t('app', 'Invalid or expired token.')],
            Yii::t('app', 'Invalid or expired token.')
        );
    }

    /**
     * Sends a user's feedback to support.
     *
     * @return mixed
     */
    public function actionFeedback()
    {
        $model = new FeedbackForm();

        $model->load(Yii::$app->request->post());

        $model->from = Yii::$app->user->identity->email;

        if ($model->send()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns new refreshed user authorization token.
     *
     * @return array
     */
    public function actionRefresh()
    {
        $user = Yii::$app->user->identity;

        return $this->authResponse($user);
    }

    /**
     * Returns paginated users list (only for super users).
     *
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        if (!$user->isSuperUser()) {
            throw new ForbiddenHttpException('You are not allowed to list user accounts.');
        }

        $searchModel  = new UserSearch(User::find());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Returns single `User` models data.
     *
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionView($id)
    {
        $user = $this->findAccessableUserById((int) $id);

        return $user->toArray([], ['settings']);
    }

    /**
     * Create a new User model.
     * NB! Only super users/admins are allowed to create other accounts.
     *
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionCreate()
    {
        if (empty(Yii::$app->params['emailPasswordAuth'])) {
            return $this->sendErrorResponse([], Yii::t('app', 'Email/Password authorization is disabled.'));
        }

        $user = Yii::$app->user->identity;
        if (!$user->isSuperUser()) {
            throw new ForbiddenHttpException('You are not allowed to create user accounts.');
        }

        $model = new UserCreateForm(['scenario' => UserCreateForm::SCENARIO_SUPER]);

        $model->load(Yii::$app->request->post());

        if ($user = $model->save()) {
            return $user->toArray([], ['settings']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Handles User model updates.
     * NB! Only super users/admins are allowed to update other accounts.
     *
     * @param  integer $id ID of the user to update.
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        $user = $this->findAccessableUserById((int) $id);

        $model = new UserUpdateForm($user, [
            'scenario' => (Yii::$app->user->identity->isSuperUser() ? UserUpdateForm::SCENARIO_SUPER : UserUpdateForm::SCENARIO_REGULAR),
        ]);

        $model->load(Yii::$app->request->post());

        $model->avatar = UploadedFile::getInstanceByName('avatar');

        if ($user = $model->save()) {
            return $user->toArray([], ['settings']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Handles User model updates.
     * NB! Only super users/admins are allowed to update other accounts.
     *
     * @param  integer $id ID of the user to update.
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $user = $this->findAccessableUserById((int) $id);

        if ($user->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }

    /**
     * Returns single accessable user by its id.
     *
     * @param  int $id
     * @return User
     * @throws ForbiddenHttpException When regular authenticated user try to query other users.
     * @throws NotFoundHttpException  When user with `$id` doesn't exist.
     */
    protected function findAccessableUserById(int $id): User
    {
        $authUser = Yii::$app->user->identity;

        if ($authUser->id == $id) {
            return $authUser;
        }

        // only super users can query other users
        if (!$authUser->isSuperUser()) {
            throw new ForbiddenHttpException('You are not allowed to query other users.');
        }

        $user = User::findOne(['id' => $id]);
        if (!$user) {
            throw new NotFoundHttpException();
        }

        return $user;
    }

    /**
     * Returns an array with newly generated user's access token and account data.
     *
     * @param  User $user
     * @return array
     */
    protected function authResponse(User $user): array
    {
        return [
            'token' => $user->generateAccessToken(),
            'user'  => $user->toArray([], ['settings'])
        ];
    }
}
