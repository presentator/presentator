<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\User;

$user = Yii::$app->user->identity;
?>

<header id="page_header" class="page-header">
    <?php if (isset($this->blocks['page_title'])): ?>
        <?= $this->blocks['page_title'] ?>
    <?php else: ?>
        <h3 class="page-title"><?= Html::encode($this->title) ?></h3>
    <?php endif; ?>

    <?php if (!Yii::$app->user->isGuest): ?>
        <div class="profile" data-bind="clickToggle" data-target="#profile_dropdown_menu">
            <span class="name user-identificator"><?= Html::encode($user->getIdentificator()) ?></span>
            <figure class="avatar">
                <img data-src="<?= $user->getAvatarUrl(true) ?>" data-nocache="1" class="lazy-load avatar-img" alt="Avatar">
            </figure>

            <div class="dropdown-menu" id="profile_dropdown_menu">
                <ul>
                    <li>
                        <a href="<?= $user->type == User::TYPE_SUPER ? Url::to(['users/update', 'id' => $user->id]) : Url::to(['users/settings']) ?>">
                            <i class="ion ion-android-settings"></i>
                            <span class="txt"><?= Yii::t('app', 'Settings') ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= Url::to(['site/logout']) ?>" data-method="post" class="danger-link">
                            <i class="ion ion-log-out"></i>
                            <span class="txt"><?= Yii::t('app', 'Logout') ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    <?php endif ?>
</header>
