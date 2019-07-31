<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\models\GuidelineSection;

/**
 * GuidelineSection create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineSectionForm extends ApiForm
{
    /**
     * @var integer
     */
    public $projectId;

    /**
     * @var integer
     */
    public $order;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var User
     */
    protected $user;

    /**
     * @var GuidelineSection
     */
    protected $section;

    /**
     * @param User                  $user
     * @param null|GuidelineSection $section
     * @param array                 [$config]
     */
    public function __construct(User $user, GuidelineSection $section = null, array $config = [])
    {
        $this->setUser($user);

        if ($section) {
            $this->setGuidelineSection($section);
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['order']       = Yii::t('app', 'Order');
        $labels['title']       = Yii::t('app', 'Title');
        $labels['description'] = Yii::t('app', 'Description');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['projectId', 'title'], 'required'];
        $rules[] = ['projectId', 'validateUserProjectId'];
        $rules[] = ['order', 'integer', 'min' => 0];
        $rules[] = ['order', 'default', 'value' => 0];
        $rules[] = [['title', 'description'], 'string', 'max' => 255];

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
     * @param GuidelineSection $section
     */
    public function setGuidelineSection(GuidelineSection $section): void
    {
        $this->section     = $section;
        $this->order       = $section->order;
        $this->projectId   = $section->projectId;
        $this->title       = $section->title;
        $this->description = $section->description;
    }

    /**
     * @return null|GuidelineSection
     */
    public function getGuidelineSection(): ?GuidelineSection
    {
        return $this->section;
    }

    /**
     * Persists model form and returns the created/updated `GuidelineSection` model.
     *
     * @return null|GuidelineSection
     */
    public function save(): ?GuidelineSection
    {
        if ($this->validate()) {
            $section = $this->getGuidelineSection() ?: (new GuidelineSection);

            $section->projectId   = $this->projectId;
            $section->order       = $this->order;
            $section->title       = $this->title;
            $section->description = $this->description;

            if ($section->save()) {
                $section->refresh();

                return $section;
            }
        }

        return null;
    }
}
