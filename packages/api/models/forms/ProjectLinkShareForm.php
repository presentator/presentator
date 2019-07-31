<?php
namespace presentator\api\models\forms;

use Yii;
use yii\validators\EmailValidator;
use presentator\api\validators\MultipleProxyValidator;
use presentator\api\models\ProjectLink;

/**
 * ProjectLink create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectLinkShareForm extends ApiForm
{
    /**
     * Email address to send the project link.
     * Use comma to separate multiple email addresses (eg. 'test1@example.com, test2@example.com').
     *
     * @var string
     */
    public $email;

    /**
     * Additional message that will be included in the shared project link email.
     *
     * @var string
     */
    public $message;

    /**
     * @var ProjectLink
     */
    protected $projectLink;

    /**
     * @param ProjectLink $projectLink
     * @param array       [$config]
     */
    public function __construct(ProjectLink $projectLink, array $config = [])
    {
        $this->setProjectLink($projectLink);

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['email']   = Yii::t('app', 'Email');
        $labels['message'] = Yii::t('app', 'Message');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['email', 'message'], 'required'];
        $rules[] = [
            'email',
            MultipleProxyValidator::class,
            'validatorClass' => EmailValidator::class,
        ];
        $rules[] = ['message', 'string', 'max' => 500];

        return $rules;
    }

    /**
     * @param ProjectLink $projectLink
     */
    public function setProjectLink(ProjectLink $projectLink): void
    {
        $this->projectLink = $projectLink;
    }

    /**
     * @return null|ProjectLink
     */
    public function getProjectLink(): ?ProjectLink
    {
        return $this->projectLink;
    }

    /**
     * Sends an email with project link info.
     *
     * @return boolean
     */
    public function send(): bool
    {
        if ($this->validate()) {
            return $this->getProjectLink()->sendShareEmail($this->email, $this->message);
        }

        return false;
    }
}
