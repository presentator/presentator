<?php
use yii\helpers\Html;

/**
 * @var $preview common\models\ProjectPreview
 * @var $message string
 */

$previewUrl = Yii::$app->mainUrlManager->createUrl(['preview/view', 'slug' => $preview->slug], true);
?>

<p><?= Yii::t('mail', 'Hello') ?>,</p>
<p>
    <?= Yii::t('mail', 'You are invited to review the design for "{projectTitle}".', ['projectTitle' => Html::encode($preview->project->title)]) ?>
</p>

<?php if (!empty($message)): ?>
    <p class="emphasis">
        <em><?= Html::encode($message) ?></em>
    </p>
<?php endif; ?>

<p style="text-align: center;">
    <a href="<?= Html::encode($previewUrl) ?>" class="btn"><?= Yii::t('mail', 'View project') ?></a><br/>
    <a href="<?= Html::encode($previewUrl) ?>" class="hint"><?= Html::encode($previewUrl) ?></a>
</p>
<p>
    <?= Yii::t('mail', "If you need any further help don't hesitate to contact us at {supportEmail}.", [
        'supportEmail' => Html::mailto(Yii::$app->params['supportEmail']),
    ]) ?>
</p>
<p>
    <?= Yii::t('mail', 'Best Regards') ?>, <br/>
    <?= Yii::t('mail', 'Presentator Team') ?>
</p>
