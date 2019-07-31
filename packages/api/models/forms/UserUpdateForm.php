<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\models\UserSetting;
use presentator\api\validators\EmailDomainValidator;

/**
 * User update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserUpdateForm extends ApiForm
{
    const SCENARIO_REGULAR = 'scenarioRegularUser';
    const SCENARIO_SUPER   = 'scenarioSuperUser';

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var boolean
     */
    public $notifyOnEachComment = true;

    /**
     * @var boolean
     */
    public $notifyOnMention = false;

    /**
     * @var string
     */
    public $oldPassword;

    /**
     * @var string
     */
    public $newPassword;

    /**
     * @var string
     */
    public $newPasswordConfirm;

    /**
     * @var boolean
     */
    public $deleteAvatar = false;

    /**
     * @var \yii\web\UploadedFile
     */
    public $avatar;

    /**
     * @var boolean
     */
    public $status;

    /**
     * @var boolean
     */
    public $type;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param User  $user
     * @param array [$config]
     */
    public function __construct(User $user, $config = [])
    {
        $this->setUser($user);

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['email']               = Yii::t('app', 'Email');
        $labels['firstName']           = Yii::t('app', 'First name');
        $labels['lastName']            = Yii::t('app', 'Last name');
        $labels['notifyOnEachComment'] = Yii::t('app', 'Notify on each comment');
        $labels['notifyOnMention']     = Yii::t('app', 'Notify on mention');
        $labels['oldPassword']         = Yii::t('app', 'Old password');
        $labels['newPassword']         = Yii::t('app', 'New password');
        $labels['newPasswordConfirm']  = Yii::t('app', 'New password confirm');
        $labels['deleteAvatar']        = Yii::t('app', 'Delete avatar');
        $labels['avatar']              = Yii::t('app', 'Avatar');
        $labels['status']              = Yii::t('app', 'Status');
        $labels['type']                = Yii::t('app', 'Type');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['notifyOnEachComment', 'notifyOnMention'], 'boolean'];
        $rules[] = [['firstName', 'lastName', 'email'], 'string', 'max' => 255];
        $rules[] = ['email', 'required', 'on' => self::SCENARIO_SUPER];
        $rules[] = ['email', 'validateNewEmail', 'on' => self::SCENARIO_SUPER];
        $rules[] = ['email', EmailDomainValidator::class, 'on' => self::SCENARIO_SUPER];
        $rules[] = ['email', 'email'];
        $rules[] = ['oldPassword', 'validateOldPassword', 'on' => self::SCENARIO_REGULAR];
        $rules[] = ['oldPassword', 'required', 'when' => function ($model) {
            return !empty($model->newPassword) || !empty($model->newPasswordConfirm);
        }, 'on' => self::SCENARIO_REGULAR];
        $rules[] = [['newPassword', 'newPasswordConfirm'], 'required', 'when' => function ($model) {
            return !empty($model->oldPassword);
        }];
        $rules[] = [['newPassword', 'newPasswordConfirm'], 'string', 'min' => 6, 'max' => 71];
        $rules[] = ['newPasswordConfirm', 'compare', 'compareAttribute' => 'newPassword', 'message' => Yii::t('app', "Passwords don't match.")];
        $rules[] = ['deleteAvatar', 'boolean'];
        $rules[] = [
            'avatar',
            'file',
            'skipOnEmpty' => true,
            'maxFiles'    => 1,
            'maxSize'     => (1024 * 1024 * Yii::$app->params['maxAvatarUploadSize']),
            'mimeTypes'   => Yii::$app->params['allowedAvatarMimetypes'],
            'checkExtensionByMimeType' => false,
        ];
        $rules[] = [['status', 'type'], 'required', 'on' => self::SCENARIO_SUPER];
        $rules[] = ['status', 'in', 'range' => array_values(User::STATUS)];
        $rules[] = ['type', 'in', 'range' => array_values(User::TYPE)];

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $baseFields = [
            'firstName', 'lastName',
            'notifyOnEachComment', 'notifyOnMention',
            'newPassword', 'newPasswordConfirm',
            'deleteAvatar', 'avatar',
        ];

        $scenarios[self::SCENARIO_REGULAR] = array_merge($baseFields, [
            'oldPassword',
        ]);

        $scenarios[self::SCENARIO_SUPER] = array_merge($baseFields, [
            'email', 'status', 'type',
        ]);

        return $scenarios;
    }

    /**
     * Inline user password validator.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateOldPassword($attribute, $params)
    {
        $user = $this->getUser();

        if (!$user || !$user->validatePassword($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid password.'));
        }
    }

    /**
     * Inline user email change validator.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateNewEmail($attribute, $params)
    {
        $newEmail = $this->{$attribute};

        $user = $this->getUser();
        if ($user->email === $newEmail) {
            return; // no email change
        }

        // another user with the provided email shouldn't exist
        $emailExist = User::find()->where(['email' => $newEmail])->exists();
        if ($emailExist) {
            $this->addError($attribute, 'User with such email address already exist.');
        }
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;

        $this->email               = $user->email;
        $this->firstName           = $user->firstName;
        $this->lastName            = $user->lastName;
        $this->status              = $user->status;
        $this->type                = $user->type;
        $this->notifyOnEachComment = $user->getSetting(UserSetting::NOTIFY_ON_EACH_COMMENT);
        $this->notifyOnMention     = $user->getSetting(UserSetting::NOTIFY_ON_MENTION);
    }

    /**
     * @return null|User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Persists form changes and update the form user model.
     *
     * @return null|User
     */
    public function save(): ?User
    {
        $user = $this->getUser();

        if ($this->validate() && $user) {
            if (
                ($this->oldPassword || $this->scenario === self::SCENARIO_SUPER) &&
                $this->newPassword &&
                $this->newPasswordConfirm
            ) {
                // change password
                $user->setPassword($this->newPassword);
                $user->generateAuthKey();
            }

            if ($this->scenario === self::SCENARIO_SUPER) {
                $user->email  = $this->email;
                $user->status = $this->status;
                $user->type   = $this->type;
            }

            $result = $user->saveWithSettings([
                'firstName' => $this->firstName,
                'lastName'  => $this->lastName,
            ], [
                [UserSetting::NOTIFY_ON_EACH_COMMENT, $this->notifyOnEachComment, UserSetting::TYPE['BOOLEAN']],
                [UserSetting::NOTIFY_ON_MENTION, (($this->notifyOnMention || $this->notifyOnEachComment) ? true : false), UserSetting::TYPE['BOOLEAN']],
            ]);

            if ($result) {
                if ($this->deleteAvatar) {
                    $user->deleteFile();
                } elseif ($this->avatar) {
                    $user->saveFile($this->avatar);
                }

                $user->refresh();

                return $user;
            }
        }

        return null;
    }
}
