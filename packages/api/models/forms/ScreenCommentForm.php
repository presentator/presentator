<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\User;
use presentator\api\models\ScreenComment;

/**
 * ScreenComment create/update form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentForm extends ApiForm
{
    const SCENARIO_CREATE = 'scenarioCreate';
    const SCENARIO_UPDATE = 'scenarioUpdate';

    /**
     * @var integer
     */
    public $screenId;

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
     * @var string
     */
    public $status;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var null|ScreenComment
     */
    protected $comment;

    /**
     * @param User          $user
     * @param ScreenComment $comment
     * @param array [$config]
     */
    public function __construct(User $user, ScreenComment $comment = null, array $config = [])
    {
        $this->setUser($user);

        if ($comment) {
            $this->setScreenComment($comment);
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['replyTo'] = Yii::t('app', 'Reply to');
        $labels['message'] = Yii::t('app', 'Message');
        $labels['left']    = Yii::t('app', 'Left');
        $labels['top']     = Yii::t('app', 'Top');
        $labels['status']  = Yii::t('app', 'Status');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['message', 'required'];
        $rules[] = ['message', 'string'];
        $rules[] = ['replyTo', 'validateReplyTo'];
        $rules[] = [['left', 'top'], 'number', 'min' => 0];
        $rules[] = [['left', 'top', 'screenId'], 'required', 'when' => function ($model) {
            return empty($model->replyTo);
        }];
        $rules[] = ['screenId', 'validateUserScreenId'];
        $rules[] = ['status', 'in', 'range' => array_values(ScreenComment::STATUS)];
        $rules[] = ['status', 'default', 'value' => ScreenComment::STATUS['PENDING']];

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CREATE] = [
            'screenId', 'replyTo', 'message', 'left', 'top', 'status',
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'left', 'top', 'status',
        ];

        return $scenarios;
    }


    /**
     * Checks if the form user is the owner of the specified screen ID.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateUserScreenId($attribute, $params)
    {
        $user = $this->getUser();

        if (!$user || !$user->findScreenById((int) $this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid screen ID.'));
        }
    }

    /**
     * Checks if the form user own a specific ScreenComment model.
     *
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateReplyTo($attribute, $params)
    {
        $user    = $this->getUser();
        $comment = $user ? $user->findScreenCommentById((int) $this->{$attribute}) : null;

        if (
            !$comment ||              // comment doesn't exist or can't be accessed
            !empty($comment->replyTo) // only primary comment can have replies
        ) {
            $this->addError($attribute, Yii::t('app', 'Invalid primary comment ID.'));
        }
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param ScreenComment $comment
     */
    public function setScreenComment(ScreenComment $comment): void
    {
        $this->comment  = $comment;
        $this->screenId = $comment->screenId;
        $this->replyTo  = $comment->replyTo;
        $this->message  = $comment->message;
        $this->left     = $comment->left;
        $this->top      = $comment->top;
        $this->status   = $comment->status;
    }

    /**
     * @return null|ScreenComment
     */
    public function getScreenComment(): ?ScreenComment
    {
        return $this->comment;
    }

    /**
     * Persists model form and returns the created/updated `ScreenComment` model.
     *
     * @return null|ScreenComment
     */
    public function save(): ?ScreenComment
    {
        if ($this->validate()) {
            $user        = $this->getUser();
            $comment     = $this->getScreenComment() ?: (new ScreenComment);
            $isNewRecord = $comment->isNewRecord;

            $comment->from    = $isNewRecord ? $user->email : $comment->from;
            $comment->message = $this->message;

            if (
                $this->replyTo &&
                ($replyToComment = ScreenComment::findById($this->replyTo))
            ) {
                $comment->screenId = $replyToComment->screenId;
                $comment->replyTo  = $replyToComment->id;
                $comment->status   = ScreenComment::STATUS['PENDING'];
                $comment->left     = 0.0;
                $comment->top      = 0.0;
            } else {
                $comment->screenId = $this->screenId;
                $comment->replyTo  = null;
                $comment->status   = $this->status;
                $comment->left     = (float) $this->left;
                $comment->top      = (float) $this->top;
            }

            if ($comment->save()) {
                if ($isNewRecord) {
                    $comment->createNotifications();
                }

                $comment->refresh();

                return $comment;
            }
        }

        return null;
    }
}
