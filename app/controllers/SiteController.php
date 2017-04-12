<?php
namespace app\controllers;

use Yii;
use yii\base\Exception;
use yii\base\ErrorException;
use yii\web\BadRequestHttpException;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\PasswordResetForm;
use app\models\PasswordResetRequestForm;
use common\models\User;
use common\components\AuthHandler;

/**
 * Site controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class SiteController extends AppController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'][] = [
            'actions' => ['entrance', 'auth', 'activation', 'forgotten-password', 'reset-password', 'error'],
            'allow' => true,
        ];

        $behaviors['verbs']['actions'] = [
            'logout' => ['post'],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    /**
     * AuthAction success callback.
     * @param \yii\authclient\ClientInterface $client
     */
    public function onAuthSuccess($client)
    {
        (new AuthHandler($client))->handle();
    }


    /**
     * Renders error page.
     * @return string
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            $this->layout = 'clean';

            return $this->render('error', [
                'name'      => ($exception instanceof Exception || $exception instanceof ErrorException) ? $exception->getName() : Yii::t('app', 'Error'),
                'exception' => $exception,
                'message'   => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Displays homepage.
     * @return string
     */
    public function actionIndex()
    {
        $user     = Yii::$app->user->identity;
        $projects = $user->findProjects(10);
        $comments = $user->findLeavedScreenComments(30);

        if (empty($projects)) {
            $this->view->params['bodyClass'] = 'flex-page-content';
        }

        $projectIds      = ArrayHelper::getColumn($projects, 'id');
        $commentCounters = $user->countUnreadCommentsByProjects($projectIds);

        return $this->render('index', [
            'user'            => $user,
            'projects'        => $projects,
            'comments'        => $comments,
            'commentCounters' => $commentCounters,
        ]);
    }

    /**
     * Entrance action that handles both login and registration.
     * @return string
     */
    public function actionEntrance()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/index']);
        }

        $this->layout = 'clean';
        $this->view->params['bodyClass'] = 'full-page';
        $this->view->params['globalWrapperClass'] = 'auth-panel-wrapper';

        $loginForm    = new LoginForm();
        $registerForm = new RegisterForm();

        // each separate post data is prefixed with the appropriate form name
        // so we can use the `Model::load()` method to detect which form was actually submitted
        $postData = Yii::$app->request->post();
        if ($loginForm->load($postData)) {
            // login
            if ($loginForm->login()) {
                return $this->goBack();
            }
        } elseif ($registerForm->load($postData)) {
            // register
            if ($registerForm->register()) {
                Yii::$app->session->setFlash('registerSuccess');
            }
        }

        return $this->render('entrance', [
            'loginForm'    => $loginForm,
            'registerForm' => $registerForm,
        ]);
    }

    /**
     * Handles user account activation.
     * @return string
     */
    public function actionActivation($email, $token)
    {
        $user = User::findOne([
            'email'  => $email,
            'status' => User::STATUS_INACTIVE,
        ]);

        if (!$user ||                                                               // no user found
            !$user->validateActivationToken($token) ||                              // invalid token
            (($user->status = User::STATUS_ACTIVE) && !$user->save()) ||            // unable to update user status
            !Yii::$app->user->login($user, Yii::$app->params['rememberMeDuration']) // unable to login
        ) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'The activation token seems to be invalid or the account is already activated.'));
        } else {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Your account was activated successfully.'));
        }

        return $this->redirect(['site/index']);
    }

    /**
     * Password reset request action.
     * @return string
     */
    public function actionForgottenPassword()
    {
        $this->layout = 'clean';
        $this->view->params['bodyClass'] = 'full-page';
        $this->view->params['globalWrapperClass'] = 'auth-panel-wrapper';

        $model = new PasswordResetRequestForm;

        if ($model->load(Yii::$app->request->post()) && $model->enquirePasswordReset()) {
            Yii::$app->session->setFlash('enquirySuccess');
        }

        return $this->render('forgotten_password', [
            'model' => $model,
        ]);
    }

    /**
     * Password reset action.
     * @param  string $token Password reset token
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        $this->layout = 'clean';
        $this->view->params['bodyClass'] = 'full-page';
        $this->view->params['globalWrapperClass'] = 'auth-panel-wrapper';

        try {
            $model = new PasswordResetForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->reset($token)) {
            Yii::$app->session->setFlash('resetSuccess');
        }

        return $this->render('reset_password', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        Yii::$app->session->setFlash('info', Yii::t('app', 'You have successfully logout! We hope to see you again soon.'));

        return $this->redirect(['site/index']);
    }
}
