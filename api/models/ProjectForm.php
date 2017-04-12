<?php
namespace api\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\Project;

/**
 * API Project form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectForm extends Model
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var integer
     */
    public $type;

    /**
     * @var integer
     */
    public $subtype;

    /**
     * @var string
     */
    public $password;

    /**
     * @var boolean
     */
    public $changePassword = false;

    /**
     * @var User
     */
    private $user;

    /**
     * Model constructor.
     * @param User $user
     * @param array  $config
     */
    public function __construct(User $user, $config = [])
    {
        $this->user = $user;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'type'], 'required'],
            [['title', 'password'], 'string', 'max' => 255],
            ['changePassword', 'boolean'],
            ['type', 'in', 'range' => array_keys(Project::getTypeLabels())],
            ['subtype', 'validateSubtypeRange'],
            ['subtype', 'required', 'when' => function ($model) {
                if ($model->type != Project::TYPE_DESKTOP) {
                    return true;
                }

                return false;
            }],
        ];
    }

    /**
     * Subtype custom range validator.
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateSubtypeRange($attribute, $params)
    {
        if (
            ($this->type === Project::TYPE_TABLET && !array_key_exists($this->subtype, Project::getTabletSubtypeLabels())) ||
            ($this->type === Project::TYPE_MOBILE && !array_key_exists($this->subtype, Project::getMobileSubtypeLabels())) ||
            ($this->subtype && !array_key_exists($this->subtype, Project::SUBTYPES))
        ) {
            $this->addError($attribute, Yii::t('app', 'Invalid value.'));
        }
    }

    /**
     * Creates or update a Project model.
     * @param  Project|null $project
     * @return Project|null The created/updated project on success, otherwise - null.
     */
    public function save(Project $project = null)
    {
        if ($this->validate()) {
            if (!$project) {
                // create
                $project = new Project;
            }

            $project->title = $this->title;
            $project->type  = (int) $this->type;

            if ($this->type != Project::TYPE_DESKTOP) {
                $project->subtype = (int) $this->subtype;
            } else {
                $project->subtype = null;
            }

            if ($project->isNewRecord || $this->changePassword) {
                $project->setPassword($this->password);
            }

            if ($project->save()) {
                $project->linkUser($this->user, false);

                return $project;
            }
        }

        return null;
    }
}
