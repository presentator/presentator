<?php
use yii\helpers\Html;

/**
 * @var $user    \common\models\User
 * @var $project \common\models\Project
 */

$isCurrentUser = false;
if (!Yii::$app->user->isGuest && Yii::$app->user->identity->id == $user->id) {
    $isCurrentUser = true;
}
?>
<div class="user-list-item <?= $isCurrentUser ? 'highlight' : '' ?>"
    data-user-id="<?= $user->id ?>"
    data-project-id="<?= $project->id ?>"
    <?php if ($isCurrentUser): ?>
        data-confirm-text="<?= Yii::t('app', 'Warning! Do you really want to unlink yourself from the current project?') ?>"
    <?php else: ?>
        data-confirm-text="<?= Yii::t('app', 'Do you really want to unlink {user}?', ['user' => Html::encode($user->getIdentificator())]) ?>"
    <?php endif ?>
>
    <div class="table-wrapper">
        <div class="table-cell min-width">
            <figure class="avatar">
                <img data-src="<?= $user->getAvatarUrl(true) ?>" alt="Avatar" class="lazy-load" data-priority="low">
            </figure>
        </div>
        <div class="table-cell max-width name">
            <?php if ($user->getFullName()): ?>
                <span class="name"><?= Html::encode($user->getFullName()) ?></span>
                <span class="email">(<?= Html::encode($user->email) ?>)</span>
            <?php else: ?>
                <?= Html::encode($user->email) ?>
            <?php endif ?>
        </div>
        <div class="table-cell min-width">
            <a href="#" class="remove-handle" data-cursor-tooltip="<?= Yii::t('app', 'Unlink admin') ?>">
                <i class="ion ion-md-trash"></i>
            </a>
        </div>
    </div>
</div>
