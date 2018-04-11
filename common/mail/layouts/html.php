<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this    \yii\web\View view component instance
 * @var $message \yii\mail\MessageInterface the message being composed
 * @var $content string main view render result
 */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <style>
        body, html {
            padding: 0;
            margin: 0;
            border: 0;
        }
        body, html,
        .global-wrapper {
            color: #9ca6b3;
            background: #eff2f8;
            font-size: 14px;
            line-height: 24px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .global-wrapper {
            display: block;
            width: 100%;
            height: 100%;
            padding: 30px 0;
        }
        .wrapper {
            width: 500px;
            max-width: 90%;
            margin: 0 auto;
            font-size: inherit;
            font-family: inherit;
            line-height: inherit;
        }
        p {
            display: block;
            margin: 10px 0;
        }
        table {
            font-size: inherit;
            font-family: inherit;
            line-height: inherit;
            width: 100%;
            max-width: 100%;
            border-spacing: 0;
            border-collapse: collapse;
        }
        td, th {
            vertical-align: middle;
            border: 0;
        }
        a {
            color: #9ca6b3 !important;
            text-decoration: underline !important;
        }
        a:hover {
            color: #9ca6b3 !important;
            text-decoration: none !important;
        }
        .btn {
            display: inline-block;
            vertical-align: top;
            border: 0;
            color: #fff !important;
            background: #44e4a6 !important;
            text-decoration: none !important;
            line-height: 42px;
            min-width: 130px;
            text-align: center;
            padding: 0 30px;
            margin: 10px 0;
            border-radius: 30px;
        }
        .btn:hover {
            color: #fff !important;
            background: #1fd68f !important;
        }
        .btn:active {
            color: #fff !important;
            background: #1cc080 !important;
        }
        .hint {
            font-size: 12px;
            line-height: 14px;
        }
        .shadowed {
            border-radius: 3px;
            box-shadow: 0px 3px 10px 0px rgba(44, 75, 137, 0.15);
        }
        .emphasis {
            padding: 15px;
            background: #f5f7fa;
            border-radius: 3px;
        }

        /* Header */
        .header {
            display: block;
            text-align: center;
            padding: 20px;
            color: #fff;
            background-color: #322956;
            background-image: url(<?= Url::to('@web/images/pattern.png', true) ?>);
            border: 0;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        .logo {
            color: #fff !important;
            text-decoration: none !important;
        }
        .logo:hover {
            color: #fff !important;
        }
        .logo img {
            vertical-align: middle;
            display: inline-block;
        }
        .logo .title {
            display: block;
            width: 100%;
            margin: 10px 0 0;
            color: inherit;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        /* Content */
        .content {
            display: block;
            padding: 15px 30px;
            background: #fff;
            border-top: 0px;
            border-bottom-left-radius: 3px;
            border-bottom-right-radius: 3px;
        }

        /* Footer */
        .footer {
            display: block;
            padding: 20px 0;
            margin: 0;
            text-align: left;
            font-size: 12px;
            line-height: 14px;
            color: inherit;
        }
        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="global-wrapper">
        <div class="wrapper shadowed">
            <div class="header">
                <a href="<?= Yii::$app->params['publicUrl'] ?>" class="logo">
                    <img src="<?= rtrim(Yii::$app->params['publicUrl'], '/') . '/images/logo_large_white.png' ?>" alt="Presentator logo" width="45" height="60" />
                    <h3 class="title">Presentator</h3>
                </a>
            </div>
            <div class="content">
                <?= $content ?>
            </div>
        </div>

        <div class="wrapper">
            <div class="footer">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 50%; text-align: left;">
                            <p>Presentator - Your designs deserve it!</p>
                        </td>
                        <td style="width: 50%; text-align: right;">
                            <?php if (!empty(Yii::$app->params['facebookUrl'])): ?>
                                <a href="<?= Yii::$app->params['facebookUrl'] ?>" class="social-icon">Facebook</a>
                            <?php endif ?>
                            |
                            <a href="mailto:<?= Yii::$app->params['supportEmail'] ?>" class="social-icon"><?= Yii::$app->params['supportEmail'] ?></a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
