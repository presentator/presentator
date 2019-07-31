<?php
namespace presentator\api\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use presentator\api\models\ProjectLink;
use presentator\api\models\ScreenComment;

/**
 * ScreenComment create form model for the preview mode.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PreviewScreenCommentForm extends ApiForm
{
    /**
     * @var integer
     */
    public $screenId;

    /**
     * @var string
     */
    public $from;

    /**
     * @var integer
     */
    public $replyTo;

    /**
     * @var string
     */
    public $message;

    /**
     * @var float
     */
    public $left = 0;

    /**
     * @var float
     */
    public $top = 0;

    /**
     * @var ProjectLink
     */
    protected $link;

    /**
     * @var null|ScreenComment
     */
    protected $comment;

    /**
     * @param ProjectLink $link
     * @param array [$config]
     */
    public function __construct(ProjectLink $link, array $config = [])
    {
        $this->setProjectLink($link);

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['screenId'] = Yii::t('app', 'Screen');
        $labels['from']     = Yii::t('app', 'From');
        $labels['replyTo']  = Yii::t('app', 'Reply to');
        $labels['message']  = Yii::t('app', 'Message');
        $labels['left']     = Yii::t('app', 'Left');
        $labels['top']      = Yii::t('app', 'Top');

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
        $rules[] = ['from', 'validateFrom'];
        $rules[] = ['message', 'string'];
        $rules[] = ['replyTo', 'validateReplyTo'];
        $rules[] = [['left', 'top'], 'number', 'min' => 0];
        $rules[] = [['left', 'top', 'screenId'], 'required', 'when' => function ($model) {
            return empty($model->replyTo);
        }];
        $rules[] = ['screenId', 'validateScreenId'];

        return $rules;
    }

    /**
     * Additional validates the `from` attribute by preventing
     * guests to impersonate the screen project admins.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateFrom($attribute, $params)
    {
        $link        = $this->getProjectLink();
        $from        = $this->{$attribute};
        $adminEmails = ArrayHelper::getColumn($link->project->users, 'email');

        if (in_array($from, $adminEmails)) {
            $this->addError($attribute, Yii::t('app', 'There is a project admin with such email address. Project admins should use the admin area to comment.'));
        }
    }

    /**
     * Checks if the form link is the owner of the specified screen ID.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateScreenId($attribute, $params)
    {
        $link = $this->getProjectLink();

        if (!$link || !$link->findAllowedScreenById((int) $this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid screen ID.'));
        }
    }

    /**
     * Checks if the form link own a specific ScreenComment model.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateReplyTo($attribute, $params)
    {
        $link    = $this->getProjectLink();
        $comment = $link ? $link->findAllowedScreenCommentById($this->{$attribute}) : null;

        if (
            !$comment ||              // comment doesn't exist or cannot be accessed
            !empty($comment->replyTo) // only primary comment could have replies
        ) {
            $this->addError($attribute, Yii::t('app', 'Invalid primary comment ID.'));
        }
    }

    /**
     * @param ProjectLink $link
     */
    public function setProjectLink(ProjectLink $link): void
    {
        $this->link = $link;
    }

    /**
     * @return ProjectLink
     */
    public function getProjectLink(): ProjectLink
    {
        return $this->link;
    }

    /**
     * Persists model form and returns the created `ScreenComment` model.
     *
     * @return null|ScreenComment
     */
    public function save(): ?ScreenComment
    {
        if ($this->validate()) {
            $comment          = new ScreenComment;
            $comment->from    = $this->from;
            $comment->message = $this->message;
            $comment->status  = ScreenComment::STATUS['PENDING'];

            if (
                $this->replyTo &&
                ($replyToComment = ScreenComment::findById($this->replyTo))
            ) {
                $comment->screenId = $replyToComment->screenId;
                $comment->replyTo  = $replyToComment->id;
                $comment->left     = 0.0;
                $comment->top      = 0.0;
            } else {
                $comment->screenId = $this->screenId;
                $comment->replyTo  = null;
                $comment->left     = (float) $this->left;
                $comment->top      = (float) $this->top;
            }

            if ($comment->save()) {
                $comment->createNotifications();

                $comment->refresh();

                return $comment;
            }
        }

        return null;
    }
}
