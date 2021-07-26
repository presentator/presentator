<?php
namespace presentator\api\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use presentator\api\models\User;
use presentator\api\models\UserAuth;
use yii\authclient\OAuth2;

/**
 * Handles user authentication via Yii auth component.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class AuthClientAuthorizationForm extends ApiForm
{
    /**
     * @var string
     */
    public $client;

    /**
     * @var string
     */
    public $code;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['client'] = Yii::t('app', 'Client');
        $labels['code']   = Yii::t('app', 'Code');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $supportedClients = array_keys(static::getConfiguredAuthClients());

        $rules[] = [['client', 'code'], 'required'];
        $rules[] = ['client', 'in', 'range' => $supportedClients];

        return $rules;
    }

    /**
     * Returns all OAuth2 collection clients with configured client ID and secret.
     *
     * @return array
     */
    public static function getConfiguredAuthClients(): array
    {
        $result = [];

        $clients = Yii::$app->has('authClientCollection') ? Yii::$app->authClientCollection->getClients() : [];

        foreach ($clients as $key => $client) {
            if (
                $client instanceof OAuth2 &&
                !empty($client->clientId) &&
                !empty($client->clientSecret)
            ) {
                $result[$key] = $client;
            }
        }

        return $result;
    }

    /**
     * Process model form and returns the authorized `User` model.
     *
     * @return null|User
     */
    public function authorize(): ?User
    {
        if ($this->validate()) {
            $client = static::getConfiguredAuthClients()[$this->client];

            // disable state param validations since we are not using sessions
            $client->validateAuthState = false;

            $token = $client->fetchAccessToken($this->code, array_filter([
                'redirect_uri' => Yii::$app->params['authClientRedirectUri'],
            ]));

            if (!empty($token)) {
                return $this->findAuthUser($client);
            }
        }

        return null;
    }

    /**
     * Finds (and creates, if missing) `User` model by the retrieved auth client user attributes.
     *
     * @return null|User
     */
    protected function findAuthUser($client): ?User
    {
        $attributes = $client->getUserAttributes();
        $email      = ArrayHelper::getValue($attributes, 'email');
        $id         = ArrayHelper::getValue($attributes, 'id');

        if (empty($email)) {
            return null;
        }

        $authModel = UserAuth::findOne([
            'source'   => $client->getId(),
            'sourceId' => $id,
        ]);

        // existing previously authorized model
        if ($authModel) {
            return $authModel->user;
        }

        $transaction = User::getDb()->beginTransaction();
        try {
            $user = User::findOne(['email' => $email]);

            if ($user) { // existing user but is not linked with an auth model
                // activate user (if not already)
                $user->activate();
            } else { // create new user
                $alphabet = [
                    ['abcdefghijklmnopqrstuvwxyz', 3], // min 3 chars
                    ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', 3], // min 3 chars
                    ['0123456789', 3],                 // min 3 chars
                ];
                $password = Yii::$app->security->generateRandomString(15, $alphabet);

                $createForm = new UserCreateForm([
                    'scenario'        => UserCreateForm::SCENARIO_SUPER,
                    'email'           => $email,
                    'password'        => $password,
                    'passwordConfirm' => $password,
                    'status'          => User::STATUS['ACTIVE'],
                    'type'            => User::TYPE['REGULAR'],
                ]);

                $user = $createForm->save();

                if (!$user) {
                    throw new \Exception('Unable to persist user create form.');
                }

                if (!empty(Yii::$app->params['emailPasswordAuth'])) {
                    // notify the user that his/her account was created
                    // automatically with the generated temp password
                    $user->sendAuthClientRegisterEmail($password);
                }
            }

            // link the user with an auth model so that it can be directly returned on the next authorization
            $authModel = new UserAuth([
                'userId'   => $user->id,
                'source'   => $client->getId(),
                'sourceId' => (string) $id,
            ]);

            if (!$authModel->save()) {
                throw new \Exception('Unable to persist the created auth model.');
            }

            $transaction->commit();

            return $user;
        } catch (\Exception | \Throwable $e) {
            $transaction->rollBack();

            Yii::error($e->getMessage());
        }

        return null;
    }
}
