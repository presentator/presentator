<?php
use yii\helpers\Html;

/**
 * @var $model            \common\models\Screen
 * @var $newComments      integer
 * @var $lazyLoad         boolean
 * @var $lazyLoadPriority string
 * @var $createThumb      boolean
 */

// Default values
$newComments      = isset($newComments)      ? $newComments      : 0;
$lazyLoad         = isset($lazyLoad)         ? $lazyLoad         : true;
$lazyLoadPriority = isset($lazyLoadPriority) ? $lazyLoadPriority : 'medium';
$createThumb      = isset($createThumb)      ? $createThumb      : true;

$url    = $model->getThumbUrl('medium', $createThumb);
$hasImg = !empty(@getimagesize($url));
?>

<div class="box screen-item" data-screen-id="<?= $model->id ?>">
    <div class="content">
        <?php if ($newComments > 0): ?>
            <div class="pin pin-warning comments-notification" data-cursor-tooltip="<?= Yii::t('app', 'Has unread comments')?>">
                <i class="ion ion-ios-notifications"></i>
            </div>
        <?php endif ?>

        <figure class="featured with-overlay-panel">
            <?php if ($lazyLoad): ?>
                <img data-src="<?= $url ?>" class="img lazy-load screen-img" alt="<?= Html::encode($model->title) ?>" data-priority="<?= $lazyLoadPriority ?>">
            <?php else: ?>
                <img src="<?= $hasImg ? $url : '' ?>" class="img screen-img" alt="<?= Html::encode($model->title) ?>">
            <?php endif ?>

            <div class="featured-overlay"></div>
            <div class="overlay-item top-left">
                <div class="form-group">
                    <input type="checkbox" id="screen_bulk_<?= $model->id ?>" class="screen-bulk-checkbox">
                    <label for="screen_bulk_<?= $model->id ?>"></label>
                </div>
            </div>
            <div class="overlay-item top-right">
                <div class="dropdown-handle more-options">
                    <i class="ion ion-ios-more" data-bind="clickToggle" data-target=".dropdown-menu" data-class="active" data-isolate="parent"></i>
                    <div class="dropdown-menu small compact">
                        <ul>
                            <li>
                                <a href="#" class="danger-link screen-delete"><?= Yii::t('app', 'Delete') ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="overlay-item center">
                <span class="circle-icon open-screen-edit">
                    <i class="ion ion-ios-options"></i>
                </span>
            </div>
        </figure>

        <div class="overlay-panel">
            <h3 class="title title-sm">
                <span class="default-link open-screen-edit screen-title" title="<?= Html::encode($model->title) ?>">
                    <?= Html::encode($model->title) ?>
                </span>
            </h3>
        </div>
    </div>
</div>
