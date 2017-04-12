<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\Project;

/**
 * Project form model.
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
    public $isPasswordProtected = false;

    /**
     * @var boolean
     */
    public $changePassword = false;

    /**
     * Project model to update
     * @var null|Project
     */
    private $project;

    /**
     * Model constructor.
     * @param null|Project $project
     * @param array        $config
     */
    public function __construct(Project $project = null, $config = [])
    {
        if ($project) {
            $this->loadProject($project);
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title'               => Yii::t('app', 'Title'),
            'type'                => Yii::t('app', 'Type'),
            'subtype'             => Yii::t('app', 'Subtype'),
            'password'            => Yii::t('app', 'Password'),
            'isPasswordProtected' => Yii::t('app', 'Is password protected (optional)'),
            'changePassword'      => Yii::t('app', 'Change password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'type'], 'required'],
            [['title', 'password'], 'string', 'max' => 255],
            [['isPasswordProtected', 'changePassword'], 'boolean'],
            ['type', 'in', 'range' => array_keys(Project::getTypeLabels())],
            ['subtype', 'validateSubtypeRange'],
            ['subtype', 'required', 'when' => function ($model) {
                if ($model->type !== Project::TYPE_DESKTOP) {
                    return true;
                }

                return false;
            }, 'whenClient' => 'function (attribute, value) {
                if (!$("#project_type_0").is(":checked")) {
                    return true;
                }

                return false;
            }'],
            ['password', 'required', 'when' => function ($model) {
                if ($this->project && !$this->project->isNewRecord) {
                    // update form...
                    if ($model->isPasswordProtected && $model->changePassword) {
                        return true;
                    }
                } else {
                    // create form...
                    if ($model->isPasswordProtected) {
                        return true;
                    }
                }

                return false;
            }, 'whenClient' => 'function (attribute, value) {
                if ($("#projectform-ispasswordprotected").is(":checked")) {
                    if (!$("#projectform-changepassword").is(":visible")|| $("#projectform-changepassword").is(":checked")) {
                        return true;
                    }
                }

                return false;
            }'],
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
            ($this->type == Project::TYPE_TABLET && !array_key_exists($this->{$attribute}, Project::getTabletSubtypeLabels())) ||
            ($this->type == Project::TYPE_MOBILE && !array_key_exists($this->{$attribute}, Project::getMobileSubtypeLabels()))
        ) {
            $this->addError($attribute, Yii::t('app', 'Invalid value.'));
        }
    }

    /**
     * Loads a single Project AR into the form model.
     */
    public function loadProject(Project $project)
    {
        $this->project = $project;
        $this->title   = $project->title;
        $this->type    = $project->type;
        $this->subtype = $project->subtype;

        if ($project->passwordHash) {
            $this->isPasswordProtected = true;
        } else {
            $this->isPasswordProtected = false;
        }
    }

    /**
     * Creates or update a Project model.
     * @return Project|null The created/updated project on success, otherwise - null.
     */
    public function save(User $user = null)
    {
        if ($this->validate()) {
            $project = $this->project ? $this->project : (new Project);

            $project->title = $this->title;
            $project->type  = $this->type;

            if ($this->type != Project::TYPE_DESKTOP) {
                $project->subtype = $this->subtype;
            } else {
                $project->subtype = null;
            }

            if ($this->isPasswordProtected) {
                if (!$project->passwordHash || $this->changePassword) {
                    $project->setPassword($this->password);
                }
            } else {
                $project->setPassword(null);
            }

            $transaction = Project::getDb()->beginTransaction();
            $saveSuccess = false;
            try {
                if ($project->save()) {
                    if ($user) {
                        $project->linkUser($user, false);
                    }

                    $saveSuccess = true;

                    $transaction->commit();
                }
            } catch(\Exception $e) {
                $transaction->rollBack();
            } catch(\Throwable $e) { // PHP 7
                $transaction->rollBack();
            }

            if ($saveSuccess) {
                return $project;
            }
        }

        return null;
    }
}
