<?php
use yii\helpers\Html;

/**
 * @var $user    \presentator\api\models\User
 * @var $comment \presentator\api\models\ScreenComment
 */

$name = $user->getFullName();

// replace tokens in comment's view url
$viewUrl = Yii::$app->params['commentViewUrl'];
$tokens = [
    'commentId'   => $comment->replyTo ?: $comment->id,
    'screenId'    => $comment->screen->id,
    'prototypeId' => $comment->screen->prototype->id,
    'projectId'   => $comment->screen->prototype->project->id,
];
foreach ($tokens as $key => $value) {
    $viewUrl = str_replace('{' . $key . '}', $value, $viewUrl);
}
?>
<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>

<p>
    <?= Yii::t('mail', 'You have an unread comment for screen "{screenTitle}" in project "{projectTitle}":', [
        'screenTitle'  => ('<strong>' . Html::encode($comment->screen->title) . '</strong>'),
        'projectTitle' => ('<strong>' . Html::encode($comment->screen->prototype->project->title) . '</strong>'),
    ]) ?>
</p>

<p class="emphasis">
    <span class="hint"><?= Html::encode($comment->from) ?>, <?= Html::encode($comment->createdAt) ?> UTC</span><br/>

    <?= nl2br(strip_tags($comment->message)) ?>
</p>

<p><?= Yii::t('mail', 'Click on the button below for detail view and other options:') ?></p>

<p style="text-align: center;">
    <a href="<?= Html::encode($viewUrl) ?>" class="btn"><?= Yii::t('mail', 'Detail view') ?></a><br/>
    <a href="<?= Html::encode($viewUrl) ?>" class="hint hidden"><?= Html::encode($viewUrl) ?></a>
</p>
