<?php
namespace common\models;

use Yii;
use common\components\helpers\CStringHelper;

/**
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
            [['from', 'to'], 'email', 'allowName' => true],
            [['to', 'subject', 'body'], 'required'],
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
            $from = CStringHelper::parseAddresses($this->from);
        } else {
            $from = [Yii::$app->params['noreplyEmail'] => 'Presentator'];
        }

        $to = CStringHelper::parseAddresses($this->to);

        $mail = Yii::$app->mailer->compose()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($this->subject)
            ->setHtmlBody($this->body)
        ;

        if (!empty($this->cc)) {
            $cc = CStringHelper::parseAddresses($this->cc);

            $mail->setCc($cc);
        }

        if (!empty($this->bcc)) {
            $bcc = CStringHelper::parseAddresses($this->bcc);

            $mail->setBcc($this->bcc);
        }

        return $mail->send();
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
}
