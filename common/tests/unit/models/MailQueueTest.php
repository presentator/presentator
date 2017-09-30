<?php
namespace common\tests\unit\models;

use Yii;
use common\models\MailQueue;
use common\tests\fixtures\MailQueueFixture;
use common\components\helpers\CStringHelper;

/**
 * MailQueue AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class MailQueueTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    public function _before()
    {
        $this->tester->haveFixtures([
            'mailQueue' => [
                'class'    => MailQueueFixture::className(),
                'dataFile' => codecept_data_dir() . 'mail_queue.php',
            ],
        ]);
    }

    /**
     * `MailQueue::markAsSent()` method test.
     */
    public function testMarkAsSent()
    {
        $this->specify('Mark pending mail model as sent', function() {
            $model = MailQueue::findOne(['id' => 1001, 'status' => MailQueue::STATUS_PENDING]);

            $result = $model->markAsSent();

            verify('Method should succeed', $result)->true();
            verify('Model status should be updated', $model->status)->equals(MailQueue::STATUS_SENT);
        });

        $this->specify('Try to mark as sent already sent mail model', function() {
            $model = MailQueue::findOne(['id' => 1002, 'status' => MailQueue::STATUS_SENT]);

            $result = $model->markAsSent();

            verify('Method should succeed', $result)->true();
            verify('Model status should not be updated', $model->status)->equals(MailQueue::STATUS_SENT);
        });
    }

    /**
     * `MailQueue::send()` method test.
     */
    public function testSend()
    {
        $this->specify('Send mail model with no from email', function() {
            $model = MailQueue::findOne(1001);

            $result = $model->send();

            $this->tester->seeEmailIsSent(1);
            $this->checkMailQueueMessage($model, $this->tester->grabLastSentEmail()->getSwiftMessage());
        });

        $this->specify('Send mail model with defined from, cc and bcc emails', function() {
            $model = MailQueue::findOne(1003);

            $result = $model->send();

            $this->tester->seeEmailIsSent();
            $this->checkMailQueueMessage($model, $this->tester->grabLastSentEmail()->getSwiftMessage());
        });
    }

    /**
     * `MailQueue::process()` method WITH purge option test.
     */
    public function testProcessWithPurge()
    {
        $this->specify('Purge already sent email', function() {
            $model = MailQueue::findOne(['id' => 1002, 'status' => MailQueue::STATUS_SENT]);

            $result = $model->process();

            $this->tester->dontSeeEmailIsSent();
            verify('Process method should succeed', $result)->true();
            verify('Model should be deleted', MailQueue::findOne($model->id))->null();
        });

        $this->specify('Purge and resend already sent email', function() {
            $model = MailQueue::findOne(['id' => 1004, 'status' => MailQueue::STATUS_SENT]);

            $result = $model->process(true, true);

            $this->tester->seeEmailIsSent();
            verify('Process method should succeed', $result)->true();
            verify('Model should be deleted', MailQueue::findOne($model->id))->null();
            $this->checkMailQueueMessage($model, $this->tester->grabLastSentEmail()->getSwiftMessage());
        });

        $this->specify('Process pending mail record and purge on success', function() {
            $model = MailQueue::findOne(['id' => 1001, 'status' => MailQueue::STATUS_PENDING]);

            $result = $model->process(true, true);

            $this->tester->seeEmailIsSent();
            verify('Process method should succeed', $result)->true();
            verify('Model should be deleted', MailQueue::findOne($model->id))->null();
            $this->checkMailQueueMessage($model, $this->tester->grabLastSentEmail()->getSwiftMessage());
        });
    }

    /**
     * `MailQueue::process()` method WITHOUT purge option test.
     */
    public function testProcessWithoutPurge()
    {
        $this->specify('Process already sent email (do no nothing)', function() {
            $model = MailQueue::findOne(['id' => 1002, 'status' => MailQueue::STATUS_SENT]);

            $result = $model->process(false);

            $afterProcessModel = MailQueue::findOne(['id' => 1004, 'status' => MailQueue::STATUS_SENT]);

            $this->tester->dontSeeEmailIsSent();
            verify('Process method should succeed', $result)->true();
            verify('Model should not be deleted', $afterProcessModel)->notEmpty();
            verify('Model status should not be changed', $afterProcessModel->status)->equals($model->status);
        });

        $this->specify('Process already sent email with resend option', function() {
            $model = MailQueue::findOne(['id' => 1004, 'status' => MailQueue::STATUS_SENT]);

            $result = $model->process(false, true);

            $afterProcessModel = MailQueue::findOne(['id' => 1004, 'status' => MailQueue::STATUS_SENT]);

            $this->tester->seeEmailIsSent();
            verify('Process method should succeed', $result)->true();
            verify('Model should not be deleted', $afterProcessModel)->notEmpty();
            verify('Model status should not be changed', $afterProcessModel->status)->equals($model->status);
            $this->checkMailQueueMessage($model, $this->tester->grabLastSentEmail()->getSwiftMessage());
        });

        $this->specify('Process pending mail record and mark as sent on success', function() {
            $model = MailQueue::findOne(['id' => 1001, 'status' => MailQueue::STATUS_PENDING]);

            $result = $model->process(true, true);

            $afterProcessModel = MailQueue::findOne(['id' => 1004, 'status' => MailQueue::STATUS_SENT]);

            $this->tester->seeEmailIsSent();
            verify('Process method should succeed', $result)->true();
            verify('Model should not be deleted', $afterProcessModel)->notEmpty();
            verify('Model status should be changed', $afterProcessModel->status)->equals(MailQueue::STATUS_SENT);
            $this->checkMailQueueMessage($model, $this->tester->grabLastSentEmail()->getSwiftMessage());
        });
    }

    /**
     * `MailQueue::findMails()` method test.
     */
    public function testFindMails()
    {
        $this->specify('Find pending mail models', function() {
            $models = MailQueue::findMails();

            verify('Models count should match', $models)->count(2);

            $prevTimestamp = null;
            foreach ($models as $model) {
                verify('Each model should be valid MailQueue model', $model)->isInstanceOf(MailQueue::className());
                verify('Each model status should be pending', $model->status)->equals(MailQueue::STATUS_PENDING);
                verify('Models should be sorted in ASC order', $model->createdAt)->greaterOrEquals($prevTimestamp);

                $prevTimestamp = $model->createdAt;
            }
        });

        $this->specify('Find already sent mail models (with limit)', function() {
            $models = MailQueue::findMails(MailQueue::STATUS_SENT, 1);

            verify('Models count should match', $models)->count(1);

            $prevTimestamp = null;
            foreach ($models as $model) {
                verify('Each model should be valid MailQueue model', $model)->isInstanceOf(MailQueue::className());
                verify('Each model status should be sent', $model->status)->equals(MailQueue::STATUS_SENT);
                verify('Models should be sorted in ASC order', $model->createdAt)->greaterOrEquals($prevTimestamp);

                $prevTimestamp = $model->createdAt;
            }
        });
    }

    /**
     * Helper method to check whether an email message has valid mail queue data.
     *
     * @param integer $model   MailQueue model to check
     * @param string  $message
     */
    protected function checkMailQueueMessage(MailQueue $model, $message)
    {
        $to    = CStringHelper::parseAddresses($model->to);
        $from  = CStringHelper::parseAddresses($model->from);
        $cc    = CStringHelper::parseAddresses($model->cc);
        $bcc   = CStringHelper::parseAddresses($model->bcc);

        foreach ($to as $email => $name) {
            verify('Mail sender should match', $message->getTo())->hasKey($email);
            verify('Mail sender name should match', $message->getTo()[$email])->equals($name);
        }

        if (!$from) {
            verify('Mail sender should match', $message->getFrom())->hasKey(Yii::$app->params['noreplyEmail']);
            verify('Mail sender name should match', $message->getFrom()[Yii::$app->params['noreplyEmail']])->equals('Presentator');
        } else {
            foreach ($from as $email => $name) {
                verify('Mail sender should match', $message->getFrom())->hasKey($email);
                verify('Mail sender name should match', $message->getFrom()[$email])->equals($name);
            }
        }

        if (!$cc) {
            verify('Mail cc should not be set', $message->getCc())->isEmpty();
        } else {
            foreach ($cc as $email => $name) {
                verify('Mail cc should match', $message->getCc())->hasKey($email);
                verify('Mail cc name should match', $message->getCc()[$email])->equals($name);
            }
        }

        if (!$bcc) {
            verify('Mail bcc should not be set', $message->getBcc())->isEmpty();
        } else {
            foreach ($bcc as $email => $name) {
                verify('Mail bcc should match', $message->getBcc())->hasKey($email);
                verify('Mail bcc name should match', $message->getBcc()[$email])->equals($name);
            }
        }

        verify('Mail subject should match', $message->getSubject())->equals($model->subject);
        verify('Mail body should match', $message->getBody())->equals($model->body);
    }
}
