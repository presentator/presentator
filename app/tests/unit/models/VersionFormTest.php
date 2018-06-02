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
        $this->specify('Set Project model via VersionForm constructor', function () {
            $project = Project::findOne(1001);
            $model   = new VersionForm($project);

            verify('Model project should return instance of Project', $model->project)->isInstanceOf(Project::className());
            verify('Model project id should match', $model->project->id)->equals($project->id);
        });

        $this->specify('Set Version model via VersionForm constructor', function () {
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
     * `VersionForm::validateSubtypeRange()` method test.
     */
    public function testValidateSubtypeRange()
    {
        $project = Project::findOne(1001);

        $this->specify('Tablet/Mobile error attempt', function () use ($project) {
            $model = new VersionForm($project, null, [
                'type'    => Version::TYPE_TABLET,
                'subtype' => 31, // mismatch with mobile subtype
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Subtype error message should be set', $model->errors)->hasKey('subtype');
        });

        $this->specify('Tablet/Mobile success attempt', function () use ($project) {
            $model = new VersionForm($project, null, [
                'type'    => Version::TYPE_TABLET,
                'subtype' => 21,
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Subtype error message should not be set', $model->errors)->hasntKey('subtype');
        });

        $this->specify('Desktop success attempt', function () use ($project) {
            $model = new VersionForm($project, null, [
                'type' => Version::TYPE_DESKTOP, // doesn't require subtype
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Subtype error message should not be set', $model->errors)->hasntKey('subtype');
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
        $project   = Project::findOne(1001);
        $model     = new VersionForm($project);
        $scenarios = [
            ['versionId' => 1001, 'expectedAutoScale' => false, 'expectedRetinaScale' => true],
            ['versionId' => 1004, 'expectedAutoScale' => true, 'expectedRetinaScale' => false],
            ['versionId' => 1006, 'expectedAutoScale' => false, 'expectedRetinaScale' => false],
        ];

        foreach ($scenarios as $i => $scenario) {
            $this->specify('Load version model for scenario ' . $i, function () use ($project, $scenario) {
                $model   = new VersionForm($project);
                $version = Version::findOne($scenario['versionId']);

                $model->setVersion($version);

                verify('Model version should return instance of Version', $model->version)->isInstanceOf(Version::className());
                verify('Model version id should match', $model->version->id)->equals($version->id);
                verify('Model title should match', $model->title)->equals($version->title);
                verify('Model type should match', $model->type)->equals($version->type);
                verify('Model subtype should match', $model->subtype)->equals($version->subtype);
                verify('Model autoScale should match', $model->autoScale)->equals($scenario['expectedAutoScale']);
                verify('Model retinaScale should match', $model->retinaScale)->equals($scenario['expectedRetinaScale']);
            });
        }
    }

    /**
     * `VersionForm::getVersion()` method test.
     */
    public function testGetVersion()
    {
        $project = Project::findOne(1001);
        $version = Version::findOne(1001);
        $model   = new VersionForm($project, $version);
        $result  = $model->getVersion();

        verify('Model version should return instance of Version', $result)->isInstanceOf(Version::className());
        verify('Model version should be the same as the loaded one', $result->id)->equals($version->id);
    }

    /**
     * `VersionForm::isUpdate()` method test.
     */
    public function testIsUpdate()
    {
        $this->specify('Is update form', function () {
            $project = Project::findOne(1001);
            $version = Version::findOne(1001);
            $model   = new VersionForm($project, $version);

            verify($model->isUpdate())->true();
        });

        $this->specify('Is create form', function () {
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

        $this->specify('Error create attempt', function () use ($project, $oldVersionsCount) {
            $model = new VersionForm($project, null, [
                'title'       => 'Some very long title...Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam dignissim, lorem in bibendum.',
                'type'        => 0,
                'subtype'     => 0,
                'retinaScale' => 'invalid_value',
                'autoScale'   => 'invalid_value',
            ]);

            $result = $model->save();

            verify('Model should not save', $result)->false();
            verify('Model should have errors', $model->errors)->notEmpty();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Type error message should be set', $model->errors)->hasKey('type');
            verify('Subtype error message should not be set because not valid type is set', $model->errors)->hasntKey('subtype');
            verify('AutoScale error message should be set', $model->errors)->hasKey('autoScale');
            verify('RetinaScale error message should be set', $model->errors)->hasKey('retinaScale');
            verify('Project versions count should not change', $project->getVersions()->count())->equals($oldVersionsCount);
        });

        $this->specify('Success create attempt', function () use ($project, $oldVersionsCount) {
            $model = new VersionForm($project, null, [
                'title'       => 'My new test version title',
                'type'        => Version::TYPE_DESKTOP,
                'autoScale'   => true,
                'retinaScale' => true,
            ]);

            $result  = $model->save();
            $version = $model->getVersion();

            verify('Model should save', $result)->true();
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Project versions count should increased', $project->getVersions()->count())->equals($oldVersionsCount + 1);
            verify('Model version should be instance of Version', $version)->isInstanceOf(Version::className());
            verify('Version title should match', $version->title)->equals('My new test version title');
            verify('Version type should be set', $version->type)->equals(Version::TYPE_DESKTOP);
            verify('Version scaleFactor should be set', $version->scaleFactor)->equals(Version::RETINA_SCALE_FACTOR);
            verify('Version subtype should not be set', $version->subtype)->null();
        });
    }

    /**
     * `VersionForm::save()` method test to update a Version model.
     */
    public function testSaveUpdate()
    {
        $project = Project::findOne(1001);
        $version = Version::findOne(1001);

        $this->specify('Error update attempt', function () use ($project, $version) {
            $data = [
                'title'       => 'Some very long title...Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam dignissim, lorem in bibendum.',
                'type'        => Version::TYPE_TABLET,
                'subtype'     => 31,
                'retinaScale' => false,
                'autoScale'   => 'invalid_value',
            ];
            $model = new VersionForm($project, $version, $data);

            $result = $model->save();
            $version->refresh();

            verify('Model should not save', $result)->false();
            verify('Model should have errors', $model->errors)->notEmpty();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Type error message should not be set', $model->errors)->hasntKey('type');
            verify('Subtype error message should be set', $model->errors)->hasKey('subtype');
            verify('AutoScale error message should be set', $model->errors)->hasKey('autoScale');
            verify('RetinaScale error message should not be set', $model->errors)->hasntKey('retinaScale');
            verify('Version title should not change', $version->title)->notEquals($data['title']);
            verify('Version type should not be changed', $version->type)->notEquals($data['type']);
            verify('Version subtype should not be changed', $version->subtype)->notEquals($data['subtype']);
        });

        $this->specify('Success update attempt', function () use ($project, $version) {
            $data = [
                'title'       => 'My new test version title',
                'type'        => Version::TYPE_MOBILE,
                'subtype'     => 31,
                'retinaScale' => true,
                'autoScale'   => true,
            ];
            $model = new VersionForm($project, $version, $data);

            $result = $model->save();
            $version->refresh();

            verify('Model should save', $result)->true();
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Version title should match', $version->title)->equals($data['title']);
            verify('Version type should match', $version->type)->equals($data['type']);
            verify('Version subtype should match', $version->subtype)->equals($data['subtype']);
            verify('Version scaleFactor should match', $version->scaleFactor)->equals(Version::AUTO_SCALE_FACTOR);
        });
    }
}
