<?php
namespace app\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\User;
use common\models\Project;
use app\models\ProjectForm;

/**
 * ProjectForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectFormTest extends \Codeception\Test\Unit
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
            'user' => [
                'class'    => UserFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user.php'),
            ],
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project.php'),
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
        ]);
    }

    /**
     * `ProjectForm::validateSubtypeRange()` method test.
     */
    public function testValidateSubtypeRange()
    {
        $project = Project::findOne(1001);

        $this->specify('Tablet/Mobile error attempt', function() use ($project) {
            $model = new ProjectForm($project, [
                'type'    => Project::TYPE_TABLET,
                'subtype' => 31, // mismatch with mobile subtype
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Error message should be set', $model->errors)->hasKey('subtype');
        });

        $this->specify('Tablet/Mobile correct attempt', function() use ($project) {
            $model = new ProjectForm($project, [
                'type'    => Project::TYPE_TABLET,
                'subtype' => 21,
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Error message should not be set', $model->errors)->hasntKey('subtype');
        });

        $this->specify('Desktop correct attempt', function() use ($project) {
            $model = new ProjectForm($project, [
                'type' => Project::TYPE_DESKTOP, // doesn't require subtype
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Error message should not be set', $model->errors)->hasntKey('subtype');
        });
    }

    /**
     * `ProjectForm::loadProject()` method test.
     */
    public function testLoadProject()
    {
        $this->specify('Load password UNPROTECTED project', function() {
            $model   = new ProjectForm();
            $project = Project::findOne(1001);
            $model->loadProject($project);

            verify('Model title should match', $model->title)->equals('Lorem ipsum title');
            verify('Model type should match', $model->type)->equals(Project::TYPE_DESKTOP);
            verify('Model subtype should be null', $model->subtype)->null();
            verify('Model autoScale should be false', $model->autoScale)->false();
            verify('Model retinaScale should be true', $model->retinaScale)->true();
            verify('Model should not be password protected', $model->isPasswordProtected)->false();
        });

        $this->specify('Load password PROTECTED project', function() {
            $model   = new ProjectForm();
            $project = Project::findOne(1002);
            $model->loadProject($project);

            verify('Model title should match', $model->title)->equals('Lorem ipsum title');
            verify('Model type should match', $model->type)->equals(Project::TYPE_TABLET);
            verify('Model subtype should match', $model->subtype)->equals(21);
            verify('Model autoScale should be false', $model->autoScale)->false();
            verify('Model retinaScale should be false', $model->retinaScale)->false();
            verify('Model should be password protected', $model->isPasswordProtected)->true();
        });
    }

    /**
     * Tests whether `ProjectForm::save()` CREATES a valid Project model.
     */
    public function testSaveCreate()
    {
        $user = User::findOne(1002);

        $this->specify('Error create attempt', function() use ($user) {
            $model = new ProjectForm(null, [
                'type'                => 0,
                'subtype'             => 0,
                'isPasswordProtected' => true,
                'retinaScale'         => 'invalid_value',
                'autoScale'           => 'invalid_value',
            ]);

            $result = $model->save($user);

            verify('Model should not save', $result)->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Type error message should be set', $model->errors)->hasKey('type');
            verify('Subtype error message should not be set because not valid type is set', $model->errors)->hasntKey('subtype');
            verify('Password error message should be set', $model->errors)->hasKey('password');
            verify('AutoScale error message should be set', $model->errors)->hasKey('autoScale');
            verify('RetinaScale error message should be set', $model->errors)->hasKey('retinaScale');
        });

        $this->specify('Success create attempt', function() use ($user) {
            $model = new ProjectForm(null, [
                'title'               => 'My new project title',
                'type'                => Project::TYPE_DESKTOP,
                'isPasswordProtected' => true,
                'password'            => '123456',
                'autoScale'           => true,
                'retinaScale'         => true,
            ]);

            $result = $model->save($user);

            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Model should return instance of Project', $result)->isInstanceOf(Project::className());
            verify('Project title should be set', $result->title)->equals('My new project title');
            verify('Project type should be set', $result->type)->equals(Project::TYPE_DESKTOP);
            verify('Project scaleFactor should be set', $result->scaleFactor)->equals(Project::RETINA_SCALE_FACTOR);
            verify('Project subtype should not be set', $result->subtype)->null();
            verify('Project passwordHash should be set', $result->passwordHash)->notEmpty();
        });
    }

    /**
     * Tests whether `ProjectForm::save()` UPDATES an existing Project model.
     */
    public function testSaveUpdate()
    {
        $project = Project::findOne(1002);

        $this->specify('Error update attempt', function() use ($project) {
            $model = new ProjectForm($project, [
                'title'               => '',
                'type'                => Project::TYPE_TABLET,
                'subtype'             => 31,
                'isPasswordProtected' => true,
                'changePassword'      => true,
                'retinaScale'         => false,
                'autoScale'           => 'invalid_value',
            ]);

            $result = $model->save();

            $project->refresh();

            verify('Model should not save', $result)->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Type error message should not be set', $model->errors)->hasntKey('type');
            verify('Subtype error message should be set', $model->errors)->hasKey('subtype');
            verify('Password error message should be set', $model->errors)->hasKey('password');
            verify('AutoScale error message should be set', $model->errors)->hasKey('autoScale');
            verify('RetinaScale error message should not be set', $model->errors)->hasntKey('retinaScale');
            verify('Project title should not be changed', $project->title)->equals('Lorem ipsum title');
            verify('Project subtype should not be changed', $project->subtype)->notEquals(31);
        });

        $this->specify('Success update attempt', function() use ($project) {
            $model = new ProjectForm($project, [
                'title'               => 'My new project title',
                'type'                => Project::TYPE_MOBILE,
                'subtype'             => 31,
                'isPasswordProtected' => false,
                'retinaScale'         => true,
                'autoScale'           => true,
            ]);

            $result = $model->save();

            $project->refresh();

            verify('Model should returns instance of Project', $result)->isInstanceOf(Project::className());
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Project title should be changed', $project->title)->equals('My new project title');
            verify('Project scaleFactor should be set', $result->scaleFactor)->equals(Project::AUTO_SCALE_FACTOR);
            verify('Project type should be changed', $project->type)->equals(Project::TYPE_MOBILE);
            verify('Project subtype should be changed', $project->subtype)->equals(31);
            verify('Project passwordHash should not be set', $project->passwordHash)->isEmpty();
        });
    }
}
