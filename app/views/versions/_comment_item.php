<?php
/**
 * $comment     /common/models/ScreenComment
 * $scaleFactor float
 * $isUnread    boolean
 * $isResolved  boolean
 */

if (!isset($isResolved)) {
    $isResolved = false;
}

if (!isset($isUnread)) {
    $isUnread = false;
}

$left = (float) ($comment->posX / $scaleFactor);
$top  = (float) ($comment->posY / $scaleFactor);

?>
<div class="comment-target <?= $isResolved ? 'resolved' : '' ?> <?= $isUnread ? 'unread' : '' ?>"
	data-original-left="<?= $comment->posX ?>"
	data-original-top="<?= $comment->posY ?>"
    data-comment-id="<?= $comment->id ?>"
    style="left: <?= $left ?>px; top: <?= $top ?>px;"
></div>
