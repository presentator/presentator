<?php
namespace console\tests\functional;

use Yii;
use console\tests\FunctionalTester;
use console\controllers\MailsController;
use common\tests\fixtures\MailQueueFixture;
use common\models\MailQueue;

/**
 * MailsController functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class MailsCest
{
    /**
     * @inheritdoc
     */
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'mailQueue' => [
                'class'    => MailQueueFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/mail_queue.php'),
            ],
        ]);
    }

    /* ===============================================================
     * `MailsController::actionProcess()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function testProcessWithPurge(FunctionalTester $I)
    {
        $I->wantTo('Successfully process 2 MailQueue records and delete them on success');

        Yii::$app->params['purgeSentMails'] = true;

        $oldMailQueueCount = MailQueue::find()->count();

        $controller = new MailsController('mails', Yii::$app);
        $result     = $controller->runAction('process', [2, 0]);

        $newMailQueueCount = MailQueue::find()->count();

        $I->seeEmailIsSent(2);
        verify('Action should complete normally', $result)->equals(MailsController::EXIT_CODE_NORMAL);
        verify('Processed mails should be deleted', $oldMailQueueCount - $newMailQueueCount)->equals(2);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testProcessWithoutPurge(FunctionalTester $I)
    {
        $I->wantTo('Successfully process MailQueue records and mark them as sent on success');

        Yii::$app->params['purgeSentMails'] = false;

        $oldMailQueueCount = MailQueue::find()->count();
        $mailsToProcess    = MailQueue::findAll(['status' => MailQueue::STATUS_PENDING]);

        $controller = new MailsController('mails', Yii::$app);
        $result     = $controller->runAction('process', [count($mailsToProcess), 0]);

        $newMailQueueCount = MailQueue::find()->count();

        $I->seeEmailIsSent(count($mailsToProcess));
        verify('Action should complete normally', $result)->equals(MailsController::EXIT_CODE_NORMAL);
        verify('Processed mails should not be deleted', $oldMailQueueCount)->equals($newMailQueueCount);
        foreach ($mailsToProcess as $mail) {
            $mail->refresh();

            verify('Each processed mail should be marked as sent', $mail->status)->equals(MailQueue::STATUS_SENT);
        }
    }

    /**
     * @param FunctionalTester $I
     */
    public function testProcessEmpty(FunctionalTester $I)
    {
        $I->wantTo('Run the action when there are not any pending MailQueue records');

        // mark all pending mails as sent to simulate emty queue
        $pendingMails = MailQueue::findAll(['status' => MailQueue::STATUS_PENDING]);
        foreach ($pendingMails as $mail) {
            $mail->markAsSent();
        }

        $controller = new MailsController('mails', Yii::$app);
        $result     = $controller->runAction('process', [10, 0]);

        $I->dontSeeEmailIsSent();
        verify('Action should complete normally', $result)->equals(MailsController::EXIT_CODE_NORMAL);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testProcessSleep(FunctionalTester $I)
    {
        $I->wantTo('test action sleep argument');

        $beforeActionTime = microtime(true);
        $controller       = new MailsController('mails', Yii::$app);
        $result           = $controller->runAction('process', [2, 500]);
        $afterActionTime  = microtime(true);

        $I->seeEmailIsSent(2);
        verify('Action should complete normally', $result)->equals(MailsController::EXIT_CODE_NORMAL);
        verify('Action execution should take atleast 1sec (mails * miliseconds)', $afterActionTime - $beforeActionTime)->greaterOrEquals(1);
    }
}
