<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\ProjectPreview;
use common\components\helpers\CStringHelper;

/**
 * @var $this            \yii\web\View
 * @var $projects        \common\models\Project[]
 * @var $shareForm       \app\models\ProjectShareForm
 * @var $commentCounters integer
 * @var $mentionsList    array
 */

$this->title = Yii::t('app', '{projectTitle} - Projects', ['projectTitle' => Html::encode($project->title)]);

$totalVersions     = count($project->versions);
$viewUrl           = $project->getPreviewUrl(ProjectPreview::TYPE_VIEW);
$viewAndCommentUrl = $project->getPreviewUrl(ProjectPreview::TYPE_VIEW_AND_COMMENT);
?>

<?php $this->beginBlock('page_title'); ?>
    <h3 class="page-title">
        <a href="<?= Url::to(['projects/index']) ?>" class="item"><?= Yii::t('app', 'Projects') ?></a>
        <span class="item project-title"><?= Html::encode($project->title) ?></span>
    </h3>
    <button type="button" data-popup="#screens_upload_popup" class="btn btn-xs btn-ghost m-t-5 visible-on-scroll"><?= Yii::t('app', 'Add screens') ?></button>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('before_global_wrapper'); ?>
    <aside id="project_sidebar" class="project-sidebar" data-project-id="<?= $project->id ?>">
        <div class="meta">
            <div class="btn-group m-b-30">
                <div class="item admins" data-popup="#admins_popup" data-cursor-tooltip="<?= Yii::t('app', 'Manage admins') ?>">
                    <i class="ion ion-md-people"></i>
                </div>
                <div class="item links" data-popup="#links_popup" data-cursor-tooltip="<?= Yii::t('app', 'Links') ?>">
                    <i class="ion ion-md-link"></i>
                </div>
                <div class="item share" data-popup="#share_popup" data-cursor-tooltip="<?= Yii::t('app', 'Share') ?>">
                    <i class="ion ion-md-share"></i>
                </div>
            </div>
            <button type="button" class="btn btn-xs btn-primary btn-ghost block project-edit-handle"><?= Yii::t('app', 'Edit project') ?></button>
        </div>

        <nav class="nav">
            <ul id="versions_list">
                <?php foreach ($project->versions as $i => $version): ?>
                    <?= $this->render('/versions/_nav_item', ['model' => $version, 'isActive' => ($i + 1 === $totalVersions ? true : false)]) ?>
                <?php endforeach; ?>
            </ul>
            <div class="block m-t-30 p-l-30 p-r-30">
                <button type="button" id="version_create" class="btn btn-xs btn-success block" data-project-id="<?= $project->id ?>">
                    <i class="ion ion-md-add m-r-5"></i>
                    <span class="txt"><?= Yii::t('app', 'New version') ?></span>
                </button>
            </div>
        </nav>
    </aside>
<?php $this->endBlock(); ?>

<div id="version_screens_tabs" class="tabs version-screens-wrapper">
    <div class="tabs-content no-padding">
        <?php foreach ($project->versions as $i => $version): ?>
            <?php $isActive = ($i + 1 === $totalVersions ? true : false); ?>
            <?= $this->render('/versions/_content_item', [
                'model'            => $version,
                'isActive'         => $isActive,
                'commentCounters'  => $commentCounters,
                'lazyLoadPriority' => ($isActive ? 'high' : 'medium'),
            ]) ?>
        <?php endforeach; ?>
    </div>
</div>

<div id="screens_bulk_panel" class="fixed-panel" style="display: none;">
    <span class="close screen-bulk-reset"><i class="ion ion-md-close"></i></span>
    <div class="table-wrapper">
        <div class="table-cell min-width">
            <div class="form-group form-group-sm no-margin versions-select">
                <select name="" id="bulk_versions_select" data-prompt-option="<?= Yii::t('app', 'Move to version...') ?>">
                </select>
            </div>
        </div>
        <div class="table-cell min-width p-l-15 p-r-15"><?= Yii::t('app', 'or') ?></div>
        <div class="table-cell min-width">
            <button type="button" id="bulk_delete_btn" class="btn btn-xs btn-danger"><?= Yii::t('app', 'Delete selected') ?></button>
        </div>
        <div class="table-cell text-right">
            <a href="#" class="screen-bulk-reset"><?= Yii::t('app', 'Reset selection') ?></a>
        </div>
    </div>
