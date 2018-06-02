<?php
namespace api\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\Version;

/**
 * API Version form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionForm extends Model
{
    const SCENARIO_CREATE = 'scenarioCreate';
    const SCENARIO_UPDATE = 'scenarioUpdate';

    /**
     * @var integer
     */
    public $projectId;

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
     * Auto scale flag for mobile and tablet Version types.
     * @var boolean
     */
    public $autoScale = false;

    /**
     * Retina scale flag for desktop Version types.
     * @var boolean
     */
    public $retinaScale = false;

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
            ['projectId', 'required', 'on' => self::SCENARIO_CREATE],
            ['projectId', 'validateUserProjectId'],
            [['title'], 'string', 'max' => 100],
            [['title'], 'filter', 'filter' => 'strip_tags'],
            [['autoScale', 'retinaScale'], 'boolean'],
            [['type'], 'required'],
            ['type', 'in', 'range' => array_keys(Version::getTypeLabels())],
            ['subtype', 'validateSubtypeRange'],
            ['subtype', 'required', 'when' => function ($model) {
                if ($model->type !== Version::TYPE_DESKTOP) {
                    return true;
                }

                return false;
            }],

        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CREATE] = [
            'projectId', 'title', 'type', 'subtype',
            'autoScale', 'retinaScale',
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'title', 'type', 'subtype',
            'autoScale', 'retinaScale',
        ];

        return $scenarios;
    }

    /**
     * Checkes if the form user is the owner of the provided project ID.
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserProjectId($attribute, $params)
    {
        if (!$this->user || !$this->user->findProjectById($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid project ID.'));
        }
    }

    /**
     * Subtype custom range validator.
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateSubtypeRange($attribute, $params)
    {
        if (
            ($this->type == Version::TYPE_TABLET && !array_key_exists($this->{$attribute}, Version::getTabletSubtypeLabels())) ||
            ($this->type == Version::TYPE_MOBILE && !array_key_exists($this->{$attribute}, Version::getMobileSubtypeLabels()))
        ) {
            $this->addError($attribute, Yii::t('app', 'Invalid value.'));
        }
    }

    /**
     * Creates or updates a Version model.
     * @param  Project|null $version
     * @return Version|null The created/updated version on success, otherwise - null.
     */
    public function save(Version $version = null)
    {
        if ($this->validate()) {
            if (!$version) {
                // create
                $version = new Version;
                $version->projectId = (int) $this->projectId;
            }

            $version->title = $this->title;
            $version->type  = $this->type;

            if ($this->type != Version::TYPE_DESKTOP) {
                $version->subtype = $this->subtype;

                $version->scaleFactor = $this->autoScale ? Version::AUTO_SCALE_FACTOR : Version::DEFAULT_SCALE_FACTOR;
            } else {
                $version->subtype = null;

                $version->scaleFactor = $this->retinaScale ? Version::RETINA_SCALE_FACTOR : Version::DEFAULT_SCALE_FACTOR;
            }

            if ($version->save()) {
                $version->refresh();

                return $version;
            }
        }

        return null;
    }
}
