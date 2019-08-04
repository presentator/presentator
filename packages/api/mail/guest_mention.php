<?php
use yii\helpers\Html;

/**
 * @var $comment     \presentator\api\models\ScreenComment
 * @var $projectLink null|\presentator\api\models\ProjectLink
 */

// set defaults
$projectLink = $projectLink ?? null;
$viewUrl     = '';

if (!empty($projectLink)) {
    // replace tokens in preview's url
    $viewUrl = Yii::$app->params['projectLinkCommentViewUrl'];
    $tokens = [
        'commentId'   => $comment->replyTo ?: $comment->id,
        'screenId'    => $comment->screen->id,
        'prototypeId' => $comment->screen->prototype->id,
        'slug'        => $projectLink->slug,
    ];
    foreach ($tokens as $key => $value) {
        $viewUrl = str_replace('{' . $key . '}', $value, $viewUrl);
    }
}
?>
<p><?= Yii::t('mail', 'Hello') ?>,</p>

<p>
    <?= Yii::t('mail', 'You have been mentioned in a comment for screen "{screenTitle}" in project "{projectTitle}":', [
        'screenTitle'  => ('<strong>' . Html::encode($comment->screen->title) . '</strong>'),
        'projectTitle' => ('<strong>' . Html::encode($comment->screen->prototype->project->title) . '</strong>'),
    ]) ?>
</p>

<p class="emphasis">
    <span class="hint"><?= Html::encode($comment->from) ?>, <?= Html::encode($comment->createdAt) ?> UTC</span><br/>

    <?= nl2br(strip_tags($comment->message)) ?>
</p>

<?php if (!empty($viewUrl)): ?>
    <p><?= Yii::t('mail', 'Click on the button below for detail view and other options:') ?></p>

    <p style="text-align: center;">
        <a href="<?= Html::encode($viewUrl) ?>" class="btn"><?= Yii::t('mail', 'Detail view') ?></a><br/>
        <a href="<?= Html::encode($viewUrl) ?>" class="hint hidden"><?= Html::encode($viewUrl) ?></a>
    </p>
<?php endif; ?>