</div>

<!-- Project edit popup -->
<div id="project_edit_popup" class="popup">
    <div class="popup-content">
        <h3 class="popup-title"><?= Yii::t('app', 'Edit project') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content"></div>
    </div>
</div>

<!-- Version edit popup -->
<div id="version_edit_popup" class="popup">
    <div class="popup-content">
        <h3 class="popup-title"><?= Yii::t('app', 'Edit version') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content"></div>
    </div>
</div>

<!-- Version create popup -->
<div id="version_create_popup" class="popup">
    <div class="popup-content">
        <h3 class="popup-title"><?= Yii::t('app', 'Create version') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content"></div>
    </div>
</div>

<!-- Links popup -->
<div id="links_popup" class="popup">
    <div class="popup-content">
        <h3 class="popup-title"><?= Yii::t('app', 'Project preview links') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content">
            <table class="table">
                <?php if ($viewUrl): ?>
                    <tr>
                        <th><?= Yii::t('app', 'View only') ?></th>
                        <td>
                            <a href="<?= $viewUrl ?>" class="preview-link" target="_blank" data-cursor-tooltip="<?= Yii::t('app', 'Open link in new tab') ?>">
                                <?= $viewUrl ?>
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if ($viewAndCommentUrl): ?>
                    <tr>
                        <th><?= Yii::t('app', 'View and comment') ?></th>
                        <td>
                            <a href="<?= $viewAndCommentUrl ?>"  class="preview-link" target="_blank" data-cursor-tooltip="<?= Yii::t('app', 'Open link in new tab') ?>"><?= $viewAndCommentUrl ?></a>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Admins popup -->
<div id="admins_popup" class="popup" data-esc-close="false">
    <div class="popup-content">
        <h3 class="popup-title"><?= Yii::t('app', 'Manage project admins') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content">
            <div id="admins_list" class="users-list">
                <?php foreach ($project->users as $user): ?>
                    <?= $this->render('_admin_list_item', ['user' => $user, 'project' => $project]) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <footer class="footer m-t-15">
            <input type="hidden" id="admins_search_project_id" name="projectId" value="<?= $project->id ?>">
            <div class="form-group no-margin">
                <input type="text"
                    id="admins_search_term_input"
                    autocomplete="off"
                    placeholder="<?= Yii::$app->params['fuzzyUsersSearch'] ? Yii::t('app', 'Type name or email to add a new admin') : Yii::t('app', 'Type user email to add a new admin') ?>"
                >
                <div id="admins_search_suggestions" class="input-dropdown" data-keyboard-nav="#admins_search_term_input" style="display: none;"></div>
            </div>
        </footer>
    </div>
</div>

<!-- Share popup -->
<div id="share_popup" class="popup">
    <div class="popup-content">
        <h3 class="popup-title"><?= Yii::t('app', 'Send project preview link') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content">
            <?= $this->render('_share_form', ['model' => $shareForm]) ?>
        </div>
    </div>
</div>

<!-- Screens upload popup -->
<div id="screens_upload_popup" class="popup" data-overlay-close="false">
    <div class="popup-content">
        <h3 class="popup-title text-center"><?= Yii::t('app', 'Screens upload') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content">
            <div id="upload_container" class="upload-container">
                <div class="loader-wrapper">
                    <div class="loader"></div>
                    <p><?= Yii::t('app', 'Uploading and generating thumbs...') ?></p>
                </div>

                <div class="content dz-message">
                    <i class="ion ion-md-cloud-upload"></i>
                    <p><?= Yii::t('app', 'Click or drop here to upload') ?> <em>(png, jpg)</em></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Screen edit popup -->
<div id="screens_edit_popup" class="popup">
    <div class="popup-content">
        <h3 class="popup-title"><?= Yii::t('app', 'Screen settings') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content">
        </div>
    </div>
</div>

<div id="hotspot_context_menu" class="context-menu hotspot-context-menu">
    <div class="menu-item duplicate-handle"><?= Yii::t('app', 'Duplicate') ?></div>
    <div class="menu-item bulk-select-handle"><?= Yii::t('app', 'Bulk select') ?></div>
    <div class="menu-item danger-link delete-handle"><?= Yii::t('app', 'Delete') ?></div>
</div>

