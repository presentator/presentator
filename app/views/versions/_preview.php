<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use common\models\Project;
use common\models\Screen;
use common\components\helpers\CFileHelper;

/**
 * $project              \common\models\Project
 * $activeVersion        \common\models\Version
 * $activeScreenId       integer|null
 * $collapseFloatingMenu boolean
 * $allowComment         boolean
 */

$collapseFloatingMenu = isset($collapseFloatingMenu) ? $collapseFloatingMenu : false;
$activeScreenId       = isset($activeScreenId) ? $activeScreenId : null;
$allowComment         = isset($allowComment) ? $allowComment : false;

if ($project->type == Project::TYPE_TABLET) {
    $type = 'tablet';
} elseif ($project->type == Project::TYPE_MOBILE) {
    $type = 'mobile';
} else {
    $type = 'desktop';
}

$generalSlideStyles = [];
if ($project->subtype) {
    $generalSlideStyles['width']  = Project::SUBTYPES[$project->subtype][0] . 'px';
    $generalSlideStyles['height'] = Project::SUBTYPES[$project->subtype][1] . 'px';
}

?>

<div id="version_slider_<?= $activeVersion->id ?>"
    class="version-slider <?= $type ?>"
    data-version-id="<?= $activeVersion->id ?>"
>

    <div class="preview-bar">
        <div class="bar-content">
            <div class="preview-thumbs">
                <?php foreach ($activeVersion->screens as $screen): ?>
                    <div class="box preview-thumb" data-screen-id="<?= $screen->id ?>" data-cursor-tooltip="<?= Html::encode($screen->title) ?>">
                        <div class="content">
                            <figure class="featured">
                                <img class="img lazy-load"
                                    alt="<?= Html::encode($screen->title) ?>"
                                    data-src="<?= $screen->getThumbUrl('medium') ?>"
                                    data-priority="low"
                                >
                            </figure>
                        </div>
                    </div>
                <?php endforeach ?>
                <?php foreach ($activeVersion->screens as $screen): ?>
                    <div class="box preview-thumb" data-screen-id="<?= $screen->id ?>" data-cursor-tooltip="<?= Html::encode($screen->title) ?>">
                        <div class="content">
                            <figure class="featured">
                                <img class="img lazy-load"
                                    alt="<?= Html::encode($screen->title) ?>"
                                    data-src="<?= $screen->getThumbUrl('medium') ?>"
                                    data-priority="low"
                                >
                            </figure>
                        </div>
                    </div>
                <?php endforeach ?>
                <?php foreach ($activeVersion->screens as $screen): ?>
                    <div class="box preview-thumb" data-screen-id="<?= $screen->id ?>" data-cursor-tooltip="<?= Html::encode($screen->title) ?>">
                        <div class="content">
                            <figure class="featured">
                                <img class="img lazy-load"
                                    alt="<?= Html::encode($screen->title) ?>"
                                    data-src="<?= $screen->getThumbUrl('medium') ?>"
                                    data-priority="low"
                                >
                            </figure>
                        </div>
                    </div>
                <?php endforeach ?>
                <?php foreach ($activeVersion->screens as $screen): ?>
                    <div class="box preview-thumb" data-screen-id="<?= $screen->id ?>" data-cursor-tooltip="<?= Html::encode($screen->title) ?>">
                        <div class="content">
                            <figure class="featured">
                                <img class="img lazy-load"
                                    alt="<?= Html::encode($screen->title) ?>"
                                    data-src="<?= $screen->getThumbUrl('medium') ?>"
                                    data-priority="low"
                                >
                            </figure>
                        </div>
                    </div>
                <?php endforeach ?>
                <?php foreach ($activeVersion->screens as $screen): ?>
                    <div class="box preview-thumb" data-screen-id="<?= $screen->id ?>" data-cursor-tooltip="<?= Html::encode($screen->title) ?>">
                        <div class="content">
                            <figure class="featured">
                                <img class="img lazy-load"
                                    alt="<?= Html::encode($screen->title) ?>"
                                    data-src="<?= $screen->getThumbUrl('medium') ?>"
                                    data-priority="low"
                                >
                            </figure>
                        </div>
                    </div>
                <?php endforeach ?>
                <?php foreach ($activeVersion->screens as $screen): ?>
                    <div class="box preview-thumb" data-screen-id="<?= $screen->id ?>" data-cursor-tooltip="<?= Html::encode($screen->title) ?>">
                        <div class="content">
                            <figure class="featured">
                                <img class="img lazy-load"
                                    alt="<?= Html::encode($screen->title) ?>"
                                    data-src="<?= $screen->getThumbUrl('medium') ?>"
                                    data-priority="low"
                                >
                            </figure>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

        <div class="bar-ctrl">
            <span class="ctrl ctrl-prev"><i class="ion ion ion-android-arrow-back"></i></span>
            <span class="ctrl ctrl-next"><i class="ion ion ion-android-arrow-forward"></i></span>
            <nav class="menu">
                <ul>
                    <?php if (count($project->versions) > 1): ?>
                        <li id="fm_versions_handle" class="menu-item versions-handle" data-cursor-tooltip="<?= Yii::t('app', 'Change versions') ?>">
                            <select class="versions-select selectify-select">
                                <?php foreach ($project->versions as $i => $version): ?>
                                    <option value="<?= $version->id ?>" <?= $version->id === $activeVersion->id ? 'selected' : '' ?>>v.<?= $i + 1 ?></option>
                                <?php endforeach ?>
                            </select>
                        </li>
                    <?php endif ?>

                    <?php if ($allowComment): ?>
                        <li id="fm_preview_handle" class="menu-item preview-handle active <?= empty($activeVersion->screens) ? 'disable' : '' ?>" data-cursor-tooltip="<?= Yii::t('app', 'All screens') ?>"" data-cursor-tooltip="<?= Yii::t('app', 'Preview mode') ?>" data-cursor-tooltip-class="hotspots-mode-tooltip"><i class="ion ion-ios-eye-outline"></i></li>
                        <li id="fm_comments_handle" class="menu-item comments-handle <?= empty($activeVersion->screens) ? 'disable' : '' ?>" data-cursor-tooltip="<?= Yii::t('app', 'All screens') ?>"" data-cursor-tooltip="<?= Yii::t('app', 'Comments mode') ?>" data-cursor-tooltip-class="comments-mode-tooltip">
                            <i class="ion ion-ios-chatboxes-outline"><span class="bubble comments-counter">0</span></i>
                        </li>
                    <?php endif ?>

                    <li id="fm_screens_handle" class="menu-item screens-handle <?= empty($activeVersion->screens) ? 'disable' : '' ?>" data-cursor-tooltip="<?= Yii::t('app', 'All screens') ?>"><i class="ion ion-ios-albums-outline"></i></li>
                    <li id="fm_visibility_handle" class="menu-item visibility-handle" data-collapsed-text="<?= Yii::t('app', 'Menu') ?>" data-expanded-text="<?= Yii::t('app', 'Hide') ?>"></li>
                </ul>
            </nav>
        </div>
    </div>

