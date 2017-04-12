<?php
use yii\helpers\Url;

$controller = Yii::$app->controller->id;
$action     = Yii::$app->controller->action->id;
?>
<aside id="page_sidebar" class="page-sidebar">
    <a href="<?= Url::home() ?>" class="logo">
        <img src="<?= Yii::getAlias('@web/images/logo.png') ?>" alt=" Presentator logo">
    </a>
    <nav class="main-menu">
        <ul>
            <li class="<?= ($controller === 'site' && $action === 'index') ? 'active' : ''?>">
                <a href="<?= Url::home() ?>" data-cursor-tooltip="<?= Yii::t('app', 'Dashboard') ?>"><i class="ion ion-home"></i></a>
            </li>
            <li class="<?= ($controller === 'projects') ? 'active' : ''?>">
                <a href="<?= Url::to(['projects/index']) ?>" data-cursor-tooltip="<?= Yii::t('app', 'Projects') ?>"><i class="ion ion-social-buffer"></i></a>
            </li>
            <li class="<?= ($controller === 'users' && $action === 'settings') ? 'active' : ''?>">
                <a href="<?= Url::to(['users/settings']) ?>" data-cursor-tooltip="<?= Yii::t('app', 'Settings') ?>"><i class="ion ion-android-settings"></i></a>
            </li>
        </ul>
    </nav>
    <a href="#" class="bug-report" target="_blank" data-cursor-tooltip="<?= Yii::t('app', 'Create GitHub issue') ?>"><i class="ion ion-bug"></i></a>
</aside>
