<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\models\Project;

/**
 * Project create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectForm extends ApiForm
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var boolean
     */
    public $archived = false;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @param User         $user
     * @param null|Project $project
     * @param array        [$config]
     */
    public function __construct(User $user, Project $project = null, array $config = [])
    {
        $this->setUser($user);

        if ($project) {
            $this->setProject($project);
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['title']    = Yii::t('app', 'Title');
        $labels['archived'] = Yii::t('app', 'Archived');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['title', 'required'],
            ['title', 'string', 'max' => 255],
            ['archived', 'boolean'],
        ];
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
     * @param Project $project
     */
    public function setProject(Project $project): void
    {
        $this->project  = $project;
        $this->title    = $project->title;
        $this->archived = $project->archived ? true : false;
    }

    /**
     * @return null|Project
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * Persists model form and returns the created/updated `Project` model.
     *
     * @return null|Project
     */
    public function save(): ?Project
    {
        if ($this->validate()) {
            $transaction = Project::getDb()->beginTransaction();

            try {
                $user    = $this->getUser();
                $project = $this->getProject() ?: (new Project);

                $project->title    = $this->title;
                $project->archived = $this->archived;

                if ($project->save()) {
                    $project->linkOnce('users', $user);

                    $transaction->commit();

                    $project->refresh();

                    return $project;
                }
            } catch(\Exception | \Throwable $e) {
                $transaction->rollBack();

                Yii::error($e->getMessage());
            }
        }

        return null;
    }
}
