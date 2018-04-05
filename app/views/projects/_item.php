<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $model       \common\models\Project
 * @var $newComments integer
 */

$newComments       = isset($newComments) ? $newComments : 0;
$hasFeaturedScreen = !empty($model->featuredScreen);
?>
<div class="box" data-project-id="<?= $model->id ?>">
    <div class="content">
        <?php if ($newComments > 0): ?>
            <div class="pin pin-warning" data-cursor-tooltip="<?= Yii::t('app', 'Has unread comments')?>">
                <i class="ion ion-ios-bell"></i>
            </div>
        <?php endif ?>

        <figure class="featured with-overlay-panel <?= !$hasFeaturedScreen ? 'no-image' : '' ?>">
            <?php if ($hasFeaturedScreen): ?>
                <img data-src="<?= $model->featuredScreen->getThumbUrl('medium') ?>" class="lazy-load" data-priority="high" alt="<?= Html::encode($model->featuredScreen->title) ?>">
            <?php endif ?>

            <div class="featured-overlay"></div>

            <div class="overlay-item top-right">
                <div class="dropdown-handle more-options">
                    <i class="ion ion-android-more-horizontal" data-bind="clickToggle" data-target=".dropdown-menu" data-class="active" data-isolate="parent"></i>
                    <div class="dropdown-menu small compact">
                        <ul>
                            <li>
                                <a href="<?= Url::to(['projects/view', 'id' => $model->id, '#' => 'share_popup']) ?>"><?= Yii::t('app', 'Sharing') ?></a>
                            </li>
                            <li>
                                <a href="<?= Url::to(['projects/view', 'id' => $model->id, '#' => 'links_popup']) ?>"><?= Yii::t('app', 'Preview links') ?></a>
                            </li>
                            <li>
                                <a href="<?= Url::to(['projects/delete', 'id' => $model->id]) ?>"
                                    class="danger-link"
                                    data-method="post"
                                    data-confirm="<?= Yii::t('app', 'Do you really want to delete project {projectTitle}?', ['projectTitle' => Html::encode($model->title)]) ?>"
                                >
                                    <?= Yii::t('app', 'Delete') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="overlay-item center">
                <a href="<?= Url::to(['projects/view', 'id' => $model->id]) ?>" class="circle-icon">
                    <i class="ion ion-ios-eye"></i>
                </a>
            </div>
        </figure>

        <div class="overlay-panel">
            <h3 class="title">
                <a href="<?= Url::to(['projects/view', 'id' => $model->id]) ?>"><?= Html::encode($model->title) ?></a>
            </h3>
            <div class="meta">
                <div class="item"><?= Yii::t('app', 'Versions') ?>: <?= count($model->versions) ?></div>
                <div class="item"><?= Yii::t('app', 'Screens') ?>: <?= count($model->screens) ?></div>
            </div>
        </div>
    </div>
</div>
