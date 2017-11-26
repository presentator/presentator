<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * $id           int     Hotspot id.
 * $spot         array   Hotspot properties.
 * $scaleFactor  float   Screen scale factor.
 * $maxWidth     float   Max allowed hotspot width.
 * $maxHeight    float   Max allowed hotspot height.
 * $showControls boolean Flag whether to show hotspot controls.
 */

if (!isset($showControls)) {
    $showControls = false;
}

$width  = (float) (ArrayHelper::getValue($spot, 'width', 0) / $scaleFactor);
$height = (float) (ArrayHelper::getValue($spot, 'height', 0) / $scaleFactor);
$top    = (float) (ArrayHelper::getValue($spot, 'top', 0) / $scaleFactor);
$left   = (float) (ArrayHelper::getValue($spot, 'left', 0) / $scaleFactor);

// normalize dimensions
if ($width > $maxWidth) {
    $width = $maxWidth;
}

if ($height > $maxHeight) {
    $height = $maxHeight;
}

if ($left > $maxWidth) {
    $left = $maxWidth - $width;
}

if ($top > $maxHeight) {
    $top = $maxHeight - $height;
}
?>
<div id="<?= Html::encode($id) ?>"
    class="hotspot"
    style="width: <?= $width ?>px; height: <?= $height ?>px; top: <?= $top ?>px; left: <?= $left ?>px"
    data-link="<?= Html::encode(ArrayHelper::getValue($spot, 'link', '')); ?>"
    <?php if ($showControls): ?>
        data-context-menu="#hotspot_context_menu"
    <?php endif  ?>
>
    <?php if ($showControls): ?>
        <span class="remove-handle context-menu-ignore"><i class="ion ion-trash-a"></i></span>
        <span class="resize-handle context-menu-ignore"></span>
    <?php endif ?>
</div>
