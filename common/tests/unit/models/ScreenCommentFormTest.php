<?php
namespace common\tests\models;

use Yii;
use yii\base\InvalidParamException;
use common\models\User;
use common\models\Project;
use common\models\Version;
use common\models\ScreenComment;
use common\models\ScreenCommentForm;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\ScreenCommentFixture;
use common\tests\fixtures\UserProjectRelFixture;

/**
 * ScreenCommentForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentFormTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \api\tests\UnitTester
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
                'dataFile' => Yii::getAlias('@common/tests/_data/user.php'),
            ],
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project.php'),
            ],
            'version' => [
                'class'    => VersionFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/version.php'),
            ],
            'screen' => [
                'class'    => ScreenFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/screen.php'),
            ],
            'comment' => [
                'class'    => ScreenCommentFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/screen_comment.php'),
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
        ]);
    }

    /**
     * Tests whether `ScreenCommentForm` constructor throw exception on passing invalid rel class.
     */
    public function testConstructor()
    {
        $this->specify('Wrong rel class', function() {
            $model = new ScreenCommentForm(new Version);
        }, ['throws' => new InvalidParamException]);

        $this->specify('Correct rel class', function() {
            $model1 = new ScreenCommentForm(new User);
            $model2 = new ScreenCommentForm(new Project);

            verify('No exception should be throwed');
        });
    }

    /**
     * `ScreenCommentForm::validateScreenId()` method test.
     */
    public function testValidateScreenId()
    {
        $rel = User::findOne(1002);

        $this->specify('Wrong or inaccessable screen id attempt', function() use ($rel) {
            $model = new ScreenCommentForm($rel, [
                'screenId' => 1003,
            ]);
            $model->validateScreenId('screenId', []);

            verify('Error message should be set', $model->errors)->hasKey('screenId');
        });

        $this->specify('Correct screen id attempt', function() use ($rel) {
            $model = new ScreenCommentForm($rel, [
                'screenId' => 1001,
            ]);
            $model->validateScreenId('screenId', []);

            verify('Error message should not be set', $model->errors)->hasntKey('screenId');
        });
    }

    /**
     * `ScreenCommentForm::validateReplyTo()` method test.
     */
    public function testValidateReplyTo()
    {
        $rel = Project::findOne(1001);

        $this->specify('Wrong or inaccessable reply id attempt', function() use ($rel) {
            $model = new ScreenCommentForm($rel, [
                'replyTo' => 1004,
            ]);
            $model->validateReplyTo('replyTo', []);

            verify('Error message should be set', $model->errors)->hasKey('replyTo');
        });

        $this->specify('Correct reply id attempt', function() use ($rel) {
            $model = new ScreenCommentForm($rel, [
                'replyTo' => 1001,
            ]);
            $model->validateReplyTo('replyTo', []);

            verify('Error message should not be set', $model->errors)->hasntKey('replyTo');
        });
    }

    /**
     * Tests whether `ScreenCommentForm::save()` creates a valid
     * ScreenComment model from User rel model.
     */
    public function testCreateViaUser()
    {
        $user = User::findOne(1002);

        $this->specify('Error create attempt', function() use ($user) {
            $model = new ScreenCommentForm($user, [
                'screenId' => 1003,
                'replyTo'  => 1,
                'message'  => '',
                'from'     => 'invalid_email@', // should not affect
                'posX'     => null,
                'posY'     => null,
            ]);
            $model->scenario = ScreenCommentForm::SCENARIO_USER;
            $result = $model->save();

            verify('Model should not return ScreenComment', $result)->null();
            verify('Model screenId error message should be set', $model->errors)->hasKey('screenId');
            verify('Model message error message should be set', $model->errors)->hasKey('message');
            verify('Model replyTo error message should be set', $model->errors)->hasKey('replyTo');
            verify('Model posX error message should not be set', $model->errors)->hasntKey('posX');
            verify('Model posY error message should not be set', $model->errors)->hasntKey('posY');
            verify('Model email error message should not be set', $model->errors)->hasntKey('from');
        });

        $this->specify('Success create primary comment attempt', function() use ($user) {
            $model = new ScreenCommentForm($user, [
                'screenId' => 1001,
                'message'  => 'Test message',
                'from'     => 'invalid_email@', // should not affect
                'posX'     => 0,
                'posY'     => 10,
            ]);
            $model->scenario = ScreenCommentForm::SCENARIO_USER;
            $result = $model->save();

            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('Model should return ScreenComment', $result)->isInstanceOf(ScreenComment::className());
            verify('Comment screenId should match', $result->screenId)->equals(1001);
            verify('Comment message should match', $result->message)->equals('Test message');
            verify('Comment posX should match', $result->posX)->equals(0);
            verify('Comment posY should match', $result->posY)->equals(10);
            verify('Comment from should match with the user email', $result->from)->equals($user->email);
        });

        $this->specify('Success create reply comment attempt', function() use ($user) {
            $primaryComment = ScreenComment::findOne(1001);
            $model = new ScreenCommentForm($user, [
                'screenId' => 1001,
                'replyTo'  => $primaryComment->id,
                'message'  => 'Test message',
                'from'     => 'invalid_email@', // should not affect
                'posX'     => 0,
                'posY'     => 10,
            ]);
            $model->scenario = ScreenCommentForm::SCENARIO_USER;
            $result = $model->save();

            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('Model should return ScreenComment', $result)->isInstanceOf(ScreenComment::className());
            verify('Comment screenId should match', $result->screenId)->equals(1001);
            verify('Comment message should match', $result->message)->equals('Test message');
            verify('Comment posX should match with the primary comment', $result->posX)->equals($primaryComment->posX);
            verify('Comment posY should match with the primary comment', $result->posY)->equals($primaryComment->posY);
            verify('Comment from should match with the user email', $result->from)->equals($user->email);
        });
    }

    /**
     * Tests whether `ScreenCommentForm::save()` creates a valid
     * ScreenComment model from Project rel model.
     */
    public function testCreateViaProject()
    {
        $project = Project::findOne(1001);

        $this->specify('Error create attempt', function() use ($project) {
            $model = new ScreenCommentForm($project, [
                'screenId' => 1003,
                'replyTo'  => 1,
                'message'  => '',
                'from'     => 'invalid_email@',
                'posX'     => null,
                'posY'     => null,
            ]);
            $model->scenario = ScreenCommentForm::SCENARIO_PREVIEW;
            $result = $model->save();

            verify('Model should not return ScreenComment', $result)->null();
            verify('Model screenId error message should be set', $model->errors)->hasKey('screenId');
            verify('Model message error message should be set', $model->errors)->hasKey('message');
            verify('Model replyTo error message should be set', $model->errors)->hasKey('replyTo');
            verify('Model email error message should be set', $model->errors)->hasKey('from');
            verify('Model posX error message should not be set', $model->errors)->hasntKey('posX');
            verify('Model posY error message should not be set', $model->errors)->hasntKey('posY');
        });

        $this->specify('Success create primary comment attempt', function() use ($project) {
            $model = new ScreenCommentForm($project, [
                'screenId' => 1001,
                'message'  => 'Test message',
                'from'     => 'test@presentator.io',
                'posX'     => 0,
                'posY'     => 10,
            ]);
            $model->scenario = ScreenCommentForm::SCENARIO_PREVIEW;
            $result = $model->save();

            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('Model should return ScreenComment', $result)->isInstanceOf(ScreenComment::className());
            verify('Comment screenId should match', $result->screenId)->equals(1001);
            verify('Comment message should match', $result->message)->equals('Test message');
            verify('Comment posX should match', $result->posX)->equals(0);
            verify('Comment posY should match', $result->posY)->equals(10);
            verify('Comment from should match', $result->from)->equals('test@presentator.io');
        });

        $this->specify('Success create reply comment attempt', function() use ($project) {
            $primaryComment = ScreenComment::findOne(1001);
            $model = new ScreenCommentForm($project, [
                'screenId' => 1001,
                'replyTo'  => $primaryComment->id,
                'message'  => 'Test message',
                'from'     => 'test@presentator.io',
                'posX'     => 0,
                'posY'     => 10,
            ]);
            $model->scenario = ScreenCommentForm::SCENARIO_PREVIEW;
            $result = $model->save();

            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('Model should return ScreenComment', $result)->isInstanceOf(ScreenComment::className());
            verify('Comment screenId should match', $result->screenId)->equals(1001);
            verify('Comment message should match', $result->message)->equals('Test message');
            verify('Comment from should match', $result->from)->equals('test@presentator.io');
            verify('Comment posX should match with the primary comment', $result->posX)->equals($primaryComment->posX);
            verify('Comment posY should match with the primary comment', $result->posY)->equals($primaryComment->posY);
        });
    }

    /**
     * `ScreenCommentForm::updatePosition()` method test.
     */
    public function testUpdatePosition()
    {
        $rel     = User::findOne(1001);
        $comment = ScreenComment::findOne(1001);

        $this->specify('Error position update attempt', function() use ($rel, $comment) {
            $model = new ScreenCommentForm($rel, [
                'scenario' => ScreenCommentForm::SCENARIO_POSITION_UPDATE,
                'posX'     => 'invalid_value',
                'posY'     => -100,
            ]);

            $result = $model->updatePosition($comment);

            verify('Model should not update', $result)->false();
            verify('posX error message should be set', $model->errors)->hasKey('posX');
            verify('posY error message should be set', $model->errors)->hasKey('posY');
        });

        $this->specify('Success position update attempt', function() use ($rel, $comment) {
            $model = new ScreenCommentForm($rel, [
                'scenario' => ScreenCommentForm::SCENARIO_POSITION_UPDATE,
                'posX'     => 15,
                'posY'     => 0,
            ]);

            $result = $model->updatePosition($comment);

            $comment->refresh();

            verify('Model should update successfully', $result)->true();
            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('Comment posX should match', $comment->posX)->equals(15);
            verify('Comment posY should match', $comment->posY)->equals(0);
        });
    }

    /**
     * `ScreenCommentForm::updateStatus()` method test.
     */
    public function testUpdateStatus()
    {
        $rel     = User::findOne(1001);
        $comment = ScreenComment::findOne([
            'id'     => 1001,
            'status' => ScreenComment::STATUS_PENDING
        ]);

        $this->specify('Error status update attempt', function() use ($rel, $comment) {
            $model = new ScreenCommentForm($rel, [
                'scenario' => ScreenCommentForm::SCENARIO_STATUS_UPDATE,
                'status'   => -1,
            ]);

            $result = $model->updateStatus($comment);

            verify('Model should not update', $result)->false();
            verify('status error message should be set', $model->errors)->hasKey('status');
        });

        $this->specify('Success status update attempt', function() use ($rel, $comment) {
            $model = new ScreenCommentForm($rel, [
                'scenario' => ScreenCommentForm::SCENARIO_STATUS_UPDATE,
                'status'   => ScreenComment::STATUS_RESOLVED,
            ]);

            $result = $model->updateStatus($comment);

            $comment->refresh();

            verify('Model should update successfully', $result)->true();
            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('Comment status should match', $comment->status)->equals(ScreenComment::STATUS_RESOLVED);
        });
    }
}
