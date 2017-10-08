<?php
namespace common\tests;

use Yii;
use Swift_Message;
use common\models\User;
use common\models\MailQueue;
use common\components\helpers\EmailHelper;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;
    use \Codeception\Specify;

    /**
     * Checks mention result items schema.
     * @see `Project::findAllCommenters()`
     * @param array $result
     */
    public function checkMentionResultItems(array $result)
    {
        // check result item fields
        foreach ($result as $item) {
            verify('Each result item should have an email key', $item)->hasKey('email');
            verify('Each result item should have a firstName key', $item)->hasKey('firstName');
            verify('Each result item should have a lastName key', $item)->hasKey('lastName');
            verify('Each result item should have a userId key', $item)->hasKey('userId');

            // if the user is guest
            $user = User::findOne(['email' => $item['email']]);
            if (!$user) {
                verify('userId should not be set for guests', $item['userId'])->null();
            } else {
                verify('userId should match with the user one', $item['userId'])->equals($user['id']);
                verify('firstName should match with the user one', $item['firstName'])->equals($user['firstName']);
                verify('lastName should match with the user one', $item['lastName'])->equals($user['lastName']);
            }
        }
    }

    /**
     * Checks whether an email message has valid mail queue data.
     * @param MailQueue     $model   MailQueue model to check
     * @param Swift_Message $message Message instance
     */
    public function checkMailQueueMessage(MailQueue $model, Swift_Message $message)
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
}
