<?php
use yii\helpers\Html;
use common\models\Screen;

/**
 * @var $screens \common\models\Screen[]
 */
?>
<div id="hotspot_popover" class="popover hotspot-popover">
    <div class="tabs">
        <div class="tabs-header">
            <div class="tab-item active" data-target="#hotspot_tab_screens"><?= Yii::t('app', 'Screens') ?></div>
            <div class="tab-item" data-target="#hotspot_tab_url"><?= Yii::t('app', 'Custom URL') ?></div>
        </div>
        <div class="tabs-content">
            <div id="hotspot_tab_screens" class="tab-item tab-screens active">
                <div class="form-group form-group-sm hotspot-transition-select-wrapper">
                    <select id="hotspot_popover_transition_select">
                        <option value="" disabled><?= Yii::t('app', 'Choose screen transition...') ?></option>
                        <?php foreach (Screen::getTransitionLabels() as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group-sm hotspot-link-type-select-wrapper">
                    <label for="hotspot_popover_link_type_select">
                      <?= Yii::t('app', 'Link to'); ?>
                    </label>
                    <select id="hotspot_popover_link_type_select">
                        <option value="" disabled><?= Yii::t('app', 'Choose type of link...') ?></option>
                        <?php foreach (Screen::getLinkTypeLabels() as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="hotspot-screens-list">
                    <?php foreach ($screens as $screen): ?>
                        <div class="box popover-thumb hotspot-popover-screen"
                            data-screen-id="<?= $screen->id ?>"
                            data-cursor-tooltip="<?= Html::encode($screen->title) ?>"
                            data-cursor-tooltip-class="primary-tooltip"
                        >
                            <div class="content">
                                <figure class="featured">
                                    <img class="img lazy-load"
                                        alt="<?= Html::encode($screen->title) ?>"
                                        data-src="<?= $screen->getThumbUrl('small') ?>"
                                        data-priority="low"
                                    >
                                </figure>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
            <div id="hotspot_tab_url" class="tab-item tab-url">
                <div class="form-group form-group-sm">
                    <input id="hotspot_popover_url_input" type="text" placeholder="eg. http://google.bg">
                </div>
                <div class="block text-center m-b-20">
                    <button type="button" id="hotspot_popover_url_btn" class="btn btn-success btn-cons btn-sm">
                        <?= Yii::t('app', 'Save') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