<?php
$this->registerJsFile('/js/hotspots.js?v=1522585665');
$this->registerJsFile('/js/pins.js?v=1522585665');
$this->registerJsFile('/js/project-view.view.js?v=1529438625');
$this->registerJsFile('/js/version.view.js?v=1527964703');
$this->registerJsFile('/js/screen-comments.view.js?v=1527964703');
$this->registerJsFile('/js/screen-hotspots.view.js?v=1527964703');
$this->registerJsFile('/js/screen-fit.view.js?v=1527964703');
$this->registerJsFile('/js/screen.view.js?v=1527964703');
$this->registerJs('
    var projectView = new ProjectView({
        ajaxGetUpdateFormUrl:  "' . Url::to(['projects/ajax-get-update-form', 'id' => $project->id]) .'",
        ajaxSaveUpdateFormUrl: "' . Url::to(['projects/ajax-save-update-form', 'id' => $project->id]) .'",
        ajaxShareProjectUrl:   "' . Url::to(['projects/ajax-share', 'id' => $project->id]) .'",
        ajaxSearchUsersUrl:    "' . Url::to(['projects/ajax-search-users', 'id' => $project->id]) .'",
        ajaxAddAdminUrl:       "' . Url::to(['projects/ajax-add-admin']) .'",
        ajaxRemoveAdminUrl:    "' . Url::to(['projects/ajax-remove-admin']) .'"
    });

    var screenView = new ScreenView({
        maxUploadSize: ' . Yii::$app->params['maxUploadSize'] . ',

        // texts
        versionOptionText:       "' . Yii::t('app', 'Version') . '",
        confirmDeleteText:       "' . Yii::t('app', 'Do you really want to delete the selected screen?') . '",
        confirmBulkDeleteText:   "' . Yii::t('app', 'Do you really want to delete the selected screens?') . '",
        hotspotsTooltipText:     "' . Yii::t('app', 'Click and drag to create hotspot') . '",
        commentsTooltipText:     "' . Yii::t('app', 'Click to leave a comment') . '",
        replaceImageConfirmText: "' . Yii::t('app', 'Do you really want to replace the screen image?') . '",

        // ajax urls
        ajaxGetSettingsUrl:        "' . Url::to(['screens/ajax-get-settings']) .'",
        ajaxSaveSettingsFormUrl:   "' . Url::to(['screens/ajax-save-settings-form']) .'",
        ajaxReplaceScreenImageUrl: "' . Url::to(['screens/ajax-replace']) .'",
        ajaxUploadUrl:             "' . Url::to(['screens/ajax-upload']) .'",
        ajaxDeleteUrl:             "' . Url::to(['screens/ajax-delete']) .'",
        ajaxReorderUrl:            "' . Url::to(['screens/ajax-reorder']) .'",
        ajaxGetThumbsUrl:          "' . Url::to(['screens/ajax-get-thumbs']) .'",
        ajaxMoveScreensUrl:        "' . Url::to(['screens/ajax-move-screens']) .'",
        ajaxGetScreensSliderUrl:   "' . Url::to(['versions/ajax-get-screens-slider']) .'",

        // sub-classes
        hotspotsViewSettings: {
            ajaxSaveHotspotsUrl: "' . Url::to(['screens/ajax-save-hotspots']) .'"
        },
        commentsViewSettings: {
            mentionsList:                 ' . json_encode($mentionsList). ',
            ajaxCommentCreateUrl:         "' . Url::to(['screen-comments/ajax-create']) .'",
            ajaxCommentDeleteUrl:         "' . Url::to(['screen-comments/ajax-delete']) .'",
            ajaxCommentsListUrl:          "' . Url::to(['screen-comments/ajax-get-comments']) .'",
            ajaxCommentPositionUpdateUrl: "' . Url::to(['screen-comments/ajax-position-update']) .'",
            ajaxCommentStatusUpdateUrl:   "' . Url::to(['screen-comments/ajax-status-update']) .'"
        },
        versionViewSettings: {
            ajaxGetFormUrl:  "' . Url::to(['versions/ajax-get-form', 'projectId' => $project->id]) .'",
            ajaxSaveFormUrl: "' . Url::to(['versions/ajax-save-form', 'projectId' => $project->id]) .'",
            ajaxCreateUrl:   "' . Url::to(['versions/ajax-create']) .'",
            ajaxDeleteUrl:   "' . Url::to(['versions/ajax-delete']) .'"
        }
    });
', View::POS_READY, 'projects-js');
