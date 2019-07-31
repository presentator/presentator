<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\models\UserSetting;
use presentator\api\validators\EmailDomainValidator;

/**
 * User create form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserCreateForm extends ApiForm
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
    public $password;

    /**
     * @var string
     */
    public $passwordConfirm;

    /**
     * @var string
     */
    public $firstName = '';

    /**
     * @var string
     */
    public $lastName = '';

    /**
     * @var boolean
     */
    public $notifyOnEachComment = true;

    /**
     * @var boolean
     */
    public $notifyOnMention = false;

    /**
     * @var boolean
     */
    public $status;

    /**
     * @var boolean
     */
    public $type;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['email']               = Yii::t('app', 'Email');
        $labels['password']            = Yii::t('app', 'Password');
        $labels['passwordConfirm']     = Yii::t('app', 'Password confirm');
        $labels['firstName']           = Yii::t('app', 'First name');
        $labels['lastName']            = Yii::t('app', 'Last name');
        $labels['notifyOnEachComment'] = Yii::t('app', 'Notify on each comment');
        $labels['notifyOnMention']     = Yii::t('app', 'Notify on mention');
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

        $rules[] = [['email', 'password', 'passwordConfirm'], 'required'];
        $rules[] = [['notifyOnEachComment', 'notifyOnMention'], 'boolean'];
        $rules[] = [['email', 'firstName', 'lastName'], 'string', 'max' => 255];
        $rules[] = ['email', 'email'];
        $rules[] = ['email', 'unique', 'targetClass' => User::class];
        $rules[] = ['email', EmailDomainValidator::class];
        $rules[] = [['password', 'passwordConfirm'], 'string', 'min' => 6, 'max' => 71];
        $rules[] = ['passwordConfirm', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('app', "Passwords don't match.")];
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
            'email', 'firstName', 'lastName',
            'password', 'passwordConfirm',
            'notifyOnEachComment', 'notifyOnMention',
        ];

        $scenarios[self::SCENARIO_REGULAR] = $baseFields;

        $scenarios[self::SCENARIO_SUPER] = array_merge($baseFields, [
            'status', 'type',
        ]);

        return $scenarios;
    }

    /**
     * Register a new user by creating a new User model.
     * It sends an activation email to the user if its status is inactive.
     *
     * @return null|User
     */
    public function save(): ?User
    {
        if ($this->validate()) {
            $user = new User;

            $user->setPassword($this->password);

            $result = $user->saveWithSettings([
                'email'     => $this->email,
                'firstName' => $this->firstName,
                'lastName'  => $this->lastName,
                'status'    => ($this->scenario === self::SCENARIO_SUPER ? $this->status : User::STATUS['INACTIVE']),
                'type'      => ($this->scenario === self::SCENARIO_SUPER ? $this->type : User::TYPE['REGULAR']),
            ], [
                [UserSetting::NOTIFY_ON_EACH_COMMENT, $this->notifyOnEachComment, UserSetting::TYPE['BOOLEAN']],
                [UserSetting::NOTIFY_ON_MENTION, (($this->notifyOnMention || $this->notifyOnEachComment) ? true : false), UserSetting::TYPE['BOOLEAN']],
            ]);

            if ($result) {
                if ($user->status == User::STATUS['INACTIVE']) {
                    $user->sendActivationEmail();
                }

                $user->refresh();

                return $user;
            }
        }

        return null;
    }
}
