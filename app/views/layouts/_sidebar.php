<?php
use yii\helpers\Url;
use common\models\User;

$user       = Yii::$app->user->identity;
$controller = Yii::$app->controller->id;
$action     = Yii::$app->controller->action->id;
?>
<aside id="page_sidebar" class="page-sidebar">
    <a href="<?= Url::to(['site/index']) ?>" class="logo">
        <img src="<?= Yii::getAlias('@web/images/logo.png') ?>?v=1492082790" alt=" Presentator logo">
    </a>
    <nav class="main-menu">
        <ul>
            <li class="<?= ($controller === 'site' && $action === 'index') ? 'active' : ''?>">
                <a href="<?= Url::to(['site/index']) ?>" data-cursor-tooltip="<?= Yii::t('app', 'Dashboard') ?>"><i class="ion ion-md-home"></i></a>
            </li>
            <li class="<?= ($controller === 'projects') ? 'active' : ''?>">
                <a href="<?= Url::to(['projects/index']) ?>" data-cursor-tooltip="<?= Yii::t('app', 'Projects') ?>"><i class="ion ion-ios-apps"></i></a>
            </li>
            <li class="<?= ($controller === 'users') ? 'active' : ''?>">
                <?php if ($user->type == User::TYPE_SUPER): ?>
                    <a href="<?= Url::to(['users/index']) ?>" data-cursor-tooltip="<?= Yii::t('app', 'Users') ?>"><i class="ion ion-ios-people"></i></a>
                <?php else: ?>
                    <a href="<?= Url::to(['users/settings']) ?>" data-cursor-tooltip="<?= Yii::t('app', 'Settings') ?>"><i class="ion ion-md-settings"></i></a>
                <?php endif ?>
            </li>
        </ul>
    </nav>

    <?php if (!empty(Yii::$app->params['issuesUrl'])): ?>
        <a href="<?= Yii::$app->params['issuesUrl'] ?>" class="bug-report" target="_blank" data-cursor-tooltip="<?= Yii::t('app', 'Create GitHub issue') ?>">
            <i class="ion ion-ios-bug"></i>
        </a>
    <?php endif ?>
</aside>
