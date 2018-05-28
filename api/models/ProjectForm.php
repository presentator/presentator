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
            [['title'], 'required'],
            [['title', 'password'], 'string', 'max' => 255],
            ['changePassword', 'boolean'],
        ];
    }

    /**
     * Creates or updates a Project model.
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

            if ($project->isNewRecord || $this->changePassword) {
                $project->setPassword($this->password);
            }

            $transaction = Project::getDb()->beginTransaction();
            $saveSuccess = false;
            try {
                if ($project->save()) {
                    if ($this->user) {
                        $project->linkUser($this->user, false);
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
