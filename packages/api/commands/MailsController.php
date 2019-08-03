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
     * php yii mails/process-comments
     * ```
     *
     * @return integer
     */
    public function actionProcessComments()
    {
        $relsQuery = UserScreenCommentRel::findProcessableQuery();
        $processed = 0;

        $this->stdout('Processing unread screen comments...', Console::FG_YELLOW);
        $this->stdout(PHP_EOL);

        foreach ($relsQuery->each() as $rel) {
            try {
                if (
                    $rel->user->sendUnreadCommentEmail($rel->screenComment) &&
                    $rel->markAsProcessed()
                ) {
                    $processed++;
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
