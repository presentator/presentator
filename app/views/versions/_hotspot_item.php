<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use common\models\Screen;

/**
 * $id           int     Hotspot id.
 * $spot         array   Hotspot properties.
 * $scaleFactor  float   Screen scale factor.
 * $showControls boolean Flag whether to show hotspot controls.
 * $maxX         float   Max allowed hotspot horizontal endpoint.
 * $maxY         float   Max allowed hotspot vertical endpoint.
 */

$maxX         = isset($maxX)         ? $maxX : INF;
$maxY         = isset($maxY)         ? $maxY : INF;
$showControls = isset($showControls) ? $showControls : false;

$originalWidth  = ArrayHelper::getValue($spot, 'width', 0);
$originalHeight = ArrayHelper::getValue($spot, 'height', 0);
$originalTop    = ArrayHelper::getValue($spot, 'top', 0);
$originalLeft   = ArrayHelper::getValue($spot, 'left', 0);

$width  = (float) ($originalWidth / $scaleFactor);
$height = (float) ($originalHeight / $scaleFactor);
$top    = (float) ($originalTop / $scaleFactor);
$left   = (float) ($originalLeft / $scaleFactor);

// normalize dimensions
if ($width > $maxX) {
    $width = $maxX;
}

if ($height > $maxY) {
    $height = $maxY;
}

if ($left + $width > $maxX) {
    $left = $maxX - $width;
}

if ($top + $height > $maxY) {
    $top = $maxY - $height;
}
?>
<div id="<?= Html::encode($id) ?>"
    class="hotspot"
    data-original-width="<?= $originalWidth ?>"
    data-original-height="<?= $originalHeight ?>"
    data-original-left="<?= $originalLeft ?>"
    data-original-top="<?= $originalTop ?>"
    style="width: <?= $width ?>px; height: <?= $height ?>px; top: <?= $top ?>px; left: <?= $left ?>px"
    data-link="<?= Html::encode(ArrayHelper::getValue($spot, 'link', '')); ?>"
    data-transition="<?= Html::encode(ArrayHelper::getValue($spot, 'transition', Screen::TRANSITION_FADE)); ?>"
    data-link-type="<?= Html::encode(ArrayHelper::getValue($spot, 'link_type', Screen::LINK_TYPE_SCREEN)); ?>"
    <?php if ($showControls): ?>
        data-context-menu="#hotspot_context_menu"
    <?php endif  ?>
>
    <?php if ($showControls): ?>
        <span class="remove-handle context-menu-ignore"><i class="ion ion-md-trash"></i></span>
        <span class="resize-handle context-menu-ignore"></span>
    <?php endif ?>
</div>
