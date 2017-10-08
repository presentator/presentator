<?php
namespace common\components\swiftmailer;

use yii\swiftmailer\Mailer;
use yii\mail\MessageInterface;
use common\models\MailQueue;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CMailer extends Mailer
{
    public $messageClass = 'common\components\swiftmailer\CMessage';

    /**
     * @inheritdoc
     */
    protected function sendMessage($message)
    {
        if ($message->isUsingMailQueue()) {
            return MailQueue::createByMessage($message);
        }

        return parent::sendMessage($message);
    }
}
