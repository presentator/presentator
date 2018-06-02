<?php
namespace api\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\User;
use common\models\Project;
use api\models\ProjectForm;

/**
 * ProjectForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectFormTest extends \Codeception\Test\Unit
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
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
        ]);
    }

    /**
     * Tests whether `ProjectForm::save()` creates a valid Project model.
     */
    public function testSaveCreate()
    {
        $user = User::findOne(1002);

        $this->specify('Error create attempt', function() use ($user) {
            $model = new ProjectForm($user, [
                'title' => '',
            ]);

            $totalUserProjects = $user->countProjects();

            verify('Model should not succeed', $model->save())->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('User projects count should not be changed', $user->countProjects())->equals($totalUserProjects);
        });

        $this->specify('Success create attempt', function() use ($user) {
            $model = new ProjectForm($user, [
                'title'    => 'My new project title',
                'password' => '123456',
            ]);

            $result = $model->save();

            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Model should return instance of Project', $result)->isInstanceOf(Project::className());
            verify('Project title should be set', $result->title)->equals('My new project title');
            verify('Project passwordHash should be set', $result->passwordHash)->notEmpty();
            verify('Project should be linked with the provided user', $user->findProjectById($result->id))->notEmpty();
        });
    }

    /**
     * Tests whether `ProjectForm::save()` updates an existing Project model.
     */
    public function testSaveUpdate()
    {
        $user    = User::findOne(1002);
        $project = Project::findOne(1001);

        $this->specify('Error update attempt', function() use ($user, $project) {
            $model = new ProjectForm($user, [
                'title'    => '',
                'password' => '123456'
            ]);

            $result = $model->save($project);

            verify('Model should not save', $result)->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Project title should not be changed', $project->title)->equals('Lorem ipsum title');
            verify('Project password hash should not be changed', $project->passwordHash)->isEmpty();
        });

        $this->specify('Success update attempt', function() use ($user, $project) {
            $model = new ProjectForm($user, [
                'title'          => 'My new project title',
                'changePassword' => true,
                'password'       => '123456'
            ]);

            $result = $model->save($project);

            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Model should returns instance of Project', $result)->isInstanceOf(Project::className());
            verify('Project title should be changed', $result->title)->equals('My new project title');
            verify('Project passwordHash should be set', $result->passwordHash)->notEmpty();
        });
    }
}
