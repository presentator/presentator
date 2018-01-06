<?php
namespace common\models;

use Yii;
use yii\swiftmailer\Message;
use common\components\helpers\EmailHelper;
use common\components\validators\CEmailValidator;
use common\components\swiftmailer\CMessage;

/**
 * @todo Attachments support
 * @todo Multiple body content types (eg. text/plain and text/html)
 *
 * MailQueue AR model.
 *
 * @property integer     $id
 * @property null|string $from
 * @property string      $to
 * @property null|string $cc
 * @property null|string $bcc
 * @property string      $subject
 * @property string      $body
 * @property integer     $status
 * @property integer     $createdAt
 * @property integer     $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class MailQueue extends CActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_SENT    = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mailQueue}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['to', 'subject', 'body'], 'required'],
            [['from', 'to', 'cc', 'bcc'], CEmailValidator::className(), 'allowName' => true, 'allowMultiple' => true],
            ['status', 'default', 'value' => static::STATUS_PENDING],
            ['status', 'in', 'range' => [static::STATUS_PENDING, static::STATUS_SENT]],
        ];
    }

    /**
     * Mark mail record as successfully send.
     * @see `self::process()`
     * @return boolean
     */
    public function markAsSent()
    {
        if ($this->status != self::STATUS_SENT) {
            $this->status = self::STATUS_SENT;

            return $this->save();
        }

        return true;
    }

    /**
     * Send single mail record no mather of its status.
     * @see `self::process()`
     * @return boolean
     */
    public function send()
    {
        if ($this->from) {
            $from = EmailHelper::stringToArray($this->from);
        } else {
            $from = [Yii::$app->params['noreplyEmail'] => 'Presentator'];
        }

        $to = EmailHelper::stringToArray($this->to);

        $message = Yii::$app->mailer->compose()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($this->subject)
            ->setHtmlBody($this->body)
        ;

        // force direct send
        if ($message instanceof CMessage) {
            $message->useMailQueue(false);
        }

        if (!empty($this->cc)) {
            $cc = EmailHelper::stringToArray($this->cc);

            $message->setCc($cc);
        }

        if (!empty($this->bcc)) {
            $bcc = EmailHelper::stringToArray($this->bcc);

            $message->setBcc($this->bcc);
        }

        return $message->send();
    }

    /**
     * Process single mail record and update its status on success.
     * @param  boolean $purge  Whether to delete or just mark the mail as sent.
     * @param  boolean $resend Whether to process mail models with sent status.
     * @return boolean
     */
    public function process($purge = true, $resend = false)
    {
        $result          = true;
        $isAlreadyMarked = $this->status == self::STATUS_SENT;

        if (!$isAlreadyMarked || $resend) {
            $result = $this->send();
        }

        if ($result) {
            if ($purge) {
                return $this->delete() > 0;
            }

            if (!$isAlreadyMarked) {
                return $this->markAsSent();
            }

            return true;
        }

        return false;
    }

    /**
     * Find mail records with specific status.
     * @param  integer $status
     * @param  integer $limit
     * @param  integer $offset
     * @return MailQueue[]
     */
    public static function findMails($status = self::STATUS_PENDING, $limit = 10, $offset = 0)
    {
        return static::find()
            ->where(['status' => $status])
            ->orderBy(['createdAt' => SORT_ASC])
            ->offset($offset)
            ->limit($limit)
            ->all();
    }

    /**
     * Create new MailQueue record by Message instance.
     * @param  Message $message
     * @return boolean
     */
    public static function createByMessage(Message $message)
    {
        $to      = EmailHelper::arrayToString((array) $message->getTo());
        $from    = EmailHelper::arrayToString((array) $message->getFrom());
        $cc      = EmailHelper::arrayToString((array) $message->getCc());
        $bcc     = EmailHelper::arrayToString((array) $message->getBcc());
        $subject = $message->getSubject();

        // extract mail body
        $body         = $message->getSwiftMessage()->getBody();
        $bodyChildren = $message->getSwiftMessage()->getChildren();
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

        $model          = new MailQueue();
        $model->from    = $from;
        $model->to      = $to;
        $model->cc      = $cc;
        $model->bcc     = $bcc;
        $model->subject = $subject;
        $model->body    = $body;
        $model->status  = self::STATUS_PENDING;

        return $model->save();
    }
}
