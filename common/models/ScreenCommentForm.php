<?php
namespace common\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use common\models\User;
use common\models\Project;
use common\models\ScreenComment;

/**
 * ScreenComment form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentForm extends Model
{
    const SCENARIO_PREVIEW         = 'scenarioPreview';
    const SCENARIO_USER            = 'scenarioUser';
    const SCENARIO_POSITION_UPDATE = 'scenarioPositionUpdate';
    const SCENARIO_STATUS_UPDATE   = 'scenarioStatusUpdate';

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
     * @var string
     */
    public $from;

    /**
     * @var float
     */
    public $posX;

    /**
     * @var float
     */
    public $posY;

    /**
     * @var integer
     */
    public $status;

    /**
     * @var User|Project
     */
    private $rel;

    /**
     * Model constructor.
     * @param User|Project $rel
     * @param array  $config
     * @throws InvalidParamException
     */
    public function __construct($rel, $config = [])
    {
        $this->rel = $rel;

        if (!($this->rel instanceof User) && !($this->rel instanceof Project)) {
            throw new InvalidParamException('$rel should be User or Project instance');
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['from', 'required', 'on' => self::SCENARIO_PREVIEW],
            ['from', 'email'],
            [['posX', 'posY'], 'number', 'min' => 0],
            [['posX', 'posY', 'screenId'], 'required', 'when' => function ($model) {
                if ($model->replyTo) {
                    return false;
                }

                return true;
            }],
            ['message', 'required'],
            ['message', 'string', 'max' => 255],
            ['message', 'filter', 'filter' => function ($value) {
                return strip_tags($value);
            }],
            ['screenId', 'validateScreenId'],
            ['replyTo', 'validateReplyTo'],
            ['status', 'required', 'on' => self::SCENARIO_STATUS_UPDATE],
            ['status', 'in', 'range' => array_keys(ScreenComment::getStatusLabels())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_USER] = [
            'screenId', 'replyTo', 'posX', 'posY', 'message',
        ];

        $scenarios[self::SCENARIO_PREVIEW] = [
            'screenId', 'replyTo', 'posX', 'posY', 'message', 'from',
        ];

        $scenarios[self::SCENARIO_POSITION_UPDATE] = [
            'posX', 'posY',
        ];

        $scenarios[self::SCENARIO_STATUS_UPDATE] = [
            'status',
        ];

        return $scenarios;
    }

    /**
     * Checkes if the form user own a specific Screen model.
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateScreenId($attribute, $params)
    {
        if (!$this->rel || !$this->rel->findScreenById($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid screen ID.'));
        }
    }

    /**
     * Checkes if the form user own a specific ScreenComment model.
     * @param string $attribute
     * @param mixed  $params
     */
    public function validateReplyTo($attribute, $params)
    {
        if (!$this->rel || !$this->rel->findScreenCommentById($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Invalid reply comment ID.'));
        }
    }

    /**
     * Creates a new ScreenComment model.
     * @return ScreenComment|null The created comment on success, otherwise - null.
     */
    public function save()
    {
        if ($this->validate()) {
            $comment           = new ScreenComment();
            $comment->screenId = (int) $this->screenId;
            $comment->posX     = (int) $this->posX;
            $comment->posY     = (int) $this->posY;
            $comment->message  = $this->message;

            if ($this->rel instanceof User) {
                $comment->from = $this->rel->email;
            } else {
                $comment->from = $this->from;
            }

            if ($this->replyTo) {
                $comment->replyTo = (int) $this->replyTo;
            } else {
                $comment->replyTo = null;
            }

            if ($comment->save()) {
                $excludeUserIds = [];
                if ($this->rel instanceof User) {
                    $comment->createUserRels($this->rel->id);
                    $excludeUserIds = [$this->rel->id];
                } else {
                    // check if `from` user exist
                    $fromUser = User::findByEmail($this->from);
                    if ($fromUser) {
                        $excludeUserIds = [$fromUser->id];
                        $comment->createUserRels($fromUser->id);
                    } else {
                        $comment->createUserRels();
                    }
                }

                $comment->sendAdminsEmail($excludeUserIds);

                return $comment;
            }
        }

        return null;
    }

    /**
     * Updates primary ScreenComment position coordinates.
     * @param  ScreenComment $comment
     * @return boolean
     */
    public function updatePosition(ScreenComment $comment)
    {
        if (
            $this->scenario === self::SCENARIO_POSITION_UPDATE &&
            $this->validate() &&
            !$comment->replyTo
        ) {
            $comment->posX = (int) $this->posX;
            $comment->posY = (int) $this->posY;

            return $comment->save();
        }

        return false;
    }

    /**
     * Updates primary ScreenComment status.
     * @param  ScreenComment $comment
     * @return boolean
     */
    public function updateStatus(ScreenComment $comment)
    {
        if (
            $this->scenario === self::SCENARIO_STATUS_UPDATE &&
            $this->validate() &&
            !$comment->replyTo
        ) {
            $comment->status = $this->status;

            return $comment->save();
        }

        return false;
    }
}
