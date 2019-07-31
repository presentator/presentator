<?php
namespace presentator\api\models\forms;

use Yii;
use presentator\api\models\ScreenComment;

/**
 * Form model to handle screen comment status change for the preview mode.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PreviewScreenCommentStatusChangeForm extends ApiForm
{
    /**
     * @var string
     */
    public $status;

    /**
     * @var ScreenComment
     */
    protected $comment;

    /**
     * @param ScreenComment $comment
     * @param array         [$config]
     */
    public function __construct(ScreenComment $comment, array $config = [])
    {
        $this->setScreenComment($comment);

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['status'] = Yii::t('app', 'Status');

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['status', 'required'];
        $rules[] = ['status', 'in', 'range' => array_values(ScreenComment::STATUS)];

        return $rules;
    }

    /**
     * @param ScreenComment $comment
     */
    public function setScreenComment(ScreenComment $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return ScreenComment
     */
    public function getScreenComment(): ScreenComment
    {
        return $this->comment;
    }

    /**
     * Persists model form and returns the updated `ScreenComment` model.
     *
     * @return null|ScreenComment
     */
    public function save(): ?ScreenComment
    {
        if ($this->validate()) {
            $comment = $this->getScreenComment();

            $comment->status = $this->status;

            if ($comment->save()) {
                $comment->refresh();

                return $comment;
            }
        }

        return null;
    }
}
