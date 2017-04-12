<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\Project;

/**
 * Preview Project access form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectAccessForm extends Model
{
    /**
     * @var string
     */
    public $password;

    /**
     * @var Project
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
            'password' => Yii::t('app', 'Password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Inline validator for the Project password.
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if ($this->project->isPasswordProtected() && !$this->project->validatePassword($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid password.'));
        }
    }

    /**
     * Grants access to a project preview.
     * @return boolean
     */
    public function grantAccess()
    {
        if ($this->validate()) {
            // optionally do something else...

            return true;
        }

        return false;
    }
}
