<?php
namespace common\tests\unit\models;

use Yii;
use yii\helpers\Html;
use yii\db\ActiveQuery;
use common\models\Project;
use common\models\ProjectPreview;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\ProjectPreviewFixture;
use common\tests\fixtures\UserProjectRelFixture;

/**
 * ProjectPreview AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectPreviewTest extends \Codeception\Test\Unit
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
            'preview' => [
                'class'    => ProjectPreviewFixture::className(),
                'dataFile' => codecept_data_dir() . 'project_preview.php',
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_project_rel.php',
            ],
        ]);
    }

    /**
     * `ProjectPreview::getProject()` relation query method test.
     */
    public function testGetProject()
    {
        $model = ProjectPreview::findOne(1001);
        $query = $model->getProject();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid Project model', $model->project)->isInstanceOf(Project::className());
        verify('Query result project id should match', $model->project->id)->equals($model->projectId);
    }

    /**
     * `ProjectPreview::getTypeLabels()` method test.
     */
    public function testGetTypeLabels()
    {
        $labels = ProjectPreview::getTypeLabels();

        verify('"View only" type should be set', $labels)->hasKey(ProjectPreview::TYPE_VIEW);
        verify('"View and comment" type should be set', $labels)->hasKey(ProjectPreview::TYPE_VIEW_AND_COMMENT);
    }

    /**
     * `ProjectPreview::generateSlug()` method test.
     */
    public function testGenerateSlug()
    {
        $model = new ProjectPreview;
        $model->generateSlug(10);

        verify('Slug should be set', $model->slug)->notNull();
        verify('Length should match', strlen($model->slug))->equals(10);
    }

    /**
     * `ProjectPreview::checkSlugExist()` method test.
     */
    public function testCheckSlugExist()
    {
        verify('Slug should not exist', ProjectPreview::checkSlugExist('qwerty123456'))->false();
        verify('Slug should exist', ProjectPreview::checkSlugExist('pLIRe9su'))->true();
    }

    /**
     * `ProjectPreview::findOneBySlug()` method test.
     */
    public function testFindOneBySlug()
    {
        $this->specify('Non existing ProjectPreview slug', function() {
            $model = ProjectPreview::findOneBySlug('qwerty123456');

            verify('Model should not exist', $model)->null();
        });

        $this->specify('Existing ProjectPreview slug', function() {
            $slug  = 'pLIRe9su';
            $model = ProjectPreview::findOneBySlug($slug);

            verify('Should be valid ProjectPreview model', $model)->isInstanceOf(ProjectPreview::className());
            verify('Model slug should match', $model->slug)->equals($slug);
        });
    }

    /**
     * `ProjectPreview::sendPreviewEmail()` method test.
     */
    public function testSendPreviewEmail()
    {
        $to          = 'test123@presentator.io';
        $userMessage = 'MY_DEMO_MESSAGE';
        $model       = ProjectPreview::findOne(1001);
        $result      = $model->sendPreviewEmail($to, $userMessage);

        $this->tester->seeEmailIsSent();
        $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
        $body = current($message->getChildren())->getBody();

        verify('Mail method should succeed', $result)->true();
        verify('Receiver email should match', $message->getTo())->hasKey($to);
        verify('Body should contains a preview url', $body)->contains(
            Html::encode(Yii::$app->mainUrlManager->createUrl(['preview/view', 'slug' => $model->slug], true))
        );
        verify('Body should contains the user message', $body)->contains($userMessage);
    }
}
