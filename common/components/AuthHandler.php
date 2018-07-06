<?php
namespace common\components;

use Yii;
use yii\helpers\ArrayHelper;
use yii\authclient\ClientInterface;
use common\models\UserAuth;
use common\models\User;

/**
 * Handles successful authentication via Yii auth component.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class AuthHandler
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * Component constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Login/Register user via auth component.
     * @return mixed
     */
    public function handle()
    {
        $attributes = $this->client->getUserAttributes();
        $email      = ArrayHelper::getValue($attributes, 'email');
        $id         = ArrayHelper::getValue($attributes, 'id');

        if (empty($email)) {
            return false;
        }

        $auth = UserAuth::findOne([
            'source'   => $this->client->getId(),
            'sourceId' => $id,
        ]);

        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                Yii::$app->user->login($auth->user, Yii::$app->params['rememberMeDuration']);
            } else { // register
                $transaction = User::getDb()->beginTransaction();

                $user = User::findOne(['email' => $email]);
                if ($user && $user->status == User::STATUS_INACTIVE) {
                    // update existing user status (if necessary)
                    $user->status = User::STATUS_ACTIVE;
                    if (!$user->save()) {
                        Yii::$app->session->setFlash('error',
                            Yii::t('app', 'Unable to save user: {errors}', [
                                'errors' => implode('<br>', $user->getFirstErrors()),
                            ])
                        );

                        return false;
                    }

                    Yii::$app->session->setFlash('success',
                        Yii::t('app', 'You have successfully activated your account via {client}.', [
                            'client' => $this->client->getTitle(),
                        ])
                    );
                } elseif (!$user) {
                    // create new user
                    $user = new User();
                    $user->email  = $email;
                    $user->status = User::STATUS_ACTIVE;

                    // generate random user password
                    $alphabet = [
                        ['abcdefghijklmnopqrstuvwxyz', 3], // min 3 chars
                        ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', 3], // min 3 chars
                        ['0123456789', 3],                 // min 3 chars
                    ];
                    $password = Yii::$app->security->generateRandomString(12, $alphabet);
                    $user->setPassword($password);

                    if (!$user->save()) {
                        Yii::$app->session->setFlash('error',
                            Yii::t('app', 'Unable to save user: {errors}', [
                                'errors' => implode('<br>', $user->getFirstErrors()),
                            ])
                        );

                        return false;
                    }

                    Yii::$app->session->setFlash('success',
                        Yii::t('app', 'You have successfully registered via {client}.', [
                            'client' => $this->client->getTitle(),
                        ])
                    );

                    $user->sendAuthRegisterEmail($password);
                }

                // link
                $auth = new UserAuth([
                    'userId'   => $user->id,
                    'source'   => $this->client->getId(),
                    'sourceId' => (string) $id,
                ]);

                if ($auth->save()) {
                    $transaction->commit();

                    Yii::$app->user->login($user, Yii::$app->params['rememberMeDuration']);
                } else {
                    $transaction->rollBack();

                    Yii::$app->session->setFlash('error',
                        Yii::t('app', 'Unable to save {client} account: {errors}', [
                            'client' => $this->client->getTitle(),
                            'errors' => implode('<br>', $auth->getFirstErrors()),
                        ])
                    );
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new UserAuth([
                    'userId'   => Yii::$app->user->id,
                    'source'   => $this->client->getId(),
                    'sourceId' => (string) $id,
                ]);
                if ($auth->save()) {
                    Yii::$app->session->setFlash('success',
                        Yii::t('app', 'Linked {client} account.', [
                            'client' => $this->client->getTitle()
                        ])
                    );
                } else {
                    Yii::$app->session->setFlash('error',
                        Yii::t('app', 'Unable to link {client} account: {errors}', [
                            'client' => $this->client->getTitle(),
                            'errors' => implode('<br>', $auth->getFirstErrors()),
                        ])
                    );
                }
            } else { // there's existing auth
                Yii::$app->session->setFlash('error',
                    Yii::t('app', 'Unable to link {client} account. There is another user using it.', [
                        'client' => $this->client->getTitle()
                    ])
                );
            }
        }
    }
}
