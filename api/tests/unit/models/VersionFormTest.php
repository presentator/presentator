<?php
namespace api\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\User;
use common\models\Version;
use common\models\Project;
use api\models\VersionForm;

/**
 * VersionForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionFormTest extends \Codeception\Test\Unit
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
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
        ]);
    }

    /**
     * `VersionForm::validateUserProjectId()` method test.
     */
    public function testValidateUserProjectId()
    {
        $user = User::findOne(1002);

        $this->specify('Wrong project id attempt', function() use ($user) {
            $model = new VersionForm($user, [
                'projectId' => 1002,
            ]);

            $model->validateUserProjectId('projectId', []);

            verify('Error message should be set', $model->errors)->hasKey('projectId');
        });

        $this->specify('Correct project id attempt', function() use ($user) {
            $model = new VersionForm($user, [
                'projectId' => 1001,
            ]);

            $model->validateUserProjectId('projectId', []);

            verify('Error message should not be set', $model->errors)->hasntKey('projectId');
        });
    }

    /**
     * `VersionForm::validateSubtypeRange()` method test.
     */
    public function testValidateSubtypeRange()
    {
        $user = User::findOne(1002);

        $this->specify('Tablet/Mobile error attempt', function() use ($user) {
            $model = new VersionForm($user, [
                'type'    => Version::TYPE_TABLET,
                'subtype' => 31, // mismatch with mobile subtype
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Error message should be set', $model->errors)->hasKey('subtype');
        });

        $this->specify('Tablet/Mobile correct attempt', function() use ($user) {
            $model = new VersionForm($user, [
                'type'    => Version::TYPE_TABLET,
                'subtype' => 21,
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Error message should not be set', $model->errors)->hasntKey('subtype');
        });

        $this->specify('Desktop correct attempt', function() use ($user) {
            $model = new VersionForm($user, [
                'type' => Version::TYPE_DESKTOP, // doesn't require subtype
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Error message should not be set', $model->errors)->hasntKey('subtype');
        });
    }

    /**
     * Tests whether `VersionForm::save()` creates a valid Version model.
     */
    public function testSaveCreate()
    {
        $user = User::findOne(1002);

        $this->specify('Error create attempt', function() use ($user) {
            $project          = Project::findOne(1002); // not owned by the provided user
            $oldVersionsCount = $project->getVersions()->count();

            $model = new VersionForm($user, [
                'scenario'    => VersionForm::SCENARIO_CREATE,
                'projectId'   => $project->id,
                'title'       => 'Some very long title...Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam dignissim, lorem in bibendum.',
                'type'        => 0,
                'subtype'     => 0,
                'retinaScale' => 'invalid_value',
                'autoScale'   => 'invalid_value',
            ]);

            $result = $model->save();

            verify('Model should not succeed', $result)->null();
            verify('Error message should be set', $model->errors)->hasKey('projectId');
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Type error message should be set', $model->errors)->hasKey('type');
            verify('Subtype error message should not be set because not valid type is set', $model->errors)->hasntKey('subtype');
            verify('AutoScale error message should be set', $model->errors)->hasKey('autoScale');
            verify('RetinaScale error message should be set', $model->errors)->hasKey('retinaScale');
            verify('Project versions count should not change', $project->getVersions()->count())->equals($oldVersionsCount);
        });

        $this->specify('Success create attempt', function() use ($user) {
            $project          = Project::findOne(1001);
            $oldVersionsCount = $project->getVersions()->count();

            $model = new VersionForm($user, [
                'scenario'    => VersionForm::SCENARIO_CREATE,
                'projectId'   => $project->id,
                'title'       => 'My new test version title',
                'type'        => Version::TYPE_DESKTOP,
                'autoScale'   => true,
                'retinaScale' => true,
            ]);

            $result = $model->save();

            verify('Model should succeed and return an instance of Version', $result)->isInstanceOf(Version::className());
            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('Version projectId should match', $result->projectId)->equals(1001);
            verify('Project versions count should increased', $project->getVersions()->count())->equals($oldVersionsCount + 1);
            verify('Version title should match', $result->title)->equals('My new test version title');
            verify('Version type should be set', $result->type)->equals(Version::TYPE_DESKTOP);
            verify('Version scaleFactor should be set', $result->scaleFactor)->equals(Version::RETINA_SCALE_FACTOR);
            verify('Version subtype should not be set', $result->subtype)->null();
        });
    }

    /**
     * `VersionForm::save()` method test to update a Version model.
     */
    public function testSaveUpdate()
    {
        $user    = User::findOne(1002);
        $version = Version::findOne(1001);

        $this->specify('Error update attempt', function () use ($user, $version) {
            $data = [
                'scenario'    => VersionForm::SCENARIO_UPDATE,
                'title'       => 'Some very long title...Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam dignissim, lorem in bibendum.',
                'type'        => Version::TYPE_TABLET,
                'subtype'     => 31,
                'retinaScale' => false,
                'autoScale'   => 'invalid_value',
            ];
            $model = new VersionForm($user, $data);

            $result = $model->save($version);
            $version->refresh();

            verify('Model should not succeed', $result)->null();
            verify('Model should have errors', $model->errors)->notEmpty();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('ProjectId error message should not be set', $model->errors)->hasntKey('projectId');
            verify('Type error message should not be set', $model->errors)->hasntKey('type');
            verify('Subtype error message should be set', $model->errors)->hasKey('subtype');
            verify('AutoScale error message should be set', $model->errors)->hasKey('autoScale');
            verify('RetinaScale error message should not be set', $model->errors)->hasntKey('retinaScale');
            verify('Version title should not change', $version->title)->notEquals($data['title']);
            verify('Version type should not be changed', $version->type)->notEquals($data['type']);
            verify('Version subtype should not be changed', $version->subtype)->notEquals($data['subtype']);
        });

        $this->specify('Success update attempt', function () use ($user, $version) {
            $data = [
                'scenario'    => VersionForm::SCENARIO_UPDATE,
                'projectId'   => 1003, // should be ignored
                'title'       => 'My new test version title',
                'type'        => Version::TYPE_MOBILE,
                'subtype'     => 31,
                'retinaScale' => true,
                'autoScale'   => true,
            ];
            $model = new VersionForm($user, $data);

            $result = $model->save($version);

            verify('Model should succeed and return an instance of Version', $result)->isInstanceOf(Version::className());
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('The returned Version should be the same as the updated one', $result->id)->equals($version->id);
            verify('Version projectId should not change', $result->projectId)->notEquals($data['projectId']);
            verify('Version title should match', $result->title)->equals($data['title']);
            verify('Version type should match', $result->type)->equals($data['type']);
            verify('Version subtype should match', $result->subtype)->equals($data['subtype']);
            verify('Version scaleFactor should match', $result->scaleFactor)->equals(Version::AUTO_SCALE_FACTOR);
        });
    }
}
