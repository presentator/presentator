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
     * `ProjectForm::setProject()` method test.
     */
    public function testSetProject()
    {
        $this->specify('Load password UNPROTECTED project', function () {
            $model   = new ProjectForm();
            $project = Project::findOne(1001);
            $model->setProject($project);

            verify('Model title should match', $model->title)->equals('Lorem ipsum title');
            verify('Model should not be password protected', $model->isPasswordProtected)->false();
        });

        $this->specify('Load password PROTECTED project', function () {
            $model   = new ProjectForm();
            $project = Project::findOne(1002);
            $model->setProject($project);

            verify('Model title should match', $model->title)->equals('Lorem ipsum title');
            verify('Model should be password protected', $model->isPasswordProtected)->true();
        });
    }

    /**
     * `ProjectForm::getProject()` method test.
     */
    public function testGetProject()
    {
        $project = Project::findOne(1002);
        $model   = new ProjectForm($project);
        $result  = $model->getProject();

        verify('Result should be instance of Project', $result)->isInstanceOf(Project::className());
        verify('Loaded project id should match', $result->id)->equals($project->id);
    }

    /**
     * `ProjectForm::isUpdate()` method test.
     */
    public function testIsUpdate()
    {
        $this->specify('Is update form', function () {
            $project = Project::findOne(1001);
            $model = new ProjectForm($project);

            verify($model->isUpdate())->true();
        });

        $this->specify('Is create form', function () {
            $model   = new ProjectForm();

            verify($model->isUpdate())->false();
        });
    }

    /**
     * Tests whether `ProjectForm::save()` CREATES a valid Project model.
     */
    public function testSaveCreate()
    {
        $user = User::findOne(1002);

        $this->specify('Error create attempt', function () use ($user) {
            $model = new ProjectForm(null, [
                'isPasswordProtected' => true,
            ]);

            $totalUserProjects = $user->countProjects();

            $result = $model->save($user);

            verify('Method should not succeed', $result)->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Password error message should be set', $model->errors)->hasKey('password');
            verify('User projects count should not be changed', $user->countProjects())->equals($totalUserProjects);
        });

        $this->specify('Success create attempt', function () use ($user) {
            $model = new ProjectForm(null, [
                'title'               => 'My new project title',
                'isPasswordProtected' => true,
                'password'            => '123456',
            ]);

            $result = $model->save($user);

            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Method should succeed and return instance of Project', $result)->isInstanceOf(Project::className());
            verify('Project title should be set', $result->title)->equals('My new project title');
            verify('Project passwordHash should be set', $result->passwordHash)->notEmpty();
            verify('Project should be linked with the provided user', $user->findProjectById($result->id))->notEmpty();
        });
    }

    /**
     * Tests whether `ProjectForm::save()` UPDATES an existing Project model.
     */
    public function testSaveUpdate()
    {
        $project = Project::findOne(1002);

        $this->specify('Error update attempt', function () use ($project) {
            $model = new ProjectForm($project, [
                'title'               => '',
                'isPasswordProtected' => true,
                'changePassword'      => true,
            ]);

            $result = $model->save();

            $project->refresh();

            verify('Model should not succeed', $result)->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Password error message should be set', $model->errors)->hasKey('password');
            verify('Project title should not be changed', $project->title)->equals('Lorem ipsum title');
        });

        $this->specify('Success update attempt', function () use ($project) {
            $model = new ProjectForm($project, [
                'title'               => 'My new project title',
                'isPasswordProtected' => false,
            ]);

            $result = $model->save();

            $project->refresh();

            verify('Method should succeed and return instance of Project', $result)->isInstanceOf(Project::className());
            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Project title should be changed', $project->title)->equals('My new project title');
            verify('Project passwordHash should not be set', $project->passwordHash)->isEmpty();
        });
    }
}
