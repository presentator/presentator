<?php
namespace common\models;

use Yii;
use yii\db\Expression;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;
use yii\base\InvalidParamException;
use common\components\helpers\CArrayHelper;
use common\components\helpers\CFileHelper;
use common\components\swiftmailer\CMessage;
use common\components\JWT;
use Imagine\Image\Box;
use yii\imagine\Image;

/**
 * User AR model
 *
 * @property integer $id
 * @property string  $email
 * @property string  $passwordHash
 * @property string  $passwordResetToken
 * @property string  $emailChangeToken
 * @property string  $authKey
 * @property integer $status
 * @property integer $type
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class User extends CActiveRecord implements IdentityInterface
{
    use UserQueryTrait;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE   = 1;

    const TYPE_REGULAR = 0;
    const TYPE_SUPER   = 1;

    const THUMB_WIDTH   = 100;
    const THUMB_HEIGHT  = 100;
    const THUMB_QUALITY = 100;

    const NOTIFICATIONS_SETTING_KEY = 'notifications';
    const MENTIONS_SETTING_KEY      = 'mentions';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['type', 'default', 'value' => User::TYPE_REGULAR],
            ['type', 'in', 'range' => array_keys(User::getTypeLabels())],
            ['status', 'default', 'value' => static::STATUS_INACTIVE],
            ['status', 'in', 'range' => array_keys(User::getStatusLabels())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        unset(
            $fields['authKey'],
            $fields['passwordHash'],
            $fields['passwordResetToken'],
            $fields['emailChangeToken']
        );

        $fields['avatar'] = function ($model, $field) {
            $url = $model->getAvatarUrl(true);

            return $url ? $url : null;
        };

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['settings'] = function ($model, $field) {
            return [
                self::NOTIFICATIONS_SETTING_KEY => $model->getSetting(self::NOTIFICATIONS_SETTING_KEY, true),
                self::MENTIONS_SETTING_KEY      => $model->getSetting(self::MENTIONS_SETTING_KEY, true),
            ];
        };

        return $extraFields;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->generateAuthKey();
            }

            return true;
        }

        return false;
    }

    /**
     * Returns User statuses with labels.
     * @return array
     */
    public static function getStatusLabels()
    {
        return [
            self::STATUS_INACTIVE => Yii::t('app', 'Inactive'),
            self::STATUS_ACTIVE   => Yii::t('app', 'Active'),
        ];
    }

    /**
     * Returns User types with labels.
     * @return array
     */
    public static function getTypeLabels()
    {
        return [
            self::TYPE_REGULAR => Yii::t('app', 'Regular user'),
            self::TYPE_SUPER   => Yii::t('app', 'Super user'),
        ];
    }

    /* Relations
    --------------------------------------------------------------- */
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAuths()
    {
        return $this->hasMany(UserAuth::className(), ['userId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserSettings()
    {
        return $this->hasMany(UserSetting::className(), ['userId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectRels()
    {
        return $this->hasMany(UserProjectRel::className(), ['userId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::className(), ['id' => 'projectId'])
            ->via('projectRels');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVersions()
    {
        return $this->hasMany(Version::className(), ['projectId' => 'id'])
            ->via('projects');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::className(), ['versionId' => 'id'])
            ->via('versions')
            ->addOrderBy([Screen::tableName() . '.order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenComments()
    {
        return $this->hasMany(ScreenComment::className(), ['screenId' => 'id'])
            ->via('screens');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenCommentRels()
    {
        return $this->hasMany(UserScreenCommentRel::className(), ['userId' => 'id']);
    }

    /* IdentityInterface related methods
    --------------------------------------------------------------- */
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => static::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
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
     * Finds user by password reset token.
     * @param string $token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'passwordResetToken' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid.
     * @param  string $token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        $expire = CArrayHelper::getValue(Yii::$app->params, 'passwordResetTokenExpire', 3600);

        return Yii::$app->security->isTimestampTokenValid($token, $expire);
    }

    /**
     * Validates password.
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * Generates password hash from password and sets it to the model.
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->passwordResetToken = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->passwordResetToken = null;
    }

    /**
     * Generates new email change token
     * @param string $newEmail
     */
    public function generateEmailChangeToken($newEmail)
    {
        $this->emailChangeToken = md5($newEmail) . '_' . time();
    }

    /**
     * Removes email change token
     */
    public function removeEmailChangeToken()
    {
        $this->emailChangeToken = null;
    }

    /**
     * Finds out if email change reset token is valid.
     * @param  string $token Token to validate
     * @param  string $email Email addressed hashed in the token
     * @return boolean
     */
    public static function isEmailChangeTokenValid($token, $email)
    {
        $expire = CArrayHelper::getValue(Yii::$app->params, 'emailChangeTokenExpire', 1800);

        if (!empty($email) && Yii::$app->security->isTimestampTokenValid($token, $expire)) {
            $hashedEmail = strstr($token, '_', true);

            return $hashedEmail === md5($email);
        }

        return false;
    }

    /**
     * Finds user by email change token.
     * @param string $token
     * @return null|static
     */
    public static function findByEmailChangeToken($token)
    {
        $expire = CArrayHelper::getValue(Yii::$app->params, 'emailChangeTokenExpire', 1800);

        if (empty($token) || !Yii::$app->security->isTimestampTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'emailChangeToken' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Changes user email address (with `emailChangeToken` check)
     * @param  string $newEmail Must be included in `emailChangeToken` hash
     * @return boolean
     */
    public function changeEmail($newEmail)
    {
        if (static::isEmailChangeTokenValid($this->emailChangeToken, $newEmail)) {
            $this->email = $newEmail;

            $this->removeEmailChangeToken();

            return $this->save();
        }

        return false;
    }

    /**
     * Generate user JWT authentication token.
     * @return string
     */
    public function generateJwtToken()
    {
        $expire = CArrayHelper::getValue(Yii::$app->params, 'apiUserTokenExpire', 3600);

        $secret = base64_encode(Yii::$app->params['apiUserSecretKey']);

        // token payload data
        $payload = [
            'iss'       => 'Presentator API',
            'iat'       => time(),
            'userId'    => $this->id,
            'userEmail' => $this->email,
            'exp'       => strtotime('+ ' . $expire . ' seconds'),
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Returns active user model by valid JWT token.
     * @param  string $token
     * @return null|User
     */
    public static function findByJwtToken($token)
    {
        $secret = base64_encode(Yii::$app->params['apiUserSecretKey']);

        try {
            $jwt = JWT::decode($token, $secret, ['HS256']);

            return static::findByEmail($jwt->userEmail);
        } catch (\Exception $e) {
            return null;
        }
    }

    /* Emails
    --------------------------------------------------------------- */
    /**
     * Sends an activation email to the current user model.
     * @see `self::getActivationToken()`
     * @return boolean
     */
    public function sendActivationEmail()
    {
        $message = Yii::$app->mailer->compose('activate', [
                'user'  => $this,
                'token' => $this->getActivationToken(),
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($this->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Account activation'))
        ;

        // force direct send
        if ($message instanceof CMessage) {
            $message->useMailQueue(false);
        }

        return $message->send();
    }

    /**
     * Sends a password reset request email to the current user model.
     * @return boolean
     */
    public function sendPasswordResetEmail()
    {
        $message = Yii::$app->mailer->compose('password_reset', [
                'user' => $this,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($this->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Password reset request'))
        ;

        // force direct send
        if ($message instanceof CMessage) {
            $message->useMailQueue(false);
        }

        return $message->send();
    }

    /**
     * Sends email change request email to the current user model.
     * @param  string $newEmail
     * @return boolean
     */
    public function sendEmailChangeEmail($newEmail)
    {
        $message = Yii::$app->mailer->compose('email_change', [
                'user'     => $this,
                'newEmail' => $newEmail,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($newEmail)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Email change request'))
        ;

        // force direct send
        if ($message instanceof CMessage) {
            $message->useMailQueue(false);
        }

        return $message->send();
    }

    /**
     * Sends a Facebook register email with the auto generated password string.
     * @param  string $password
     * @return boolean
     */
    public function sendFacebookRegisterEmail($password)
    {
        $message = Yii::$app->mailer->compose('fb_register', [
                'user'     => $this,
                'password' => $password,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($this->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Registered with Facebook'))
        ;

        // force direct send
        if ($message instanceof CMessage) {
            $message->useMailQueue(false);
        }

        return $message->send();
    }

    /* File upload
    --------------------------------------------------------------- */
    /**
     * Returns user upload dir path.
     * @return string
     */
    public function getUploadDir()
    {
        return sprintf('%s/uploads/users/%s',
            Yii::getAlias('@mainWeb'),
            md5($this->id)
        );
    }

    /**
     * Returns the local path of user avatar.
     * @param  boolean $thumb Whether to fetch the thumb size.
     * @return string
     */
    public function getAvatarPath($thumb = false)
    {
        $fileName = 'avatar.jpg';
        if ($thumb) {
            $fileName = 'avatar_thumb.jpg';
        }

        return $this->getUploadDir() . '/' . $fileName;
    }

    /**
     * Returns the absolute avatar url.
     * NB! Will returns empty string if the avatar doesn't exist.
     * @param  boolean $thumb Whether to fetch the thumb size.
     * @return string
     */
    public function getAvatarUrl($thumb = false, $checkExist = true)
    {
        $path = $this->getAvatarPath($thumb);

        if (!$checkExist || file_exists($path)) {
            return CFileHelper::getUrlFromPath($path);
        }

        return '';
    }

    /**
     * Returns the local path of temporary user avatar.
     * @return string
     */
    public function getTempAvatarPath()
    {
        return $this->getUploadDir() . '/' . 'avatar_temp.jpg';
    }

    /**
     * Returns the absolute temporary user avatar url.
     * @return string
     */
    public function getTempAvatarUrl()
    {
        return CFileHelper::getUrlFromPath($this->getTempAvatarPath());
    }

    /**
     * Creates single image thumb (based on `self::THUMB_SIZES`).
     * If `$cropDimensions` is missing it will create by default a centered thumb.
     * @param  null|array $cropDimensions
     * @return boolean
     */
    public function cropAvatar(array $cropDimensions = null)
    {
        ini_set('memory_limit', '512M');

        $originalAvatarPath = $this->getAvatarPath(false);
        if (!file_exists($originalAvatarPath)) {
            return false;
        }

        if (!empty($cropDimensions)) {
            if (!isset($cropDimensions['w']) ||
                !isset($cropDimensions['h']) ||
                !isset($cropDimensions['x']) ||
                !isset($cropDimensions['y'])
            ) {
                throw new InvalidParamException('Crop dimensions must have "w", "h", "x" and "y" properties!');
            }

            $image = Image::crop(
                    $originalAvatarPath,
                    $cropDimensions['w'],
                    $cropDimensions['h'],
                    [
                        ($cropDimensions['x'] ? $cropDimensions['x'] : 0),
                        ($cropDimensions['y'] ? $cropDimensions['y'] : 0),
                    ]
                )
                ->thumbnail(new Box(self::THUMB_WIDTH, self::THUMB_HEIGHT));
        } else {
            $image = Image::thumbnail($originalAvatarPath, self::THUMB_WIDTH, self::THUMB_HEIGHT);
        }

        // store avatar thumb
        $image->save($this->getAvatarPath(true), ['quality' => self::THUMB_QUALITY]);

        return true;
    }

    /* Others
    --------------------------------------------------------------- */
    /**
     * Returns a list with filtered user models by searching within their name and email.
     * @param  string  $search      Search keyword.
     * @param  array   $exclude     User id(s) to exclude.
     * @param  boolean $fuzzySearch Whether to enable fuzzy email/name search or not.
     * @param  boolean $activeOnly  Whether to return only active user models or not.
     * @param  integer $limit       Number of returned results.
     * @param  integer $offset      Returned results offset.
     * @return User[]
     */
    public static function searchUsers(
        $search,
        array $exclude = [],
        $fuzzySearch = false,
        $activeOnly = true,
        $limit = 20,
        $offset = 0
    )
    {
        $query = static::find()->distinct();

        if ($fuzzySearch) {
            $nameExpression = new Expression("UPPER(CONCAT_WS(' ', `firstName`, `lastName`))");
            $query->where([
                'or',
                ['like', $nameExpression, strtoupper($search)],
                ['like', 'email', $search]
            ]);
        } else {
            // full string match
            $query->where(['email' => $search]);
        }

        if ($activeOnly) {
            $query->andWhere(['status' => static::STATUS_ACTIVE]);
        }

        return $query->andWhere(['not in', 'id', $exclude])
            ->limit($limit)
            ->offset($offset)
            ->all();
    }

    /**
     * Finds user by its email address.
     * @param  string  $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne([
            'email'  => $email,
            'status' => static::STATUS_ACTIVE,
        ]);
    }

    /**
     * Returns list with user models.
     * @param  integer $limit      Number of returned results.
     * @param  integer $offset     Results page offset.
     * @param  array   $conditions Optional conditions to be applied to the query.
     * @return User[]
     */
    public static function findUsers($limit = -1, $offset = 0, array $conditions = [])
    {
        return User::find()
            ->distinct()
            ->andFilterWhere($conditions)
            ->orderBy(['createdAt' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all();
    }

    /**
     * Counts total user models.
     * @param  array $conditions Optional conditions to be applied to the query.
     * @return integer
     */
    public static function countUsers(array $conditions = [])
    {
        return (int) User::find()
            ->distinct()
            ->select('id')
            ->andFilterWhere($conditions)
            ->count();
    }

    /**
     * Returns and generates hashed activation token.
     * @return string
     */
    public function getActivationToken()
    {
        return md5($this->email . Yii::$app->params['activationSalt'] . $this->authKey);
    }

    /**
     * Validates single user activation token.
     * @param  string $token
     * @return boolean
     */
    public function validateActivationToken($token)
    {
        return $this->getActivationToken() === $token;
    }

    /**
     * Returns user full name.
     * @return string
     */
    public function getFullName()
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /**
     * Returns user identification string (name or email).
     * NB! This method will returns always the user email address as a fallback.
     * @param  null|string $type [null|firstName|lastName|email]
     * @return string
     */
    public function getIdentificator($type = null)
    {
        if ($type === null) {
            // auto find the proper identificator
            return strlen($this->getFullName()) > 0 ? $this->getFullName() : $this->email;
        } elseif ($type === 'firstName') {
            return strlen($this->firstName) > 0 ? $this->firstName : $this->email;
        } elseif ($type === 'lastName') {
            return strlen($this->lastName) > 0 ? $this->lastName : $this->email;
        }

        return $this->email;
    }

    /**
     * Returns single UserSetting value by setting name.
     * @see `UserSetting::getSettingByUser()`
     * @param  string $settingName
     * @param  mixed $defaultValue
     * @return mixed
     */
    public function getSetting($settingName, $defaultValue = null)
    {
        return UserSetting::getSettingByUser($this, $settingName, $defaultValue);
    }

    /**
     * Updates or creates a new UserSetting model.
     * @see `UserSetting::setSettingByUser()`
     * @param  string $settingName
     * @param  mixed $settingValue
     * @return boolean
     */
    public function setSetting($settingName, $settingValue)
    {
        return UserSetting::setSettingByUser($this, $settingName, $settingValue);
    }
}
