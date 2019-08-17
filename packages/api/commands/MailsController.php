<?php
namespace presentator\api\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use presentator\api\models\UserScreenCommentRel;

/**
 * Manages bulk email sending commands.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class MailsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $color = true;

    /**
     * Processes unread screen comments and sends an email to the related users.
     *
     * Example usage:
     * ```bash
     * php yii mails/process-comments [sleepBatch] [sleepDuration] [createdThreshold]
     * ```
     *
     * @param  integer [$sleepBatch]       The number of emails to process before sleep to occur (default to 5).
     * @param  integer [$sleepDuration]    Sleep interval duration in seconds (default to 2).
     * @param  integer [$createdThreshold] Process only comments that are created before the provided interval in seconds (default to 900 /15 minutes/).
     * @return integer
     */
    public function actionProcessComments(int $sleepBatch = 5, int $sleepDuration = 2, int $createdThreshold = 900)
    {
        $relsQuery = UserScreenCommentRel::findProcessableQuery(date('Y-m-d H:i:s', strtotime('- ' . $createdThreshold . ' seconds')));
        $processed = 0;

        $this->stdout('Processing unread screen comments...', Console::FG_YELLOW);
        $this->stdout(PHP_EOL);

        foreach ($relsQuery->each() as $i => $rel) {
            try {
                if (
                    $rel->user->sendUnreadCommentEmail($rel->screenComment) &&
                    $rel->markAsProcessed()
                ) {
                    $processed++;
                }

                if (($i + 1) % $sleepBatch == 0) {
                    sleep($sleepDuration);
                }
            } catch (\Exception | \Throwable $e) {
                Yii::error($e->getMessage());
            }
        }

        $this->stdout('Processed: ' . $processed, Console::BG_GREEN, Console::FG_BLACK);
        $this->stdout(PHP_EOL);

        return self::EXIT_CODE_NORMAL;
    }
}
