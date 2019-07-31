<?php
namespace presentator\api\models;

/**
 * Hotspot AR model
 *
 * @property integer $id
 * @property integer $screenId
 * @property integer $hotspotTemplateId
 * @property string  $type
 * @property float   $left
 * @property float   $top
 * @property float   $width
 * @property float   $height
 * @property string  $settings
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Hotspot extends ActiveRecord
{
    const TYPE = [
        'URL'     => 'url',
        'SCREEN'  => 'screen',
        'OVERLAY' => 'overlay',
        'PREV'    => 'prev',
        'NEXT'    => 'next',
        'BACK'    => 'back',
        'SCROLL'  => 'scroll',
    ];

    const TRANSITION = [
        'NONE'         => 'none',
        'FADE'         => 'fade',
        'SLIDE_LEFT'   => 'slide-left',
        'SLIDE_RIGHT'  => 'slide-right',
        'SLIDE_TOP'    => 'slide-top',
        'SLIDE_BOTTOM' => 'slide-bottom',
    ];

    const OVERLAY_POSITION = [
        'CENTERED'      => 'centered',
        'TOP_LEFT'      => 'top-left',
        'TOP_CENTER'    => 'top-center',
        'TOP_RIGHT'     => 'top-right',
        'BOTTOM_LEFT'   => 'bottom-left',
        'BOTTOM_CENTER' => 'bottom-center',
        'BOTTOM_RIGHT'  => 'bottom-right',
    ];

    const SETTING = [
        'SCREEN'           => 'screenId',
        'TRANSITION'       => 'transition',
        'OVERLAY_POSITION' => 'overlayPosition',
        'URL'              => 'url',
        'OUTSIDE_CLOSE'    => 'outsideClose',
        'OFFSET_TOP'       => 'offsetTop',
        'OFFSET_BOTTOM'    => 'offsetBottom',
        'OFFSET_LEFT'      => 'offsetLeft',
        'OFFSET_RIGHT'     => 'offsetRight',
        'SCROLL_TOP'       => 'scrollTop',
        'SCROLL_LEFT'      => 'scrollLeft',
    ];

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreen()
    {
        return $this->hasOne(Screen::class, ['id' => 'screenId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHotspotTemplate()
    {
        return $this->hasOne(HotspotTemplate::class, ['id' => 'hotspotTemplateId']);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (is_array($this->settings)) {
                $this->setSettings($this->settings);
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();

        $fields['settings'] = function ($model, $field) {
            return $model->getDecodedSettings();
        };

        return $fields;
    }

    /**
     * Encodes and sets model settings.
     *
     * @param array $settings
     */
    public function setSettings(array $settings)
    {
        $this->settings = json_encode($settings);
    }

    /**
     * Returns decoded model settings array list.
     *
     * @return array
     */
    public function getDecodedSettings(): array
    {
        return (array) @json_decode($this->settings, true);
    }

    /**
     * Returns single setting value by its key.
     *
     * @param  string $key
     * @param  mixed  [$defaultValue] The default value that will be returned if the setting is not set.
     * @return mixed
     */
    public function getSetting(string $key, $defaultValue = null)
    {
        $settings = $this->getDecodedSettings();

        return isset($settings[$key]) ? $settings[$key] : $defaultValue;
    }
}
