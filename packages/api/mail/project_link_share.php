<?php
use yii\helpers\Html;

/**
 * @var $projectLink common\models\ProjectLink
 * @var $message     string
 */

$previewUrl = str_replace('{slug}', $projectLink->slug, Yii::$app->params['projectLinkUrl']);
?>

<p><?= Yii::t('mail', 'Hello') ?>,</p>

<p>
    <?= Yii::t('mail', 'You are invited to review the design for project "{projectTitle}".', [
        'projectTitle' => Html::encode($projectLink->project->title),
    ]) ?>
</p>

<?php if (!empty($message)): ?>
    <p class="emphasis"><?= nl2br(strip_tags($message)) ?></p>
<?php endif; ?>

<p style="text-align: center;">
    <a href="<?= Html::encode($previewUrl) ?>" class="btn"><?= Yii::t('mail', 'View design') ?></a><br/>
    <a href="<?= Html::encode($previewUrl) ?>" class="hint hidden"><?= Html::encode($previewUrl) ?></a>
</p>
