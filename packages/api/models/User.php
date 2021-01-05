<?php
namespace presentator\api\models;

use Yii;
use yii\db\Expression;
use yii\web\IdentityInterface;
use yii\helpers\ArrayHelper;
use presentator\api\behaviors\FileStorageBehavior;
use presentator\api\base\JWT;

/**
 * User AR model
 *
 * @property integer $id
 * @property string  $type
 * @property string  $email
 * @property string  $passwordHash
 * @property string  $passwordResetToken
 * @property string  $authKey
 * @property string  $firstName
 * @property string  $lastName
 * @property string  $avatarFilePath
 * @property string  $status
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class User extends ActiveRecord implements IdentityInterface
{
    use UserQueryTrait;

    const TYPE = [
        'REGULAR' => 'regular',
        'SUPER'   => 'super',
    ];

    const STATUS = [
        'INACTIVE' => 'inactive',
        'ACTIVE'   => 'active',
    ];

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettings()
    {
        return $this->hasMany(UserSetting::class, ['userId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProjectRels()
    {
        return $this->hasMany(UserProjectRel::class, ['userId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::class, ['id' => 'projectId'])
            ->via('userProjectRels');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProjectLinkRels()
    {
        return $this->hasMany(UserProjectLinkRel::class, ['userId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccessedProjectLinks()
    {
        return $this->hasMany(ProjectLink::class, ['id' => 'projectLinkId'])
            ->via('userProjectLinkRels');
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->type === null) {
                $this->type = static::TYPE['REGULAR'];
            }

            if ($insert) {
                $this->generateAuthKey();
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // delete related projects if no other admins are managing them
        foreach ($this->projects as $project) {
            if (
                count($project->users) == 1 &&
                $project->users[0]->id == $this->id &&
                !$project->delete()
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['fileBehavior'] = [
            'class' => FileStorageBehavior::class,
            'filePathAttribute' => 'avatarFilePath',
            'filePathPrefix' => function ($model) {
                $userKey = md5(Yii::$app->params['storageKeysSalt'] . $model->id);

                return '/users/' . $userKey;
            },
            'thumbs' => [
                'small'  => ['width' => 100, 'height' => 100, 'smartResize' => true],
            ],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();

        unset(
            $fields['authKey'],
            $fields['passwordHash'],
            $fields['passwordResetToken'],
            $fields['avatarFilePath']
        );

        $fields['avatar'] = function ($model, $field) {
            return (object) $model->getAvatar();
        };

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['settings'] = function ($model, $field) {
            $result = [];

            foreach ($model->settings as $setting) {
                $result[$setting->name] = $setting->getValue();
            }

            return (object) $result; // cast to ensure that value will be always seriazed to a json object
        };

        return $extraFields;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'email'],
            ['email', 'required'],
            ['status', 'default', 'value' => static::STATUS['INACTIVE']],
        ];
    }

    /**
     * Checks whether the current `User` model is super user/admin.
     *
     * @return boolean
     */
    public function isSuperUser(): bool
    {
        return $this->type == static::TYPE['SUPER'];
    }

    /**
     * Activates the current inactive `User` model.
     *
     * @return boolean
     */
    public function activate(): bool
    {
        if ($this->status == static::STATUS['ACTIVE']) {
            return true; // already activated
        }

        $this->status = static::STATUS['ACTIVE'];

        return $this->save();
    }

    /**
     * Returns list with all model's avatar urls.
     *
     * @return array
     */
    public function getAvatar(): array
    {
        if ($this->avatarFilePath) {
            return [
                'original' => $this->getUrl(),
                'small'    => $this->getThumbUrl('small'),
            ];
        }

        return [];
    }

    /* IdentityInterface related methods
    --------------------------------------------------------------- */
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findById($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        try {
            // find user's secret
            $payload = JWT::unsafeDecode($token);
            $user    = !empty($payload->userId) ? static::findById($payload->userId) : null;
            $secret  = ($user ? $user->getAuthKey() : '') . Yii::$app->params['accessTokenSecret'];

            if ($user && JWT::isValid($token, $secret)) {
                return $user;
            }
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Generates random authentication key and sets it to the model.
     */
    public function generateAuthKey()
    {
        $this->authKey = Yii::$app->security->generateRandomString();
    }

    /* Passwords, tokens, etc.
    --------------------------------------------------------------- */
    /**
     * Validates user password string.
     *
     * @param  string $password
     * @return boolean
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * Generates and sets password hash from `$password`.
     *
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates and sets a new password reset token string.
     */
    public function generatePasswordResetToken(): void
    {
        $this->passwordResetToken = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Reset user password reset token.
     */
    public function clearPasswordResetToken(): void
    {
        $this->passwordResetToken = null;
    }

    /**
     * Finds out if a password reset token is valid.
     *
     * @param  string $token
     * @return boolean
     */
    public static function isPasswordResetTokenValid(string $token): bool
    {
        if (!$token) {
            return false;
        }

        $expire = ArrayHelper::getValue(Yii::$app->params, 'passwordResetTokenDuration', 3600);

        return Yii::$app->security->isTimestampTokenValid($token, $expire);
    }

    /**
     * Returns single active user by its non expired password reset token.
     *
     * @param  string $token
     * @return null|User
     */
    public static function findByPasswordResetToken(string $token): ?User
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::find()
            ->where([
                'passwordResetToken' => $token,
                'status' => static::STATUS['ACTIVE'],
            ])
            ->one();
    }

    /**
     * Generates new JWT activation token.
     *
     * @return string
     */
    public function generateActivationToken(): string
    {
        $duration = ArrayHelper::getValue(Yii::$app->params, 'activationTokenDuration', 3600);
        $secret   = $this->getAuthKey() . Yii::$app->params['activationTokenSecret'];

        return JWT::encode([
            "iss"    => "presentator_api",
            "iat"    => time(),
            "exp"    => (time() + $duration),
            "userId" => $this->id,
        ], $secret);
    }

    /**
     * Validates and applies user activation token.
     * Returns the activated `User` model on success, otherwise - `null`.
     *
     * @param  string $token
     * @return null|User
     */
    public static function activateByActivationToken(string $token): ?User
    {
        // find user's secret
        $payload = JWT::unsafeDecode($token);
        $user = static::findOne([
            'id'     => $payload->userId,
            'status' => static::STATUS['INACTIVE'],
        ]);
        $secret = ($user ? $user->getAuthKey() : '') . Yii::$app->params['activationTokenSecret'];

        if (
            $user &&                         // active user exist
            JWT::isValid($token, $secret) && // valid jwt token
            $user->activate()                // successful activation
        ) {
            return $user;
        }

        return null;
    }

    /**
     * Generates new JWT email change token.
     *
     * @param  string $newEmail
     * @return string
     */
    public function generateEmailChangeToken(string $newEmail): string
    {
        $duration = ArrayHelper::getValue(Yii::$app->params, 'emailChangeTokenDuration', 3600);
        $secret   = $this->getAuthKey() . Yii::$app->params['emailChangeTokenSecret'];

        return JWT::encode([
            "iss"      => "presentator_api",
            "iat"      => time(),
            "exp"      => (time() + $duration),
            "userId"   => $this->id,
            "newEmail" => $newEmail,
        ], $secret);
    }

    /**
     * Validates and applies user email change token
     * (aka. replaces a user email with the token's `newEmail` claim value).
     * Returns the modified `User` model on success, otherwise - `null`.
     *
     * @param  string $token
     * @return null|User
     */
    public static function changeEmailByEmailChangeToken(string $token): ?User
    {
        // find user's secret
        $payload = JWT::unsafeDecode($token);
        $user    = !empty($payload->userId) ? static::findById($payload->userId) : null;
        $secret  = ($user ? $user->getAuthKey() : '') . Yii::$app->params['emailChangeTokenSecret'];

        if ($user && JWT::isValid($token, $secret)) {
            $user->email = $payload->newEmail;

            if ($user->save()) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Generates new JWT access token.
     *
     * @return string
     */
    public function generateAccessToken(): string
    {
        $duration = ArrayHelper::getValue(Yii::$app->params, 'accessTokenDuration', 3600);
        $secret   = $this->getAuthKey() . Yii::$app->params['accessTokenSecret'];

        return JWT::encode([
            "iss"      => "presentator_api",
            "iat"      => time(),
            "exp"      => (time() + $duration),
            "userId"   => $this->id,
        ], $secret);
    }

    /**
     * {@inheritdoc}
     */
    public static function findById(int $id, array $filters = [])
    {
        return static::find()
            ->where([
                'id'     => $id,
                'status' => static::STATUS['ACTIVE'],
            ])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Returns single active `User` model by its email address.
     *
     * @param  string $email     User email address to search for.
     * @param  array  [$filters] Additional conditions to apply to the query.
     * @return null|User
     */
    public static function findByEmail(string $email, array $filters = []) : ?User
    {
        return static::find()
            ->where([
                'email'  => $email,
                'status' => static::STATUS['ACTIVE'],
            ])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Search for active `User` models filtered by their name and email fields.
     *
     * @param  string  $search        Search term/keyword.
     * @param  array   [$excludeIds]  User ids to exclude.
     * @param  boolean [$looseSearch] Enable/disable loose search - aka. via `like` statement (`true` by default).
     * @param  integer [$limit]       Number of returned results (30 by default).
     * @param  integer [$offset]      Results page offset (0 by default).
     * @return User[]
     */
    public static function searchUsers(
        string $search,
        array $excludeIds = [],
        bool $looseSearch = true,
        int $limit = 30,
        int $offset = 0
    ): array
    {
        $connection = static::getDb();

        $query = static::find();

        if ($looseSearch) {
            $nameExpression = new Expression(sprintf(
                "UPPER(CONCAT_WS(' ', %s, %s))",
                $connection->quoteColumnName('firstName'),
                $connection->quoteColumnName('lastName')
            ));

            $query->where([
                'or',
                ['like', $nameExpression, strtoupper($search)],
                ['like', 'email', $search]
            ]);
        } else {
            // full string match
            $query->where(['email' => $search]);
        }

        return $query->andWhere(['status' => static::STATUS['ACTIVE']])
            ->andWhere(['not in', 'id', $excludeIds])
            ->limit($limit)
            ->offset($offset)
            ->all();
    }

    /**
     * Returns single casted user setting value by its name.
     *
     * @param  string $name           Name of the UserSetting value to return.
     * @param  mixed  [$defaultValue] Value to return if no such setting exist.
     * @return mixed
     */
    public function getSetting(string $name, $defaultValue = null)
    {
        foreach ($this->settings as $setting) {
            if ($setting->name === $name) {
                return $setting->getValue();
            }
        }

        return $defaultValue;
    }

    /**
     * Updates (or creates) user setting model.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  integer [$type]
     * @return boolean
     */
    public function setSetting(string $name, $value, string $type = UserSetting::TYPE['BOOLEAN']): bool
    {
        $setting = null;

        // find or create setting model
        foreach ($this->settings as $existingSetting) {
            if ($existingSetting->name === $name) {
                $setting = $existingSetting;
                break;
            }
        }
        if (!$setting) {
            $setting = new UserSetting;
            $setting->userId = $this->id;
        }

        $setting->name = $name;
        $setting->type = $type;
        $setting->setValue($value);

        if ($setting->save()) {
            unset($this->settings); // reset populated relation models

            return true;
        }

        return false;
    }

    /**
     * Performs model data and settings save in a transactional manner.
     *
     * Example usage:
     * ```php
     * $user->saveWithSettings([
     *     'email'     => 'test@example.com',
     *     'firstName' => 'Lorem',
     *     'lastName'  => 'Ipsum',
     * ], [
     *     [UserSetting::NOTIFY_ON_EACH_COMMENT, false, UserSetting::TYPE['BOOLEAN']],
     *     [UserSetting::NOTIFY_ON_MENTION, true, UserSetting::TYPE['BOOLEAN']],
     * ]);
     * ```
     *
     * @param  array [$modelData] User data to populate in key value format (eg. `[attr => value]`).
     * @param  array [$settings]  Settings to upsert in array list format (eg. `[['mySetting', 1, 1], ...]`).
     * @return boolean
     */
    public function saveWithSettings(array $modelData = [], array $settings = []): bool
    {
        $transaction = static::getDb()->beginTransaction();

        try {
            foreach ($modelData as $key => $value) {
                $this->{$key} = $value;
            }

            $result = $this->save();

            foreach ($settings as $setting) {
                $result &= $this->setSetting(
                    $setting[0],
                    $setting[1],
                    (isset($setting[2]) ? $setting[2] : UserSetting::TYPE['BOOLEAN'])
                );
            }

            if ($result) {
                $transaction->commit();
            }

            return $result;
        } catch (\Exception | \Throwable $e) {
            $transaction->rollBack();

            Yii::error($e->getMessage());
        }

        return false;
    }

    /**
     * Checks whether the user is linked to the provided project.
     *
     * @param  Project $project
     * @return boolean
     */
    public function isLinkedToProject(Project $project): bool
    {
        return UserProjectRel::find()
            ->where([
                'userId'    => $this->id,
                'projectId' => $project->id,
            ])
            ->exists();
    }

    /**
     * Returns the user's full name.
     *
     * @return string
     */
    public function getFullName()
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /**
     * Sends an account activation email.
     *
     * @return boolean
     */
    public function sendActivationEmail(): bool
    {
        return Yii::$app->mailer->compose('activation', [
                'user' => $this,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($this->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Account activation'))
            ->send();
    }


    /**
     * Sends a password reset request email.
     *
     * @return boolean
     */
    public function sendForgottenPasswordEmail(): bool
    {
        return Yii::$app->mailer->compose('forgotten_password', [
                'user' => $this,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($this->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Password reset request'))
            ->send();
    }

    /**
     * Sends email change request email.
     *
     * @return boolean
     */
    public function sendEmailChangeEmail(string $newEmail): bool
    {
        return Yii::$app->mailer->compose('email_change', [
                'user'     => $this,
                'newEmail' => $newEmail,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($newEmail)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Email change confirmation'))
            ->send();
    }

    /**
     * Sends a successfull auth client authentication email
     * with the auto generated user's password.
     *
     * @param  string $password
     * @return boolean
     */
    public function sendAuthClientRegisterEmail(string $password): bool
    {
        return Yii::$app->mailer->compose('auth_register', [
                'user'     => $this,
                'password' => $password,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($this->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Successfully authenticated'))
            ->send();
    }

    /**
     * Sends an email when the user is assigned as an administrator to a project.
     *
     * @param  Project $project
     * @return boolean
     */
    public function sendLinkedToProjectEmail(Project $project): bool
    {
        return Yii::$app->mailer->compose('linked_project_admin', [
                'user'    => $this,
                'project' => $project,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($this->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Administrator assignment'))
            ->send();
    }

    /**
     * Sends an email when the user is discharged as an administrator from a project.
     *
     * @param  Project $project
     * @return boolean
     */
    public function sendUnlinkedFromProjectEmail(Project $project): bool
    {
        return Yii::$app->mailer->compose('unlinked_project_admin', [
                'user'    => $this,
                'project' => $project,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($this->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Administrator discharge'))
            ->send();
    }

    /**
     * Sends an unread screen comment email to the current user model.
     * NB! This method doesn't check user's email notification settings.
     *
     * @param  ScreenComment $comment
     * @return boolean
     */
    public function sendUnreadCommentEmail(ScreenComment $comment): bool
    {
        return Yii::$app->mailer->compose('unread_comment', [
                'user'    => $this,
                'comment' => $comment,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($this->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Unread comment'))
            ->send();
    }
}