<div class="version-slider-content">
    <span class="preview-bar-toggle"><?= Yii::t('app', 'Hide') ?></span>

        <?php if (0): ?>
    <nav class="floating-menu <?= $collapseFloatingMenu ? 'collapsed' : '' ?>">
        <ul>
            <?php if (count($project->versions) > 1): ?>
                <li id="fm_versions_handle" class="menu-item versions-handle" data-cursor-tooltip="<?= Yii::t('app', 'Change versions') ?>">
                    <select class="versions-select">
                        <?php foreach ($project->versions as $i => $version): ?>
                            <option value="<?= $version->id ?>" <?= $version->id === $activeVersion->id ? 'selected' : '' ?>>v.<?= $i + 1 ?></option>
                        <?php endforeach ?>
                    </select>
                </li>
            <?php endif ?>

            <?php if ($allowComment): ?>
                <li id="fm_preview_handle" class="menu-item preview-handle active <?= empty($activeVersion->screens) ? 'disable' : '' ?>" data-cursor-tooltip="<?= Yii::t('app', 'All screens') ?>"" data-cursor-tooltip="<?= Yii::t('app', 'Preview mode') ?>" data-cursor-tooltip-class="hotspots-mode-tooltip"><i class="ion ion-ios-eye-outline"></i></li>
                <li id="fm_comments_handle" class="menu-item comments-handle <?= empty($activeVersion->screens) ? 'disable' : '' ?>" data-cursor-tooltip="<?= Yii::t('app', 'All screens') ?>"" data-cursor-tooltip="<?= Yii::t('app', 'Comments mode') ?>" data-cursor-tooltip-class="comments-mode-tooltip">
                    <i class="ion ion-ios-chatboxes-outline"></i>
                    <span class="bubble comments-counter">0</span>
                </li>
            <?php endif ?>

            <li id="fm_screens_handle" class="menu-item screens-handle <?= empty($activeVersion->screens) ? 'disable' : '' ?>" data-cursor-tooltip="<?= Yii::t('app', 'All screens') ?>"><i class="ion ion-ios-albums-outline"></i></li>
            <li id="fm_visibility_handle" class="menu-item visibility-handle" data-collapsed-text="<?= Yii::t('app', 'Menu') ?>" data-expanded-text="<?= Yii::t('app', 'Hide') ?>"></li>
        </ul>
    </nav>
        <?php endif ?>

    <?php if (empty($activeVersion->screens)): ?>
        <div class="block text-center m-t-30 m-b-30 padded panel panel-sm">
            <h5><?= Yii::t('app', 'Oops, the selected version does not have any screens.') ?></h5>

            <?php if (count($project->versions) > 1): ?>
                <p><?= Yii::t('app', 'Choose another version') ?></p>
                <select class="versions-select selectify-select">
                    <?php foreach ($project->versions as $i => $version): ?>
                        <option value="<?= $version->id ?>" <?= $version->id === $activeVersion->id ? 'selected' : '' ?>>v.<?= $i + 1 ?></option>
                    <?php endforeach ?>
                </select>
            <?php endif ?>
        </div>
    <?php else: ?>
        <div class="slider-caption-wrapper">
            <div class="slider-caption">
                <h3 class="title active-slide-title"></h3>
                <span class="slide-counter"><?= Yii::t('app', 'Screen')?>&nbsp;<span class="active-slide-order"></span>&nbsp;<?= Yii::t('app', 'of') ?>&nbsp;<?= count($activeVersion->screens) ?></span>
            </div>
        </div>

        <div class="slider-items">
            <?php foreach ($activeVersion->screens as $i => $screen): ?>
                <?php
                    if ($activeScreenId === null && $i === 0) {
                        $isActive = true;
                    } else {
                        $isActive = $activeScreenId !== null && $activeScreenId == $screen->id;
                    }

                    // alignment
                    if ($screen->alignment == Screen::ALIGNMENT_LEFT) {
                        $align = 'left';
                    } elseif ($screen->alignment == Screen::ALIGNMENT_RIGHT) {
                        $align = 'right';
                    } else {
                        $align = 'center';
                    }

                    $background = ($screen->background ? $screen->background : '#eff2f8');

                    // image dimensions
                    $width  = 0;
                    $height = 0;
                    if (file_exists(CFileHelper::getPathFromUrl($screen->imageUrl))) {
                        list($width, $height) = getimagesize(CFileHelper::getPathFromUrl($screen->imageUrl));
                    }

                    // hotspots
                    $hotspots = $screen->hotspots ? json_decode($screen->hotspots, true) : [];
                ?>
                <div class="slider-item screen <?= $isActive ? 'active' : ''?>"
                    data-screen-id="<?= $screen->id ?>"
                    data-alignment="<?= $align ?>"
                    data-title="<?= Html::encode($screen->title) ?>"
                    style="<?= Html::cssStyleFromArray(array_merge($generalSlideStyles, ['background' => $background])) ?>"
                >
                    <figure class="img-wrapper hotspot-layer-wrapper">
                        <img class="img lazy-load hotspot-layer"
                            alt="<?= Html::encode($screen->title) ?>"
                            width="<?= $width ?>px"
                            height="<?= $height ?>px"
                            data-src="<?= $screen->imageUrl ?>"
                            data-priority="<?= $isActive ? 'high' : 'medium' ?>"
                        >

                        <!-- Hotspots -->
                        <div id="hotspots_wrapper">
                            <?php foreach ($hotspots as $id => $spot): ?>
                                <div id="<?= Html::encode($id) ?>"
                                    class="hotspot"
                                    data-link="<?= Html::encode(ArrayHelper::getValue($spot, 'link', '')); ?>"
                                    style="width: <?= Html::encode($spot['width']); ?>px; height: <?= Html::encode($spot['height']); ?>px; top: <?= Html::encode($spot['top']); ?>px; left: <?= Html::encode($spot['left']); ?>px"
                                >
                                </div>
                            <?php endforeach ?>
                        </div>

                        <!-- Comment targets -->
                        <div id="comment_targets_list" class="comment-targets-list">
                            <?php foreach ($screen->primaryScreenComments as $comment): ?>
                                <div class="comment-target"
                                    data-comment-id="<?= $comment->id ?>"
                                    style="left: <?= Html::encode($comment->posX) ?>px; top: <?= Html::encode($comment->posY) ?>px;"
                                ></div>
                            <?php endforeach ?>
                        </div>
                    </figure>
                </div>
            <?php endforeach ?>
        </div>

        <div class="preview-thumbs-wrapper">
            <div class="preview-thumbs">
                <h3 class="title"><?= Yii::t('app', 'Version screens') ?></h3>
                <div class="listing">
                    <?php foreach ($activeVersion->screens as $screen): ?>
                        <div class="box preview-thumb" data-screen-id="<?= $screen->id ?>" data-cursor-tooltip="<?= Html::encode($screen->title) ?>">
                            <div class="content">
                                <figure class="featured">
                                    <img class="img lazy-load"
                                        alt="<?= Html::encode($screen->title) ?>"
                                        data-src="<?= $screen->getThumbUrl('medium') ?>"
                                        data-priority="low"
                                    >
                                </figure>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($allowComment): ?>
        <?= $this->render('_comments_popover') ?>
    <?php endif ?>
</div>

</div>
