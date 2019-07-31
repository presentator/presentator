<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\models\Prototype;
use presentator\api\models\ProjectLink;
use yii\helpers\ArrayHelper;

/**
 * ProjectLink create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectLinkForm extends ApiForm
{
    /**
     * @var integer
     */
    public $projectId;

    /**
     * @var boolean
     */
    public $allowComments = true;

    /**
     * @var boolean
     */
    public $allowGuideline = true;

    /**
     * Set to an empty string to clear the current project link password.
     * Set to any string (4+ letters) to set a new project link password.
     * Set to `null` for no changes.
     *
     * @var null|string
     */
    public $password;

    /**
     * @var array
     */
    public $prototypes = [];

    /**
     * @var User
     */
    protected $user;

    /**
     * @var ProjectLink
     */
    protected $projectLink;

    /**
     * @param User             $user
     * @param null|ProjectLink $projectLink
     * @param array            [$config]
     */
    public function __construct(User $user, ProjectLink $projectLink = null, array $config = [])
    {
        $this->setUser($user);

        if ($projectLink) {
            $this->setProjectLink($projectLink);
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['allowComments']  = Yii::t('app', 'Allow comments');
        $labels['allowGuideline'] = Yii::t('app', 'Allow guideline');
        $labels['password']       = Yii::t('app', 'Password');
        $labels['prototypes']     = Yii::t('app', 'Prototypes');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['projectId', 'required'];
        $rules[] = ['projectId', 'validateUserProjectId'];
        $rules[] = ['password', 'string', 'min' => 4, 'max' => 71];
        $rules[] = [['allowComments', 'allowGuideline'], 'boolean'];
        $rules[] = [
            'prototypes',
            'exist',
            'targetClass' => Prototype::class,
            'targetAttribute' => 'id',
            'allowArray' => true,
            'filter' => function ($query) {
                $query->andWhere(['projectId' => $this->projectId]);
            }
        ];

        return $rules;
    }

    /**
     * Checks if the form user is the owner of the loaded project ID.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserProjectId($attribute, $params)
    {
        $user = $this->getUser();

        if (!$user || !$user->findProjectById((int) $this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid project ID.'));
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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param ProjectLink $projectLink
     */
    public function setProjectLink(ProjectLink $projectLink): void
    {
        $this->projectLink     = $projectLink;
        $this->projectId       = $projectLink->projectId;
        $this->allowComments   = $projectLink->allowComments;
        $this->allowGuideline = $projectLink->allowGuideline;
        $this->prototypes      = $projectLink->prototypes ? ArrayHelper::getColumn($projectLink->prototypes, 'id') : [];
    }

    /**
     * @return null|ProjectLink
     */
    public function getProjectLink(): ?ProjectLink
    {
        return $this->projectLink;
    }

    /**
     * Persists model form and returns the created/updated `ProjectLink` model.
     *
     * @return null|ProjectLink
     */
    public function save(): ?ProjectLink
    {
        if ($this->validate()) {
            $transaction = ProjectLink::getDb()->beginTransaction();

            try {
                $user        = $this->getUser();
                $projectLink = $this->getProjectLink() ?: (new ProjectLink);

                $projectLink->projectId       = $this->projectId;
                $projectLink->allowComments   = $this->allowComments;
                $projectLink->allowGuideline = $this->allowGuideline;

                if ($this->password !== null) {
                    $projectLink->setPassword($this->password);
                }

                if ($projectLink->save()) {
                    $projectLink->setPrototypes((array) $this->prototypes);

                    $transaction->commit();

                    $projectLink->refresh();

                    return $projectLink;
                }
            } catch(\Exception | \Throwable $e) {
                $transaction->rollBack();

                Yii::error($e->getMessage());
            }
        }

        return null;
    }
}
