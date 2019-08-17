<?php
namespace presentator\api\models;

use yii\db\ActiveQuery;

/**
 * UserScreenCommentRel AR model
 *
 * @property integer $id
 * @property integer $userId
 * @property integer $screenCommentId
 * @property integer $isRead
 * @property integer $isProcessed
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserScreenCommentRel extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenComment()
    {
        return $this->hasOne(ScreenComment::class, ['id' => 'screenCommentId']);
    }

    /**
     * Marks the current rel model as read (updating the `isRead` column).
     *
     * @return boolean
     */
    public function markAsRead(): bool
    {
        if ($this->isRead) {
            return true; // already read
        }

        $this->isRead = true;

        return $this->save();
    }

    /**
     * Marks the current rel model as unread (updating the `isRead` column).
     *
     * @return boolean
     */
    public function markAsUnread(): bool
    {
        if (!$this->isRead) {
            return true; // already unread
        }

        $this->isRead = false;

        return $this->save();
    }

    /**
     * Marks the current rel model as processed (updating the `isProcessed` column).
     *
     * @return boolean
     */
    public function markAsProcessed(): bool
    {
        if ($this->isProcessed) {
            return true; // already processed
        }

        $this->isProcessed = true;

        return $this->save();
    }

    /**
     * Marks the current rel model as unprocessed (updating the `isProcessed` column).
     *
     * @return boolean
     */
    public function markAsUnprocessed(): bool
    {
        if (!$this->isProcessed) {
            return true; // already unprocessed
        }

        $this->isProcessed = false;

        return $this->save();
    }

    /**
     * Generates a query to fetch records that could be processed by `\console\controller\MailsController::actionProcess()`.
     *
     * @param  string [$beforeCreatedAt] Optional datetime sting (eg. '2019-08-17 11:00:00') that requires each rel model to be created before a specific time.
     * @return \yii\db\ActiveQuery
     */
    public function findProcessableQuery(string $beforeCreatedAt = ''): ActiveQuery
    {
        return static::find()
            ->with(['user', 'screenComment'])
            ->andWhere([
                static::tableName() . '.isRead'      => false,
                static::tableName() . '.isProcessed' => false,
            ])
            ->andFilterWhere([
                '<=',
                (static::tableName() . '.createdAt'),
                $beforeCreatedAt
            ]);
    }

}
