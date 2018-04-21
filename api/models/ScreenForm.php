<?php
namespace api\models;

use Yii;
use yii\base\Model;
use yii\helpers\Inflector;
use common\models\User;
use common\models\Screen;
use common\components\validators\HotspotsValidator;
use common\components\helpers\CFileHelper;
use common\components\web\CUploadedFile;

/**
 * API Screen form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenForm extends Model
{
    const SCENARIO_CREATE = 'scenarioCreate';
    const SCENARIO_UPDATE = 'scenarioUpdate';

    /**
     * @var integer
     */
    public $versionId;

    /**
     * @var integer
     */
    public $order;

    /**
     * @var string
     */
    public $title;

    /**
     * @var integer
     */
    public $alignment;

    /**
     * @var string
     */
    public $background;

    /**
     * @var null|string|array
     */
    public $hotspots;

    /**
     * @var UploadedFile
     */
    public $image;

    /**
     * @var User
     */
    private $user;

    /**
     * @param User  $user
     * @param array $config
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
            [['versionId', 'title', 'alignment'], 'required'],
            [['title', 'background'], 'trim'],
            ['title', 'string', 'max' => 255],
            ['background', 'string', 'min' => 7, 'max' => 7],
            ['background', 'validateHex'],
            ['hotspots', HotspotsValidator::className()],
            ['versionId', 'validateUserVersionId'],
            ['alignment', 'in', 'range' => array_keys(Screen::getAlignmentLabels())],
            ['image', 'required', 'on' => self::SCENARIO_CREATE],
            ['image', 'image',
                'skipOnEmpty' => false,
                'extensions'  => 'png, jpg, jpeg',
                'maxFiles'    => 1,
                'maxSize'     => (1024 * 1024 * Yii::$app->params['maxUploadSize']),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CREATE] = [
            'versionId', 'title', 'background',
            'alignment', 'order', 'hotspots', 'image',
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'versionId', 'title', 'background',
            'alignment', 'order', 'hotspots',
        ];

        return $scenarios;
    }

    /**
     * Validates hex color code.
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateHex($attribute, $params)
    {
        $color = $this->{$attribute};
        $code = strlen($color) > 3 ? substr($color, 1) : null;

        if (!ctype_xdigit($code) || $color[0] !== '#') {
            $this->addError($attribute, Yii::t('app', 'Invalid HEX color code.'));
        }
    }

    /**
     * Checkes if the form user own a certain Version model.
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserVersionId($attribute, $params)
    {
        if (!$this->user || !$this->user->findVersionById($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid version ID.'));
        }
    }

    /**
     * Creates/Updates a Screen model.
     * @param  Screen|null $screen
     * @return Screen|null The created/updated screen on success, otherwise - null.
     */
    public function save(Screen $screen = null)
    {
        if ($this->validate()) {
            $version = $this->user->findVersionById($this->versionId);

            if (!$screen) {
                // create
                $screen = new Screen;
            }

            $screen->title      = $this->title;
            $screen->hotspots   = $this->hotspots;
            $screen->versionId  = (int) $this->versionId;
            $screen->alignment  = (int) $this->alignment;
            $screen->background = $this->background ? $this->background : null;

            if ($this->order) {
                $screen->order = (int) $this->order;
            }

            // Image upload
            if ($this->image && ($this->image instanceof CUploadedFile)) {
                $uploadDirPath = $version->project->getUploadDir();
                $uploadDirUrl  = CFileHelper::getUrlFromPath($uploadDirPath, false);
                $filename      = Inflector::slug($this->image->basename) . '_' . time() . '_' . rand(0, 100) . '.' . $this->image->extension;

                // ensure the directory exist
                CFileHelper::createDirectory($uploadDirPath);

                // move to the screen upload dir
                $this->image->saveAs(rtrim($uploadDirPath, '/') . '/' . $filename);

                // store image url in db for reference
                $screen->imageUrl  = rtrim($uploadDirUrl, '/') . '/' . $filename;
            }

            if ($screen->save()) {
                return $screen;
            }
        }

        return null;
    }
}
