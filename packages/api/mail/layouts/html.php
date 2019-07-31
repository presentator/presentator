<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this    \yii\web\View              view component instance
 * @var $message \yii\mail\MessageInterface the message being composed
 * @var $content string                     main view render result
 */

$cidLogo = $message->embed(Yii::getAlias('@app/mail/layouts/logo.png'));
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <style>
        *,
        ::before,
        ::after {
            box-sizing: border-box;
        }
        body, html {
            padding: 0;
            margin: 0;
            border: 0;
        }
        body, html,
        .global-wrapper {
            color: #454c69;
            background: #f5f7fb;
            font-size: 14px;
            line-height: 21px;
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
        strong {
            font-weight: bold;
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
            color: inherit !important;
            text-decoration: underline !important;
        }
        a:hover {
            color: inherit !important;
            text-decoration: none !important;
        }
        a img {
            border: 0;
        }
        .btn {
            display: inline-block;
            vertical-align: top;
            border: 0;
            outline: 0;
            color: #fff !important;
            background: #4038ab !important;
            text-decoration: none !important;
            line-height: 24px;
            min-width: 130px;
            font-weight: bold;
            text-align: center;
            padding: 10px 20px;
            margin: 10px 0;
            border-radius: 6px;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            color: #fff !important;
            background: #362f90 !important;
        }
        .btn:active {
            color: #fff !important;
            background: #322b85 !important;
            -webkit-box-shadow: 0px 0px 0px 2px #8f8ad9;
            box-shadow: 0px 0px 0px 2px #8f8ad9;
        }
        .hint {
            display: inline-block;
            vertical-align: top;
            margin: 0 0 10px;
            font-size: 12px !important;
            line-height: 15px !important;
            color: #848cae !important;
        }
        .emphasis {
            display: block;
            padding: 15px;
            background: #f5f7fb;
            border-radius: 6px;
        }
        .hidden {
            display: none !important;
        }
        /* Header */
        .header {
            display: block;
            text-align: center;
            padding: 40px 30px;
            background: #4038ab;
            color: #fff;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            box-shadow: 0px 3px 10px 0px rgba(14, 16, 77, 0.07);
        }
        .logo {
            display: inline-block;
            vertical-align: top;
            border: 0;
            text-decoration: none !important;
        }
        .logo svg,
        .logo img {
            display: inline-block;
            vertical-align: top;
        }
        /* Body */
        .content {
            display: block;
            padding: 25px 30px;
            background: #fff;
            border-radius: 6px;
            word-break: break-word;
            box-shadow: 0px 3px 10px 0px rgba(14, 16, 77, 0.07);
        }
        .content > *:first-child {
            margin-top: 0 !important;
        }
        .content > *:last-child {
            margin-bottom: 0 !important;
        }
        .header + .content {
            border-top-left-radius: 0px;
            border-top-right-radius: 0px;
        }
        /* Footer */
        .footer {
            display: block;
            font-size: 12px;
            line-height: 15px;
            padding: 20px 30px;
            margin: 0;
            color: #848cae;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>
    <div class="global-wrapper">
        <div class="wrapper">
            <div class="header">
                <a href="<?= Yii::$app->params['appUrl'] ?>" class="logo" title="Presentator logo">
                    <img src="<?= $cidLogo ?>" alt="Presentator logo" height="50"/>
                </a>
            </div>

            <div class="content">
                <?= $content ?>

                <p>
                    <?= Yii::t('mail', 'Best Regards') ?>,<br/>
                    <?= Yii::t('mail', 'Presentator Team') ?>
                </p>
            </div>

            <div class="footer">
                <p>
                    Presentator - Design feedback simplified<br />
                    <a href="mailto:<?= Yii::$app->params['supportEmail'] ?>"><?= Yii::$app->params['supportEmail'] ?></a>
                </p>
            </div>
        </div>
    </div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
