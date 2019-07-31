<?php
namespace presentator\api\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * ScreenComment AR model
 *
 * @property integer $id
 * @property integer $replyTo
 * @property integer $screenId
 * @property string  $from
 * @property string  $message
 * @property float   $left
 * @property float   $top
 * @property string  $status
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenComment extends ActiveRecord
{
    const STATUS = [
        'PENDING'  => 'pending',
        'RESOLVED' => 'resolved',
    ];

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreen()
    {
        return $this->hasOne(Screen::class, ['id' => 'screenId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReplyToComment()
    {
        return $this->hasOne(ScreenComment::class, ['id' => 'replyTo']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReplies()
    {
        return $this->hasMany(ScreenComment::class, ['replyTo' => 'id'])
            ->orderBy([ScreenComment::tableName() . '.createdAt' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::class, ['email' => 'from'])
            ->andOnCondition([User::tableName() . '.status' => User::STATUS['ACTIVE']]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserScreenCommentRels()
    {
        return $this->hasOne(UserScreenCommentRel::class, ['screenCommentId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotifiedUsers()
    {
        return $this->hasMany(User::class, ['id' => 'userId'])
            ->via('userScreenCommentRels');
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->status === null) {
                $this->status = static::STATUS['PENDING'];
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // delete replies
        foreach ($this->replies as $reply) {
            if (!$reply->delete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['fromUser'] = function ($model, $field) {
            if (!$model->fromUser) {
                return null;
            }

            return $model->fromUser->toArray(['id', 'email', 'firstName', 'lastName', 'avatar']);
        };

        $extraFields['metaData'] = function ($model, $field) {
            return [
                'screenId'       => $model->screen->id,
                'screenTitle'    => $model->screen->title,
                'prototypeId'    => $model->screen->prototype->id,
                'prototypeTitle' => $model->screen->prototype->title,
                'projectId'      => $model->screen->prototype->project->id,
                'projectTitle'   => $model->screen->prototype->project->title,
            ];
        };

        return $extraFields;
    }

    /**
     * Updates the related comment notification `isRead` flag for the provided user.
     *
     * @param  User $user
     * @return boolean
     */
    public function markAsReadForUser(User $user): bool
    {
        $rel = $this->getUserScreenCommentRels()
            ->andWhere([UserScreenCommentRel::tableName() . '.userId' => $user->id])
            ->one();

        return $rel && $rel->markAsRead();
    }

    /**
     * Creates comment notifications for the related project collaborators.
     *
     * @param  boolean [$sendGuestsMentionEmails] Whether to send an email to each mentioned guest collaborator.
     * @param  boolean [$sendFirestoreMessages]   Whether to send a firestore updated messages request for the registered users (only if `firestore` service is configured).
     * @return boolean
     */
    public function createNotifications($sendGuestsMentionEmails = true, $sendFirestoreMessages = true): bool
    {
        $transaction = static::getDb()->beginTransaction();

        try {
            $mentions = $this->extractMentionedCollaborators();

            // "soft" notify project admins
            foreach ($this->screen->prototype->project->users as $user) {
                if ($user->email === $this->from) {
                    continue; // skip the author of the comment
                }

                $isProcessed = false;
                if (
                    // doesn't want to be email notified on each comment
                    !$user->getSetting(UserSetting::NOTIFY_ON_EACH_COMMENT, false) &&
                    // doesn't want to be email notified on mention OR the current user is not mentioned
                    (
                        !$user->getSetting(UserSetting::NOTIFY_ON_MENTION, false) ||
                        !in_array($user->email, $mentions)
                    )
                ) {
                    // mark as processed to prevent sending an email notification
                    $isProcessed = true;
                }

                $this->linkOnce('notifiedUsers', $user, [
                    'isRead'      => false,
                    'isProcessed' => $isProcessed,
                ]);
            }

            $transaction->commit();

            // send guests mention emails
            if ($sendGuestsMentionEmails) {
                try {
                    $this->sendGuestsMentionEmails();
                } catch (\Exception | \Throwable $je) {
                    // silence any errors
                }
            }

            // send firestore notification messages
            if ($sendFirestoreMessages && Yii::$app->has('firestore')) {
                $data = [];
                foreach ($this->screen->prototype->project->users as $user) {
                    $data['u' . $user->id] = ['integerValue' => strtotime($this->updatedAt)];
                }

                try {
                    Yii::$app->firestore->upsert(
                        'presentator_notifications',
                        ('p' . $this->screen->prototype->projectId),
                        $data,
                        ['timeout' => 0.1] // we don't need the response so it is not needed to wait for it
                    );
                } catch (\Exception | \Throwable $e) {
                    // silence any errors
                }
            }

            return true;
        } catch (\Exception | \Throwable $e) {
            $transaction->rollBack();

            Yii::error($e->getMessage());
        }

        return false;
    }

    /**
     * Sends an email to each mentioned guest collaborator.
     *
     * @return boolean
     */
    public function sendGuestsMentionEmails(): bool
    {
        // try to find the first project links that allow leaving comments
        // (password unprotected links are with higher priority)
        $projectLink = null;
        foreach ($this->screen->prototype->project->projectLinks as $link) {
            if (
                // can leave comments
                $link->allowComments &&
                // is allowed to access the comment's screen prototype
                (
                    empty($link->projectLinkPrototypeRels) ||
                    in_array($this->screen->prototype->id, ArrayHelper::getColumn($link->projectLinkPrototypeRels, 'prototypeId'))
                ) &&
                // is not password protected or another link is not found yet
                (
                    !$link->isPasswordProtected() ||
                    !$projectLink
                )
            ) {
                $projectLink = $link;
            }
        }

        $result   = true;
        $mentions = $this->extractMentionedCollaborators();
        $guests   = array_diff($mentions, ArrayHelper::getColumn($this->screen->prototype->project->users, 'email'));

        foreach ($guests as $guest) {
            $result = $result && Yii::$app->mailer->compose('guest_mention', [
                    'comment'     => $this,
                    'projectLink' => $projectLink,
                ])
                ->setFrom([Yii::$app->params['noreplyEmail'] => 'Presentator'])
                ->setTo($guest)
                ->setSubject('Presentator - ' . Yii::t('mail', 'You have been mentioned in a comment'))
                ->send();
        }

        return $result;
    }

    /**
     * Extracts mentioned collaborator emails from the comment message.
     * The mentioned trigger characters are '@' and '+'.
     *
     * @return array
     */
    public function extractMentionedCollaborators(): array
    {
        $result  = [];
        $matches = [];
        $pattern = '/(?<=\s|^)[@+]([a-z0-9._@]+)/i';

        preg_match_all($pattern, $this->message, $matches);

        $collaborators = $this->screen->prototype->project->findAllCollaborators();

        // filter mentions with the project's collaborators
        if (!empty($matches[1]) && !empty($collaborators)) {
            foreach ($collaborators as $collaborators) {
                if (in_array($collaborators['email'], $matches[1])) {
                    $result[] = $collaborators['email'];
                }
            }
        }

        return $result;
    }
}
