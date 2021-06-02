<?php
namespace presentator\api\models;

use Yii;
use yii\helpers\ArrayHelper;
use presentator\api\base\JWT;

/**
 * ProjectLink AR model
 *
 * @property integer $id
 * @property integer $projectId
 * @property string  $slug
 * @property string  $passwordHash
 * @property integer $allowComments
 * @property integer $allowGuideline
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectLink extends ActiveRecord
{
    use ProjectLinkQueryTrait;

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'projectId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectLinkPrototypeRels()
    {
        return $this->hasMany(ProjectLinkPrototypeRel::class, ['projectLinkId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrototypes()
    {
        return $this->hasMany(Prototype::class, ['id' => 'prototypeId'])
            ->via('projectLinkPrototypeRels');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProjectLinkRels()
    {
        return $this->hasMany(UserProjectLinkRel::class, ['projectLinkId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccessedUsers()
    {
        return $this->hasMany(User::class, ['id' => 'userId'])
            ->via('userProjectLinkRels');
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!$this->slug) {
                $this->generateSlug();
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();

        unset($fields['passwordHash']);

        $fields['passwordProtected'] = function ($model, $field) {
            return $model->isPasswordProtected();
        };

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields[] = 'prototypes';

        $extraFields['projectInfo'] = function ($model, $field) {
            return $model->project->toArray(['id', 'title', 'archived']);;
        };

        return $extraFields;
    }

    /**
     * Returns whether the current model requires password authentication or not.
     *
     * @return boolean
     */
    public function isPasswordProtected() {
        return !empty($this->passwordHash);
    }

    /**
     * Validates prototypes password string.
     *
     * @param  string $password
     * @return boolean
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * Sets prototype password.
     * Pass an empty string/null to remove previously set password.
     *
     * @param null|string $password
     */
    public function setPassword(?string $password): void
    {
        if ($password) {
            $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
        } else {
            $this->passwordHash = null;
        }
    }

    /**
     * Link/unlinks prototypes to the current model.
     * Pass an empty array to reset/unlink all linked prototypes.
     *
     * @param  array $prototypeIds
     * @return boolean
     */
    public function setPrototypes(array $prototypeIds): bool
    {
        $transaction = static::getDb()->beginTransaction();

        try {
            foreach ($this->project->prototypes as $prototype) {
                if (in_array($prototype->id, $prototypeIds)) {
                    $this->linkOnce('prototypes', $prototype);
                } else {
                    $this->unlink('prototypes', $prototype, true);
                }
            }

            $transaction->commit();

            return true;
        } catch(\Exception | \Throwable $e) {
            $transaction->rollBack();

            Yii::error($e->getMessage());
        }

        return false;
    }

    /**
     * Sets random unique string as a slug.
     *
     * @param integer $minLength Slug minumum length.
     */
    public function generateSlug($minLength = 8)
    {
        $alphabet = [
            ['abcdefghijklmnopqrstuvwxyz', 6], // min 6 chars
            ['123456789', 2],                  // min 2 char
        ];

        // generate random slug
        do {
            $slug = Yii::$app->security->generateRandomString($minLength++, $alphabet);
        } while (self::hasSlug($slug));

        $this->slug = $slug;
    }

    /**
     * Checks if project link with the provided slug exists.
     *
     * @param  string $slug
     * @return boolean
     */
    public static function hasSlug(string $slug): bool
    {
        return static::find()
            ->where(['slug' => $slug])
            ->exists();
    }

    /**
     * Returns single `ProjectLink` model by its slug.
     *
     * @param  string $slug
     * @param  array  [$filters]
     * @return null|ProjectLink
     */
    public static function findBySlug(string $slug, array $filters = []): ?ProjectLink
    {
        return static::find()
            ->where(['slug' => $slug])
            ->andFilterWhere($filters)
            ->one();
    }

    /**
     * Sends an email to the specified receivers with info about the current `ProjectLink` model.
     *
     * @param  string $email     Receiver(s) email address(es) (use commma to separate multiple receivers).
     * @param  string [$message] Additional message that will be appended to the email body.
     * @return boolean
     */
    public function sendShareEmail(string $email, string $message = ''): bool
    {
        $receivers = array_unique(array_map('trim', explode(',', $email)));

        $result = true;

        foreach ($receivers as $receiver) {
            $result &= Yii::$app->mailer->compose('project_link_share', [
                    'projectLink' => $this,
                    'message'     => $message,
                ])
                ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
                ->setTo($receiver)
                ->setSubject('Presentator - ' . Yii::t('mail', '{projectTitle} preview', ['projectTitle' => $this->project->title]))
                ->send();
        }

        return $result;
    }

    /* Preview token
    --------------------------------------------------------------- */
    /**
     * Registers that the provided user has accessed/previewed the `ProjectLink` model.
     *
     * @param  User $user
     * @return bool
     */
    public function logUserAccess(User $user): bool
    {
        $rel = UserProjectLinkRel::findOne([
            'projectLinkId' => $this->id,
            'userId' => $user->id,
        ]);

        try {
            if ($rel) {
                // existing rel
                $rel->touch('updatedAt');
            } else {
                // new rel
                $this->link('accessedUsers', $user);
            }

            return true;
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return false;
    }

    /**
     * Generates and returns a unique preview token secret key.
     *
     * @return string
     */
    protected function getPreviewTokenSecret(): string
    {
        return Yii::$app->params['previewTokenSecret'] . md5($this->slug . $this->passwordHash);
    }

    /**
     * Generates new JWT preview token.
     *
     * @return string
     */
    public function generatePreviewToken(): string
    {
        $duration = ArrayHelper::getValue(Yii::$app->params, 'previewTokenDuration', 3600);
        $secret   = $this->getPreviewTokenSecret();;

        $payload = [
            "iss" => "presentator_api",
            "iat" => time(),
            "exp" => (time() + $duration),
            "slug" => $this->slug,
        ];

        return JWT::encode($payload, $secret);
    }

    /**
     * Returns single `ProjectLink` model by a valid preview token.
     *
     * @param  string $token
     * @return null|ProjectLink
     */
    public static function findByPreviewToken(string $token): ?ProjectLink
    {
        try {
            // fetch the preview link
            $payload = JWT::unsafeDecode($token);
            $link = !empty($payload->slug) ? static::findBySlug($payload->slug) : null;

            if ($link && JWT::isValid($token, $link->getPreviewTokenSecret())) {
                return $link;
            }
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return null;
    }
}
