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
 * @property string  $passwordHash
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Project extends CActiveRecord
{
    use ProjectQueryTrait;

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
            $this->createDefaultVersion();

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
        $extraFields['previews'] = 'previews';

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
     * Creates the initial/default project version.
     * @return boolean
     */
    public function createDefaultVersion()
    {
        $version              = new Version;
        $version->projectId   = $this->id;
        $version->type        = Version::TYPE_DESKTOP;
        $version->subtype     = null;
        $version->scaleFactor = Version::DEFAULT_SCALE_FACTOR;

        return $version->save();
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
     * @param  string $type   ProjectPreview type.
     * @param  array  $params Additional query params to be passed to the generated url.
     * @return string
     */
    public function getPreviewUrl($type, array $params = [])
    {
        if (!isset($this->previewUrls[$type])) {
            $previews = CArrayHelper::map($this->previews, 'type', 'slug');
            if (isset($previews[$type])) {
                $params['slug'] = $previews[$type];
                array_unshift($params, 'preview/view');

                $this->previewUrls[$type] = Yii::$app->urlManager->createAbsoluteUrl($params);
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
