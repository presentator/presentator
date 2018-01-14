<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presentator Installer</title>
    <link href="../css/style.css" rel="stylesheet">
    <link href="../images/favicon.png" rel="shortcut icon" type="image/png">
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
