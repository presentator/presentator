<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use common\models\MailQueue;

class MailsController extends Controller
{
    /**
     * @inheritdoc
     */
    public $color = true;

    /**
     * Process pending MailQueue records.
     *
     * @example
     * ```bash
     * # default mails limit
     * php yii mails/process
     *
     * # custom mails limit
     * php yii mails/process 50
     * ```
     *
     * @param  integer $limit Total number of mails to process.
     * @param  integer $sleep Interval in seconds to wait before processing new mail.
     * @return integer
     */
    public function actionProcess($limit = 15, $sleep = 1)
    {
        $mails      = MailQueue::findMails(MailQueue::STATUS_PENDING, (int) $limit);
        $totalMails = count($mails);

        if ($totalMails === 0) {
            $this->stdout('No mails to process.' . PHP_EOL, Console::FG_GREEN);
        } else {
            $successMails = 0;

            echo PHP_EOL;
            $this->stdout('Mails to process: ' . $totalMails, Console::BOLD);
            echo PHP_EOL . PHP_EOL;

            Console::startProgress(0, $totalMails);
            foreach ($mails as $i => $mail) {
                if ($mail->process(Yii::$app->params['purgeSentMails'])) {
                    $successMails++;
                }

                if ($sleep > 0) {
                    sleep($sleep);
                }

                Console::updateProgress($i+1, $totalMails);
            }
            Console::endProgress();

            echo PHP_EOL;
            $this->stdout('Successfully processed mails: ' . $successMails, Console::BG_GREEN, Console::FG_BLACK);
            echo PHP_EOL . PHP_EOL;
        }

        return self::EXIT_CODE_NORMAL;
    }
}
