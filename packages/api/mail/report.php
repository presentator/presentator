<?php
use yii\helpers\Html;

/**
 * @var $projectLink presentator\api\models\ProjectLink
 * @var $details     string
 */

$previewUrl = str_replace('{slug}', $projectLink->slug, Yii::$app->params['projectLinkUrl']);
?>
<p>Hello,</p>

<p>The following report violation was submitted:</p>

<p class="emphasis">
    <strong>Project:</strong> <?= Html::encode($projectLink->project->title) ?> <br/>

    <strong>Preview:</strong> <a href="<?= Html::encode($previewUrl) ?>"><?= Html::encode($previewUrl) ?></a> <br/>

    <?php if (!empty($details)): ?>
        <strong>Details:</strong> <br/>
        <?= nl2br(strip_tags($details)) ?>
    <?php endif ?>
</p>
