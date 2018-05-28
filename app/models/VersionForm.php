<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\Project;
use common\models\Version;

/**
 * Version form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionForm extends Model
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var integer
     */
    public $type = Version::TYPE_DESKTOP;

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
     * Project model to assign the version.
     * @var Project
     */
    private $project;

    /**
     * Version model to update.
     * @var null|Version
     */
    private $version;

    /**
     * Model constructor.
     * @param null|Version $version
     * @param null|Project $project
     * @param array        $config
     */
    public function __construct(Project $project, Version $version = null, $config = [])
    {
        $this->setProject($project);

        if ($version) {
            $this->setVersion($version);
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title'       => Yii::t('app', 'Title'),
            'type'        => Yii::t('app', 'Type'),
            'subtype'     => Yii::t('app', 'Subtype'),
            'autoScale'   => Yii::t('app', 'Auto rescale'),
            'retinaScale' => Yii::t('app', '2x (Retina) rescale'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
            }, 'whenClient' => 'function (attribute, value) {
                if (!$("#version_type_1").is(":checked")) {
                    return true;
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
            ($this->type == Version::TYPE_TABLET && !array_key_exists($this->{$attribute}, Version::getTabletSubtypeLabels())) ||
            ($this->type == Version::TYPE_MOBILE && !array_key_exists($this->{$attribute}, Version::getMobileSubtypeLabels()))
        ) {
            $this->addError($attribute, Yii::t('app', 'Invalid value.'));
        }
    }

    /**
     * Setter for the `$project` property.
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
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
     * Setter for the `$version` property.
     */
    public function setVersion(Version $version)
    {
        $this->version = $version;
        $this->title   = $version->title;
        $this->type    = $version->type;
        $this->subtype = $version->subtype;

        $this->autoScale   = false;
        $this->retinaScale = false;
        if ($version->scaleFactor == Version::AUTO_SCALE_FACTOR) {
            $this->autoScale   = true;
        } elseif ($version->scaleFactor == Version::RETINA_SCALE_FACTOR) {
            $this->retinaScale = true;
        }
    }

    /**
     * Getter for the `$version` property.
     * @return null|Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Check whether the form is for update or create.
     * @return boolean
     */
    public function isUpdate()
    {
        return $this->version && !$this->version->isNewRecord;
    }

    /**
     * Creates or update a Version model.
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $version = $this->version ? $this->version : (new Version);

            $version->projectId = $this->project->id;
            $version->title     = $this->title;
            $version->type      = $this->type;

            if ($this->type != Version::TYPE_DESKTOP) {
                $version->subtype = $this->subtype;

                $version->scaleFactor = $this->autoScale ? Version::AUTO_SCALE_FACTOR : Version::DEFAULT_SCALE_FACTOR;
            } else {
                $version->subtype = null;

                $version->scaleFactor = $this->retinaScale ? Version::RETINA_SCALE_FACTOR : Version::DEFAULT_SCALE_FACTOR;
            }

            if ($version->save()) {
                $this->setVersion($version);

                return true;
            }
        }

        return false;
    }
}
