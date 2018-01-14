<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use common\components\helpers\CFileHelper;
use common\components\helpers\CArrayHelper;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UpdateController extends Controller
{
    /**
     * @inheritdoc
     */
    public $color = true;

    /**
     * @example
     * ```bash
     * php yii update
     * ```
     *
     * @return integer
     */
    public function actionIndex()
    {
        $extractPath = dirname(Yii::$app->basePath);
        $archivePath = rtrim(Yii::$app->basePath, '/') . '/update_' . time() . '.zip';

        // Check if there is a newer version available
        // -----------------------------------------------------------
        $info = $this->getLatestVersionInfo();
        $needToUpdate = CArrayHelper::getValue($info, 'update', false);
        if (!$needToUpdate) {
            $this->stdout('No new versions found. Your system is up-to-date.', Console::FG_GREEN);
            $this->stdout(PHP_EOL);

            return self::EXIT_CODE_NORMAL;
        }

        // Download archive
        // -----------------------------------------------------------
        $this->stdout('Downloading latest version archive...', Console::FG_YELLOW);
        $this->stdout(PHP_EOL);

        $this->downloadLatestVersionArchive($archivePath);

        $this->stdout('Successfully downloaded latest version archive.', Console::FG_GREEN);
        $this->stdout(PHP_EOL . PHP_EOL);

        // Unzip archive
        // -----------------------------------------------------------
        $this->stdout('Extracting the downloaded archive...', Console::FG_YELLOW);
        $this->stdout(PHP_EOL);
        $zip = new \ZipArchive;
        $res = $zip->open($archivePath);

        if ($res !== true) {
            $this->stdout('An error occured while unzipping the archive - it seems to be either corrupted or invalid.', Console::BG_RED);
            $this->stdout(PHP_EOL);

            @unlink($archivePath);

            return self::EXIT_CODE_ERROR;
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // delete the archive file
        @unlink($archivePath);

        // try to delete the installer dir
        try {
            CFileHelper::removeDirectory($extractPath . '/app/web/install');
        } catch (\Exception $e) {
        }

        $this->stdout('Successfully extracted the archive files.', Console::FG_GREEN);
        $this->stdout(PHP_EOL . PHP_EOL);

        // Apply migrations
        // -----------------------------------------------------------
        $this->stdout('Applying migration scripts...', Console::FG_YELLOW);
        $this->stdout(PHP_EOL);
        $migrationResult = Yii::$app->runAction('migrate/up', ['interactive' => 0]);
        if ($migrationResult === self::EXIT_CODE_ERROR) {
            $this->stdout('An error occured while applying the migration scripts.', Console::BG_RED);
            $this->stdout(PHP_EOL);

            return self::EXIT_CODE_ERROR;
        }

        $this->stdout('Successfully applied migration scripts.', Console::FG_GREEN);
        $this->stdout(PHP_EOL . PHP_EOL);

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * Checks whether the provided version is the latest one and
     * returns info about the latest available app version.
     *
     * @return array
     */
    protected function getLatestVersionInfo()
    {
        $currentVersion = Yii::$app->params['currentVersion'];

        $url = rtrim(Yii::$app->params['versionCheckUrl'], '/');
        $url = sprintf(
            '%s%sversion=%s',
            $url,
            (strpos($url, '?') !== false ? '&' : '?'),
            $currentVersion
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!empty($result)) {
            return (array)json_decode($result, true);
        }

        return [];
    }

    /**
     * Downloads the latest available app archive and save it to `$destination`.
     *
     * @param  string $destination Destination archive file path.
     * @return boolean
     */
    protected function downloadLatestVersionArchive($destination)
    {
        // get latest archive
        $ch = curl_init();
        $url = Yii::$app->params['latestVersionArchiveUrl'];
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec ($ch);
        curl_close ($ch);

        // save as zip archive
        $file = fopen($destination, "w+");

        if ($file === false || fwrite($file, $result) === false) {
            return false;
        }

        fclose($file);

        return true;
    }
}
