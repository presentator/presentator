<?php
namespace app\models;

use Yii;
use yii\base\Model;
use common\models\Screen;

/**
 * Form model that takes care for updating general Screen settings.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenSettingsForm extends Model
{
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
     * @var Screen
     */
    private $screen;

    /**
     * Model constructor.
     * @param Screen $screen
     * @param array  $config
     */
    public function __construct(Screen $screen, $config = [])
    {
        $this->loadScreen($screen);

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title'      => Yii::t('app', 'Title'),
            'alignment'  => Yii::t('app', 'Alignment'),
            'background' => Yii::t('app', 'Background'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'alignment'], 'required'],
            [['title', 'background'], 'trim'],
            ['title', 'string', 'max' => 255],
            ['background', 'string', 'min' => 7, 'max' => 7],
            ['background', 'validateHex'],
            ['alignment', 'in', 'range' => array_keys(Screen::getAlignmentLabels())],
        ];
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
     * Load Screen model settings.
     * @param Screen $screen
     */
    public function loadScreen(Screen $screen)
    {
        $this->screen     = $screen;
        $this->title      = $screen->title;
        $this->background = $screen->background;
        $this->alignment  = $screen->alignment;
    }

    /**
     * Updates the loaded Screen model with the form settings.
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $this->screen->title      = $this->title;
            $this->screen->alignment  = $this->alignment;
            $this->screen->background = $this->background ? $this->background : null;

            return $this->screen->save();
        }

        return false;
    }
}
