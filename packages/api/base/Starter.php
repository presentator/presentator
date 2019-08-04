<?php
namespace presentator\api\base;

use Composer\Composer;
use Composer\Script\Event;
use Composer\Util\Filesystem;

/**
 * Basic class with collection of helper static methods that could be
 * used as callbacks during the Composer execution process.
 *
 * This class is intended to be used together with `presentator/starter` package
 * to seamlessly handle project installation/update.
 *
 * List of all available static methods (check the dockblock section of each method for detailed information):
 * @method postCmd()
 * @method appInit()
 * @method appMigrate()
 * @method linkSpa()
 *
 * In order to change some of the default methods behavior, you could define the following properties under the `extra[starter]` key:
 * `spaConfig` - Used by `self::linkSpa()`. SPA config settings that will be injected as inline `APP_CONFIG` js object in the spa application (default to empty array).
 * `spaSrc`    - Used by `self::linkSpa()`. SPA source directory to copy (default to `vendor/presentator/spa/dist/`).
 * `spaDest`   - Used by `self::linkSpa()`. SPA target directory where the `spaSrc` content will be placed (default to `web/`).
 *
 * Example usage in `composer.json`:
 * ```json
 * "scripts": {
 *     "post-install-cmd": [
 *         "presentator\\api\\base\\Starter::postCmd"
 *     ],
 *     "post-update-cmd": [
 *         "presentator\\api\\base\\Starter::linkSpa",
 *         "presentator\\api\\base\\Starter::appInit",
 *         "presentator\\api\\base\\Starter::appMigrate",
 *         ...
 *     ]
 * },
 * "extra": {
 *     "starter": {
 *         "spaSrc": 'some-custom/relative/directory/path/A'
 *         "spaDest": 'some-custom/relative/directory/path/B'
 *         "spaConfig": {
 *             "projectUrl": "https://example.com",
 *             "baseTitle": "My app base title",
 *             ...
 *         }
 *     }
 * }
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Starter
{
    const EXTRA_BASE_KEY       = 'starter';
    const EXTRA_SPA_SRC_KEY    = 'spaSrc';
    const EXTRA_SPA_DEST_KEY   = 'spaDest';
    const EXTRA_SPA_CONFIG_KEY = 'spaConfig';

    /**
     * Generic handler that takes care for all the required
     * operations after app installation/update.
     *
     * Usually called on `post-install-cmd` and/or `post-update-cmd`.
     *
     * @param Event $event
     */
    public static function postCmd(Event $event)
    {
        static::linkSpa($event);

        $workingDir = getcwd();

        $isInited = (
            file_exists($workingDir . '/yii') &&
            file_exists($workingDir . '/config/base-local.php') &&
            file_exists($workingDir . '/web/api/index.php')
        );

        if ($isInited) {
            static::appMigrate($event);
        } else {
            static::appInit($event);

            echo PHP_EOL;
            echo 'Please check the following steps in order to complete the installation:' . PHP_EOL;
            echo '---' . PHP_EOL;
            echo "1. Go to './config/' and edit the environment '-local' config files" . PHP_EOL;
            echo "(usually only './config/base-local.php' and './config/params-local.php' config files need to change)." . PHP_EOL;
            echo "2. Rerun the same command (aka. 'composer install/update')." . PHP_EOL;
        }
    }

    /**
     * Runs app's `init` command in non-interactive mode.
     *
     * @param  Event $event
     */
    public static function appInit(Event $event)
    {
        self::addAndRunScript(
            $event->getComposer(),
            '@php vendor/presentator/api/init --env=Starter --overwrite=n --targetRoot=' . getcwd()
        );
    }

    /**
     * Runs app's `yii/migrate` command in non-interactive mode.
     *
     * @param  Event $event
     */
    public static function appMigrate(Event $event)
    {
        self::addAndRunScript(
            $event->getComposer(),
            '@php yii migrate/up --interactive=0'
        );
    }

    /**
     * Clones presentator-spa inside the public starter package directory of presentator.
     *
     * @param  Event $event
     * @throws \Exception
     */
    public static function linkSpa(Event $event)
    {
        $fs       = new Filesystem;
        $composer = $event->getComposer();
        $extra    = $composer->getPackage()->getExtra();
        $root     = getcwd();

        $spaSrc     = $root . '/' . ($extra[self::EXTRA_BASE_KEY][self::EXTRA_SPA_SRC_KEY]  ?? 'vendor/presentator/spa/dist');
        $spaDest    = $root . '/' . ($extra[self::EXTRA_BASE_KEY][self::EXTRA_SPA_DEST_KEY] ?? 'web');
        $spaConfig  = (array) ($extra[self::EXTRA_BASE_KEY][self::EXTRA_SPA_CONFIG_KEY] ?? []);

        if (!file_exists($spaSrc) || !is_dir($spaSrc)) {
            throw new \Exception($spaSrc . ' is not a directory.');
        }

        // remove old SPA resources
        $fs->removeDirectoryPhp($spaDest . '/spa-resources');

        // renew SPA directory
        if (!$fs->copy($spaSrc, $spaDest)) {
            throw new \Exception('Unable to copy SPA dir inside presentator.');
        }

        // inject custom SPA config
        if (!empty($spaConfig)) {
            self::injectSpaConfig($spaDest . '/index.html', $spaConfig);
        }
    }

    /**
     * Injects `$data` as inline SPA configurations.
     *
     * @param  string $src    Path to the `index.html` file to update.
     * @param  array  [$data] Config array data to set.
     * @return boolean
     */
    protected static function injectSpaConfig(string $src, array $data = []): bool
    {
        if (!file_exists($src)) {
            return false;
        }

        $dom = new \DOMDocument;

        // load html file
        $dom->loadHTML(file_get_contents($src));

        // find the head dom element
        $head = $dom->getElementsByTagName('head')->item(0);

        // create script tag
        $script = $dom->createElement('script', 'window.APP_CONFIG = ' . json_encode($data));

        // append the script to the head dom element
        $head->appendChild($script);

        // save the updated html content to the source file
        $result = file_put_contents($src, $dom->saveHTML());

        return $result !== false ? true : false;
    }

    /**
     * Adds package script with the provided command on the fly and executes it.
     *
     * @see https://getcomposer.org/doc/articles/scripts.md#defining-scripts
     * @param Composer $composer
     * @param string   $command
     */
    protected static function addAndRunScript(Composer $composer, string $command)
    {
        $scripts = $composer->getPackage()->getScripts();
        $script = md5($command) . time();
        $scripts[$script] = [$command];
        $composer->getPackage()->setScripts($scripts);

        $composer->getEventDispatcher()->dispatchScript($script);
    }
}
