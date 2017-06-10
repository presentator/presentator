<?php
namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * ScreenComment AR model.
 *
 * @property integer $id
 * @property integer $replyTo
 * @property integer $screenId
 * @property string  $from
 * @property string  $message
 * @property integer $posX
 * @property integer $posY
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenComment extends CActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%screenComment}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreen()
    {
        return $this->hasOne(Screen::className(), ['id' => 'screenId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReplies()
    {
        return $this->hasMany(ScreenComment::className(), ['replyTo' => 'id'])
            ->orderBy([ScreenComment::tableName() . '.createdAt' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrimaryComment()
    {
        return $this->hasOne(ScreenComment::className(), ['id' => 'replyTo']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRels()
    {
        return $this->hasMany(UserScreenCommentRel::className(), ['screenCommentId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoginUserRel()
    {
        $userId = (!Yii::$app->user->isGuest ? Yii::$app->user->identity->id : -1);

        return $this->hasOne(UserScreenCommentRel::className(), ['screenCommentId' => 'id'])
            ->andOnCondition([UserScreenCommentRel::tableName() . '.userId' => $userId]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['email' => 'from'])
            ->andWhere([User::tableName() . '.status' => User::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $replyIds = ArrayHelper::getColumn($this->replies, 'id');

        if (parent::delete()) {
            self::deleteAll(['id' => $replyIds]);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->replyTo && $this->getOldAttribute('replyTo') != $this->replyTo) {
                return $this->copyPrimaryCommentProps();
            }

            return true;
        }

        return false;
    }

    /**
     * Ensures that a reply comment is properly attached to its primary one.
     * @return boolean
     */
    protected function copyPrimaryCommentProps()
    {
        $replyComment = static::findOne($this->replyTo);
        if ($replyComment->replyTo) {
            // is not direct primary
            $primaryComment = $replyComment->primaryComment;
        } else {
            $primaryComment = $replyComment;
        }

        if (!$primaryComment) {
            return false;
        }

        // copy primary comment fields
        $this->replyTo  = $primaryComment->id;
        $this->screenId = $primaryComment->screenId;
        $this->posX     = $primaryComment->posX;
        $this->posY     = $primaryComment->posY;

        return true;
    }

    /**
     * Creates user-comment rels for all project owners.
     * @param  integer|array $readId Id(s) of the user(s) to mark the comment as read.
     * @return boolean
     */
    public function createUserRels($readId = null)
    {
        $result       = true;
        $readUserIds  = is_array($readId) ? $readId : [$readId];
        $projectUsers = $this->screen->project->users;
        $existUserIds = ArrayHelper::getColumn($this->userRels, 'userId');

        foreach ($projectUsers as $user) {
            if (in_array($user->id, $existUserIds)) {
                continue; // already linked
            }

            $model = new UserScreenCommentRel([
                'userId'          => $user->id,
                'screenCommentId' => $this->id,
            ]);

            if (in_array($user->id, $readUserIds)) {
                $model->isRead = UserScreenCommentRel::IS_READ_TRUE;
            } else {
                $model->isRead = UserScreenCommentRel::IS_READ_FALSE;
            }

            $result = $result && $model->save();
        }

        return $result;
    }

    /**
     * Marks the current comment as read by creating/updating a user-comment relation.
     * @param  User $user The user to mark the comment as read.
     * @return boolean
     */
    public function markAsRead(User $user)
    {
        $rel = UserScreenCommentRel::findOne(['screenCommentId' => $this->id, 'userId' => $user->id]);
        if (!$rel) {
            $rel         = new UserScreenCommentRel;
            $rel->userId = $user->id;
            $rel->screenCommentId = $this->id;
        }

        if ($rel->isNewRecord || $rel->isRead != UserScreenCommentRel::IS_READ_TRUE) {
            $rel->isRead = UserScreenCommentRel::IS_READ_TRUE;

            return $rel->save();
        }

        return true;
    }

    /**
     * Checkes whether a comment is read by a user.
     *
     * NB! Assumes the comment as read when:
     * - missing UserCommentRel record
     * - has rel record and `UserCommentRel::isRead` is `false`
     *
     * @param  User    $user
     * @return boolean
     */
    public function isRead(User $user)
    {
        $rel = UserScreenCommentRel::findOne(['screenCommentId' => $this->id, 'userId' => $user->id]);

        if ($rel) {
            return $rel->isRead == UserScreenCommentRel::IS_READ_TRUE;
        }

        // probably means that the user is added later as project admin
        // and this is why we assume all previous comments before that as read.
        return true;
    }

    /**
     * Checkes whether a comment is read by the current loggin user (useful for some eager loading optimizations).
     *
     * NB! Assumes the comment as read when:
     * - user is not logged in (guest)
     * - missing UserCommentRel record
     * - has rel record and `UserCommentRel::isRead` is `true`
     *
     * @see `self::getLoginUserRel()`
     * @return boolean
     */
    public function isReadByLoginUser()
    {
        if (!Yii::$app->user->isGuest && $this->loginUserRel) {
            return $this->loginUserRel->isRead == UserScreenCommentRel::IS_READ_TRUE;
        }

        return true;
    }

    /**
     * Sends emails to all project admin to notify with the current model info to a specific project admin.
     * @param  integer|array $exclude User id(s) to exclude.
     * @return boolean
     */
    public function sendAdminsEmail($excludeId = null)
    {
        $result = true;

        // normalize exlude user ids
        $excludeId = is_array($excludeId) ? $excludeId : array_filter([$excludeId]);

        foreach ($this->screen->project->users as $user) {
            if (in_array($user->id, $excludeId) ||
                !$user->getSetting(User::NOTIFICATIONS_SETTING_KEY, true)
            ) {
                continue;
            }

            $result = $result && Yii::$app->mailer->compose('new_comment', [
                    'user'    => $user,
                    'comment' => $this,
                ])
                ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
                ->setTo($user->email)
                ->setSubject('Presentator - ' . Yii::t('app', 'New comment'))
                ->send();
        }

        return $result;
    }
}
