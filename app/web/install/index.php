<?php
/**
 * -------------------------------------------------------------------
 * Presentator Web Installer.
 *
 * For more info and instructions make sure to check https://github.com/ganigeorgiev/presentator/blob/master/docs/start-installation.md.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 * -------------------------------------------------------------------
 */

$errors          = [];
$installSuccess  = null;
$baseUrl         = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$rootDir         = dirname(dirname(dirname(__DIR__)));
$mainLocalPath   = $rootDir . '/common/config/main-local.php';
$paramsLocalPath = $rootDir . '/common/config/params-local.php';

if (isAlreadyInstalled()) {
    header('Location: ' . $baseUrl);
    exit;
}

if (!is_writable(__DIR__)) {
    $errors[] = 'The install directory does not have write permissions. They are required in order to auto delete all installer files after successfull installation.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $installSuccess = false;

    try {
        $installSuccess = install($_POST);

        if ($installSuccess) {
            if (!unlink(__DIR__)) {
                $errors[] = "Warning! The install folder was not able to be deleted automatically. For security reasons it is recommended to delete the folder manually!";
            }
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

require_once('./_installer_view.php');

// -------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------

/**
 * @param  array $data
 * @return boolean
 */
function install($data) {
    global $rootDir, $mainLocalPath, $paramsLocalPath;

    $phpPath = !empty($data['phpPath']) ? $data['phpPath'] : 'php';

    // Init application
    exec(sprintf('%s %s/init --env=Production --overwrite=y', $phpPath, $rootDir), $output, $initResult);
    if ($initResult !== 0) {
        throw new Exception('Oops! Something when wrong while initializing the application.');
    }

    // Save local settings
    $main   = resolveMainComponents($data);
    $params = resolveParams($data);
    $settingsResult = file_put_contents($paramsLocalPath, '<?php' . PHP_EOL . var_export($params, true)) !== false;
    $settingsResult = $settingsResult && (file_put_contents($mainLocalPath, '<?php' . PHP_EOL . var_export($main, true)) !== false);
    if (!$settingsResult) {
        throw new Exception('Oops! Something when wrong while trying to save the configuration files.');
    }

    // Apply migrations
    exec(sprintf('%s %s/yii migrate --interactive=0', $phpPath, $rootDir), $output, $migrationsResult);
    if ($migrationsResult !== 0) {
        throw new Exception('Oops! Something when wrong while applying migration scripts.');
    }

    return true;
}

/**
 * @return boolean
 */
function isAlreadyInstalled()
{
    global $rootDir, $mainLocalPath, $paramsLocalPath;

    return (
        file_exists($rootDir . '/app/web/index.php') &&
        file_exists($mainLocalPath) &&
        file_exists($paramsLocalPath)
    );
}

/**
 * Resolves and returns global app parameters.
 *
 * @param  array $data
 * @return array
 */
function resolveParams(array $data)
{
    global $baseUrl, $paramsLocalPath;

    if (isset($data['params']) && is_array($data['params'])) {
        if (empty($data['params']['publicUrl'])) {
            $data['params']['publicUrl'] = $baseUrl;
        }

        $initParams = [];
        if (file_exists($paramsLocalPath)) {
            $initParams = (array) include($paramsLocalPath);
        }

        return array_merge($initParams, $data['params']);
    }

    return [];
}

/**
 * Resolves and returns main components configurations.
 *
 * @param  array $data
 * @return array
 */
function resolveMainComponents(array $data)
{
    global $mainLocalPath;

    $main = [];

    $main['components']['db'] = [
        'class' => 'yii\db\Connection',
        'dsn' => sprintf(
            "%s:host=%s;dbname=%s",
            (!empty($data['db']['driver']) ? $data['db']['driver'] : ''),
            (!empty($data['db']['host']) ? $data['db']['host'] : ''),
            (!empty($data['db']['name']) ? $data['db']['name'] : '')
        ),
        'username' => !empty($data['db']['user']) ? $data['db']['user'] : '',
        'password' => !empty($data['db']['password']) ? $data['db']['password'] : '',
        'charset'  => 'utf8',
    ];

    $main['components']['mailer'] = [
        'class' => 'common\components\swiftmailer\CMailer',
        'viewPath' => '@common/mail',
        'useFileTransport' => false,
        'transport' => [
            'class'      => 'Swift_SmtpTransport',
            'host'       => !empty($data['mailer']['host']) ? $data['mailer']['host'] : '',
            'username'   => !empty($data['mailer']['user']) ? $data['mailer']['user'] : '',
            'password'   => !empty($data['mailer']['password']) ? $data['mailer']['password'] : '',
            'port'       => !empty($data['mailer']['port']) ? $data['mailer']['port'] : '465',
            'encryption' => !empty($data['mailer']['encryption']) ? $data['mailer']['encryption'] : 'ssl',
        ],
    ];

    $initMain = [];
    if (file_exists($mainLocalPath)) {
        $initMain = (array) include($mainLocalPath);
    }

    return array_merge($initMain, $main);
}
