<?php
namespace common\components\swiftmailer;

use Yii;
use yii\swiftmailer\Message;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CMessage extends Message
{
    /**
     * @var boolean
     */
    protected $useMailQueue;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->useMailQueue === null) {
            $this->useMailQueue = !empty(Yii::$app->params['useMailQueue']);
        }
    }

    /**
     * @param  boolean $val
     * @return CMessage
     */
    public function useMailQueue($val = true)
    {
        $this->useMailQueue = $val ? true : false;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isUsingMailQueue()
    {
        return $this->useMailQueue === true;
    }
}
