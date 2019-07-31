<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\models\Prototype;

/**
 * Prototype create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PrototypeForm extends ApiForm
{
    /**
     * @var integer
     */
    public $projectId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $title;

    /**
     * @var float
     */
    public $width;

    /**
     * @var float
     */
    public $height;

    /**
     * @var float
     */
    public $scaleFactor = 1;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Prototype
     */
    protected $prototype;

    /**
     * @param User           $user
     * @param null|Prototype $prototype
     * @param array          [$config]
     */
    public function __construct(User $user, Prototype $prototype = null, array $config = [])
    {
        $this->setUser($user);

        if ($prototype) {
            $this->setPrototype($prototype);
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['type']        = Yii::t('app', 'Type');
        $labels['title']       = Yii::t('app', 'Title');
        $labels['width']       = Yii::t('app', 'Width');
        $labels['height']      = Yii::t('app', 'Height');
        $labels['scaleFactor'] = Yii::t('app', 'Scale factor');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['projectId', 'type', 'title'], 'required'];
        $rules[] = ['projectId', 'validateUserProjectId'];
        $rules[] = ['type', 'in', 'range' => array_values(Prototype::TYPE)];
        $rules[] = ['title', 'string', 'max' => 255];
        $rules[] = ['scaleFactor', 'number', 'min' => 0];
        $rules[] = ['scaleFactor', 'default', 'value' => 1];
        $rules[] = [['width', 'height'], 'required', 'when' => function ($model) {
            return $model->type != Prototype::TYPE['DESKTOP'];
        }];
        $rules[] = [['width', 'height'], 'number', 'min' => 100, 'when' => function ($model) {
            return $model->type != Prototype::TYPE['DESKTOP'];
        }];

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
     * @param Prototype $prototype
     */
    public function setPrototype(Prototype $prototype): void
    {
        $this->prototype   = $prototype;
        $this->projectId   = $prototype->projectId;
        $this->type        = $prototype->type;
        $this->title       = $prototype->title;
        $this->width       = $prototype->width;
        $this->height      = $prototype->height;
        $this->scaleFactor = $prototype->scaleFactor;
    }

    /**
     * @return null|Prototype
     */
    public function getPrototype(): ?Prototype
    {
        return $this->prototype;
    }

    /**
     * Persists model form and returns the created/updated `Prototype` model.
     *
     * @return null|Prototype
     */
    public function save(): ?Prototype
    {
        if ($this->validate()) {
            $prototype = $this->getPrototype() ?: (new Prototype);

            $prototype->projectId   = $this->projectId;
            $prototype->title       = $this->title;
            $prototype->type        = $this->type;
            $prototype->scaleFactor = (float) $this->scaleFactor;

            if ($this->type == Prototype::TYPE['DESKTOP']) {
                $prototype->width  = 0;
                $prototype->height = 0;
            } else {
                $prototype->width  = (float) $this->width;
                $prototype->height = (float) $this->height;
            }

            if ($prototype->save()) {
                $prototype->refresh();

                return $prototype;
            }
        }

        return null;
    }
}
