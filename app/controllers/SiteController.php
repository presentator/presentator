<?php
namespace app\controllers;

use Yii;
use yii\base\Exception;
use yii\base\ErrorException;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\db\IntegrityException;
use yii\web\BadRequestHttpException;
use yii\helpers\ArrayHelper;
use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\PasswordResetForm;
use app\models\PasswordResetRequestForm;
use common\models\User;
use common\components\AuthHandler;
use common\components\helpers\CArrayHelper;

/**
 * Site controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class SiteController extends AppController
{
    const SESSION_LOGIN_ATTEMPTS_KEY = 'wrongLoginAttempts';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'][] = [
            'actions' => [
                'entrance',
                'auth',
                'activation',
                'forgotten-password',
                'reset-password',
                'change-email',
                'error',
            ],
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

        if ($exception === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $this->layout = 'clean';

        return $this->render('error', [
            'name'    => ($exception instanceof Exception || $exception instanceof ErrorException) ? $exception->getName() : Yii::t('app', 'Error'),
            'message' => $exception->getMessage(),
        ]);
    }

    /**
     * Displays homepage.
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/entrance']);
        }

        $user            = Yii::$app->user->identity;
        $projects        = $user->findProjects(9, 0, true);
        $comments        = $user->findLeavedScreenComments(30, 0, true);
        $commentCounters = $user->countUnreadCommentsByProjects(ArrayHelper::getColumn($projects, 'id'));

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

        $hasFbConfig        = CArrayHelper::hasNonEmptyValues(['facebookAuth.clientId', 'facebookAuth.clientSecret']);
        $hasGoogleConfig    = CArrayHelper::hasNonEmptyValues(['googleAuth.clientId', 'googleAuth.clientSecret']);
        $hasReCaptchaConfig = CArrayHelper::hasNonEmptyValues(['recaptcha.siteKey', 'recaptcha.secretKey']);

        $isLoginAttemp      = true;
        $loginForm          = new LoginForm();
        $registerForm       = new RegisterForm();
        $wrongLoginAttempts = Yii::$app->session->get(self::SESSION_LOGIN_ATTEMPTS_KEY, 0);

        // each separate post data is prefixed with the appropriate form name
        // so we can use the `Model::load()` method to detect which form was actually submitted
        $postData = Yii::$app->request->post();
        if ($loginForm->load($postData)) {
            // login
            if ($loginForm->login()) {
                Yii::$app->session->remove(self::SESSION_LOGIN_ATTEMPTS_KEY);
                return $this->goBack();
            }

            Yii::$app->session->set(self::SESSION_LOGIN_ATTEMPTS_KEY, ++$wrongLoginAttempts);
        } elseif ($registerForm->load($postData)) {
            $isLoginAttemp = false;

            // register
            if ($registerForm->register()) {
                Yii::$app->session->setFlash('registerSuccess');
            }
        }

        // show recaptcha on too many wrong login attempts
        if ($wrongLoginAttempts >= 3 && $hasReCaptchaConfig) {
            $loginForm->scenario = LoginForm::SCENARIO_RECAPTCHA;
        }

        return $this->render('entrance', [
            'isLoginAttemp'      => $isLoginAttemp,
            'loginForm'          => $loginForm,
            'registerForm'       => $registerForm,
            'hasFbConfig'        => $hasFbConfig,
            'hasGoogleConfig'    => $hasGoogleConfig,
            'hasReCaptchaConfig' => $hasReCaptchaConfig,
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
     * Change user email action.
     * @param  string $email New email to set
     * @param  string $token Email change token
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionChangeEmail($email, $token)
    {
        $user = User::findByEmailChangeToken($token);

        try {
            if (!$user || !$user->changeEmail($email)) {
                throw new BadRequestHttpException('Invalid or expired email change token.');
            }
        } catch (IntegrityException $e) {
            throw new BadRequestHttpException('The email ' . $email . ' seems to be already registered.');
        }

        Yii::$app->session->setFlash('success', Yii::t('app', 'Your email address was successfully updated.'));

        return $this->redirect(['site/index']);
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
