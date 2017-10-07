<?php
namespace common\tests\unit\models;

use Yii;
use Swift_Message;
use common\models\MailQueue;
use common\tests\fixtures\MailQueueFixture;
use common\components\helpers\EmailHelper;

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
     * Helper method to check whether an email message has valid mail queue data.
     *
     * @param MailQueue     $model   MailQueue model to check
     * @param Swift_Message $message Message instance
     */
    protected function checkMailQueueMessage(MailQueue $model, Swift_Message $message)
    {
        $to    = EmailHelper::stringToArray($model->to);
        $from  = EmailHelper::stringToArray($model->from);
        $cc    = EmailHelper::stringToArray($model->cc);
        $bcc   = EmailHelper::stringToArray($model->bcc);

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

        $body         = $message->getBody();
        $bodyChildren = $message->getChildren();
        if (empty($body) && !empty($bodyChildren)) {
            $parts = [];

            foreach ($bodyChildren as $child) {
                $parts[$child->getContentType()] = $child->getBody();
            }

            if (!empty($parts['text/html'])) {
                $body = $parts['text/html'];
            } elseif (!empty($parts['text/plain'])) {
                $body = $parts['text/plain'];
            }
        }

        verify('Mail body should match', $body)->equals($model->body);
    }

    /**
     * Test model validation rules.
     */
    public function testValidationRules()
    {
        $this->specify('Validate required fields', function() {
            $model = new MailQueue();

            $result = $model->save();

            verify('Save method should not succeed', $result)->false();
            verify('From error message should not be set', $model->errors)->hasntKey('from');
            verify('Cc error message should not be set', $model->errors)->hasntKey('cc');
            verify('Bcc error message should not be set', $model->errors)->hasntKey('bcc');
            verify('Status error message should not be set', $model->errors)->hasntKey('status');
            verify('To error message should be set', $model->errors)->hasKey('to');
            verify('Subject error message should be set', $model->errors)->hasKey('subject');
            verify('Body error message should be set', $model->errors)->hasKey('body');
        });

        $this->specify('Validate field values format', function() {
            $model          = new MailQueue();
            $model->to      = 'invalid_email, test@presentator.io';
            $model->from    = '123456@presentator';
            $model->cc      = 'Lorem Ipsum test@presentator.io';
            $model->bcc     = 'John Doe <test@presentator.io>, invalid_email';
            $model->status  = -1;
            $model->subject = 'test subject';
            $model->body    = 'test body';

            $result = $model->save();

            verify('Save method should not succeed', $result)->false();
            verify('From error message should be set', $model->errors)->hasKey('from');
            verify('Cc error message should be set', $model->errors)->hasKey('cc');
            verify('Bcc error message should be set', $model->errors)->hasKey('bcc');
            verify('Status error message should be set', $model->errors)->hasKey('status');
            verify('To error message should be set', $model->errors)->hasKey('to');
            verify('Subject error message should not be set', $model->errors)->hasntKey('subject');
            verify('Body error message should not be set', $model->errors)->hasntKey('body');
        });

        $this->specify('Success create attempt', function() {
            $model          = new MailQueue();
            $model->to      = 'test@presentator.io';
            $model->from    = 'test1@presentator.io, John Doe <test2@presentator.io>';
            $model->cc      = 'test3@presentator.io';
            $model->bcc     = 'Lorem Ipsum <test4@presentator.io>';
            $model->status  = MailQueue::STATUS_PENDING;
            $model->subject = 'test subject';
            $model->body    = 'test body';

            $result = $model->save();

            verify('Save method should succeed', $result)->true();
            verify('Model errors should not be set', $model->errors)->isEmpty();
        });
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
     * `MailQueue::createByMessage()` method test.
     */
    public function testCreateByMessage()
    {
        $this->specify('Succcessfully create MailQueue record from a Message instance with text body', function() {
            $message = Yii::$app->mailer->compose()
                ->setFrom(['test@presentator.io' => null])
                ->setTo([
                    'test1@presentator.io' => 'John Doe',
                    'test2@presentator.io' => null,
                    'test3@presentator.io' => 'Lorem Ipsum',
                ])
                ->setSubject('Test text body subject')
                ->setTextBody('Test text body')
            ;

            $beforeMailQueueCount = MailQueue::find()->count();
            $result               = MailQueue::createByMessage($message);
            $afterMailQueueCount  = MailQueue::find()->count();

            verify('The create method should succeed', $result)->true();
            verify('New MailQueue record should be created', $afterMailQueueCount)->equals($beforeMailQueueCount + 1);

            $model = MailQueue::findOne(['subject' => 'Test text body subject']);
            $this->checkMailQueueMessage($model, $message->getSwiftMessage());
        });

        $this->specify('Succcessfully create MailQueue record from a Message instance with html body', function() {
            $message = Yii::$app->mailer->compose()
                ->setFrom([
                    'test@presentator.io' => null,
                ])
                ->setTo([
                    'test1@presentator.io' => null,
                ])
                ->setCc([
                    'test2@presentator.io' => 'Lorem Ipsum',
                ])
                ->setBcc([
                    'test3@presentator.io' => 'John Doe',
                ])
                ->setSubject('Test html body subject')
                ->setHtmlBody('<p>Test html body</p>')
            ;

            $beforeMailQueueCount = MailQueue::find()->count();
            $result               = MailQueue::createByMessage($message);
            $afterMailQueueCount  = MailQueue::find()->count();

            verify('The create method should succeed', $result)->true();
            verify('New MailQueue record should be created', $afterMailQueueCount)->equals($beforeMailQueueCount + 1);

            $model = MailQueue::findOne(['subject' => 'Test html body subject']);
            $this->checkMailQueueMessage($model, $message->getSwiftMessage());
        });

        $this->specify('Succcessfully create MailQueue record from a Message instance with text and html body', function() {
            $message = Yii::$app->mailer->compose()
                ->setFrom([
                    'test@presentator.io' => null,
                ])
                ->setTo([
                    'test1@presentator.io' => null,
                ])
                ->setBcc([
                    'test3@presentator.io' => 'John Doe',
                ])
                ->setSubject('Test text/html body subject')
                ->setTextBody('Test text body')
                ->setHtmlBody('<p>Test html body</p>')
            ;

            $beforeMailQueueCount = MailQueue::find()->count();
            $result               = MailQueue::createByMessage($message);
            $afterMailQueueCount  = MailQueue::find()->count();

            verify('The create method should succeed', $result)->true();
            verify('New MailQueue record should be created', $afterMailQueueCount)->equals($beforeMailQueueCount + 1);

            $model = MailQueue::findOne(['subject' => 'Test text/html body subject']);
            $this->checkMailQueueMessage($model, $message->getSwiftMessage());
        });
    }
}
