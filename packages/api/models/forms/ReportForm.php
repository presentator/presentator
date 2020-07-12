<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\ProjectLink;

/**
 * Violation report form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ReportForm extends ApiForm
{
    /**
     * @var string
     */
    public $details;

    /**
     * @var null|ProjectLink
     */
    protected $projectLink;

    /**
     * @param ProjectLink $user
     * @param array [$config]
     */
    public function __construct(ProjectLink $projectLink, array $config = [])
    {
        $this->setProjectLink($projectLink);

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['details'] = Yii::t('app', 'Details');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['details', 'string'];

        return $rules;
    }

    /**
     * @param ProjectLink $projectLink
     */
    public function setProjectLink(ProjectLink $projectLink): void
    {
        $this->projectLink = $projectLink;
    }

    /**
     * @return ProjectLink
     */
    public function getProjectLink(): ProjectLink
    {
        return $this->projectLink;
    }

    /**
     * Validates the form and sends an email to support.
     *
     * @return boolean
     */
    public function send(): bool
    {
        if ($this->validate()) {
            return Yii::$app->mailer->compose('report', [
                    'projectLink' => $this->projectLink,
                    'details'     => $this->details,
                ])
                ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
                ->setTo(Yii::$app->params['supportEmail'])
                ->setSubject('Presentator - Violation report')
                ->send();
        }

        return false;
    }
}
