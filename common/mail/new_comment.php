<?php
use yii\helpers\Html;

/**
 * @var $user    \common\models\User
 * @var $comment \common\models\ScreenComment
 */

$replyUrl = Yii::$app->mainUrlManager->createUrl([
    'projects/view',
    'id'             => $comment->screen->project->id,
    'screen'         => $comment->screen->id,
    'comment_target' => ($comment->replyTo ? $comment->replyTo : $comment->id),
    'reply_to'       => $comment->id,
], true);
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $user->getFullName() ? (' ' . Html::encode($user->getFullName())) : '' ?>,</p>
<p><?= Yii::t('mail', 'A new comment was left for project "{projectTitle}".', ['projectTitle' => Html::encode($comment->screen->project->title)]) ?><p>

<p class="emphasis">
    <b><?= Yii::t('mail', 'Screen') ?></b>: <?= Html::encode($comment->screen->title) ?><br/>
    <b><?= Yii::t('mail', 'From') ?></b>: <a href="mailto:<?= Html::encode($comment->from)?>"><?= Html::encode($comment->from)?></a><br/>
    <b><?= Yii::t('mail', 'Message') ?></b>:<br/>
    <em><?= Html::encode($comment->message) ?></em>
</p>

<p><?= Yii::t('mail', 'Click on the button below for detail view and other options:') ?></p>
<p style="text-align: center;">
    <a href="<?= $replyUrl ?>" class="btn"><?= Yii::t('mail', 'Detail view') ?></a><br/>
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

<p class="hint">
    <?= Yii::t('mail', "P.S. If you don't want to receive any comments notifications, you could update your preferences at your Presentator account settings page.") ?>
</p>
