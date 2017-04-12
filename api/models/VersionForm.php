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
    /**
     * @var integer
     */
    public $projectId;

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
            ['projectId', 'required'],
            ['projectId', 'validateUserProjectId'],
        ];
    }

    /**
     * Checkes if the form user has a project ID.
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
     * Creates a Version model.
     * @return Version|null The created version on success, otherwise - null.
     */
    public function save()
    {
        if ($this->validate()) {
            $version            = new Version;
            $version->projectId = (int) $this->projectId;

            if ($version->save()) {
                return $version;
            }
        }

        return null;
    }
}
