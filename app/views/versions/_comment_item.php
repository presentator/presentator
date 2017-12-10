<?php
/**
 * $comment      /common/models/ScreenComment
 * $scaleFactor  float
 * $isUnread     boolean
 * $isResolved   boolean
 * $maxX         float   Max allowed comment X position.
 * $maxY         float   Max allowed comment Y position.
 */

$maxX       = isset($maxX)       ? $maxX : INF;
$maxY       = isset($maxY)       ? $maxY : INF;
$isResolved = isset($isResolved) ? $isResolved : false;
$isUnread   = isset($isUnread)   ? $isUnread : false;

$left      = (float) ($comment->posX / $scaleFactor);
$top       = (float) ($comment->posY / $scaleFactor);
$tolerance = 35;

// normalize dimensions
if ($left + $tolerance >= $maxX) {
    $left = $maxX - $tolerance;
}

if ($top + $tolerance >= $maxY) {
    $top = $maxY - $tolerance;
}
?>
<div class="comment-target <?= $isResolved ? 'resolved' : '' ?> <?= $isUnread ? 'unread' : '' ?>"
	data-original-left="<?= $comment->posX ?>"
	data-original-top="<?= $comment->posY ?>"
    data-comment-id="<?= $comment->id ?>"
    style="left: <?= $left ?>px; top: <?= $top ?>px;"
></div>
