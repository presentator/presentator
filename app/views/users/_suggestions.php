<?php
use yii\helpers\Html;

/**
 * @var $users \common\models\User[]
 */
?>

<?php if (empty($users)): ?>
    <div class="item not-found"><?= Yii::t('app', 'No results found') ?></div>
<?php else: ?>
    <?php foreach ($users as $i => $user): ?>
        <div class="item user-suggestion-item <?= $i == 0 ? 'active' : '' ?>" data-user-id="<?= $user->id ?>" data-value="<?= Html::encode($user->getIdentificator()) ?>">
            <?php if ($user->getFullName()): ?>
                <?= Html::encode($user->getFullName()) ?>
                (<?= Html::encode($user->email) ?>)
            <?php else: ?>
                <?= Html::encode($user->email) ?>
            <?php endif ?>
        </div>
    <?php endforeach ?>
<?php endif ?>
