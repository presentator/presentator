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

<div id="users_search_bar" class="search-bar">
    <label id="users_search_handle" class="search-icon" for="users_search_input"><i class="ion ion-ios-search"></i></label>
    <span id="users_search_clear" class="search-clear clear-users-search"><i class="ion ion-backspace"></i></span>
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
            'prevPageLabel' => '<i class="ion ion-ios-arrow-left"></i>',
            'nextPageLabel' => '<i class="ion ion-ios-arrow-right"></i>',
        ]); ?>
    </div>
</div>

<?php
$this->registerJsFile('/js/super-index.view.js?v=1521397241');
$this->registerJs('
    var superIndex = new SuperIndex({
        ajaxSearchUsersUrl: "' . Url::to(['super/ajax-search-users']) . '"
    });
', View::POS_READY, 'super-index-js');
