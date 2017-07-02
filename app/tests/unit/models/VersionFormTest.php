<?php
namespace app\tests\models;

use Yii;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\models\Project;
use common\models\Version;
use app\models\VersionForm;

/**
 * VersionForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionFormTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \app\tests\UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    public function _before()
    {
        $this->tester->haveFixtures([
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project.php'),
            ],
            'version' => [
                'class'    => VersionFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/version.php'),
            ],
        ]);
    }

    /**
     * `VersionForm::__constructor()` method test.
     */
    public function testConstruct()
    {
        $this->specify('Set Project model via VersionForm constructor', function() {
            $project = Project::findOne(1001);
            $model   = new VersionForm($project);

            verify('Model project should return instance of Project', $model->project)->isInstanceOf(Project::className());
            verify('Model project id should match', $model->project->id)->equals($project->id);
        });

        $this->specify('Set Version model via VersionForm constructor', function() {
            $project = Project::findOne(1001);
            $version = Version::findOne(1002);
            $model   = new VersionForm($project, $version);

            verify('Model project should return instance of Project', $model->project)->isInstanceOf(Project::className());
            verify('Model project id should match', $model->project->id)->equals($project->id);
            verify('Model version should return instance of Version', $model->version)->isInstanceOf(Version::className());
            verify('Model version id should match', $model->version->id)->equals($version->id);
        });
    }

    /**
     * `VersionForm::setProject()` method test.
     */
    public function testSetProject()
    {
        $project1 = Project::findOne(1001);
        $project2 = Project::findOne(1002);
        $model    = new VersionForm($project1);

        $model->setProject($project2);

        verify('Model version return instance of Project', $model->project)->isInstanceOf(Project::className());
        verify('Model project id should match', $model->project->id)->equals($project2->id);
    }

    /**
     * `VersionForm::getProject()` method test.
     */
    public function testGetProject()
    {
        $project = Project::findOne(1002);
        $model   = new VersionForm($project);

        verify('Model version return instance of Project', $model->getProject())->isInstanceOf(Project::className());
        verify('Model project id should match', $model->getProject()->id)->equals($project->id);
    }

    /**
     * `VersionForm::setVersion()` method test.
     */
    public function testSetVersion()
    {
        $project = Project::findOne(1001);
        $version = Version::findOne(1001);
        $model   = new VersionForm($project);

        $model->setVersion($version);

        verify('Model version should return instance of Version', $model->version)->isInstanceOf(Version::className());
        verify('Model version id should match', $model->version->id)->equals($version->id);
        verify('Model title should match with the Version one', $model->title)->equals($version->title);
    }

    /**
     * `VersionForm::getVersion()` method test.
     */
    public function testGetVersion()
    {
        $project = Project::findOne(1001);
        $version = Version::findOne(1001);
        $model   = new VersionForm($project, $version);

        verify('Model version should return instance of Version', $model->getVersion())->isInstanceOf(Version::className());
        verify('Model version id should match', $model->getVersion()->id)->equals($version->id);
        verify('Model title should match with the Version one', $model->title)->equals($version->title);
    }

    /**
     * `VersionForm::isUpdate()` method test.
     */
    public function testIsUpdate()
    {
        $this->specify('Is update form', function() {
            $project = Project::findOne(1001);
            $version = Version::findOne(1001);
            $model   = new VersionForm($project, $version);

            verify($model->isUpdate())->true();
        });

        $this->specify('Is create form', function() {
            $project = Project::findOne(1001);
            $model   = new VersionForm($project);

            verify($model->isUpdate())->false();
        });
    }

    /**
     * `VersionForm::save()` method test to create a Version model.
     */
    public function testSaveCreate()
    {
        $project          = Project::findOne(1001);
        $oldVersionsCount = $project->getVersions()->count();

        $this->specify('Error create attempt', function() use ($project, $oldVersionsCount) {
            $model = new VersionForm($project, null, [
                'title' => 'Some very long title...Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam dignissim, lorem in bibendum.',
            ]);

            $result = $model->save();

            verify('Model should not save', $result)->false();
            verify('Model should have errors', $model->errors)->notEmpty();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Project versions count should not change', $project->getVersions()->count())->equals($oldVersionsCount);
        });

        $this->specify('Success create attempt', function() use ($project, $oldVersionsCount) {
            $model = new VersionForm($project, null, [
                'title' => 'My new test version title',
            ]);

            $result  = $model->save();
            $version = $model->getVersion();

            verify('Model should save', $result)->true();
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Project versions count should increased', $project->getVersions()->count())->equals($oldVersionsCount + 1);
            verify('Model version should be instance of Version', $version)->isInstanceOf(Version::className());
            verify('Version title should match', $version->title)->equals('My new test version title');
        });
    }

    /**
     * `VersionForm::save()` method test to update a Version model.
     */
    public function testSaveUpdate()
    {
        $project         = Project::findOne(1001);
        $version         = Version::findOne(1001);
        $oldVersionTitle = $version->title;

        $this->specify('Error update attempt', function() use ($project, $version, $oldVersionTitle) {
            $model = new VersionForm($project, $version, [
                'title' => 'Some very long title...Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam dignissim, lorem in bibendum.',
            ]);

            $result = $model->save();
            $version->refresh();

            verify('Model should not save', $result)->false();
            verify('Model should have errors', $model->errors)->notEmpty();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Version title should not change', $version->title)->equals($oldVersionTitle);
        });

        $this->specify('Success update attempt', function() use ($project, $version) {
            $model = new VersionForm($project, $version, [
                'title' => 'My new test version title',
            ]);

            $result = $model->save();
            $version->refresh();

            verify('Model should save', $result)->true();
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Version title should match', $version->title)->equals('My new test version title');
        });
    }
}
