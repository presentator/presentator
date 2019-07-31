<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\validators\EmailDomainValidator;

/**
 * Request user email change form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserEmailChangeRequestForm extends ApiForm
{
    /**
     * @var string
     */
    public $newEmail;

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
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['newEmail', 'required'];
        $rules[] = ['newEmail', 'email'];
        $rules[] = ['newEmail', 'string', 'max' => 255];
        $rules[] = ['newEmail', 'validateNewEmail'];
        $rules[] = ['newEmail', EmailDomainValidator::class];

        return $rules;
    }

    /**
     * Inline user email change validator.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateNewEmail($attribute, $params)
    {
        // another user with the provided email shouldn't exist
        $userExist = User::find()->where(['email' => $this->{$attribute}])->exists();

        if ($userExist) {
            $this->addError($attribute, Yii::t('app', 'User with such email address already exist.'));
        }
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return null|User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sends a change email request to the provided form email address.
     *
     * @return boolean
     */
    public function send(): bool
    {
        $user = $this->getUser();

        if ($this->validate() && $user) {
            return $user->sendEmailChangeEmail($this->newEmail);
        }

        return false;
    }
}
