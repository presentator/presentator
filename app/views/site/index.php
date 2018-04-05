<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/**
 * @var $this            \yii\web\View
 * @var $user            \common\models\User
 * @var $projects        \common\models\Project[]
 * @var $comments        \common\models\ScreenComment[]
 * @var $commentCounters integer
 */

$this->title = Yii::t('app', 'Dashboard') ;
?>

<?php if (!empty($comments)): ?>
    <!-- Latest leaved comments -->
    <h5 class="m-t-0"><?= Yii::t('app', 'Latest leaved comments') ?></h5>
    <div class="dashboard-widget comments-widget">
        <table class="table-list">
            <thead>
                <tr>
                    <th class="min-width"><?= Yii::t('app', 'Screen') ?></th>
                    <th><?= Yii::t('app', 'From') ?></th>
                    <th><?= Yii::t('app', 'Message') ?></th>
                    <th class="min-width text-right"><?= Yii::t('app', 'Is seen') ?></th>
                    <th class="min-width text-right"><?= Yii::t('app', 'Date') ?></th>
                    <th class="min-width text-right"><?= Yii::t('app', 'Action') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td class="min-width">
                            <div class="thumb">
                                <img data-src="<?= $comment->screen->getThumbUrl('small') ?>" class="lazy-load" alt="<?= Html::encode($comment->screen->title) ?>">
                            </div>
                        </td>
                        <td class="min-width"><?= Html::encode($comment->from) ?></td>
                        <td><?= Html::encode($comment->message) ?></td>
                        <td class="min-width text-center">
                            <?php if ($comment->isReadByLoginUser()): ?>
                                <span class="marker marker-success" data-cursor-tooltip="<?= Yii::t('app', 'Seen') ?>"></span>
                            <?php else: ?>
                                <span class="marker marker-danger" data-cursor-tooltip="<?= Yii::t('app', 'Not seen') ?>"></span>
                            <?php endif ?>
                        </td>
                        <td class="min-width text-right"><?= date('d.m.Y H:i', $comment->createdAt) ?></td>
                        <td class="min-width text-right">
                            <a href="<?= Url::to([
                                'projects/view',
                                'id'             => $comment->screen->project->id,
                                'screen'         => $comment->screen->id,
                                'comment_target' => ($comment->replyTo ? $comment->replyTo : $comment->id),
                                'reply_to'       => $comment->id
                            ]) ?>" class="btn btn-label btn-ghost">
                                <?= Yii::t('app', 'View and Reply') ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <div class="clearfix m-t-30"></div>
<?php endif; ?>

<?php if (!empty($projects)): ?>
    <!-- Latest projects -->
    <h5 class="m-t-0"><?= Yii::t('app', 'Latest projects') ?></h5>
    <div class="projects-list">
        <a href="<?= Url::to(['projects/index', '#' => 'project_create_popup']) ?>" class="box action-box primary">
            <div class="content">
                <div class="table-wrapper">
                    <div class="table-cell">
                        <span class="icon"><i class="ion ion-ios-plus-outline"></i></span>
                        <span class="txt"><?= Yii::t('app', 'Create new project') ?></span>
                    </div>
                </div>
            </div>
        </a>

        <?php foreach ($projects as $project): ?>
            <?= $this->render('/projects/_item', [
                'model'       => $project,
                'newComments' => ArrayHelper::getValue($commentCounters, $project->id, 0),
            ]); ?>
        <?php endforeach; ?>
    </div>
    <div class="block text-center">
        <a href="<?= Url::to('projects/index') ?>" class="btn btn-primary btn-ghost btn-cons"><?= Yii::t('app', 'All projects') ?></a>
    </div>
<?php else: ?>
    <div class="welcome-panel">
        <img class="featured" src="/images/logo_stamp.png" alt="Presentator logo" width="100">
        <p><?= Yii::t('app', 'Hello and welcome to Presentator!') ?></p>
        <p><?= Yii::t('app', 'To create your first project click on the button below:') ?></p>
        <div class="block m-t-30 m-b-30">
            <a href="<?= Url::to(['projects/index', '#' => 'project_create_popup']) ?>" class="block btn btn-lg btn-cons btn-success">
                <i class="ion ion-plus m-r-5"></i>
                <span class="txt"><?= Yii::t('app', 'Create my first project') ?></span>
            </a>
        </div>
    </div>
<?php endif ?>
