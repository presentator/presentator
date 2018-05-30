<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\validators\EmailValidator;
use common\models\Project;
use common\models\ProjectPreview;

/**
 * Form model that takes care for sending public Project share link invitations.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectShareForm extends Model
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $message;

    /**
     * @var boolean
     */
    public $allowComments = true;

    /**
     * @var Screen
     */
    private $project;

    /**
     * Model constructor.
     * @param Project $project
     * @param array  $config
     */
    public function __construct(Project $project, $config = [])
    {
        $this->project = $project;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'         => Yii::t('app', 'Email'),
            'message'       => Yii::t('app', 'Message (optional)'),
            'allowComments' => Yii::t('app', 'Allow to read and leave comments'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'validateEmails'],
            ['allowComments', 'boolean'],
            [['email', 'message'], 'string', 'max' => 255],
            ['message', 'filter', 'filter' => function ($value) {
                return strip_tags($value);
            }],
        ];
    }

    /**
     * Helper to extract the valid email addresses.
     * @return array
     */
    protected function extractValidEmails()
    {
        $emails = [];

        $parts = explode(',', $this->email);
        $validator = new EmailValidator();
        foreach ($parts as $email) {
            $email = trim($email);
            if ($validator->validate($email)) {
                $emails[] = $email;
            }
        }

        return $emails;
    }

    /**
     * Inline email validator with support for multiple emails.
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateEmails($attribute, $params)
    {
        $parts       = explode(',', $this->{$attribute});
        $validEmails = $this->extractValidEmails();

        if (count($parts) !== count($validEmails)) {
            $this->addError($attribute, Yii::t('app', 'Invalid format.'));
        }
    }

    /**
     * Getter for the `project` model property.
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Sends public project share link.
     * @return boolean
     */
    public function send()
    {
        if ($this->validate()) {
            if ($this->allowComments) {
                $preview = $this->project->findPreviewByType(ProjectPreview::TYPE_VIEW_AND_COMMENT);
            } else {
                $preview = $this->project->findPreviewByType(ProjectPreview::TYPE_VIEW);
            }

            if ($preview) {
                return $preview->sendPreviewEmail($this->extractValidEmails(), $this->message);
            }
        }

        return false;
    }
}
