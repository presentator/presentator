<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidConfigException;
use common\models\User;

/**
 * @todo Rename notifications to comments user setting (update also the UserNotificationForm)
 *
 * User form model intented to be used by system admins for User create/update.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class SuperUserForm extends Model
{
    const SCENARIO_UPDATE = 'scenario_update';
    const SCENARIO_CREATE = 'scenario_create';

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var integer
     */
    public $status;

    /**
     * @var integer
     */
    public $type;

    /**
     * @var boolean
     */
    public $changePassword = false;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $passwordConfirm;

    /**
     * @var boolean
     */
    public $notifications = true;

    /**
     * @var boolean
     */
    public $mentions = true;

    /**
     * @var User
     */
    private $user;

    /**
     * Model constructor.
     * @param User  $user
     * @param array $config
     */
    public function __construct(User $user = null, $config = [])
    {
        // set default scenario
        $this->scenario = self::SCENARIO_CREATE;

        if ($user) {
            if (!$user->isNewRecord) {
                $this->scenario = self::SCENARIO_UPDATE;
            }

            $this->loadUser($user);
        } else {
            $this->user = new User;
        }

        parent::__construct($config);

        if (
            $this->scenario == self::SCENARIO_UPDATE &&
            (!$this->user || $this->user->isNewRecord)
        ) {
            throw new InvalidConfigException('Existing user instance is required for update scenario.');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'firstName'       => Yii::t('app', 'First name'),
            'lastName'        => Yii::t('app', 'Last name'),
            'email'           => Yii::t('app', 'Email'),
            'changePassword'  => Yii::t('app', 'Change password'),
            'password'        => Yii::t('app', 'Password'),
            'passwordConfirm' => Yii::t('app', 'Password confirmation'),
            'notifications'   => Yii::t('app', 'Receive an email when a new screen comment is added'),
            'mentions'        => Yii::t('app', 'Receive an email when someone mentions you'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $baseFields = [
            'firstName', 'lastName', 'email',
            'status', 'type',
            'password', 'passwordConfirm',
            'notifications', 'mentions',
        ];

        $scenarios[self::SCENARIO_CREATE] = $baseFields;

        $scenarios[self::SCENARIO_UPDATE] = array_merge($baseFields, [
            'changePassword',
        ]);

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'status', 'type'], 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::className(), 'filter' => function ($query) {
                $query->andFilterWhere(['not', ['email' => $this->user->email]]);
            }],
            ['type', 'default', 'value' => User::TYPE_REGULAR],
            ['type', 'in', 'range' => array_keys(User::getTypeLabels())],
            ['status', 'default', 'value' => User::STATUS_INACTIVE],
            ['status', 'in', 'range' => array_keys(User::getStatusLabels())],
            [['firstName', 'lastName'], 'string', 'max' => 255],
            [['firstName', 'lastName'], 'filter', 'filter' => function ($value) {
                $value = trim($value);

                // capitalize first letter
                return (mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1));
            }],
            [['notifications', 'mentions'], 'boolean'],
            ['password', 'string', 'min' => 4, 'max' => 255],
            ['passwordConfirm', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('app', "Passwords don't match")],
            ['password', 'required', 'when' => function ($model) {
                if ($model->scenario == self::SCENARIO_CREATE) {
                    return true;
                }

                if ($model->changePassword) {
                    return true;
                }

                return false;
            }, 'whenClient' => 'function (attribute, value) {
                if ($("#superuserform-changepassword").length) {
                    return $("#superuserform-changepassword").is(":checked");
                }

                return true;
            }'],
        ];
    }

    /**
     * Loads User settings into the form model.
     * @param User $user
     */
    public function loadUser(User $user)
    {
        $this->user          = $user;
        $this->firstName     = $this->user->firstName;
        $this->lastName      = $this->user->lastName;
        $this->email         = $this->user->email;
        $this->status        = $this->user->status;
        $this->type          = $this->user->type;
        $this->notifications = $this->user->getSetting(User::NOTIFICATIONS_SETTING_KEY, true);
        $this->mentions      = $this->user->getSetting(User::MENTIONS_SETTING_KEY, true);
    }

    /**
     * Saves model settings to the user model.
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $user            = $this->user;
            $user->firstName = $this->firstName;
            $user->lastName  = $this->lastName;
            $user->email     = $this->email;
            $user->status    = $this->status;
            $user->type      = $this->type;

            if ($this->scenario == self::SCENARIO_CREATE || $this->changePassword) {
                $user->setPassword($this->password);
            }

            $transaction = User::getDb()->beginTransaction();
            try {
                $result = $user->save();
                $result = $result && $user->setSetting(User::NOTIFICATIONS_SETTING_KEY, $this->notifications ? true : false);
                $result = $result && $user->setSetting(User::MENTIONS_SETTING_KEY, $this->mentions ? true : false);

                if ($result) {
                    $transaction->commit();

                    return true;
                }

                $transaction->rollBack();
            } catch(\Exception $e) {
                $transaction->rollBack();
            } catch(\Throwable $e) {
                $transaction->rollBack();
            }
        }

        return false;
    }
}
