<?php
namespace common\tests\unit\models;

use yii\db\ActiveQuery;
use common\models\User;
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
 * UserScreenCommentRel AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserScreenCommentRelTest extends \Codeception\Test\Unit
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
            'screenComment' => [
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

    /**
     * `UserProjectRel::getUser()` relation query method test.
     */
    public function testGetUser()
    {
        $model = UserScreenCommentRel::findOne(1001);
        $query = $model->getUser();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid User model', $model->user)->isInstanceOf(User::className());
        verify('Query result user id should match', $model->user->id)->equals($model->userId);
    }

    /**
     * `UserProjectRel::getComment()` relation query method test.
     */
    public function testGetComment()
    {
        $model = UserScreenCommentRel::findOne(1001);
        $query = $model->getComment();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid ScreenComment model', $model->comment)->isInstanceOf(ScreenComment::className());
        verify('Query result comment id should match', $model->comment->id)->equals($model->screenCommentId);
    }
}
