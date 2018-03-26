<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\User;

/**
 * @var $this  \yii\web\View
 * @var $users \common\models\User[]
 */
?>

<table class="text-left table-list">
    <thead>
        <tr>
            <th class="min-width">ID</th>
            <th><?= Yii::t('app', 'Profile') ?></th>
            <th><?= Yii::t('app', 'Status') ?></th>
            <th><?= Yii::t('app', 'Type') ?></th>
            <th><?= Yii::t('app', 'Date Modified') ?></th>
            <th class="text-right min-width"><?= Yii::t('app', 'Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($users)): ?>
            <tr>
                <td colspan="6">
                    <span class="txt v-align-middle m-r-5"><?= Yii::t('app', 'No results found') ?></span>
                    <button type="button" class="v-align-middle btn btn-label btn-ghost btn-danger clear-users-search"><?= Yii::t('app', 'Reset search') ?></button>
                </td>
            </tr>
        <?php endif ?>

        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->id ?></td>
                <td>
                    <figure class="avatar">
                        <img data-src="<?= $user->getAvatarUrl(true) ?>" class="lazy-load avatar-img" alt="Avatar">
                    </figure>
                    <span class="m-l-5">
                        <?php if ($user->getFullName()): ?>
                            <?= Html::encode($user->getFullName()); ?>
                        <?php else: ?>
                            N/A
                        <?php endif ?>
                        <small class="hint">(<?= $user->email ?>)</small>
                    </span>
                </td>
                <td>
                    <?php if ($user->status == User::STATUS_ACTIVE): ?>
                        <span class="label label-success"><?= Yii::t('app', 'Active') ?></span>
                    <?php else: ?>
                        <span class="label label-danger"><?= Yii::t('app', 'Inactive') ?></span>
                    <?php endif ?>
                </td>
                <td>
                    <?php if ($user->type == User::TYPE_SUPER): ?>
                        <span class="label label-primary"><?= Yii::t('app', 'Super user')?></span>
                    <?php else: ?>
                        <span class="label label-default"><?= Yii::t('app', 'Regular user')?></span>
                    <?php endif ?>
                </td>
                <td><?= date('Y-m-d H:i:s', $user->updatedAt) ?></td>
                <td class="min-width">
                    <a href="<?= Url::to(['users/update', 'id' => $user->id]) ?>" class="btn btn-ghost btn-label btn-primary m-r-15">
                        <?= Yii::t('app', 'Edit') ?>
                    </a>
                    <a href="<?= Url::to(['users/delete', 'id' => $user->id]) ?>"
                        class="btn btn-ghost btn-label btn-danger"
                        data-method="post"
                        data-confirm="<?= Yii::t('app', 'Are you really sure you want to delete user {userEmail}?', ['userEmail' => $user->email]) ?>"
                    ><?= Yii::t('app', 'Delete') ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
