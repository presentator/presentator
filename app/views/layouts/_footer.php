<?php
use common\widgets\LanguageSwitch;
?>
<footer id="page_footer" class="page-footer">
    <div class="links">
        <ul class="separator-list">
            <li><?= date('Y') ?> Presentator v<?= Yii::$app->params['currentVersion'] ?></li>
            <li>
                <?= LanguageSwitch::widget(); ?>
            </li>

            <?php if (!empty(Yii::$app->params['githubUrl'])): ?>
                <li>
                    <a href="<?= Yii::$app->params['githubUrl'] ?>" class="icon-link github-link" target="_blank">
                        <i class="ion ion-social-github"></i>
                        <span class="txt">GitHub</span>
                    </a>
                </li>
            <?php endif ?>

            <?php if (!empty(Yii::$app->params['facebookUrl'])): ?>
                <li>
                    <a href="<?= Yii::$app->params['facebookUrl'] ?>" class="icon-link fb-link" target="_blank">
                        <i class="ion ion-social-facebook"></i>
                        <span class="txt">Facebook</span>
                    </a>
                </li>
            <?php endif ?>

            <?php if (!empty(Yii::$app->params['supportUsUrl'])): ?>
            <li>
                <a href="<?= Yii::$app->params['supportUsUrl'] ?>" class="icon-link heart-link" target="_blank">
                    <i class="ion ion-heart"></i>
                    <span class="txt"><?= Yii::t('app', 'Support us') ?></span>
                </a>
            </li>
            <?php endif ?>
        </ul>
    </div>

    <?php if (!empty(Yii::$app->params['showCredits'])): ?>
        <div class="credits"><?= Yii::t('app', 'Crafted by') ?>&nbsp;<a href="http://gani.bg" target="_blank">Gani</a></div>
    <?php endif; ?>
</footer>
