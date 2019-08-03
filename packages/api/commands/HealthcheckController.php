<?php
namespace presentator\api\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Provides application health checks.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HealthcheckController extends Controller
{
    /**
     * @inheritdoc
     */
    public $color = true;

    /**
     * Checks whether the DB connection is established.
     *
     * @example
     * ```bash
     * php yii healthcheck/db
     * ```
     *
     * @return integer
     */
    public function actionDb()
    {
        try {
            Yii::$app->db->open();

            if (Yii::$app->db->isActive) {
                $this->stdout('DB connection is established.', Console::BG_GREEN);
                $this->stdout(PHP_EOL);

                Yii::$app->db->close();

                return self::EXIT_CODE_NORMAL;
            }
        } catch (\Exception $e) {
            $this->stdout($e->getMessage(), Console::FG_RED);
            $this->stdout(PHP_EOL);
        }

        $this->stdout('Cannot connect to DB.', Console::BG_RED);
        $this->stdout(PHP_EOL);

        return self::EXIT_CODE_ERROR;
    }
}
