<?php
use yii\web\View;
use yii\helpers\Url;

/**
 * @var $this         \yii\web\View
 * @var $form         \app\models\SuperUserForm
 * @var $statusesList array
 * @var $typesList    array
 */

$this->title = Yii::t('app', 'Create User');
?>

<?php $this->beginBlock('page_title'); ?>
    <h3 class="page-title">
        <a href="<?= Url::to(['users/index']) ?>" class="item"><?= Yii::t('app', 'Users') ?></a>
        <span class="item project-title"><?= Yii::t('app', 'Create') ?></span>
    </h3>
<?php $this->endBlock(); ?>


<div class="base-wrapper">
    <div class="panel padded">
        <?= $this->render('_super_form', [
            'model'        => $form,
            'statusesList' => $statusesList,
            'typesList'    => $typesList,
        ]) ?>
    </div>
</div>
