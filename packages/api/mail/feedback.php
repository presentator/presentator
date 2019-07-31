<?php
use yii\helpers\Html;

/**
 * @var $from    string
 * @var $message string
 */
?>
<p>Hello,</p>

<p>The following feedback was received from <?= Html::encode($from) ?>:</p>

<p class="emphasis"><?= nl2br(strip_tags($message)) ?></p>
