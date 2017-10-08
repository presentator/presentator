<?php
use yii\helpers\Html;
use common\models\ProjectPreview;

/**
 * @var $mention array
 * @var $comment \common\models\ScreenComment
 */

$isGuest    = empty($mention['userId']) ? true : false;
$previewUrl = $comment->screen->project->getPreviewUrl(ProjectPreview::TYPE_VIEW_AND_COMMENT, ['m' => 'comments']);
?>

<p><?= Yii::t('mail', 'Hello') ?><?= !empty($mention['firstName']) ? (' ' . Html::encode($mention['firstName'])) : '' ?>,</p>

<p><?= Yii::t('mail', 'You have been mentioned in a comment for project "{projectTitle}" .', ['projectTitle' => Html::encode($comment->screen->project->title)]) ?><p>

<p class="emphasis">
    <b><?= Yii::t('mail', 'Screen') ?></b>: <?= Html::encode($comment->screen->title) ?><br/>
    <b><?= Yii::t('mail', 'From') ?></b>: <a href="mailto:<?= Html::encode($comment->from)?>"><?= Html::encode($comment->from)?></a><br/>
    <b><?= Yii::t('mail', 'Message') ?></b>:<br/>
    <em><?= Html::encode($comment->message) ?></em>
</p>

<p style="text-align: center;">
    <a href="<?= $previewUrl ?>" class="btn"><?= Yii::t('mail', 'View project') ?></a><br/>
    <a href="<?= $previewUrl ?>" class="hint"><?= $previewUrl ?></a>
</p>

<p>
    <?= Yii::t('mail', "If you need any further help don't hesitate to contact us at {supportEmail}.", [
        'supportEmail' => Html::mailto(Yii::$app->params['supportEmail']),
    ]) ?>
</p>

<p>
    <?= Yii::t('mail', 'Best Regards') ?>,<br/>
    <?= Yii::t('mail', 'Presentator Team') ?>
</p>

<?php if (!$isGuest): ?>
    <p class="hint">
        <?= Yii::t('mail', "P.S. If you don't want to receive any comments notifications, you could update your preferences at your Presentator account settings page.") ?>
    </p>
<?php endif; ?>
