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
     * Project model to assign the version.
     * @var Project
     */
    private $project;

    /**
     * Version model to update
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
            'title' => Yii::t('app', 'Title'),
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
        ];
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

            if ($version->save()) {
                $this->setVersion($version);

                return true;
            }
        }

        return false;
    }
}
