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
     * `ProjectForm::validateSubtypeRage()` method test.
     */
    public function testValidateSubtypeRange()
    {
        $user = User::findOne(1002);

        $this->specify('Tablet/Mobile error attempt', function() use ($user) {
            $model = new ProjectForm($user, [
                'type'    => Project::TYPE_TABLET,
                'subtype' => 31, // mismatch with mobile subtype
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Error message should be set', $model->errors)->hasKey('subtype');
        });

        $this->specify('Tablet/Mobile correct attempt', function() use ($user) {
            $model = new ProjectForm($user, [
                'type'    => Project::TYPE_TABLET,
                'subtype' => 21,
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Error message should not be set', $model->errors)->hasntKey('subtype');
        });

        $this->specify('Desktop correct attempt', function() use ($user) {
            $model = new ProjectForm($user, [
                'type' => Project::TYPE_DESKTOP, // doesn't require subtype
            ]);
            $model->validateSubtypeRange('subtype', []);

            verify('Error message should not be set', $model->errors)->hasntKey('subtype');
        });
    }

    /**
     * Tests whether `ProjectForm::save()` creates a valid Project model.
     */
    public function testSaveCreate()
    {
        $user = User::findOne(1002);

        $this->specify('Error create attempt', function() use ($user) {
            $model = new ProjectForm($user, [
                'title'   => '',
                'type'    => 0,
                'subtype' => 0,
            ]);

            verify('Model should not save', $model->save())->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Type error message should be set', $model->errors)->hasKey('type');
            verify('Subtype error message should not be set because not valid type is set', $model->errors)->hasntKey('subtype');
        });

        $this->specify('Success create attempt', function() use ($user) {
            $model = new ProjectForm($user, [
                'title'    => 'My new project title',
                'type'     => Project::TYPE_DESKTOP,
                'password' => '123456',
            ]);

            $result = $model->save();

            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Model should return instance of Project', $result)->isInstanceOf(Project::className());
            verify('Project title should be set', $result->title)->equals('My new project title');
            verify('Project type should be set', $result->type)->equals(Project::TYPE_DESKTOP);
            verify('Project subtype should not be set', $result->subtype)->null();
            verify('Project passwordHash should be set', $result->passwordHash)->notEmpty();
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
                'type'     => Project::TYPE_TABLET,
                'subtype'  => 31,
                'password' => '123456'
            ]);

            $result = $model->save($project);

            verify('Model should not save', $result)->null();
            verify('Title error message should be set', $model->errors)->hasKey('title');
            verify('Type error message should not be set', $model->errors)->hasntKey('type');
            verify('Subtype error message should be set', $model->errors)->hasKey('subtype');
            verify('Project title should not be changed', $project->title)->equals('Lorem ipsum title');
            verify('Project subtype should not be changed', $project->subtype)->notEquals(31);
            verify('Project password hash should not be changed', $project->passwordHash)->isEmpty();
        });

        $this->specify('Success update attempt', function() use ($user, $project) {
            $model = new ProjectForm($user, [
                'title'          => 'My new project title',
                'type'           => Project::TYPE_MOBILE,
                'subtype'        => 31,
                'changePassword' => true,
                'password'       => '123456'
            ]);

            $result = $model->save($project);

            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Model should returns instance of Project', $result)->isInstanceOf(Project::className());
            verify('Project title should be changed', $result->title)->equals('My new project title');
            verify('Project type should be changed', $result->type)->equals(Project::TYPE_MOBILE);
            verify('Project subtype should be changed', $result->subtype)->equals(31);
            verify('Project passwordHash should be set', $result->passwordHash)->notEmpty();
        });
    }
}
