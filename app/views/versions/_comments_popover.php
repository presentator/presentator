<?php
use yii\helpers\Html;
?>

<div id="comment_popover" class="popover comment-popover">
    <div id="comments_list" class="comments-wrapper"></div>

    <form id="comment_form" class="reply-form">
        <div class="block input-block">
            <?php if (Yii::$app->user->isGuest): ?>
                <input type="email" id="comment_form_from_input" class="reply-input from-input" placeholder="<?= Yii::t('app', 'From (email)') ?>" autocomplete="off">
            <?php else: ?>
                <input type="hidden" id="comment_form_from_input" class="reply-input from-input" value="<?= Html::encode(Yii::$app->user->identity->email) ?>">
            <?php endif ?>
            <input type="text" id="comment_form_message_input" class="reply-input message-input" placeholder="<?= Yii::t('app', 'Write a comment....') ?>" autocomplete="off">
        </div>
        <button class="reply-btn"><i class="ion ion-forward"></i></button>
    </form>
</div>
