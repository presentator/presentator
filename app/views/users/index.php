<?php
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/**
 * @var $this       \yii\web\View
 * @var $pagination \yii\data\Pagination
 * @var $users      \common\models\User[]
 */

$this->title = Yii::t('app', 'Users');
?>

<?php $this->beginBlock('page_title'); ?>
    <h3 class="page-title"><?= $this->title ?></h3>
    <a href="<?= Url::to(['users/create']) ?>" class="btn btn-cons btn-xs btn-success m-t-5"><?= Yii::t('app', 'Create user') ?></a>
<?php $this->endBlock(); ?>

<div id="users_search_bar" class="search-bar">
    <label id="users_search_handle" class="search-icon" for="users_search_input"><i class="ion ion-ios-search"></i></label>
    <span id="users_search_clear" class="search-clear clear-users-search"><i class="ion ion-md-close-circle"></i></span>
    <input type="text" id="users_search_input" class="search-input" placeholder="<?= Yii::t('app', 'Search for users...') ?>">
</div>

<div id="users_search_list_wrapper" style="display: none;">
    <h5 class="hint"><?= Yii::t('app', 'Search results') ?>:</h5>
    <div id="users_search_list" class="panel users-list"></div>
</div>

<div class="clearfix"></div>

<div id="users_list_wrapper" class="block">
    <div id="users_list" class="panel">
        <?= $this->render('_users_list', ['users' => $users]) ?>
    </div>

    <div class="block text-right">
        <?= LinkPager::widget([
            'pagination'    => $pagination,
            'prevPageLabel' => '<i class="ion ion-ios-arrow-back"></i>',
            'nextPageLabel' => '<i class="ion ion-ios-arrow-forward"></i>',
        ]); ?>
    </div>
</div>

<?php
$this->registerJsFile('/js/super-index.view.js?v=1521397241');
$this->registerJs('
    var superIndex = new SuperIndex({
        ajaxSearchUsersUrl: "' . Url::to(['users/ajax-search-users']) . '"
    });
', View::POS_READY, 'super-index-js');
