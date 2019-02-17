<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presentator Installer</title>
    <link href="../css/style.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="../images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon-16x16.png">
    <link rel="manifest" href="../images/site.webmanifest">
    <link rel="mask-icon" href="../images/safari-pinned-tab.svg" color="#322a55">
    <link rel="shortcut icon" href="../images/favicon.ico">
    <meta name="msapplication-TileColor" content="#322a55">
    <meta name="msapplication-config" content="../images/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
</head>
<body>
    <div id="global_wrapper" class="global-wrapper padded">
        <div class="panel panel-lg padded m-b-40">
            <div class="alert alert-secondary text-center padded m-b-30" style="background-image: url('../images/pattern_white.png')">
                <img src="../images/logo_large_white.png" alt="Presentator">
                <h1 class="m-t-15">Presentator installer</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger m-b-30">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach ?>
                </div>
            <?php endif; ?>

            <?php if ($installSuccess === true): ?>
                <div class="alert alert-success">
                    <p>The installation process was completed successfully and you can now continue to your <a href="../"><strong>application homepage</strong></a>.</p>
                </div>
            <?php else: ?>
                <?php require_once('./_installer_form.php') ?>
            <?php endif; ?>

            <hr>

            <div class="content text-center">
                <p>
                    <small>Don't worry. You can always change later the application settings manually by editing <br> <em>common/config/<strong>main-local.php</strong></em> and <em>common/config/<strong>params-local.php</strong></em>.</small>
                </p>
                <p>
                    <small>To see all available application configurations and parameters check <br> <em>common/config/<strong>main.php</strong></em> and <em>common/config/<strong>params.php</strong></em>.</small>
                </p>
                <p>
                    <small>If you need any further help don't hesitate to contact us at <a href="mailto:support@presentator.io">support@presentator.io</a>.</small>
                </p>
            </div>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/pr.js"></script>
    <script src="../js/selectify.js"></script>
    <script src="../js/app.js"></script>
    <script>
        $(function () {
            var baseUrl = (window.location .protocol + "//" + window.location.host);

            $('#params_public_url').val(baseUrl);
        });
    </script>
</body>
</html>
