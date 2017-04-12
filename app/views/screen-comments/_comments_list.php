<?php
use yii\helpers\Html;

/**
 * @var $comments \common\models\ScreenComment[]
 */

$showDelete = isset($showDelete) ? $showDelete : true;
?>
<?php foreach ($comments as $comment): ?>
    <div class="comment <?= !$comment->replyTo ? 'primary-comment' : '' ?>" data-comment-id="<?= $comment->id ?>">
        <div class="heading">
            <figure class="avatar" data-txt="<?= substr($comment->from, 0, 1) ?>">
                <?php if ($comment->fromUser && $comment->fromUser->getAvatarUrl(true)): ?>
                    <img src="<?= $comment->fromUser->getAvatarUrl(true) ?>" alt="User avatar">
                <?php endif ?>
            </figure>
            <a href="mailto: <?= Html::encode($comment->from) ?>" class="author"><?= Html::encode($comment->from) ?></a>
            <span class="date"><?= date('d.m.Y H:i', $comment->createdAt) ?></span>

            <?php if ($showDelete): ?>
                <span class="delete-handle comment-delete">
                    <i class="ion ion-trash-a"></i>
                </span>
            <?php endif ?>
        </div>
        <div class="content">
            <p><?= Html::encode($comment->message) ?></p>
        </div>
    </div>
<?php endforeach ?>
