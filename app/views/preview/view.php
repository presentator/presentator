<?php
use yii\web\View;
use yii\helpers\Url;

/**
 * @var $preview       \common\models\Preview
 * @var $project       \common\models\Project
 * @var $accessForm    \app\models\ProjectAccessForm
 * @var $grantedAccess boolean
 */

$this->registerMetaTag([
    'name'    => 'robots',
    'content' => 'noindex, nofollow',
]);

$this->title = $project->title;
?>

<?php if (!$grantedAccess): ?>
    <div id="access_form_wrapper" class="table-wrapper access-form-wrapper active">
        <div class="table-cell padded">
            <div class="access-form-panel">
                <?= $this->render('_form', ['model' => $accessForm]); ?>
            </div>
        </div>
    </div>
<?php endif ?>

<div id="preview_wrapper" class="preview-wrapper <?= $grantedAccess ? 'active' : 'inactive' ?>"></div>

<?php
$this->registerJsFile('/js/pins.js?v=1512236204');
$this->registerJsFile('/js/screen-fit.view.js?v=1512236204');
$this->registerJsFile('/js/screen-comments.view.js?v=1513489044');
$this->registerJsFile('/js/preview.view.js?v=1512236204');
$this->registerJs('
    var preview = new PreviewView({
        grantedAccess: ' . ($grantedAccess ? 'true' : 'false') . ',

        // comments settings
        commentsViewSettings: {
            enableDrag:           false,
            ajaxCommentCreateUrl: "' . Url::to(['screen-comments/ajax-create', 'previewSlug'       => $preview->slug]) .'",
            ajaxCommentsListUrl:  "' . Url::to(['screen-comments/ajax-get-comments', 'previewSlug' => $preview->slug]) .'"
        },

        // ajax urls
        ajaxInvokeAccessUrl:  "' . Url::to(['preview/ajax-invoke-access', 'slug' => $preview->slug]) .'",

        // texts
        commentsTooltipText: "' . Yii::t('app', 'Click to leave a comment') . '"
    });
', View::POS_READY, 'projects-preview-js');
