<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\models\Hotspot;

/**
 * Hotspot create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotForm extends ApiForm
{
    /**
     * @var integer
     */
    public $screenId;

    /**
     * @var integer
     */
    public $hotspotTemplateId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var float
     */
    public $left;

    /**
     * @var float
     */
    public $top;

    /**
     * @var float
     */
    public $width;

    /**
     * @var float
     */
    public $height;

    /**
     * @var integer
     */
    public $settingScreenId;

    /**
     * @var string
     */
    public $settingTransition;

    /**
     * @var string
     */
    public $settingUrl;

    /**
     * @var string
     */
    public $settingOverlayPosition;

    /**
     * @var boolean
     */
    public $settingFixOverlay;

    /**
     * @var float
     */
    public $settingOffsetTop;

    /**
     * @var float
     */
    public $settingOffsetBottom;

    /**
     * @var float
     */
    public $settingOffsetLeft;

    /**
     * @var float
     */
    public $settingOffsetRight;

    /**
     * @var boolean
     */
    public $settingOutsideClose;

    /**
     * @var float
     */
    public $settingScrollTop;

    /**
     * @var float
     */
    public $settingScrollLeft;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Hotspot
     */
    protected $hotspot;

    /**
     * @param User         $user
     * @param null|Hotspot $hotspot
     * @param array        [$config]
     */
    public function __construct(User $user, Hotspot $hotspot = null, array $config = [])
    {
        $this->setUser($user);

        if ($hotspot) {
            $this->setHotspot($hotspot);
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['type']                   = Yii::t('app', 'Type');
        $labels['left']                   = Yii::t('app', 'Left');
        $labels['top']                    = Yii::t('app', 'Top');
        $labels['width']                  = Yii::t('app', 'Width');
        $labels['height']                 = Yii::t('app', 'Height');
        $labels['settingScreenId']        = Yii::t('app', 'Screen');
        $labels['settingTransition']      = Yii::t('app', 'Transition');
        $labels['settingUrl']             = Yii::t('app', 'Url');
        $labels['settingOverlayPosition'] = Yii::t('app', 'Overlay position');
        $labels['settingFixOverlay']      = Yii::t('app', 'Fix overlay');
        $labels['settingScrollTop']       = Yii::t('app', 'Vertical position');
        $labels['settingScrollLeft']      = Yii::t('app', 'Horizontal position');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['left', 'top', 'width', 'height', 'type'], 'required'];
        $rules[] = ['type', 'in', 'range' => array_values(Hotspot::TYPE)];
        $rules[] = ['screenId', 'validateUserScreenId'];
        $rules[] = ['hotspotTemplateId', 'validateUserHotspotTemplateId'];
        $rules[] = ['screenId', 'required', 'when' => function ($model) {
            return !$model->hotspotTemplateId;
        }];
        $rules[] = ['hotspotTemplateId', 'required', 'when' => function ($model) {
            return !$model->screenId;
        }];
        $rules[] = [['left', 'top'], 'number', 'min' => 0];
        $rules[] = [['width', 'height'], 'number', 'min' => 5];

        // Setting keys
        // @todo more detailed `when` checks
        // ---
        $rules[] = ['settingUrl', 'required', 'when' => function ($model) {
            return $model->type == Hotspot::TYPE['URL'];
        }];
        $rules[] = ['settingUrl', 'url', 'when' => function ($model) {
            return $model->type == Hotspot::TYPE['URL'];
        }];
        $rules[] = ['settingScreenId', 'required', 'when' => function ($model) {
            return (
                $model->type == Hotspot::TYPE['SCREEN'] ||
                $model->type == Hotspot::TYPE['OVERLAY']
            );
        }];
        $rules[] = ['settingScreenId', 'validateUserScreenId', 'when' => function ($model) {
            return (
                $model->type == Hotspot::TYPE['SCREEN'] ||
                $model->type == Hotspot::TYPE['OVERLAY']
            );
        }];
        $rules[] = ['settingOverlayPosition', 'required', 'when' => function ($model) {
            return $model->type == Hotspot::TYPE['OVERLAY'];
        }];
        $rules[] = ['settingOverlayPosition', 'in', 'range' => array_values(Hotspot::OVERLAY_POSITION)];
        $rules[] = ['settingTransition', 'in', 'range' => array_values(Hotspot::TRANSITION)];
        $rules[] = ['settingTransition', 'default', 'value' => Hotspot::TRANSITION['NONE']];
        $rules[] = [['settingOutsideClose', 'settingFixOverlay'], 'boolean'];
        $rules[] = [[
            'settingOffsetTop',
            'settingOffsetBottom',
            'settingOffsetLeft',
            'settingOffsetRight',
        ], 'number'];

        $rules[] = [['settingScrollTop', 'settingScrollLeft'], 'number', 'min' => 0];
        $rules[] = [['settingScrollTop', 'settingScrollLeft'], 'default', 'value' => 0];
        $rules[] = [['settingScrollTop', 'settingScrollLeft'], 'required', 'when' => function ($model) {
            return $model->type == Hotspot::TYPE['SCROLL'];
        }];

        return $rules;
    }

    /**
     * Checks if the form user is the owner of the specified screen ID.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserScreenId($attribute, $params)
    {
        $user = $this->getUser();

        if (!$user || !$user->findScreenById($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid screen ID.'));
        }
    }

    /**
     * Checks if the form user is the owner of the specified hotspot template ID.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserHotspotTemplateId($attribute, $params)
    {
        $user = $this->getUser();

        if (!$user || !$user->findHotspotTemplateById($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid hotspot template ID.'));
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
     * @param Hotspot $hotspot
     */
    public function setHotspot(Hotspot $hotspot): void
    {
        $this->hotspot           = $hotspot;
        $this->screenId          = $hotspot->screenId;
        $this->hotspotTemplateId = $hotspot->hotspotTemplateId;
        $this->type              = $hotspot->type;
        $this->left              = $hotspot->left;
        $this->top               = $hotspot->top;
        $this->width             = $hotspot->width;
        $this->height            = $hotspot->height;

        $settings                     = $hotspot->getDecodedSettings();
        $this->settingScreenId        = $settings[Hotspot::SETTING['SCREEN']]           ?? null;
        $this->settingOverlayPosition = $settings[Hotspot::SETTING['OVERLAY_POSITION']] ?? '';
        $this->settingFixOverlay      = $settings[Hotspot::SETTING['FIX_OVERLAY']]      ?? false;
        $this->settingTransition      = $settings[Hotspot::SETTING['TRANSITION']]       ?? '';
        $this->settingUrl             = $settings[Hotspot::SETTING['URL']]              ?? '';
        $this->settingOffsetTop       = $settings[Hotspot::SETTING['OFFSET_TOP']]       ?? 0;
        $this->settingOffsetBottom    = $settings[Hotspot::SETTING['OFFSET_BOTTOM']]    ?? 0;
        $this->settingOffsetLeft      = $settings[Hotspot::SETTING['OFFSET_LEFT']]      ?? 0;
        $this->settingOffsetRight     = $settings[Hotspot::SETTING['OFFSET_RIGHT']]     ?? 0;
        $this->settingScrollTop       = $settings[Hotspot::SETTING['SCROLL_TOP']]       ?? 0;
        $this->settingScrollLeft      = $settings[Hotspot::SETTING['SCROLL_LEFT']]      ?? 0;
        $this->settingOutsideClose    = $settings[Hotspot::SETTING['OUTSIDE_CLOSE']]    ?? false;
    }

    /**
     * @return null|Hotspot
     */
    public function getHotspot(): ?Hotspot
    {
        return $this->hotspot;
    }

    /**
     * Persists model form and returns the created/updated `Hotspot` model.
     *
     * @return null|Hotspot
     */
    public function save(): ?Hotspot
    {
        if ($this->validate()) {
            $hotspot = $this->getHotspot() ?: (new Hotspot);

            if ($this->screenId) {
                $hotspot->screenId          = $this->screenId;
                $hotspot->hotspotTemplateId = null;
            } else {
                $hotspot->screenId          = null;
                $hotspot->hotspotTemplateId = $this->hotspotTemplateId;
            }

            $hotspot->type   = $this->type;
            $hotspot->left   = $this->left;
            $hotspot->top    = $this->top;
            $hotspot->width  = $this->width;
            $hotspot->height = $this->height;

            $hotspot->setSettings($this->exportSettingsData());

            if ($hotspot->save()) {
                $hotspot->refresh();

                return $hotspot;
            }
        }

        return null;
    }

    /**
     * Returns an array list with the valid settings data based on the model type.
     *
     * @return array
     */
    protected function exportSettingsData()
    {
        if ($this->type == Hotspot::TYPE['SCREEN']) {
            return [
                Hotspot::SETTING['SCREEN']     => $this->settingScreenId,
                Hotspot::SETTING['TRANSITION'] => $this->settingTransition,
            ];
        }

        if ($this->type == Hotspot::TYPE['OVERLAY']) {
            return [
                Hotspot::SETTING['SCREEN']           => $this->settingScreenId,
                Hotspot::SETTING['TRANSITION']       => $this->settingTransition,
                Hotspot::SETTING['OVERLAY_POSITION'] => $this->settingOverlayPosition,
                Hotspot::SETTING['FIX_OVERLAY']      => $this->settingFixOverlay,
                Hotspot::SETTING['OFFSET_TOP']       => $this->settingOffsetTop,
                Hotspot::SETTING['OFFSET_BOTTOM']    => $this->settingOffsetBottom,
                Hotspot::SETTING['OFFSET_LEFT']      => $this->settingOffsetLeft,
                Hotspot::SETTING['OFFSET_RIGHT']     => $this->settingOffsetRight,
                Hotspot::SETTING['OUTSIDE_CLOSE']    => $this->settingOutsideClose ? true : false,
            ];
        }

        if (
            $this->type == Hotspot::TYPE['NEXT'] ||
            $this->type == Hotspot::TYPE['PREV'] ||
            $this->type == Hotspot::TYPE['BACK']
        ) {
            return [
                Hotspot::SETTING['TRANSITION'] => $this->settingTransition,
            ];
        }

        if ($this->type == Hotspot::TYPE['URL']) {
            return [
                Hotspot::SETTING['URL'] => $this->settingUrl,
            ];
        }

        if ($this->type == Hotspot::TYPE['SCROLL']) {
            return [
                Hotspot::SETTING['SCROLL_TOP']  => $this->settingScrollTop,
                Hotspot::SETTING['SCROLL_LEFT'] => $this->settingScrollLeft,
            ];
        }

        return [];
    }
}
