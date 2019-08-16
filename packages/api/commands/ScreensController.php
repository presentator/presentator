<?php
namespace presentator\api\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use presentator\api\models\Screen;

/**
 * Provides helper functions related to the project screens (eg. thumbs generation).
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $color = true;

    /**
     * Regenerates all project screen thumbs.
     *
     * Example usage:
     * ```bash
     * php yii screens/generate-thumbs
     * ```
     *
     * @return integer
     */
    public function actionGenerateThumbs()
    {
        $thumbsCount = 0;
        foreach (Screen::find()->each() as $screen) {
            $this->stdout('Creating thumbs for screen ' . $screen->filePath . '...', Console::FG_YELLOW);
            $this->stdout(PHP_EOL);

            $thumbsCount += $screen->createThumbs();
        }

        $this->stdout('Successfully created ' . $thumbsCount . ' thumb(s).', Console::BG_GREEN);
        $this->stdout(PHP_EOL);

        return self::EXIT_CODE_NORMAL;
    }
}
