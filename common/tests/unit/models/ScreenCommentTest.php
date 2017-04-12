<?php
namespace common\tests\unit\models;

use Yii;
use yii\db\ActiveQuery;
use common\models\User;
use common\models\Screen;
use common\models\ScreenComment;
use common\models\UserScreenCommentRel;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\ScreenCommentFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\tests\fixtures\UserScreenCommentRelFixture;

/**
 * ScreenComment AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class'    => UserFixture::className(),
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => codecept_data_dir() . 'project.php',
            ],
            'version' => [
                'class'    => VersionFixture::className(),
                'dataFile' => codecept_data_dir() . 'version.php',
            ],
            'screen' => [
                'class'    => ScreenFixture::className(),
                'dataFile' => codecept_data_dir() . 'screen.php',
            ],
            'comment' => [
                'class'    => ScreenCommentFixture::className(),
                'dataFile' => codecept_data_dir() . 'screen_comment.php',
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_project_rel.php',
            ],
            'userCommentRel' => [
                'class'    => UserScreenCommentRelFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_screen_comment_rel.php',
            ],
        ]);
    }

    /* ===============================================================
     * Relations
     * ============================================================ */
    /**
     * `ScreenComment::getScreen()` relation query method test.
     */
    public function testGetScreen()
    {
        $model = ScreenComment::findOne(1001);
        $query = $model->getScreen();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid Project model', $model->screen)->isInstanceOf(Screen::className());
        verify('Screen id should match', $model->screen->id)->equals($model->screenId);
    }

    /**
     * `ScreenComment::getReplies()` relation query method test.
     */
    public function testGetReplies()
    {
        $this->specify('ScreenComment model WITHOUT reply comments', function() {
            $model = ScreenComment::findOne(1004);
            $query = $model->getReplies();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be an empty array', $model->replies)->isEmpty();
        });

        $this->specify('ScreenComment model WITH reply comments', function() {
            $model = ScreenComment::findOne(1002);
            $query = $model->getReplies();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be an empty array', $model->replies)->notEmpty();
            foreach ($model->replies as $reply) {
                verify('Query result item should be valid ScreenComment model', $reply)->isInstanceOf(ScreenComment::className());
                verify('Query result item replyTo should match', $reply->replyTo)->equals($model->id);
            }
        });
    }

    /**
     * `ScreenComment::getPrimaryComment()` relation query method test.
     */
    public function testGetPrimaryComment()
    {
        $this->specify('Try to get primary comment of a primary ScreenComment model', function() {
            $model = ScreenComment::findOne(1002);
            $query = $model->getPrimaryComment();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be null', $model->primaryComment)->null();
        });

        $this->specify('Try to get primary comment of a reply ScreenComment model', function() {
            $model = ScreenComment::findOne(1003);
            $query = $model->getPrimaryComment();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be valid ScreenComment model', $model->primaryComment)->isInstanceOf(ScreenComment::className());
            verify('Query result replyTo should not be set', $model->primaryComment->replyTo)->null();
        });
    }

    /**
     * `ScreenComment::getUserRels()` relation query method test.
     */
    public function testGetUserRels()
    {
        $this->specify('ScreenComment model WITHOUT UserScreenCommentRel models', function() {
            $model = ScreenComment::findOne(1003);
            $query = $model->getUserRels();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be an empty array', $model->userRels)->isEmpty();
        });

        $this->specify('ScreenComment model WITH UserScreenCommentRel models', function() {
            $model = ScreenComment::findOne(1004);
            $query = $model->getUserRels();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be an empty array', $model->userRels)->notEmpty();
            foreach ($model->userRels as $rel) {
                verify('Query result item should be valid UserScreenCommentRel model', $rel)->isInstanceOf(UserScreenCommentRel::className());
                verify('Query result item screenCommentId should match', $rel->screenCommentId)->equals($model->id);
            }
        });
    }

    /**
     * `ScreenComment::getLoginUserRel()` relation query method test.
     */
    public function testGetLoginUserRel()
    {
        $this->specify('No logged in user', function() {
            $model = ScreenComment::findOne(1003);
            $query = $model->getLoginUserRel();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be null', $model->loginUserRel)->null();
        });

        $this->specify('Logged in user', function() {
            $user  = User::findOne(1003);
            Yii::$app->user->login($user);

            $model = ScreenComment::findOne(1004);
            $query = $model->getLoginUserRel();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be valid UserScreenCommentRel model', $model->loginUserRel)->isInstanceOf(UserScreenCommentRel::className());
            verify('Query result screenCommentId should match', $model->loginUserRel->screenCommentId)->equals($model->id);
        });
    }

    /**
     * `ScreenComment::getFromUser()` relation query method test.
     */
    public function testGetFromUser()
    {
        $this->specify('User does not exist', function() {
            $model = ScreenComment::findOne(1001);
            $query = $model->getFromUser();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be null', $model->fromUser)->null();
        });

        $this->specify('User exists', function() {
            $model = ScreenComment::findOne(1005);
            $query = $model->getFromUser();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be valid UserScreenCommentRel model', $model->fromUser)->isInstanceOf(User::className());
            verify('Query result screenCommentId should match', $model->fromUser->email)->equals($model->from);
        });
    }

    /* ===============================================================
     * Others
     * ============================================================ */
    /**
     * `ScreenComment::createUserRels()` method test.
     */
    public function testCreateUserRels()
    {
        $comment = new ScreenComment([
            'replyTo'  => null,
            'screenId' => 1003,
            'from'     => 'test123456@presentator.io',
            'message'  => 'New comment message...',
            'posX'     => 10,
            'posY'     => 20,
        ]);
        $comment->save();
        $result = $comment->createUserRels(1002);
        $comment->refresh();

        verify('Method should succeed', $result)->true();
        verify('Should have 2 user rels', $comment->userRels)->count(2);
        foreach ($comment->userRels as $rel) {
            if ($rel->userId == 1002) {
                verify('Should be marked as read', $rel->isRead)->equals(UserScreenCommentRel::IS_READ_TRUE);
            } else {
                verify('Should be marked as unread', $rel->isRead)->equals(UserScreenCommentRel::IS_READ_FALSE);
            }
        }
    }

    /**
     * `ScreenComment::markAsRead()` method test.
     */
    public function testMarkAsRead()
    {
        $this->specify('Create new UserCommentRel model', function() {
            $user    = User::findOne(1002);
            $comment = ScreenComment::findOne(1003);
            $rel     = UserScreenCommentRel::findOne(['userId' => $user->id, 'screenCommentId' => $comment->id]);

            verify('Rel should not exist', $rel)->null();

            $result = $comment->markAsRead($user);
            $rel     = UserScreenCommentRel::findOne(['userId' => $user->id, 'screenCommentId' => $comment->id]);

            verify('Should complete successfully', $result)->true();
            verify('Rel should be valid UserScreenCommentRel model', $rel)->isInstanceOf(UserScreenCommentRel::className());
            verify('Should be marked as read', $rel->isRead)->equals(UserScreenCommentRel::IS_READ_TRUE);
            verify('User id should match', $rel->userId)->equals($user->id);
            verify('Comment id should match', $rel->screenCommentId)->equals($comment->id);
        });

        $this->specify('Update existing UserCommentRel model', function() {
            $user    = User::findOne(1003);
            $comment = ScreenComment::findOne(1004);
            $rel     = UserScreenCommentRel::findOne(['userId' => $user->id, 'screenCommentId' => $comment->id]);

            verify('Should not be marked as read', $rel->isRead)->equals(UserScreenCommentRel::IS_READ_FALSE);

            // update
            $result = $comment->markAsRead($user);
            $rel->refresh();

            verify('Should complete successfully', $result)->true();
            verify('Should be marked as read', $rel->isRead)->equals(UserScreenCommentRel::IS_READ_TRUE);
        });
    }

    /**
     * `ScreenComment::isRead()` method test.
     */
    public function testIsRead()
    {
        $user = User::findOne(1003);

        $this->specify('Check unread comment', function() use ($user) {
            $comment = ScreenComment::findOne(1004);
            verify('Should not be read', $comment->isRead($user))->false();
        });

        $this->specify('Check read comment', function() use ($user) {
            $comment = ScreenComment::findOne(1005);
            verify('Should be read', $comment->isRead($user))->true();
        });
    }

    /**
     * `ScreenComment::isReadByLoginUser()` method test.
     */
    public function testIsReadByLoginUser()
    {
        $comment = ScreenComment::findOne(1004);
        $this->specify('Non logged in user', function() use ($comment) {
            verify('Should be read', $comment->isReadByLoginUser())->true();
        });

        $this->specify('Logged in user read comment', function() use ($comment) {
            $user = User::findOne(1003);
            Yii::$app->user->login($user);

            verify('Should not be read', $comment->isReadByLoginUser())->false();
        });
    }

    /**
     * `ScreenComment::sendAdminsEmail()` method test.
     */
    public function testSendAdminsEmail()
    {
        $this->specify('Send preview email WITHOUT user exclude', function() {
            $model       = ScreenComment::findOne(1004);
            $validEmails = ['test3@presentator.io', 'test4@presentator.io'];

            verify('Mail method should succeed', $model->sendAdminsEmail())->true();

            $this->tester->seeEmailIsSent(2);
            $emails = $this->tester->grabSentEmails();
            foreach ($emails as $email) {
                $message = $email->getSwiftMessage();
                $to = array_keys($email->getTo())[0];

                verify('Receiver email should match', in_array($to, $validEmails))->true();
                verify('Body should contains a reply url', current($message->getChildren())->getBody())->contains(
                    Yii::$app->mainUrlManager->createUrl([
                        'projects/view',
                        'id'             => $model->screen->project->id,
                        'screen'         => $model->screen->id,
                        'comment_target' => ($model->replyTo ? $model->replyTo : $model->id),
                        'reply_to'       => $model->id
                    ], true)
                );
            }
        });

        $this->specify('Send preview email WITH user exclude', function() {
            $model       = ScreenComment::findOne(1004);
            $validEmails = ['test3@presentator.io', 'test4@presentator.io'];

            verify('Mail method should succeed', $model->sendAdminsEmail([1003]))->true();

            $this->tester->seeEmailIsSent(3); // 1 + 2 (count the previously 2 send)
            $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
            verify('Receiver email should match', $message->getTo())->hasKey('test4@presentator.io');
            verify('Body should contains a preview url', current($message->getChildren())->getBody())->contains(
                Yii::$app->mainUrlManager->createUrl([
                    'projects/view',
                    'id'             => $model->screen->project->id,
                    'screen'         => $model->screen->id,
                    'comment_target' => ($model->replyTo ? $model->replyTo : $model->id),
                    'reply_to'       => $model->id
                ], true)
            );
        });
    }
}
