<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\validators\HexValidator;
use presentator\api\models\User;
use presentator\api\models\GuidelineAsset;

/**
 * GuidelineAsset create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineAssetForm extends ApiForm
{
    const SCENARIO_FILE_CREATE  = 'scenarioFileCreate';
    const SCENARIO_FILE_UPDATE  = 'scenarioFileUpdate';
    const SCENARIO_COLOR_CREATE = 'scenarioColorCreate';
    const SCENARIO_COLOR_UPDATE = 'scenarioColorUpdate';

    /**
     * @var integer
     */
    public $guidelineSectionId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var integer
     */
    public $order = 0;

    /**
     * @var string
     */
    public $hex;

    /**
     * @var string
     */
    public $title;

    /**
     * @var \yii\web\UploadedFile
     */
    public $file;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var GuidelineAsset
     */
    protected $asset;

    /**
     * @param User                 $user
     * @param null|GuidelineAsset $asset
     * @param array                [$config]
     */
    public function __construct(User $user, GuidelineAsset $asset = null, array $config = [])
    {
        $this->setUser($user);

        if ($asset) {
            $this->setGuidelineAsset($asset);
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['type']  = Yii::t('app', 'Type');
        $labels['order'] = Yii::t('app', 'Order');
        $labels['title'] = Yii::t('app', 'Title');
        $labels['file']  = Yii::t('app', 'File');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['guidelineSectionId', 'type'], 'required'];
        $rules[] = ['guidelineSectionId', 'validateUserGuidelineSectionId'];
        $rules[] = ['type', 'in', 'range' => array_values(GuidelineAsset::TYPE)];
        $rules[] = ['order', 'integer', 'min' => 0];
        $rules[] = ['title', 'string', 'max' => 255];
        $rules[] = ['title', 'required', 'on' => [self::SCENARIO_FILE_UPDATE, self::SCENARIO_COLOR_CREATE, self::SCENARIO_COLOR_UPDATE]]; // not required on creates since it fallbacks to file basename
        $rules[] = ['hex', HexValidator::class];
        $rules[] = ['hex', 'required', 'when' => function ($model) {
            return $model->type == GuidelineAsset::TYPE['COLOR'];
        }];
        $rules[] = ['file', 'required', 'when' => function ($model) {
            return $model->type == GuidelineAsset::TYPE['FILE'];
        }];
        $rules[] = [
            'file',
            'file',
            'skipOnEmpty' => false,
            'maxFiles'    => 1,
            'maxSize'     => (1024 * 1024 * Yii::$app->params['maxGuidelineAssetUploadSize']),
            'mimeTypes'   => Yii::$app->params['allowedGuidelineAssetMimeTypes'],
            'checkExtensionByMimeType' => false,
        ];

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        // file scenarios
        $scenarios[self::SCENARIO_FILE_CREATE] = [
            'guidelineSectionId', 'order', 'type', 'title', 'file',
        ];
        $scenarios[self::SCENARIO_FILE_UPDATE] = [
            'guidelineSectionId', 'order', 'title',
        ];

        // color scenarios
        $scenarios[self::SCENARIO_COLOR_CREATE] = [
            'guidelineSectionId', 'order', 'type', 'title', 'hex',
        ];
        $scenarios[self::SCENARIO_COLOR_UPDATE] = [
            'guidelineSectionId', 'order', 'title', 'hex',
        ];

        return $scenarios;
    }

    /**
     * Checks if the form user is the owner of the specified section ID.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserGuidelineSectionId($attribute, $params)
    {
        $section     = $this->getUser()->findGuidelineSectionById((int) $this->{$attribute});
        $loadedAsset = $this->getGuidelineAsset();

        if (
            // section not found
            !$section ||
            // moving asset to a section from another project is not allowed
            ($loadedAsset && $loadedAsset->guidelineSection->projectId != $section->projectId)
        ) {
            $this->addError($attribute, Yii::t('app', 'Invalid guideline section ID.'));
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
     * @param GuidelineAsset $asset
     */
    public function setGuidelineAsset(GuidelineAsset $asset): void
    {
        $this->asset              = $asset;
        $this->guidelineSectionId = $asset->guidelineSectionId;
        $this->type               = $asset->type;
        $this->order              = $asset->order;
        $this->hex                = $asset->hex;
        $this->title              = $asset->title;
    }

    /**
     * @return null|GuidelineAsset
     */
    public function getGuidelineAsset(): ?GuidelineAsset
    {
        return $this->asset;
    }

    /**
     * Persists model form and returns the created/updated `GuidelineAsset` model.
     *
     * @return null|GuidelineAsset
     */
    public function save(): ?GuidelineAsset
    {
        if ($this->validate()) {
            $asset = $this->getGuidelineAsset() ?: (new GuidelineAsset);

            $asset->guidelineSectionId = $this->guidelineSectionId;
            $asset->order              = $this->order;
            $asset->title              = $this->title;

            if ($this->isCreateScenario()) {
                $asset->type = $this->type;
            }

            if ($this->type == GuidelineAsset::TYPE['FILE']) {
                $asset->hex   = '';

                // set the file name as a title
                if (!$asset->title && $this->file) {
                    $asset->title = mb_substr($this->file->basename, 0, 100);
                }
            } else {
                $asset->hex      = (string) $this->hex;
                $asset->filePath = '';
            }

            if ($asset->save()) {
                if (
                    $this->isCreateScenario() &&
                    $asset->type == GuidelineAsset::TYPE['FILE']
                ) {
                    if (!$this->file || !$asset->saveFile($this->file)) {
                        $asset->delete();

                        return null;
                    }
                }

                $asset->refresh();

                return $asset;
            }
        }

        return null;
    }

    /**
     * Checks whether the form is intended for creating an asset.
     *
     * @return boolean
     */
    protected function isCreateScenario(): bool
    {
        return in_array($this->getScenario(), [
            self::SCENARIO_COLOR_CREATE,
            self::SCENARIO_FILE_CREATE,
        ]);
    }
}
