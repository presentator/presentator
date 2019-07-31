<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\validators\HexValidator;
use presentator\api\models\User;
use presentator\api\models\Screen;

/**
 * Screen create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenForm extends ApiForm
{
    const SCENARIO_CREATE  = 'scenarioCreate';
    const SCENARIO_UPDATE  = 'scenarioUpdate';

    /**
     * @var integer
     */
    public $prototypeId;

    /**
     * @var integer
     */
    public $order = 0;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $alignment;

    /**
     * @var string
     */
    public $background;

    /**
     * @var float
     */
    public $fixedHeader;

    /**
     * @var float
     */
    public $fixedFooter;

    /**
     * @var \yii\web\UploadedFile
     */
    public $file;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Screen
     */
    protected $screen;

    /**
     * @param User        $user
     * @param null|Screen $screen
     * @param array       [$config]
     */
    public function __construct(User $user, Screen $screen = null, array $config = [])
    {
        $this->setUser($user);

        if ($screen) {
            $this->setScreen($screen);
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
        $labels['alignment']   = Yii::t('app', 'Alignment');
        $labels['background']  = Yii::t('app', 'Background');
        $labels['fixedHeader'] = Yii::t('app', 'Fixed header');
        $labels['fixedFooter'] = Yii::t('app', 'Fixed footer');
        $labels['file']        = Yii::t('app', 'File');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['prototypeId', 'required'];
        $rules[] = ['prototypeId', 'validateUserPrototypeId'];
        $rules[] = ['alignment', 'in', 'range' => array_values(Screen::ALIGNMENT)];
        $rules[] = [['fixedHeader', 'fixedFooter'], 'number', 'min' => 0];
        $rules[] = ['background', HexValidator::class];
        $rules[] = ['order', 'integer', 'min' => 0];
        $rules[] = ['title', 'string', 'max' => 255];
        $rules[] = ['title', 'required', 'on' => self::SCENARIO_UPDATE]; // not required on creates since it fallbacks to file basename
        $rules[] = ['file', 'required', 'on' => self::SCENARIO_CREATE];
        $rules[] = [
            'file',
            'file',
            'skipOnEmpty' => false,
            'maxFiles'    => 1,
            'maxSize'     => (1024 * 1024 * Yii::$app->params['maxScreenUploadSize']),
            'mimeTypes'   => Yii::$app->params['allowedScreenMimeTypes'],
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

        $scenarios[self::SCENARIO_CREATE] = [
            'prototypeId', 'order', 'title', 'alignment',
            'background', 'fixedHeader', 'fixedFooter', 'file',
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'prototypeId', 'order', 'title', 'alignment',
            'background', 'fixedHeader', 'fixedFooter',
        ];

        return $scenarios;
    }

    /**
     * Checks if the form user is the owner of the specified prototype ID.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserPrototypeId($attribute, $params)
    {
        $prototype    = $this->getUser()->findPrototypeById((int) $this->{$attribute});
        $loadedScreen = $this->getScreen();

        if (
            // prototype not found
            !$prototype ||
            // moving screen to a prototype from another project is not allowed
            ($loadedScreen && $loadedScreen->prototype->projectId != $prototype->projectId)
        ) {
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
     * @param Screen $screen
     */
    public function setScreen(Screen $screen): void
    {
        $this->screen      = $screen;
        $this->prototypeId = $screen->prototypeId;
        $this->order       = $screen->order;
        $this->title       = $screen->title;
        $this->alignment   = $screen->alignment;
        $this->background  = $screen->background;
        $this->fixedHeader = $screen->fixedHeader;
        $this->fixedFooter = $screen->fixedFooter;
    }

    /**
     * @return null|Screen
     */
    public function getScreen(): ?Screen
    {
        return $this->screen;
    }

    /**
     * Persists model form and returns the created/updated `Screen` model.
     *
     * @return null|Screen
     */
    public function save(): ?Screen
    {
        if ($this->validate()) {
            $screen = $this->getScreen() ?: (new Screen);

            $screen->prototypeId = $this->prototypeId;

            $lastSibling = $screen->findLastSibling();

            $screen->order       = $this->order;
            $screen->title       = (string) $this->title;
            $screen->alignment   = $this->alignment ?: ($lastSibling ? $lastSibling->alignment : Screen::ALIGNMENT['CENTER']);
            $screen->background  = $this->background ?: ($lastSibling ? $lastSibling->background : '#ffffff');
            $screen->fixedHeader = $this->fixedHeader !== null ? (float) $this->fixedHeader : ($lastSibling ? $lastSibling->fixedHeader : 0.0);
            $screen->fixedFooter = $this->fixedFooter !== null ? (float) $this->fixedFooter : ($lastSibling ? $lastSibling->fixedFooter : 0.0);

            if (!$screen->title && $this->file) {
                $screen->title = $this->file->basename;
            }

            if ($screen->save()) {
                if ($this->getScenario() === self::SCENARIO_CREATE) {
                    if (!$this->file || !$screen->saveFile($this->file)) {
                        $screen->delete();

                        return null;
                    }
                }

                $screen->refresh();

                return $screen;
            }
        }

        return null;
    }
}
