<?php
namespace common\tests\unit\models;

use yii\db\ActiveQuery;
use common\models\Screen;
use common\models\Version;
use common\models\Project;
use common\models\ScreenComment;
use common\components\helpers\CFileHelper;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\ScreenCommentFixture;

/**
 * Screen AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenTest extends \Codeception\Test\Unit
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
        ]);
    }

    /**
     * `Screen::getScreenComments()` relation query method test.
     */
    public function testGetScreenComments()
    {
        $this->specify('Screen WITHOUT related ScreenComment models', function() {
            $model = Screen::findOne(1002);
            $query = $model->getScreenComments();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $model->screenComments)->count(0);
        });

        $this->specify('Screen WITH related ScreenComment models', function() {
            $model = Screen::findOne(1001);
            $query = $model->getScreenComments();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $model->screenComments)->notEmpty();
            foreach ($model->screenComments as $comment) {
                verify('Query result item should be valid ScreenComment model', $comment)->isInstanceOf(ScreenComment::className());
                verify('Query result item screenId should match', $comment->screenId)->equals($model->id);
            }
        });
    }

    /**
     * `Screen::getPrimaryScreenComments()` relation query method test.
     */
    public function testGetPrimaryScreenComments()
    {
        $this->specify('Screen WITHOUT related primary ScreenComment models', function() {
            $model = Screen::findOne(1002);
            $query = $model->getPrimaryScreenComments();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $model->primaryScreenComments)->count(0);
        });

        $this->specify('Screen WITH related primary ScreenComment models', function() {
            $model = Screen::findOne(1001);
            $query = $model->getPrimaryScreenComments();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $model->primaryScreenComments)->notEmpty();
            foreach ($model->primaryScreenComments as $comment) {
                verify('Query result item should be valid ScreenComment model', $comment)->isInstanceOf(ScreenComment::className());
                verify('Query result item screenId should match', $comment->screenId)->equals($model->id);
                verify('Query result item replyTo should not be set', $comment->replyTo)->null();
            }
        });
    }

    /**
     * `Screen::getVersion()` relation query method test.
     */
    public function testGetVersion()
    {
        $model = Screen::findOne(1001);
        $query = $model->getVersion();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid Version model', $model->version)->isInstanceOf(Version::className());
        verify('Query result version id should match', $model->version->id)->equals($model->versionId);
    }

    /**
     * `Screen::getProject()` relation query method test.
     */
    public function testGetProject()
    {
        $model = Screen::findOne(1001);
        $query = $model->getProject();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid Project model', $model->project)->isInstanceOf(Project::className());
        verify('Query result project id should match', $model->project->id)->equals(1001);
    }

    /**
     * `Screen::getAlignmentLabels()` method test.
     */
    public function testGetAlignmentLabels()
    {
        $labels = Screen::getAlignmentLabels();

        verify('Should have 3 labels', count($labels))->equals(3);
        verify('Center alignment key should be set', $labels)->hasKey(Screen::ALIGNMENT_CENTER);
        verify('Left alignment key should be set', $labels)->hasKey(Screen::ALIGNMENT_LEFT);
        verify('Right alignment key should be set', $labels)->hasKey(Screen::ALIGNMENT_RIGHT);
    }

    /**
     * `Screen::getThumbPath()` method test.
     */
    public function testGetThumbPath()
    {
        $thumbName = 'small';
        $screen    = Screen::findOne(1001);
        $path      = $screen->getThumbPath($thumbName);

        verify('Should not be empty', $path)->notEmpty();
        verify('Should contains the thumb name', $path)->contains('_thumb_' . $thumbName);
    }

    /**
     * `Screen::getThumbUrl()` method test.
     */
    public function testGetThumbUrl()
    {
        $thumbName = 'small';
        $screen    = Screen::findOne(1001);
        $path      = $screen->getThumbPath($thumbName);
        $url       = $screen->getThumbUrl($thumbName, false);

        verify('Should not be empty', $url)->notEmpty();
        verify('Should be the same as thumb path', CFileHelper::getPathFromUrl($url))->equals($path);
    }
}
