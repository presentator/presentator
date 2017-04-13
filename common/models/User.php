<?php
namespace common\models;

use Yii;
use yii\db\Expression;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;
use yii\base\InvalidParamException;
use common\components\helpers\CArrayHelper;
use common\components\helpers\CFileHelper;
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
 * @property string  $authKey
 * @property integer $status
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

    const THUMB_WIDTH   = 100;
    const THUMB_HEIGHT  = 100;
    const THUMB_QUALITY = 100;

    const LANGUAGE_SETTING_KEY      = 'language';
    const NOTIFICATIONS_SETTING_KEY = 'notifications';

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
            ['status', 'default', 'value' => static::STATUS_INACTIVE],
            ['status', 'in', 'range' => [static::STATUS_ACTIVE, static::STATUS_INACTIVE]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        unset($fields['authKey'], $fields['passwordHash'], $fields['passwordResetToken']);

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
                self::LANGUAGE_SETTING_KEY      => $model->getSetting(self::LANGUAGE_SETTING_KEY, Yii::$app->language),
                self::NOTIFICATIONS_SETTING_KEY => $model->getSetting(self::NOTIFICATIONS_SETTING_KEY, true),
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
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = CArrayHelper::getValue(Yii::$app->params, 'passwordResetTokenExpire', 3600);

        return ($timestamp + $expire) >= time();
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
     * Generate user JWT authentication token.
     * @return string
     */
    public function generateJwtToken()
    {
        $secret = base64_encode(Yii::$app->params['apiUserSecretKey']);

        // token payload data
        $payload = [
            'iss'       => 'Presentator.io API',
            'iat'       => time(),
            'userId'    => $this->id,
            'userEmail' => $this->email,
            'exp'       => strtotime('+ 60min'),
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
        return Yii::$app->mailer->compose('activate', [
                'user'  => $this,
                'token' => $this->getActivationToken(),
            ])
            ->setFrom(Yii::$app->params['noreplyEmail'])
            ->setTo($this->email)
            ->setSubject(Yii::t('app', 'Account activation'))
            ->send();
    }

    /**
     * Sends a password reset request email to the current user model.
     * @return boolean
     */
    public function sendPasswordResetEmail()
    {
        return Yii::$app->mailer->compose('password_reset', [
                'user' => $this,
            ])
            ->setFrom(Yii::$app->params['noreplyEmail'])
            ->setTo($this->email)
            ->setSubject('Presentator.io - ' . Yii::t('app', 'Password reset request'))
            ->send();
    }

    /**
     * Sends a Facebook register email with the auto generated password string.
     * @param  string $password
     * @return boolean
     */
    public function sendFacebookRegisterEmail($password)
    {
        return Yii::$app->mailer->compose('fb_register', [
                'user'     => $this,
                'password' => $password,
            ])
            ->setFrom(Yii::$app->params['noreplyEmail'])
            ->setTo($this->email)
            ->setSubject('Presentator.io - ' . Yii::t('app', 'Registered with Facebook'))
            ->send();
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
        ini_set('memory_limit', '256M');

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
     * @param  string  $search  Search keyword.
     * @param  array   $exclude User id(s) to exclude.
     * @param  integer $limit   Number of returned results.
     * @param  integer $offset  Returned results offset.
     * @return User[]
     */
    public static function searchUsers($search, array $exclude = [], $limit = -1, $offset = 0)
    {
        $nameExpression = new Expression("UPPER(CONCAT_WS(' ', `firstName`, `lastName`))");

        return static::find()
            ->distinct()
            ->where([
                'or',
                ['like', $nameExpression, strtoupper($search)],
                ['like', 'email', $search]
            ])
            ->andWhere(['status' => static::STATUS_ACTIVE])
            ->andWhere(['not in', 'id', $exclude])
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
