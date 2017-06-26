<?php
namespace common\models;

use Yii;
use common\components\helpers\CFileHelper;
use common\components\helpers\CArrayHelper;

/**
 * Project AR model.
 *
 * @property integer $id
 * @property string  $title
 * @property integer $type
 * @property integer $subtype
 * @property string  $passwordHash
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Project extends CActiveRecord
{
    use ProjectQueryTrait;

    // Main types (@see `self::getTypeLabels()`)
    const TYPE_DESKTOP = 1;
    const TYPE_TABLET  = 2;
    const TYPE_MOBILE  = 3;

    const SUBTYPES = [
        // Tablet (@see `self::getTabletSubtypeLabels()`)
        21 => [768, 1024],
        22 => [1024, 768],
        23 => [800, 1200],
        24 => [1200, 800],
        // Mobile (@see `self::getMobileSubtypeLabels()`)
        31 => [320, 480],
        32 => [480, 320],
        33 => [375, 667],
        34 => [667, 375],
        35 => [412, 732],
        36 => [732, 712],
                
        99 => [412, 732] 
    ];

    /**
     * Stores generated project preview urls.
     * @var array
     */
    private $previewUrls = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%project}}';
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $version = new Version;
            $version->projectId = $this->id;
            $version->save();

            $this->createDefaultPreviews();
        }
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        CFileHelper::removeDirectory($this->getUploadDir());

        return parent::delete();
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        unset($fields['passwordHash']);

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['featured'] = 'featuredScreen';
        $extraFields['versions'] = 'versions';
        $extraFields['screens']  = 'screens';

        return $extraFields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRels()
    {
        return $this->hasMany(UserProjectRel::className(), ['projectId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'userId'])
            ->via('userRels');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVersions()
    {
        return $this->hasMany(Version::className(), ['projectId' => 'id'])
            ->orderBy([Version::tableName() . '.order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreviews()
    {
        return $this->hasMany(ProjectPreview::className(), ['projectId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::className(), ['versionId' => 'id'])
            ->via('versions');
    }

    /**
     * Generates relation query to fetch the first screen of last active project version.
     * @return \yii\db\ActiveQuery
     */
    public function getFeaturedScreen()
    {
        return $this->hasOne(Screen::className(), ['versionId' => 'id'])
            ->via('versions')
            ->orderBy([
                Screen::tableName() . '.versionId' => SORT_DESC,
                Screen::tableName() . '.order'     => SORT_ASC,
            ]);
    }

    /**
     * Generates relation query to fetch the latest project version with atleast one screen.
     * @return \yii\db\ActiveQuery
     */
    public function getLatestActiveVersion()
    {
        return $this->hasOne(Version::className(), ['projectId' => 'id'])
            ->orderBy([Version::tableName() . '.order' => SORT_DESC])
            ->innerJoinWith('screens', false);
    }

    /**
     * Validates a password string against the model passwordHash.
     * @param  string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * Generates a password hash from `$password` and sets it to the model.
     * @param string|null $password
     */
    public function setPassword($password = null)
    {
        if ($password === '' || $password === null) {
            $this->passwordHash = '';
        } else {
            $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
        }
    }

    /**
     * Checks whether the current model is password protected or not.
     * @return boolean
     */
    public function isPasswordProtected()
    {
        return !empty($this->passwordHash);
    }

    /**
     * Returns project types with labels.
     * @return array
     */
    public static function getTypeLabels()
    {
        return [
            self::TYPE_MOBILE  => Yii::t('app', 'Mobile'),
            self::TYPE_TABLET  => Yii::t('app', 'Tablet'),
            self::TYPE_DESKTOP => Yii::t('app', 'Desktop'),
        ];
    }

    /**
     * Returns project tablet type's subtypes with label.
     * @return array
     */
    public static function getTabletSubtypeLabels()
    {
        return [
            21 => Yii::t('app', '768x1024 <em>(Portrait)</em>'),
            22 => Yii::t('app', '768x1024 <em>(Landscape)</em>'),
            23 => Yii::t('app', '800x1200 <em>(Portrait)</em>'),
            24 => Yii::t('app', '800x1200 <em>(Landscape)</em>'),
        ];
    }

    /**
     * Returns project mobile type's subtypes with labels.
     * @return array
     */
    public static function getMobileSubtypeLabels()
    {
        return [
            31 => Yii::t('app', '320x480 <em>(Portrait)</em>'),
            32 => Yii::t('app', '320x480 <em>(Landscape)</em>'),
            33 => Yii::t('app', '375x667 <em>(Portrait)</em>'),
            34 => Yii::t('app', '375x667 <em>(Landscape)</em>'),
            35 => Yii::t('app', '412x732 <em>(Portrait)</em>'),
            36 => Yii::t('app', '412x732 <em>(Landscape)</em>'),
            99 => Yii::t('app', 'Fit to Screen <em>(Portrait)</em>'),
        ];
    }

    /**
     * Returns path string to the model upload directory.
     * @return string
     */
    public function getUploadDir()
    {
        return sprintf('%s/uploads/projects/%s',
            Yii::getAlias('@mainWeb'),
            md5($this->id)
        );
    }

    /**
     * Creates all default project preview models.
     * @return boolean
     */
    public function createDefaultPreviews()
    {
        $result      = true;
        $previews    = CArrayHelper::map($this->previews, 'type', 'slug');
        $accessTypes = ProjectPreview::getTypeLabels();

        foreach ($accessTypes as $type => $label) {
            if (isset($previews[$type])) {
                continue; // prevent creating duplicating previews
            }

            $model = new ProjectPreview;
            $model->generateSlug();
            $model->type      = $type;
            $model->projectId = $this->id;

            $result = $result && $model->save();
        }

        return $result;
    }

    /**
     * Returns an absolute project preview link url.
     * @param  string $type ProjectPreview type.
     * @return string
     */
    public function getPreviewUrl($type)
    {
        if (!isset($this->previewUrls[$type])) {
            $previews = CArrayHelper::map($this->previews, 'type', 'slug');
            if (isset($previews[$type])) {
                $this->previewUrls[$type] = Yii::$app->urlManager->createAbsoluteUrl(['preview/view', 'slug' => $previews[$type]]);
            } else {
                $this->previewUrls[$type] = '#';
            }
        }

        return $this->previewUrls[$type];
    }

    /**
     * Links user to the current project model.
     * @param  User    $user
     * @param  boolean $sendEmail
     * @return boolean
     */
    public function linkUser(User $user, $sendEmail = true)
    {
        $rel = UserProjectRel::findOne(['userId' => $user->id, 'projectId' => $this->id]);
        if (!$rel) {
            try {
                // link user
                $this->link('users', $user);

                if ($sendEmail) {
                    $this->sendAddedProjectAdminEmail($user);
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        // seems to be already linked
        return true;
    }

    /**
     * Unlinks user from the current project model.
     * @param  User $user
     * @param  boolean $sendEmail
     * @return boolean
     */
    public function unlinkUser(User $user, $sendEmail = true)
    {
        // The project must have at least two administrators to further proceed
        if (count($this->userRels) > 1) {
            try {
                $this->unlink('users', $user, true);

                if ($sendEmail) {
                    $this->sendRemovedProjectAdminEmail($user);
                }

                return true;
            } catch (\Exception $e) {
            }
        }

        return false;
    }

    /* Emails
    --------------------------------------------------------------- */
    /**
     * Sends an information email to an user when is assigned as a project administrator.
     * @param  User $user
     * @return boolean
     */
    public function sendAddedProjectAdminEmail(User $user)
    {
        return Yii::$app->mailer->compose('added_project_admin', [
                'user'    => $user,
                'project' => $this,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($user->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Administrator assignment'))
            ->send();
    }

    /**
     * Sends an information email to an user when is discharge as a project administrator.
     * @param  User $user
     * @return boolean
     */
    public function sendRemovedProjectAdminEmail(User $user)
    {
        return Yii::$app->mailer->compose('removed_project_admin', [
                'user'    => $user,
                'project' => $this,
            ])
            ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
            ->setTo($user->email)
            ->setSubject('Presentator - ' . Yii::t('mail', 'Administrator discharge'))
            ->send();
    }
}
