<?php
namespace presentator\api\models\forms;

use Yii;

/**
 * Feedback request form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class FeedbackForm extends ApiForm
{
    /**
     * @var string
     */
    public $from;

    /**
     * @var string
     */
    public $message;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['from']    = Yii::t('app', 'From');
        $labels['message'] = Yii::t('app', 'Message');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['from', 'message'], 'required'];
        $rules[] = ['from', 'email'];

        return $rules;
    }

    /**
     * Validates the form and sends an email to support.
     *
     * @return boolean
     */
    public function send(): bool
    {
        if ($this->validate()) {
            return Yii::$app->mailer->compose('feedback', [
                    'from'    => $this->from,
                    'message' => $this->message,
                ])
                ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
                ->setTo(Yii::$app->params['supportEmail'])
                ->setSubject('Presentator - Feedback')
                ->send();
        }

        return false;
    }
}
