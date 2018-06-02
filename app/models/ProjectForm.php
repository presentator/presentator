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
            $this->setProject($project);
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
            [['title'], 'required'],
            [['title', 'password'], 'string', 'max' => 255],
            [['isPasswordProtected', 'changePassword'], 'boolean'],
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
     * Loads a single Project AR into the form model.
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
        $this->title   = $project->title;

        if ($project->passwordHash) {
            $this->isPasswordProtected = true;
        } else {
            $this->isPasswordProtected = false;
        }
    }

    /**
     * Getter for the `$project` property.
     * @return null|Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Check whether the form is for update or create.
     * @return boolean
     */
    public function isUpdate()
    {
        return $this->project && !$this->project->isNewRecord;
    }

    /**
     * Creates or update a Project model.
     * @param  null|User    User to link to the created/updated project.
     * @return null|Project The created/updated project on success, otherwise - null.
     */
    public function save(User $user = null)
    {
        if ($this->validate()) {
            $project = $this->project ? $this->project : (new Project);

            $project->title = $this->title;

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
