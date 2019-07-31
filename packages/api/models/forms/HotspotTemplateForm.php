<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\models\HotspotTemplate;

/**
 * HotspotTemplate create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotTemplateForm extends ApiForm
{
    const SCENARIO_CREATE = 'scenarioCreate';
    const SCENARIO_UPDATE = 'scenarioUpdate';

    /**
     * @var integer
     */
    public $prototypeId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var HotspotTemplate
     */
    protected $template;

    /**
     * @param User                 $user
     * @param null|HotspotTemplate $template
     * @param array                [$config]
     */
    public function __construct(User $user, HotspotTemplate $template = null, array $config = [])
    {
        $this->setUser($user);

        if ($template) {
            $this->setHotspotTemplate($template);
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['title'] = Yii::t('app', 'Title');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['prototypeId', 'title'], 'required'];
        $rules[] = ['prototypeId', 'validateUserPrototypeId'];
        $rules[] = ['title', 'string', 'max' => 255];

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CREATE] = [
            'prototypeId', 'title',
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'title',
        ];

        return $scenarios;
    }

    /**
     * Checks if the form user is the owner of the loaded prototype ID.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserPrototypeId($attribute, $params)
    {
        $user = $this->getUser();

        if (!$user || !$user->findPrototypeById((int) $this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid prototype ID.'));
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
     * @param HotspotTemplate $template
     */
    public function setHotspotTemplate(HotspotTemplate $template): void
    {
        $this->template    = $template;
        $this->prototypeId = $template->prototypeId;
        $this->title       = $template->title;
    }

    /**
     * @return null|HotspotTemplate
     */
    public function getHotspotTemplate(): ?HotspotTemplate
    {
        return $this->template;
    }

    /**
     * Persists model form and returns the created/updated `HotspotTemplate` model.
     *
     * @return null|HotspotTemplate
     */
    public function save(): ?HotspotTemplate
    {
        if ($this->validate()) {
            $template = $this->getHotspotTemplate() ?: (new HotspotTemplate);

            $template->prototypeId = $this->prototypeId;
            $template->title       = (string) $this->title;

            if ($template->save()) {
                $template->refresh();

                return $template;
            }
        }

        return null;
    }
}
