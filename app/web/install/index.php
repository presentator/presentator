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
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

require_once('./_installer_view.php');

// try to delete the installer directory on success
if ($installSuccess) {
    removeDir(__DIR__);
}

// -------------------------------------------------------------------
// Helpers and handlers
// -------------------------------------------------------------------

/**
 * @param  array $data
 * @return boolean
 */
function install($data) {
    global $rootDir, $mainLocalPath, $paramsLocalPath;

    $phpPath = !empty($data['phpPath']) ? $data['phpPath'] : 'php';

    // Init application
    exec(sprintf('%s %s/init --env=Production --overwrite=y', $phpPath, $rootDir), $initOutput, $initResult);
    if ($initResult !== 0) {
        throw new Exception('Oops! Something went wrong while initializing the application.<br>' . implode('<br>', $initOutput));
    }

    // Save local settings
    $main   = resolveMainComponents($data);
    $params = resolveParams($data);
    $settingsResult = saveArrayToFile($paramsLocalPath, $params);
    $settingsResult = $settingsResult && saveArrayToFile($mainLocalPath, $main);
    if (!$settingsResult) {
        @unlink($mainLocalPath);
        @unlink($paramsLocalPath);
        throw new Exception('Oops! Something went wrong while trying to save the configuration files.<br>');
    }

    // Apply migrations
    exec(sprintf('%s %s/yii migrate --interactive=0', $phpPath, $rootDir), $migrationsOutput, $migrationsResult);
    if ($migrationsResult !== 0) {
        @unlink($mainLocalPath);
        @unlink($paramsLocalPath);
        throw new Exception('Oops! Something went wrong while applying the migration scripts (probably invalid DB settings).');
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

    $boolParams = ['useMailQueue', 'purgeSentMails', 'showCredits', 'fuzzyUsersSearch'];

    if (isset($data['params']) && is_array($data['params'])) {
        if (empty($data['params']['publicUrl'])) {
            $data['params']['publicUrl'] = $baseUrl;
        }

        if (!empty($data['params']['allowedRegistrationDomains'])) {
            $domains = explode(',', $data['params']['allowedRegistrationDomains']);
            $data['params']['allowedRegistrationDomains'] = [];
            foreach ($domains as $domain) {
                $data['params']['allowedRegistrationDomains'][] = trim($domain);
            }
        } else {
            $data['params']['allowedRegistrationDomains'] = [];
        }

        if (empty($data['params']['recaptcha']['secretKey'])) {
            unset($data['params']['recaptcha']);
        }

        // normalize boolean params
        foreach ($boolParams as $param) {
            if (!isset($data['params'][$param])) {
                continue;
            }

            if ($data['params'][$param]) {
                $data['params'][$param] = true;
            } else {
                $data['params'][$param] = false;
            }
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

/**
 * Recursively remove directory and all its files.
 *
 * @param  string $dir Path to the directory to remove.
 * @return boolean
 */
function removeDir($dir)
{
    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        is_dir("$dir/$file") ? delTree("$dir/$file") : unlink("$dir/$file");
    }

    return rmdir($dir);
}

/**
 * Simple and very robust "prettier" alternative to `var_export`.
 *
 * @param  array   $data  Array to export.
 * @param  integer $level Recursion flag.
 * @return string
 */
function simpleArrayExport(array $data, $level = 1) {
    $result = "";

    for ($i = 0; $i < $level - 1; $i++) {
        $result .= "\t";
    }

    $result = "[\n";

    foreach ($data as $key => $value) {
        for ($i = 0; $i < $level; $i++) {
            $result .= "\t";
        }
        $result .= "'$key' => ";

        if (is_array($value)) {
            $result .= (simpleArrayExport($value, $level + 1) . ",\n");
        } elseif (is_string($value)) {
            $result .= "'$value',\n";
        } elseif ($value === true) {
            $result .= "true,\n";
        } elseif ($value === false) {
            $result .= "false,\n";
        } else {
            $result .= "$value,\n";
        }
    }

    for ($i = 0; $i < $level - 1; $i++) {
        $result .= "\t";
    }

    $result .= "]";

    return $result;
}

/**
 * Saves array in a file (with return statement).
 *
 * @param  string $file
 * @param  array  $data
 * @return boolean
 */
function saveArrayToFile($file, array $data) {
    $content = "<?php\nreturn" . simpleArrayExport($data) . ";\n";

    return file_put_contents($file, $content) !== false;
}
